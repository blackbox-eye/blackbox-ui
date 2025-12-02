# 📨 EKSTERN POST – TIL TS24 TEAM

**Fra:** ALPHA Development Team (GDI)  
**Til:** TS24 Development Team  
**Dato:** 2. december 2025  
**Emne:** SSO Integration – Konfiguration af TS24 Login Redirect  
**Prioritet:** 🔴 Høj  

---

## 📋 Baggrund

GDI-siden (ALPHA Interface GUI) har nu implementeret fuld SSO-integration med TS24-konsollen. Når en bruger på `blackbox.codes` vælger "Åbn TS24 konsol", håndterer vi:

1. ✅ Bruger-autentificering via GDI login
2. ✅ JWT-token generering efter succesfuldt login
3. ✅ Redirect til `intel24.blackbox.codes/sso-login?sso=<JWT>`

---

## 🎯 Anmodning til TS24 Team

### Problem
Når brugeren klikker "Åbn TS24 konsol" på `agent-access.php`, skal de sendes til **TS24's login-side** (som vist i jeres design med TS24 Intelligence branding), **IKKE** til GDI's login-side.

### Nuværende flow (forkert)
```
Bruger klikker "Åbn TS24" → GDI Login → ??? → TS24
```

### Ønsket flow (korrekt)
```
Bruger klikker "Åbn TS24" → TS24 Login Side (intel24.blackbox.codes) → TS24 Dashboard
```

---

## 🔧 Teknisk Implementering

### Option A: Direkte link til TS24 login (anbefalet for nu)
GDI sender brugeren direkte til TS24's login-formular:
```
https://intel24.blackbox.codes/login
```

**Kræver fra TS24:**
- Bekræft URL til jeres login-side (er det `/login`, `/sso-login`, eller andet?)

### Option B: SSO med JWT (fremtidig integration)
GDI sender JWT-token til TS24 for automatisk login:
```
https://intel24.blackbox.codes/sso-login?sso=<JWT_TOKEN>
```

**Kræver fra TS24:**
1. Accepter `?sso=<token>` query parameter
2. Validér JWT med delt hemmelighed (`GDI_SSO_SECRET`)
3. Log bruger ind automatisk hvis token er gyldig
4. Vis login-formular som fallback hvis token mangler/ugyldig

---

## ❓ Spørgsmål til TS24 Team

1. **Login URL:** Hvad er den korrekte URL til jeres login-side?
   - `https://intel24.blackbox.codes/login` ?
   - `https://intel24.blackbox.codes/sso-login` ?
   - Andet?

2. **SSO Integration:** Er I klar til at modtage JWT-tokens fra GDI?
   - Hvis ja: Hvilken secret skal vi bruge til signering?
   - Hvis nej: Vi kan starte med direkte link til jeres login

3. **Token Format:** Ønsket JWT payload struktur:
   ```json
   {
     "sub": "agent-id",
     "uid": "database-id", 
     "name": "Agent Name",
     "role": "agent|admin",
     "iat": 1733140800,
     "exp": 1733141400,
     "aud": "https://intel24.blackbox.codes/sso-login"
   }
   ```
   Er dette acceptabelt, eller har I andre krav?

---

## 📎 Relaterede Ressourcer

- **GDI SSO Health Check:** `https://blackbox.codes/tools/sso_health.php`
- **GDI Access Hub:** `https://blackbox.codes/agent-access.php`
- **GDI Dokumentation:** `docs/ts24_sso_bridge.md`
- **Commit Reference:** `7da7bdd` (fix: Handle TS24 launch when JWT unavailable)

---

## ✅ Handling Required

| # | Handling | Ansvarlig | Status |
|---|----------|-----------|--------|
| 1 | Bekræft TS24 login URL | TS24 Team | ⏳ Afventer |
| 2 | Del SSO secret med ALPHA team | TS24 Team | ⏳ Afventer |
| 3 | Implementer JWT validering | TS24 Team | ⏳ Afventer |
| 4 | Opdater GDI redirect URL | ALPHA Team | ⏳ Afventer TS24 svar |
| 5 | End-to-end test | Begge teams | ⏳ Afventer |

---

## 📞 Kontakt

**ALPHA Development Team**  
- Repository: `AlphaAcces/ALPHA-Interface-GUI`
- Branch: `main`

Ved spørgsmål, kontakt ALPHA teamet direkte.

---

*BLACKBOX EYE™ – Intelligent Sikkerhed. Klar til Handling.*
