# CI/CD Workflows Oversigt

Dette dokument giver et overblik over alle GitHub Actions workflows i blackbox-ui repositoriet.

## Current Production Source Of Truth

- Current production is documented as repo-controlled deployment from `main` via `ci.yml`, using FTP to origin with optional TLS/FTPS negotiation where available.
- Cloudflare sits in front of origin as CDN, cache, and security edge.
- `cloudflare-pages.yml` is not the current authoritative production deployment path and should be treated as staging, preview, or experimental until separately owner-approved.
- `.htaccess` and origin config remain the intended repo-controlled path for header changes, but this document does not claim verified live header alignment or canonical header ownership.
- Any header alignment or ownership claim requires a separate dated header review before it is relied on as canonical.
- `.htaccess.production` is an intended production/reference template whose alignment with `.htaccess` must be verified before relying on it. It is not proven live runtime.
- Owner approval is required before changing deployment path, header policy, `.htaccess`, `.htaccess.production`, or workflow behavior.
- See [DEPLOYMENT_SOURCE_OF_TRUTH.md](DEPLOYMENT_SOURCE_OF_TRUTH.md).

## Workflow-oversigt

| Workflow | Fil | Triggers | Formål |
|----------|-----|----------|--------|
| **CI & Deploy (Secure)** | `ci.yml` | `push:main` (paths-filtered), `workflow_dispatch` | Current production deployment to origin using FTP with optional TLS/FTPS negotiation where available, smoke tests |
| **Visual Regression** | `visual-regression.yml` | `push:main`, `pull_request:main` (paths-filtered), `workflow_dispatch` | Playwright visuelle tests |
| **Cloudflare Pages Deploy** | `cloudflare-pages.yml` | `workflow_dispatch` | Cloudflare Pages staging/preview/experimental |
| **Blog Intel Weekly** | `blog-intel-weekly.yml` | `schedule`, `workflow_dispatch` | Blog intelligence automation |
| **CodeQL** | `codeql-analysis.yml` | `push:main`, `pull_request:main` (paths-filtered), `schedule` (ugentligt), `workflow_dispatch` | Sikkerhedsscanning af PHP/JS |
| **Lighthouse Audit** | `lighthouse.yml` | `push:main` (paths-filtered), `workflow_dispatch` | Performance og accessibility audit |
| **Sprint 5 Smoke Test** | `sprint5-smoke-test.yml` | `pull_request:main` (paths-filtered) | Endpoint-tests og Lighthouse |

---

## Detaljeret beskrivelse

### 1. CI & Deploy (Secure) – `ci.yml`

Current owner-approved production role: authoritative repo-controlled deployment path to origin.

**Triggers:**
- Push til `main` branch (ignorerer `.github/agents/**`, `docs/**`, `**/*.md`)
- Manuel dispatch

**Jobs:**
1. **Build & Verify** – Validerer secrets og kritiske filer
2. **Delete index.html** – Fjerner statisk index.html på origin host before deploy
3. **FTP Deploy** – Deployer til origin via FTP with optional TLS/FTPS negotiation where available
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
- Manuel dispatch for staging, preview, or experimental flow

Current owner-approved role: staging, preview, or experimental flow only. This is not the current authoritative production deployment path.

**Jobs:**
1. **Build & Prepare** – Validerer filer og Cloudflare secrets
2. **Deploy to Staging** – Deployer til preview URL
3. **Verify Staging** – Kører 5 verifikationstests
4. **Optional Manual Promotion** – Manuel godkendelse krævet, men ikke authoritative for current production ownership

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
- FTP operationer er dokumenteret som FTP to origin with optional TLS/FTPS negotiation where available, men TLS enforcement er ikke endnu canonical i governance-dokumentationen

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
