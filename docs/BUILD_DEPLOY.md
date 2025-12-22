# Build & Deploy Guide

> Last updated: 2025-12-22 (Sprint 2)

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
$css_version = '1.6.15';  // Bump when CSS changes
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

# 4. Stage and commit
git add -A
git commit -m "feat/fix: description"

# 5. Push to origin
git push origin main

# 6. Verify live (FTP auto-deploys from main)
curl -sI "https://blackbox.codes/assets/css/marketing.min.css?v=X.X.X" | grep content-type
# Should return: content-type: text/css
```

### CI/CD Notes

Currently using **manual deployment** via FTP sync from `main` branch.

**Future improvement:** Add GitHub Action to run `npm run build:css` on push.

---

## Component CSS Files

New component CSS lives in `assets/css/components/`:

| File | Purpose |
|------|---------|
| `bbx-icons.css` | Icon system + tooltip styles (Sprint 2: consistent icon styling) |
| `bbx-snackbar.css` | Toast notification styles |

These are **not minified** and loaded on-demand by components that need them.

---

## Sprint 2 Changes (Auth-UX Polish)

### New Features
- **SSO buttons**: Now clickable with tooltip explaining disabled state, "Request SSO access" link
- **MFA step indicator**: Shows "Step 2 of 2" with privacy notice
- **Favorites → Pinned**: Renamed terminology, snackbar says "pinned to quick switch"
- **Recent Activity**: Dummy API with localStorage, realistic timestamps
- **Reduced motion**: All animated elements respect `prefers-reduced-motion`
- **Footer polish**: Enhanced contrast on legal links, tooltips on cert badges

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
