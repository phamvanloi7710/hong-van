[CmdletBinding()]
param()

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$repositoryRoot = Split-Path -Parent $PSScriptRoot
$rootExamplePath = Join-Path $repositoryRoot '.env.example'
$rootEnvPath = Join-Path $repositoryRoot '.env'
$backendExamplePath = Join-Path $repositoryRoot 'BackEnd/.env.example'
$backendEnvPath = Join-Path $repositoryRoot 'BackEnd/.env'
$utf8WithoutBom = New-Object System.Text.UTF8Encoding($false)

function Get-EnvValues {
    param([string]$Path)

    $values = @{}
    if (-not (Test-Path -LiteralPath $Path)) {
        return $values
    }

    foreach ($line in Get-Content -LiteralPath $Path) {
        if ($line -match '^([A-Za-z_][A-Za-z0-9_]*)=(.*)$') {
            $values[$matches[1]] = $matches[2].Trim('"')
        }
    }

    return $values
}

function Get-FirstValue {
    param(
        [hashtable[]]$Sources,
        [string]$Name
    )

    foreach ($source in $Sources) {
        if ($source.ContainsKey($Name) -and -not [string]::IsNullOrWhiteSpace([string]$source[$Name])) {
            return [string]$source[$Name]
        }
    }

    return $null
}

function New-RandomHex {
    param([int]$ByteCount = 24)

    $bytes = New-Object byte[] $ByteCount
    $generator = [System.Security.Cryptography.RandomNumberGenerator]::Create()
    try {
        $generator.GetBytes($bytes)
    }
    finally {
        $generator.Dispose()
    }

    return ([BitConverter]::ToString($bytes)).Replace('-', '').ToLowerInvariant()
}

function New-AppKey {
    $bytes = New-Object byte[] 32
    $generator = [System.Security.Cryptography.RandomNumberGenerator]::Create()
    try {
        $generator.GetBytes($bytes)
    }
    finally {
        $generator.Dispose()
    }

    return 'base64:' + [Convert]::ToBase64String($bytes)
}

function Set-EnvValue {
    param(
        [string]$Content,
        [string]$Name,
        [string]$Value
    )

    $pattern = '(?m)^' + [Regex]::Escape($Name) + '=.*$'
    $line = $Name + '=' + $Value

    if ([Regex]::IsMatch($Content, $pattern)) {
        return [Regex]::Replace($Content, $pattern, [System.Text.RegularExpressions.MatchEvaluator]{ param($match) $line })
    }

    return $Content.TrimEnd() + [Environment]::NewLine + $line + [Environment]::NewLine
}

function Write-EnvFile {
    param(
        [string]$ExamplePath,
        [string]$DestinationPath,
        [hashtable]$Values
    )

    $content = [IO.File]::ReadAllText($ExamplePath)
    foreach ($name in $Values.Keys) {
        $content = Set-EnvValue -Content $content -Name $name -Value ([string]$Values[$name])
    }

    [IO.File]::WriteAllText($DestinationPath, $content, $utf8WithoutBom)
}

$rootValues = Get-EnvValues -Path $rootEnvPath
$backendValues = Get-EnvValues -Path $backendEnvPath
$sources = @($rootValues, $backendValues)

$dbPassword = Get-FirstValue -Sources $sources -Name 'DB_PASSWORD'
if ($null -eq $dbPassword) {
    $dbPassword = New-RandomHex
}

$redisPassword = Get-FirstValue -Sources $sources -Name 'REDIS_PASSWORD'
if ($null -eq $redisPassword) {
    $redisPassword = New-RandomHex
}

$mysqlRootPassword = Get-FirstValue -Sources @($rootValues) -Name 'MYSQL_ROOT_PASSWORD'
if ($null -eq $mysqlRootPassword) {
    $mysqlRootPassword = New-RandomHex
}

if (-not (Test-Path -LiteralPath $rootEnvPath)) {
    Write-EnvFile -ExamplePath $rootExamplePath -DestinationPath $rootEnvPath -Values @{
        COMPOSE_PROJECT_NAME = 'hongvan'
        DOCKER_DOMAIN = 'hongvan.local'
        DOCKER_PMA_DOMAIN = 'hongvan-pma.local'
        MYSQL_ROOT_PASSWORD = $mysqlRootPassword
        DB_DATABASE = 'hongvan_platform'
        DB_USERNAME = 'hongvan'
        DB_PASSWORD = $dbPassword
        REDIS_PASSWORD = $redisPassword
    }
    Write-Host 'Created ignored root .env for Docker Compose.'
}

if (-not (Test-Path -LiteralPath $backendEnvPath)) {
    Write-EnvFile -ExamplePath $backendExamplePath -DestinationPath $backendEnvPath -Values @{
        APP_KEY = (New-AppKey)
        APP_URL = 'http://hongvan.local'
        TRUSTED_HOSTS = 'hongvan.local,localhost,127.0.0.1'
        TRUSTED_PROXIES = '172.16.0.0/12'
        DB_HOST = 'mysql'
        DB_PORT = '3306'
        DB_DATABASE = 'hongvan_platform'
        DB_USERNAME = 'hongvan'
        DB_PASSWORD = $dbPassword
        REDIS_HOST = 'redis'
        REDIS_PASSWORD = $redisPassword
        REDIS_PORT = '6379'
    }
    Write-Host 'Created ignored BackEnd/.env with a generated APP_KEY.'
}

$resolvedRootValues = Get-EnvValues -Path $rootEnvPath
$resolvedBackendValues = Get-EnvValues -Path $backendEnvPath
foreach ($requiredName in @('MYSQL_ROOT_PASSWORD', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'REDIS_PASSWORD')) {
    if (-not $resolvedRootValues.ContainsKey($requiredName) -or [string]::IsNullOrWhiteSpace([string]$resolvedRootValues[$requiredName])) {
        throw "Missing required $requiredName in $rootEnvPath."
    }
}

foreach ($requiredName in @('APP_KEY', 'DB_PASSWORD', 'REDIS_PASSWORD')) {
    if (-not $resolvedBackendValues.ContainsKey($requiredName) -or [string]::IsNullOrWhiteSpace([string]$resolvedBackendValues[$requiredName])) {
        throw "Missing required $requiredName in $backendEnvPath."
    }
}

if ($resolvedRootValues['DB_PASSWORD'] -ne $resolvedBackendValues['DB_PASSWORD']) {
    throw 'DB_PASSWORD differs between root .env and BackEnd/.env.'
}

if ($resolvedRootValues['REDIS_PASSWORD'] -ne $resolvedBackendValues['REDIS_PASSWORD']) {
    throw 'REDIS_PASSWORD differs between root .env and BackEnd/.env.'
}

Write-Host 'Docker environment files are ready; secret values were not printed.'
