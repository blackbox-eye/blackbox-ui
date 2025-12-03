# 📨 EKSTERN POST – TIL TS24 TEAM

**Fra:** ALPHA Development Team (GDI)
**Til:** TS24 Development Team
**Dato:** 2. december 2025
**Emne:** SSO Integration – Konfiguration af TS24 Login Redirect
**Prioritet:** 🔴 Høj
**Status:** ✅ AFTALT - Afventer secret deployment

---

## ✅ Aftalt Konfiguration

| Parameter | Værdi |
|-----------|-------|
| SSO URL | `intel24.blackbox.codes/sso-login?sso=<JWT>` |
| Fallback URL | `intel24.blackbox.codes/login` |
| JWT Algorithm | HS256 |
| Issuer (iss) | `ts24-intel` |
| Audience (aud) | `ts24-intel` |

### JWT Payload Struktur
```json
{
  "iss": "ts24-intel",
  "aud": "ts24-intel",
  "sub": "<bruger-id>",
  "name": "<visningsnavn>",
  "role": "admin|user",
  "exp": <unix-timestamp>,
  "iat": <unix-timestamp>
}
```

---

## 🔐 Shared Secret

**ALPHA har genereret et 256-bit (32 bytes) secret.**

⚠️ **SECRET DELES VIA SIKKER KANAL (1Password/Signal)** - ikke i denne fil!

### Deployment Instructions:

**TS24 Team:**
```bash
# Sæt i production environment
SSO_JWT_SECRET=<secret-delt-via-sikker-kanal>
```

**ALPHA Team:**
```bash
# Sæt i production environment
GDI_SSO_SECRET=<samme-secret>
# eller
JWT_SECRET=<samme-secret>
```

---

## 📋 Næste Skridt

| # | Handling | Ansvarlig | Status |
|---|----------|-----------|--------|
| 1 | Generér shared secret (256-bit) | ALPHA Team | ✅ Færdig |
| 2 | Del secret via sikker kanal | ALPHA Team | ⏳ Afventer |
| 3 | Sæt `SSO_JWT_SECRET` i production | TS24 Team | ⏳ Afventer |
| 4 | Sæt `GDI_SSO_SECRET` i production | ALPHA Team | ⏳ Afventer |
| 5 | Test med `/api/auth/sso-health` | TS24 Team | ⏳ Afventer |
| 6 | Test med `/tools/sso_health.php` | ALPHA Team | ⏳ Afventer |
| 7 | End-to-end SSO test | Begge teams | ⏳ Afventer |

---

## 🧪 Test Endpoints

**ALPHA/GDI Side:**
```
https://blackbox.codes/tools/sso_health.php
```

**TS24 Side:**
```
https://intel24.blackbox.codes/api/auth/sso-health
```

---

## 📎 Referencer

- **Commit:** `a12c122`
- **GDI Access Hub:** `https://blackbox.codes/agent-access.php`
- **JWT Helper:** `includes/jwt_helper.php`

---

*BLACKBOX EYE™ – Intelligent Sikkerhed. Klar til Handling.*
