# CI/CD Pipelines Documentation

> **Last updated:** 2025-12-01
> **Version:** 1.1

## Overview

Denne fil beskriver alle GitHub Actions-workflows for ALPHA Interface GUI, hvornår de kører, og hvilke gates der gælder for TS24 SSO.

---

## Workflow Summary

| Workflow | Fil | Trigger | Formål |
|----------|-----|---------|--------|
| **CI Deploy** | `ci.yml` | Push til `main` | FTPS deploy + smoke tests + Cloudflare purge |
| **Cloudflare Pages** | `cloudflare-pages.yml` | Push til `main` | Cloudflare Pages-build |
| **Visual Regression** | `visual-regression.yml` | Push/PR | Playwright-baseret UI-regression inkl. SSO preflight |
| **Lighthouse** | `lighthouse.yml` | Push/PR | Performance-, SEO- og a11y-audits |
| **CodeQL Analysis** | `codeql-analysis.yml` | Push/PR/Schedule | JS + PHP security scanning |
| **Sprint 5 Smoke Test** | `sprint5-smoke-test.yml` | Manual dispatch | Midlertidig sprintkontrol |

---

## Workflow Details

### `ci.yml` – Deploy & Smoke Tests

- **Trigger:** Push til `main`
- **Trin:** checkout → FTPS upload → smoke tests → Cloudflare purge
- **Secrets:** `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD`, `FTP_REMOTE_PATH`, `CF_ZONE_ID`, `CF_API_TOKEN`

### `cloudflare-pages.yml` – Pages Deploy

- **Formål:** Bygger Tailwind-assets og deployer til Cloudflare Pages
- **Trigger:** Push til `main`
- **Secrets:** `CLOUDFLARE_API_TOKEN`, `CLOUDFLARE_ACCOUNT_ID`

### `visual-regression.yml` – Playwright Tests

- **Trigger:** Push/PR (udelukker docs/ og *.md)
- **Preflight:** `npm run sso:health` sikrer GDI + TS24 stub svarer før tests
- **Steps:** Checkout → Node 20 + PHP 8.3 → CI DB stub → `npm ci` → Playwright install → `npm run test:ci` → upload screenshots
- **Timeout:** 30 min
- **Concurrency:** `visual-regression-${{ github.ref }}` med `cancel-in-progress: true`

### `lighthouse.yml` – Performance Audits

- Måler Performance, Accessibility, Best Practices og SEO på seneste build
- Kører på push/PR til `main`

### `codeql-analysis.yml` – Security Scanning

- Dækker JavaScript og PHP
- Trigger: push/PR + ugentlig schedule
- Output: SARIF-rapport i GitHub Security-tab

### `sprint5-smoke-test.yml`

- Manuelt workflow til specifikke sprinttestcases

---

## Ready-to-Merge Gates

- ✅ Cloudflare Pages build succesfuld
- ✅ CodeQL (JS + PHP) uden kritiske findings
- ✅ Visual Regression passerer
- ✅ `ci.yml` smoke tests grønne
- ✅ Ingen mergekonflikter

### Anbefalede ekstra checks

- `npm run sso:health` lokalt (stub + prod curl)
- Ingen rå i18n-nøgler i UI
- Mobilresponsivitet testet (320px/768px/1280px)
- QA-release checklist udfyldt (se `docs/qa_release_checklist.md`)

---

## Kør workflows lokalt

```bash
# Visual regression
npm ci
npx playwright install --with-deps
php -S localhost:8000 &
npm run test:ci

# SSO healthcheck
php -S localhost:8000 &
php -S 127.0.0.1:8091 &
npm run sso:health

# Tailwind build
npm run build:tailwind
```

---

## 🔧 Ops-supplement: Manuel TS24 prod-kontrol

Kør inden high-risk releases for at bekræfte den kanoniske TS24-entry (`https://intel24.blackbox.codes/sso-login`):

```bash
curl -I https://intel24.blackbox.codes/sso-login
```

Valider DNS, SSL/TLS og HTTP 200/3xx. Fejlscenarier og handlinger findes i `docs/sso_healthcheck.md`.

---

## Workflow-filer

- `.github/workflows/ci.yml`
- `.github/workflows/cloudflare-pages.yml`
- `.github/workflows/visual-regression.yml`
- `.github/workflows/lighthouse.yml`
- `.github/workflows/codeql-analysis.yml`
- `.github/workflows/sprint5-smoke-test.yml`

---

## Relateret dokumentation

- `docs/CI_CD_SETUP_GUIDE.md`
- `docs/WORKFLOW_VALIDATION_REPORT.md`
- `docs/sso_healthcheck.md`
- `docs/ts24_sso_bridge.md`

---

## Changelog

| Dato | Ændring | PR |
|------|---------|----|
| 2025-11-30 | Første version | #61 |
| 2025-12-01 | Tilføjet TS24 curl-supplement + opdaterede workflows | Current |
