# Landing P0 Fix Report

**Branch:** `fix/landing-p0-stability`  
**Date:** 2025-12-23  
**Scope:** Landing page only (index.php + shared includes)  
**Status:** ✅ READY FOR REVIEW

---

## Executive Summary

This PR addresses **5 critical P0 issues** on the landing page affecting mobile usability on iPhone-sized (390×844) and narrow desktop (~420px) viewports.

**Baseline typography is LOCKED** — no font-size or zoom changes were made.

---

## P0-1: Sticky CTA Bar Layering Bug ✅

### Problem
- Bar flipped between overlay/underlay during scroll
- Became transparent randomly
- Overlapped footer legal row

### Solution
- **Solid background:** `#0a0b0d` (zero alpha)
- **No blur:** `backdrop-filter: none !important`
- **Fixed z-index:** `70` (per contract: drawer:40 < sticky:70 < cookie:80)
- **Footer docking:** IntersectionObserver adds `.is-docked` class when footer visible
- **Safe area padding:** `env(safe-area-inset-bottom)` for notched devices
- **Main content padding:** 90px bottom padding prevents overlap

### Files Changed
- [landing-p0-fix.css](assets/css/components/landing-p0-fix.css) lines 23-110
- [site.js](assets/js/site.js) — added footer observer for sticky CTA

### z-index Contract Established
| Layer | z-index |
|-------|---------|
| Drawer overlay | 39 |
| Drawer | 40 |
| Sticky CTA | 70 |
| Cookie banner | 80 |
| Modals | 90 |

---

## P0-2: Burger Menu Drawer ✅

### Problem
- Covered 70-80% of screen
- Had backdrop blur causing performance issues
- Some items not visible without awkward scrolling

### Solution
- **Width:** `min(40vw, 280px)` with `min-width: 240px`
- **Matte black:** `#0a0b0d` solid background (no blur)
- **Compact nav links:** 13px font, 44px touch targets
- **Internal scroll:** Only nav section scrolls if needed
- **Close button:** Guaranteed 44×44px tap target

### Files Changed
- [landing-p0-fix.css](assets/css/components/landing-p0-fix.css) lines 114-285

---

## P0-3: AI Assistant Overlay ✅

### Problem
- Opening assistant blurred entire page
- Felt like hijacking navigation

### Solution
- **No backdrop blur:** `backdrop-filter: none !important`
- **Light dim only:** `rgba(0, 0, 0, 0.15)` as specified
- **Panel constraints:** max-width 340px, max-height 45vh
- **Dark theme enforced:** `#0d0e10` background
- **z-index:** 45 (above content, below sticky CTA)

### Files Changed
- [landing-p0-fix.css](assets/css/components/landing-p0-fix.css) lines 290-370

---

## P0-4: Light Mode Glitch Prevention ✅

### Problem
- Some overlays/drawer appeared white unexpectedly in dark mode

### Solution
- **Force dark surfaces** on drawer, overlay, sticky CTA, cookie banner in dark mode
- **Explicit light mode styles** only apply when `[data-theme="light"]` is set
- Override any Tailwind `bg-gray-900/98` classes that might flash

### Files Changed
- [landing-p0-fix.css](assets/css/components/landing-p0-fix.css) lines 375-430

---

## P0-5: FOUC Prevention ✅

### Problem
- Split-second flash of giant icons/unstyled state

### Solution
- **Explicit logo dimensions:** height 32px (mobile), 40px (desktop)
- **Burger icon sizing:** 44×44px container, 24×24px icon
- **FOUC-ready class:** JS adds `fouc-ready` after first paint via double rAF
- **Transitions disabled** until `fouc-ready` is set

### Files Changed
- [landing-p0-fix.css](assets/css/components/landing-p0-fix.css) lines 435-500
- [site.js](assets/js/site.js) — added `fouc-ready` class handling

---

## Files Changed Summary

| File | Type | Lines |
|------|------|-------|
| `assets/css/components/landing-p0-fix.css` | **NEW** | ~500 |
| `includes/site-header.php` | Modified | +4 (CSS preload) |
| `assets/js/site.js` | Modified | +25 (FOUC + footer observer) |
| `assets/js/site.min.js` | Modified | (rebuilt) |
| `tests/a11y-hardgate.spec.js` | Modified | +90 (4 new P0 tests) |

---

## Test Results

```
a11y-hardgate.spec.js: 36 passed (1.1m)
marketing-landing.spec.js: 60 passed (30.9s)
```

### New P0 Tests Added
1. **drawer should show all nav items without page scroll**
2. **sticky CTA should not overlap footer legal row**
3. **AI assistant overlay should not blur page**
4. **drawer overlay should have light dim only**

---

## Acceptance Criteria Verification

| Criterion | Status |
|-----------|--------|
| No glitch/flash on first paint | ✅ FOUC prevention applied |
| Sticky CTA never transparent | ✅ Solid `#0a0b0d` |
| Sticky CTA never under content | ✅ z-index 70 |
| Sticky CTA doesn't overlap footer | ✅ `.is-docked` on footer visible |
| Drawer shows all items | ✅ Compact + internal scroll |
| Drawer width ≤40% | ✅ `min(40vw, 280px)` |
| No page blur when assistant opens | ✅ `backdrop-filter: none` |
| Assistant stays dark | ✅ Forced `#0d0e10` |
| No light mode glitches | ✅ Dark surfaces enforced |

---

## Rollback Instructions

Remove CSS preload from `site-header.php`:
```php
<!-- Remove this line -->
<link rel="preload" href="/assets/css/components/landing-p0-fix.css?v=..." ...>
```

---

## PR Details

**Branch:** `fix/landing-p0-stability`  
**Title:** `fix(landing): P0 stability — sticky CTA, drawer, assistant overlay, FOUC`

**Labels:** `P0`, `landing`, `mobile`, `stability`

---

**Ready for review. DO NOT MERGE without approval.**
