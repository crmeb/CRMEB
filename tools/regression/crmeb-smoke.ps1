param(
    [string]$BaseUrl = "http://localhost:8011",
    [string]$ComposeFile = "help/docker/docker-compose.yml",
    [string]$ComposeProject = "crmeb",
    [int]$ProductId = 1,
    [int]$TimeoutSeconds = 30,
    [switch]$SkipTokenTests
)

Set-StrictMode -Version 2.0
$ErrorActionPreference = "Stop"

$script:Results = New-Object System.Collections.ArrayList
$script:RepoRoot = Resolve-Path (Join-Path $PSScriptRoot "..\..")
if ([System.IO.Path]::IsPathRooted($ComposeFile)) {
    $script:ComposePath = $ComposeFile
} else {
    $script:ComposePath = Join-Path $script:RepoRoot $ComposeFile
}

function Add-SmokeResult {
    param(
        [string]$Name,
        [ValidateSet("PASS", "FAIL", "SKIP")]
        [string]$Result,
        [string]$Detail,
        [int]$ElapsedMs,
        [bool]$Required = $true
    )

    [void]$script:Results.Add([PSCustomObject]@{
        Name = $Name
        Result = $Result
        Detail = $Detail
        ElapsedMs = $ElapsedMs
        Required = $Required
    })
}

function Limit-Detail {
    param([string]$Text, [int]$MaxLength = 180)

    if ($null -eq $Text) {
        return ""
    }

    $clean = ($Text -replace "\s+", " ").Trim()
    if ($clean.Length -le $MaxLength) {
        return $clean
    }

    return $clean.Substring(0, $MaxLength) + "..."
}

function Invoke-DockerExec {
    param(
        [Parameter(Mandatory = $true)]
        [string[]]$Arguments
    )

    $output = & docker @Arguments 2>&1
    $exitCode = $LASTEXITCODE

    return [PSCustomObject]@{
        ExitCode = $exitCode
        Output = (($output | Out-String).Trim())
    }
}

function Invoke-Check {
    param(
        [string]$Name,
        [scriptblock]$ScriptBlock,
        [bool]$Required = $true
    )

    $sw = [System.Diagnostics.Stopwatch]::StartNew()
    try {
        $outcome = & $ScriptBlock
        $sw.Stop()

        if ($null -eq $outcome) {
            Add-SmokeResult -Name $Name -Result "PASS" -Detail "ok" -ElapsedMs ([int]$sw.ElapsedMilliseconds) -Required $Required
            return
        }

        if ($outcome.PSObject.Properties.Name -contains "Result") {
            Add-SmokeResult -Name $Name -Result $outcome.Result -Detail $outcome.Detail -ElapsedMs ([int]$sw.ElapsedMilliseconds) -Required $Required
        } else {
            Add-SmokeResult -Name $Name -Result "PASS" -Detail ([string]$outcome) -ElapsedMs ([int]$sw.ElapsedMilliseconds) -Required $Required
        }
    } catch {
        $sw.Stop()
        Add-SmokeResult -Name $Name -Result "FAIL" -Detail (Limit-Detail $_.Exception.Message) -ElapsedMs ([int]$sw.ElapsedMilliseconds) -Required $Required
    }
}

function New-Outcome {
    param(
        [ValidateSet("PASS", "FAIL", "SKIP")]
        [string]$Result,
        [string]$Detail
    )

    return [PSCustomObject]@{
        Result = $Result
        Detail = $Detail
    }
}

function Invoke-HttpRequest {
    param([string]$Path)

    $url = $BaseUrl.TrimEnd("/") + "/" + $Path.TrimStart("/")
    $requestParams = @{
        Uri = $url
        Method = "GET"
        TimeoutSec = $TimeoutSeconds
        ErrorAction = "Stop"
    }

    if ($PSVersionTable.PSVersion.Major -lt 6) {
        $requestParams["UseBasicParsing"] = $true
    }

    $sw = [System.Diagnostics.Stopwatch]::StartNew()
    try {
        $response = Invoke-WebRequest @requestParams
        $sw.Stop()
        return [PSCustomObject]@{
            Url = $url
            StatusCode = [int]$response.StatusCode
            Body = [string]$response.Content
            ElapsedMs = [int]$sw.ElapsedMilliseconds
        }
    } catch {
        $sw.Stop()
        $statusCode = 0
        $body = ""

        if ($_.Exception.Response) {
            $rawResponse = $_.Exception.Response
            if ($rawResponse.StatusCode) {
                $statusCode = [int]$rawResponse.StatusCode
            }

            if ($_.ErrorDetails -and $_.ErrorDetails.Message) {
                $body = [string]$_.ErrorDetails.Message
            } elseif ($rawResponse.GetResponseStream) {
                $stream = $rawResponse.GetResponseStream()
                if ($stream) {
                    $reader = New-Object System.IO.StreamReader($stream)
                    $body = $reader.ReadToEnd()
                }
            }
        }

        if ($statusCode -eq 0) {
            throw
        }

        return [PSCustomObject]@{
            Url = $url
            StatusCode = $statusCode
            Body = $body
            ElapsedMs = [int]$sw.ElapsedMilliseconds
        }
    }
}

function ConvertFrom-JsonSafe {
    param([string]$Body)

    if ([string]::IsNullOrWhiteSpace($Body)) {
        return $null
    }

    try {
        return $Body | ConvertFrom-Json
    } catch {
        return $null
    }
}

function Get-BusinessStatus {
    param($Json)

    if ($null -eq $Json) {
        return $null
    }

    foreach ($name in @("status", "code")) {
        if ($Json.PSObject.Properties.Name -contains $name) {
            return $Json.$name
        }
    }

    return $null
}

function Get-BusinessMessage {
    param($Json)

    if ($null -eq $Json) {
        return ""
    }

    foreach ($name in @("msg", "message")) {
        if ($Json.PSObject.Properties.Name -contains $name) {
            return [string]$Json.$name
        }
    }

    return ""
}

function Format-HttpDetail {
    param(
        [int]$HttpStatus,
        $BusinessStatus,
        [string]$BusinessMessage
    )

    $parts = @("HTTP=$HttpStatus")
    if ($null -ne $BusinessStatus) {
        $parts += "status=$BusinessStatus"
    }
    if (-not [string]::IsNullOrWhiteSpace($BusinessMessage)) {
        $parts += ("msg=" + (Limit-Detail $BusinessMessage 80))
    }

    return ($parts -join " ")
}

function Invoke-HttpCheck {
    param(
        [string]$Name,
        [string]$Path,
        [scriptblock]$Validator,
        [bool]$Required = $true
    )

    try {
        $response = Invoke-HttpRequest -Path $Path
        $json = ConvertFrom-JsonSafe -Body $response.Body
        $businessStatus = Get-BusinessStatus -Json $json
        $businessMessage = Get-BusinessMessage -Json $json
        $detail = Format-HttpDetail -HttpStatus $response.StatusCode -BusinessStatus $businessStatus -BusinessMessage $businessMessage

        if ($response.StatusCode -eq 500) {
            Add-SmokeResult -Name $Name -Result "FAIL" -Detail $detail -ElapsedMs $response.ElapsedMs -Required $Required
            return
        }

        $validation = & $Validator $response $json $businessStatus $businessMessage
        if ($validation.Result -eq "PASS") {
            Add-SmokeResult -Name $Name -Result "PASS" -Detail $detail -ElapsedMs $response.ElapsedMs -Required $Required
        } elseif ($validation.Result -eq "SKIP") {
            Add-SmokeResult -Name $Name -Result "SKIP" -Detail $validation.Detail -ElapsedMs $response.ElapsedMs -Required $Required
        } else {
            $failDetail = $detail
            if (-not [string]::IsNullOrWhiteSpace($validation.Detail)) {
                $failDetail = $detail + " " + $validation.Detail
            }
            Add-SmokeResult -Name $Name -Result "FAIL" -Detail $failDetail -ElapsedMs $response.ElapsedMs -Required $Required
        }
    } catch {
        Add-SmokeResult -Name $Name -Result "FAIL" -Detail (Limit-Detail $_.Exception.Message) -ElapsedMs 0 -Required $Required
    }
}

Push-Location $script:RepoRoot
try {
    Invoke-Check -Name "Compose file exists" -ScriptBlock {
        if (-not (Test-Path -LiteralPath $script:ComposePath)) {
            throw "Compose file not found: $script:ComposePath"
        }
        "found"
    }

    Invoke-Check -Name "Docker compose ps" -ScriptBlock {
        $result = Invoke-DockerExec -Arguments @("compose", "-p", $ComposeProject, "-f", $script:ComposePath, "ps")
        if ($result.ExitCode -ne 0) {
            throw (Limit-Detail $result.Output)
        }
        "compose ps ok"
    }

    foreach ($containerName in @("crmeb_mysql", "crmeb_redis", "crmeb_php", "crmeb_nginx")) {
        Invoke-Check -Name "Container $containerName" -ScriptBlock {
            $result = Invoke-DockerExec -Arguments @("inspect", "-f", "{{.State.Status}}", $containerName)
            if ($result.ExitCode -ne 0) {
                throw (Limit-Detail $result.Output)
            }
            $status = $result.Output.Trim()
            if ($status -ne "running") {
                throw "container status=$status"
            }
            "running"
        }
    }

    Invoke-Check -Name "PHP version" -ScriptBlock {
        $result = Invoke-DockerExec -Arguments @("exec", "crmeb_php", "php", "-v")
        if ($result.ExitCode -ne 0) {
            throw (Limit-Detail $result.Output)
        }
        $firstLine = ($result.Output -split "`n" | Select-Object -First 1).Trim()
        if ($firstLine -notmatch "PHP 7\.4\.") {
            throw "expected PHP 7.4.x, got: $firstLine"
        }
        $firstLine
    }

    Invoke-Check -Name "PHP OPcache" -Required $false -ScriptBlock {
        $result = Invoke-DockerExec -Arguments @("exec", "crmeb_php", "php", "-m")
        if ($result.ExitCode -ne 0) {
            throw (Limit-Detail $result.Output)
        }
        if ($result.Output -notmatch "Zend OPcache") {
            return New-Outcome -Result "SKIP" -Detail "Zend OPcache not loaded; non-blocking until docker performance baseline lands"
        }
        "Zend OPcache loaded"
    }

    Invoke-Check -Name "ThinkPHP CLI" -ScriptBlock {
        $result = Invoke-DockerExec -Arguments @("exec", "crmeb_php", "php", "think", "list")
        if ($result.ExitCode -ne 0) {
            throw (Limit-Detail $result.Output)
        }
        "php think list exit 0"
    }

    Invoke-Check -Name "MySQL PDO SELECT 1" -ScriptBlock {
        $php = @'
try {
    $host = getenv('MYSQL_HOST_IP') ?: 'crmeb_mysql';
    $port = getenv('MYSQL_PORT') ?: '3306';
    $db = getenv('MYSQL_DATABASE') ?: 'crmeb';
    $user = getenv('MYSQL_USER') ?: '';
    $pass = getenv('MYSQL_PASSWORD') ?: '';
    $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $db . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $user, $pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5));
    $value = $pdo->query('SELECT 1')->fetchColumn();
    if ((string)$value === '1') {
        echo 'OK';
        exit(0);
    }
    fwrite(STDERR, 'PDO check returned unexpected result');
    exit(1);
} catch (Throwable $e) {
    fwrite(STDERR, 'PDO check failed');
    exit(1);
}
'@
        $result = Invoke-DockerExec -Arguments @("exec", "crmeb_php", "php", "-r", $php)
        if ($result.ExitCode -ne 0) {
            throw (Limit-Detail $result.Output)
        }
        if ($result.Output.Trim() -ne "OK") {
            throw "unexpected PDO result"
        }
        "SELECT 1 ok"
    }

    Invoke-Check -Name "Redis auth ping" -ScriptBlock {
        $php = @'
try {
    $host = getenv('REDIS_HOST_IP') ?: 'crmeb_redis';
    $port = (int)(getenv('REDIS_PORT') ?: 6379);
    $db = (int)(getenv('REDIS_DATABASE') ?: 0);
    $pass = getenv('REDIS_PASSWORD') ?: '';
    $redis = new Redis();
    $redis->connect($host, $port, 5);
    if ($pass !== '') {
        $redis->auth($pass);
    }
    $redis->select($db);
    $pong = $redis->ping();
    if ($pong === true || $pong === '+PONG' || strtoupper((string)$pong) === 'PONG' || strtoupper((string)$pong) === '+PONG') {
        echo 'OK';
        exit(0);
    }
    fwrite(STDERR, 'Redis ping returned unexpected result');
    exit(1);
} catch (Throwable $e) {
    fwrite(STDERR, 'Redis ping failed');
    exit(1);
}
'@
        $result = Invoke-DockerExec -Arguments @("exec", "crmeb_php", "php", "-r", $php)
        if ($result.ExitCode -ne 0) {
            throw (Limit-Detail $result.Output)
        }
        if ($result.Output.Trim() -ne "OK") {
            throw "unexpected Redis result"
        }
        "PING ok"
    }

    $httpOk = {
        param($Response, $Json, $BusinessStatus, $BusinessMessage)
        if ($Response.StatusCode -ge 200 -and $Response.StatusCode -lt 400) {
            return New-Outcome -Result "PASS" -Detail "ok"
        }
        return New-Outcome -Result "FAIL" -Detail "expected HTTP 2xx/3xx"
    }

    $business200 = {
        param($Response, $Json, $BusinessStatus, $BusinessMessage)
        if ([string]$BusinessStatus -eq "200") {
            return New-Outcome -Result "PASS" -Detail "business status 200"
        }
        return New-Outcome -Result "FAIL" -Detail "expected business status 200"
    }

    Invoke-HttpCheck -Name "HTTP /" -Path "/" -Validator $httpOk
    Invoke-HttpCheck -Name "HTTP /admin/" -Path "/admin/" -Validator $httpOk
    Invoke-HttpCheck -Name "HTTP /adminapi/login/info" -Path "/adminapi/login/info" -Validator $business200
    Invoke-HttpCheck -Name "HTTP /api/index" -Path "/api/index" -Validator $business200
    Invoke-HttpCheck -Name "HTTP product detail" -Path "/api/product/detail/$ProductId" -Validator $business200

    Invoke-HttpCheck -Name "HTTP product h5 code" -Path "/api/product/code/$ProductId`?user_type=h5" -Validator {
        param($Response, $Json, $BusinessStatus, $BusinessMessage)
        if ([string]$BusinessStatus -eq "200" -or [string]$BusinessStatus -eq "400") {
            return New-Outcome -Result "PASS" -Detail "business status accepted"
        }
        return New-Outcome -Result "FAIL" -Detail "expected business status 200 or explicit 400"
    }

    Invoke-HttpCheck -Name "HTTP routine_code unauthenticated" -Path "/api/user/routine_code" -Validator {
        param($Response, $Json, $BusinessStatus, $BusinessMessage)
        if ($Response.StatusCode -eq 401) {
            return New-Outcome -Result "PASS" -Detail "HTTP 401"
        }
        if ([string]$BusinessStatus -eq "401" -or [string]$BusinessStatus -eq "410000") {
            return New-Outcome -Result "PASS" -Detail "business login required"
        }
        if ($BusinessMessage -match "login|登录|登陆") {
            return New-Outcome -Result "PASS" -Detail "message login required"
        }
        return New-Outcome -Result "FAIL" -Detail "expected login-required response"
    }

    Invoke-Check -Name "HTTP routine_code token empty-config" -Required $false -ScriptBlock {
        if ($SkipTokenTests) {
            return New-Outcome -Result "SKIP" -Detail "SkipTokenTests was set"
        }
        return New-Outcome -Result "SKIP" -Detail "no safe temporary token source in v1"
    }
} finally {
    Pop-Location
}

$displayResults = $script:Results | Select-Object Name, Result, Detail, ElapsedMs
$displayResults | Format-Table -AutoSize

$requiredFailures = @($script:Results | Where-Object { $_.Required -and $_.Result -eq "FAIL" })
if ($requiredFailures.Count -gt 0) {
    Write-Host "RESULT: FAIL"
    exit 1
}

$optionalWarnings = @($script:Results | Where-Object { -not $_.Required -and $_.Result -ne "PASS" })
if ($optionalWarnings.Count -gt 0) {
    Write-Host "RESULT: PASS WITH WARNINGS"
    exit 0
}

Write-Host "RESULT: PASS"
exit 0
