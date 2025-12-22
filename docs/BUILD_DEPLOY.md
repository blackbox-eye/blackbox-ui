# Build & Deploy Guide

> Last updated: 2025-12-22

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
| CCS Login (`ccs-login.php`) | `ccs-login.css` | Dedicated styles, not minified |
| Agent Access (`agent-access.php`) | `admin.min.css` + console-selector inline | Uses admin styles |
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

# 2. Verify no uncommitted changes
git status

# 3. Stage and commit
git add -A
git commit -m "feat/fix: description"

# 4. Push to origin
git push origin main

# 5. Verify live (FTP auto-deploys from main)
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
| `bbx-icons.css` | Icon system + tooltip styles |
| `bbx-snackbar.css` | Toast notification styles |

These are **not minified** and loaded on-demand by components that need them.

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
