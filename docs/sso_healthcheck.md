# SSO Healthcheck Guide

> **Senest opdateret / Last updated:** 2025-12-01
> **Version:** 1.1

Denne guide forklarer, hvordan du kører SSO healthchecks for GDI og TS24 – både lokalt, i CI og i produktion.

---

## Oversigt

Healthcheck-scriptet `scripts/sso-health.js` (og det ældre PHP-alternativ `scripts/check_sso_health.php`) validerer, at GDI og TS24 stub svarer korrekt inden Playwright-tests eller deploy.

---

## Endpoints

| Navn | URL | Port | Beskrivelse |
|------|-----|------|-------------|
| **GDI** | `http://127.0.0.1:8000` | 8000 | Hoved-GUI-applikation |
| **TS24** (stub) | `http://127.0.0.1:8091/tools/ts24_health_stub.php` | 8091 | Lokal stub for TS24 SSO |
| **TS24** (prod) | `https://intel24.blackbox.codes/sso-login` | 443 | Kanonisk SSO entry – DNS + cert |

---

## Kør healthcheck lokalt

```bash
# Start PHP-servere
php -S localhost:8000 &
php -S 127.0.0.1:8091 &

# Node-baseret check (foretrukket)
npm run sso:health

# Alternativ PHP-check
npm run sso:health:php
```

### Forventet output (succes)

```text
🔍 SSO Health Check

==================================================

✅ GDI (Main GUI application)
   URL: http://127.0.0.1:8000
   Status: OK
   HTTP Code: 200
   Latency: 15ms

✅ TS24 (TS24 SSO integration)
   URL: http://127.0.0.1:8091/tools/ts24_health_stub.php
   Status: OK
   HTTP Code: 200
   Latency: 8ms
   Stub: Yes
   Secret Configured: Yes
   Uses HS256: Yes
   Expected Issuer: https://blackbox.codes
   Expected Audience: ts24
   Recent Errors: 0

==================================================

✅ All health checks passed!
```

---

## Exit-koder

| Kode | Betydning |
|------|-----------|
| `0` | OK – begge endpoints svarer |
| `1` | En eller flere checks fejlede |
| `2` | GDI endpoint unreachable |
| `3` | Invalid JSON fra GDI |
| `4` | GDI-konfigurationsfejl (secret, mint) |
| `5` | TS24 check fejlede |

---

## TS24 stub JSON-respons

```json
{
  "stub": true,
  "secretConfigured": true,
  "usesHS256": true,
  "expectedIss": "https://blackbox.codes",
  "expectedAud": "ts24",
  "recentErrors": [],
  "notes": "TS24 stub response for local testing",
  "timestamp": "2025-12-01T12:00:00+00:00"
}
```

---

## 🚀 Prod-verifikation (Ops-supplement)

> **Status (2025-12-02):** DNS + cert er live og verificeret.

Brug curl til at validere det kanoniske TS24 prod-endpoint inden high-risk releases:

```bash
curl -I https://intel24.blackbox.codes/sso-login
```

**Forventet resultat:**

- HTTP-status: `200 OK` eller `3xx` redirect
- Certifikat: Gyldigt SSL/TLS
- Ingen DNS-fejl

### Fejlscenarier

| Symptom | Årsag | Handling |
|---------|-------|----------|
| `NXDOMAIN` | DNS-problem | Tjek DNS, kontakt TS24-team |
| `ECONNREFUSED` | Server nede | Kontakt TS24-team |
| `SSL certificate problem` | Certifikat udløbet/invalid | Tjek certifikat |
| `HTTP 404` | Endpoint ikke deployed | Bekræft TS24-release |

---

## CI-integration

Healthcheck kører som preflight i `.github/workflows/visual-regression.yml`:

```yaml
- name: SSO Stack Health Preflight
  run: npm run sso:health
```

Pipeline stoppes automatisk, hvis healthcheck fejler.

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| `ECONNREFUSED` | Server kører ikke på forventet port |
| `TIMEOUT` | Server uresponsiv – tjek for blocking ops |
| `vendor/autoload.php` missing | Kør `composer install` |
| HTTP 500 | Tjek PHP-fejl i server output |

### Debug manuelt

```bash
curl -v http://127.0.0.1:8000
curl -v http://127.0.0.1:8091/tools/ts24_health_stub.php
```

---

## Relateret dokumentation

- `docs/ts24_sso_bridge.md` – Canonical entry URL og ejerskab
- `docs/sso_ops_runbook.md` – Drift og fejlsøgning
- `docs/sso_v1_signoff_gdi.md` – GDI sign-off
- `docs/ci_pipelines.md` – CI/CD pipeline-oversigt

---

## Changelog

| Dato | Ændring | PR |
|------|---------|----|
| 2025-11-30 | Første version | #61 |
| 2025-12-01 | Tilføjet prod-verifikation + detaljerede sektioner | Current |
