[CmdletBinding()]
param(
    [switch]$SkipInstall,
    [switch]$RunMigrations
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$repoRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$backendDirectory = Join-Path $repoRoot 'BackEnd'
$composerLock = Join-Path $backendDirectory 'composer.lock'

if (-not (Test-Path -LiteralPath $composerLock -PathType Leaf)) {
    throw 'BackEnd/composer.lock is required for reproducible backend QA.'
}

function Invoke-Checked {
    param(
        [Parameter(Mandatory = $true)][string]$Command,
        [Parameter(Mandatory = $true)][string[]]$Arguments
    )

    & $Command @Arguments
    if ($LASTEXITCODE -ne 0) {
        throw "$Command $($Arguments -join ' ') failed with exit code $LASTEXITCODE."
    }
}

Push-Location $backendDirectory
try {
    Invoke-Checked -Command 'composer' -Arguments @('validate', '--strict', '--no-check-publish')
    Invoke-Checked -Command 'composer' -Arguments @('audit', '--locked')

    if (-not $SkipInstall) {
        Invoke-Checked -Command 'composer' -Arguments @('install', '--no-interaction', '--prefer-dist', '--no-progress')
    }

    if (-not (Test-Path -LiteralPath (Join-Path $backendDirectory 'vendor/autoload.php') -PathType Leaf)) {
        throw 'Backend dependencies are missing. Run without -SkipInstall.'
    }

    if ($RunMigrations) {
        $environment = [Environment]::GetEnvironmentVariable('APP_ENV')
        $database = [Environment]::GetEnvironmentVariable('DB_DATABASE')
        if ($environment -notin @('testing', 'ci') -or $database -notmatch '(_testing|_ci)$') {
            throw 'Migration QA is allowed only with APP_ENV=testing|ci and a database ending in _testing or _ci.'
        }
        Invoke-Checked -Command 'php' -Arguments @('artisan', 'migrate', '--force')
    }

    Invoke-Checked -Command 'php' -Arguments @((Join-Path $repoRoot 'scripts/check-table-prefix.php'))
    Invoke-Checked -Command 'php' -Arguments @('vendor/bin/pint', '--test')
    Invoke-Checked -Command 'php' -Arguments @('vendor/bin/phpstan', 'analyse', '--memory-limit=1G')
    Invoke-Checked -Command 'php' -Arguments @('artisan', 'test')
}
finally {
    Pop-Location
}

Write-Host 'Backend QA completed successfully.'
