# FEJLFINDING LOG (Scroll-Lock & Layout Collapse)

Purpose

- Single source of truth for scroll-lock/layout-collapse investigation.
- Record every hypothesis, test, finding, and outcome chronologically.

How to log

- For each item, capture: Date/UA/Page, Hypothesis, Action, Result, Conclusion, Next step.
- Keep entries concise; one bullet per hypothesis.

Workflow (approved “Udelukkelses-metode”)

1. Baseline & repro: device/UA, page, steps, whether lock happens on load or after interaction.
2. Global CSS/JS scan: find global locks, hijack libs, overlays, CSS integrity.
3. Runtime inspection: computed styles on html/body, scrollchain behavior, iOS body-freeze.
4. Layout collapse: zero-height parents, floats/absolute positioning, whitespace inline-block issues.
5. Scrollbar compensation: verify padding-right when overflow hidden removes scrollbar.
6. Modal lifecycle: ensure open/close cleans up classes/styles and restores scroll position.
7. Z-index/overlay: ensure hidden overlays use pointer-events:none and escape stacking contexts.

Log Sections

Global CSS/JS Scan (Phase 1)

- CSS locks (html/body): overflow:hidden, position:fixed, height:100vh, padding-right compensation.
- JS hijackers: Lenis, Locomotive, body-scroll-lock, addEventListener on wheel/touchmove with preventDefault/passive flags.
- Overlays/modals: hidden overlays with pointer-events:auto or z-index traps.
- CSS integrity: missing braces, 404/MIME issues, truncated files.

Findings

- [x] 2026-01-03: CSS locks scan.
- [x] 2026-01-03: JS hijackers scan.
- [x] 2026-01-03: Layout collapse (agent-access) brace check.

Detailed Findings (2026-01-03)

- CSS locks (html/body)
  - Global scrollbar removal on root via WebKit selectors and scrollbar-width none in [assets/css/critical.css](assets/css/critical.css#L1-L28) and minified twin [assets/css/critical.min.css](assets/css/critical.min.css#L1-L7). Body set to min-height 100vh at [assets/css/critical.css](assets/css/critical.css#L35-L54). No direct overflow hidden on html/body; guard rule forces overflow-y:auto when no overlay classes at [assets/css/critical.css](assets/css/critical.css#L58-L68).
  - Scroll contract safety style sets html, body overflow-y:auto and touch-action pan-y at [assets/css/scroll-contract.min.css](assets/css/scroll-contract.min.css#L1). Also defines landing/modals classes but keeps overflow-y:auto.
  - Marketing CSS contains multiple component-level overflow:hidden declarations (e.g., [assets/css/marketing.css](assets/css/marketing.css#L1687), [assets/css/marketing.css](assets/css/marketing.css#L4362), [assets/css/marketing.css](assets/css/marketing.css#L5318)) but none target html/body.
- Height 100vh checks
  - Body min-height 100vh in [style.css](style.css#L37) and [assets/css/critical.css](assets/css/critical.css#L70-L74). Hero/sections min-height 100vh in marketing and scroll-contract; not applied to body/html.
- JS hijackers / wheel-touch listeners
  - No Lenis/Locomotive/body-scroll-lock strings found across JS/CSS.
  - Wheel/touchmove listeners present in detection/failsafe code in [assets/js/site.min.js](assets/js/site.min.js#L1) (passive:true; used to detect and auto-unlock body) and in test harness [tests/production-scroll-debug.spec.js](tests/production-scroll-debug.spec.js#L198-L225).
- Layout collapse / brace integrity (agent-access)
  - Mobile agent-access overrides are syntactically valid; media queries closed in [assets/css/components/agent-access-mobile.css](assets/css/components/agent-access-mobile.css#L1-L120).
  - agent-access page styles present in [assets/css/marketing.css](assets/css/marketing.css#L2887-L3000) and minified counterpart [assets/css/marketing.min.css](assets/css/marketing.min.css#L1). No obvious missing braces observed in the extracted file.

Forensic Analysis: Dec 16 Branch (origin/fix/i18n-mobile-theme-hardening vs origin/main)

- Critical scroll guards removed: Entire [assets/css/critical.css](assets/css/critical.css) deleted in branch; this file contained global scrollbar hide plus explicit unlock guards (`overflow-y:auto !important`, `position:static`, `touch-action:auto`) when no overlays were present. Loss of this guard can allow latent locks to persist.
- Global “scroll contract” removed: [assets/css/scroll-contract.css](assets/css/scroll-contract.css) deleted. This file enforced `overflow-y:auto !important`, `height:auto`, `position:relative`, and `touch-action:pan-y !important` on html/body and override classes (`mobile-menu-open`, `modal-open`, `drawer-open`). Its removal re-opens the possibility of scroll lock via other styles.
- Component CSS deletions touching mobile overlays and sticky elements: multiple component sheets removed in the branch (e.g., components/sticky-cta.css, mobile-baseline.css, mobile-nav-scale.css, landing-p0-fix.css, mobile-final-polish.css). These previously hid/neutralized sticky CTA and set mobile-safe overflow behavior; their removal could let fixed/sticky elements capture scroll.
- JS source of site.min.js: built from [assets/js/site.js](assets/js/site.js) (diff is large at ~5k lines). This is the upstream source for [assets/js/site.min.js](assets/js/site.min.js), which wires scroll/overlay listeners. Branch shows major rewrites (insertions+deletions); needs targeted review for body lock/unlock logic.
- Marketing.css loading scope: [includes/site-header.php](includes/site-header.php#L333-L360) loads marketing.css (minified in prod) for all non-admin pages, including agent-access.php and demo.php. Any overflow/height/fixed wrappers defined there will apply site-wide on these pages.

Remediation (2026-01-03)

- ScrollGuard restored: Added [assets/css/scroll-guard.css](assets/css/scroll-guard.css) and loaded after all styles in [includes/site-header.php](includes/site-header.php#L333-L362). Forces `overflow-y:auto`, `height:auto`, `position:static`, `touch-action:pan-y`, and ensures a visible scrollbar to neutralize global locks.
- Z-index neutralization: Reduced assistant stack from 2147483647 to 9999 in [assets/css/components/alphabot-ios-fix.css](assets/css/components/alphabot-ios-fix.css#L20-L64) and [assets/css/custom-ui.css](assets/css/custom-ui.css#L2579-L2586;assets/css/custom-ui.css#L6359-L6384). Added `pointer-events:none` on `.bbx-command-rail` with widget pointer events restored to avoid invisible overlay walls. Overlay baseline remains `pointer-events:none` except when explicitly visible.
- Root cause logged: Primary cause = deletion of critical.css/scroll-contract.css; contributing cause = Max-Int z-index on assistant rail/overlay creating event interception risk.

Notes

- Mark each item above as [x] when addressed; append detailed bullets below with evidence and paths.
