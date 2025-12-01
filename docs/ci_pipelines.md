# CI/CD Pipelines

Oversigt over GitHub Actions workflows i ALPHA Interface GUI.

---

## Workflows

### `ci.yml` – Deploy & Smoke Tests

**Trigger:** Push til `main`

**Trin:**

1. Checkout kode
2. Upload via FTPS til prod
3. Kør smoke tests
4. Purge Cloudflare cache

**Secrets:**

- `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD`, `FTP_REMOTE_PATH`
- `CF_ZONE_ID`, `CF_API_TOKEN`

---

### `visual-regression.yml` – Playwright Tests

**Trigger:** Push eller PR

**Trin:**

1. Checkout kode
2. Setup Node.js
3. Install dependencies
4. **SSO Stack Health Preflight** – `npm run sso:health`
5. Start PHP dev server
6. Kør Playwright tests
7. Upload test-artefakter

**SSO Healthcheck:**

Før Playwright-tests startes, køres `npm run sso:health` som preflight. Dette sikrer, at SSO-konfigurationen er korrekt, før vi tester UI-flows.

---

### `lighthouse.yml` – Performance Audits

**Trigger:** Push til `main`

**Trin:**

1. Checkout kode
2. Kør Lighthouse audits
3. Upload rapport

---

### `codeql-analysis.yml` – Security Scanning

**Trigger:** Push, PR, schedule

**Trin:**

1. Checkout kode
2. Initialize CodeQL
3. Autobuild
4. Perform analysis

---

## Lokal test af workflows

### SSO Health

```bash
npm run sso:health
```

### Playwright

```bash
npm test
```

### Tailwind build

```bash
npm run build:tailwind
```

---

## 🔧 Ops-supplement: Manuel Prod TS24-kontrol

Ud over de automatiske SSO healthchecks anbefales en manuel verifikation af TS24 prod-domænet før kritiske releases:

```bash
curl -I https://intel24.tstransport.app/sso-login
```

Denne kontrol verificerer:

- DNS-opløsning
- SSL/TLS-certifikat
- Endpoint-tilgængelighed

> **Bemærk:** Dette er et manuelt ops-supplement til de automatiske `sso:health` checks. Inkluderes i `docs/qa_release_checklist.md` som release-gate.

Se `docs/sso_healthcheck.md` for detaljer og fejlsøgning.

---

## Workflow-filer

- `.github/workflows/ci.yml`
- `.github/workflows/visual-regression.yml`
- `.github/workflows/lighthouse.yml`
- `.github/workflows/codeql-analysis.yml`

---

## Relateret dokumentation

- `docs/CI_CD_SETUP_GUIDE.md` – Komplet CI/CD opsætningsguide
- `docs/WORKFLOW_VALIDATION_REPORT.md` – Workflow-validering
- `docs/sso_healthcheck.md` – SSO healthcheck-guide
- `docs/ts24_sso_bridge.md` – TS24 integration overview
