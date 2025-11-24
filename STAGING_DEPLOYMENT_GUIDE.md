# Staging Deployment & Test Guide - Quick Reference

**Repository:** AlphaAcces/ALPHA-Interface-GUI  
**Formål:** Hurtig guide til at køre deployment og tests på staging  
**Status:** ✅ KLAR TIL BRUG

---

## 🚀 Hurtig Start - Deployment til Staging

### 1. Kør CI/CD Deployment (Anbefalet)

**Via GitHub Web:**
1. Gå til: https://github.com/AlphaAcces/ALPHA-Interface-GUI/actions
2. Klik på "CI & Deploy (Secure)" i venstre sidebar
3. Klik "Run workflow" knappen (højre side)
4. Vælg branch (vælg `main` for produktion eller feature branch for test)
5. Klik grøn "Run workflow" knap

**Forventet Output:**
```
✅ Build & Verify (1-2 min)
  → Validerer secrets
  → Checker critical files
  
✅ Secure Delete index.html (1 min)
  → Fjerner index.html via FTPS
  → Sikrer DirectoryIndex fungerer
  
✅ Secure FTP Deploy (5-10 min)
  → Uploader alle filer via FTPS
  → Ekskluderer .git, node_modules, docs
  
✅ Smoke Tests (2-3 min)
  → Tester alle endpoints
  → Verificerer deployment success
```

**Total tid:** ~10-15 minutter

---

## 🧪 Kør Sikkerhedstests

### Option A: CodeQL Security Scanning

**Via GitHub Web:**
1. Gå til: https://github.com/AlphaAcces/ALPHA-Interface-GUI/actions
2. Klik på "CodeQL" workflow
3. Klik "Run workflow"
4. Vælg options:
   - Run PHP analysis: `true` (anbefalet)
   - Run JavaScript analysis: `false` (valgfrit)
5. Klik "Run workflow"

**Note:** CodeQL kræver at "Code scanning" er aktiveret i repository settings.  
Se `CODEQL_ACTIVATION_GUIDE.md` for instruktioner.

**Forventet Output:**
```
✅ PHP CodeQL Analysis (15-20 min)
  → Scanner PHP kode for sårbarheder
  → Uploader resultater til Security tab
  
⚠️  Hvis code scanning ikke er aktiveret:
  → Workflow fejler med instruktioner
  → Følg link til settings for at aktivere
```

---

## 🔍 Kør Lighthouse Performance Audit

**Via GitHub Web:**
1. Gå til: https://github.com/AlphaAcces/ALPHA-Interface-GUI/actions
2. Klik på "Lighthouse Audit" workflow
3. Klik "Run workflow"
4. Vælg branch
5. Klik "Run workflow"

**Forventet Output:**
```
✅ Lighthouse CI (5-8 min)
  → Kører Lighthouse på SITE_URL
  → Genererer performance rapport
  → Uploader results som artifact
```

**Download Results:**
1. Når workflow er færdig, scroll ned til "Artifacts" section
2. Download `lighthouse-results` artifact
3. Unzip filen
4. Åbn JSON fil eller HTML rapport

**Alternativ - Extract Scores (Lokal):**
```bash
# Download artifact og unzip
cd /path/to/downloaded/lighthouse-results

# Kør extract script
bash scripts/extract-lighthouse-scores.sh
```

---

## 🧰 Manuel Verifikation (Lokal Testing)

### Verificer Secrets Håndtering

```bash
# Clone repo
git clone https://github.com/AlphaAcces/ALPHA-Interface-GUI.git
cd ALPHA-Interface-GUI

# Check for hardcoded secrets (skal returnere kun REPLACE_ON_SERVER)
grep -r "password\|secret\|token" --include="*.php" . | grep -v "password_hash\|password_verify\|REPLACE_ON_SERVER"

# Check secrets i workflows (skal kun vise ${{ secrets.* }})
grep "secrets\." .github/workflows/*.yml
```

**Forventet:** Ingen hardcoded secrets, kun `${{ secrets.VAR }}` format.

---

### Verificer Deployment Configuration

```bash
# Check workflow configuration
cat .github/workflows/ci.yml | grep -A 2 "timeout-minutes:"

# Check FTPS configuration
cat .github/workflows/ci.yml | grep "ftp:ssl"

# Check smoke test URLs
cat .github/workflows/ci.yml | grep "SITE_URL"
```

**Forventet:** 
- Alle jobs har timeout
- FTPS encryption aktiv (`ftp:ssl-force true`)
- SITE_URL secret eller fallback til FTP_HOST

---

### Test Endpoints Manuel (Efter Deploy)

```bash
# Test root endpoint
curl -I https://blackbox.codes/

# Test subpages
curl -I https://blackbox.codes/about.php
curl -I https://blackbox.codes/cases.php
curl -I https://blackbox.codes/contact.php

# Verify index.html is NOT served
curl -I https://blackbox.codes/index.html  # Should return 404
```

**Forventede Status Codes:**
- `/` → 200 OK
- `/about.php` → 200 OK
- `/cases.php` → 200 OK
- `/contact.php` → 200 OK
- `/index.html` → 404 Not Found (GODT!)

---

## 📊 Verificer Lighthouse Results på Live-siden

### Nuværende Status
⚠️ **Lighthouse results vises IKKE automatisk på live-siden**

Lighthouse scores er tilgængelige via:

1. **GitHub Actions Artifacts** (efter hver Lighthouse workflow run)
2. **Dokumentation:**
   - `WEB_OPTIMIZATION_STATUS.md`
   - `WEB_OPTIMIZATION_AUDIT.md`
   - `WEB_OPTIMIZATION_EXECUTIVE_SUMMARY.md`

### Hvis du vil vise scores på live-siden (Valgfrit)

**Quick Option - Status Badge:**
Tilføj til website footer eller about page:
```html
<img src="https://img.shields.io/badge/performance-95-brightgreen" alt="Performance Score">
<img src="https://img.shields.io/badge/accessibility-100-brightgreen" alt="Accessibility Score">
<img src="https://img.shields.io/badge/best%20practices-92-green" alt="Best Practices Score">
<img src="https://img.shields.io/badge/SEO-100-brightgreen" alt="SEO Score">
```

**Advanced Option - Performance Dashboard:**
Se `FINAL_SECURITY_DEPLOYMENT_VALIDATION.md` section 6 for detaljer.

---

## ✅ Post-Deployment Checklist

Efter deployment til staging/produktion, verificer følgende:

### Immediate Checks (0-5 min)
- [ ] Website er tilgængelig på root URL
- [ ] Login page loader korrekt
- [ ] CSS og JavaScript loader (check browser console)
- [ ] Ingen 404 errors på kritiske assets

### Functional Tests (5-15 min)
- [ ] Agent login fungerer
- [ ] Dashboard loader med korrekt data
- [ ] Navigation mellem pages fungerer
- [ ] Kontaktformular viser korrekt
- [ ] reCAPTCHA loader på kontaktformular

### Security Validation (15-30 min)
- [ ] HTTPS aktiv (check padlock i browser)
- [ ] Headers korrekte (Content-Security-Policy, X-Frame-Options)
- [ ] Session management fungerer
- [ ] Logout funktionalitet virker
- [ ] Ingen exposed credentials i console/network tab

### Performance Validation (30-60 min)
- [ ] Kør Lighthouse workflow
- [ ] Download og review results
- [ ] Verificer Core Web Vitals
- [ ] Check resource loading times

---

## 🚨 Troubleshooting

### Deployment Fejler

**Problem:** Secrets validation fejler
```
❌ Missing required secrets: FTP_HOST
```

**Løsning:**
1. Gå til: https://github.com/AlphaAcces/ALPHA-Interface-GUI/settings/secrets/actions
2. Tilføj manglende secret
3. Kør workflow igen

---

**Problem:** FTP connection timeout
```
❌ FTP failed (exit code 1)
```

**Løsning:**
1. Verificer FTP_HOST er korrekt
2. Check om FTP server er oppe
3. Verificer firewall tillader FTPS (port 21)
4. Check FTP credentials er korrekte

---

**Problem:** Smoke tests fejler
```
❌ Root endpoint returned HTTP 500
```

**Løsning:**
1. Check server PHP error logs
2. Verificer .htaccess er uploaded korrekt
3. Verificer db.php connection fungerer
4. Check file permissions på server

---

### CodeQL Fejler

**Problem:** Code scanning not enabled
```
⚠️ CodeQL failed - code scanning not enabled
```

**Løsning:**
1. Gå til: https://github.com/AlphaAcces/ALPHA-Interface-GUI/settings/security_analysis
2. Enable "Code scanning"
3. Kør CodeQL workflow igen

Se `CODEQL_ACTIVATION_GUIDE.md` for detaljeret guide.

---

### Lighthouse Fejler

**Problem:** Lighthouse CI fails
```
Error: Page did not load within timeout
```

**Løsning:**
1. Verificer SITE_URL secret er korrekt
2. Check at website er accessible fra GitHub runners
3. Verificer ingen firewall blokkerer GitHub IPs
4. Prøv igen - kan være midlertidig network issue

---

## 📞 Support

**Technical Issues:**
- Email: ops@blackbox.codes
- Include: Workflow run URL, error messages, browser console logs

**Security Concerns:**
- Email: ops@blackbox.codes
- Priority: HIGH
- Subject: [SECURITY] beskrivelse af issue

**Documentation:**
- `FINAL_SECURITY_DEPLOYMENT_VALIDATION.md` - Komplet sikkerhedsrapport
- `README.md` - Repository overview og secrets guide
- `DEPLOYMENT_COMPLETE.md` - Deployment dokumentation

---

**Sidst opdateret:** 2025-11-24  
**Version:** 1.0  
**Agent:** ALPHA-CI-Security-Agent
