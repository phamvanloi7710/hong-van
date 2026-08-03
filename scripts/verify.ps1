[CmdletBinding()]
param(
    [switch]$SkipInstall,
    [switch]$SkipReadonlySourceCheck,
    [switch]$RunMigrations,
    [switch]$RunE2E,
    [string]$SmokeBaseUrl
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
$powerShell = (Get-Process -Id $PID).Path

function Invoke-Script {
    param(
        [Parameter(Mandatory = $true)][string]$Path,
        [string[]]$Arguments = @()
    )
    & $script:powerShell -NoProfile -ExecutionPolicy Bypass -File $Path @Arguments
    if ($LASTEXITCODE -ne 0) {
        throw "$Path failed with exit code $LASTEXITCODE."
    }
}

Invoke-Script -Path (Join-Path $PSScriptRoot 'verify-prerequisites.ps1')
if (-not $SkipReadonlySourceCheck) {
    Invoke-Script -Path (Join-Path $PSScriptRoot 'verify-readonly-sources.ps1')
}

$backendArguments = @()
if ($SkipInstall) { $backendArguments += '-SkipInstall' }
if ($RunMigrations) { $backendArguments += '-RunMigrations' }
Invoke-Script -Path (Join-Path $PSScriptRoot 'qa-backend.ps1') -Arguments $backendArguments

$adminArguments = @()
if ($SkipInstall) { $adminArguments += '-SkipInstall' }
if ($RunE2E) { $adminArguments += '-RunE2E' }
Invoke-Script -Path (Join-Path $PSScriptRoot 'qa-admin.ps1') -Arguments $adminArguments

if (-not [string]::IsNullOrWhiteSpace($SmokeBaseUrl)) {
    Invoke-Script -Path (Join-Path $PSScriptRoot 'smoke.ps1') -Arguments @('-BaseUrl', $SmokeBaseUrl)
}

Write-Host 'Repository verification completed successfully.'
