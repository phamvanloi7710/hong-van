param(
    [string]$PackRoot = (Split-Path -Parent (Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)))
)
$ErrorActionPreference = "Stop"
$promptRoot = Join-Path $PackRoot "prompts\hongvan-master-v2"
$queuePath = Join-Path $promptRoot "queue\MASTER_QUEUE.json"
$statePath = Join-Path $promptRoot "state\STATE.json"
$generatedQueuePath = Join-Path $promptRoot "generated\QUEUE.json"
if (-not (Test-Path $queuePath)) { throw "Queue not found: $queuePath" }
if (-not (Test-Path $statePath)) { throw "State not found: $statePath" }
if (-not (Test-Path $generatedQueuePath)) { throw "Generated queue not found: $generatedQueuePath" }
$queue = Get-Content -LiteralPath $queuePath -Raw -Encoding UTF8 | ConvertFrom-Json
$state = Get-Content -LiteralPath $statePath -Raw -Encoding UTF8 | ConvertFrom-Json
$generatedQueue = Get-Content -LiteralPath $generatedQueuePath -Raw -Encoding UTF8 | ConvertFrom-Json
if ($queue.task_count -ne 240) { throw "Expected 240 tasks, found $($queue.task_count)" }
$allowedStatuses = @("PENDING", "IN_PROGRESS", "DONE", "VERIFIED", "BLOCKED", "BLOCKED_EXTERNAL", "FAILED")
$ids = @{}
foreach ($task in $queue.tasks) {
    if ($ids.ContainsKey($task.id)) { throw "Duplicate task id: $($task.id)" }
    $ids[$task.id] = $true
    $path = Join-Path $promptRoot $task.file
    if (-not (Test-Path -LiteralPath $path)) { throw "Missing task file: $path" }
    if ((Get-Item -LiteralPath $path).Length -lt 800) { throw "Task file is unexpectedly small: $path" }
    foreach ($dependency in $task.depends_on) {
        if (-not ($queue.tasks.id -contains $dependency)) { throw "Unknown dependency $dependency for task $($task.id)" }
    }
    $stateTask = $state.tasks.($task.id)
    if ($null -eq $stateTask) { throw "Missing state entry for task $($task.id)" }
    if ($allowedStatuses -notcontains $stateTask.status) { throw "Invalid state status for task $($task.id): $($stateTask.status)" }
}
if (@($state.tasks.PSObject.Properties).Count -ne 240) { throw "Expected 240 state entries, found $(@($state.tasks.PSObject.Properties).Count)" }
if ($state.current_task -and -not $ids.ContainsKey($state.current_task)) { throw "Unknown current task: $($state.current_task)" }
if ($state.current_task -and $state.tasks.($state.current_task).status -notin @("IN_PROGRESS", "FAILED")) {
    throw "Current task must be IN_PROGRESS or FAILED: $($state.current_task)"
}
if ($generatedQueue.status -notin @("EMPTY", "PENDING", "IN_PROGRESS", "COMPLETED", "FAILED")) {
    throw "Invalid generated queue status: $($generatedQueue.status)"
}
foreach ($generatedTask in $generatedQueue.tasks) {
    if (-not $generatedTask.id -or -not $generatedTask.file -or $allowedStatuses -notcontains $generatedTask.status) {
        throw "Invalid generated task entry"
    }
    if (-not (Test-Path -LiteralPath (Join-Path $promptRoot $generatedTask.file))) {
        throw "Missing generated task file: $($generatedTask.file)"
    }
}
Write-Host "Pack validation PASS"
Write-Host "Tasks: 240"
Write-Host "State entries: 240"
Write-Host "Generated queue: $($generatedQueue.status)"
Write-Host "Queue: $queuePath"
