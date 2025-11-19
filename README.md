Her er en fuldt opdateret `README.md` til roden af dit repo. Erstat hele indholdet i din nuværende `README.md` med nedenstående, så alle nye `/docs/`-filer og rapporter linkes korrekt:

```markdown
# ALPHA Interface GUI  
> **BLACKBOX.CODES | Enterprise Edition v1.0**

---

## 📋 Om Projektet

**ALPHA Interface GUI** er den officielle, responsive brugerflade for BLACKBOX.CODES-platformen (kodenavn: BLACKBOX E.Y.E).  
Dette repo leverer frontend- og webkomponenter til enterprise cyber operations, OSINT, secure access og AI-drevet mission control.

---

## 🏗️ Features & Komponenter

- **Responsivt dashboard** (PHP, HTML, CSS, JS)  
- **Role-based login:** Agent/Admin, PIN/Password (klar til 2FA/Token-integration)  
- **Modulbaseret navigation:** Dashboard, Missions, Intelligence, Logs, Tools  
- **Realtime systemstatus & alerts**  
- **Stealth log management** (eksport/slet, live-view)  
- **Klar til CI/CD-pipeline & Vault-integration**  
- **Kompatibel med Hak5 Cloud C2 & AI Codex workflows**

---

## 🗂️ Mappestruktur

```

/  # Projektrod
├── assets/            # Billeder, stylesheets, scripts
├── includes/          # PHP includes (header, footer mv.)
├── .well-known/       # Certifikater & validation
├── admin.php          # Adminpanel
├── dashboard.php      # Dashboard view
├── index.php          # Login-side
├── logout.php         # Logout-handler
├── db.php             # Database access/config
├── settings.php       # Indstillinger
├── style.css          # Hoved CSS
├── script.js          # Hoved JS
├── .github/           # CI/CD workflows
└── docs/              # Dokumentation og rapporter

````

---

## 🚦 Installation & Deployment

1. **Krav:**  
   - PHP 7.4+  
   - MySQL/MariaDB  
   - Apache/nginx  
   - Git (til CI/CD og deploy)

2. **Klon repo:**  
   ```bash
   git clone https://github.com/AlphaAcces/ALPHA-Interface-GUI.git
````

3. **Upload & konfiguration:**

   * Upload `assets/`, PHP-filer og `.well-known/` til `public_html`.
   * Importér SQL-script til `agents`, `hosts` mv.
   * Redigér `db.php` med korrekte DB-credentials.
   * Konfigurer Vault/Secrets (HashiCorp/Azure) ifølge blueprint.
   * Tilpas branding ved at udskifte logo, CSS og tekster.

4. **CI/CD:**

   * Se `.github/workflows/ci.yml` for build/test/deploy til staging/production.
   * Sørg for at alle secrets ligger som GitHub Secrets.

---

## 🔒 Sikkerhed & Compliance

* **Password auth (midlertidigt dev-setup):** Agent-login matcher nu klartekst-passwords for hurtig test; revert til `password_hash()` / `password_verify()` før produktion
* **Prepared statements:** MySQLi/PDO for SQL-injection-beskyttelse
* **Session management:** Secure cookies, `session_start()`
* **Role-based access control:** Admin vs. Agent
* **Vault-integration:** Klar til HashiCorp/Azure Secrets
* **Audit logging & change tracking**
* **GDPR / Privacy-by-Design:** Pseudonymisering og logging efter enterprise-standard

---

## 👤 Agentroller & Adgang

| Rolle     | Funktioner                                            | Data-adgang |
| --------- | ----------------------------------------------------- | ----------- |
| **Admin** | Opret/vedligehold agenter, se alle logs, systemstatus | Fuld adgang |
| **Agent** | Missions, logs, stealth tools, basis-dashboard        | Begrænset   |

---

## 📖 Dokumentation & Blueprint

Alle dokumenter ligger nu under `/docs/`:

### 📑 Blueprint & Handlingsplan

* [SYSTEM\_BLUEPRINT\_AIG\_v1.0.md](/docs/SYSTEM_BLUEPRINT_AIG_v1.0.md)
* [aig\_blueprint\_v1.md](/docs/aig_blueprint_v1.md)

### 📄 Versionerede rapporter (i `/docs/reports/`)

| Dokumenttype  | Filnavn                                                                                             | Version | Dato       | Ansvarlig |
| ------------- | --------------------------------------------------------------------------------------------------- | ------- | ---------- | --------- |
| Onboarding    | [v1.1\_20250707\_onboarding.md](docs/reports/v1.1_20250707_onboarding.md)                           | v1.1    | 2025-07-07 | ALPHADEV  |
| Statusrapport | [v1.2\_20250701\_statusrapport.md](docs/reports/v1.2_20250701_statusrapport.md)                     | v1.2    | 2025-07-01 | ALPHADEV  |
| Sprintplan    | [v1.3\_20250705\_sprintplan.md](docs/reports/v1.3_20250705_sprintplan.md)                           | v1.3    | 2025-07-05 | ALPHADEV  |
| Auditlog      | [v1.4\_20250710\_auditlog.md](docs/reports/v1.4_20250710_auditlog.md)                               | v1.4    | 2025-07-10 | QA-agent  |
| Masterprompt  | [AIG\_MASTERPROMPT\_v1.2\_20250630.md](docs/reports/docs/reports/AIG_MASTERPROMPT_v1.2_20250630.md) | v1.2    | 2025-06-30 | NEX       |

---

## 🗓️ Versionshistorik

Se [CHANGELOG.md](CHANGELOG.md) for detaljeret release-tracking.

---

## 🤝 Bidrag & Udvikling

* Følg branch-/PR-politik og semver.
* QA & CI er obligatorisk før merge til `main`.
* Sørg for at opdatere dokumentation ved hver major/minor release.
* Security audits og code reviews kræves før produktion.

---

## 📄 Licens

Dette projekt er frigivet under **MIT License** (se [LICENSE](LICENSE)).

---

## 📞 Kontakt & Support

* E-mail: [ops@blackbox.codes](mailto:ops@blackbox.codes)
* Discord: BLACKBOX E.Y.E. Ops Center

Dokumentation og deployment-guides opdateres løbende. For enterprise-integration eller revision, kontakt ALPHA Lead via ovenstående.

---

## 🔐 Secret rotation

Dette repository bruger Actions-secrets til FTP-deployment: `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD`, `FTP_REMOTE_PATH`.

For at rotere en secret:
1. Gå til repository Settings -> Secrets and variables -> Actions.
2. Klik `New repository secret` for at oprette eller `Update` for at opdatere en eksisterende.
3. Indsæt den nye værdi og gem.
4. Hvis du også oprettede/tilbagekaldt credentials hos hosting-udbyderen (fx cPanel), sørg for at tilbagekalde de gamle credentials hos udbyderen.
5. Efter rotation, kør workflowet igen fra Actions-fanen for at bekræfte at deployment + smoke tests lykkes.

Tips:
- Sørg for at `FTP_REMOTE_PATH` peger på din site-root (fx `/public_html` eller `/`).
- Overvej at bruge en separat FTP-bruger med begrænsede rettigheder til automatisk deploy for bedre sikkerhed.
- Lad være med at indsætte secrets i PR-beskrivelser eller chat — brug GitHub Secrets.

````