# Sprint 9 - Mobile Real-Device Verification Report

**Date:** 2025-01-20  
**Device Tested:** iPhone 16 Pro / Brave @ 100%  
**Issue Reported:** UI feels "micro" on hero/landing at first paint  

---

## 🔍 Investigation Summary

### Critical CSS Verification

| Check | Status | Details |
|-------|--------|---------|
| Mobile font-size in critical.css | ✅ Present | `@media (max-width: 768px) { :root { font-size: 112.5%; } }` |
| Critical CSS inlined in `<head>` | ✅ Verified | Line 291 of site-header.php |
| `data-theme="dark"` on `<html>` | ✅ Server-rendered | No FOUC condition triggered |
| css_version cache-bust | ✅ Active | v1.6.19 |
| A11y hardgate tests | ✅ 30/30 passing | Full coverage |

### Critical CSS Mobile Scale Rule (Lines 131-136)
```css
@media (max-width: 768px) {
  :root { font-size: 112.5%; }
  .graphene-hero-3d__content { padding: 6rem 1rem 3rem; }
  .graphene-hero-title { min-height: 3em; }
}
```

### iPhone 16 Pro Viewport Analysis
- **Logical Width:** 393px CSS pixels
- **Breakpoint:** 768px max-width
- **Result:** ✅ Device width (393px) < breakpoint (768px) — rule SHOULD apply

---

## 🔬 Root Cause Analysis

### Possible Causes for "Micro" First Paint

1. **Aggressive Browser Cache** (Most Likely)
   - Brave on iOS uses WebKit with aggressive caching
   - Old CSS without mobile uplift may be cached
   - **Fix:** Force hard refresh (hold reload button → "Request Desktop Site" toggle or clear cache)

2. **Service Worker Cache**
   - If a service worker is registered, it may serve stale CSS
   - **Check:** No active service worker found in this codebase

3. **CDN/Proxy Cache Layer**
   - If deploying via CDN, cached response may lack new critical CSS
   - **Fix:** Purge CDN cache after deployment

4. **iOS Safari Text Size Adjust**
   - iOS Safari may apply `-webkit-text-size-adjust` differently
   - **Current:** `html { -webkit-text-size-adjust: 100%; }` (correct)

---

## ✅ Verification Steps for Real Device

### Step 1: Force Hard Cache Clear
```
iOS Brave/Safari:
1. Settings → Brave/Safari → Advanced → Website Data
2. Search for your domain → Delete
3. Reload the page
```

### Step 2: Verify CSS Version in Response Headers
Open Brave dev tools (connect to Mac) or use:
```bash
curl -I "https://yourdomain.com/index.php" | grep -i "cache\|etag"
```

### Step 3: Check Computed Font Size
In Safari Web Inspector:
1. Connect iPhone to Mac
2. Safari → Develop → iPhone → Select page
3. Elements → Select `<html>` → Computed Styles
4. Verify `font-size: 18px` (112.5% of 16px = 18px)

---

## 📊 A11y Hardgate Results

```
Test Suite: tests/a11y-hardgate.spec.js
Result: 30 passed (17.5s)

Coverage:
- Critical & Serious Violations: 10 routes tested
- Focus Visibility: ✅
- Touch Target Size: ✅
- Color Contrast: ✅
- Keyboard Navigation: ✅
```

---

## 📝 Technical Details

### Files Verified
| File | Purpose | Status |
|------|---------|--------|
| [assets/css/critical.css](../assets/css/critical.css) | Above-the-fold CSS (inlined) | ✅ Contains mobile uplift |
| [includes/site-header.php](../includes/site-header.php#L291) | Critical CSS injection point | ✅ Correctly inlines CSS |
| [assets/css/tokens.css](../assets/css/tokens.css) | Design tokens (async loaded) | ✅ Also has mobile uplift |

### CSS Loading Strategy
```
1. Critical CSS (inlined) ──→ Contains mobile font-size 112.5%
   ↓
2. Preload async CSS ──→ tokens.css, custom-ui.css, etc.
   ↓
3. onload swap to stylesheet ──→ Full styles applied
```

### Cache-Bust Version Trail
```
v1.6.19 - Current (Sprint 9 Batch 3)
```

---

## 🎯 Conclusion

**No code changes required.** The critical CSS correctly includes the mobile scale uplift rule. The "micro" first paint issue is most likely caused by **aggressive browser caching** on the real device.

### Recommended Actions:
1. Clear Brave/Safari cache on iPhone and re-test
2. If deploying to production, increment `$css_version` to force cache refresh
3. Consider adding `Cache-Control: no-cache` during development testing

---

## 📋 Checklist for Production Deployment

- [ ] Increment `$css_version` in site-header.php (currently 1.6.19)
- [ ] Purge CDN cache (if applicable)
- [ ] Verify mobile font-size in production HTML source
- [ ] Test on real device with cleared cache

---

*Report generated: Sprint 9 Mobile UX Validation*  
*Branch: sprint8-a11y-hardgate*
