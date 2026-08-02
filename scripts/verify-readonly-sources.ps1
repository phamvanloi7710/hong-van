[CmdletBinding()]
param(
    [switch]$PrintBaseline
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$repoRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$baselinePath = Join-Path $repoRoot '.readonly-sources.sha256'
$sourcePaths = @('Template', 'FrontEndTemplate', 'SourceIntegrations')

if (-not (Test-Path -LiteralPath (Join-Path $repoRoot 'AGENTS.md') -PathType Leaf)) {
    Write-Error "Repository root could not be verified from: $PSScriptRoot"
    exit 2
}

if (-not (Get-Command -Name 'git' -ErrorAction SilentlyContinue)) {
    Write-Error 'Git is required to calculate read-only source fingerprints.'
    exit 2
}

function Get-Sha256Text {
    param([Parameter(Mandatory = $true)][string]$Text)

    $sha256 = [System.Security.Cryptography.SHA256]::Create()
    try {
        $encoding = New-Object System.Text.UTF8Encoding($false)
        $bytes = $encoding.GetBytes($Text)
        return ([System.BitConverter]::ToString($sha256.ComputeHash($bytes))).Replace('-', '').ToLowerInvariant()
    }
    finally {
        $sha256.Dispose()
    }
}

function Get-GitObjectHashes {
    param([Parameter(Mandatory = $true)][string[]]$Paths)

    if ($Paths.Count -eq 0) {
        return [string[]]@()
    }

    $gitCommand = Get-Command -Name 'git' -ErrorAction Stop
    $startInfo = New-Object System.Diagnostics.ProcessStartInfo
    $startInfo.FileName = $gitCommand.Source
    $startInfo.Arguments = 'hash-object --stdin-paths'
    $startInfo.WorkingDirectory = $repoRoot
    $startInfo.UseShellExecute = $false
    $startInfo.CreateNoWindow = $true
    $startInfo.RedirectStandardInput = $true
    $startInfo.RedirectStandardOutput = $true
    $startInfo.RedirectStandardError = $true

    $process = New-Object System.Diagnostics.Process
    $process.StartInfo = $startInfo
    try {
        [void]$process.Start()
        $standardOutputTask = $process.StandardOutput.ReadToEndAsync()
        $standardErrorTask = $process.StandardError.ReadToEndAsync()
        foreach ($path in $Paths) {
            $process.StandardInput.WriteLine($path)
        }
        $process.StandardInput.Close()
        $process.WaitForExit()
        $standardOutput = $standardOutputTask.Result
        $standardError = $standardErrorTask.Result

        if ($process.ExitCode -ne 0) {
            throw "Git hash-object failed: $($standardError.Trim())"
        }

        return [string[]]@($standardOutput -split "`r?`n" | Where-Object { $_ -ne '' })
    }
    finally {
        $process.Dispose()
    }
}

function Get-DirectoryFingerprint {
    param([Parameter(Mandatory = $true)][string]$RelativeDirectory)

    $absoluteDirectory = [System.IO.Path]::GetFullPath((Join-Path $repoRoot $RelativeDirectory))
    if (-not (Test-Path -LiteralPath $absoluteDirectory -PathType Container)) {
        return [pscustomobject]@{ Hash = $null; Count = 0 }
    }

    $prefixLength = $absoluteDirectory.TrimEnd([System.IO.Path]::DirectorySeparatorChar, [System.IO.Path]::AltDirectorySeparatorChar).Length + 1
    [string[]]$relativeFiles = @(
        Get-ChildItem -LiteralPath $absoluteDirectory -File -Recurse -Force | ForEach-Object {
            $_.FullName.Substring($prefixLength).Replace('\', '/')
        }
    )
    [Array]::Sort($relativeFiles, [System.StringComparer]::Ordinal)

    $gitPaths = New-Object System.Collections.Generic.List[string]
    foreach ($relativeFile in $relativeFiles) {
        if ($relativeFile.Contains("`n") -or $relativeFile.Contains("`r")) {
            throw "Unsupported newline in path under ${RelativeDirectory}."
        }
        $gitPaths.Add("$RelativeDirectory/$relativeFile")
    }

    [string[]]$fileHashes = @(Get-GitObjectHashes -Paths $gitPaths.ToArray())
    if ($fileHashes.Count -ne $relativeFiles.Count) {
        throw "Git could not hash every file under ${RelativeDirectory}."
    }

    $manifest = New-Object System.Text.StringBuilder
    for ($index = 0; $index -lt $relativeFiles.Count; $index++) {
        [void]$manifest.Append($fileHashes[$index].ToLowerInvariant()).Append(' ').Append($relativeFiles[$index]).Append("`n")
    }

    return [pscustomobject]@{ Hash = Get-Sha256Text -Text $manifest.ToString(); Count = $relativeFiles.Count }
}

$actual = @{}
foreach ($sourcePath in $sourcePaths) {
    $actual[$sourcePath] = Get-DirectoryFingerprint -RelativeDirectory $sourcePath
}

if ($PrintBaseline) {
    foreach ($sourcePath in $sourcePaths) {
        if ($null -eq $actual[$sourcePath].Hash) {
            Write-Error "Required read-only source directory is missing: $sourcePath"
            exit 1
        }
        Write-Output ('{0}  {1}' -f $actual[$sourcePath].Hash, $sourcePath)
    }
    exit 0
}

if (-not (Test-Path -LiteralPath $baselinePath -PathType Leaf)) {
    Write-Host '[MISSING] Baseline file .readonly-sources.sha256 was not found.'
    Write-Host 'Run with -PrintBaseline, review the output, and update the baseline intentionally.'
    exit 1
}

$expected = @{}
foreach ($line in Get-Content -LiteralPath $baselinePath) {
    if ([string]::IsNullOrWhiteSpace($line) -or $line.StartsWith('#')) {
        continue
    }

    if ($line -notmatch '^([0-9a-fA-F]{64})  (.+)$') {
        Write-Error "Invalid baseline entry: $line"
        exit 2
    }
    $expected[$Matches[2]] = $Matches[1].ToLowerInvariant()
}

$hasFailure = $false
foreach ($sourcePath in $sourcePaths) {
    $fingerprint = $actual[$sourcePath]
    if ($null -eq $fingerprint.Hash) {
        Write-Host ('[MISSING] {0}' -f $sourcePath)
        $hasFailure = $true
    }
    elseif (-not $expected.ContainsKey($sourcePath)) {
        Write-Host ('[UNTRACKED] {0} ({1} files)' -f $sourcePath, $fingerprint.Count)
        $hasFailure = $true
    }
    elseif ($expected[$sourcePath] -ne $fingerprint.Hash) {
        Write-Host ('[CHANGED] {0} ({1} files)' -f $sourcePath, $fingerprint.Count)
        $hasFailure = $true
    }
    else {
        Write-Host ('[MATCH]   {0} ({1} files)' -f $sourcePath, $fingerprint.Count)
    }
}

if ($hasFailure) {
    Write-Host 'Read-only source verification failed.'
    exit 1
}

Write-Host 'All read-only source fingerprints match the approved baseline.'
exit 0
