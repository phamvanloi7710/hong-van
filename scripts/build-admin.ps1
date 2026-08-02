[CmdletBinding()]
param(
    [ValidateSet('Full', 'BuildOnly')]
    [string]$Mode = 'Full',
    [switch]$SkipInstall
)

$ErrorActionPreference = 'Stop'
$repositoryDirectory = Split-Path -Parent $PSScriptRoot
$adminDirectory = Join-Path $repositoryDirectory 'Admin'
$packageLockPath = Join-Path $adminDirectory 'package-lock.json'
$installedLockPath = Join-Path $adminDirectory 'node_modules/.package-lock.json'
$npmCommand = if (Get-Command npm.cmd -ErrorAction SilentlyContinue) { 'npm.cmd' } else { 'npm' }

function Invoke-NpmCommand {
    param(
        [Parameter(Mandatory)]
        [string[]]$Arguments
    )

    & $script:npmCommand @Arguments

    if ($LASTEXITCODE -ne 0) {
        throw "npm $($Arguments -join ' ') failed with exit code $LASTEXITCODE."
    }
}

Push-Location $adminDirectory

try {
    $installRequired = -not $SkipInstall -and (
        -not (Test-Path -LiteralPath $installedLockPath) -or
        (Get-Item -LiteralPath $packageLockPath).LastWriteTimeUtc -gt
            (Get-Item -LiteralPath $installedLockPath).LastWriteTimeUtc
    )

    if ($installRequired) {
        Invoke-NpmCommand -Arguments @('ci')
    }

    if ($Mode -eq 'Full') {
        Invoke-NpmCommand -Arguments @('run', 'lint')
        Invoke-NpmCommand -Arguments @('test', '--', '--watch=false')
    }

    Invoke-NpmCommand -Arguments @('run', 'build:laravel')
} finally {
    Pop-Location
}
