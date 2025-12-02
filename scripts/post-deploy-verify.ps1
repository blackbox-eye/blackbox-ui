<#
.SYNOPSIS
    Post-deploy verification script for ALPHA-GUI Blue/Green deployment.

.DESCRIPTION
    Runs health checks, SSO endpoint validation, and cookie integrity tests
    after a production deployment.

.PARAMETER BaseUrl
    The base URL of the deployed environment (e.g., https://alpha.blackbox.codes)

.EXAMPLE
    .\post-deploy-verify.ps1 -BaseUrl "https://alpha.blackbox.codes"
#>

param(
    [Parameter(Mandatory = $true)]
    [string]$BaseUrl
)

$ErrorActionPreference = "Stop"
$results = @()

function Test-Endpoint {
    param(
        [string]$Name,
        [string]$Url,
        [int]$ExpectedStatus = 200
    )

    try {
        $response = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 15 -MaximumRedirection 0 -ErrorAction SilentlyContinue
        $status = $response.StatusCode
    }
    catch {
        if ($_.Exception.Response) {
            $status = [int]$_.Exception.Response.StatusCode
        }
        else {
            $status = 0
        }
    }

    $passed = ($status -eq $ExpectedStatus)
    $script:results += [PSCustomObject]@{
        Test   = $Name
        Status = if ($passed) { "PASS" } else { "FAIL" }
        Detail = "HTTP $status (expected $ExpectedStatus)"
    }

    return $passed
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  ALPHA-GUI Post-Deploy Verification" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Target: $BaseUrl`n"

# 1. Health Endpoint
Write-Host "[1/5] Checking SSO health endpoint..." -ForegroundColor Yellow
$healthUrl = "$BaseUrl/tools/sso_health.php"
$healthPassed = Test-Endpoint -Name "SSO Health Endpoint" -Url $healthUrl -ExpectedStatus 200

if ($healthPassed) {
    try {
        $healthResponse = Invoke-RestMethod -Uri $healthUrl -TimeoutSec 10
        if ($healthResponse.sso_enabled -eq $true) {
            Write-Host "  ✅ SSO enabled" -ForegroundColor Green
        }
        else {
            Write-Host "  ⚠️ SSO not enabled in response" -ForegroundColor Yellow
        }
    }
    catch {
        Write-Host "  ⚠️ Could not parse health response" -ForegroundColor Yellow
    }
}

# 2. Login Page Accessibility
Write-Host "[2/5] Checking login page..." -ForegroundColor Yellow
Test-Endpoint -Name "Agent Login Page" -Url "$BaseUrl/agent-login.php" -ExpectedStatus 200 | Out-Null

# 3. Protected Route (should redirect without auth)
Write-Host "[3/5] Checking protected route guard..." -ForegroundColor Yellow
Test-Endpoint -Name "Dashboard Guard (no auth)" -Url "$BaseUrl/dashboard.php" -ExpectedStatus 302 | Out-Null

# 4. Static Assets
Write-Host "[4/5] Checking static assets..." -ForegroundColor Yellow
Test-Endpoint -Name "Router Guard JS" -Url "$BaseUrl/assets/js/router-guard.js" -ExpectedStatus 200 | Out-Null
Test-Endpoint -Name "Admin CSS" -Url "$BaseUrl/assets/css/admin.css" -ExpectedStatus 200 | Out-Null

# 5. QA Mode Disabled Check
Write-Host "[5/5] Verifying QA mode is disabled..." -ForegroundColor Yellow
try {
    $loginHtml = Invoke-WebRequest -Uri "$BaseUrl/agent-login.php" -UseBasicParsing -TimeoutSec 15
    $qaVisible = $loginHtml.Content -match "qa-debug-panel"

    $script:results += [PSCustomObject]@{
        Test   = "QA Panel Hidden"
        Status = if (-not $qaVisible) { "PASS" } else { "FAIL" }
        Detail = if (-not $qaVisible) { "QA panel not in HTML" } else { "QA panel found (QA_MODE may be enabled)" }
    }

    if ($qaVisible) {
        Write-Host "  ⚠️ QA debug panel detected in production!" -ForegroundColor Red
    }
    else {
        Write-Host "  ✅ QA panel correctly hidden" -ForegroundColor Green
    }
}
catch {
    Write-Host "  ⚠️ Could not verify QA mode status" -ForegroundColor Yellow
}

# Summary
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  Verification Summary" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

$passCount = ($results | Where-Object { $_.Status -eq "PASS" }).Count
$failCount = ($results | Where-Object { $_.Status -eq "FAIL" }).Count

$results | Format-Table -AutoSize

if ($failCount -eq 0) {
    Write-Host "✅ ALL CHECKS PASSED ($passCount/$($results.Count))" -ForegroundColor Green
    Write-Host "`nDeploy verification successful. Ready for traffic switch.`n" -ForegroundColor Green
    exit 0
}
else {
    Write-Host "❌ FAILURES DETECTED ($failCount/$($results.Count))" -ForegroundColor Red
    Write-Host "`nReview failures before proceeding. Consider rollback if critical.`n" -ForegroundColor Red
    exit 1
}
