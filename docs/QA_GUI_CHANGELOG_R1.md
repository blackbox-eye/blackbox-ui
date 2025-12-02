# QA GUI Change Log — Round 1

- `tests/e2e/sso-gui.spec.ts` — Full Playwright suite covering core token validation and ALPHA matrix flows with diagnostics attachments.
- `src/router/index.ts` — TypeScript guard enforcing JWT validity, fallback routing, QA overrides and structured decision logging.
- `assets/js/router-guard.js` — Browser bootstrapper mirroring guard logic for PHP pages, including cookie desync detection and QA override handling.
- `includes/components/qa-debug-panel.php` — Server-rendered QA panel exposing token metadata, countdowns, redirect trace and health endpoint wiring.
- `assets/js/qa-mode.js` — QA runtime hooks for suppressing prod overlays, logging router snapshots, polling health checks and driving panel interactions.
- `includes/admin-footer.php` — Footer badge + QA chip injection for admin layout and conditional panel include.
- `includes/site-footer.php` — Marketing footer badge and QA panel inclusion toggle tied to BBX_QA_MODE.
- `assets/css/admin.css` — Admin palette updates for command deck/footer plus cross-browser blur/backdrop styling.
- `assets/css/qa-mode.css` — Styling for QA badge/panel, responsive layout constraints and visual hierarchy.
- `agent-login.php` — Conditional QA panel include and script hooks for the login surface.
- `package.json` — Added `qa:gui` / `qa:gui:ci` scripts and cross-env dependency for forcing QA_MODE during test runs.
