# SSO v1 Sign-off – GDI / Blackbox UI

## A. Purpose / Scope

Dette dokument beskriver, hvornår Blackbox UI (GDI-siden) betragter SSO v1 som klar til sign-off. Det anvendes sammen med:

- `docs/sso_gdi_ts24.md` – teknisk kontrakt og payload-specifikation.
- `docs/e2e_gdi_ts24_sso_test.md` – detaljerede end-to-end tests.
- `docs/sso_ops_runbook.md` – drift, healthchecks og auditlog-vejledning.
- TS24’s egen sign-off-guide (hostet i TS24-repoet), som dækker modtager-siden.

## B. Preconditions / Environment

- `GDI_SSO_SECRET` er sat og matcher TS24’s hemmelighed (`VITE_SSO_JWT_SECRET`).
- `GDI_SSO_TTL_SECONDS` er konfigureret til aftalt værdi (typisk 600 sekunder).
- `TS24_CONSOLE_URL` peger på det miljø, som test/sign-off skal foregå på.
- Kommandoer, der skal være grønne:
  - `npm run sso:health` (lokalt eller i CI) → både “GDI SSO” og “TS24 SSO” = OK.
  - GitHub Actions workflow `Visual Regression` (som også kører health-preflight) er grøn.

## C. Functional checks (GDI-side)

- Login via `agent-login.php` med en gyldig agent.
- Bekræft at Agent Access-siden viser TS24-kortet med `data-sso-active="true"`, når SSO er aktiveret.
- Tjek at `href` på TS24-knappen linker til `{TS24_CONSOLE_URL}/...` og indeholder `?sso=`-parametret.

## D. End-to-end SSO tests (high-level)

Se `docs/e2e_gdi_ts24_sso_test.md` for detaljer. Minimumskrav:

- **Happy path:** Start fra GDI-login, klik TS24-linket, land i TS24-dashboard uden fejl.
- **Expired/malformed token:** Genbrug et ældre link eller manipuler `sso`-parametret; TS24 skal afvise tokenet og tilbyde manuel login/fejl-banner.

## E. Health & audit

- Kør `npm run sso:health` og bekræft at begge linjer er OK.
- Kør `npm run sso:audit:tail` og se, at der logges `SSO_TOKEN_ISSUED` events efter login, og at der ikke er gentagne `SSO_STACK_HEALTH_FAIL`-events i et stabilt miljø.
- Hvis yderligere analyse er nødvendig, åbn `logs/sso_events.log` (JSON-lines) for fuld historik.

## F. Ready for v1 sign-off

Tjek følgende, før du markerer SSO v1 som godkendt:

- [ ] Konfiguration er på plads (hemmeligheder, TTL, TS24 URL).
- [ ] Login + Agent Access flow virker (minting og UI indikationer).
- [ ] TS24-link indeholder korrekt base-URL og `?sso=`-parameter.
- [ ] End-to-end scenarier (happy path + failure cases) fra E2E-dokumentet er gennemført uden blokeringer.
- [ ] Healthcheck og auditlog viser grøn status uden vedvarende fejl.
- Checked by: ________________________  Dato: ____________________
