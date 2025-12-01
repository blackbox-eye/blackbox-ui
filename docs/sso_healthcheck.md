# SSO Healthcheck Guide

Denne guide beskriver, hvordan du verificerer SSO-stackens sundhed for GDI ⇄ TS24 integrationen.

---

## Automatisk Healthcheck (CI/Lokal)

### Kommando

```bash
npm run sso:health
```

### Output-format

```text
GDI SSO: OK
TS24 SSO: OK
```

Eller ved fejl:

```text
GDI SSO: Failed flags: has_secret (notes: Missing GDI_SSO_SECRET / JWT_SECRET)
TS24 SSO: Skipped - TS24 URL not configured
```

### Exit-koder

| Kode | Betydning |
| --- | --- |
| `0` | Begge checks OK |
| `2` | GDI health endpoint unreachable |
| `3` | Invalid JSON fra GDI |
| `4` | GDI SSO-fejl (manglende secret, JWT-mint fejl, etc.) |
| `5` | TS24 SSO-fejl (endpoint unreachable, config-problemer) |

---

## GDI-side healthcheck

Endpoint: `/tools/sso_health.php`

Tjekker:

- `sso_enabled` – Er SSO aktiveret?
- `has_secret` – Er `GDI_SSO_SECRET` / `JWT_SECRET` konfigureret?
- `has_ts24_url` – Er `TS24_CONSOLE_URL` sat?
- `jwt_mint_ok` – Kan vi minte et test-token?

### Lokal test

```bash
curl http://127.0.0.1:8080/tools/sso_health.php
```

---

## TS24-side healthcheck

Endpoint: `{TS24_CONSOLE_URL}/api/auth/sso-health`

Tjekker:

- `secretConfigured` – Er `VITE_SSO_JWT_SECRET` sat?
- `usesHS256` – Bruger TS24 HS256-algoritmen?
- `expectedIss` – Forventet issuer
- `expectedAud` – Forventet audience
- `recentErrors` – Nylige token-verifikationsfejl

### Stub-tilstand (lokal udvikling)

Ved `--ts24-stub` flag bruges `/tools/ts24_health_stub.php` i stedet for det rigtige TS24-endpoint:

```bash
npm run sso:health  # Bruger stub automatisk
```

---

## 🚀 Ops-test (Prod)

> **Status (2025-12-01):** DNS + cert er verificeret og live. Nedenstående curl-kommando bør returnere HTTP 200.

### Manuel verifikation af TS24 prod-endpoint

```bash
curl -I https://intel24.tstransport.app/sso-login
```

**Forventet resultat:**

- HTTP-status: `200 OK` eller `3xx` redirect
- Certifikat: Gyldigt SSL/TLS
- Ingen DNS-fejl

### Fejlsituationer

| Symptom | Årsag | Handling |
| --- | --- | --- |
| `NXDOMAIN` | DNS-problem | Tjek DNS-konfiguration, kontakt TS24-team |
| `ERR_CONNECTION_REFUSED` | TS24-server ikke kørende | Kontakt TS24-team |
| `SSL certificate problem` | Certifikat-fejl | Tjek certifikat-udstedelse |
| `HTTP 404` | `/sso-login` endpoint ikke deployed | Bekræft TS24-deployment |

---

## CI-integration

Healthcheck køres automatisk i `.github/workflows/visual-regression.yml` som preflight før Playwright-tests.

```yaml
- name: SSO Stack Health Preflight
  run: npm run sso:health
```

Ved fejl stoppes pipelinen før testene køres.

---

## Relateret dokumentation

- `docs/ts24_sso_bridge.md` – Canonical TS24 entry URL og ejerskab
- `docs/sso_ops_runbook.md` – Drift og fejlsøgning
- `docs/sso_v1_signoff_gdi.md` – GDI sign-off checklist
- `docs/ci_pipelines.md` – CI/CD pipeline-oversigt
