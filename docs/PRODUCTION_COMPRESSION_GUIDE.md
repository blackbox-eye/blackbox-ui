# Production Compression & Performance Guide

**Sprint 9 Batch 3** | Last Updated: 2025-12-23

---

## Overview

This guide documents the compression and caching configuration for Blackbox EYE™ production deployment. The PHP built-in dev server ignores these rules, so local Lighthouse scores will be lower than production.

---

## Current Configuration Status

| Feature | Apache (.htaccess) | Status |
|---------|-------------------|--------|
| Gzip (mod_deflate) | ✅ Configured | Lines 26-31 |
| Brotli (mod_brotli) | ✅ Configured | Lines 34-37 |
| Browser Caching | ✅ Configured | Lines 40-67 |
| Cache-Control Headers | ✅ Configured | Lines 70-92 |
| Security Headers | ✅ Configured | Lines 94-99 |

---

## Expected Transfer Size Reduction

| Asset Type | Uncompressed | Gzip (~70%) | Brotli (~75%) |
|------------|--------------|-------------|---------------|
| tailwind.full.css | 43 KB | ~13 KB | ~11 KB |
| marketing.min.css | 408 KB | ~122 KB | ~102 KB |
| custom-ui.css | 180 KB | ~54 KB | ~45 KB |
| site.min.js | 44 KB | ~13 KB | ~11 KB |
| **Total CSS/JS** | **~675 KB** | **~202 KB** | **~169 KB** |

**Expected reduction: 70-75%**

---

## Expected Lighthouse Impact

| Metric | Local Dev (no compression) | Production (with compression) |
|--------|---------------------------|-------------------------------|
| Performance | 0.55-0.60 | **0.80-0.90** |
| FCP | 6.5-7.5 s | **1.5-2.5 s** |
| LCP | 8.0-9.0 s | **2.5-3.5 s** |
| TTI | 8.0-9.0 s | **3.0-4.0 s** |

---

## Apache Configuration (Current)

The `.htaccess` file includes:

```apache
# Enable Gzip Compression
<IfModule mod_deflate.c>
AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript
AddOutputFilterByType DEFLATE application/xml application/xhtml+xml application/rss+xml
AddOutputFilterByType DEFLATE application/javascript application/x-javascript
AddOutputFilterByType DEFLATE font/ttf font/otf font/woff font/woff2 image/svg+xml
</IfModule>

# Enable Brotli Compression (if available)
<IfModule mod_brotli.c>
AddOutputFilterByType BROTLI_COMPRESS text/html text/plain text/xml text/css text/javascript
AddOutputFilterByType BROTLI_COMPRESS application/javascript application/json application/xml
</IfModule>
```

### Apache Module Requirements

Ensure these modules are enabled on your Apache server:

```bash
# Enable required modules
sudo a2enmod deflate
sudo a2enmod brotli   # Apache 2.4.26+
sudo a2enmod expires
sudo a2enmod headers

# Restart Apache
sudo systemctl restart apache2
```

---

## Nginx Configuration (Alternative)

If deploying to Nginx, add this to your server block:

```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name blackbox.codes www.blackbox.codes;
    root /var/www/blackbox-ui;
    index index.php index.html;

    # ═══════════════════════════════════════
    # GZIP COMPRESSION
    # ═══════════════════════════════════════
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_min_length 256;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/json
        application/javascript
        application/x-javascript
        application/xml
        application/xml+rss
        application/xhtml+xml
        image/svg+xml
        font/ttf
        font/otf
        font/woff
        font/woff2;

    # ═══════════════════════════════════════
    # BROTLI COMPRESSION (requires ngx_brotli module)
    # ═══════════════════════════════════════
    brotli on;
    brotli_comp_level 6;
    brotli_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/json
        application/javascript
        application/xml
        image/svg+xml;

    # ═══════════════════════════════════════
    # BROWSER CACHING
    # ═══════════════════════════════════════
    
    # Static assets (1 year)
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|otf|webp)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        add_header Vary "Accept-Encoding";
    }

    # HTML/PHP (short cache)
    location ~* \.(html|php)$ {
        expires 1h;
        add_header Cache-Control "public, must-revalidate";
    }

    # ═══════════════════════════════════════
    # SECURITY HEADERS
    # ═══════════════════════════════════════
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # PHP handling
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    }
}
```

### Nginx Brotli Module Installation

```bash
# Ubuntu/Debian
sudo apt install libnginx-mod-brotli

# Or compile from source
git clone https://github.com/google/ngx_brotli.git
cd ngx_brotli && git submodule update --init
# Add --add-dynamic-module=/path/to/ngx_brotli to nginx configure
```

---

## Verification Commands

### Test Compression (Apache)

```bash
# Check gzip
curl -H "Accept-Encoding: gzip" -I https://blackbox.codes/assets/css/tailwind.full.css

# Check brotli
curl -H "Accept-Encoding: br" -I https://blackbox.codes/assets/css/tailwind.full.css

# Expected header: Content-Encoding: gzip (or br)
```

### Test Compression (Nginx)

```bash
# Test with verbose output
curl -H "Accept-Encoding: gzip, deflate, br" -sI https://blackbox.codes/ | grep -i encoding
```

---

## CDN Considerations

If using Cloudflare or similar CDN:

1. **Auto-minify**: Enable for HTML, CSS, JS
2. **Brotli**: Enable in Speed → Optimization
3. **Polish**: Enable for image optimization
4. **Caching Level**: Standard or Aggressive
5. **Browser Cache TTL**: Respect Existing Headers

---

## Checklist Before Production

- [ ] Apache mod_deflate enabled
- [ ] Apache mod_brotli enabled (optional but recommended)
- [ ] Apache mod_expires enabled
- [ ] Apache mod_headers enabled
- [ ] Test compression with curl
- [ ] Verify Cache-Control headers
- [ ] Run Lighthouse on production URL
- [ ] Confirm Performance score > 0.80

---

## Related Files

| File | Purpose |
|------|---------|
| `.htaccess` | Apache configuration (compression, caching, security) |
| `assets/css/critical.css` | Inlined above-the-fold CSS |
| `includes/site-header.php` | CSS preload + async pattern |
| `includes/site-footer.php` | Deferred JS loading |
