# UI Stacking Model - Blackbox EYE

> **Version**: 1.0.0  
> **Last Updated**: 2025-06-28  
> **Status**: ENFORCED

## Overview

This document defines the z-index stacking order for all UI elements across the Blackbox EYE domain. **All new components MUST follow this model.**

---

## Z-Index Hierarchy (Low → High)

| Layer             | z-index    | Component                             | File Reference                                |
| ----------------- | ---------- | ------------------------------------- | --------------------------------------------- |
| **Background**    | -2 to 0    | Page backgrounds, decorative elements | Various                                       |
| **Content**       | 1-10       | Cards, sections, text content         | marketing.css                                 |
| **Interactive**   | 20-30      | Tooltips, dropdown menus, popovers    | liquid-glass.css:140                          |
| **Navigation**    | 40-50      | Header, nav bar, sticky elements      | critical.css:195                              |
| **Sticky CTA**    | 75         | Sticky CTA bar (bottom of viewport)   | sticky-cta.css:32, liquid-glass.css:249       |
| **Cookie Banner** | 80-85      | Cookie consent overlay                | liquid-glass.css:185, alphabot-ios-fix.css:87 |
| **EYE Assistant** | 96         | Alphabot widget toggle                | liquid-glass.css:318                          |
| **Modals**        | 1000       | Modal overlays and dialogs            | console-selector-mobile.css:311               |
| **Snackbar**      | 10001      | Toast notifications                   | bbx-snackbar.css:22                           |
| **Critical**      | 2147483647 | EYE Assistant panel (top-most)        | alphabot-ios-fix.css:28                       |

---

## Component-Specific Rules

### EYE Assistant (Alphabot)

```
z-index: 2147483647 (MAX_SAFE_INTEGER)
```

- **MUST** always be the topmost element
- **MUST NOT** create global overlays
- **MUST NOT** apply backdrop-filter to body/html
- **MUST NOT** alter page opacity or visibility

### Sticky CTA Bar

```
z-index: 75
```

- Sits below cookie banner (85)
- Sits above all page content
- Must remain clickable when visible

### Cookie Banner

```
z-index: 85
```

- Above sticky CTA
- Below EYE Assistant
- Does not block interaction with alphabot

### Mobile Menu

```
z-index: 50 (menu panel)
z-index: 40 (overlay backdrop)
```

- **MUST NOT** lock body scroll
- **MUST NOT** use position:fixed on body
- Uses transform for show/hide animation

### Modals & Dialogs

```
z-index: 1000
```

- Appears above all navigation
- Uses local scrim (not global body blur)

---

## FORBIDDEN Patterns

The following patterns are **PROHIBITED** across the entire domain:

### ❌ Scroll Lock (CSS)

```css
/* FORBIDDEN - NEVER USE */
body {
  overflow: hidden;
}
html {
  overflow: hidden;
}
body {
  position: fixed;
}
body.mobile-menu-open {
  overflow: hidden;
}
```

### ❌ Scroll Lock (JavaScript)

```javascript
// FORBIDDEN - NEVER USE
document.body.style.overflow = "hidden";
document.documentElement.style.overflow = "hidden";
document.body.style.position = "fixed";
event.preventDefault(); // on touchmove for scroll lock
```

### ❌ Global Page Effects

```css
/* FORBIDDEN - NEVER USE */
body {
  filter: blur(8px);
}
body {
  backdrop-filter: blur(8px);
}
body {
  opacity: 0.5;
}
html::after {
  backdrop-filter: blur();
} /* overlay on html */
```

### ❌ Z-Index Wars

```css
/* FORBIDDEN - Avoid !important escalation */
.my-component {
  z-index: 99999999 !important;
}
```

---

## ALLOWED Patterns

### ✅ Local Component Effects

```css
/* OK - backdrop-filter on specific components */
.header {
  backdrop-filter: blur(12px);
}
.modal-content {
  backdrop-filter: blur(16px);
}
.sticky-cta-bar {
  backdrop-filter: blur(16px);
}
```

### ✅ Non-Blocking Overlays

```css
/* OK - overlay that doesn't lock scroll */
.menu-overlay {
  position: fixed;
  inset: 0;
  pointer-events: auto; /* captures clicks */
  /* NO overflow:hidden on body */
}
```

### ✅ Touch Interaction

```css
/* OK - prevent pull-to-refresh without blocking scroll */
.menu-panel {
  overscroll-behavior: contain;
  touch-action: pan-y pinch-zoom;
}
```

---

## CSS Variables (Recommended)

Use these CSS variables for consistent z-index values:

```css
:root {
  --z-background: 0;
  --z-content: 1;
  --z-interactive: 20;
  --z-navigation: 40;
  --z-sticky-cta: 75;
  --z-cookie-banner: 85;
  --z-alphabot: 96;
  --z-modal: 1000;
  --z-snackbar: 10001;
  --z-critical: 2147483647;
}
```

---

## Testing Requirements

Before merging any UI changes:

1. **iPhone Safari Test** (390×844)

   - Page scrolls freely
   - No bounce-lock on menu open
   - Alphabot is clickable and non-blocking

2. **Desktop Chrome/Firefox** (1440×900)

   - All layers visible at correct order
   - No z-index conflicts

3. **Tablet** (820×1180)
   - Landscape/portrait transitions smooth
   - No scroll lock on orientation change

---

## Audit Commands

Use these commands to audit z-index usage:

```bash
# Find all z-index values
grep -rn "z-index" assets/css/ --include="*.css" | grep -v ".min.css"

# Find scroll-lock patterns (SHOULD BE ZERO)
grep -rn "overflow.*hidden\|position.*fixed" assets/css/ --include="*.css" | grep -i "body\|html"

# Find body/html manipulation in JS (SHOULD BE ZERO)
grep -rn "body.style.overflow\|body.style.position" assets/js/
```

---

## Change Log

| Date       | Author   | Change                |
| ---------- | -------- | --------------------- |
| 2025-06-28 | AI Agent | Initial documentation |

---

## References

- [alphabot-ios-fix.css](assets/css/components/alphabot-ios-fix.css)
- [liquid-glass.css](assets/css/components/liquid-glass.css)
- [sticky-cta.css](assets/css/components/sticky-cta.css)
- [landing-p0-fix.css](assets/css/components/landing-p0-fix.css)
