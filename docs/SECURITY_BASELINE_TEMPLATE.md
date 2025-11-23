# Blackbox EYE – Security Baseline for GitHub Repositories

**Version**: 1.0  
**Dato**: 2025-11-23  
**Gyldig for**: Alle Blackbox EYE repositories (GreyEYE, ALPHA-Interface-GUI, osv.)  
**Ejer**: ops@blackbox.codes  
**Status**: 🟢 Aktiv baseline standard

---

## 📋 Oversigt

Dette dokument definerer **minimum sikkerhedsstandard** for alle GitHub-repositories under Blackbox EYE-økosystemet. Standarden er baseret på Master Blueprint (v1.0.2), Leverance 5 (Compliance & Security), og industriens best practices (OWASP, NIST, CIS).

**Formål**: Sikre ensartet sikkerhedsniveau, compliance-readiness, og effektiv CI/CD på tværs af alle projekter.

---

## 🌳 Branch Protection & Trunk-Based Development

### Branch Strategy

Blackbox EYE følger **Trunk-Based Development** med `main` som single source of truth:

- **Main branch** (`main`): Produktionsklar kode, altid deployable
- **Feature branches**: Kortlivede branches (`feature/*`, `fix/*`, `docs/*`), max 2-3 dages levetid
- **No long-lived development branches**: Ingen `develop`, `staging` branches (merge direkte til `main` via PR)

### Branch Protection Rules for `main`

| Regel | Requirement | Rationale |
|-------|-------------|-----------|
| **Require pull request** | ✅ Påkrævet | Ingen direkte commits til main |
| **Require 1+ approvals** | ✅ Påkrævet | Code review før merge |
| **Dismiss stale reviews** | ✅ Påkrævet | Review skal være fresh efter ny commit |
| **Require status checks** | ✅ Påkrævet | CI/CD skal være grøn (lint, test, security) |
| **Require conversation resolution** | ✅ Påkrævet | Alle review-kommentarer skal addresseres |
| **Require signed commits** | ⚠️ Anbefalet | GPG-signering for non-repudiation (optional) |
| **Restrict who can push** | ⬜ Optional | Kun for high-sensitivity repos |

**Hvordan konfigurere**: Settings → Branches → Add rule → Branch name pattern: `main`

---

## 🔧 CI/CD Pipeline - Obligatoriske Jobs

Alle repos **skal** have følgende jobs i `.github/workflows/` for at være compliant:

### 1. Lint Job

**Formål**: Sikre code quality og konsistent formatting.

```yaml
lint:
  name: Code Quality Check
  runs-on: ubuntu-latest
  steps:
    - uses: actions/checkout@v4
    - name: Run linter
      run: |
        # For PHP: phpcs, psalm
        # For JavaScript: eslint
        # For Python: flake8, pylint
```

**Krav**:
- Skal køre på alle PRs til `main`
- Blocking status check (merge ikke mulig hvis fejl)
- Max execution time: 5 minutter

### 2. Test Job

**Formål**: Verificer funktionalitet og undgå regressioner.

```yaml
test:
  name: Unit & Integration Tests
  runs-on: ubuntu-latest
  steps:
    - uses: actions/checkout@v4
    - name: Run tests
      run: |
        # For PHP: phpunit
        # For JavaScript: jest, mocha
        # For Python: pytest
```

**Krav**:
- Minimum 60% code coverage (langsomt op mod 80%+)
- Unit tests + integration tests
- Max execution time: 10 minutter

### 3. SAST (Static Application Security Testing) - CodeQL

**Formål**: Automatisk sikkerhedsscanning for sårbarheder.

```yaml
codeql:
  name: CodeQL Security Analysis
  runs-on: ubuntu-latest
  permissions:
    security-events: write
  steps:
    - uses: actions/checkout@v4
    - uses: github/codeql-action/init@v2
      with:
        languages: php  # eller javascript, python, osv.
    - uses: github/codeql-action/analyze@v2
```

**Krav**:
- **OBLIGATORISK** for alle repos med kode
- Kør på push til `main`, PRs, og ugentligt (cron)
- Code scanning **skal** være aktiveret i repo settings
- Kritiske findings skal fixes inden 7 dage, high inden 30 dage

**Setup**: Se `SECURITY_IMPLEMENTATION_SUMMARY.md` for detaljerede instruktioner.

### 4. Dependency Scanning - Dependabot

**Formål**: Opdage sårbare dependencies (npm, composer, pip, osv.).

**Krav**:
- Dependabot alerts **skal** være aktiveret i Settings → Security
- Dependabot security updates **anbefales** (auto-PR for patches)
- Review og merge Dependabot PRs inden 14 dage
- For kritiske sårbarheder: immediate action (samme dag)

**Konfiguration**: `.github/dependabot.yml`

```yaml
version: 2
updates:
  - package-ecosystem: "npm"
    directory: "/"
    schedule:
      interval: "weekly"
  - package-ecosystem: "composer"
    directory: "/"
    schedule:
      interval: "weekly"
```

### 5. Secret Scanning

**Formål**: Forhindre accidental commit af credentials.

**Krav**:
- Secret scanning **skal** være aktiveret i Settings → Security
- Push protection **skal** være aktiveret (blokerer push med secrets)
- Alle findings **skal** reviewes og roteres inden 24 timer
- False positives skal dismisses med kommentar i GitHub

**Ingen workflow nødvendig** - GitHub scanner automatisk.

---

## 🔐 Secrets Management - HashiCorp Vault

### Vault Integration Standard

Alle secrets **skal** håndteres via HashiCorp Vault (eller Azure Key Vault som fallback). **Ingen hardcoded secrets** i kode eller `.env` files i git.

#### Tilladt i Repository

✅ **Placeholders i konfigurationsfiler:**

```env
# .env.example (committed til git)
FTP_HOST=REPLACE_ON_SERVER
FTP_USERNAME=REPLACE_ON_SERVER
FTP_PASSWORD=REPLACE_ON_SERVER
DB_HOST=localhost
DB_NAME=my_database
DB_USER=REPLACE_ON_SERVER
DB_PASS=REPLACE_ON_SERVER
```

✅ **GitHub Secrets for CI/CD:**

Secrets til GitHub Actions gemmes i Settings → Secrets and variables → Actions:
- `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD`
- `VAULT_TOKEN` (for production Vault integration)
- `RECAPTCHA_SECRET_KEY`

Referenceres i workflows som: `${{ secrets.SECRET_NAME }}`

#### Forbudt

❌ **Hardcoded credentials:**

```php
// ❌ ALDRIG gør dette
$password = "MySecretP@ssw0rd";
$api_key = "sk_live_1234567890abcdef";
```

❌ **Committed `.env` med real secrets:**

```env
# ❌ ALDRIG commit real values
DB_PASS=SuperSecretPassword123
```

#### Vault Workflow (Production)

1. **Development**: Lokale secrets i `.env` (ikke committed, i `.gitignore`)
2. **CI/CD**: Secrets fra GitHub Secrets (midlertidig løsning)
3. **Production**: Secrets fra Vault via API/CLI

**Vault secret rotation**: Kritiske secrets roteres hver 90. dag minimum.

**Se også**: Leverance 5, afsnit "HashiCorp Vault Setup" for detaljeret implementeringsguide.

---

## 🌐 Web Security - Security Headers & HTTPS

### Obligatoriske Security Headers

Alle webapplikationer **skal** returnere følgende HTTP-headers:

#### 1. Content Security Policy (CSP)

```apache
# .htaccess eller webserver config
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://www.google.com https://www.gstatic.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self'; connect-src 'self'; frame-src 'self' https://www.google.com; object-src 'none'; base-uri 'self'; form-action 'self'"
```

**Formål**: Forhindre XSS, clickjacking, code injection.

#### 2. HTTP Strict Transport Security (HSTS)

```apache
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
```

**Formål**: Tving HTTPS for alle requests, forhindre man-in-the-middle.

#### 3. X-Frame-Options

```apache
Header always set X-Frame-Options "SAMEORIGIN"
```

**Formål**: Forhindre clickjacking ved at blokere iframe-embedding.

#### 4. X-Content-Type-Options

```apache
Header always set X-Content-Type-Options "nosniff"
```

**Formål**: Forhindre MIME-sniffing attacks.

#### 5. Referrer-Policy

```apache
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

**Formål**: Kontrollér hvilke referrer-data sendes til eksterne sites.

#### 6. Permissions-Policy

```apache
Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
```

**Formål**: Deaktivér unødvendige browser-features.

### Subresource Integrity (SRI)

For **alle eksterne scripts og stylesheets** (CDN), brug SRI:

```html
<!-- ✅ God praksis -->
<script src="https://cdn.example.com/library.js" 
        integrity="sha384-HASH_HERE" 
        crossorigin="anonymous"></script>

<!-- ❌ Usikkert -->
<script src="https://cdn.example.com/library.js"></script>
```

**Tool til generering**: https://www.srihash.org/

### HTTPS-Only

- **Alle** production-domæner skal bruge HTTPS (TLS 1.2+)
- HTTP→HTTPS redirect i `.htaccess`:

```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

- Test med: https://www.ssllabs.com/ssltest/
- Mål: A eller A+ rating

### Security Headers Test

Verificér headers med: https://securityheaders.com/

**Minimum acceptable score**: B (mål: A)

---

## 📊 Compliance Hooks - Logging, Audit Trails & 90-Dages Plan

### Logging Requirements

Alle applikationer **skal** logge følgende events:

| Event Type | Logging Requirement | Retention |
|------------|---------------------|-----------|
| **Authentication** | Login success/failure, user ID, timestamp, IP | 1 år |
| **Authorization** | Access denied events, role changes | 1 år |
| **Data Access** | Read/write til PII/sensitive data | 1 år |
| **Configuration Changes** | Settings updates, admin actions | 2 år |
| **Security Events** | Failed auth attempts, suspicious activity | 2 år |
| **Errors** | Application crashes, exceptions | 90 dage |

**Log format**: Struktureret JSON for nem parsing i SIEM.

**Log destinations**:
- Development: Lokal fil eller stdout
- Production: Central SIEM (Elastic Stack, Splunk, eller Datadog)

### Audit Trail

For **GDPR og NIS2 compliance**, skal alle data-operationer have audit trail:

```php
// Eksempel: Audit log entry
audit_log([
    'user_id' => $user_id,
    'action' => 'DELETE_PERSONAL_DATA',
    'resource' => 'users',
    'resource_id' => $target_user_id,
    'timestamp' => time(),
    'ip_address' => $_SERVER['REMOTE_ADDR'],
    'result' => 'SUCCESS',
    'gdpr_basis' => 'User right to erasure (Art. 17)'
]);
```

### 90-Dages Compliance Plan (GDPR/NIS2)

Når nyt repo oprettes eller eksisterende repo skal compliance-upgrades:

#### Måned 1 (Dage 1-30): Foundation
1. Aktivér alle GitHub security features (CodeQL, Dependabot, Secret scanning)
2. Implementér security headers på alle endpoints
3. Cookie consent banner (GDPR krav)
4. Privacy Policy og Terms of Service opdateret
5. Data mapping: Identificér alle PII-felter i database

#### Måned 2 (Dage 31-60): Implementation
6. Vault integration for secret management
7. Central logging til SIEM
8. Backup & disaster recovery plan dokumenteret
9. GDPR data subject requests workflow (export, delete)
10. NIS2 incident response plan (initial draft)

#### Måned 3 (Dage 61-90): Monitoring & Validation
11. Security monitoring dashboard (SIEM alerts)
12. Compliance audit (intern review)
13. Penetration test eller vulnerability assessment
14. Documentation finalized (runbooks, SOPs)
15. Compliance sign-off fra ops@blackbox.codes

**Reference**: Se Leverance 5 for detaljeret GDPR- og NIS2-checklister.

---

## 🎯 Konkret Eksempel: ALPHA-Interface-GUI

Dette repo (ALPHA-Interface-GUI) følger baseline-standarden med følgende konkrete implementeringer:

### ✅ Implementeret

| Feature | Status | Detaljer |
|---------|--------|----------|
| **Branch Protection** | ⚠️ Partial | PR-krav aktivt, mangler status check enforcement |
| **CodeQL Workflow** | ✅ Configured | `.github/workflows/codeql-analysis.yml` klar, afventer code scanning aktivering |
| **FTPS/TLS Deployment** | ✅ Active | CI workflow bruger FTPS med TLS 1.2+ (se `ci.yml`) |
| **Smoke Tests** | ✅ Active | 6 endpoints testet efter deploy |
| **Security Headers** | ✅ Configured | CSP, HSTS, XFO, XCTO i `.htaccess` |
| **Secrets in GitHub** | ✅ Active | FTP credentials, RECAPTCHA key i GitHub Secrets |
| **Dependabot** | ❌ Not enabled | **TODO**: Aktiver i Settings |
| **Secret Scanning** | ❌ Not enabled | **TODO**: Aktiver i Settings |

### 🔄 I Gang (Next 30 Days)

1. Aktiver CodeQL code scanning (5 min)
2. Aktiver Dependabot alerts (2 min)
3. Aktiver Secret scanning med push protection (2 min)
4. Opdater branch protection rules til at kræve status checks (5 min)
5. Cookie consent banner (GDPR) - 1-2 dages udvikling

### 📋 Backlog (31-90 Days)

6. Vault PoC og production deployment
7. Central logging til SIEM
8. Zero Trust network access (Cloudflare Access + MFA)
9. Compliance dashboard
10. Quarterly security audit setup

**Se**: `SECURITY_IMPLEMENTATION_SUMMARY.md` for komplet 90-dages roadmap.

---

## 🔗 Related Documentation & Resources

### Blackbox EYE Internal Docs

- **Master Blueprint**: `docs/MASTER BLUEPRINT_ Udvikling i Github Repos v1.0.2.pdf`
- **Leverance 5 (Compliance & Security)**: `docs/Leverance 5 - Blackbox EYE – Compliance & Security Enhancements.pdf`
- **CI/CD Security Hardening Report**: `docs/CI_CD_SECURITY_HARDENING_REPORT_v2.0.md`
- **Security Implementation Summary**: `SECURITY_IMPLEMENTATION_SUMMARY.md` (repo-specifik)

### External Standards & Best Practices

- **OWASP Top 10**: https://owasp.org/www-project-top-ten/
- **NIST Cybersecurity Framework**: https://www.nist.gov/cyberframework
- **CIS Controls**: https://www.cisecurity.org/controls
- **GitHub Security Best Practices**: https://docs.github.com/en/code-security
- **GDPR Official Text**: https://gdpr-info.eu/
- **NIS2 Directive**: https://digital-strategy.ec.europa.eu/en/policies/nis2-directive

### Tools & Testing

- **Security Headers Check**: https://securityheaders.com/
- **SSL/TLS Test**: https://www.ssllabs.com/ssltest/
- **CSP Evaluator**: https://csp-evaluator.withgoogle.com/
- **SRI Hash Generator**: https://www.srihash.org/
- **OWASP ZAP** (security testing): https://www.zaproxy.org/

---

## 📧 Kontakt & Support

**Spørgsmål om security baseline eller compliance?**

- **Email**: ops@blackbox.codes
- **Ansvarlig**: ALPHA-CI-Security-Agent
- **Eskalering**: AlphaAcces (repository owner)

**Rapportering af sikkerhedssårbarheder**: Se `SECURITY.md` i hvert repository.

---

## 🔄 Changelog

### Version 1.0 - 2025-11-23
- **Created**: Initial security baseline template for Blackbox EYE
- **Scope**: Branch protection, CI/CD, SAST, dependency scanning, secret management, web security, compliance
- **Example**: ALPHA-Interface-GUI used as reference implementation
- **Status**: Active baseline standard for all repos

---

**Document prepared by**: ALPHA-CI-Security-Agent  
**Date**: 2025-11-23  
**Next review**: Quarterly (every 90 days) or on major security incidents
