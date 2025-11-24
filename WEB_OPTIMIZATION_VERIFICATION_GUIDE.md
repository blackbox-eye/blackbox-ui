# Web Optimization - Quick Verification Guide

## Hurtig Verificering af Web-Optimering

Denne guide giver et hurtigt overblik over hvordan man verificerer at alle web-optimeringer fungerer korrekt.

---

## 1. Lazy Loading Verificering ✅

### Browser DevTools Test

**Chrome/Edge DevTools:**
1. Åbn hjemmesiden i Chrome/Edge
2. Åbn DevTools (F12)
3. Gå til **Network** tab
4. Filtrer på **Img**
5. Scroll langsomt ned på siden
6. ✅ **Forventet:** Billeder loader først når de kommer i viewport

**Firefox DevTools:**
1. Åbn hjemmesiden i Firefox
2. Åbn DevTools (F12)
3. Gå til **Network** tab
4. Filtrer på **Images**
5. Disable cache (højreklik → "Disable Cache")
6. Reload siden og scroll
7. ✅ **Forventet:** Billeder loader on-demand

### Test Pages
- `/blog.php` - Blog listing images
- `/blog-post.php` - Featured image og related posts
- `/agent-login.php` - Logo image

### Expected Attributes
```html
<img src="..." alt="..." loading="lazy" class="...">
```

---

## 2. Minification Verificering ✅

### File Size Check

**Via SSH/FTP:**
```bash
# Check file sizes
ls -lh assets/css/
ls -lh assets/js/

# Expected output:
# marketing.css: ~2.5 KB
# marketing.min.css: ~1.4 KB (44% smaller)
# admin.css: ~5.0 KB
# admin.min.css: ~2.6 KB (48% smaller)
# site.js: ~41 KB
# site.min.js: ~18 KB (56% smaller)
```

**Via Browser:**
1. Åbn hjemmesiden
2. Åbn DevTools (F12)
3. Gå til **Network** tab
4. Reload siden
5. Find `marketing.min.css` eller `admin.min.css`
6. ✅ **Verificer:** Size column viser minified size (~1.4 KB)

### Production vs Debug Mode

**Production Mode (default):**
```html
<!-- Loads minified -->
<link rel="stylesheet" href="/assets/css/marketing.min.css">
<script src="assets/js/site.min.js" defer></script>
```

**Debug Mode (BBX_DEBUG_RECAPTCHA = true):**
```html
<!-- Loads unminified -->
<link rel="stylesheet" href="/assets/css/marketing.css">
```

### Test URLs
- Any marketing page (index.php, products.php, etc.)
- Any admin page (dashboard.php, admin.php, etc.)

---

## 3. SRI (Subresource Integrity) Verificering ✅

### Chart.js SRI Check

**Browser DevTools:**
1. Åbn `/dashboard.php`
2. Åbn DevTools (F12)
3. Gå til **Elements/Inspector** tab
4. Find `<script>` tag for Chart.js
5. ✅ **Verificer følgende attributer:**

```html
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" 
        integrity="sha384-OLBgp1GsljhM2TJ+sbHjaiH9txEUvgdDTAzHv2P24donTt6/529l+9Ua0vFImLlb"
        crossorigin="anonymous">
</script>
```

### Console Check
1. Åbn Dashboard
2. Åbn DevTools Console (F12)
3. ✅ **Forventet:** Ingen SRI-related errors
4. ❌ **Hvis fejl:** "Failed to find a valid digest in the 'integrity' attribute"

### CDN Resources Overview

| Resource | SRI Status | Location |
|----------|------------|----------|
| Chart.js | ✅ Implemented | dashboard.php |
| Tailwind CSS | ⚠️ Not applicable (JIT) | site-header.php |
| Google Fonts | ⚠️ Not applicable (dynamic) | site-header.php |
| Calendly | ⚠️ Not applicable (dynamic) | demo.php |

---

## 4. Accessibility Verificering ✅

### Keyboard Navigation Test

**Basic Flow:**
1. Åbn hjemmesiden
2. **TAB** - Skal springe til "Skip to content" link
3. **ENTER** - Springer til main content
4. **TAB** flere gange - Går gennem alle links i navigation
5. **ENTER** på link - Navigerer til side
6. **TAB** til mobile menu knap (på mobile)
7. **ENTER** - Åbner mobile menu
8. **ESC** - Lukker mobile menu

**✅ Verificer:**
- Fokus indicator er synlig (gul outline)
- Tab order er logisk
- Alle interaktive elementer er keyboard accessible
- Skip-to-content link vises ved focus

### ARIA Attributes Check

**Contact Form:**
1. Åbn `/contact.php`
2. Åbn DevTools → Elements
3. Find `<form id="contact-form">`
4. ✅ **Verificer følgende attributer:**

```html
<form aria-label="Contact form">
    <input type="text" 
           required 
           aria-required="true">
    
    <div id="contact-form-error"
         role="alert"
         aria-live="polite">
    
    <div id="contact-form-success"
         role="status"
         aria-live="polite">
```

### Mobile Menu Test

**Touch Device:**
1. Åbn på mobile/tablet
2. Tap hamburger menu
3. ✅ Menu åbner med smooth animation
4. ✅ Overlay vises bag menu
5. ✅ Body scroll disabled
6. Tap overlay eller ✕ button
7. ✅ Menu lukker

**Keyboard:**
1. Tab til hamburger button
2. Enter/Space aktiverer menu
3. ✅ Fokus flytter til første link
4. Escape lukker menu
5. ✅ Fokus returnerer til hamburger button

### Color Contrast Test

**Automatic Tool:**
1. Installer [axe DevTools](https://www.deque.com/axe/devtools/) extension
2. Åbn hvilken som helst side
3. Kør axe scan
4. ✅ **Forventet:** Ingen contrast issues

**Manual Check:**
- White text (#EAEAEA) on dark bg (#101419) ✅
- Amber accent (#FFC700) on dark bg ✅
- Medium emphasis text (#B0B8C6) on dark bg ✅

### Reduced Motion Test

**Browser Settings:**
1. **Windows:** Settings → Accessibility → Display → "Show animations"
2. **macOS:** System Preferences → Accessibility → Display → "Reduce motion"
3. **Browser DevTools:** 
   - Chrome: Cmd/Ctrl+Shift+P → "Emulate CSS prefers-reduced-motion"
   - Firefox: about:config → ui.prefersReducedMotion = 1

**Test:**
1. Enable reduced motion
2. Reload hjemmeside
3. ✅ **Verificer:**
   - Ingen animations (fade-ins, slides, etc.)
   - Matrix rain canvas skjult
   - Transitions er minimal/none
   - Funktionalitet bevaret

---

## 5. Performance Metrics Test

### Lighthouse Audit (Chrome)

**Kør Audit:**
1. Åbn Chrome
2. Åbn DevTools (F12)
3. Gå til **Lighthouse** tab
4. Vælg **Desktop** eller **Mobile**
5. Vælg kategorier:
   - ✅ Performance
   - ✅ Accessibility
   - ✅ Best Practices
   - ✅ SEO
6. Klik **Analyze page load**

**✅ Forventede Scores (Desktop):**
- Performance: 90+ 🟢
- Accessibility: 95+ 🟢
- Best Practices: 90+ 🟢
- SEO: 90+ 🟢

**✅ Forventede Scores (Mobile):**
- Performance: 85+ 🟢
- Accessibility: 95+ 🟢
- Best Practices: 90+ 🟢
- SEO: 90+ 🟢

### Network Performance Check

**DevTools Network Tab:**
1. Åbn DevTools → Network
2. Disable cache
3. Reload side (Cmd/Ctrl+Shift+R)
4. ✅ **Verificer:**
   - Total page size < 150 KB (first load)
   - DOMContentLoaded < 1.5s
   - Load event < 2.5s
   - Minified CSS/JS loads

---

## 6. Cross-Browser Testing

### Test Matrix

| Browser | Version | Platform | Status |
|---------|---------|----------|--------|
| Chrome | Latest | Desktop | ✅ |
| Firefox | Latest | Desktop | ✅ |
| Safari | Latest | macOS | ⚠️ Test |
| Edge | Latest | Desktop | ✅ |
| Chrome | Latest | Android | ⚠️ Test |
| Safari | Latest | iOS | ⚠️ Test |

### Quick Test Checklist
- [ ] Navigation fungerer
- [ ] Lazy loading fungerer
- [ ] Forms kan submitters
- [ ] Mobile menu fungerer
- [ ] Keyboard navigation fungerer
- [ ] Ingen console errors

---

## 7. Automated CI/CD Checks

### GitHub Actions Workflows

**Lighthouse CI:**
```yaml
# Runs on every push to main
.github/workflows/lighthouse.yml
```

**Visual Regression:**
```yaml
# Runs on every push
.github/workflows/visual-regression.yml
```

**CodeQL Analysis:**
```yaml
# Runs weekly + on security changes
.github/workflows/codeql-analysis.yml
```

### Check Workflow Status
1. Gå til GitHub repository
2. Klik på **Actions** tab
3. ✅ Verify all workflows pass (green checkmarks)

---

## Quick Command Reference

### Generate SRI Hash
```bash
# For any CDN resource
curl -s <CDN_URL> | openssl dgst -sha384 -binary | openssl base64 -A
```

### Check File Sizes
```bash
# Check minified vs original
du -h assets/css/*.css
du -h assets/js/*.js
```

### Grep for Lazy Loading
```bash
# Find all images with/without lazy loading
grep -r "loading=" --include="*.php" .
grep -r "<img" --include="*.php" . | grep -v "loading="
```

### Check SRI Implementation
```bash
# Find all script/link tags with integrity
grep -r "integrity=" --include="*.php" .
```

---

## Troubleshooting

### Issue: Minified Files Not Loading

**Check:**
1. Verify `BBX_DEBUG_RECAPTCHA` is not set to `true` in production
2. Check `.htaccess` environment variables
3. Verify minified files exist in `assets/` directories

**Fix:**
```php
// In .htaccess or environment
SetEnv BBX_DEBUG_RECAPTCHA "0"
```

### Issue: SRI Hash Mismatch

**Symptom:** Console error: "Failed to find a valid digest"

**Fix:**
1. Regenerate SRI hash:
   ```bash
   curl -s https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js | \
   openssl dgst -sha384 -binary | openssl base64 -A
   ```
2. Update `integrity` attribute in PHP file

### Issue: Images Not Lazy Loading

**Check:**
1. Verify `loading="lazy"` attribute exists
2. Check browser compatibility (IE11 not supported)
3. Clear browser cache

**Debug:**
```javascript
// In browser console
document.querySelectorAll('img[loading="lazy"]').length
// Should return > 0
```

---

## Summary Checklist

Quick verification før deployment:

- [ ] ✅ Lazy loading på alle billeder
- [ ] ✅ Minified CSS/JS loader i produktion
- [ ] ✅ SRI hash på Chart.js er korrekt
- [ ] ✅ Keyboard navigation fungerer
- [ ] ✅ ARIA attributes er korrekte
- [ ] ✅ Color contrast overholder WCAG AA
- [ ] ✅ Reduced motion respekteres
- [ ] ✅ Mobile menu fungerer på touch devices
- [ ] ✅ Lighthouse scores > 85
- [ ] ✅ No console errors

---

**Reference:** Se WEB_OPTIMIZATION_AUDIT.md for detaljeret dokumentation.

**Support:** ops@blackbox.codes for spørgsmål om web optimization.
