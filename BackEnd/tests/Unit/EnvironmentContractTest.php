<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class EnvironmentContractTest extends TestCase
{
    public function test_backend_example_covers_project_environment_contract_without_dead_keys(): void
    {
        $variables = $this->variables($this->backendPath('.env.example'));
        $configVariables = $this->configVariables();

        $required = [
            'ADMIN_DEFAULT_LOCALE',
            'APP_PREVIOUS_KEYS',
            'DASHBOARD_CACHE_TTL_SECONDS',
            'DASHBOARD_REPORT_RETENTION_HOURS',
            'DASHBOARD_SYNC_EXPORT_LIMIT',
            'DASHBOARD_TIMEZONE',
            'LEAD_DEDUPLICATE_MINUTES',
            'LEAD_EXPORT_LIMIT',
            'LEAD_PRIVACY_POLICY_VERSION',
            'LEAD_RETENTION_DAYS',
            'PAGE_BUILDER_PREVIEW_CACHE_STORE',
            'PAGE_BUILDER_PREVIEW_TTL_SECONDS',
        ];

        foreach ($required as $key) {
            $this->assertArrayHasKey($key, $variables, "$key must be documented in BackEnd/.env.example.");
            $this->assertContains($key, $configVariables, "$key must be consumed by a backend config file.");
        }

        foreach (['BCRYPT_ROUNDS', 'BROADCAST_CONNECTION', 'PHP_CLI_SERVER_WORKERS', 'VITE_APP_NAME'] as $key) {
            $this->assertArrayNotHasKey($key, $variables, "$key is not consumed by the production source.");
            $this->assertNotContains($key, $configVariables, "$key unexpectedly became a backend config variable.");
        }
    }

    public function test_root_example_only_documents_the_consumed_cross_workspace_override(): void
    {
        $variables = $this->variables(dirname($this->backendPath()).DIRECTORY_SEPARATOR.'.env.example');

        $this->assertSame(
            ['PLAYWRIGHT_BASE_URL' => 'http://hongvan.local/admin/'],
            $variables,
        );

        $playwrightConfig = file_get_contents(dirname($this->backendPath()).DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'playwright.config.ts');
        $this->assertIsString($playwrightConfig);
        $this->assertStringContainsString("process.env['PLAYWRIGHT_BASE_URL']", $playwrightConfig);
    }

    public function test_secret_placeholders_are_empty(): void
    {
        $variables = $this->variables($this->backendPath('.env.example'));

        foreach ([
            'APP_KEY',
            'APP_PREVIOUS_KEYS',
            'SEARCH_ANALYTICS_HASH_KEY',
            'DB_PASSWORD',
            'SUPER_ADMIN_EMAIL',
            'SUPER_ADMIN_PASSWORD',
            'REDIS_PASSWORD',
            'MAIL_USERNAME',
            'MAIL_PASSWORD',
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',
            'AWS_BUCKET',
        ] as $key) {
            $this->assertArrayHasKey($key, $variables);
            $this->assertSame('', $variables[$key], "$key must not contain a real or example secret.");
        }
    }

    /** @return array<string, string> */
    private function variables(string $path): array
    {
        $content = file_get_contents($path);
        $this->assertIsString($content, "Unable to read $path.");

        preg_match_all('/^([A-Z][A-Z0-9_]*)=(.*)$/m', $content, $matches, PREG_SET_ORDER);

        $variables = [];
        foreach ($matches as $match) {
            $this->assertArrayNotHasKey($match[1], $variables, "$match[1] is duplicated in $path.");
            $variables[$match[1]] = trim($match[2], " \t\n\r\0\x0B\"'");
        }

        return $variables;
    }

    private function backendPath(string $path = ''): string
    {
        $root = dirname(__DIR__, 2);

        return $path === '' ? $root : $root.DIRECTORY_SEPARATOR.$path;
    }

    /** @return list<string> */
    private function configVariables(): array
    {
        $variables = [];

        foreach (glob($this->backendPath('config/*.php')) ?: [] as $path) {
            $content = file_get_contents($path);
            $this->assertIsString($content, "Unable to read $path.");
            preg_match_all('/env\(\s*[\'\"]([A-Z0-9_]+)[\'\"]/', $content, $matches);
            array_push($variables, ...$matches[1]);
        }

        return array_values(array_unique($variables));
    }
}
