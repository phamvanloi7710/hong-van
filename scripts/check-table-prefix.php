<?php

declare(strict_types=1);

const REQUIRED_TABLE_PREFIX = 'hongvan_';

$repositoryRoot = dirname(__DIR__);
$violations = [];
$explicitPaths = [];

foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with($argument, '--path=')) {
        $explicitPaths[] = substr($argument, strlen('--path='));

        continue;
    }

    $violations[] = "Unknown argument: {$argument}";
}

$targets = $explicitPaths ?: [
    $repositoryRoot.DIRECTORY_SEPARATOR.'BackEnd'.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations',
    $repositoryRoot.DIRECTORY_SEPARATOR.'BackEnd'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Models',
];

$files = [];

foreach ($targets as $target) {
    $candidate = isAbsolutePath($target) ? $target : $repositoryRoot.DIRECTORY_SEPARATOR.$target;

    if (is_file($candidate)) {
        $files[] = $candidate;

        continue;
    }

    if (! is_dir($candidate)) {
        $violations[] = "Path does not exist: {$target}";

        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($candidate, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
            $files[] = $file->getPathname();
        }
    }
}

sort($files);

foreach (array_unique($files) as $file) {
    $contents = file_get_contents($file);

    if ($contents === false) {
        $violations[] = relativePath($repositoryRoot, $file).': cannot read file';

        continue;
    }

    inspectPhpSource($repositoryRoot, $file, stripPhpComments($contents), $violations);
}

if ($explicitPaths === []) {
    inspectKnownConfiguration($repositoryRoot, $violations);
}

if ($violations !== []) {
    fwrite(STDERR, "Table prefix check failed:\n");

    foreach (array_unique($violations) as $violation) {
        fwrite(STDERR, " - {$violation}\n");
    }

    exit(1);
}

fwrite(STDOUT, sprintf(
    "Table prefix check passed: %d PHP files inspected; required prefix is %s.\n",
    count(array_unique($files)),
    REQUIRED_TABLE_PREFIX,
));

/**
 * @param  list<string>  $violations
 */
function inspectPhpSource(string $repositoryRoot, string $file, string $contents, array &$violations): void
{
    $relativeFile = relativePath($repositoryRoot, $file);
    $schemaPattern = '/Schema\s*::\s*(create|table|drop|dropIfExists|hasTable|hasColumn|rename)\s*\(([^\r\n;]*)/i';

    if (preg_match_all($schemaPattern, $contents, $schemaCalls, PREG_SET_ORDER) !== false) {
        foreach ($schemaCalls as $schemaCall) {
            $method = strtolower($schemaCall[1]);
            $arguments = $schemaCall[2];

            if (preg_match('/^\s*([\'\"])([^\'\"]+)\1/', $arguments, $firstTable) !== 1) {
                $violations[] = "{$relativeFile}: Schema::{$schemaCall[1]} uses a dynamic or unverifiable table name";

                continue;
            }

            validateTableName($relativeFile, $firstTable[2], "Schema::{$schemaCall[1]}", $violations);

            if ($method === 'rename') {
                if (preg_match('/^\s*([\'\"])([^\'\"]+)\1\s*,\s*([\'\"])([^\'\"]+)\3/', $arguments, $renameTables) !== 1) {
                    $violations[] = "{$relativeFile}: Schema::rename destination is dynamic or unverifiable";

                    continue;
                }

                validateTableName($relativeFile, $renameTables[4], 'Schema::rename destination', $violations);
            }
        }
    }

    if (preg_match_all('/->\s*(constrained|on)\s*\(\s*([\'\"])([^\'\"]+)\2\s*\)/i', $contents, $references, PREG_SET_ORDER) !== false) {
        foreach ($references as $reference) {
            validateTableName($relativeFile, $reference[3], "->{$reference[1]}", $violations);
        }
    }

    if (preg_match_all('/\b(?:public|protected)\s+\$table\s*=\s*([\'\"])([^\'\"]+)\1\s*;/i', $contents, $modelTables, PREG_SET_ORDER) !== false) {
        foreach ($modelTables as $modelTable) {
            validateTableName($relativeFile, $modelTable[2], 'model $table', $violations);
        }
    }
}

/**
 * @param  list<string>  $violations
 */
function validateTableName(string $file, string $table, string $source, array &$violations): void
{
    if (! str_starts_with($table, REQUIRED_TABLE_PREFIX)) {
        $violations[] = "{$file}: {$source} references table '{$table}' without prefix ".REQUIRED_TABLE_PREFIX;

        return;
    }

    if (str_starts_with($table, REQUIRED_TABLE_PREFIX.REQUIRED_TABLE_PREFIX)) {
        $violations[] = "{$file}: {$source} references double-prefixed table '{$table}'";
    }
}

/**
 * @param  list<string>  $violations
 */
function inspectKnownConfiguration(string $repositoryRoot, array &$violations): void
{
    $requiredValues = [
        'BackEnd/config/database.php' => ['hongvan_migrations'],
        'BackEnd/config/auth.php' => ['hongvan_password_reset_tokens'],
        'BackEnd/config/session.php' => ['hongvan_sessions'],
        'BackEnd/config/cache.php' => ['hongvan_cache', 'hongvan_cache_locks'],
        'BackEnd/config/queue.php' => ['hongvan_jobs', 'hongvan_job_batches', 'hongvan_failed_jobs'],
        'BackEnd/.env.example' => [
            'hongvan_password_reset_tokens',
            'hongvan_sessions',
            'hongvan_cache',
            'hongvan_cache_locks',
            'hongvan_jobs',
            'hongvan_job_batches',
            'hongvan_failed_jobs',
        ],
    ];

    foreach ($requiredValues as $relativeFile => $values) {
        $absoluteFile = $repositoryRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativeFile);
        $contents = file_get_contents($absoluteFile);

        if ($contents === false) {
            $violations[] = "{$relativeFile}: cannot read required configuration";

            continue;
        }

        foreach ($values as $value) {
            if (! str_contains($contents, $value)) {
                $violations[] = "{$relativeFile}: missing required table mapping '{$value}'";
            }
        }

        if (preg_match_all('/env\(\s*([\'\"])[^\'\"]*TABLE[^\'\"]*\1\s*,\s*([\'\"])([^\'\"]+)\2/', $contents, $tableDefaults, PREG_SET_ORDER) !== false) {
            foreach ($tableDefaults as $tableDefault) {
                validateTableName($relativeFile, $tableDefault[3], 'table environment default', $violations);
            }
        }

        if ($relativeFile === 'BackEnd/.env.example'
            && preg_match_all('/^[A-Z0-9_]*TABLE=([^\r\n]+)$/m', $contents, $environmentTables, PREG_SET_ORDER) !== false) {
            foreach ($environmentTables as $environmentTable) {
                validateTableName($relativeFile, trim($environmentTable[1]), 'table environment value', $violations);
            }
        }

        if (str_contains($contents, 'DB_PREFIX')) {
            $violations[] = "{$relativeFile}: connection-level DB_PREFIX is forbidden";
        }
    }

    $databaseConfig = file_get_contents($repositoryRoot.DIRECTORY_SEPARATOR.'BackEnd'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'database.php');

    if ($databaseConfig === false) {
        return;
    }

    $connectionsEnd = strpos($databaseConfig, "'migrations' =>");
    $connectionsConfig = $connectionsEnd === false ? $databaseConfig : substr($databaseConfig, 0, $connectionsEnd);

    if (preg_match_all('/[\'\"]prefix[\'\"]\s*=>\s*([^,\r\n]+)/', $connectionsConfig, $prefixes, PREG_SET_ORDER) !== false) {
        foreach ($prefixes as $prefix) {
            if (trim($prefix[1]) !== "''" && trim($prefix[1]) !== '\"\"') {
                $violations[] = 'BackEnd/config/database.php: database connection prefix must remain empty';
            }
        }
    }
}

function stripPhpComments(string $contents): string
{
    $result = '';

    foreach (token_get_all($contents) as $token) {
        if (is_array($token) && in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
            $result .= str_repeat("\n", substr_count($token[1], "\n"));

            continue;
        }

        $result .= is_array($token) ? $token[1] : $token;
    }

    return $result;
}

function isAbsolutePath(string $path): bool
{
    return preg_match('/^(?:[A-Za-z]:[\\\\\/]|[\\\\\/]{2}|\/)/', $path) === 1;
}

function relativePath(string $repositoryRoot, string $path): string
{
    $normalizedRoot = rtrim(str_replace('\\', '/', $repositoryRoot), '/').'/';
    $normalizedPath = str_replace('\\', '/', $path);

    return str_starts_with(strtolower($normalizedPath), strtolower($normalizedRoot))
        ? substr($normalizedPath, strlen($normalizedRoot))
        : $normalizedPath;
}
