# Security Scanning & Deployment Setup - Sprint 5

## Oversigt

Dette dokument beskriver den omfattende sikkerhedsscanning og deployment pipeline implementeret i Sprint 5 for ALPHA Interface GUI / BLACKBOX.CODES.

## 📋 Implementerede Features

### 1. Security Scanning Workflow

Omfattende sikkerhedsscanning der kører automatisk efter hver merge til main:

#### 1.1 Dependency Audit
- **Tool**: npm audit
- **Funktion**: Scanner alle npm dependencies for kendte sårbarheder
- **Output**: JSON rapport med severity levels (critical, high, moderate, low)
- **Artifacts**: `npm-audit-results.json` (bevares i 30 dage)

#### 1.2 Snyk Security Scan (Optional)
- **Tool**: Snyk
- **Krav**: `SNYK_TOKEN` secret skal være konfigureret
- **Funktion**: Advanced vulnerability scanning med curated database
- **Trigger**: Kun hvis `SNYK_TOKEN` er sat eller `ENABLE_SNYK` variable er 'true'
- **Output**: `snyk-results.json` med detaljerede remediation steps

#### 1.3 SAST Scanning
- **Tool**: Semgrep
- **Sprog**: PHP og JavaScript
- **Rulesets**: Auto-configured (inkluderer OWASP Top 10, CWE)
- **Output**: 
  - SARIF format til GitHub Security tab
  - JSON rapport (`semgrep-results.json`)
- **Integrations**: Resultater uploades til GitHub Code Scanning

#### 1.4 Secret Scanning
- **Tool**: TruffleHog
- **Scope**: Fuld git historik (fetch-depth: 0)
- **Funktion**: Detekterer hardcoded secrets, API keys, credentials
- **Verification**: Kun verificerede secrets rapporteres
- **Output**: `trufflehog-results.json`

#### 1.5 License Compliance
- **Tool**: license-checker (npm package)
- **Funktion**: Verificerer alle dependency licenses
- **Checks**: 
  - Identificerer problematic licenses (GPL-3.0, AGPL, SSPL)
  - Genererer fuld license rapport
- **Output**: `license-report.json` med summary

#### 1.6 Container Scanning (Conditional)
- **Tool**: Trivy (Aqua Security)
- **Trigger**: Kun hvis `Dockerfile` findes
- **Scope**: Filesystem scanning
- **Output**: SARIF format til GitHub Security tab

#### 1.7 Security Summary
- **Funktion**: Aggregerer resultater fra alle scans
- **Output**: Konsolideret rapport med:
  - Scan metadata (dato, commit, branch)
  - Status for alle scans
  - Links til artifacts og Security tab
  - Contact information (ops@blackbox.codes)

### 2. Cloudflare Pages Deployment

Automatisk deployment til staging og production via Cloudflare Pages:

#### 2.1 Build & Prepare
- Validerer repository struktur
- Checker kritiske filer (index.php, .htaccess)
- Installerer dependencies
- Verificerer Cloudflare configuration

#### 2.2 Staging Deployment
- **Trigger**: Automatisk ved push til main eller pull requests
- **Environment**: staging
- **Output**: Preview URL genereret af Cloudflare
- **Features**:
  - Branch-specific deployments
  - Environment variables via Cloudflare API
  - Automatic rollback ved fejl

#### 2.3 Staging Verification
Omfattende smoke tests på staging environment:

**Test 1: Root Endpoint**
- Verificerer HTTP 200/301/302
- Checker HTML content validity
- Detekterer PHP execution

**Test 2: Contact Page**
- Verificerer contact.php accessibility
- Checker form rendering

**Test 3: reCAPTCHA Configuration**
- Verificerer reCAPTCHA script loading
- Checker site key presence
- Validerer integration

**Test 4: Logs Directory Security**
- Verificerer at /logs/ er beskyttet
- Forventer HTTP 403/404
- Warninger hvis publicly accessible

**Test 5: Performance Check**
- Måler response time
- Target: < 3 sekunder
- Warninger ved slow responses

#### 2.4 Production Deployment
- **Trigger**: Manuel workflow dispatch
- **Krav**: Staging verification skal være bestået
- **Environment**: production (requires approval)
- **URL**: https://blackbox.codes
- **Notification**: ops@blackbox.codes

### 3. Enhanced CodeQL Analysis

Opdateret CodeQL workflow med automatiske triggers:

#### 3.1 PHP Analysis
- **Trigger**: Automatisk ved push/PR til main
- **Schedule**: Ugentligt (søndag kl. 00:00 UTC)
- **Composer**: Installerer dependencies hvis composer.json findes

#### 3.2 JavaScript Analysis (Gated)
- **Trigger**: 
  - Manual: Via workflow dispatch
  - Automatic: Hvis `ENABLE_JS_CODEQL` variable er 'true'
- **Dependencies**: npm ci hvis package.json findes

## 🔐 Required Secrets

### GitHub Secrets (Settings > Secrets and variables > Actions)

#### Security Scanning
- `SNYK_TOKEN` (optional): Snyk API token for enhanced scanning

#### Cloudflare Pages
- `CLOUDFLARE_API_TOKEN`: Cloudflare API token med Pages write access
- `CLOUDFLARE_ACCOUNT_ID`: Cloudflare account ID
- `CF_PAGES_PROJECT_NAME`: Project navn (default: 'blackbox-codes')
- `CF_ZONE_ID`: Cloudflare zone ID for cache purging
- `CF_API_TOKEN`: Cloudflare API token for cache operations

#### Application Secrets
- `BBX_RECAPTCHA_SECRET_KEY`: reCAPTCHA v3 secret key
- `SMTP_HOST`, `SMTP_PORT`, `SMTP_USERNAME`, `SMTP_PASSWORD`: SMTP configuration (optional)

#### Existing FTP Secrets (bibeholdt fra tidligere)
- `FTP_HOST`: FTP server hostname
- `FTP_USERNAME`: FTP account username
- `FTP_PASSWORD`: FTP account password
- `FTP_REMOTE_PATH`: Remote path til site root
- `SITE_URL`: Custom site URL for smoke tests

## 📊 Workflow Triggers

### Automatic Triggers

| Workflow | Push to main | Pull Request | Schedule | Manual |
|----------|--------------|--------------|----------|--------|
| Security Scanning | ✅ | ✅ | ✅ (Søn 02:00) | ✅ |
| Cloudflare Pages | ✅ | ✅ | ❌ | ✅ |
| CodeQL Analysis | ✅ | ✅ | ✅ (Søn 00:00) | ✅ |
| CI & Deploy (FTP) | ✅ | ❌ | ❌ | ✅ |

### Manual Workflow Dispatch

Alle workflows kan køres manuelt via GitHub Actions UI:

1. Gå til **Actions** tab
2. Vælg workflow fra venstre sidebar
3. Klik **Run workflow** dropdown
4. Vælg branch og eventuelle input parameters
5. Klik **Run workflow**

## 🎯 Best Practices

### Security Scanning
1. **Review findings regelmæssigt**: Check GitHub Security tab efter hver scan
2. **Prioriter critical/high severity**: Adresser høj-prioritets sårbarheder først
3. **Keep dependencies updated**: Kør `npm audit fix` regelmæssigt
4. **Rotate secrets**: Opdater API tokens og credentials periodisk

### Cloudflare Deployment
1. **Test på staging først**: Verificer altid på preview URL før production
2. **Monitor logs**: Check Cloudflare Pages logs efter deployment
3. **Configure environment variables**: Set secrets i Cloudflare dashboard
4. **Use Wrangler CLI**: For programmatic secret management:
   ```bash
   wrangler pages secret put BBX_RECAPTCHA_SECRET_KEY --project=blackbox-codes
   ```

### CodeQL Analysis
1. **Enable code scanning**: Aktivér i repository Settings > Security
2. **Review alerts**: Check Security tab for CodeQL findings
3. **Fix false positives**: Mark alerts som false positive hvis nødvendigt
4. **Track metrics**: Monitor trends over tid

## 🔧 Setup Instructions

### 1. Konfigurer GitHub Secrets

```bash
# Navigate til repository settings
https://github.com/AlphaAcces/ALPHA-Interface-GUI/settings/secrets/actions

# Tilføj required secrets (se ovenstående liste)
```

### 2. Aktivér GitHub Code Scanning

```bash
# Navigate til security settings
https://github.com/AlphaAcces/ALPHA-Interface-GUI/settings/security_analysis

# Enable "Code scanning" feature
# Vælg "GitHub Actions" som scan method
```

### 3. Konfigurer Cloudflare Pages

#### Via Dashboard:
1. Log ind på [Cloudflare Dashboard](https://dash.cloudflare.com)
2. Gå til **Pages**
3. Opret eller vælg project: `blackbox-codes`
4. Gå til **Settings > Environment variables**
5. Tilføj environment variables:
   - `BBX_RECAPTCHA_SECRET_KEY`
   - `RECAPTCHA_SITE_KEY`
   - `SITE_BASE_URL`
   - SMTP variables (hvis nødvendigt)

#### Via Wrangler CLI:
```bash
# Install Wrangler
npm install -g wrangler

# Login
wrangler login

# Set secrets
wrangler pages secret put BBX_RECAPTCHA_SECRET_KEY --project=blackbox-codes
wrangler pages secret put RECAPTCHA_SITE_KEY --project=blackbox-codes
```

### 4. Verificer Workflows

Kør workflows manuelt første gang for at verificere konfiguration:

```bash
# Security Scanning
Actions > Security Scanning > Run workflow

# Cloudflare Pages
Actions > Cloudflare Pages Deploy > Run workflow > environment: staging

# CodeQL
Actions > CodeQL > Run workflow
```

## 📈 Monitoring & Reporting

### GitHub Security Tab

Alle security findings er tilgængelige under:
```
https://github.com/AlphaAcces/ALPHA-Interface-GUI/security
```

**Sections:**
- **Code scanning alerts**: Semgrep og CodeQL findings
- **Dependabot alerts**: Dependency vulnerabilities
- **Secret scanning alerts**: Leaked credentials (hvis GitHub Advanced Security er aktiveret)

### Workflow Artifacts

Alle scan resultater uploades som artifacts (retention: 30 dage):
- `npm-audit-results`
- `snyk-results` (hvis Snyk er aktiveret)
- `semgrep-results`
- `trufflehog-results`
- `license-report`

Download via:
```
Actions > [Workflow Run] > Artifacts section
```

### Cloudflare Pages Logs

Access deployment logs via:
```
https://dash.cloudflare.com/pages/view/blackbox-codes/logs
```

## 🚨 Troubleshooting

### Security Scanning Issues

**Problem**: Snyk scan fejler
- **Solution**: Verificer at `SNYK_TOKEN` er korrekt konfigureret
- **Alternative**: Disable Snyk ved at undlade at sætte secret

**Problem**: Semgrep timeout
- **Solution**: Reducer antal filer ved at excludere directories i workflow

**Problem**: TruffleHog false positives
- **Solution**: Kun verified secrets rapporteres - ignorer hvis fejl

### Cloudflare Deployment Issues

**Problem**: "API token invalid"
- **Solution**: Verificer `CLOUDFLARE_API_TOKEN` har Pages write permissions

**Problem**: Environment variables ikke tilgængelige
- **Solution**: Set via Cloudflare dashboard eller Wrangler CLI

**Problem**: Preview URL 404
- **Solution**: Wait 30-60 sekunder for propagation, tjek Cloudflare logs

### CodeQL Issues

**Problem**: "Code scanning not enabled"
- **Solution**: Aktivér code scanning i repository settings

**Problem**: Analysis timeout
- **Solution**: Reducer codebase size eller disable JS analysis

## 📞 Support

For assistance med security scanning og deployment:

**Email**: ops@blackbox.codes
**Discord**: BLACKBOX E.Y.E. Ops Center

## 📚 References

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Cloudflare Pages Documentation](https://developers.cloudflare.com/pages/)
- [Semgrep Rules](https://semgrep.dev/docs/writing-rules/overview/)
- [Snyk Documentation](https://docs.snyk.io/)
- [CodeQL Documentation](https://codeql.github.com/docs/)
- [TruffleHog Documentation](https://github.com/trufflesecurity/trufflehog)

## 🔄 Changelog

**v1.5.0-sprint5 (2025-11-24)**
- Initial implementation af security scanning workflow
- Cloudflare Pages deployment workflow tilføjet
- CodeQL analysis opdateret med automatic triggers
- Comprehensive documentation created

---

*Dette dokument opdateres løbende i takt med nye features og forbedringer.*
