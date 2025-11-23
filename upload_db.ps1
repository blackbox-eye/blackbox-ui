# ═══════════════════════════════════════════════════════════════════════════════
# Quick Upload db.php to Production (PowerShell)
# ═══════════════════════════════════════════════════════════════════════════════

$SSH_KEY = "$HOME\.ssh\nexus-v5-key"
$SSH_HOST = "blackowu@server702.web-hosting.com"
$REMOTE_PATH = "/home/blackowu/public_html"
$LOCAL_DB_PHP = ".\db.php"

Write-Host "════════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "🚀 Upload db.php to Production" -ForegroundColor Yellow
Write-Host "════════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Check if SSH key exists
if (-not (Test-Path $SSH_KEY)) {
  Write-Host "❌ SSH key not found: $SSH_KEY" -ForegroundColor Red
  Write-Host "   Please verify the key location" -ForegroundColor Yellow
  exit 1
}

# Check if local db.php exists
if (-not (Test-Path $LOCAL_DB_PHP)) {
  Write-Host "❌ Local db.php not found: $LOCAL_DB_PHP" -ForegroundColor Red
  exit 1
}

# Verify db.php contains new error handling
$dbContent = Get-Content $LOCAL_DB_PHP -Raw
if ($dbContent -notmatch "BBX_DB_CONNECTED") {
  Write-Host "⚠️  WARNING: Local db.php does NOT contain BBX_DB_CONNECTED" -ForegroundColor Yellow
  $response = Read-Host "   Are you sure you want to upload this file? (y/n)"
  if ($response -ne "y") {
    Write-Host "❌ Upload cancelled" -ForegroundColor Red
    exit 1
  }
}

Write-Host "📤 Uploading db.php to production..." -ForegroundColor Green
Write-Host "   Source: $LOCAL_DB_PHP" -ForegroundColor Gray
Write-Host "   Target: $SSH_HOST`:$REMOTE_PATH/db.php" -ForegroundColor Gray
Write-Host ""

# Upload file using SCP
try {
  $scpCommand = "scp -i `"$SSH_KEY`" `"$LOCAL_DB_PHP`" $SSH_HOST`:$REMOTE_PATH/db.php"
  Invoke-Expression $scpCommand

  Write-Host "✅ db.php uploaded successfully" -ForegroundColor Green
  Write-Host ""

  # Verify upload
  Write-Host "🔍 Verifying upload..." -ForegroundColor Green
  $verifyCommand = "ssh -i `"$SSH_KEY`" $SSH_HOST `"grep -q 'BBX_DB_CONNECTED' $REMOTE_PATH/db.php && echo 'BBX_FOUND' || echo 'BBX_NOT_FOUND'`""
  $result = Invoke-Expression $verifyCommand

  if ($result -match "BBX_FOUND") {
    Write-Host "✅ BBX_DB_CONNECTED found in production db.php" -ForegroundColor Green
  }
  else {
    Write-Host "❌ BBX_DB_CONNECTED NOT found in production db.php" -ForegroundColor Red
  }

  Write-Host ""
  Write-Host "════════════════════════════════════════════════════════════════" -ForegroundColor Cyan
  Write-Host "✅ UPLOAD COMPLETE" -ForegroundColor Green
  Write-Host "════════════════════════════════════════════════════════════════" -ForegroundColor Cyan
  Write-Host ""
  Write-Host "Next steps:" -ForegroundColor Yellow
  Write-Host "  1. Clear Cloudflare cache" -ForegroundColor White
  Write-Host "  2. Test FAQ/Blog pages: https://blackbox.codes/faq.php" -ForegroundColor White
  Write-Host "  3. Check error logs:" -ForegroundColor White
  Write-Host "     ssh -i `"$SSH_KEY`" $SSH_HOST 'tail -50 $REMOTE_PATH/error_log'" -ForegroundColor Cyan
  Write-Host ""

}
catch {
  Write-Host "❌ Upload failed: $($_.Exception.Message)" -ForegroundColor Red
  exit 1
}
