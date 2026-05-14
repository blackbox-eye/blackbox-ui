# Build & Deploy Guide

> Last updated: 2025-12-22 (Sprint 7)

## CSS Build Process

### When to Run `npm run build:css`

Run the build command **before every commit** that modifies:

| Source File | Output File |
|-------------|-------------|
| `assets/css/marketing.css` | `assets/css/marketing.min.css` |
| `assets/css/admin.css` | `assets/css/admin.min.css` |

```bash
npm run build:css
```

This runs PostCSS with cssnano to minify CSS files.

### Which Pages Load Which CSS

| Page | CSS File | Notes |
|------|----------|-------|
| Marketing pages (`index.php`, `about.php`, etc.) | `marketing.min.css` | Minified for production |
| Admin dashboard (`admin.php`, `dashboard.php`) | `admin.min.css` | Minified for production |
| CCS Login (`ccs-login.php`) | `ccs-login.css` | Dedicated styles (Sprint 2: MFA step, SSO polish) |
| Agent Access (`agent-access.php`) | `admin.min.css` + console-selector inline | Uses admin styles |
| Login card modular (`includes/login-card-modular.php`) | Inline styles | Reusable, supports MFA step indicator |
| Development/Debug | `marketing.css` | Unminified, use `?debug=1` |

### Cache-Busting Versions

Version numbers are controlled in `includes/head.php`:

```php
$css_version = '1.7.0';  // Bump when CSS changes
```

Update this version when:
1. Any CSS file is modified
2. After running `npm run build:css`
3. Before deploying to production

### Deployment Checklist

```bash
# 1. Build minified CSS
npm run build:css

# 2. Run test suite
npm test

# 3. Verify no uncommitted changes
git status

# 4. Create or switch to a feature branch
git switch -c your-branch-name

# 5. Stage and commit on the branch
git add -A
git commit -m "feat/fix: description"

# 6. Push the branch
git push -u origin your-branch-name

# 7. Open a pull request
# 8. Merge to main only after approval

# 9. Production deploy then runs from main via ci.yml

# 10. Verify live after merge to main (ci.yml deploys to origin using FTP with optional TLS/FTPS negotiation where available)
curl -sI "https://blackbox.codes/assets/css/marketing.min.css?v=X.X.X" | grep content-type
# Should return: content-type: text/css
```

### CI/CD Notes

Current production source-of-truth is repo-controlled deployment from `main` via `.github/workflows/ci.yml`, using FTP to origin with optional TLS/FTPS negotiation where available.

Manual cPanel or FTP changes are not canonical unless explicitly owner-approved.

`.github/workflows/cloudflare-pages.yml` is not the current authoritative production deployment path and should be treated as staging, preview, or experimental until separately owner-approved.

This document does not claim verified live header alignment or canonical header ownership. A separate dated header review is required before either claim is relied on as canonical.

See [DEPLOYMENT_SOURCE_OF_TRUTH.md](DEPLOYMENT_SOURCE_OF_TRUTH.md).

**Future improvement:** Add GitHub Action to run `npm run build:css` on push.

---

## Component CSS Files

New component CSS lives in `assets/css/components/`:

| File | Purpose |
|------|---------|
| `bbx-icons.css` | Icon system + tooltip styles (Sprint 2: consistent icon styling) |
| `bbx-snackbar.css` | Toast notification styles |
| `motion-safe.css` | Global `prefers-reduced-motion` support (Sprint 7) |
| `hero-mobile.css` | Unified hero section mobile styles with clamp() (Sprint 7) |
| `console-selector-mobile.css` | Mobile-first console card styles (Sprint 5) |
| `ccs-login-mobile.css` | CCS login mobile polish (Sprint 6) |

These component CSS files are loaded via `site-header.php`:
- `motion-safe.css` - Always loaded (global animation control)
- `hero-mobile.css` - Loaded with `media="(max-width: 768px)"`

---

## Sprint 7 Changes (Final Mobile & Desktop Polish)

### Animation Cleanup
- **No hop/bounce animations**: Console card highlight uses border glow only
- **Reduced motion**: Global `prefers-reduced-motion` support via `motion-safe.css`
- **Feed items**: Opacity fade instead of slide-in

### Quick Switch
- **Pinned star**: Consistent `#c9a227` gold color in both themes
- **Dropdown styling**: Semi-opaque dark surface with brand accent for selected item
- **Mobile**: Sticky position, 160px max width, proper touch targets

### SSO & MFA
- **SSO buttons**: Active buttons in nav drawer triggering modal (not just links)
- **MFA flow**: Step 1 (Credentials) → Step 2 (MFA modal) → Snackbar confirmation

### Navigation Drawer
- **Mobile drawer**: SSO buttons with `data-sso-request` triggers
- **Quick Switch**: Available in both top bar and nav drawer
- **Global SSO modal**: Included via `site-footer.php`

### Hero Sections
- **Unified styling**: `clamp()` for responsive font sizing
- **Mobile**: Centered layout, proper spacing, 48px CTAs

---

## WebKit Testing Setup

WebKit tests require system dependencies. On Ubuntu/Debian:

```bash
npx playwright install-deps webkit
```

Run WebKit tests conditionally:
```bash
npm test -- --project=webkit
```

---

## Test Coverage

```bash
# Run all tests (Chromium only - fastest)
npm test -- --project=chromium

# Run specific test file
npm test -- tests/ccs-login.spec.js

# Run with headed browser (debugging)
npm test -- --headed --project=chromium
```

Current: **380 Chromium tests passing**

---

### Backend-Ready Structures
- `bbx_console_activity` localStorage key (structure matches planned API)
- SSO request link routes to contact form with console parameter
- MFA step indicator ready for real OTP/WebAuthn integration

---

## Troubleshooting

### CSS returns HTML instead of CSS
- Check file exists on server
- Verify `.htaccess` allows CSS extension
- Clear Cloudflare cache

### Styles not updating on live
1. Bump version in `includes/head.php`
2. Clear browser cache
3. Clear Cloudflare cache (if needed)

### Minified CSS out of sync
```bash
npm run build:css
git add assets/css/*.min.css
git commit -m "chore: rebuild minified CSS"
git push
```
