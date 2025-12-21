# Blackbox UI
> **BLACKBOX.CODES | Enterprise Edition v1.0**

---

## 📋 Om Projektet

**Blackbox UI** er den officielle, responsive brugerflade for BLACKBOX.CODES-platformen (kodenavn: BLACKBOX E.Y.E).
> _Tidligere kodenavn: **ALPHA Interface GUI**_

Dette repo leverer frontend- og webkomponenter til enterprise cyber operations, OSINT, secure access og AI-drevet mission control.

---

## 🏗️ Features & Komponenter

- **Responsivt dashboard** (PHP, HTML, CSS, JS)
- **Role-based login:** Agent/Admin, PIN/Password (klar til 2FA/Token-integration)
- **Modulbaseret navigation:** Dashboard, Missions, Intelligence, Logs, Tools
- **Realtime systemstatus & alerts**
- **Stealth log management** (eksport/slet, live-view)
- **Klar til CI/CD-pipeline & Vault-integration**
- **Kompatibel med Hak5 Cloud C2 & AI Codex workflows**

---

## 🗂️ Mappestruktur

```text

/  # Projektrod
├── assets/            # Billeder, stylesheets, scripts
├── includes/          # PHP includes (header, footer mv.)
├── .well-known/       # Certifikater & validation
├── admin.php          # Adminpanel
├── dashboard.php      # Dashboard view
├── index.php          # Login-side
├── logout.php         # Logout-handler
├── db.php             # Database access/config
├── settings.php       # Indstillinger
├── style.css          # Hoved CSS
├── script.js          # Hoved JS
├── .github/           # CI/CD workflows
└── docs/              # Dokumentation og rapporter

```

---

## 🚀 Quickstart for Developers

Quick setup for local development:

```bash
# 1. Clone repository
git clone https://github.com/Blackbox-EYE/blackbox-ui.git
cd blackbox-ui

# 2. Install dependencies
npm install

# 3. Configure database (optional for UI testing)
cp db.php.example db.php
# Edit db.php with your database credentials, or leave as-is for UI-only testing

# 4. Start PHP development servers
php -S localhost:8000 &           # Main application
php -S 127.0.0.1:8091 &           # TS24 SSO stub

# 5. Verify SSO health
npm run sso:health

# 6. Run tests
npm test

# 7. Build Tailwind CSS (if needed)
npm run build:tailwind
```

### 🐉 Kali Playwright setup

For kali-rolling Playwright dependencies (no `--with-deps`) and a one-shot helper script, see [docs/SETUP_KALI.md](docs/SETUP_KALI.md).

### Available npm Scripts

| Script | Description |
|--------|-------------|
| `npm test` | Run all Playwright tests (via shim) |
| `npm run test:ci` | Run tests in CI mode |
| `npm run sso:health` | Check GDI and TS24 SSO health |
| `npm run build:tailwind` | Build Tailwind CSS |

---

## 🚦 Installation & Deployment

1. **Krav:**
   - PHP 7.4+
   - MySQL/MariaDB
   - Apache/nginx
   - Git (til CI/CD og deploy)

2. **Klon repo:**

   ```bash
   git clone https://github.com/Blackbox-EYE/blackbox-ui.git
   ```

3. **Upload & konfiguration:**

   - Upload `assets/`, PHP-filer og `.well-known/` til `public_html`.
   - Importér SQL-script til `agents`, `hosts` mv.
   - Redigér `db.php` med korrekte DB-credentials.
   - Konfigurer Vault/Secrets (HashiCorp/Azure) ifølge blueprint.
   - Tilpas branding ved at udskifte logo, CSS og tekster.

4. **CI/CD:**

   - Se `.github/workflows/ci.yml` for build/test/deploy til staging/production.
   - Sørg for at alle secrets ligger som GitHub Secrets.

5. **Test:**

   ```bash
   npm install          # Installer dependencies
   npm test             # Kør alle Playwright-tests (via shim)
   npm run build:tailwind  # Byg Tailwind CSS
   ```

   - **Playwright Exit-Code Shim:** Projektet bruger `scripts/playwright-shim.js` til at normalisere exit-koder på Windows. Shimmen analyserer JSON-rapporten og returnerer exit 0, når alle tests passerer.
   - **Artefakter:** Test-rapporter gemmes i `artifacts/` (ignoreret i git). HTML-rapporter gemmes i `playwright-report/` (også ignoreret).

---

## 🔒 Sikkerhed & Compliance

- **Password auth (midlertidigt dev-setup):** Agent-login matcher nu klartekst-passwords for hurtig test; revert til `password_hash()` / `password_verify()` før produktion
- **Prepared statements:** MySQLi/PDO for SQL-injection-beskyttelse
- **Session management:** Secure cookies, `session_start()`
- **Role-based access control:** Admin vs. Agent
- **Vault-integration:** Klar til HashiCorp/Azure Secrets
- **Audit logging & change tracking**
- **GDPR / Privacy-by-Design:** Pseudonymisering og logging efter enterprise-standard

### 🔗 TS24 SSO Integration

GDI (Blackbox UI) understøtter single sign-on til TS24 Intel Console:

- **Canonical TS24 SSO entry:** `https://intel24.blackbox.codes/sso-login`
- **Fuld SSO URL (GDI bygger):** `https://intel24.blackbox.codes/sso-login?sso=<JWT>`
- **Manuel login fallback:** `https://intel24.blackbox.codes/login`

Domænet `intel24.blackbox.codes` ejes af **ts24-intel-console**. GDI ejer konfigurationen (`TS24_CONSOLE_URL` env var) og token-minting.

> **Status (2025-12-02):** DNS + cert er verificeret. GDI peger nu på den kanoniske SSO-entry `/sso-login` som default.

For detaljer, se:
- [docs/ts24\_sso\_bridge.md](docs/ts24_sso_bridge.md) – Canonical URLs og ejerskab
- [docs/sso\_healthcheck.md](docs/sso_healthcheck.md) – Healthcheck-guide
- [docs/sso\_gdi\_ts24.md](docs/sso_gdi_ts24.md) – Teknisk JWT-specifikation

---

## 👤 Agentroller & Adgang

| Rolle     | Funktioner                                            | Data-adgang |
| --------- | ----------------------------------------------------- | ----------- |
| **Admin** | Opret/vedligehold agenter, se alle logs, systemstatus | Fuld adgang |
| **Agent** | Missions, logs, stealth tools, basis-dashboard        | Begrænset   |

---

## 📖 Dokumentation & Blueprint

Alle dokumenter ligger nu under `/docs/`:

### 📑 Blueprint & Handlingsplan

- [SYSTEM\_BLUEPRINT\_AIG\_v1.0.md](/docs/SYSTEM_BLUEPRINT_AIG_v1.0.md)
- [aig\_blueprint\_v1.md](/docs/aig_blueprint_v1.md)

### 🔧 CI/CD & Workflow Dokumentation

- [CI_CD_SETUP_GUIDE.md](/docs/CI_CD_SETUP_GUIDE.md) - Komplet opsætningsguide til CI/CD workflow
- [WORKFLOW_VALIDATION_REPORT.md](/docs/WORKFLOW_VALIDATION_REPORT.md) - Validering af workflow-konfiguration (PR #3/PR #5)
- [ci_pipelines.md](/docs/ci_pipelines.md) - Detaljeret oversigt over workflows, triggers og TS24 curl-ops-supplement

### 🔗 SSO & TS24 Integration

- [ts24_sso_bridge.md](/docs/ts24_sso_bridge.md) - Canonical TS24 entry, ejerskab og JWT-flow
- [sso_healthcheck.md](/docs/sso_healthcheck.md) - Healthcheck-script, stub og prod-verifikationsguide
- [sso_gdi_ts24.md](/docs/sso_gdi_ts24.md) - Teknisk JWT-specifikation
- [sso_ops_runbook.md](/docs/sso_ops_runbook.md) - Drift og fejlsøgning
- [sso_v1_signoff_gdi.md](/docs/sso_v1_signoff_gdi.md) - GDI sign-off checklist
- [e2e_gdi_ts24_sso_test.md](/docs/e2e_gdi_ts24_sso_test.md) - End-to-end testplan

### ✅ QA & Release

- [qa_release_checklist.md](/docs/qa_release_checklist.md) - Både dansk release-tjekliste og udvidet preflight-checks

### 📄 Versionerede rapporter (i `/docs/reports/`)

| Dokumenttype  | Filnavn                                                                                             | Version | Dato       | Ansvarlig |
| ------------- | --------------------------------------------------------------------------------------------------- | ------- | ---------- | --------- |
| Onboarding    | [v1.1\_20250707\_onboarding.md](docs/reports/v1.1_20250707_onboarding.md)                           | v1.1    | 2025-07-07 | ALPHADEV  |
| Statusrapport | [v1.2\_20250701\_statusrapport.md](docs/reports/v1.2_20250701_statusrapport.md)                     | v1.2    | 2025-07-01 | ALPHADEV  |
| Sprintplan    | [v1.3\_20250705\_sprintplan.md](docs/reports/v1.3_20250705_sprintplan.md)                           | v1.3    | 2025-07-05 | ALPHADEV  |
| Auditlog      | [v1.4\_20250710\_auditlog.md](docs/reports/v1.4_20250710_auditlog.md)                               | v1.4    | 2025-07-10 | QA-agent  |
| Masterprompt  | [AIG\_MASTERPROMPT\_v1.2\_20250630.md](docs/reports/docs/reports/AIG_MASTERPROMPT_v1.2_20250630.md) | v1.2    | 2025-06-30 | NEX       |

---

## 🗓️ Versionshistorik

Se [CHANGELOG.md](CHANGELOG.md) for detaljeret release-tracking.

---

## 🤝 Bidrag & Udvikling

- Følg branch-/PR-politik og semver.
- QA & CI er obligatorisk før merge til `main`.
- Sørg for at opdatere dokumentation ved hver major/minor release.
- Security audits og code reviews kræves før produktion.

---

## 🔐 Secret rotation

Dette repository bruger Actions-secrets til FTP-deployment: `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD`, `FTP_REMOTE_PATH`.

For at rotere en secret:
1. Gå til repositoryets Settings → Secrets and variables → Actions.
2. Vælg den secret du vil opdatere (eller klik "New repository secret" for at tilføje).
3. Sæt den nye værdi og gem.
4. Hvis du har tilbagekaldt eller oprettet nye credentials hos din hosting-udbyder (fx cPanel), så husk at fjerne/fortryde de gamle credentials der også.
5. Efter rotation, kør workflowet igen (fra Actions-fanen eller ved at pushe en commit) for at bekræfte at deployment og smoke tests stadig lykkes.

Tips:
- Sørg for at `FTP_REMOTE_PATH` peger på din site-root (fx `/public_html` eller bare `/`). Hvis du ændrer denne sti under rotation,
  opdater secret og verificér upload-stien i workflowet.
- Overvej at bruge en separat FTP-bruger med begrænsede rettigheder til automatiseret deploy for bedre sikkerhed.

---

## 📄 Licens

Dette projekt er frigivet under **MIT License** (se [LICENSE](LICENSE)).

---

## 📞 Kontakt & Support

- E-mail: [ops@blackbox.codes](mailto:ops@blackbox.codes)
- Discord: BLACKBOX E.Y.E. Ops Center

Dokumentation og deployment-guides opdateres løbende. For enterprise-integration eller revision, kontakt Blackbox Lead via ovenstående.
