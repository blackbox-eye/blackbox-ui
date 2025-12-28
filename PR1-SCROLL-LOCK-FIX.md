# PR #1: P0 iOS Scroll-Lock Root Cause Fix

## 🚨 Critical Bug Fix - iPhone Safari/Brave/DuckDuckGo Scroll Lock

### Summary

Fixes the P0 scroll-lock issue where iPhone Safari (and other iOS browsers) would become permanently scroll-locked after interacting with the mobile menu drawer.

### Root Cause Analysis

The root cause was identified in [landing-p0-fix.css](assets/css/components/landing-p0-fix.css#L589-L597):

```css
/* BEFORE (BROKEN) */
body.mobile-menu-open.page-home {
  overflow: hidden !important;
  position: fixed !important; /* ← THIS WAS THE PROBLEM */
  width: 100% !important;
}
```

**Why this broke iOS Safari:**

1. `position: fixed` on `<body>` causes iOS Safari to lose its scroll position reference
2. When combined with back-forward cache (bfcache), the browser could restore the page with the body still in `position: fixed` state
3. JavaScript unlock functions would run, but CSS `!important` rules would persist
4. Result: Permanent scroll-lock that only a hard refresh could fix

### Fix Applied

```css
/* AFTER (FIXED) */
body.mobile-menu-open.page-home {
  overflow: hidden !important;
  touch-action: none; /* ← Prevents scroll without position:fixed */
  -webkit-overflow-scrolling: auto;
}
```

### Additional Defensive Fixes

#### 1. Enhanced `unlockBodyScroll()` function ([site.js](assets/js/site.js#L240-L313))

- Now detects `position: fixed` via `getComputedStyle()`
- Checks multiple lock classes: `mobile-menu-open`, `alphabot-locked`, `modal-open`, `drawer-open`
- Clears comprehensive style properties: `overflow`, `overflowX`, `overflowY`, `position`, `top`, `left`, `right`, `width`, `height`, `touchAction`
- Also clears styles on `<html>` element

#### 2. Enhanced touchstart failsafe ([site.js](assets/js/site.js#L386-L443))

- Detects blocked scroll when no legitimate overlay is visible
- Checks alphabot panel, modal, and drawer states
- Forces unlock if user touches screen while incorrectly locked

#### 3. New touchmove failsafe ([site.js](assets/js/site.js#L445-L470))

- Monitors touchmove events for blocked scroll
- If page hasn't scrolled after 100ms of touch movement, forces unlock
- Catches edge cases where touchstart failsafe misses

### Files Changed

| File                                                                                 | Change                                                           |
| ------------------------------------------------------------------------------------ | ---------------------------------------------------------------- |
| [assets/css/components/landing-p0-fix.css](assets/css/components/landing-p0-fix.css) | Removed `position: fixed !important`, added `touch-action: none` |
| [assets/js/site.js](assets/js/site.js)                                               | Enhanced `unlockBodyScroll()`, added touchmove failsafe          |

### Testing

New Playwright test file: [tests/ios-scroll-lock.spec.js](tests/ios-scroll-lock.spec.js)

Tests include:

- ✅ Page should be scrollable on initial load
- ✅ Body should not have `position:fixed` when menu is closed
- ✅ Page should remain scrollable after menu open/close cycle
- ✅ HTML and body overflow should not be hidden on page load
- ✅ Alphabot panel open/close should not lock scroll
- ✅ Cookie banner dismiss should not cause scroll lock
- ✅ Touch events should not be blocked on page body
- ✅ Multiple viewport tests (iPhone Safari, iPhone SE, iPad)

### How to Verify

1. Open blackbox.codes on iPhone Safari/Brave/DuckDuckGo
2. Open mobile menu (hamburger icon)
3. Close mobile menu
4. Attempt to scroll the page
5. **Expected:** Page scrolls normally
6. **Before fix:** Page was scroll-locked

With `?debugUI` parameter:

- Visual debug panel shows real-time scroll/lock state
- "Force Unlock Scroll" button available for testing

### Risk Assessment

**Low Risk** - This fix:

- Only affects mobile menu open/close CSS behavior
- Uses `touch-action: none` which is widely supported
- Adds multiple layers of defensive JavaScript failsafes
- Does not change any business logic or data flow
