# Visual Test Protocol - Navigation Fix Verification

**Commit:** b56daff  
**Date:** November 23, 2025  
**Tester:** [Your Name]  
**Server:** http://localhost:8000

---

## 🎯 TEST OBJECTIVES

Verify that navigation links display correct colors across all states:
- **Default:** Gray-300 (#d1d5db) - NOT browser blue (#0000EE)
- **Visited:** Gray-300 (#d1d5db) - NOT browser purple (#551A8B)
- **Hover:** Amber-400 (#fbbf24)
- **Active Page:** White (#ffffff) with amber underline

---

## 📋 DESKTOP TESTING (Chrome DevTools)

### 1. Open Browser and DevTools
```powershell
# Open Chrome with DevTools
Start-Process "chrome.exe" "http://localhost:8000" --auto-open-devtools-for-tabs
```

### 2. Inspect Navigation Link Colors

**Step-by-Step:**
1. Right-click on "Om os" navigation link → Select "Inspect"
2. In DevTools Elements panel, you should see:
   ```html
   <a href="index.php?lang=da#om-os" class="nav-link whitespace-nowrap">
   ```
3. Click on "Computed" tab in DevTools
4. Search for "color" property
5. **EXPECTED VALUE:** `rgb(209, 213, 219)` or `#d1d5db` (gray-300)
6. **FAIL IF:** `rgb(0, 0, 238)` (blue) or any blue shade

**Screenshot:** `test-results/01-desktop-nav-default-color.png`

---

### 3. Test Hover State

**Step-by-Step:**
1. In DevTools Elements panel, keep "Om os" link selected
2. Click the `:hov` button in Styles panel
3. Check the `:hover` checkbox
4. In Computed tab, verify color changed to: `rgb(251, 191, 36)` or `#fbbf24` (amber-400)
5. **Bonus:** Verify underline animation appears (::after pseudo-element)

**Screenshot:** `test-results/02-desktop-nav-hover-color.png`

---

### 4. Test Visited State (CRITICAL)

**Step-by-Step:**
1. Click on "Produkter" link (navigate away from home)
2. Press Browser Back button (or click logo to return home)
3. Inspect "Produkter" link again
4. In Computed tab, verify color is STILL: `rgb(209, 213, 219)` (#d1d5db)
5. **CRITICAL FAIL IF:** Color is `rgb(85, 26, 139)` (purple) or any purple shade
6. Repeat for ALL navigation links: Om os, Produkter, Kundecases, Priser, Kontakt

**Screenshot:** `test-results/03-desktop-nav-visited-color.png`

---

### 5. Test Active Page State

**Step-by-Step:**
1. Navigate to "Om os" section (click link or scroll to #om-os)
2. Inspect the "Om os" navigation link
3. In Elements panel, verify it has class: `nav-link-active`
4. In Computed tab, verify:
   - **color:** `rgb(255, 255, 255)` (#ffffff - white)
   - **font-weight:** `600` (semibold)
   - **border-bottom-width:** `2px`
   - **border-bottom-color:** `rgb(251, 191, 36)` (#fbbf24)

**Screenshot:** `test-results/04-desktop-nav-active-page.png`

---

### 6. Test Language Switcher

**Step-by-Step:**
1. Click "EN" language button
2. Verify page reloads with English text
3. Re-test navigation link colors (should be identical)
4. Click "DA" to return to Danish
5. Re-test navigation link colors

**Screenshot:** `test-results/05-desktop-language-switch.png`

---

## 📱 MOBILE TESTING (Chrome DevTools Device Emulation)

### 7. Enable Device Emulation

**Step-by-Step:**
1. In Chrome DevTools, press `Ctrl+Shift+M` (or click device icon)
2. Select "iPhone 12 Pro" from device dropdown
3. Set dimensions: 390 x 844 px
4. Refresh page (F5)

---

### 8. Test Mobile Menu Button

**Step-by-Step:**
1. Verify desktop navigation is HIDDEN
2. Verify hamburger button (☰) is VISIBLE in top-right
3. **CRITICAL:** Inspect hamburger button → verify NO white box/background
4. Expected button styling:
   ```css
   background-color: transparent
   border: none
   color: rgb(255, 255, 255)
   ```

**Screenshot:** `test-results/06-mobile-hamburger-button.png`

---

### 9. Test Mobile Menu Overlay

**Step-by-Step:**
1. Click hamburger button (☰)
2. Verify overlay appears:
   - Black background with 80% opacity
   - Backdrop blur effect
   - Covers entire viewport
3. Verify mobile menu slides in from right:
   - Dark gray background (bg-gray-900/95)
   - Links display vertically
   - Close button (X) visible in top-right

**Screenshot:** `test-results/07-mobile-menu-open.png`

---

### 10. Test Mobile Navigation Links

**Step-by-Step:**
1. With mobile menu open, inspect "Om os" link
2. In Computed tab, verify color: `rgb(209, 213, 219)` (#d1d5db)
3. In Elements panel, verify class: `nav-link-mobile`
4. Click "Om os" (or use :hover simulation)
5. Verify hover color changes to: `rgb(251, 191, 36)` (#fbbf24)
6. Click close button (X) or overlay → menu closes

**Screenshot:** `test-results/08-mobile-nav-links.png`

---

### 11. Test Android Viewport

**Step-by-Step:**
1. Change device to "Samsung Galaxy S21"
2. Set dimensions: 360 x 800 px
3. Repeat tests 8-10
4. Verify no horizontal scrolling
5. Verify touch targets are large enough (minimum 44x44px)

**Screenshot:** `test-results/09-android-mobile-menu.png`

---

## 🌐 CROSS-BROWSER TESTING

### 12. Brave Browser (Desktop)

**Step-by-Step:**
1. Open Brave: `Start-Process "brave.exe" "http://localhost:8000"`
2. Repeat desktop tests 2-6
3. **Enable Shields:**
   - Click Brave Shields icon
   - Set to "Shields Up"
   - Refresh page
   - Verify navigation still works
4. **Test Dark Mode:**
   - Go to `brave://flags`
   - Search for "Force Dark Mode for Web Contents"
   - Enable flag
   - Restart Brave
   - Visit localhost:8000
   - **CRITICAL:** Verify Matrix animation NOT inverted (should be green on black, NOT magenta on white)
   - Verify hero gradient text has NO white rectangles
   - Verify navigation colors maintained (NOT inverted)

**Screenshot:** `test-results/10-brave-dark-mode.png`

---

### 13. Firefox (Desktop)

**Step-by-Step:**
1. Open Firefox: `Start-Process "firefox.exe" "http://localhost:8000"`
2. Press F12 to open DevTools
3. Repeat desktop tests 2-6
4. **Firefox-Specific Checks:**
   - Verify :visited pseudo-class works (Firefox has stricter privacy)
   - Check CSS transitions smooth (different rendering engine)
   - Verify backdrop-blur works (may need fallback)

**Screenshot:** `test-results/11-firefox-navigation.png`

---

### 14. Edge (Desktop)

**Step-by-Step:**
1. Open Edge: `Start-Process "msedge.exe" "http://localhost:8000"`
2. Press F12 to open DevTools
3. Repeat desktop tests 2-6
4. Verify rendering matches Chrome (both use Chromium)

**Screenshot:** `test-results/12-edge-navigation.png`

---

## 🔬 LIGHTHOUSE AUDIT

### 15. Run Lighthouse - Desktop

**Step-by-Step:**
1. In Chrome DevTools, click "Lighthouse" tab
2. Select categories:
   - ✅ Performance
   - ✅ Accessibility
   - ✅ Best Practices
   - ✅ SEO
3. Device: **Desktop**
4. Click "Analyze page load"
5. Wait for audit to complete (30-60 seconds)
6. **Document scores:**
   - Performance: ___ / 100
   - Accessibility: ___ / 100
   - Best Practices: ___ / 100
   - SEO: ___ / 100
7. **Document Core Web Vitals:**
   - LCP (Largest Contentful Paint): ___ s (target: < 2.5s)
   - FID (First Input Delay): ___ ms (target: < 100ms)
   - CLS (Cumulative Layout Shift): ___ (target: < 0.1)

**Screenshot:** `test-results/13-lighthouse-desktop.png`

---

### 16. Run Lighthouse - Mobile

**Step-by-Step:**
1. In Lighthouse tab, change Device to: **Mobile**
2. Click "Analyze page load"
3. Document scores (same as desktop)
4. **Mobile-Specific Checks:**
   - Viewport meta tag present
   - Text readable without zooming
   - Tap targets sized appropriately (48x48dp)

**Screenshot:** `test-results/14-lighthouse-mobile.png`

---

## 📊 RESULTS TEMPLATE

Copy this to `test-results/TEST_RESULTS.md`:

```markdown
# Test Results - Navigation Fix Verification

**Date:** November 23, 2025  
**Commit:** b56daff  
**Tester:** [Your Name]

## Desktop Navigation (Chrome)
- [ ] Default color: Gray-300 ✅ / ❌
- [ ] Hover color: Amber-400 ✅ / ❌
- [ ] Visited color: Gray-300 (NOT purple) ✅ / ❌
- [ ] Active page: White + amber underline ✅ / ❌
- [ ] Language switcher works ✅ / ❌

## Mobile Navigation (iPhone 12 Pro Emulation)
- [ ] Hamburger button NO white box ✅ / ❌
- [ ] Mobile menu slides in ✅ / ❌
- [ ] Overlay appears (blur + opacity) ✅ / ❌
- [ ] Mobile links gray-300 default ✅ / ❌
- [ ] Close button works ✅ / ❌

## Cross-Browser
- [ ] Brave: Navigation works ✅ / ❌
- [ ] Brave Dark Mode: Matrix animation OK ✅ / ❌
- [ ] Firefox: Navigation works ✅ / ❌
- [ ] Edge: Navigation works ✅ / ❌

## Lighthouse Scores
**Desktop:**
- Performance: ___ / 100
- Accessibility: ___ / 100
- Best Practices: ___ / 100
- SEO: ___ / 100

**Mobile:**
- Performance: ___ / 100
- Accessibility: ___ / 100
- Best Practices: ___ / 100
- SEO: ___ / 100

## Core Web Vitals
- LCP: ___ s (target: < 2.5s)
- FID: ___ ms (target: < 100ms)
- CLS: ___ (target: < 0.1)

## Issues Found
### Critical (Must Fix)
- [List any critical issues]

### Medium Priority
- [List any medium issues]

### Low Priority / Nice-to-Have
- [List any low priority issues]

## Conclusion
[ ] All tests passed - ready for production  
[ ] Some issues found - fixes required  
[ ] Major issues found - rework needed

**Overall Status:** 🟢 PASS / 🟡 PARTIAL / 🔴 FAIL

**Next Actions:**
1. [List next steps based on findings]
```

---

## 🚀 QUICK TEST SCRIPT

Save this as `test.ps1` for rapid testing:

```powershell
# Quick Visual Test Script
Write-Host "`n=== BLACKBOX EYE - Navigation Test Suite ===`n" -ForegroundColor Cyan

# 1. Ensure PHP server is running
Write-Host "1. Starting PHP server on localhost:8000..." -ForegroundColor Yellow
$phpProcess = Start-Process -FilePath "C:\php view\php.exe" -ArgumentList "-S", "localhost:8000" -WindowStyle Hidden -PassThru
Start-Sleep -Seconds 2

# 2. Open Chrome with DevTools
Write-Host "2. Opening Chrome with DevTools..." -ForegroundColor Yellow
Start-Process "chrome.exe" "http://localhost:8000" --auto-open-devtools-for-tabs

Write-Host "`n=== TEST CHECKLIST ===`n" -ForegroundColor Green
Write-Host "[1] Inspect navigation link → Verify color: #d1d5db (gray-300)"
Write-Host "[2] Hover link → Verify color: #fbbf24 (amber-400)"
Write-Host "[3] Click link + back → Verify visited color: #d1d5db (NOT purple)"
Write-Host "[4] Check active page → Verify color: #ffffff + amber underline"
Write-Host "[5] Resize to <768px → Test mobile menu (NO white box on button)"
Write-Host "[6] Run Lighthouse audit → Document scores"
Write-Host "`nPress Ctrl+C when done testing to stop PHP server." -ForegroundColor Cyan

# Keep script running until user stops
try {
    while ($true) { Start-Sleep -Seconds 1 }
} finally {
    Write-Host "`nStopping PHP server..." -ForegroundColor Yellow
    Stop-Process -Id $phpProcess.Id -Force
    Write-Host "Done!" -ForegroundColor Green
}
```

---

## ✅ COMPLETION CRITERIA

Test session is considered COMPLETE when:
1. ✅ All 16 test steps executed
2. ✅ All screenshots captured
3. ✅ TEST_RESULTS.md filled out
4. ✅ Lighthouse scores documented
5. ✅ Any issues logged with severity
6. ✅ Pass/Fail verdict given

**Minimum Passing Criteria:**
- ✅ Navigation links are gray-300 (NOT blue/purple)
- ✅ Visited links stay gray-300 (critical for UX)
- ✅ Hover changes to amber-400
- ✅ Active page shows white + amber underline
- ✅ Mobile menu has NO white box artifact
- ✅ Lighthouse Performance > 85
- ✅ Lighthouse Accessibility > 90
- ✅ Works in Chrome, Brave, Firefox, Edge

---

**Good luck with testing! 🚀**
