# CI/CD Workflows Oversigt

Dette dokument giver et overblik over alle GitHub Actions workflows i blackbox-ui repositoriet.

## Workflow-oversigt

| Workflow | Fil | Triggers | Formål |
|----------|-----|----------|--------|
| **CI & Deploy (Secure)** | `ci.yml` | `push:main` (paths-filtered), `workflow_dispatch` | FTP deployment med FTPS, smoke tests |
| **Visual Regression** | `visual-regression.yml` | `push:main`, `pull_request:main` (paths-filtered), `workflow_dispatch` | Playwright visuelle tests |
| **Cloudflare Pages Deploy** | `cloudflare-pages.yml` | `push:main`, `pull_request:main` (paths-filtered), `workflow_dispatch` | Cloudflare Pages staging/production |
| **CodeQL** | `codeql-analysis.yml` | `push:main`, `pull_request:main` (paths-filtered), `schedule` (ugentligt), `workflow_dispatch` | Sikkerhedsscanning af PHP/JS |
| **Lighthouse Audit** | `lighthouse.yml` | `push:main` (paths-filtered), `workflow_dispatch` | Performance og accessibility audit |
| **Sprint 5 Smoke Test** | `sprint5-smoke-test.yml` | `pull_request:main` (paths-filtered) | Endpoint-tests og Lighthouse |

---

## Detaljeret beskrivelse

### 1. CI & Deploy (Secure) – `ci.yml`

**Triggers:**
- Push til `main` branch (ignorerer `.github/agents/**`, `docs/**`, `**/*.md`)
- Manuel dispatch

**Jobs:**
1. **Build & Verify** – Validerer secrets og kritiske filer
2. **Delete index.html** – Fjerner statisk index.html på FTP server via FTPS
3. **FTP Deploy** – Deployer via SamKirkland/FTP-Deploy-Action med FTPS
4. **Smoke Tests** – Kører 6 endpoint-tests (root, about, cases, contact, index.html removal, DirectoryIndex)

**Secrets brugt:**
- `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD`, `FTP_REMOTE_PATH`
- `CF_ZONE_ID`, `CF_API_TOKEN` (valgfri, til cache purge)
- `SITE_URL` (valgfri)

**Concurrency:**
- `ci-deploy-${{ github.ref }}`
- `cancel-in-progress: false` (for at undgå at afbryde igangværende deployments)

---

### 2. Visual Regression – `visual-regression.yml`

**Triggers:**
- Push til `main` branch
- Pull requests mod `main`
- Manuel dispatch

**Paths inkluderet:**
- `includes/**`, `assets/**`, `*.php`, `lang/**`

**Paths ignoreret:**
- `.github/agents/**`, `docs/**`, `**/*.md`, `README.md`, `CHANGELOG.md`

**Jobs:**
1. **Visual Tests** – Kører Playwright visuelle tests og uploader screenshots som artifacts

**Concurrency:**
- `visual-regression-${{ github.ref }}`
- `cancel-in-progress: true` (auto-cancel gamle runs)

---

### 3. Cloudflare Pages Deploy – `cloudflare-pages.yml`

**Triggers:**
- Push til `main` branch (paths-filtered)
- Pull requests mod `main` (paths-filtered)
- Manuel dispatch med environment valg (staging/production)

**Jobs:**
1. **Build & Prepare** – Validerer filer og Cloudflare secrets
2. **Deploy to Staging** – Deployer til preview URL
3. **Verify Staging** – Kører 5 verifikationstests
4. **Deploy to Production** – Manuel godkendelse krævet

**Secrets brugt:**
- `CF_API_TOKEN` eller `CLOUDFLARE_API_TOKEN`
- `CF_ACCOUNT_ID` eller `CLOUDFLARE_ACCOUNT_ID`
- `CF_PAGES_PROJECT_NAME` (valgfri, default: `blackbox-codes`)

**Concurrency:**
- `cloudflare-${{ github.ref }}`
- `cancel-in-progress: true`

---

### 4. CodeQL – `codeql-analysis.yml`

**Triggers:**
- Push til `main` branch (ignorerer `.github/agents/**`, `docs/**`, `**/*.md`)
- Pull requests mod `main` (ignorerer `.github/agents/**`, `docs/**`, `**/*.md`)
- Schedule: Søndag kl. 00:00 UTC (ugentligt)
- Manuel dispatch

**Jobs:**
1. **PHP CodeQL Analysis** – Scanner PHP-kode for sikkerhedssårbarheder
2. **JavaScript CodeQL Analysis** – Gated (kræver `ENABLE_JS_CODEQL` variable)

**Features:**
- Graceful håndtering når Code Scanning ikke er aktiveret
- Uploades til Security tab

**Concurrency:**
- `codeql-${{ github.ref }}`
- `cancel-in-progress: true`

---

### 5. Lighthouse Audit – `lighthouse.yml`

**Triggers:**
- Push til `main` branch (ignorerer `.github/agents/**`, `docs/**`, `**/*.md`)
- Manuel dispatch

**Jobs:**
1. **Lighthouse CI** – Kører performance og accessibility audit mod live site

**URL testet:**
- `SITE_URL` secret eller fallback til `https://blackbox.codes`

---

### 6. Sprint 5 Smoke Test – `sprint5-smoke-test.yml`

**Triggers:**
- Pull requests mod `main` (kun ved ændringer i specifikke paths)

**Paths:**
- `assets/js/site.js`, `assets/js/site.min.js`
- `*.php`, `lang/*.json`, `includes/*.php`

**Jobs:**
1. **Smoke Test** – Starter lokal PHP server og tester endpoints
2. **Lighthouse Audit** – Kører Lighthouse audit mod lokal server

**Concurrency:**
- `sprint5-smoke-${{ github.head_ref || github.ref }}`
- `cancel-in-progress: true`

---

## Sikkerhedspraksis

### Permissions
Alle workflows bruger eksplicitte `permissions:` for at begrænse GITHUB_TOKEN rettigheder:
- `contents: read` – standard for de fleste workflows
- `security-events: write` – kun CodeQL
- `deployments: write` – kun Cloudflare Pages

### Secrets
- Alle credentials håndteres via GitHub Secrets
- Ingen hardcoded værdier i workflows
- FTPS (TLS) bruges til alle FTP-operationer

### Concurrency
Alle workflows har `concurrency` blokke for at forhindre spam:
- Visual Regression: én kørsel pr. branch, auto-cancel
- Sprint 5 Smoke Test: én kørsel pr. PR, auto-cancel
- CodeQL: én kørsel pr. branch, auto-cancel
- Cloudflare Pages: én kørsel pr. branch, auto-cancel
- CI & Deploy: én kørsel pr. branch, **uden** auto-cancel (for at sikre deployments fuldføres)

---

## Node.js Version

Alle workflows bruger konsistent:
- **Node.js 20.x** (LTS)
- **npm ci** for deterministiske builds (i stedet for `npm install`)

---

## Paths-ignore

Følgende paths ignoreres af de fleste workflows for at reducere unødig støj:
- `.github/agents/**` – agent-konfigurationsfiler
- `docs/**` – dokumentation
- `**/*.md` – markdown-filer

Visual Regression har mere specifikke path-filters, da den kun skal køre ved UI-ændringer.

---

## Maintenance

For at opdatere workflows:
1. Opret branch fra `main`
2. Lav ændringer i `.github/workflows/`
3. Test via `workflow_dispatch` først
4. Opret PR og verificér alle checks er grønne
5. Merge til `main`

Se også: [CI/CD Setup Guide](CI_CD_SETUP_GUIDE.md)
