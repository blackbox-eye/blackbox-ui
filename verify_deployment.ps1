# ═══════════════════════════════════════════════════════════════════════════════
# Deployment Verification Script for blackbox.codes
# ═══════════════════════════════════════════════════════════════════════════════

Write-Host "════════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "BLACKBOX.CODES - Deployment Verification" -ForegroundColor Yellow
Write-Host "════════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Configuration
$SITE_URL = "https://blackbox.codes"
$SSH_KEY = "$HOME\.ssh\nexus-v5-key"
$SSH_HOST = "blackowu@server702.web-hosting.com"
$REMOTE_PATH = "/home/blackowu/public_html"

# ═══════════════════════════════════════════════════════════════════════════════
# STEP 1: Check if db.php contains new error handling
# ═══════════════════════════════════════════════════════════════════════════════

Write-Host "📋 STEP 1: Checking db.php deployment status..." -ForegroundColor Green
Write-Host ""

$checkDbScript = @"
if grep -q 'BBX_DB_CONNECTED' $REMOTE_PATH/db.php 2>/dev/null; then
    echo 'DB_PHP_UPDATED=YES'
else
    echo 'DB_PHP_UPDATED=NO'
fi
"@

try {
  $result = ssh -i $SSH_KEY $SSH_HOST $checkDbScript 2>&1

  if ($result -match "DB_PHP_UPDATED=YES") {
    Write-Host "✅ db.php is updated with BBX_DB_CONNECTED error handling" -ForegroundColor Green
    $dbPhpStatus = "UPDATED"
  }
  elseif ($result -match "DB_PHP_UPDATED=NO") {
    Write-Host "⚠️  db.php does NOT contain BBX_DB_CONNECTED constants" -ForegroundColor Yellow
    Write-Host "   Action required: Upload db.php to production" -ForegroundColor Yellow
    $dbPhpStatus = "NEEDS_UPDATE"
  }
  else {
    Write-Host "❌ Unable to verify db.php status" -ForegroundColor Red
    Write-Host "   SSH Response: $result" -ForegroundColor Red
    $dbPhpStatus = "UNKNOWN"
  }
}
catch {
  Write-Host "❌ SSH connection failed: $($_.Exception.Message)" -ForegroundColor Red
  $dbPhpStatus = "SSH_FAILED"
}

Write-Host ""

# ═══════════════════════════════════════════════════════════════════════════════
# STEP 2: Check database connection from server
# ═══════════════════════════════════════════════════════════════════════════════

Write-Host "📋 STEP 2: Testing MySQL database connection..." -ForegroundColor Green
Write-Host ""

# Use environment variable DB_PASSWORD for security. Do NOT hardcode the DB password in repo.
$dbPass = $env:DB_PASSWORD
if (-not $dbPass -or $dbPass.Trim() -eq "") {
  Write-Host "❌ DB_PASSWORD environment variable is not set. Set it before running this script." -ForegroundColor Red
  Write-Host "   Example (PowerShell):`n     $env:DB_PASSWORD = 'your_db_password'`n     ./verify_deployment.ps1" -ForegroundColor Yellow
  $dbConnectionStatus = "UNKNOWN"
}
else {
  $escapedPass = $dbPass -replace "'", "'\\''"
  $checkDbConnectionScript = @"
mysql -u blackowu_bbx_user -p'$escapedPass' -h localhost blackowu_blackbox -e 'SELECT 1;' 2>&1
if [ \$? -eq 0 ]; then
    echo 'DB_CONNECTION=SUCCESS'
else
    echo 'DB_CONNECTION=FAILED'
fi
"@

  try {
    $result = ssh -i $SSH_KEY $SSH_HOST $checkDbConnectionScript 2>&1

    if ($result -match "DB_CONNECTION=SUCCESS") {
      Write-Host "✅ MySQL connection successful" -ForegroundColor Green
      $dbConnectionStatus = "CONNECTED"
    }
    elseif ($result -match "DB_CONNECTION=FAILED") {
      Write-Host "❌ MySQL connection FAILED" -ForegroundColor Red
      Write-Host "   Check credentials, database name, and user privileges" -ForegroundColor Yellow
      $dbConnectionStatus = "FAILED"
    }
    else {
      Write-Host "⚠️  Unable to test database connection" -ForegroundColor Yellow
      Write-Host "   Response: $result" -ForegroundColor Gray
      $dbConnectionStatus = "UNKNOWN"
    }
  }
  catch {
    Write-Host "❌ SSH command failed: $($_.Exception.Message)" -ForegroundColor Red
    $dbConnectionStatus = "SSH_FAILED"
  }
}

Write-Host ""

# ═══════════════════════════════════════════════════════════════════════════════
# STEP 3: Check if required tables exist
# ═══════════════════════════════════════════════════════════════════════════════

Write-Host "📋 STEP 3: Checking database tables..." -ForegroundColor Green
Write-Host ""

if ($dbConnectionStatus -eq "CONNECTED") {
  $checkTablesScript = @"
mysql -u blackowu_bbx_user -p'$escapedPass' -h localhost blackowu_blackbox -e 'SHOW TABLES;' 2>&1 | grep -E 'faq_items|blog_posts' | wc -l
"@

  try {
    $result = ssh -i $SSH_KEY $SSH_HOST $checkTablesScript 2>&1
    $tableCount = [int]$result.Trim()

    if ($tableCount -eq 2) {
      Write-Host "✅ Both faq_items and blog_posts tables exist" -ForegroundColor Green
      $tablesStatus = "EXISTS"
    }
    elseif ($tableCount -eq 1) {
      Write-Host "⚠️  Only 1 of 2 required tables exists" -ForegroundColor Yellow
      $tablesStatus = "PARTIAL"
    }
    else {
      Write-Host "❌ Required tables (faq_items, blog_posts) do NOT exist" -ForegroundColor Red
      Write-Host "   This is why FAQ/Blog show error fallback UI" -ForegroundColor Yellow
      $tablesStatus = "MISSING"
    }
  }
  catch {
    Write-Host "❌ Unable to check tables: $($_.Exception.Message)" -ForegroundColor Red
    $tablesStatus = "SSH_FAILED"
  }
}
else {
  Write-Host "⚠️ Skipping table check because DB connectivity could not be verified." -ForegroundColor Yellow
  $tablesStatus = "SKIPPED"
}

Write-Host ""

# ═══════════════════════════════════════════════════════════════════════════════
# STEP 4: Test live site HTTP endpoints
# ═══════════════════════════════════════════════════════════════════════════════

Write-Host "📋 STEP 4: Testing live site endpoints..." -ForegroundColor Green
Write-Host ""

$endpoints = @(
  @{Name = "Home"; Url = "$SITE_URL/" },
  @{Name = "FAQ"; Url = "$SITE_URL/faq.php" },
  @{Name = "Blog"; Url = "$SITE_URL/blog.php" },
  @{Name = "Marketing CSS"; Url = "$SITE_URL/assets/css/marketing.css" },
  @{Name = "Site JS"; Url = "$SITE_URL/assets/js/site.min.js" }
)

foreach ($endpoint in $endpoints) {
  try {
    $response = Invoke-WebRequest -Uri $endpoint.Url -UseBasicParsing -TimeoutSec 10 -ErrorAction Stop
    $status = $response.StatusCode
    $size = $response.Content.Length

    if ($status -eq 200) {
      Write-Host "✅ $($endpoint.Name): HTTP $status (${size} bytes)" -ForegroundColor Green
    }
    else {
      Write-Host "⚠️  $($endpoint.Name): HTTP $status" -ForegroundColor Yellow
    }
  }
  catch {
    $statusCode = $_.Exception.Response.StatusCode.Value__
    Write-Host "❌ $($endpoint.Name): HTTP $statusCode or timeout" -ForegroundColor Red
  }
}

Write-Host ""

# ═══════════════════════════════════════════════════════════════════════════════
# STEP 5: Check site.min.js file size (verify not corrupted)
# ═══════════════════════════════════════════════════════════════════════════════

Write-Host "📋 STEP 5: Verifying site.min.js integrity..." -ForegroundColor Green
Write-Host ""

$checkJsScript = @"
stat -c %s $REMOTE_PATH/assets/js/site.min.js 2>/dev/null || stat -f %z $REMOTE_PATH/assets/js/site.min.js 2>/dev/null
"@

try {
  $result = ssh -i $SSH_KEY $SSH_HOST $checkJsScript 2>&1
  $fileSize = [int]$result.Trim()

  if ($fileSize -gt 18000 -and $fileSize -lt 19000) {
    Write-Host "✅ site.min.js is correct size: $fileSize bytes (expected ~18,200)" -ForegroundColor Green
    $jsStatus = "VALID"
  }
  elseif ($fileSize -eq 14) {
    Write-Host "❌ site.min.js is CORRUPTED (14 bytes)" -ForegroundColor Red
    Write-Host "   This will cause content visibility issues" -ForegroundColor Yellow
    $jsStatus = "CORRUPTED"
  }
  else {
    Write-Host "⚠️  site.min.js size is unexpected: $fileSize bytes" -ForegroundColor Yellow
    $jsStatus = "UNEXPECTED_SIZE"
  }
}
catch {
  Write-Host "❌ Unable to check file size: $($_.Exception.Message)" -ForegroundColor Red
  $jsStatus = "CHECK_FAILED"
}

Write-Host ""

# ═══════════════════════════════════════════════════════════════════════════════
# SUMMARY REPORT
# ═══════════════════════════════════════════════════════════════════════════════

Write-Host "════════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "VERIFICATION SUMMARY" -ForegroundColor Yellow
Write-Host "════════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

Write-Host "📊 Status Overview:" -ForegroundColor White
Write-Host "   db.php deployment: $dbPhpStatus" -ForegroundColor $(if ($dbPhpStatus -eq "UPDATED") { "Green" }else { "Yellow" })
Write-Host "   MySQL connection: $dbConnectionStatus" -ForegroundColor $(if ($dbConnectionStatus -eq "CONNECTED") { "Green" }else { "Red" })
Write-Host "   Database tables: $tablesStatus" -ForegroundColor $(if ($tablesStatus -eq "EXISTS") { "Green" }else { "Red" })
Write-Host "   site.min.js integrity: $jsStatus" -ForegroundColor $(if ($jsStatus -eq "VALID") { "Green" }else { "Yellow" })
Write-Host ""

# ═══════════════════════════════════════════════════════════════════════════════
# REQUIRED ACTIONS
# ═══════════════════════════════════════════════════════════════════════════════

Write-Host "🔧 Required Actions:" -ForegroundColor Yellow
Write-Host ""

if ($dbPhpStatus -ne "UPDATED") {
  Write-Host "   1. Upload db.php to production:" -ForegroundColor White
  Write-Host "      scp -i `"$SSH_KEY`" db.php $SSH_HOST`:$REMOTE_PATH/" -ForegroundColor Cyan
  Write-Host ""
}

if ($dbConnectionStatus -eq "FAILED") {
  Write-Host "   2. Fix MySQL connection:" -ForegroundColor White
  Write-Host "      - Check cPanel → MySQL Databases" -ForegroundColor Gray
  Write-Host "      - Verify database 'blackowu_blackbox' exists" -ForegroundColor Gray
  Write-Host "      - Verify user 'blackowu_bbx_user' has privileges" -ForegroundColor Gray
  Write-Host "      - Test credentials in cPanel phpMyAdmin" -ForegroundColor Gray
  Write-Host ""
}

if ($tablesStatus -ne "EXISTS") {
  Write-Host "   3. Create missing database tables:" -ForegroundColor White
  Write-Host "      - Import schema from db/schema/faq_items.sql" -ForegroundColor Gray
  Write-Host "      - Import schema from db/schema/blog_posts.sql" -ForegroundColor Gray
  Write-Host "      OR accept graceful fallback UI (current behavior)" -ForegroundColor Gray
  Write-Host ""
}

Write-Host "   4. Clear Cloudflare cache (CRITICAL):" -ForegroundColor White
Write-Host "      - Go to: https://dash.cloudflare.com" -ForegroundColor Cyan
Write-Host "      - Select 'blackbox.codes' domain" -ForegroundColor Gray
Write-Host "      - Caching → Purge Everything" -ForegroundColor Gray
Write-Host "      OR purge specific files:" -ForegroundColor Gray
Write-Host "        • /assets/css/marketing.css" -ForegroundColor Gray
Write-Host "        • /assets/js/site.min.js" -ForegroundColor Gray
Write-Host ""

Write-Host "   5. Test in browsers after cache clear:" -ForegroundColor White
Write-Host "      - Hard refresh (Ctrl+Shift+R) in Chrome, Brave, Firefox" -ForegroundColor Gray
Write-Host "      - Verify navigation colors (gray-300, not blue/purple)" -ForegroundColor Gray
Write-Host "      - Verify hero section visible" -ForegroundColor Gray
Write-Host "      - Verify all sections below hero visible" -ForegroundColor Gray
Write-Host ""

Write-Host "════════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
