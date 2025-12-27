# PR #2: Alphabot/CTA Visual Fix & Debug Tools

## Summary

Fixes the black/broken appearance of the Alphabot AI Assistant toggle on iOS browsers and adds developer debug tools for scroll-lock diagnosis.

### Issue: Black Alphabot Toggle

The Alphabot toggle was appearing as a solid black rectangle on iOS Safari/Brave/DuckDuckGo instead of the expected glass-effect styling.

### Root Cause

CSS load order conflict:

1. [custom-ui.css](assets/css/custom-ui.css#L2285) - Defines `.alphabot-toggle` with proper styling
2. [liquid-glass.css](assets/css/components/liquid-glass.css#L321) - Overrides with `var(--bbx-glass-fallback-strong)` which is `rgba(8, 12, 18, 0.96)` (near-black)
3. [alphabot-ios-fix.css](assets/css/components/alphabot-ios-fix.css#L51) - Should override but didn't set background

`liquid-glass.css` loads last and its fallback colors are intentionally dark for non-blur browsers, but the alphabot toggle needs the glass effect, not the fallback.

### Fix Applied

Updated [alphabot-ios-fix.css](assets/css/components/alphabot-ios-fix.css#L51-L72):

```css
.alphabot-toggle {
  position: relative !important;
  z-index: 2147483647 !important;
  pointer-events: auto !important;
  cursor: pointer !important;

  /* CRITICAL FIX: Override liquid-glass.css dark fallback */
  -webkit-backdrop-filter: blur(16px) !important;
  backdrop-filter: blur(16px) !important;
  background: rgba(13, 15, 17, 0.85) !important;
  border: 1px solid rgba(138, 123, 106, 0.35) !important;
  color: var(--text-high-emphasis, #e8e8e8) !important;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(138, 123, 106, 0.15) !important;
}

@supports not (backdrop-filter: blur(1px)) {
  .alphabot-toggle {
    background: rgba(13, 15, 17, 0.98) !important;
  }
}
```

### Debug Panel Feature

Added visual debug panel for development ([site.js](assets/js/site.js#L316-L416)):

**Enable:** Add `?debugUI` to any page URL

**Features:**

- Real-time scroll Y position
- Body overflow state
- Body position state
- Active lock classes (`mobile-menu-open`, `alphabot-locked`, etc.)
- Touch-action state
- Drawer open state
- Alphabot open state
- "Force Unlock Scroll" button

**Screenshot:**

```
┌─────────────────────────────────┐
│ 🔧 Debug Panel              [×] │
├─────────────────────────────────┤
│ Scroll Y:        342            │
│ Body overflow:   auto / auto    │
│ Body position:   static         │
│ Classes:         -              │
│ Touch action:    auto           │
│ Drawer open:     -              │
│ Alphabot open:   -              │
├─────────────────────────────────┤
│   [Force Unlock Scroll]         │
└─────────────────────────────────┘
```

### Playwright Tests Added

New test file: [tests/ios-scroll-lock.spec.js](tests/ios-scroll-lock.spec.js)

**Alphabot Visual Tests:**

- `alphabot toggle should not appear solid black` - Verifies background isn't `rgb(0, 0, 0)`
- `alphabot toggle should be visible and clickable` - Verifies `pointer-events: auto` and `opacity > 0`

**Sticky CTA Visual Tests:**

- `sticky CTA should have proper styling when visible` - Verifies CTA isn't pure black

### Files Changed

| File                                                                                     | Change                                                     |
| ---------------------------------------------------------------------------------------- | ---------------------------------------------------------- |
| [assets/css/components/alphabot-ios-fix.css](assets/css/components/alphabot-ios-fix.css) | Added explicit background/backdrop-filter for toggle       |
| [assets/js/site.js](assets/js/site.js)                                                   | Added `initDebugPanel()` function with visual debug UI     |
| [tests/ios-scroll-lock.spec.js](tests/ios-scroll-lock.spec.js)                           | New Playwright tests for scroll-lock and visual appearance |

### CSS Collision Analysis

**Identified Conflicts:**

| Element                 | File 1             | File 2           | Resolution                            |
| ----------------------- | ------------------ | ---------------- | ------------------------------------- |
| `.alphabot-toggle`      | custom-ui.css      | liquid-glass.css | Fixed in alphabot-ios-fix.css         |
| `.alphabot-panel`       | custom-ui.css      | liquid-glass.css | Already fixed in alphabot-ios-fix.css |
| `body.mobile-menu-open` | landing-p0-fix.css | -                | Fixed (removed position:fixed)        |

**Z-Index Stack (from lowest to highest):**

- 50: Header
- 75: Sticky CTA bar
- 80: Cookie banner
- 90: Modal overlays
- 100: Drawer overlay
- 2147483647: Alphabot (max z-index)

### How to Verify

1. **Alphabot Visual:**

   - Open blackbox.codes on iPhone Safari
   - Look at bottom-right corner for Alphabot toggle
   - **Expected:** Dark glass-effect button with gold border accent
   - **Before fix:** Solid black rectangle

2. **Debug Panel:**
   - Open `https://blackbox.codes/?debugUI`
   - Bottom-left shows debug panel
   - Open/close mobile menu and watch states change in real-time

### Risk Assessment

**Low Risk** - This fix:

- Only affects visual styling of Alphabot toggle
- Debug panel only loads when `?debugUI` parameter present
- Uses `!important` to ensure override but doesn't break other elements
