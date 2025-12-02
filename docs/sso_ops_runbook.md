# GDI ⇄ TS24 SSO Operations Runbook

## A. Formål
Dette dokument er den operative manual til overvågning og fejlsøgning af SSO-broen mellem ALPHA-Interface-GUI (GDI) og ts24-intel-console. Hvor `docs/sso_gdi_ts24.md` beskriver den tekniske protokol, og `docs/e2e_gdi_ts24_sso_test.md` beskriver de manuelle end-to-end tests, fokuserer denne runbook på de konkrete skridt ops/dev skal tage for at holde integrationen sund i daglig drift.

## B. Sådan kører du SSO stack health (lokalt og i CI)

- Kør `npm run sso:health` fra repo-roden.
- Kommandoen wrapper `scripts/check_sso_health.php`, som udfører to kald:
  1. `tools/sso_health.php` (GUI/GDI) for at validere secrets, TS24-URL, JWT-bibliotek og en test-mint.
  2. `{TS24_CONSOLE_URL}/api/auth/sso-health` (TS24) for at sikre, at modtageren også er korrekt konfigureret.
- Resultaterne kombineres i ét samlet output (GDI + TS24).
- Det samme check kører som preflight i `.github/workflows/visual-regression.yml`. Hele Playwright-jobbet springes over, hvis stack-health fejler, så fejl fanges tidligt.

## C. Forstå outputtet (GDI SSO / TS24 SSO)

Scriptet skriver to linjer:

```text
GDI SSO: OK
TS24 SSO: OK
```



```text
GDI SSO: Failed flags: has_secret (notes: Missing GDI_SSO_SECRET / JWT_SECRET)
TS24 SSO: Skipped - TS24 URL not configured
```
eller


```text
GDI SSO: OK
TS24 SSO: Issues: secretConfigured=false (notes: expectedIss empty | recentErrors=INVALID_SIGNATURE,EXPIRED)
```

Exitkoden er forskellig fra nul, når enten GUI- eller TS24-delen har problemer. Brug outputlinjerne til at afgøre, hvor du skal starte.

## D. Typiske fejlscenarier og næste skridt

| Scenario | Typisk årsag | Løsning |
| --- | --- | --- |
| `GDI SSO: Failed flags: has_secret` | `GDI_SSO_SECRET` / `JWT_SECRET` mangler i miljøet | Sæt hemmeligheden i env/server, deploy igen, kør `npm run sso:health` |
| `GDI SSO: Failed flags: jwt_mint_ok` | `firebase/php-jwt` mangler eller autoload fejler | Kør `composer install`, bekræft `vendor/autoload.php`, ret eventuelle PHP-fejl |
| `TS24 SSO: Endpoint unreachable (HTTP 502)` | `TS24_CONSOLE_URL` er forkert, eller ts24-intel-console kører ikke | Verificer base-URL, at miljøet svarer, og at `/api/auth/sso-health` er åbent |
| `TS24 SSO: Issues: secretConfigured=false` eller `usesHS256=false` | TS24’s `VITE_SSO_JWT_SECRET` matcher ikke GDI, eller algoritmen er ikke HS256 | Harmoniser secrets og algoritme jf. kontrakten, opdater ts24-intel-console env, retdeploy |
| `TS24 SSO: OK (notes: recentErrors=EXPIRED,INVALID_SIGNATURE)` | Health er grøn, men TS24 rapporterer nylige verificeringsfejl | Gennemgå TS24-logfilerne for tokenfejl, tjek klokkesynkronisering og udløbstider |

Når `expectedIss` eller `expectedAud` rapporteres som tomme, betyder det typisk, at TS24 ikke er fuldt konfigureret. Sørg for, at disse felter svarer til værdierne i `docs/sso_gdi_ts24.md`.

## E. Related docs

- `docs/sso_gdi_ts24.md` – teknisk specifikation af payload, claims og sikkerhedskrav. Bruges når kontrakten skal ændres eller verificeres.
- `docs/e2e_gdi_ts24_sso_test.md` – manuelle/verificerende testcases til releases, inklusive anbefalet rækkefølge. Bruges ved større ændringer eller release sign-off.
- `docs/sso_ops_runbook.md` (dette dokument) – dagligdags drift, healthchecks og fejlsøgning. Bruges, når health-scriptet fejler eller når ops skal reagere hurtigt.
- `docs/sso_v1_signoff_gdi.md` – GUI SSO v1 sign-off checklist til overblik over, hvornår GDI-siden er klar.

## F. SSO audit log

- GDI skriver nu et JSON-lines auditlog til `logs/sso_events.log`. Hver linje indeholder UTC timestamp, event-navn, niveau, agent-id (når kendt) og relevante felter (fingerprint, status m.m.).
- Ops/dev bør inspicere filen når:
  - der opstår gentagne login-problemer, og man vil vide om tokens bliver minted
  - `npm run sso:health` (eller CI) fejler, og man vil se de præcise `gdi_status`/`ts24_status` værdier

Eksempler:

```json
{"timestamp":"2025-11-30T10:11:12Z","source":"GDI_SSO","event":"SSO_TOKEN_ISSUED","level":"INFO","agent_id":"ops01","token_fingerprint":"9f8c1b2d4a5e6f70","expires_at":1732962000}
{"timestamp":"2025-11-30T10:15:00Z","source":"GDI_SSO","event":"SSO_STACK_HEALTH_FAIL","level":"ERROR","gdi_status":"Failed flags: has_secret","ts24_status":"Endpoint unreachable (HTTP 502)","ts24_url":"https://ts24.example/login"}
```

Auditloggen indeholder aldrig selve JWT’en—kun en forkortet SHA-1 fingerprint—så den kan bruges sikkert til fejlsøgning uden at lække tokens.

### CLI: Se de seneste SSO events

- Brug `npm run sso:audit:tail` for at vise de seneste ~20 linjer (read-only).
- Kommandoen kalder `scripts/inspect_sso_log.php`, som læser `logs/sso_events.log` og udskriver de nyeste events i rækkefølge. Perfekt til hurtigt overblik over `SSO_TOKEN_ISSUED` / `SSO_STACK_HEALTH_FAIL` uden at åbne filen manuelt.
