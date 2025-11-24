# Sprint 5 Setup Guide - Trin 1 & 2
## Security Scanning & Cloudflare Pages Deployment

Denne guide hjælper dig med at konfigurere de nye workflows og få Trin 1 og 2 op at køre.

## ✅ Status
- [x] Workflows implementeret og committed
- [x] YAML syntaks valideret
- [ ] GitHub Secrets konfigureret
- [ ] GitHub Code Scanning aktiveret
- [ ] Cloudflare Pages projekt oprettet
- [ ] Workflows testet manuelt

## 📋 Næste Skridt

### 1. Konfigurer GitHub Secrets

Gå til repository settings og tilføj følgende secrets:

```
https://github.com/AlphaAcces/ALPHA-Interface-GUI/settings/secrets/actions
```

#### A. Security Scanning Secrets (Optional)
```
SNYK_TOKEN = [Your Snyk API token]
```
- Get Snyk token here: https://app.snyk.io/account
- Snyk scanning is optional - workflow runs without it

#### B. Cloudflare Pages Secrets (Required for Trin 2)
```
CLOUDFLARE_API_TOKEN = [API token with Pages write access]
CLOUDFLARE_ACCOUNT_ID = [Your Cloudflare account ID]
CF_PAGES_PROJECT_NAME = blackbox-codes
CF_ZONE_ID = [Zone ID for blackbox.codes]
```

**How to get Cloudflare credentials:**

1. **API Token**:
   - Log ind på Cloudflare Dashboard
   - Gå til: https://dash.cloudflare.com/profile/api-tokens
   - Klik "Create Token"
   - Vælg template "Edit Cloudflare Pages"
   - Eller opret custom token med permissions:
     - Account > Cloudflare Pages > Edit
     - Zone > Cache Purge > Purge
   - Kopier token (vises kun én gang!)

2. **Account ID**:
   - Cloudflare Dashboard > Workers & Pages
   - Account ID vises i højre sidebar
   - Format: 32 karakterer (hex)

3. **Zone ID**:
   - Cloudflare Dashboard > Websites > blackbox.codes
   - Scroll ned til "API" section
   - Kopier Zone ID

#### C. Application Secrets (Already configured)
```
BBX_RECAPTCHA_SECRET_KEY = [Allerede konfigureret]
FTP_HOST = [Allerede konfigureret]
FTP_USERNAME = [Allerede konfigureret]
FTP_PASSWORD = [Allerede konfigureret]
FTP_REMOTE_PATH = [Allerede konfigureret]
SITE_URL = [Allerede konfigureret]
```

### 2. Aktivér GitHub Code Scanning

Dette er nødvendigt for at CodeQL og Semgrep kan uploade resultater.

**Steps:**
1. Gå til: https://github.com/AlphaAcces/ALPHA-Interface-GUI/settings/security_analysis
2. Under "Code scanning":
   - Klik "Set up" button
   - Vælg "GitHub Actions" method
   - Workflows er allerede konfigureret, så bare aktiver featuren
3. Gem ændringerne

### 3. Opret/Verificer Cloudflare Pages Projekt

#### Option A: Via Cloudflare Dashboard (Anbefalet for første gang)

1. **Log ind på Cloudflare**:
   - Gå til: https://dash.cloudflare.com
   - Vælg dit account

2. **Create Pages Project**:
   - Klik "Workers & Pages" i sidebar
   - Klik "Create application" > "Pages" tab
   - Connect til GitHub repository:
     - Vælg "AlphaAcces/ALPHA-Interface-GUI"
     - Repository permissions: Read & Write
   - Project name: `blackbox-codes`
   - Production branch: `main`
   - Build settings: None (PHP project)
   - Root directory: `/` (default)

3. **Konfigurer Environment Variables**:
   - Gå til project settings: https://dash.cloudflare.com/pages/view/blackbox-codes/settings/environment-variables
   - Tilføj følgende variables:
   
   **Production:**
   ```
   BBX_RECAPTCHA_SECRET_KEY = [Din reCAPTCHA secret key]
   RECAPTCHA_SITE_KEY = [Din reCAPTCHA site key]
   SITE_BASE_URL = https://blackbox.codes
   ```
   
   **Preview (Optional - same values):**
   ```
   BBX_RECAPTCHA_SECRET_KEY = [Same as production]
   RECAPTCHA_SITE_KEY = [Same as production]
   SITE_BASE_URL = [Will be set by Cloudflare automatically]
   ```

4. **SMTP Variables (Optional)**:
   Hvis du vil bruge SMTP på Cloudflare Pages:
   ```
   SMTP_HOST = [Din SMTP server]
   SMTP_PORT = 587
   SMTP_USERNAME = [SMTP username]
   SMTP_PASSWORD = [SMTP password]
   SMTP_SECURE = tls
   SMTP_FROM_EMAIL = noreply@blackbox.codes
   SMTP_FROM_NAME = Blackbox EYE
   ```

#### Option B: Via Wrangler CLI

```bash
# Install Wrangler
npm install -g wrangler

# Login
wrangler login

# Create project (hvis ikke allerede oprettet)
wrangler pages project create blackbox-codes

# Set secrets
wrangler pages secret put BBX_RECAPTCHA_SECRET_KEY --project=blackbox-codes
wrangler pages secret put RECAPTCHA_SITE_KEY --project=blackbox-codes

# List secrets (for at verificere)
wrangler pages secret list --project=blackbox-codes
```

### 4. Test Workflows Manuelt

Nu skal du teste at alle workflows virker korrekt.

#### A. Test Security Scanning

1. Gå til: https://github.com/AlphaAcces/ALPHA-Interface-GUI/actions
2. Klik på "Security Scanning" workflow i venstre sidebar
3. Klik "Run workflow" dropdown (top right)
4. Vælg branch: `main`
5. Klik grøn "Run workflow" button
6. Vent ca. 5-10 minutter
7. Verificer at alle jobs er grønne ✅

**Hvad skal virke:**
- ✅ Dependency Audit (npm audit)
- ✅ SAST Scanning (Semgrep)
- ✅ Secret Scanning (TruffleHog)
- ✅ License Compliance
- ⊘ Snyk Scan (kun hvis SNYK_TOKEN er sat)
- ⊘ Container Scan (kun hvis Dockerfile findes)
- ✅ Security Summary

#### B. Test Cloudflare Pages Deployment

1. Gå til: https://github.com/AlphaAcces/ALPHA-Interface-GUI/actions
2. Klik på "Cloudflare Pages Deploy" workflow
3. Klik "Run workflow" dropdown
4. Vælg:
   - Branch: `main`
   - environment: `staging`
5. Klik "Run workflow"
6. Vent ca. 5-10 minutter
7. Verificer:
   - ✅ Build & Prepare job succeeds
   - ✅ Deploy to Staging job succeeds
   - ✅ Verify Staging job succeeds med alle 5 tests

**Preview URL:**
- Find preview URL i workflow output
- Format: `https://[hash].blackbox-codes.pages.dev`
- Test manuelt at siden virker

#### C. Test CodeQL Analysis

1. Gå til: https://github.com/AlphaAcces/ALPHA-Interface-GUI/actions
2. Klik på "CodeQL" workflow
3. Klik "Run workflow"
4. Vælg:
   - Branch: `main`
   - enable_php: `true`
   - enable_js: `false` (for første test)
5. Klik "Run workflow"
6. Vent ca. 10-15 minutter (CodeQL er langsom)
7. Verificer:
   - ✅ PHP Analysis job succeeds
   - Check GitHub Security tab for findings

### 5. Verificer Resultater

#### Security Tab
1. Gå til: https://github.com/AlphaAcces/ALPHA-Interface-GUI/security
2. Check "Code scanning" tab:
   - Burde se findings fra Semgrep
   - Burde se findings fra CodeQL (hvis enabled)
3. Review og triage findings:
   - Mark false positives som "Dismissed"
   - Fix real security issues

#### Actions Artifacts
1. Gå til en workflow run
2. Scroll ned til "Artifacts" section
3. Download og review:
   - `npm-audit-results`
   - `semgrep-results`
   - `trufflehog-results`
   - `license-report`

#### Cloudflare Dashboard
1. Gå til: https://dash.cloudflare.com/pages/view/blackbox-codes
2. Check "Deployments" tab:
   - Burde se nyeste deployment
   - Status: Success ✅
3. Klik på deployment for at se preview URL
4. Test preview URL manuelt

## 🚨 Troubleshooting

### Problem: "CLOUDFLARE_API_TOKEN not configured"
**Solution**: Tilføj secret i GitHub repository settings

### Problem: "Code scanning not enabled"
**Solution**: Aktivér Code Scanning i Security settings (se trin 2)

### Problem: Cloudflare deployment fejler
**Solution**: 
- Verificer API token har correct permissions
- Check at account ID og project name er korrekte
- Se Cloudflare Pages logs for detaljer

### Problem: Semgrep timeout
**Solution**: Dette er normalt hvis codebase er stor - ignorer eller exclude directories

### Problem: License-checker findes ulovlige licenses
**Solution**: Review license-report.json og vurder om det er acceptabelt

## ✅ Success Criteria

Du har gennemført Trin 1 og 2 succesfuldt når:

**Trin 1 - Security Scanning:**
- [x] Security Scanning workflow kører uden fejl
- [x] Alle 6 scans completes (eller 4 hvis Snyk/Trivy skipped)
- [x] Artifacts genereres og kan downloades
- [x] Security tab viser findings (hvis nogen)
- [x] Ingen critical/high severity vulnerabilities (eller acknowledged)

**Trin 2 - Cloudflare Pages:**
- [x] Cloudflare Pages projekt oprettet
- [x] Environment variables konfigureret
- [x] Staging deployment succeeds
- [x] Preview URL er accessible
- [x] Alle 5 verification tests er grønne
- [x] /logs/ directory er beskyttet (403/404)
- [x] reCAPTCHA virker på contact form

## 📞 Næste Skridt

Når Trin 1 og 2 er completed og alle workflows er grønne:

1. **Notificer mig** at du er klar til Trin 3
2. Jeg vil da:
   - Tag release med `v1.5.0-sprint5`
   - Opdatere CHANGELOG.md med finale resultater
   - Oprette GitHub release med release notes
   - Deploy til production (hvis godkendt)
   - Sende notification til ops@blackbox.codes

## 📚 Dokumentation

Fuld dokumentation findes i:
- `docs/SPRINT5_SECURITY_DEPLOYMENT_GUIDE.md` - Teknisk guide
- `CHANGELOG.md` - Version history
- Workflow files - Inline kommentarer på dansk

## 📧 Support

Hvis du støder på problemer:
- Check workflow logs for fejlmeddelelser
- Review denne guide igen
- Contact: ops@blackbox.codes

---

**God fornøjelse med Sprint 5! 🚀**
