# TS24 SSO Bridge – GDI Integration Guide

This document describes how ALPHA Interface GUI (GDI) integrates with the TS24 Intel Console via SSO v1.

---

## 🎯 Canonical TS24 Entry URL (GDI-perspektiv)

### URLs

| Type | URL |
| --- | --- |
| **SSO Entry (kanonisk)** | `https://intel24.tstransport.app/sso-login` |
| **Full SSO URL (GDI bygger)** | `https://intel24.tstransport.app/sso-login?sso=<JWT>` |
| **Manual Login (fallback)** | `https://intel24.tstransport.app/login` |

### Ejerskab

| Komponent | Ejes af | Ansvar |
| --- | --- | --- |
| Domæne `intel24.tstransport.app` | **ts24-intel-console** | DNS, certifikat, hosting |
| `/sso-login` endpoint | **ts24-intel-console** | Token-verifikation, session-oprettelse |
| `/login` endpoint | **ts24-intel-console** | Manuel login-flow (fallback) |
| `TS24_CONSOLE_URL` env-variabel | **ALPHA-Interface-GUI** | Konfiguration af target-URL |
| Agent Access TS24-link | **ALPHA-Interface-GUI** | Bygge SSO-link med JWT |
| SSO Token Minting | **ALPHA-Interface-GUI** | Udstedelse af HS256-signeret JWT |

### TS24 Dokumentationsreferencer

For detaljer om TS24-sidens implementering, se følgende filer i ts24-intel-console repoet:

- `ts24_login_flow.md` – Login-flow og token-verifikation
- `sso_v1_signoff_ts24.md` – TS24-sidens sign-off checklist

---

## Miljøvariabel

| Variabel | Beskrivelse |
| --- | --- |
| `TS24_CONSOLE_URL` | Base URL til TS24 SSO-entry. Default: `https://intel24.tstransport.app/sso-login` |

> **Status (2025-12-01):** DNS + cert er verificeret. Produktions-default peger nu på `/sso-login`. Miljøvariablen kan overskrives via env hvis anden URL ønskes.

---

## SSO Link-bygning (GDI)

GDI bygger TS24-linket på `agent-access.php`:

```php
$ts24Url = rtrim(BBX_TS24_CONSOLE_URL, '/');
$separator = strpos($ts24Url, '?') === false ? '?' : '&';
$ts24Link = $ts24Url . $separator . 'sso=' . urlencode($token);
```

Når `TS24_CONSOLE_URL` sættes til `https://intel24.tstransport.app/sso-login`, bliver det fulde link:

```
https://intel24.tstransport.app/sso-login?sso=eyJhbGciOiJIUzI1NiIs...
```

---

## JWT Payload

Tokens signeres med **HS256** og indeholder:

| Claim | Beskrivelse |
| --- | --- |
| `iss` | Origin site base URL (`BBX_SITE_BASE_URL`) |
| `aud` | `ts24` |
| `sub` | Agent identifier (`agents.agent_id`) |
| `uid` | Internal numeric agent ID |
| `name` | Display name (fallback: `agent_id`) |
| `role` | `admin` eller `operator` |
| `scope` | Array af tilladte områder |
| `iat` / `nbf` | Issued-at timestamp |
| `exp` | `iat + TTL` (default 600 sekunder) |

---

## Flowdiagram

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  GDI Login      │ ──► │  Agent Access   │ ──► │  TS24 Console   │
│  agent-login.php│     │  agent-access   │     │  /sso-login     │
└─────────────────┘     │  (mint JWT)     │     │  (verify JWT)   │
                        └─────────────────┘     └─────────────────┘
```

1. Agent logger ind via `agent-login.php`
2. JWT mintes og gemmes i session/cookie
3. Agent klikker TS24-link på `agent-access.php`
4. Browser navigerer til `{TS24_CONSOLE_URL}?sso=<JWT>`
5. TS24 verificerer token og opretter session

---

## Relateret dokumentation

- `docs/sso_gdi_ts24.md` – Teknisk JWT-specifikation
- `docs/sso_ops_runbook.md` – Drift og healthchecks
- `docs/sso_v1_signoff_gdi.md` – GDI sign-off checklist
- `docs/e2e_gdi_ts24_sso_test.md` – End-to-end testplan
- `docs/sso_healthcheck.md` – Healthcheck-guide inkl. prod-test
