# P1 Scroll Audit Summary

**Branch:** `feature/p1-scroll-audit`  
**Version:** 1.6.27  
**Date:** 2025-01-XX  
**Status:** ✅ Complete - All 31 tests passing

---

## Executive Summary

This audit resolved the assistant rail responsiveness issue and completed a comprehensive review of the scroll/hero architecture. The root cause was the `P0_SCROLL_ISOLATION` flag being set to `true`, which disabled the assistant rail rendering in production/tests.

---

## 1. Root Cause Analysis

### Primary Issue: Assistant Rail Not Rendering

- **Location:** [includes/debug-killswitch.php](../includes/debug-killswitch.php#L26)
- **Cause:** `P0_SCROLL_ISOLATION` was set to `true`, which:
  - Set `$_BBX_DISABLE_CHAT = true` → disabled Alphabot/assistant rendering
  - Set `$_BBX_DISABLE_AGENT_ACCESS = true` → hid agent-access navigation links
- **Fix:** Changed `P0_SCROLL_ISOLATION` from `true` to `false`

### Secondary Issue: Landing Gate FOUC Blocking Scroll

- **Location:** [includes/site-header.php](../includes/site-header.php)
- **Cause:** The `landing-gate` class with inline JS was adding opacity:0 and potentially interfering with scroll detection
- **Fix:** Removed inline landing-gate JS and CSS blocks from site-header.php

---

## 2. Changes Made

### debug-killswitch.php

```php
// Before
define('P0_SCROLL_ISOLATION', true);

// After
define('P0_SCROLL_ISOLATION', false);
```

### site-header.php

- Removed 50+ lines of inline `landing-gate` JavaScript and CSS
- Removed `landing-gate` from body class assignment
- Bumped `$css_version` from `1.6.24` to `1.6.27`

### site.js

- Removed `mobile-menu-open` from `OVERLAY_CLASS_LIST` (menu no longer locks scroll)
- Removed `savedScrollY` save/restore logic for mobile menu
- Simplified `unlockBodyScroll()` to not check for `mobile-menu-open`
- Debug panel now tracks only: `alphabot-locked`, `modal-open`, `drawer-open`

### scroll-contract.css

- Neutralized `.landing-gate` and `.landing-ready` classes:
  ```css
  .landing-gate,
  .landing-ready {
    opacity: 1 !important;
    visibility: visible !important;
    overflow-y: auto !important;
    position: relative !important;
  }
  ```

### ios-scroll-lock.spec.js

- Added new test: `mobile menu open still allows page scroll and body stays unfixed`
- Fixed ESLint formatting issues in cookie banner state assertions

---

## 3. CSS Architecture Review

### Load Order (Deterministic)

The CSS files load in this order from [site-header.php](../includes/site-header.php):

1. **critical.css** (inline) - Above-fold styles
2. **tailwind.full.css** - Utility framework
3. **tokens.css** - Design tokens
4. **custom-ui.css** - UI components
5. **theme-overrides.css** - Theme customizations
6. **marketing.css** / **admin.css** - Page-type specific
7. **Component CSS** (12 files):
   - motion-safe.css
   - hero-mobile.css
   - mobile-baseline.css
   - mobile-nav-scale.css
   - mobile-final-polish.css
   - landing-p0-fix.css
   - alphabot-ios-fix.css
   - landing-p1-polish.css
   - sticky-cta.css
   - glass-effects.css
   - alphabot-widget.css
   - assistant-rail.css
8. **scroll-contract.css** (LAST) - Global scroll authority

### Key Principle

`scroll-contract.css` loads last and acts as the "global scroll authority", overriding any scroll-blocking rules from earlier stylesheets.

---

## 4. Hero/Scroll Refactor Status

### ✅ Verified

- All heroes use `min-height: 100vh; min-height: 100dvh;` (dynamic viewport with fallback)
- No `position: fixed` set on body/html by JavaScript
- `landing-gate`/`landing-ready` classes are neutralized in CSS
- Mobile menu no longer uses `position: fixed` scroll lock pattern

### Hero Classes in marketing.css

```css
.graphene-hero {
  min-height: 100vh;
  min-height: 100dvh;
  overflow: visible;
}
.hero-full {
  min-height: 100vh;
  min-height: 100dvh;
}
```

---

## 5. Compatibility Warnings (Non-Blocking)

The following CSS compatibility issues were identified but do not affect functionality:

| Issue                      | Location      | Browser Support                   |
| -------------------------- | ------------- | --------------------------------- |
| `color-mix()`              | marketing.css | Chrome 111+, Safari 16.2+         |
| `scrollbar-width`          | critical.css  | Chrome 121+, no Safari            |
| `-webkit-text-size-adjust` | critical.css  | Needs `text-size-adjust` fallback |

These are logged for future optimization but do not block the current release.

---

## 6. Test Results

```
Test Suites: 3 passed, 3 total
Tests:       31 passed, 0 failed
```

### Test Files

- `ios-scroll-lock.spec.js` - iOS scroll regression tests
- `frontpage-responsive.spec.js` - Responsive layout + assistant rail tests
- `production-scroll-debug.spec.js` - Production scroll behavior tests

### Key Test Coverage

- ✅ Page scrollable on initial load
- ✅ Mobile menu open/close doesn't lock scroll
- ✅ Alphabot panel open/close doesn't lock scroll
- ✅ Cookie banner completely removed from DOM
- ✅ BFcache navigation preserves scroll
- ✅ Assistant rail visible and clickable at all viewports

---

## 7. Recommended Structural Changes

### Short-term (Next Sprint)

1. **Add `text-size-adjust` fallback** alongside `-webkit-text-size-adjust`
2. **Add `@supports` guards** for `color-mix()` usage in marketing.css
3. **Consider consolidating** landing-p0-fix.css and landing-p1-polish.css into single file

### Medium-term

1. **Remove dead code**: The `landing-gate` JS/CSS can be fully deleted (currently neutralized)
2. **Audit component CSS**: 12 component files could potentially be merged
3. **Add scrollbar-width fallback** for Safari users

### Long-term

1. **CSS custom properties migration**: Replace `color-mix()` with pre-computed values for broader support
2. **Performance audit**: Some CSS files could be lazy-loaded based on page type

---

## 8. Files Modified

| File                               | Changes                                 |
| ---------------------------------- | --------------------------------------- |
| includes/debug-killswitch.php      | P0_SCROLL_ISOLATION = false             |
| includes/site-header.php           | Removed landing-gate, bumped to v1.6.27 |
| assets/js/site.js                  | Simplified scroll lock logic            |
| assets/js/site.min.js              | Minified build                          |
| assets/css/scroll-contract.css     | Neutralized landing classes             |
| assets/css/scroll-contract.min.css | Minified build                          |
| assets/css/marketing.css           | No changes (reviewed)                   |
| assets/css/marketing.min.css       | Rebuilt                                 |
| tests/ios-scroll-lock.spec.js      | Added new test, formatting fixes        |

---

## 9. Commit Reference

```
commit fef17c6
P1 scroll audit: resolve assistant rail responsiveness, de-gate landing FOUC, unify scroll contract
```

---

## 10. Verification Checklist

- [x] P0_SCROLL_ISOLATION disabled
- [x] Landing-gate removed from JS
- [x] Landing-gate neutralized in CSS
- [x] All 31 tests passing
- [x] Assets rebuilt (CSS + JS)
- [x] Version bumped to 1.6.27
- [x] Changes committed and pushed
- [x] Documentation created

---

_Generated by P1 Scroll Audit - Unified Action Plan_
