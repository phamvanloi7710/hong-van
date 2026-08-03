[CmdletBinding()]
param(
    [string]$BaseUrl = 'http://hongvan.local',
    [ValidateRange(1, 120)][int]$TimeoutSeconds = 15
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Net.Http

$baseUri = [Uri]($BaseUrl.TrimEnd('/') + '/')
if ($baseUri.Scheme -notin @('http', 'https')) {
    throw 'BaseUrl must use HTTP or HTTPS.'
}

$checks = @(
    @{ Path = 'health'; Expected = 200 },
    @{ Path = 'api/public/v1/system/ping'; Expected = 200 },
    @{ Path = 'admin/'; Expected = 200 },
    @{ Path = 'api/admin/v1/auth/me'; Expected = 401 }
)

$handler = New-Object System.Net.Http.HttpClientHandler
$handler.AllowAutoRedirect = $false
$client = New-Object System.Net.Http.HttpClient($handler)
$client.Timeout = [TimeSpan]::FromSeconds($TimeoutSeconds)
$client.DefaultRequestHeaders.UserAgent.ParseAdd('HongVan-CI-Smoke/1.0')

try {
    foreach ($check in $checks) {
        $uri = New-Object Uri($baseUri, $check.Path)
        $response = $client.GetAsync($uri).GetAwaiter().GetResult()
        try {
            $status = [int]$response.StatusCode
            if ($status -ne $check.Expected) {
                throw "Smoke check failed for ${uri}: expected $($check.Expected), received $status."
            }
            Write-Host ('[OK] {0} -> {1}' -f $uri, $status)
        }
        finally {
            $response.Dispose()
        }
    }
}
finally {
    $client.Dispose()
    $handler.Dispose()
}

Write-Host 'HTTP smoke checks completed successfully.'
