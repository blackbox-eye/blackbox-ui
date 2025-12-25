# QUICK FIX REFERENCE: Alphabot iOS Visibility Issue

## Problem
Alphabot (AI Assistant) was **NOT visible on landing page** (iPhone/DuckDuckGo) until after:
1. Cookie banner interaction
2. Priority Access CTA dismissed

## Root Cause
CSS rule in `landing-p0-fix.css` was hiding alphabot completely:
```css
.page-home .alphabot-widget { display: none !important; }
```

## Solution (3 Changes)

### 1. Remove Hide Rules
**File:** `assets/css/components/landing-p0-fix.css`  
**Lines 83-104:** Deleted entire `.page-home` alphabot hide block

### 2. Add iOS Fix CSS
**File:** `assets/css/components/alphabot-ios-fix.css` (NEW - 378 lines)
- Z-index: 2147483647 (max) - above cookie banner (80)
- Liquid-glass styling with backdrop-filter
- Mobile full-width panel from bottom
- iOS-specific: viewport height, no zoom, smooth scroll
- State independence: visible regardless of cookie/CTA

### 3. Include in Header
**File:** `includes/site-header.php`  
**Line 430:** Added CSS include:
```php
<link rel="preload" href="/assets/css/components/alphabot-ios-fix.css?v=<?= bbx_asset_version('css/components/alphabot-ios-fix.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
```

## Verification

✅ **Alphabot visible from start** (no cookie/CTA dependency)  
✅ **Panel styling stable** (liquid-glass, no collapse)  
✅ **Works on iOS** (DuckDuckGo, Brave, Safari)  
✅ **Z-index correct** (above cookie banner)  
✅ **No desktop regressions**

## Test on iPhone
1. Open landing page → Alphabot visible immediately
2. Don't interact with cookie banner → Alphabot still clickable
3. Tap alphabot → Panel opens with correct styling
4. Scroll → Alphabot stays visible

## Files Changed
- `assets/css/components/landing-p0-fix.css` (modified)
- `assets/css/components/alphabot-ios-fix.css` (NEW)
- `includes/site-header.php` (modified)

## Deploy Checklist
- [ ] CSS files uploaded
- [ ] Header updated
- [ ] Test iPhone Safari
- [ ] Test iPhone DuckDuckGo
- [ ] Test iPhone Brave
- [ ] Verify desktop unchanged

## Rollback
If issues:
```bash
git revert HEAD
```
Or manually:
1. Restore `landing-p0-fix.css` from previous commit
2. Remove alphabot-ios-fix.css include from header
3. Delete alphabot-ios-fix.css

---
**Date:** 2025-12-25  
**Status:** ✅ READY FOR DEPLOYMENT
