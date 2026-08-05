param(
    [Parameter(Mandatory = $false)]
    [string]$ProjectRoot = "D:\www\HongVan"
)
$ErrorActionPreference = "Stop"
$promptRoot = Join-Path $ProjectRoot "prompts\hongvan-master-v2"
$queue = Get-Content -LiteralPath (Join-Path $promptRoot "queue\MASTER_QUEUE.json") -Raw -Encoding UTF8 | ConvertFrom-Json
$state = Get-Content -LiteralPath (Join-Path $promptRoot "state\STATE.json") -Raw -Encoding UTF8 | ConvertFrom-Json
$generated = Get-Content -LiteralPath (Join-Path $promptRoot "generated\QUEUE.json") -Raw -Encoding UTF8 | ConvertFrom-Json

if ($state.current_task) {
    $s = $state.tasks.($state.current_task).status
    if ($s -eq "IN_PROGRESS" -or $s -eq "FAILED") {
        $task = $queue.tasks | Where-Object { $_.id -eq $state.current_task } | Select-Object -First 1
        Write-Host "$($task.id) - $($task.title)"
        Write-Host (Join-Path $promptRoot $task.file)
        exit 0
    }
}

if ($generated.tasks) {
    $g = $generated.tasks | Where-Object { $_.status -eq "PENDING" -or $_.status -eq "IN_PROGRESS" -or $_.status -eq "FAILED" } | Select-Object -First 1
    if ($g) {
        Write-Host "$($g.id) - $($g.title)"
        Write-Host (Join-Path $promptRoot $g.file)
        exit 0
    }
}

foreach ($task in $queue.tasks) {
    $status = $state.tasks.($task.id).status
    if ($status -ne "PENDING") { continue }
    $ready = $true
    foreach ($dep in $task.depends_on) {
        $depStatus = $state.tasks.($dep).status
        if ($depStatus -ne "DONE" -and $depStatus -ne "VERIFIED") { $ready = $false; break }
    }
    if ($ready) {
        Write-Host "$($task.id) - $($task.title)"
        Write-Host (Join-Path $promptRoot $task.file)
        exit 0
    }
}
Write-Host "No runnable task. Check BLOCKED dependencies and state."
exit 2
