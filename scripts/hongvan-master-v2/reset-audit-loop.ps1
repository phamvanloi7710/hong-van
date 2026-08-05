param(
    [Parameter(Mandatory = $false)]
    [string]$ProjectRoot = "D:\www\HongVan"
)
$ErrorActionPreference = "Stop"
$statePath = Join-Path $ProjectRoot "prompts\hongvan-master-v2\state\STATE.json"
$queuePath = Join-Path $ProjectRoot "prompts\hongvan-master-v2\generated\QUEUE.json"
$state = Get-Content -LiteralPath $statePath -Raw -Encoding UTF8 | ConvertFrom-Json
$queue = Get-Content -LiteralPath $queuePath -Raw -Encoding UTF8 | ConvertFrom-Json
foreach ($id in @("T235","T236","T237","T238","T239")) {
    $state.tasks.($id).status = "PENDING"
    $state.tasks.($id).started_at = $null
    $state.tasks.($id).completed_at = $null
    $state.tasks.($id).base_head = $null
    $state.tasks.($id).result_head = $null
    $state.tasks.($id).summary = $null
    $state.tasks.($id).blocker = $null
}
$state.current_task = $null
$state.audit_round = [int]$state.audit_round + 1
$state.audit_recheck_required = $true
$state.generated_queue_status = "COMPLETED"
$queue.status = "COMPLETED"
$state | ConvertTo-Json -Depth 12 | Set-Content -LiteralPath $statePath -Encoding UTF8
$queue | ConvertTo-Json -Depth 12 | Set-Content -LiteralPath $queuePath -Encoding UTF8
Write-Host "Audit loop reset to T235."
