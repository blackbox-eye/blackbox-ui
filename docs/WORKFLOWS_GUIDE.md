# GitHub Actions Workflows Guide

Dette dokument beskriver alle CI/CD workflows i Blackbox UI repository.

## 📋 Oversigt

Repository indeholder 4 GitHub Actions workflows:

| Workflow | Formål | Trigger | Status |
|----------|--------|---------|--------|
| `ci.yml` | Build, Deploy & Test | Push til main, manual | ✅ Aktiv |
| `codeql-analysis.yml` | Security scanning | Manual | ⚠️ Kræver setup |
| `lighthouse.yml` | Performance audit | Push til main, manual | ✅ Aktiv |
| `visual-regression.yml` | Visual testing | Push til main, manual | ✅ Aktiv |

## 🚀 CI & Deploy Pipeline (ci.yml)

### Formål
Komplet deployment pipeline med sikkerhed, validation og testing.

### Jobs Flow
```
build → delete-index-html → ftp-deploy → smoke-tests
```

### Job Beskrivelser

#### 1. Build & Verify (10 min timeout)
- Tjekker repository struktur
- Validerer at kritiske filer findes (README.md, index.php)
- **NYT**: Validerer at FTP secrets er konfigureret (kun på main branch)

#### 2. Secure Delete index.html (10 min timeout)
- Fjerner statisk index.html via FTPS
- Sikrer at index.php bliver served som DirectoryIndex
- Bruger lftp med TLS encryption

#### 3. Secure FTP Deploy (30 min timeout)
- Uploader alle filer via FTPS
- Bruger SamKirkland/FTP-Deploy-Action@v4.3.5
- Excluderer .git, node_modules, docs, etc.

#### 4. Smoke Tests (15 min timeout)
- Venter 15 sekunder på propagation
- **OPTIONAL**: Purger Cloudflare cache (kun hvis CF secrets er sat)
- Tester følgende endpoints:
  - Root (/) - skal returnere 200/301/302
  - About page (/about.php)
  - Cases page (/cases.php)
  - Contact page (/contact.php)
  - index.html - skal returnere 404/403 (deleted)
  - PHP execution verification

### Required Secrets

| Secret | Beskrivelse | Påkrævet |
|--------|-------------|----------|
| `FTP_HOST` | FTP server hostname | ✅ Ja |
| `FTP_USERNAME` | FTP bruger | ✅ Ja |
| `FTP_PASSWORD` | FTP password | ✅ Ja |
| `FTP_REMOTE_PATH` | Remote sti (fx /public_html) | ✅ Ja |
| `SITE_URL` | Site URL (fx https://blackbox.codes) | ❌ Nej (fallback: http://FTP_HOST) |
| `CF_ZONE_ID` | Cloudflare Zone ID | ❌ Nej (optional) |
| `CF_API_TOKEN` | Cloudflare API token | ❌ Nej (optional) |

### Sikkerhedsfunktioner
- ✅ FTPS (FTP over TLS) enforced
- ✅ TLS data protection aktiveret
- ✅ Credentials aldrig logget
- ✅ Certificate verification konfigurerbar
- ✅ Explicit permissions per job
- ✅ Secrets validation før deployment

### Kørsel
```bash
# Automatisk på push til main
git push origin main

# Manuel kørsel
# Gå til: Actions → CI & Deploy (Secure) → Run workflow
```

## 🔒 CodeQL Security Analysis (codeql-analysis.yml)

### Formål
Scanner kode for sikkerhedssårbarheder i PHP og JavaScript.

### Status
⚠️ **KRÆVER SETUP**: Code scanning skal aktiveres i repository settings.

### Setup Guide
1. Gå til Repository Settings → Security → Code security and analysis
2. Klik "Set up" ved "Code scanning"
3. Vælg "GitHub Actions" som metode
4. Aktiver funktionen
5. Uncomment triggers i workflow filen (push, pull_request, schedule)

### Jobs

#### PHP Analysis (30 min timeout)
- Scanner PHP kode for sårbarheder
- Installer composer dependencies hvis composer.json findes
- Uploader resultater til Security alerts

#### JavaScript Analysis (30 min timeout)
- **Gated**: Kører kun efter PHP analysis
- Scanner JavaScript for sårbarheder
- Installer npm dependencies hvis package.json findes

### Manual Kørsel
```bash
# Via Actions tab
Actions → CodeQL → Run workflow
- Enable PHP: true
- Enable JS: true/false
```

### Automatisk Kørsel (når aktiveret)
- På push til main
- På pull requests til main
- Hver søndag kl 00:00 UTC (schedule)

## 🌟 Lighthouse Performance Audit (lighthouse.yml)

### Formål
Måler website performance, accessibility, SEO og best practices.

### Konfiguration
- **Node.js**: 20
- **URL**: Bruger `SITE_URL` secret, fallback til `https://blackbox.codes`
- **Action**: treosh/lighthouse-ci-action@v9

### Features
- Performance scoring
- Accessibility audit
- SEO checks
- Best practices validation
- Uploader resultater som artifacts

### Timeout
20 minutter

### Kørsel
```bash
# Automatisk på push til main
git push origin main

# Manuel kørsel
Actions → Lighthouse Audit → Run workflow
```

## 🎨 Visual Regression Testing (visual-regression.yml)

### Formål
Tager screenshots på forskellige devices og browsere for at opdage visuelle ændringer.

### Konfiguration
- **Node.js**: 20 (aligned med lighthouse)
- **Test Framework**: Playwright
- **Browsere**: Chromium, Firefox, WebKit, Chromium-dark

### Viewports Testet
- Mobile: 375x812
- Tablet: 768x1024
- Desktop Medium: 1024x768
- Desktop Large: 1440x900

### Features
- Full page screenshots
- Header-specific screenshots
- Cross-browser testing
- Dark mode testing
- Artifacts upload (always, even on failure)

### Timeout
20 minutter

### Kørsel
```bash
# Automatisk på push til main
git push origin main

# Manuel kørsel
Actions → Visual Regression → Run workflow

# Lokal kørsel
npm install
npx playwright install --with-deps
npm run test:ci
```

## 🔧 Fejlfinding

### Workflow fejler med "missing secrets"
**Problem**: FTP secrets er ikke konfigureret.

**Løsning**:
1. Gå til Settings → Secrets and variables → Actions
2. Tilføj manglende secrets (FTP_HOST, FTP_USERNAME, FTP_PASSWORD, FTP_REMOTE_PATH)

### Cloudflare cache purge fejler
**Problem**: CF secrets er ikke sat, eller token er ugyldig.

**Løsning**:
- Cloudflare purge er **optional**
- Workflow fortsætter selvom det fejler (conditional check)
- Tilføj CF_ZONE_ID og CF_API_TOKEN secrets hvis du vil bruge det

### CodeQL workflow fejler
**Problem**: Code scanning er ikke aktiveret.

**Løsning**:
1. Gå til Settings → Security → Code security and analysis
2. Aktiver "Code scanning"
3. Kør workflow igen

### FTP deployment timeout
**Problem**: Upload tager for lang tid (>30 min).

**Løsning**:
- Øg timeout-minutes i ci.yml for ftp-deploy job
- Tjek FTP server forbindelse
- Overvej at exclude flere store filer/mapper

### Visual tests fejler lokalt
**Problem**: Kan ikke tilgå blackbox.codes.

**Løsning**:
- Sørg for internet forbindelse
- Tjek at site er oppe
- Tests kører normalt fint i GitHub Actions

## 📊 Best Practices

### Secrets Rotation
```bash
# 1. Generer nye credentials hos hosting provider
# 2. Opdater secrets i GitHub
Settings → Secrets and variables → Actions → Update secret

# 3. Test deployment
Actions → CI & Deploy (Secure) → Run workflow

# 4. Revoke gamle credentials hos provider
```

### Workflow Monitoring
```bash
# Tjek workflow status
Actions tab → Vælg workflow → Se runs

# Download artifacts
Actions → Specific run → Artifacts section → Download
```

### Manual Workflow Dispatch
Alle workflows kan køres manuelt via Actions tab:
1. Gå til Actions
2. Vælg workflow i venstre sidebar
3. Klik "Run workflow"
4. Vælg branch (normalt main)
5. Klik "Run workflow" (grøn knap)

## 🔐 Security Checklist

- [x] Alle workflows bruger explicit permissions
- [x] FTPS encryption enforced for FTP operations
- [x] Credentials aldrig logget i output
- [x] Secrets validation før deployment
- [x] Timeouts sat for alle jobs
- [x] Actions versions opdateret (checkout@v4, setup-node@v4)
- [x] Conditional execution for optional features
- [x] Proper error handling med exit codes

## 📚 Relaterede Dokumenter

- [CI/CD Setup Guide](CI_CD_SETUP_GUIDE.md) - Opsætningsvejledning
- [README](../README.md) - Projekt oversigt
- [SECURITY](../SECURITY.md) - Security politik

## 🆘 Support

Ved spørgsmål eller problemer:
- 📧 Email: ops@blackbox.codes
- 💬 GitHub Issues: Opret issue i repository
- 📖 Dokumentation: Se docs/ folder

---

**Sidste opdatering**: 2025-11-24  
**Version**: 2.0  
**Ansvarlig**: ALPHA-CI-Security-Agent
