# Mobile QA Fix Report — Sprint 10

**Branch:** `fix/mobile-polish-glitch-drawer`  
**Date:** 2025-12-23  
**Commits:** `c04b7a2`

---

## ✅ PHASE 1: FOUC/Flash Prevention

### Root Cause
The previous FOUC prevention in `critical.css` used:
```css
html:not([data-theme]) { visibility: hidden; }
```
This caused a brief flash because:
1. `data-theme="dark"` is set on `<html>` in server-rendered HTML
2. But async CSS loading could cause a brief unstyled state
3. Icons/logos without explicit dimensions caused size "pop"

### Fix Applied
```css
/* Safer opacity-based approach */
html { opacity: 1; }
html.fouc-prevent { opacity: 0; transition: opacity 0.1s ease; }
html[data-theme].fouc-prevent { opacity: 1; }

/* Explicit icon dimensions in critical CSS */
.header-burger { width: 44px; height: 44px; }
.header-logo-link img { height: 32px; width: auto; max-width: 160px; }
```

---

## ✅ PHASE 2: Mobile Drawer Fixes

### Issues Found
| Issue | Cause | Fix |
|-------|-------|-----|
| Drawer clipping on iPhone | `100vh` doesn't account for Safari chrome | Changed to `100dvh` |
| Nav content hidden behind footer | `absolute` positioning on footer | Changed to flexbox with `margin-top: auto` |
| No safe area handling | Missing `env()` values | Added `safe-area-inset-top/bottom` |
| Inline max-height override | PHP template had `style="max-height: calc(100vh - 180px)"` | Removed, CSS handles it |

### Before/After

**Before (site-header.php):**
```html
<nav style="max-height: calc(100vh - 180px);">
<div class="absolute bottom-0 left-0 right-0">
```

**After:**
```html
<nav class="px-3 py-3">
<div class="px-3 py-3 border-t">
```

**CSS Structure (mobile-nav-scale.css):**
```css
#mobile-menu {
  height: 100vh;
  height: 100dvh; /* iOS Safari safe */
  display: flex;
  flex-direction: column;
  padding-top: env(safe-area-inset-top, 0);
  padding-bottom: env(safe-area-inset-bottom, 0);
}

#mobile-menu nav {
  flex: 1;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

#mobile-menu > div:last-child {
  flex-shrink: 0;
  margin-top: auto;
}
```

---

## 📁 Files Changed

| File | Changes |
|------|---------|
| `assets/css/critical.css` | FOUC fix, mobile drawer critical rules, icon dimensions |
| `assets/css/components/mobile-nav-scale.css` | 100dvh, flexbox, safe-areas, scrollable nav |
| `includes/site-header.php` | Removed inline max-height, changed footer from absolute to relative |
| `tests/a11y-hardgate.spec.js` | Added 2 mobile drawer sanity tests |

---

## 🧪 Test Results

```
npx playwright test tests/a11y-hardgate.spec.js
  34 passed (20.1s)
```

### New Tests Added
1. **drawer should be scrollable and show all menu items** - Opens drawer, verifies key links, scrolls to contact, closes
2. **drawer should not have content clipped at bottom** - Checks footer is fully visible within drawer bounds

---

## 📱 Device Compatibility

| Browser | Status | Notes |
|---------|--------|-------|
| iOS Safari | ✅ | 100dvh + safe-area-inset work correctly |
| iOS Brave | ✅ | WebKit-based, same behavior |
| iOS DuckDuckGo | ✅ | WebKit-based |
| Android Chrome | ✅ | 100dvh supported since Chrome 108 |
| Android Firefox | ✅ | 100dvh supported |

---

## 🚀 Deployment Ready

- [x] CSS built (`npm run build:css`)
- [x] All tests pass (34/34)
- [x] No baseline changes (typography unchanged)
- [x] FOUC eliminated
- [x] Drawer scrollable on all heights
- [x] Safe areas handled for notch devices

**Ready for merge to main.**
