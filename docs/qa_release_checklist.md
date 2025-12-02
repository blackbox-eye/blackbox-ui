# QA Release Checklist

> **Senest opdateret / Last updated:** 2025-12-01
> **Version:** 1.1

Denne checklist kombinerer de oprindelige danske release-gates og den nye detaljerede preflight-guide. Brug den før både PR-merge og produktion.

---

## Grundlæggende kontroller

- [ ] `npm test` passerer uden fejl
- [ ] `npm run build:tailwind` kørt efter CSS-ændringer
- [ ] CHANGELOG.md opdateret
- [ ] Ingen hardkodede secrets (brug `bbx_env()`)
- [ ] Prod-passwords bruger `password_hash()`/`password_verify()`
- [ ] Session-cookies er Secure + HttpOnly
- [ ] reCAPTCHA aktiv på offentlige formularer

---

## Pre-release checklist

### 1. SSO health

- [ ] Start lokale PHP-servere (8000 for GDI, 8091 for TS24 stub)
- [ ] `npm run sso:health` viser ✅ for GDI og TS24 + `Secret Configured: Yes`
- [ ] `logs/sso_events.log` indeholder ingen nye fejl

### 2. Playwright suites

- [ ] `npm test` (fuld suite) gennemført
- [ ] Ekstra targeted suites efter behov (fx `tests/agent-access.spec.js`)
- [ ] Rapport viser `unexpected === 0`

### 3. i18n

- [ ] Ingen rå nøgler på `/agent-access.php`
- [ ] Hero-tekst, kort og audit notice oversat på både EN/DA

### 4. Mobile responsiveness

- [ ] 320 px: CTA'er ≥ 48 px, kort stakker korrekt
- [ ] 768 px: Navigation tilgængelig
- [ ] 1280 px: Console cards side om side

### 5. Visual regression

- [ ] Graphene hero matcher baseline
- [ ] Header/footer konsistente
- [ ] Farver følger GreyEYE palette

### 6. Deployment readiness

- [ ] Cloudflare Pages preview grøn
- [ ] `ci.yml` smoke tests passerer
- [ ] Ingen JS-console fejl på preview/staging
- [ ] Cloudflare cache klar til purge

---

## TS24 / SSO integration

### Lokal & CI healthcheck

- [ ] `npm run sso:health` OK
- [ ] `GDI_SSO_SECRET` / `JWT_SECRET` sat
- [ ] Agent Access viser TS24-kort med `data-sso-active="true"`
- [ ] SSO-link bruger `https://intel24.blackbox.codes/sso-login?sso=...`

### Prod-verifikation

- [ ] `curl -I https://intel24.blackbox.codes/sso-login` svarer 200/3xx (DNS + cert ok)
- [ ] Happy path: GDI login → TS24 link → dashboard
- [ ] Expired og tampered tokens afvises korrekt

---

## Quick verification commands

```bash
# Minimum gate
npm run sso:health && npm test -- tests/agent-access.spec.js

# Full pre-merge
npm run sso:health && npm test
```

---

## Release approval matrix

| Criterion | Status |
|-----------|--------|
| SSO health checks pass | ✅ Required |
| Playwright + visual regression pass | ✅ Required |
| Ingen rå i18n-nøgler | ✅ Required |
| Mobile CTA'er tilgængelige | ✅ Required |
| CodeQL (JS + PHP) grøn | ✅ Required |
| Cloudflare Pages deploy | ✅ Required |

---

## Post-deploy verifikation

- [ ] Prod-site loader (<https://blackbox.codes>)
- [ ] Agent-login + TS24 redirect fungerer
- [ ] Prod SSO health OK
- [ ] Ingen browser-console fejl

---

## Troubleshooting

| Issue | Action |
|-------|--------|
| `npm run sso:health` fejler | Tjek at begge PHP-servere kører |
| Playwright tests fejler | Læs rapport, regenerér snapshots hvis UI ændret |
| i18n-nøgler vises | Opdater `lang/en.json` og `lang/da.json` |
| Deploy fejler | Verificér Cloudflare/FTP secrets |

---

## Sign-off

- **QA-owner:** _______________________
- **Dato:** _______________________
- **Version:** _______________________

---

## Relateret dokumentation

- `docs/sso_healthcheck.md`
- `docs/ts24_sso_bridge.md`
- `docs/sso_v1_signoff_gdi.md`
- `docs/ci_pipelines.md`

---

## Changelog

| Dato | Ændring | PR |
|------|---------|----|
| 2025-11-30 | Første version | #61 |
| 2025-12-01 | Bilingual merge + TS24 prod-gate | Current |
