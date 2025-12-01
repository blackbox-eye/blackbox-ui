# QA Release Checklist

Denne checklist bruges ved releases af ALPHA Interface GUI for at sikre, at alle kritiske funktioner er verificeret.

---

## Generelt

- [ ] Alle unit tests passerer (`npm test`)
- [ ] Ingen kritiske linting-fejl (`npm run lint` hvis tilgængelig)
- [ ] Tailwind CSS bygget og opdateret (`npm run build:tailwind`)
- [ ] CHANGELOG.md opdateret med release-noter

---

## Sikkerhed

- [ ] Secrets er ikke hardkodet i kodebasen
- [ ] Password-hashing bruger bcrypt (ikke plaintext i prod)
- [ ] Session-cookies er Secure + HttpOnly
- [ ] reCAPTCHA er aktiveret på public forms

---

## TS24 / SSO Integration

### Lokal/CI healthcheck

- [ ] `npm run sso:health` returnerer OK for både GDI og TS24
- [ ] SSO audit log (`logs/sso_events.log`) viser ingen gentagne fejl

### GDI-side SSO

- [ ] `GDI_SSO_SECRET` / `JWT_SECRET` er konfigureret
- [ ] JWT-minting fungerer (verificér via healthcheck)
- [ ] Agent Access-siden viser TS24-kort med `data-sso-active="true"`
- [ ] TS24-link indeholder korrekt `?sso=` parameter

### TS24 Prod-verifikation

- [ ] Prod TS24 SSO-entry (`https://intel24.tstransport.app/sso-login`) svarer OK (DNS + cert)
  - Se `docs/sso_healthcheck.md` for curl-eksempel

### End-to-end

- [ ] Happy path: GDI login → TS24 link → TS24 dashboard uden fejl
- [ ] Expired token afvises korrekt af TS24
- [ ] Tampered token afvises korrekt

---

## Visual Regression

- [ ] Playwright tests passerer
- [ ] Graphene hero-komponent vises korrekt
- [ ] CTA-knapper fungerer og linker korrekt
- [ ] Ingen visuelle regressionsfejl

---

## Deployment

- [ ] FTP-credentials er gyldige
- [ ] `composer install` kørt (hvis vendor-dependencies)
- [ ] Database-migrations kørt (hvis relevant)
- [ ] Cloudflare cache purget

---

## Post-deploy verifikation

- [ ] Prod-site loader korrekt
- [ ] Login fungerer
- [ ] SSO healthcheck på prod returnerer OK
- [ ] Ingen console-fejl i browser

---

## Sign-off

- **QA-ansvarlig:** ________________________
- **Dato:** ________________________
- **Version:** ________________________

---

## Relateret dokumentation

- `docs/sso_healthcheck.md` – Healthcheck-guide
- `docs/ts24_sso_bridge.md` – TS24 integration overview
- `docs/sso_v1_signoff_gdi.md` – SSO v1 sign-off
- `docs/ci_pipelines.md` – CI/CD pipeline-info
