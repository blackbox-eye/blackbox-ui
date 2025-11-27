# LEVERANCE 7 – Funktionalitetsudvidelse

**Dato:** 9. januar 2025  
**Sprint:** 7 – Feature Expansion  
**Status:** ✅ Gennemført

---

## Oversigt

Denne leverance udvider ALPHA Interface GUI med tre nye funktionelle områder:

1. **Request Access Workflow** – Komplet formular til adgangsanmodninger
2. **Intel Vault** – Placeholder-side til sikker dokumenthåndtering
3. **API & Keys** – Administration af API-nøgler

Alle nye sider bruger det fælles `admin-layout.php` og er integreret i Command Deck-navigationen.

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
