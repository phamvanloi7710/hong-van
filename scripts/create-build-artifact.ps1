[CmdletBinding()]
param(
    [switch]$SkipInstall,
    [string]$OutputDirectory
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$repoRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$artifactParent = if ([string]::IsNullOrWhiteSpace($OutputDirectory)) {
    Join-Path $repoRoot '.tmp/artifacts'
} else {
    [System.IO.Path]::GetFullPath($OutputDirectory)
}
$artifactDirectory = [System.IO.Path]::GetFullPath((Join-Path $artifactParent 'hongvan-web-assets'))
$expectedDefaultParent = [System.IO.Path]::GetFullPath((Join-Path $repoRoot '.tmp/artifacts'))

if ([string]::IsNullOrWhiteSpace($OutputDirectory) -and $artifactParent -ne $expectedDefaultParent) {
    throw 'Default artifact path resolution failed.'
}
if ([System.IO.Path]::GetFileName($artifactDirectory) -ne 'hongvan-web-assets') {
    throw "Refusing unsafe artifact target: $artifactDirectory"
}

function Invoke-Checked {
    param(
        [Parameter(Mandatory = $true)][string]$Command,
        [Parameter(Mandatory = $true)][string[]]$Arguments,
        [Parameter(Mandatory = $true)][string]$WorkingDirectory
    )
    Push-Location $WorkingDirectory
    try {
        & $Command @Arguments
        if ($LASTEXITCODE -ne 0) {
            throw "$Command $($Arguments -join ' ') failed with exit code $LASTEXITCODE."
        }
    }
    finally {
        Pop-Location
    }
}

$npmCommand = if (Get-Command 'npm.cmd' -ErrorAction SilentlyContinue) { 'npm.cmd' } else { 'npm' }
$backendDirectory = Join-Path $repoRoot 'BackEnd'
$adminDirectory = Join-Path $repoRoot 'Admin'

if (-not $SkipInstall) {
    Invoke-Checked -Command $npmCommand -Arguments @('ci') -WorkingDirectory $backendDirectory
    Invoke-Checked -Command $npmCommand -Arguments @('ci') -WorkingDirectory $adminDirectory
}

Invoke-Checked -Command $npmCommand -Arguments @('run', 'build') -WorkingDirectory $backendDirectory
Invoke-Checked -Command $npmCommand -Arguments @('run', 'build:laravel') -WorkingDirectory $adminDirectory

$publicBuild = Join-Path $backendDirectory 'public/build'
$adminBuild = Join-Path $backendDirectory 'public/admin/browser'
foreach ($requiredDirectory in @($publicBuild, $adminBuild)) {
    if (-not (Test-Path -LiteralPath $requiredDirectory -PathType Container)) {
        throw "Required build output is missing: $requiredDirectory"
    }
}

New-Item -ItemType Directory -Path $artifactParent -Force | Out-Null
if (Test-Path -LiteralPath $artifactDirectory) {
    Remove-Item -LiteralPath $artifactDirectory -Recurse -Force
}
New-Item -ItemType Directory -Path (Join-Path $artifactDirectory 'public/admin') -Force | Out-Null
Copy-Item -LiteralPath $publicBuild -Destination (Join-Path $artifactDirectory 'public/build') -Recurse
Copy-Item -LiteralPath $adminBuild -Destination (Join-Path $artifactDirectory 'public/admin/browser') -Recurse

$checksumPath = Join-Path $artifactDirectory 'SHA256SUMS.txt'
$prefixLength = $artifactDirectory.TrimEnd('\', '/').Length + 1
$lines = Get-ChildItem -LiteralPath $artifactDirectory -File -Recurse | Where-Object {
    $_.FullName -ne $checksumPath
} | Sort-Object FullName | ForEach-Object {
    $relative = $_.FullName.Substring($prefixLength).Replace('\', '/')
    $hash = (Get-FileHash -LiteralPath $_.FullName -Algorithm SHA256).Hash.ToLowerInvariant()
    "$hash  $relative"
}
$checksumContent = if ($lines.Count -gt 0) { ($lines -join "`n") + "`n" } else { '' }
[System.IO.File]::WriteAllText($checksumPath, $checksumContent, (New-Object System.Text.UTF8Encoding($false)))

Write-Host "Build artifact created at $artifactDirectory with $($lines.Count) checksums."
