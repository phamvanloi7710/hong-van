[CmdletBinding()]
param(
    [switch]$SkipInstall,
    [switch]$RunE2E
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$repoRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$adminDirectory = Join-Path $repoRoot 'Admin'
$npmCommand = if (Get-Command 'npm.cmd' -ErrorAction SilentlyContinue) { 'npm.cmd' } else { 'npm' }

if (-not (Test-Path -LiteralPath (Join-Path $adminDirectory 'package-lock.json') -PathType Leaf)) {
    throw 'Admin/package-lock.json is required for reproducible admin QA.'
}

function Invoke-Npm {
    param([Parameter(Mandatory = $true)][string[]]$Arguments)
    & $script:npmCommand @Arguments
    if ($LASTEXITCODE -ne 0) {
        throw "npm $($Arguments -join ' ') failed with exit code $LASTEXITCODE."
    }
}

Push-Location $adminDirectory
try {
    if (-not $SkipInstall) {
        Invoke-Npm -Arguments @('ci')
    }
    if (-not (Test-Path -LiteralPath (Join-Path $adminDirectory 'node_modules/.package-lock.json') -PathType Leaf)) {
        throw 'Admin dependencies are missing. Run without -SkipInstall.'
    }

    Invoke-Npm -Arguments @('audit', '--omit=dev', '--audit-level=high')
    Invoke-Npm -Arguments @('audit', '--audit-level=critical')
    Invoke-Npm -Arguments @('run', 'lint')
    Invoke-Npm -Arguments @('test', '--', '--watch=false')
    Invoke-Npm -Arguments @('run', 'build:laravel')
    if ($RunE2E) {
        Invoke-Npm -Arguments @('exec', '--', 'playwright', 'test')
    }
}
finally {
    Pop-Location
}

Write-Host 'Admin QA completed successfully.'
