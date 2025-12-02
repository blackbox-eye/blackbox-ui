# TS24 SSO Bridge – GDI Integration Guide

> **Senest opdateret / Last updated:** 2025-12-01
> **Version:** 1.1

Denne guide beskriver, hvordan ALPHA Interface GUI (GDI) integrerer med TS24 Intel Console via SSO v1.

---

## 🎯 Canonical TS24 Entry URL

| Type | URL |
|------|-----|
| **SSO Entry (kanonisk)** | `https://intel24.blackbox.codes/sso-login` |
| **Full SSO URL (GDI bygger)** | `https://intel24.blackbox.codes/sso-login?sso=<JWT>` |
| **Manual Login (fallback)** | `https://intel24.blackbox.codes/login` |

> **Status (2025-12-02):** DNS + cert er verificeret. GDI default peger på `/sso-login`.

---

## Ejerskab

| Komponent | Ejes af | Ansvar |
|-----------|---------|--------|
| Domæne `intel24.blackbox.codes` | **ts24-intel-console** | DNS, cert, hosting |
| `/sso-login` endpoint | **ts24-intel-console** | Token-verifikation, session |
| `/login` endpoint | **ts24-intel-console** | Fallback login |
| `TS24_CONSOLE_URL` env | **ALPHA-Interface-GUI** | Config af target-URL |
| Agent Access TS24-link | **ALPHA-Interface-GUI** | Byg SSO-link med JWT |
| SSO Token Minting | **ALPHA-Interface-GUI** | Udstedelse af HS256-signeret JWT |

---

## Arkitektur

```text
┌─────────────────────┐    JWT    ┌─────────────────────┐
│   Blackbox EYE      │ ───────── │   TS24 Console      │
│   (agent-login.php) │  ?sso=    │   (tstransport.app) │
└─────────────────────┘           └─────────────────────┘
         │                                  │
         ▼                                  ▼
┌─────────────────────┐           ┌─────────────────────┐
│   Audit Log         │           │   Session Manager   │
│   (sso_audit.php)   │           │                     │
└─────────────────────┘           └─────────────────────┘
```

---

## SSO Link-bygning (GDI)

GDI bygger TS24-linket på `agent-access.php`:

```php
$ts24Url = rtrim(BBX_TS24_CONSOLE_URL, '/');
$separator = strpos($ts24Url, '?') === false ? '?' : '&';
$ts24Link = $ts24Url . $separator . 'sso=' . urlencode($token);
```

Når `TS24_CONSOLE_URL` sættes til `https://intel24.blackbox.codes/sso-login`, bliver det fulde link:

```text
https://intel24.blackbox.codes/sso-login?sso=eyJhbGciOiJIUzI1NiIs...
```

---

## JWT Payload

Tokens signeres med **HS256** og indeholder:

| Claim | Beskrivelse |
|-------|-------------|
| `iss` | Origin site base URL (`BBX_SITE_BASE_URL`) |
| `aud` | `ts24` |
| `sub` | Agent identifier (`agents.agent_id`) |
| `uid` | Internal numeric agent ID |
| `name` | Display name (fallback: `agent_id`) |
| `role` | `admin` eller `operator` |
| `scope` | Array af tilladte områder |
| `iat` / `nbf` | Issued-at timestamp |
| `exp` | `iat + TTL` (default 600 sekunder) |
| `jti` | Unique token ID (replay-prevention) |

### Eksempel payload

```json
{
  "iss": "https://blackbox.codes",
  "aud": "ts24",
  "sub": "agent_12345",
  "uid": 42,
  "name": "Agent Smith",
  "role": "operator",
  "scope": ["read:intel", "read:alerts"],
  "iat": 1733000400,
  "nbf": 1733000400,
  "exp": 1733004000,
  "jti": "a1b2c3d4-e5f6-7890-abcd-ef1234567890"
}
```

---

## Flow

```text
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  GDI Login      │ ──► │  Agent Access   │ ──► │  TS24 Console   │
│  agent-login.php│     │  (mint JWT)     │     │  /sso-login     │
└─────────────────┘     └─────────────────┘     │  (verify JWT)   │
                                                └─────────────────┘
```

1. Agent logger ind via `agent-login.php`
2. JWT mintes og gemmes i session/cookie
3. Agent klikker TS24-link på `agent-access.php`
4. Browser navigerer til `{TS24_CONSOLE_URL}?sso=<JWT>`
5. TS24 verificerer token og opretter session

---

## Audit Logging

Alle SSO-events logges i `sso_audit.php`:

| Event | Beskrivelse |
|-------|-------------|
| `sso_token_generated` | JWT oprettet |
| `sso_redirect_initiated` | Bruger redirectet til TS24 |
| `sso_validation_success` | Token verificeret |
| `sso_validation_failure` | Token fejlede |

> **Sikkerhed:** Fuld JWT logges aldrig – kun `jti` og `sub`.

---

## Miljøvariabel

| Variabel | Beskrivelse |
|----------|-------------|
| `TS24_CONSOLE_URL` | Base URL til TS24 SSO-entry. Default: `https://intel24.blackbox.codes/sso-login` |
| `TS24_SSO_SECRET` | Delt secret til JWT-signering (min. 256 bit) |
| `SSO_TOKEN_TTL` | Token TTL i sekunder (default 300) |

---

## Sikkerhedsovervejelser

| Risiko | Mitigering |
|--------|------------|
| Token-intercept | HTTPS, kort TTL |
| Replay-angreb | JTI-tracking |
| Nøglekompromittering | Regelmæssig rotation |
| Clock skew | ±30 s tolerance på exp/nbf |

---

## Test lokalt

```bash
# Start stub server
php -S 127.0.0.1:8091

# Healthcheck
npm run sso:health
```

---

## Relateret dokumentation

- `docs/sso_gdi_ts24.md` – Teknisk JWT-specifikation
- `docs/sso_healthcheck.md` – Healthcheck-guide
- `docs/sso_ops_runbook.md` – Drift og fejlsøgning
- `docs/sso_v1_signoff_gdi.md` – GDI sign-off checklist
- `docs/e2e_gdi_ts24_sso_test.md` – End-to-end testplan

TS24-sidens dokumentation findes i **ts24-intel-console** repoet:

- `ts24_login_flow.md`
- `sso_v1_signoff_ts24.md`

---

## Changelog

| Dato | Ændring | PR |
|------|---------|----|
| 2025-11-30 | Første version | #61 |
| 2025-12-01 | Sammenlagt arkitektur- og ejerskabsafsnit | Current |
