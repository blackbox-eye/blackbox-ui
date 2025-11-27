# LEVERANCE 7 – Funktionalitetsudvidelse

**Dato:** 9. januar 2025 (opdateret 27. januar 2025)
**Sprint:** 7 – Feature Expansion + QA Audit
**Status:** ✅ Gennemført

---

## Oversigt

Denne leverance udvider ALPHA Interface GUI med tre nye funktionelle områder:

1. **Request Access Workflow** – Komplet formular til adgangsanmodninger (nu med database)
2. **Intel Vault** – Sikker dokumenthåndtering med AES-256-GCM kryptering
3. **API & Keys** – Administration af API-nøgler med fuld CRUD

Alle nye sider bruger det fælles `admin-layout.php` og er integreret i Command Deck-navigationen.

---

## QA Audit (27. januar 2025)

### Sikkerhedsrettelser

| Problem | Løsning | Fil |
|---------|---------|-----|
| Command Deck synlig før login | Fjernet helt fra login-siden | `agent-login.php` |
| Navigation tilgængelig uautentificeret | Menu-markup fjernet, kun efter session | `agent-login.php` |

### Favicon-konsistens

Alle sider har nu identisk favicon-konfiguration:

```html
<link rel="icon" type="image/png" sizes="32x32" href="/assets/logo%20pakker%20BlackboxEYE/...">
<link rel="icon" type="image/png" sizes="192x192" href="/assets/logo%20pakker%20BlackboxEYE/...">
<link rel="apple-touch-icon" sizes="180x180" href="/assets/logo%20pakker%20BlackboxEYE/...">
<link rel="shortcut icon" href="/assets/logo%20pakker%20BlackboxEYE/.../BlackboxEYE_white.ico">
```

**Opdaterede filer:**
- `agent-login.php`
- `dashboard.php`
- `includes/header.php`
- `includes/admin-layout.php`
- `includes/site-header.php` (havde allerede)

### CSS-konsistens

- `dashboard.php` ændret fra relativ til absolut sti (`/assets/css/admin.css`)
- Alle admin-sider bruger nu konsistent `/assets/css/admin.css` og `/assets/css/tailwind.full.css`

### Alt-tekst og Lazy Loading Audit

Alle billeder verificeret med:

- Meningsfulde `alt`-tekster eller `alt=""` for dekorative (med `aria-hidden="true"`)
- `loading="lazy"` på alle billeder

---

## Database-integration (27. januar 2025)

### Request Access Database

| Fil | Beskrivelse |
|-----|-------------|
| `db/schema/access_requests.sql` | Tabelskema for adgangsanmodninger |
| `api/request-access.php` | Opdateret med PDO-indsættelse |
| `access-requests.php` | Admin-side til visning/håndtering |

**Tabel: `access_requests`**

- `id`, `full_name`, `email`, `organization`, `role`, `reason`
- `status` (pending/approved/rejected)
- `created_at`, `reviewed_at`, `reviewed_by`

### Intel Vault Database

| Fil | Beskrivelse |
|-----|-------------|
| `db/schema/intel_vault.sql` | Tabelskema for krypterede filer |
| `includes/vault-encryption.php` | AES-256-GCM kryptering/dekryptering |
| `api/vault-upload.php` | Upload med kryptering |
| `api/vault-download.php` | Download med dekryptering |
| `api/vault-delete.php` | Sikker sletning |
| `intel-vault.php` | Fuld UI med upload/download |

**Sikkerhed:**

- AES-256-GCM kryptering med passphrase-deriveret nøgle (PBKDF2)
- Unique IV per fil
- Auth tag verificering ved dekryptering
- Filtype-validering (docx, pdf, txt, csv, json)
- Maks. 50MB upload

### API Keys Database

| Fil | Beskrivelse |
|-----|-------------|
| `db/schema/api_keys.sql` | Tabelskema for API-nøgler |
| `includes/apikey-helper.php` | Secure key generation (SHA-256) |
| `api/api-keys.php` | Full CRUD API |
| `api-keys.php` | Admin-side med live data |

**Nøgleformat:** `bbx_[prefix]_[32-char-random]`
**Storage:** Kun SHA-256 hash gemmes, original vises én gang ved oprettelse

---

## 10. Playwright Test Suite (27. januar 2025)

Ny testfil: `tests/admin-features.spec.js`

### Test-scenarier

1. **Login Security Tests**
   - Command Deck IKKE synlig på login-side
   - Login-kort centreret og synlig
   - Back-link navigerer til forside
   - Request Access modal funktionel

2. **Favicon Consistency Tests**
   - Alle sider har favicon
   - Korrekt BlackboxEYE-logo

3. **Authenticated Admin Tests** (kræver session)
   - Command Deck synlig efter login
   - Request Access admin-side loader
   - Intel Vault side loader
   - API Keys side loader

4. **Accessibility Tests**
   - Skip-link tilgængelig
   - Billeder har alt-attributter
   - Formular-inputs har labels

5. **Responsive Layout Tests**
   - Login-kort på mobil, tablet, desktop

---

## 11. Admin UI Redesign (28. januar 2025)

### Oversigt

Komplet modernisering af alle admin-sider med ensartet design, forbedret responsivitet og GreyEYE-branding.

### Opdaterede sider

| Side | Ændringer |
|------|-----------|
| `dashboard.php` | Fuldstændig omskrivning med kortbaseret grid-layout |
| `settings.php` | Panel-baseret design med sektionsopdeling |
| `admin.php` | Moderniseret med `.admin-page` styling |

### Dashboard-redesign

**Før:** Standalone HTML med gammel `#nav-menu` sidebar

**Efter:**
- Bruger `admin-layout.php` som base
- 4-kolonne responsivt grid (`auto-fit, minmax(300px, 1fr)`)
- Kort-baserede sektioner:
  - **Aktive Alarmer** – trusselsoversigt med badges
  - **Systemstatus** – operationel status med indikatorer
  - **AI Command** – hurtigkommandoer
  - **Netværk** – forbindelsesstatus
- GreyEYE-logo erstatter BLACKBOX EYE
- Command Deck til navigation (ingen sidebar)

### Settings-redesign

**Før:** Blandet layout med lodret MENU-knap

**Efter:**
- 6 panel-baserede sektioner:
  1. **Konto & Status** – agent-info og statistik
  2. **Sikkerhed** – password og PIN-ændring
  3. **Token** – API-token generering
  4. **Login-logs** – seneste loginaktivitet
  5. **Kontaktinformation** – email/telefon opdatering
  6. **Konto Handlinger** – ghost mode, deaktivering
- Responsivt 2-kolonne grid på desktop
- Lodret MENU-knap fjernet

### Admin-side modernisering

- Bruger `.admin-page` CSS-klasser
- Forbedret stats-kort for agentantal
- Moderniseret tabel med `.admin-users__table`
- Renere formular-layout til oprettelse af agenter

### CSS-tilføjelser

Nye klasser i `assets/css/admin.css`:

```css
/* Dashboard */
.dashboard-grid { display: grid; gap: 1.5rem; }
.dashboard-card { background: var(--admin-bg-secondary); border: 1px solid var(--admin-border-subtle); }
.dashboard-card__header { border-bottom: 1px solid var(--admin-border-subtle); }
.dashboard-stat { font-size: 2.5rem; color: var(--admin-text-gold); }

/* Settings */
.settings-grid { display: grid; gap: 1.5rem; }
.settings-panel { background: var(--admin-bg-secondary); padding: 1.5rem; }
.settings-form-row { display: flex; gap: 1rem; }

/* Admin Users */
.admin-users__stats { display: flex; gap: 1rem; }
.admin-users__table { width: 100%; border-collapse: collapse; }
```

### Design Tokens

Alle sider bruger konsistente CSS-variabler:

| Token | Værdi | Brug |
|-------|-------|------|
| `--admin-gold` | `#d4af37` | Primær accent |
| `--admin-text-gold` | `#FFE8A3` | Tekst med høj kontrast |
| `--admin-bg-secondary` | `rgba(255,255,255,0.03)` | Kortbaggrund |
| `--admin-border-subtle` | `rgba(212,175,55,0.2)` | Subtile kanter |

### Nye Playwright Tests

Testfil: `tests/admin-redesign.spec.js`

**Test-kategorier:**

1. **Dashboard Layout**
   - Verificerer `admin-page` struktur
   - Tjekker kortbaseret grid med min. 3 kort
   - Bekræfter fjernelse af `#nav-menu`
   - Responsiv test på mobil/tablet/desktop

2. **Settings Layout**
   - Verificerer panel-baserede sektioner
   - Tjekker password/PIN og token sektioner
   - Bekræfter ghost mode toggle
   - Responsiv test

3. **Admin Page**
   - Verificerer brugertabel
   - Tjekker stats-kort
   - Bekræfter oprettelsesformular

4. **Command Deck Integration**
   - Tilgængelig fra alle admin-sider
   - Korrekt aktiv-markering
   - Tastatur-tilgængelighed (ESC lukker)

5. **Design Token Konsistens**
   - Verificerer CSS custom properties
   - Tjekker kortbaggrunde

6. **Accessibility**
   - Heading-hierarki
   - Labels på formularfelter
   - Keyboard-navigation

---

## 1. Request Access Workflow

### Komponenter

| Fil | Beskrivelse |
|-----|-------------|
| `includes/components/request-access.php` | Frontend-komponent med modal dialog |
| `api/request-access.php` | Backend API-endpoint |

### Funktionalitet

- **Formularfelter:**
  - Fulde navn (påkrævet)
  - Sikker e-mail (påkrævet)
  - Organisation (påkrævet)
  - Ønsket rolle (valgfrit: Observer, Operator, Analyst, Admin)
  - Begrundelse (påkrævet)

- **Sikkerhedslag:**
  - Honeypot-felt til bot-detektion
  - reCAPTCHA v3-integration med action `request_access`
  - Input-sanitering mod header injection
  - Rate limiting via reCAPTCHA score (min. 0.5)

- **E-mail-notifikation:**
  - Sender via PHPMailer til sikkerhedsteamet
  - Inkluderer alle formulardata + sikkerhedsmetadata
  - Reply-To sat til ansøgerens e-mail

### Brug

Komponenten inkluderes automatisk på `agent-login.php`:

```php
<?php require_once 'includes/components/request-access.php'; ?>
```

---

## 2. Intel Vault

### Fil

`intel-vault.php`

### Formål

Placeholder-side til fremtidig sikker dokumenthåndtering og efterretningslager.

### Indhold

- **Under udvikling-notice** med tidsramme
- **Feature preview-kort** med kommende funktioner:
  - Krypteret lager (AES-256, HSM)
  - Klassifikationssystem (UKLASSIFICERET → STRENGT FORTROLIGT)
  - Fuldtekstsøgning med OCR og AI
  - Fuld sporbarhed (audit trail)
  - Adgangskontrol (RBAC, need-to-know)
  - Sikker deling med vandmærkning

- **Tom dokumentliste** som placeholder

### Adgang

Kræver aktiv session (`$_SESSION['agent_id']`).

---

## 3. API & Keys

### Fil

`api-keys.php`

### Formål

Administration af API-nøgler til eksterne integrationer.

### Indhold

- **Statistikkort:** Aktive, udløbne, tilbagekaldte nøgler + daglige API-kald
- **Nøgletabel med:**
  - Navn og ID
  - Maskeret nøgle med prefix
  - Status (aktiv/udløbet/tilbagekaldt)
  - Tilladelser (read/write/admin)
  - Rate limit
  - Oprettelses- og sidst brugt-dato
  - Handlinger (rotér, tilbagekald, slet)

- **Hurtigstart-dokumentation** med cURL-eksempel
- **Links til fuld dokumentation**

### Dummy-data

Siden viser 4 eksempel-API-nøgler med forskellige statusser til demonstration.

### Modal

"Opret ny nøgle"-knappen åbner en placeholder-modal der informerer om, at funktionen er under udvikling.

---

## 4. Command Deck-opdatering

Navigation udvidet i `includes/admin-layout.php`:

```php
$admin_nav_items = [
  ['slug' => 'dashboard', ...],
  ['slug' => 'intel-vault', 'label' => 'Intel Vault', 'href' => 'intel-vault.php', ...],
  ['slug' => 'api-keys', 'label' => 'API & Keys', 'href' => 'api-keys.php', ...],
  ['slug' => 'admin', 'label' => 'Brugerstyring', 'admin_only' => true, ...],
  ['slug' => 'download-logs', ...],
  ['slug' => 'settings', ...]
];
```

---

## 5. CSS-tilføjelser

Nye styles i `assets/css/admin.css`:

### Generelle admin-page styles
- `.admin-page` – fælles layout
- `.admin-page__header` – sidehoved med titel og handlinger
- `.admin-btn` – primær/sekundær knapstil

### Intel Vault styles
- `.intel-vault__notice` – under udvikling-banner
- `.intel-vault__feature-grid` – responsivt feature-grid
- `.intel-vault__feature` – feature-kort med hover-effekt
- `.intel-vault__empty-state` – tom tilstand

### API Keys styles
- `.api-keys__stats` – statistikkort-grid
- `.api-keys__table` – nøgletabel med statusfarver
- `.api-keys__code-block` – kodeboks med syntax highlighting
- `.api-keys__modal` – opret nøgle-modal

---

## 6. Filstruktur

```
├── api/
│   └── request-access.php      # Ny API-endpoint
├── includes/
│   ├── admin-layout.php        # Opdateret med nye nav-items
│   └── components/
│       └── request-access.php  # Udvidet komponent
├── assets/css/
│   └── admin.css               # Nye page styles
├── intel-vault.php             # Ny side
├── api-keys.php                # Ny side
└── docs/
    └── LEVERANCE_7.md          # Denne dokumentation
```

---

## 7. Sikkerhedsovervejelser

### Request Access
- Alle inputs saniteres før brug
- reCAPTCHA beskytter mod automatiserede angreb
- Honeypot fanger simple bots
- E-mail-headere beskyttes mod injection

### Intel Vault & API Keys
- Begge sider kræver autentificering
- Admin-only funktioner kontrolleres via `$_SESSION['is_admin']`
- Placeholder-sider eksponerer ikke reel data

---

## 8. Næste skridt

1. **Intel Vault:** Implementér document upload, kryptering og søgefunktion
2. **API Keys:** Tilføj reel CRUD-funktionalitet med database
3. **Request Access:** Gem anmodninger i database for tracking
4. **Tests:** Tilføj Playwright-tests for nye sider

---

## 9. Relaterede filer

- `includes/mail-helper.php` – PHPMailer-wrapper
- `includes/env.php` – Miljøvariabel-helper
- `assets/js/interface-menu.js` – Command Deck-controller

---

*Dokumentation oprettet: 9. januar 2025*
*Sidst opdateret: 27. november 2025*

---

## 12. Visual QA & Light Theme Forbedringer (November 2025)

### Light Theme Kontrastforbedringer

| Element | Ændring | CSS-værdi |
|---------|---------|-----------|
| Threat score label | Bedre læsbarhed | `color: var(--admin-text-secondary)` |
| Kritiske værdier | Mørkere rød | `#c41e16` |
| Advarsel-værdier | Mørkere guld | `#b8860b` |
| Stat values | Mørkere guld | `#6b5712` |
| Stat labels | Forbedret kontrast | `var(--admin-text-secondary)` |
| Card titles | Justeret farve | `#5a4a10` |
| Card icons | Justeret guld | `#8b6914` |

### Dashboard Bugfixes

- **NaN-dato bug:** `timeAgo()` funktionen håndterer nu `undefined` og invalide datoer korrekt
- **Alert rendering:** Bruger nu `created_at` eller `time_ago` property i stedet for `timestamp`
- **Stavefejl:** "Usædvanlig Port Scanning" → "Usædvanlig portscanning"

### Responsivitet

**Brugerstyringstabel (admin.php):**

| Skærmbredde | Skjulte kolonner |
|-------------|------------------|
| < 900px | PIN, Token, Ghost |
| < 640px | ID, Sidste login (ekstra) |

### Command Deck Forbedringer

- Farvet scrollbar med thin styling
- MENU-tekst lysere i light theme (`rgba(0,0,0,0.45)`)
- Forbedret form-label kontrast
- Tabel-header styling i light theme

---

## 13. Testudvidelser (November 2025)

### Nye Test Suites (`dashboard-accessibility.spec.js`)

**API Negative Tests (7 tests):**
- Invalid severity filter håndtering
- Excessively large limit parameter
- Empty/missing command body
- Malformed JSON input
- Invalid HTTP methods
- Non-existent endpoint 404

**Theme Toggle with Data (3 tests):**
- Data persisterer efter tema-skift
- Theme preference applied til alle elementer
- Alert badges farver i begge temaer

**ARIA Labels Comprehensive (3 tests):**
- Alle interaktive elementer har accessible names
- Dashboard cards har proper heading struktur
- Command Deck links har descriptive text

### Bugfixes i Tests

**`dashboard-dynamic.spec.js`:**
- Erstattet `toBeOneOf()` (ikke en standard Playwright-matcher) med `expect([values]).toContain()`
- Tilføjet 500ms wait for localStorage update i theme persistence test

### Test Status

| Kategori | Antal | Status |
|----------|-------|--------|
| Total tests | 548 | — |
| Passed | 208 | ✅ |
| Skipped | 340 | ⏭️ (kræver login) |
| Failed | 0 | ✅ |

---

## 14. Sprint Afrunding – Nye Features (November 2025)

### Password Show/Hide Toggle

**Beskrivelse:** Global komponent til at vise/skjule password og PIN felter med øje-ikon.

**Implementerede filer:**
| Fil | Beskrivelse |
|-----|-------------|
| `assets/js/password-toggle.js` | Auto-initialiserende JS-modul |
| `assets/css/admin.css` | CSS for toggle-knap og states |
| `includes/admin-footer.php` | Script inclusion |
| `agent-login.php` | Script inclusion |

**Funktionalitet:**
- Automatisk initialisering på alle `input[type="password"]` felter
- Keyboard support: Enter og Space til toggle
- Opdaterede aria-labels ved state-skift
- MutationObserver til dynamisk tilføjede felter
- Light/dark theme support

**CSS-klasser:**
```css
.password-field          /* Wrapper container */
.password-toggle         /* Knap-element */
.password-toggle--visible /* Når password er synligt */
```

### Ghost Mode Admin-Only

**Beskrivelse:** Ghost mode panel er nu kun synligt for admin-brugere.

**Ændring:**
```php
<!-- settings.php -->
<?php if (!empty($_SESSION['is_admin'])): ?>
  <!-- Ghost Mode Panel -->
  ...
<?php endif; ?>
```

**Sikkerhed:**
- Panel er helt fjernet fra HTML for non-admins
- `admin.php` kræver allerede admin-session (redirect til dashboard)

### IP-baserede Dashboard Feeds

**Beskrivelse:** Realistiske mock feeds baseret på agentens IP-adresse.

**Session data gemt ved login:**
```php
// agent-login.php
$_SESSION['agent_ip'] = $clientIp;
$_SESSION['agent_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
```

**API response (`/api/dashboard-stats.php`):**
```json
{
  "success": true,
  "data": {
    "alerts_count": 5,
    "agent_region": "Copenhagen, DK",
    "feeds": [
      {
        "title": "Suspicious login attempt blocked",
        "severity": "warning",
        "source_ip": "192.168.1.42",
        "region": "Copenhagen, DK",
        "timestamp": "2025-11-27T10:30:00+00:00",
        "detail": "Observed from 192.168.1.42; matched pattern #4523"
      }
    ]
  }
}
```

**Feed typer:**
| Title | Severity |
|-------|----------|
| Suspicious login attempt blocked | warning |
| Port scanning activity detected | warning |
| Malware signature matched (sandbox) | critical |
| Abnormal API usage spike | warning |
| New vulnerability observed in third-party service | info |

**Regioner:** Copenhagen, Aarhus, London, New York, Berlin, Stockholm, Oslo, Amsterdam

### Light Theme Command Deck Finjustering

**Nye CSS regler tilføjet:**

| Selector | Ændring |
|----------|---------|
| `[data-theme="light"] .command-deck__item` | Lys gradient baggrund, mørkere tekst |
| `[data-theme="light"] .command-deck__item:hover` | Varm guld hover effekt |
| `[data-theme="light"] .command-deck__item.is-active` | Fremhævet aktiv state |
| `[data-theme="light"] .command-deck__item-icon` | Reduceret kontrast til light mode |
| `[data-theme="light"] .command-deck__action` | Subtile baggrund og borders |
| `[data-theme="light"] .command-deck__agent-badge` | Justeret baggrund |
| `[data-theme="light"] .command-deck__divider` | Lettere separator |

### Nye Playwright Tests

**Tilføjet til `tests/admin-features.spec.js`:**

| Test Suite | Antal Tests |
|------------|-------------|
| Password Toggle Component | 8 tests |
| Ghost Mode Admin-Only Visibility | 2 tests |
| Dashboard Feeds API | 6 tests |
| Light Theme Support | 3 tests |

**Password Toggle Tests:**
- Eye icon display
- Click toggle functionality
- Keyboard activation (Enter/Space)
- Accessible aria-labels
- Label update on toggle
- PIN field support
- Focus indicator visibility

**API Tests:**
- 401 on unauthenticated request
- Valid JSON response structure
- `feeds` array presence
- Feed item required properties
- Valid severity levels
- ISO 8601 timestamp validation

---

## Lokal Verificering

### Test Dashboard API

**PowerShell:**
```powershell
# Start PHP server
php -S localhost:8000

# Test uden auth (forventet 401)
Invoke-RestMethod -Uri 'http://localhost:8000/api/dashboard-stats.php' -Method GET
```

**Browser DevTools:**
1. Log ind på `/agent-login.php`
2. Åbn Network-fanen
3. Naviger til dashboard
4. Find `dashboard-stats.php` request
5. Verificer `feeds` og `agent_region` i response

### Test Password Toggle

1. Gå til `/agent-login.php`
2. Verificer øje-ikoner ved password og PIN felter
3. Klik på ikon – password bør blive synligt
4. Tryk Tab til ikon, derefter Enter – bør toggle
5. Skift til light theme og verificer synlighed

### Test Ghost Mode

**Som admin:**
1. Log ind med admin-konto
2. Gå til `/settings.php`
3. Verificer "Ghost-mode" panel er synligt

**Som non-admin:**
1. Log ind med standard agent
2. Gå til `/settings.php`
3. Verificer "Ghost-mode" panel IKKE er synligt

---

## Blackbox EYE Landing Update (27. november 2025)

### Design Tokens

Nye Blackbox-specifikke CSS-variabler tilføjet i `assets/css/marketing.css`:

```css
/* Blackbox EYE brand colors (parent company identity) */
--blackbox-primary: #1a1a2e;
--blackbox-secondary: #0f0f1a;
--blackbox-accent: #16213e;
--blackbox-highlight: #e94560;
--blackbox-text: #eaeaea;
--blackbox-border: rgba(233, 69, 96, 0.3);
--blackbox-glow: 0 0 20px rgba(233, 69, 96, 0.2);
```

### Hero Section Updates

| Komponent | Beskrivelse |
|-----------|-------------|
| `.blackbox-section` | Gradient baggrund med radial glow |
| `.blackbox-badge` | Brand badge med ikon ("Blackbox EYE™ Security Platform") |
| `.stats-counter` | Tre KPI'er: Threats Blocked, Uptime, Response Time |

### Branding Strategi

- **Hero + Footer:** Blackbox EYE branding (moderselskab)
- **Produktsider:** GreyEYE som produktmodul
- **Design ratio:** 80-90% fælles tokens, 10-20% Blackbox-specifikke

### Nye CSS Komponenter

| Klasse | Formål |
|--------|--------|
| `.blackbox-section` | Sektioner med Blackbox-baggrund |
| `.blackbox-badge` | Brand badge med ikon |
| `.blackbox-highlight` | Tekst i highlight-farve |
| `.glass-effect--blackbox` | Kort med Blackbox-styling |
| `.stats-counter` | KPI-visning med tre elementer |
| `.live-feed-item--new` | Animation til nye feed-items |

### i18n Opdateringer

Nye oversættelsesnøgler tilføjet:

```json
// EN
"stats": {
  "threats": "Threats Blocked",
  "uptime": "Uptime",
  "response": "Response Time"
}

// DA
"stats": {
  "threats": "Trusler blokeret",
  "uptime": "Oppetid",
  "response": "Responstid"
}
```

### Tests Tilføjet

Nye test-grupper i `tests/marketing-landing.spec.js`:

- **Blackbox EYE Branding:** Badge, section class, stats counter
- **Blackbox CSS Variables:** Section existence, badge structure

### Verificering

```bash
# Kør tests
npm test

# Tjek specifikke Blackbox tests
npx playwright test tests/marketing-landing.spec.js --grep "Blackbox"
```

