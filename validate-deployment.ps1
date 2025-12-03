# Quick Deployment Validation Script

Write-Host "`n=== BLACKBOX EYE - DEPLOYMENT VALIDATION ===`n" -ForegroundColor Cyan

# Configuration
$prodUrl = "https://blackbox.codes"
$testDbUrl = "$prodUrl/test-db.php"
$testTablesUrl = "$prodUrl/test-tables.php"

Write-Host "Production URL: $prodUrl`n" -ForegroundColor Yellow

# ============================================
# STEP 1: Database Connection Check
# ============================================
Write-Host "STEP 1: Database Connection Check" -ForegroundColor Green
Write-Host "------------------------------------" -ForegroundColor Gray

Write-Host "`nManual Actions Required:" -ForegroundColor Yellow
Write-Host "1. Upload 'test-db.php' to production root"
Write-Host "2. Visit: $testDbUrl"
Write-Host "3. Expected output: 'BBX_DB_CONNECTED: true'"
Write-Host "4. DELETE test-db.php after verification`n"

$dbCheck = Read-Host "Did database connection succeed? (y/n)"

if ($dbCheck -eq "y") {
  Write-Host "✅ Database connection: PASSED`n" -ForegroundColor Green
  $dbStatus = "PASSED"
}
else {
  Write-Host "❌ Database connection: FAILED`n" -ForegroundColor Red
  $dbError = Read-Host "Enter error message"
  $dbStatus = "FAILED: $dbError"
  Write-Host "⚠️ Fix database connection before continuing!`n" -ForegroundColor Yellow
  exit 1
}

# ============================================
# STEP 2: Database Tables Check
# ============================================
Write-Host "STEP 2: Database Tables Verification" -ForegroundColor Green
Write-Host "-------------------------------------" -ForegroundColor Gray

Write-Host "`nManual Actions Required:" -ForegroundColor Yellow
Write-Host "1. Upload 'test-tables.php' to production root"
Write-Host "2. Visit: $testTablesUrl"
Write-Host "3. Expected: '✅ faq_items: EXISTS' and '✅ blog_posts: EXISTS'"
Write-Host "4. DELETE test-tables.php after verification`n"

$tablesCheck = Read-Host "Do both tables exist? (y/n)"

if ($tablesCheck -eq "y") {
  Write-Host "✅ Database tables: PASSED`n" -ForegroundColor Green
  $faqCount = Read-Host "faq_items row count"
  $blogCount = Read-Host "blog_posts row count"
  $tablesStatus = "PASSED (faq_items: $faqCount rows, blog_posts: $blogCount rows)"
}
else {
  Write-Host "❌ Database tables: MISSING`n" -ForegroundColor Red
  Write-Host "Import schema files via phpMyAdmin:`n" -ForegroundColor Yellow
  Write-Host "- db/schema/faq_items.sql"
  Write-Host "- db/schema/blog_posts.sql`n"
  $continueAnyway = Read-Host "Continue anyway? (y/n)"
  if ($continueAnyway -ne "y") { exit 1 }
  $tablesStatus = "FAILED - Needs schema import"
}

# ============================================
# STEP 3: Cloudflare Cache Clear
# ============================================
Write-Host "STEP 3: Cloudflare Cache Clear" -ForegroundColor Green
Write-Host "-------------------------------" -ForegroundColor Gray

Write-Host "`nManual Actions Required:" -ForegroundColor Yellow
Write-Host "1. Login: https://dash.cloudflare.com"
Write-Host "2. Select domain: blackbox.codes"
Write-Host "3. Caching → Configuration → Purge Everything"
Write-Host "4. Wait 30 seconds"
Write-Host "5. Visit $prodUrl (Ctrl+Shift+R hard refresh)"
Write-Host "6. DevTools → Network → Check style.css has 'cf-cache-status: MISS'`n"

$cacheCheck = Read-Host "Was Cloudflare cache purged successfully? (y/n)"

if ($cacheCheck -eq "y") {
  Write-Host "✅ Cloudflare cache: PURGED`n" -ForegroundColor Green
  $cacheStatus = "PURGED"
}
else {
  Write-Host "⚠️ Cloudflare cache: NOT PURGED`n" -ForegroundColor Yellow
  $cacheStatus = "PENDING"
}

# ============================================
# STEP 4: Cross-Browser Testing
# ============================================
Write-Host "STEP 4: Cross-Browser Testing" -ForegroundColor Green
Write-Host "------------------------------" -ForegroundColor Gray

Write-Host "`nOpening production site in default browser...`n" -ForegroundColor Yellow
Start-Process $prodUrl

Write-Host "Test Checklist:" -ForegroundColor Yellow
Write-Host "[ ] Chrome: Navigation links gray-300 (NOT blue)"
Write-Host "[ ] Chrome: Visited links gray-300 (NOT purple)"
Write-Host "[ ] Chrome: Hover changes to amber-400"
Write-Host "[ ] Chrome: Mobile menu NO white box"
Write-Host "[ ] Brave: Test with Dark Mode enabled"
Write-Host "[ ] Brave: Matrix animation NOT inverted"
Write-Host "[ ] Firefox: Navigation colors correct"
Write-Host "[ ] Edge: Rendering matches Chrome`n"

$chromeTest = Read-Host "Chrome tests passed? (y/n)"
$braveTest = Read-Host "Brave tests passed? (y/n)"
$firefoxTest = Read-Host "Firefox tests passed? (y/n)"
$edgeTest = Read-Host "Edge tests passed? (y/n)"

$browserResults = @{
  Chrome  = if ($chromeTest -eq "y") { "PASSED" } else { "FAILED" }
  Brave   = if ($braveTest -eq "y") { "PASSED" } else { "FAILED" }
  Firefox = if ($firefoxTest -eq "y") { "PASSED" } else { "FAILED" }
  Edge    = if ($edgeTest -eq "y") { "PASSED" } else { "FAILED" }
}

Write-Host "`nBrowser Test Results:" -ForegroundColor Cyan
$browserResults.GetEnumerator() | ForEach-Object {
  $icon = if ($_.Value -eq "PASSED") { "✅" } else { "❌" }
  Write-Host "  $icon $($_.Key): $($_.Value)"
}
Write-Host ""

# ============================================
# STEP 5: Lighthouse Audit
# ============================================
Write-Host "STEP 5: Lighthouse Audit" -ForegroundColor Green
Write-Host "-------------------------" -ForegroundColor Gray

Write-Host "`nManual Actions Required:" -ForegroundColor Yellow
Write-Host "1. Open Chrome DevTools (F12)"
Write-Host "2. Click Lighthouse tab"
Write-Host "3. Select: Performance, Accessibility, Best Practices, SEO"
Write-Host "4. Device: Desktop → Analyze page load"
Write-Host "5. Wait for completion (~60 seconds)"
Write-Host "6. Repeat for Mobile`n"

$runLighthouse = Read-Host "Ready to input Lighthouse scores? (y/n)"

if ($runLighthouse -eq "y") {
  Write-Host "`n--- DESKTOP SCORES ---" -ForegroundColor Cyan
  $desktopPerf = Read-Host "Performance (0-100)"
  $desktopA11y = Read-Host "Accessibility (0-100)"
  $desktopBP = Read-Host "Best Practices (0-100)"
  $desktopSEO = Read-Host "SEO (0-100)"

  Write-Host "`n--- MOBILE SCORES ---" -ForegroundColor Cyan
  $mobilePerf = Read-Host "Performance (0-100)"
  $mobileA11y = Read-Host "Accessibility (0-100)"
  $mobileBP = Read-Host "Best Practices (0-100)"
  $mobileSEO = Read-Host "SEO (0-100)"

  Write-Host "`n--- CORE WEB VITALS ---" -ForegroundColor Cyan
  $lcp = Read-Host "LCP - Largest Contentful Paint (seconds)"
  $fid = Read-Host "FID - First Input Delay (ms)"
  $cls = Read-Host "CLS - Cumulative Layout Shift (0.0-1.0)"

  $lighthouseStatus = "COMPLETED"
}
else {
  Write-Host "⚠️ Lighthouse audit skipped`n" -ForegroundColor Yellow
  $lighthouseStatus = "SKIPPED"
  $desktopPerf = "N/A"
  $desktopA11y = "N/A"
  $mobilePerf = "N/A"
  $lcp = "N/A"
  $fid = "N/A"
  $cls = "N/A"
}

# ============================================
# STEP 6: Generate Report
# ============================================
Write-Host "`nSTEP 6: Generating Validation Report" -ForegroundColor Green
Write-Host "--------------------------------------" -ForegroundColor Gray

$reportPath = "docs\DEPLOYMENT_RESULTS_$(Get-Date -Format 'yyyyMMdd_HHmmss').md"

$report = @"
# Deployment Validation Results

**Date:** $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
**Production URL:** $prodUrl
**Validator:** $env:USERNAME
**Commits:** b56daff, 866a266, 7855ca1

---

## Validation Summary

| Check | Status | Details |
|-------|--------|---------|
| Database Connection | $dbStatus | BBX_DB_CONNECTED verified |
| Database Tables | $tablesStatus | faq_items, blog_posts |
| Cloudflare Cache | $cacheStatus | Cache purge verification |
| Cross-Browser | $(if($browserResults.Values -contains 'FAILED'){'PARTIAL'}else{'PASSED'}) | 4 browsers tested |
| Lighthouse Audit | $lighthouseStatus | Desktop + Mobile scores |

---

## Detailed Results

### 1. Database Connection
**Status:** $dbStatus

Test performed via test-db.php uploaded to production.
BBX_DB_CONNECTED constant verified working correctly.

### 2. Database Tables
**Status:** $tablesStatus

Tables verified:
- faq_items: $faqCount rows
- blog_posts: $blogCount rows

### 3. Cloudflare Cache
**Status:** $cacheStatus

Cache purge performed via Cloudflare Dashboard.
Verified style.css loading with new navigation CSS.

### 4. Cross-Browser Testing

| Browser | Status | Notes |
|---------|--------|-------|
| Chrome | $($browserResults.Chrome) | Navigation colors, hover states, mobile menu |
| Brave | $($browserResults.Brave) | Dark mode tested, Matrix animation verified |
| Firefox | $($browserResults.Firefox) | CSS compatibility verified |
| Edge | $($browserResults.Edge) | Chromium rendering verified |

### 5. Lighthouse Audit Results

#### Desktop Scores
- **Performance:** $desktopPerf / 100
- **Accessibility:** $desktopA11y / 100
- **Best Practices:** $desktopBP / 100
- **SEO:** $desktopSEO / 100

#### Mobile Scores
- **Performance:** $mobilePerf / 100
- **Accessibility:** $mobileA11y / 100
- **Best Practices:** $mobileBP / 100
- **SEO:** $mobileSEO / 100

#### Core Web Vitals
- **LCP (Largest Contentful Paint):** $lcp s (target: < 2.5s)
- **FID (First Input Delay):** $fid ms (target: < 100ms)
- **CLS (Cumulative Layout Shift):** $cls (target: < 0.1)

---

## Issues Found

"@

# Add issues section
$hasIssues = $false

if ($dbStatus -like "FAILED*") {
  $report += "`n### Critical: Database Connection`n"
  $report += "Database connection failed. Review error message and fix credentials.`n"
  $hasIssues = $true
}

if ($tablesStatus -like "FAILED*") {
  $report += "`n### Critical: Database Tables Missing`n"
  $report += "Import schema files via phpMyAdmin or MySQL CLI.`n"
  $hasIssues = $true
}

if ($browserResults.Values -contains "FAILED") {
  $report += "`n### Medium: Cross-Browser Issues`n"
  $failedBrowsers = $browserResults.GetEnumerator() | Where-Object { $_.Value -eq "FAILED" } | ForEach-Object { $_.Key }
  $report += "Failed in: $($failedBrowsers -join ', ')`n"
  $report += "Review navigation CSS and test specific browser rendering.`n"
  $hasIssues = $true
}

if ([int]$desktopPerf -lt 85 -and $desktopPerf -ne "N/A") {
  $report += "`n### Low: Performance Below Target`n"
  $report += "Desktop Performance score: $desktopPerf (target: > 85)`n"
  $report += "Consider image optimization, CSS/JS minification, caching improvements.`n"
  $hasIssues = $true
}

if (-not $hasIssues) {
  $report += "`n**No critical issues found.** ✅`n"
}

$report += @"

---

## Deployment Status

"@

# Determine overall status
$allPassed = $dbStatus -eq "PASSED" -and
$tablesStatus -like "PASSED*" -and
$cacheStatus -eq "PURGED" -and
-not ($browserResults.Values -contains "FAILED") -and
$lighthouseStatus -eq "COMPLETED"

if ($allPassed) {
  $report += "**Overall Status:** 🟢 **DEPLOYMENT SUCCESSFUL**`n`n"
  $report += "All validation checks passed. Ready for final release tagging.`n"
}
else {
  $report += "**Overall Status:** 🟡 **DEPLOYMENT PARTIAL**`n`n"
  $report += "Some validation checks failed or incomplete. Review issues above.`n"
}

$report += @"

---

## Next Steps

"@

if ($allPassed) {
  $report += @"
1. ✅ Tag release: ``git tag -a v1.0.0-sprint4 -m "Sprint 4 validated"``
2. ✅ Push tag: ``git push origin v1.0.0-sprint4``
3. ✅ Create GitHub release with these results
4. ✅ Update SPRINT4_VERIFICATION_AUDIT.md with final status
5. ✅ Close all deployment todos

**Deployment ready for production announcement!** 🚀
"@
}
else {
  $report += @"
1. ⚠️ Fix critical issues listed above
2. ⚠️ Re-run failed validation checks
3. ⚠️ Re-run this script after fixes
4. ⚠️ Document fixes in git commits

**Do not tag release until all checks pass.**
"@
}

# Save report
$report | Out-File -FilePath $reportPath -Encoding UTF8

Write-Host "`n✅ Validation report saved to: $reportPath`n" -ForegroundColor Green

# Display summary
Write-Host "=== VALIDATION SUMMARY ===" -ForegroundColor Cyan
Write-Host "Database: $dbStatus"
Write-Host "Tables: $tablesStatus"
Write-Host "Cache: $cacheStatus"
Write-Host "Browsers: $(if($browserResults.Values -contains 'FAILED'){'PARTIAL'}else{'PASSED'})"
Write-Host "Lighthouse: $lighthouseStatus"
Write-Host ""

if ($allPassed) {
  Write-Host "🟢 DEPLOYMENT SUCCESSFUL - Ready for release!" -ForegroundColor Green

  $tagRelease = Read-Host "`nCreate git tag v1.0.0-sprint4 now? (y/n)"
  if ($tagRelease -eq "y") {
    Write-Host "`nCreating git tag..." -ForegroundColor Yellow
    git tag -a v1.0.0-sprint4 -m "Sprint 4: Navigation Fix + Performance Optimization

Deployment validated on $(Get-Date -Format 'yyyy-MM-dd'):
- Database connection: $dbStatus
- Database tables: $tablesStatus
- Cloudflare cache: $cacheStatus
- Cross-browser: 4/4 browsers passed
- Lighthouse Desktop: Performance $desktopPerf, A11y $desktopA11y
- Lighthouse Mobile: Performance $mobilePerf
- Core Web Vitals: LCP $lcp s, FID $fid ms, CLS $cls

All validation checks passed successfully."

    Write-Host "✅ Tag created: v1.0.0-sprint4" -ForegroundColor Green

    $pushTag = Read-Host "Push tag to GitHub? (y/n)"
    if ($pushTag -eq "y") {
      git push origin v1.0.0-sprint4
      Write-Host "✅ Tag pushed to GitHub" -ForegroundColor Green
      Write-Host "`nCreate GitHub release at:" -ForegroundColor Yellow
      Write-Host "https://github.com/AlphaAcces/blackbox-ui/releases/new?tag=v1.0.0-sprint4"
    }
  }
}
else {
  Write-Host "🟡 DEPLOYMENT PARTIAL - Review issues in report" -ForegroundColor Yellow
}

Write-Host "`n=== VALIDATION COMPLETE ===`n" -ForegroundColor Cyan
Write-Host "Report saved to: $reportPath" -ForegroundColor Gray
Write-Host "Review report for detailed findings.`n"
