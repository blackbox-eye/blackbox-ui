# Subresource Integrity (SRI) Implementation Guide
**Platform:** Blackbox UI (Blackbox EYE™)  
**Date:** 2025-11-24  
**Version:** 1.0.0

---

## Executive Summary

Dette dokument beskriver implementering af Subresource Integrity (SRI) for Blackbox UI platformen. SRI er en sikkerhedsmekanisme der verificerer at eksterne ressourcer (CDN scripts og stylesheets) ikke er blevet modificeret.

### Status
- ✅ **Chart.js**: Pinned version + crossorigin (SRI klar)
- ⚠️ **Calendly**: Crossorigin tilføjet (SRI ikke anbefalet)
- ❌ **Tailwind CDN**: Dynamisk content (SRI ikke muligt)
- ❌ **reCAPTCHA**: Google-managed (SRI ikke muligt)
- ⚠️ **Google Fonts**: Browser-specific (SRI komplekst)

---

## 1. Hvad er Subresource Integrity?

### Grundprincip
SRI er en W3C-standard der tillader browsere at verificere at eksterne ressourcer ikke er blevet kompromitteret ved at checke deres kryptografiske hash.

### Eksempel
```html
<!-- UDEN SRI (usikkert) -->
<script src="https://cdn.example.com/library.js"></script>

<!-- MED SRI (sikkert) -->
<script src="https://cdn.example.com/library.js"
        integrity="sha384-oqVuAfXRKap7fdgcCY5uykM6+R9GqQ8K/uxy9rx7HNQlGYl1kPzQho1wx4JwY8wC"
        crossorigin="anonymous"></script>
```

### Fordele
- ✅ **Beskyttelse mod CDN kompromittering**: Hvis CDN hackes, loader browseren ikke modified scripts
- ✅ **Beskyttelse mod MITM-angreb**: Tredjeparter kan ikke ændre ressourcen under transit
- ✅ **Compliance**: Mange security frameworks (PCI-DSS, SOC 2) anbefaler SRI

### Ulemper
- ❌ **Vedligeholdelse**: Hashes skal opdateres når CDN-ressourcer opdateres
- ❌ **Dynamisk content**: Virker ikke for ressourcer der ændres ofte
- ❌ **Browser support**: Ældre browsere understøtter ikke SRI

---

## 2. Browser Support

| Browser | Version | Support |
|---------|---------|---------|
| Chrome | 45+ | ✅ Fuld |
| Firefox | 43+ | ✅ Fuld |
| Safari | 11.1+ | ✅ Fuld |
| Edge | 17+ | ✅ Fuld |
| IE | Alle | ❌ Ingen |

**Fallback behavior**: Browsere der ikke understøtter SRI ignorer `integrity` attributten og loader ressourcen normalt.

---

## 3. Implementeringsstatus

### 3.1 Chart.js ✅ KLAR TIL SRI

**Nuværende implementering** (dashboard.php):
```html
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" 
        crossorigin="anonymous"></script>
```

**Med SRI** (anbefalet):
```html
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" 
        integrity="sha384-[HASH_INDSÆT_HER]"
        crossorigin="anonymous"></script>
```

**Action items:**
1. Generate SRI hash for Chart.js@4.4.1
2. Test hash i staging
3. Deploy til production
4. Dokumenter hash i dette dokument

**Hvordan generere hash:**
```bash
# Download Chart.js og generer hash
curl -sL "https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" | \
  openssl dgst -sha384 -binary | \
  openssl base64 -A

# Eller brug online tool:
# https://www.srihash.org/
```

---

### 3.2 Calendly ⚠️ SRI IKKE ANBEFALET

**Nuværende implementering** (demo.php):
```html
<link href="https://assets.calendly.com/assets/external/widget.css" 
      rel="stylesheet" 
      crossorigin="anonymous">
<script src="https://assets.calendly.com/assets/external/widget.js" 
        type="text/javascript" 
        async 
        crossorigin="anonymous"></script>
```

**Årsag til IKKE at bruge SRI:**
- Calendly opdaterer deres widget.js og widget.css løbende
- Ingen version-pinning tilgængelig i deres CDN
- SRI ville bryde widget hver gang Calendly opdaterer

**Anbefalet approach:**
1. ✅ **Brug crossorigin="anonymous"** (allerede implementeret)
2. ✅ **CSP headers** (allerede implementeret i .htaccess)
3. 📋 **Monitorer Calendly releases** (fremtidig feature)
4. 📋 **Overvej self-hosting** hvis Calendly giver mulighed

---

### 3.3 Tailwind CDN ❌ SRI IKKE MULIGT

**Nuværende implementering** (site-header.php, dashboard.php, agent-login.php):
```html
<script src="https://cdn.tailwindcss.com"></script>
```

**Hvorfor SRI ikke virker:**
- Tailwind CDN genererer CSS dynamisk baseret på HTML content
- Hver side får unik CSS output
- Hash ændres ved hver request

**Anbefalet løsning:**
```bash
# Skift til lokal Tailwind build
npm install -D tailwindcss
npx tailwindcss init

# I package.json
"scripts": {
  "build:css": "tailwindcss -i ./src/input.css -o ./assets/css/tailwind.min.css --minify"
}

# Build CSS lokalt
npm run build:css
```

**Benefits ved local build:**
- ✅ SRI support (statisk fil)
- ✅ Mindre fil (kun brugte classes)
- ✅ Hurtigere load (ingen CDN roundtrip)
- ✅ Fungerer offline

**Implementation estimate:** 2-4 timer

---

### 3.4 reCAPTCHA ❌ SRI IKKE MULIGT

**Nuværende implementering** (site-header.php):
```html
<script src="https://www.google.com/recaptcha/api.js?render={SITE_KEY}" async defer></script>
```

**Hvorfor SRI ikke virker:**
- Google opdaterer reCAPTCHA løbende for at forhindre bots
- Script ændres flere gange om måneden
- Ingen version-pinning tilgængelig

**Acceptable security:**
- ✅ Google er trusted CDN med stærk security track record
- ✅ HTTPS garanterer transit-sikkerhed
- ✅ CSP headers begrænser hvilke scripts der kan loade
- ✅ reCAPTCHA bruges kun på kontaktform (begrænset exposure)

**No action needed** - nuværende implementering er acceptabel.

---

### 3.5 Google Fonts ⚠️ KOMPLEKS SRI

**Nuværende implementering** (site-header.php):
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=Chakra+Petch:wght@700&display=swap" 
      rel="stylesheet">
```

**Udfordringer med SRI:**
- CSS fil varierer baseret på User-Agent (browser detection)
- Forskellige browsere får forskellige font formats (woff2, woff, ttf)
- Hash ændres konstant

**Anbefalet løsning: Self-hosted fonts**

#### Step 1: Download fonts
```bash
# Brug google-webfonts-helper
# https://gwfh.mranftl.com/fonts

# Eller brug google-fonts-download
npm install -g google-fonts-download
google-fonts-download "Inter:300,400,500,600,700,900" -o assets/fonts/inter
google-fonts-download "Chakra Petch:700" -o assets/fonts/chakra-petch
```

#### Step 2: Opdater CSS
```css
/* assets/css/fonts.css */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 300;
  font-display: swap;
  src: url('/assets/fonts/inter/Inter-Light.woff2') format('woff2'),
       url('/assets/fonts/inter/Inter-Light.woff') format('woff');
}

/* Gentag for alle weights... */

@font-face {
  font-family: 'Chakra Petch';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: url('/assets/fonts/chakra-petch/ChakraPetch-Bold.woff2') format('woff2'),
       url('/assets/fonts/chakra-petch/ChakraPetch-Bold.woff') format('woff');
}
```

#### Step 3: Opdater site-header.php
```html
<!-- Erstat Google Fonts link med: -->
<link rel="stylesheet" href="/assets/css/fonts.css">
```

**Benefits:**
- ✅ SRI support (via .htaccess ETag eller Integrity header)
- ✅ Bedre performance (ingen DNS lookup til Google)
- ✅ Privacy-friendly (ingen tracking fra Google)
- ✅ Fungerer offline

**Implementation estimate:** 3-5 timer

---

## 4. SRI Hash Generation

### 4.1 Command Line (Linux/macOS)

```bash
# SHA-384 (anbefalet)
curl -sL "https://cdn.example.com/script.js" | \
  openssl dgst -sha384 -binary | \
  openssl base64 -A

# SHA-256 (alternativ)
curl -sL "https://cdn.example.com/script.js" | \
  openssl dgst -sha256 -binary | \
  openssl base64 -A

# SHA-512 (maksimal sikkerhed)
curl -sL "https://cdn.example.com/script.js" | \
  openssl dgst -sha512 -binary | \
  openssl base64 -A
```

### 4.2 Online Tools

- **SRI Hash Generator**: https://www.srihash.org/
- **Report URI**: https://report-uri.com/home/sri_hash
- **KeyCDN**: https://tools.keycdn.com/sri

### 4.3 Node.js Script

```javascript
// generate-sri.js
const crypto = require('crypto');
const https = require('https');

function generateSRI(url, algorithm = 'sha384') {
  https.get(url, (res) => {
    const hash = crypto.createHash(algorithm);
    res.on('data', (chunk) => hash.update(chunk));
    res.on('end', () => {
      const digest = hash.digest('base64');
      console.log(`${algorithm}-${digest}`);
    });
  });
}

// Usage
generateSRI('https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js');
```

### 4.4 Automated Workflow

```yaml
# .github/workflows/update-sri.yml
name: Update SRI Hashes
on:
  schedule:
    - cron: '0 0 * * 1'  # Hver mandag
  workflow_dispatch:

jobs:
  update-sri:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      - name: Generate SRI for Chart.js
        run: |
          CHARTJS_HASH=$(curl -sL "https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" | \
            openssl dgst -sha384 -binary | openssl base64 -A)
          echo "Chart.js SRI: sha384-$CHARTJS_HASH"
          
      - name: Check if hash changed
        run: |
          # Compare with stored hash
          # Create PR if changed
```

---

## 5. Testing & Verification

### 5.1 Manual Testing

```html
<!-- Test page: test-sri.html -->
<!DOCTYPE html>
<html lang="da">
<head>
  <meta charset="UTF-8">
  <title>SRI Test</title>
  
  <!-- Correct hash - should load -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"
          integrity="sha384-CORRECT_HASH_HERE"
          crossorigin="anonymous"></script>
  
  <!-- Wrong hash - should fail -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"
          integrity="sha384-WRONG_HASH_123456789"
          crossorigin="anonymous"></script>
</head>
<body>
  <script>
    if (typeof Chart !== 'undefined') {
      console.log('✅ Chart.js loaded successfully with correct SRI');
    } else {
      console.error('❌ Chart.js failed to load (check SRI hash)');
    }
  </script>
</body>
</html>
```

### 5.2 Browser Console

```javascript
// Check if script loaded
if (typeof Chart !== 'undefined') {
  console.log('✅ Chart.js loaded');
} else {
  console.error('❌ Chart.js failed to load');
}

// Check for SRI errors in console
// Look for: "Failed to find a valid digest in the 'integrity' attribute"
```

### 5.3 Automated Testing

```javascript
// playwright-sri-test.js
const { test, expect } = require('@playwright/test');

test('SRI verification for Chart.js', async ({ page }) => {
  // Listen for console errors
  const errors = [];
  page.on('console', msg => {
    if (msg.type() === 'error') {
      errors.push(msg.text());
    }
  });
  
  await page.goto('https://blackbox.codes/dashboard.php');
  
  // Wait for Chart.js to load
  await page.waitForFunction(() => typeof Chart !== 'undefined');
  
  // Check no SRI errors
  const sriErrors = errors.filter(e => e.includes('integrity'));
  expect(sriErrors).toHaveLength(0);
  
  console.log('✅ Chart.js loaded successfully with SRI');
});
```

---

## 6. Maintenance & Updates

### 6.1 When to Update SRI Hashes

**Scenarier:**
1. **CDN version opdatering** (f.eks. Chart.js 4.4.1 → 4.5.0)
2. **Sikkerhedsopdatering** (CVE patch i library)
3. **Breaking changes** (major version upgrade)

**Process:**
1. Update version number i HTML
2. Generer ny SRI hash
3. Test lokalt/staging
4. Deploy til production
5. Monitorer fejl i 24 timer

### 6.2 Monitoring

```javascript
// Detect SRI failures
window.addEventListener('error', (e) => {
  if (e.target.tagName === 'SCRIPT' || e.target.tagName === 'LINK') {
    // Log to error tracking service
    console.error('Resource failed to load:', e.target.src || e.target.href);
    
    // Send to monitoring
    fetch('/api/log-error', {
      method: 'POST',
      body: JSON.stringify({
        type: 'sri_failure',
        resource: e.target.src || e.target.href,
        timestamp: new Date().toISOString()
      })
    });
  }
}, true);
```

### 6.3 Rollback Plan

```bash
# If SRI breaks production:

# Step 1: Remove integrity attribute temporarily
git checkout HEAD~1 dashboard.php

# Step 2: Quick deploy
git commit -m "hotfix: Remove broken SRI hash"
git push

# Step 3: Investigate and fix
# - Verify correct hash
# - Check CDN availability
# - Test in staging

# Step 4: Redeploy with correct hash
```

---

## 7. Roadmap

### Phase 1: Immediate (Sprint 6)
- [ ] Generer Chart.js SRI hash
- [ ] Test Chart.js SRI i staging
- [ ] Deploy Chart.js SRI til production
- [ ] Dokumenter hash i dette dokument

### Phase 2: Short-term (Sprint 7-8)
- [ ] Implementer local Tailwind build
- [ ] Self-host Google Fonts
- [ ] Opdater alle referencer til lokale assets
- [ ] Tilføj SRI til alle lokale assets

### Phase 3: Long-term (Q1 2026)
- [ ] Automatiseret SRI hash updates (GitHub Actions)
- [ ] SRI monitoring dashboard
- [ ] Performance testing (local vs CDN)
- [ ] Overvej Calendly alternativ med SRI support

---

## 8. Security Considerations

### 8.1 CSP Integration

SRI fungerer bedst sammen med Content Security Policy:

```apache
# .htaccess (already implemented)
Header always set Content-Security-Policy "\
  default-src 'self'; \
  script-src 'self' https://cdn.jsdelivr.net https://www.google.com; \
  require-sri-for script style; \
"
```

**Note**: `require-sri-for` er deprecated i CSP Level 3, men stadig supported.

### 8.2 Fallback Strategy

```html
<!-- Primary CDN with SRI -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"
        integrity="sha384-[HASH]"
        crossorigin="anonymous"></script>

<!-- Fallback til local copy hvis CDN fejler -->
<script>
  if (typeof Chart === 'undefined') {
    document.write('<script src="/assets/js/chart.min.js"><\/script>');
  }
</script>
```

### 8.3 HTTPS Only

⚠️ **SRI kræver HTTPS** - virker ikke over HTTP.

Verificer HTTPS enforcement i `.htaccess`:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 9. References

### Official Documentation
- **W3C SRI Spec**: https://www.w3.org/TR/SRI/
- **MDN Web Docs**: https://developer.mozilla.org/en-US/docs/Web/Security/Subresource_Integrity
- **Can I Use**: https://caniuse.com/subresource-integrity

### Tools
- **SRI Hash Generator**: https://www.srihash.org/
- **Report URI**: https://report-uri.com/home/sri_hash
- **Google Webfonts Helper**: https://gwfh.mranftl.com/fonts

### Libraries
- **Chart.js**: https://www.chartjs.org/
- **Tailwind CSS**: https://tailwindcss.com/
- **Calendly**: https://calendly.com/

---

## 10. Contact & Support

For spørgsmål om SRI implementation:

**Email:** ops@blackbox.codes  
**Team:** ALPHA Web Diagnostics & Security  
**Version:** 1.0.0  
**Last Updated:** 2025-11-24

---

## Changelog

### v1.0.0 (2025-11-24)
- ✅ Initial SRI guide created
- ✅ Chart.js pinned to v4.4.1 with crossorigin
- ✅ Calendly updated with crossorigin
- ✅ Documented SRI limitations for Tailwind, reCAPTCHA, Google Fonts
- 📋 Action items created for Phase 1 implementation

---

**Status:** ✅ **DOKUMENTATION KOMPLET**  
**Next Step:** Generate og test Chart.js SRI hash

