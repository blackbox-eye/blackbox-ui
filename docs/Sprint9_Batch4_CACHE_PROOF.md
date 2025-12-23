# Sprint 9 Batch 4 — Cache-Proof Mobile Scale

**Date:** 2025-12-23  
**Focus:** Eliminate manual cache-clear requirement for CSS/JS updates  

---

## Problem Statement

Real-device testing (iPhone 16 Pro / Brave) showed stale CSS despite correct code. Root cause: static `$css_version = '1.6.19'` required manual bumps, and browser `immutable` caching prevented refresh from fetching new files.

---

## Solution: Automatic filemtime() Versioning

### A) Asset Versioning (site-header.php)

**Before:**
```php
$css_version = '1.6.19'; // Manual bump required
<link href="/assets/css/tokens.css?v=<?= $css_version ?>">
```

**After:**
```php
function bbx_asset_version(string $path): string {
    $full_path = $asset_base . '/' . ltrim($path, '/');
    return file_exists($full_path) 
        ? substr(md5(filemtime($full_path)), 0, 8) 
        : '1.6.20';
}

<link href="/assets/css/tokens.css?v=<?= bbx_asset_version('css/tokens.css') ?>">
```

**Result:** Each file gets unique 8-char hash based on modification time. Deploy = instant cache invalidation.

### B) .htaccess Cache Headers

| Resource Type | Cache-Control | Rationale |
|---------------|---------------|-----------|
| CSS/JS | `max-age=31536000, immutable` | Long cache, versioned URLs handle invalidation |
| Images/Fonts | `max-age=31536000, immutable` | Rarely change, max performance |
| HTML/PHP | `no-cache, must-revalidate` | **Critical:** Always fetch fresh to get new asset URLs |

**Key change:** HTML from `max-age=3600` → `no-cache` ensures browser always checks for new asset versions.

---

## Files Changed

| File | Change |
|------|--------|
| [includes/site-header.php](../includes/site-header.php) | Added `bbx_asset_version()` function, updated all CSS/JS refs |
| [.htaccess](../.htaccess) | HTML Cache-Control: `no-cache, must-revalidate` |
| [docs/MOBILE_CACHE_TROUBLESHOOT.md](MOBILE_CACHE_TROUBLESHOOT.md) | New: 10-line troubleshooting guide |

---

## Verification

### Asset Versions (curl output)
```
tokens.css?v=ed2d64a8
marketing.min.css?v=36c18936
config.js?v=25582369
motion-safe.css?v=5c658434
```

### Regression Tests
```
a11y-hardgate: 30/30 passed (18.0s)
npm run build:css: ✓
```

---

## Why This Prevents iOS/Brave Stale CSS

1. **HTML never cached** → Browser always fetches fresh PHP output
2. **Fresh PHP** → Contains new `?v=XXXXXXXX` query strings (filemtime-based)
3. **New query string** → Browser treats as different URL → fetches fresh CSS
4. **CSS is immutable** → Once fetched, cached forever (until next file change)

**Result:** Normal refresh delivers updated styles. No manual cache-clear needed.

---

## Commit

```
feat(sprint9): Cache-proof CSS/JS versioning via filemtime()

- Add bbx_asset_version() helper using md5(filemtime()) for auto-invalidation
- Update all CSS/JS asset refs to use per-file versioning
- Set HTML Cache-Control to no-cache for fresh asset URL discovery
- Add MOBILE_CACHE_TROUBLESHOOT.md

Verified: a11y-hardgate 30/30, unique version hashes per file
```
