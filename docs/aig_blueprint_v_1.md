# 🛠️ SYSTEMBLUEPRINT & HANDLINGSPLAN – ALPHA Interface GUI (Blackbox E.Y.E)

**Version:** v1.0  
**Dato:** 2025-07-01  
**Ansvarlig:** AIG AlphaDEV  
**Godkendt af:** Agent NEX

---

## 🎯 FORMÅL

Systemblueprintet beskriver ALPHA Interface GUI (AIG) som modulær webfrontend til BLACKBOX CODES-platformen (Blackbox E.Y.E). Formålet er at tilbyde et brugervenligt kontrolpanel for missions, intelligence, loghåndtering og værktøjer til agenter og administratorer. Systemet er hybrid (offline-first), med lokal krypteret database (MySQL/MariaDB) og cloud-backend (GreyEYE-platform).

---

## 📌 OVERORDNET ARKITEKTUR

- Frontend: PHP/HTML/CSS/JS med moduler for Login, Dashboard, Missions, Intelligence, Logs og Tools.
- Backend: REST-API'er (JSON/JWT) og WebSocket-forbindelser.
- Database: Modeller for agenter, missioner, logs.
- Sikkerhed: OAuth2/JWT, TLS, Vault/Secrets-integration.

---

## 🧱 MODUL- OG KOMPONENTBESKRIVELSE

| Modul            | Beskrivelse                                                             |
|------------------|-------------------------------------------------------------------------|
| **Login**        | Autentificering med PIN/password, klar til 2FA (nu midlertidigt plain-text for dev-test) |
| **Dashboard**    | Oversigtsside med status, alarmer, widgets                              |
| **Missions**     | Opret/søg/tildel missioner via REST API                                 |
| **Intelligence** | OSINT-datafeeds, analyser, rapporter                                    |
| **Logs**         | Central audit og logvisning                                             |
| **Tools**        | Værktøjer til stealth-operationer via API                               |
| **Vault**        | Håndtering af credentials via Vault (HashiCorp/Azure)                   |

---

### Login roadmap (midlertidig status)

- Aktuel løsning: lokal `agents`-tabel bevarer `password_hash()`-værdier; UI accepterer midlertidigt klartekstinput for hurtig verificering.
- TODO: migrér til GreyEYE central auth via `/api/auth/token` i tråd med MASTER BLUEPRINT, inkl. session-håndtering og token-refresh.

---

## 🚀 CI/CD, TEST & DEVOPS

- **Pipeline:** GitHub Actions (eller Jenkins) for build/test/deploy
- **Tests:** Unit-tests (PHPUnit), integrationstests, UI-tests (Cypress)
- **Kodeanalyse:** PHP_CodeSniffer, PHPStan/Psalm, SonarQube

---

## 🔐 SIKKERHED & COMPLIANCE

- **Autentificering:** OAuth2/JWT, 2FA, ghost-mode (dev-miljø bruger pt. simpel klartekst-match til hurtig test)
- **Kryptering:** TLS og server-side DB-kryptering
- **Audit-logs:** GDPR-compliant audit-trail
- **Vault:** Integrering af Vault til credentials

---

## 🔗 MODULINTEGRATION

| Modul        | Integrationsteknologi                 |
|--------------|--------------------------------------|
| **BCP**      | REST/Webhook, fælles JWT/OAuth2 auth |
| **ID-Matrix**| API til agent-identifikation          |
| **BBP**      | REST API for prompt-udveksling       |
| **GreyEYE**  | AI-eventbus/WebSocket                |

---

## 📖 DOKUMENTATION & VERSIONERING

- **Blueprint:** `/docs/SYSTEM_BLUEPRINT_AIG.md`
- **README:** Opdateret installationsvejledning
- **Driftshåndbog:** Fejlsøgning og supportguide
- **CHANGELOG:** Versionering efter SemVer
- **Swagger/OpenAPI:** API-specifikationer

---

## 📅 ROADMAP & TIDSPLAN

| Fase       | Tidsramme | Opgaver                                                        |
|------------|-----------|----------------------------------------------------------------|
| **1**      | 0–4 uger  | Infrastruktur, DB, Vault, CI/CD, missions og intelligence      |
| **2**      | 5–8 uger  | Tools, 2FA-login, ghost mode, tests                            |
| **3**      | 9–12 uger | BCP-integration, eventbus, penetrationstest                    |
| **4**      | 13–16 uger| Dokumentation, onboarding, canary-deploy, SemVer               |

---

## 📋 HANDLINGS- OG OPGAVELISTE

| Opgave                      | Ansvarlig  | Deadline  |
|-----------------------------|------------|-----------|
| Missions-modul              | AlphaDEV   | 01/08/25  |
| Tools-modul                 | AlphaDEV   | 15/08/25  |
| Intelligence-modul          | AlphaDEV   | 15/08/25  |
| Vault-integration           | AlphaDEV   | 01/08/25  |
| 2FA/token-login & Ghost Mode| AlphaDEV   | 15/08/25  |
| Databaseskema og scripts    | AlphaDEV   | 01/08/25  |
| Logging/audit               | AlphaDEV   | 01/08/25  |
| CI/CD-pipeline              | AlphaDEV   | 01/08/25  |
| Automatiserede tests        | AlphaDEV   | 01/08/25  |
| Statisk kodeanalyse         | AlphaDEV   | 01/08/25  |
| QA-plan & Review-workflow   | AI/Agent   | 15/07/25  |
| Sikkerhedstest              | AI/Agent   | 15/07/25  |
| BCP-integration             | AlphaDEV   | 01/09/25  |
| SYSTEM_BLUEPRINT.md         | AI/Agent   | 15/07/25  |
| Driftshåndbog               | AI/Agent   | 15/07/25  |
| Onboarding-guide            | AI/Agent   | 15/07/25  |

---

## 👥 ROLLER OG ANSVARSFORDELING

- **Agent NEX:** Arkitekt, final approval
- **AlphaDEV:** Implementering og udvikling
- **QA-Agent:** Tests og compliance-kontrol
- **AI/Agent:** Dokumentation, arkitektur, QA-assistance

---

## 📎 BILAG OG REFERENCER

- `/docs/SYSTEM_BLUEPRINT_AIG_v1.0.pdf`
- `/docs/CHANGELOG.md`
- API-specifikation (Swagger/OpenAPI)
- Statusrapporter i `/docs/reports/`

---

## ✅ GODKENDELSE

Denne blueprint-version v1.0 (2025-07-01) er godkendt af Agent NEX og er master-reference for hele AIG-projektet og Blackbox-økosystemet.

