[CmdletBinding()]
param()

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$repoRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
if (-not (Test-Path -LiteralPath (Join-Path $repoRoot 'AGENTS.md') -PathType Leaf)) {
    Write-Error "Repository root could not be verified from: $PSScriptRoot"
    exit 2
}

$hasFailure = $false

function Get-ToolVersion {
    param(
        [Parameter(Mandatory = $true)][string]$Command,
        [Parameter(Mandatory = $true)][string[]]$Arguments
    )

    if (-not (Get-Command -Name $Command -ErrorAction SilentlyContinue)) {
        return $null
    }

    $previousErrorActionPreference = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    try {
        $output = (& $Command @Arguments 2>&1 | Out-String).Trim()
        $exitCode = $LASTEXITCODE
    }
    finally {
        $ErrorActionPreference = $previousErrorActionPreference
    }

    if ($exitCode -ne 0) {
        return $null
    }

    $match = [regex]::Match($output, '(?<!\d)(\d+\.\d+(?:\.\d+)?)(?!\d)')
    if (-not $match.Success) {
        return $null
    }

    return [version]$match.Groups[1].Value
}

function Write-Result {
    param(
        [Parameter(Mandatory = $true)][string]$Tool,
        [AllowNull()][version]$Version,
        [Parameter(Mandatory = $true)][bool]$Compatible,
        [Parameter(Mandatory = $true)][string]$Expected
    )

    if ($null -eq $Version) {
        Write-Host ('[MISSING]      {0,-10} expected {1}' -f $Tool, $Expected)
        $script:hasFailure = $true
        return
    }

    if ($Compatible) {
        Write-Host ('[OK]           {0,-10} {1}' -f $Tool, $Version)
        return
    }

    Write-Host ('[INCOMPATIBLE] {0,-10} {1}; expected {2}' -f $Tool, $Version, $Expected)
    $script:hasFailure = $true
}

$php = Get-ToolVersion -Command 'php' -Arguments @('--version')
$composer = Get-ToolVersion -Command 'composer' -Arguments @('--version')
$node = Get-ToolVersion -Command 'node' -Arguments @('--version')
$npm = Get-ToolVersion -Command 'npm' -Arguments @('--version')
$git = Get-ToolVersion -Command 'git' -Arguments @('--version')

Write-Result -Tool 'PHP' -Version $php -Compatible ($null -ne $php -and $php -ge [version]'8.5.0' -and $php -lt [version]'8.6.0') -Expected '8.5.x'
Write-Result -Tool 'Composer' -Version $composer -Compatible ($null -ne $composer -and $composer.Major -eq 2) -Expected '2.x'
Write-Result -Tool 'Node.js' -Version $node -Compatible ($null -ne $node -and $node -ge [version]'24.15.0' -and $node -lt [version]'25.0.0') -Expected '>= 24.15.0 and < 25.0.0'
Write-Result -Tool 'npm' -Version $npm -Compatible ($null -ne $npm) -Expected 'available with the target Node.js runtime'
Write-Result -Tool 'Git' -Version $git -Compatible ($null -ne $git -and $git.Major -ge 2) -Expected '2.x or newer'

if ($hasFailure) {
    Write-Host 'Prerequisite verification completed with missing or incompatible tools.'
    exit 1
}

Write-Host 'All prerequisite checks passed.'
exit 0
