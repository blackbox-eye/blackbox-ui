# CHANGELOG

Alle større ændringer i ALPHA Interface GUI (AIG) dokumenteres her, så audit og drift altid kan følge release-tracking.

## [v1.0.1] – 2025-12-02 (Status Report Update)
### Added
- **Comprehensive ALPHA Team Status Report**
  - Ny statusrapport der adresserer alle spørgsmål om GUI, SSO og UI/UX
  - Dokumenterer GUI ændringer til ny serveropsætning
  - TS24 SSO login flow status og JWT token specifikation
  - UI/UX test resultater og WCAG 2.1 compliance
  - Sikkerhedsforanstaltninger oversigt
  - GO/NO-GO status for GDI og TS24 integration

### Documentation
- `docs/ALPHA_TEAM_STATUS_REPORT.md` - Komplet statusrapport
- Opdateret `README.md` med link til ny statusrapport

### Status Summary
- GDI (ALPHA-Interface-GUI): ✅ GO - Produktionsklar
- TS24 Integration: ❌ NO-GO - Afventer ekstern DNS konfiguration

## [v1.0.0-sprint4] – 2025-11-23 (Sprint 4)
### Added
- **Comprehensive Visual Regression Testing**
  - Playwright-baseret automatiseret visual testing med cross-browser support
  - 4 viewport sizes: Mobile (375×812), Tablet (768×1024), Desktop Medium (1024×768), Desktop Large (1440×900)
  - 4 browser configurations: Chromium, Firefox, WebKit, Chromium-dark
  - 24 screenshots genereret og arkiveret (full page + header-only)
  - 16/16 tests bestået med 49,5 sekunders eksekvering

- **Lighthouse CI Integration**
  - Automatiseret Lighthouse audit på hver deployment
  - Core Web Vitals monitoring (LCP, FID, CLS)
  - Performance, Accessibility, Best Practices, SEO score tracking
  - Note: Artifact upload issue identificeret - audit kører perfekt, men GitHub Actions API afviser artifact

- **Enhanced Header Verification**
  - FAQ link synlighed og funktionalitet bekræftet på alle viewports
  - Language selection buttons verificeret cross-browser
  - Responsive navigation menu behavior valideret
  - Mobile menu toggle testet på mobile/tablet devices

### Infrastructure
- `.github/workflows/visual-regression.yml` - Playwright visual testing workflow
- `.github/workflows/lighthouse.yml` - Lighthouse CI audit workflow
- `tests/visual.spec.js` - Visual regression test suite
- `playwright.config.js` - Browser og viewport konfiguration
- `scripts/extract-lighthouse-scores.sh` - Utility script til score extraction
- `docs/sprint4/screenshots/` - Permanent storage af visual regression screenshots

### Documentation
- `SPRINT4_VERIFICATION_AUDIT.md` - Komplet verifikationsrapport med test-resultater
- `RELEASE_NOTES_v1.0.0-sprint4.md` - Release notes med detaljerede test metrics
- `docs/sprint4/screenshots/README.md` - Screenshot artifact dokumentation

### Known Issues
- Lighthouse artifact upload fejler konsekvent pga. GitHub Actions API-begrænsning
  - Workaround: Kør Lighthouse manuelt via Chrome DevTools eller CLI
  - Impact: Automatiseret performance-tracking ikke tilgængelig via artifacts

## [v1.2] – 2025-01-XX (Sprint 2)
### Added
- **Breadcrumb Navigation (P1)**
  - Dynamisk breadcrumb generering med `aig_get_breadcrumbs()` PHP-funktion
  - Schema.org BreadcrumbList structured data (JSON-LD for SEO)
  - Semantic HTML med `<nav aria-label="Breadcrumb">`
  - ARIA `current="page"` attribut for nuværende side
  - Responsivt design med mobile wrapping
  - Skips home page (returnerer tomt array på index.php)

- **Enhanced Mobile Menu (P1)**
  - Smooth slide-in animation med transform translateX
  - Dark overlay med fade-in/fade-out effekt
  - ESC-tast lukker menu med fokus-restaurering
  - Klik udenfor menu lukker den automatisk
  - Focus trap inden i åben menu (Tab cykler gennem items)
  - WCAG keyboard navigation (Tab, ESC, Enter/Space)
  - Reduced motion support (instant show/hide)

- **AI Loading States (P1)**
  - Professional spinner med rotation animation
  - Skeleton screens med 3-block pulse/shimmer effekt
  - Integreret på tværs af alle AI features:
    * Quick Assessment (spinner)
    * Gemini Modal (spinner)
    * Recommendation (skeleton)
    * Case Analysis (skeleton)
  - Respekterer `prefers-reduced-motion` bruger-præference
  - Smooth fade transitions mellem loader og resultat

- **Sticky CTA Button (P1)**
  - Scroll-triggered visibility (vises ved 50% viewport scroll)
  - Skjuler sig tæt på footer (inden for 200px)
  - Responsiv tekst: "Kontakt" (mobil) / "Book Møde" (desktop)
  - Kalender-ikon med hover scale effekt
  - Fixed positioning (bottom-right)
  - ARIA label: "Book sikkerhedsmøde"
  - Reduced motion fallback (instant show/hide)

- **Documentation**
  - Tilføjet `docs/SPRINT2_TEST_PLAN.md` med 37 test cases
  - Tilføjet `docs/PULL_REQUEST_SPRINT_2.md` (PR template)
  - Test matrices for device/browser compatibility
  - Keyboard accessibility checklist
  - Screen reader testing scenarios
  - Lighthouse audit requirements (≥95 target)
  - Performance metrics (FCP, LCP, CLS, TTI)
  - Cross-feature integration tests

### Changed
- `includes/site-header.php`: +315 lines (breadcrumb functions + CSS for alle features)
- `assets/js/site.js`: +135 net lines (mobile menu logic + AI loading helpers + sticky CTA scroll)
- `includes/site-footer.php`: +25 lines (sticky CTA HTML)

### Performance
- Target: Lighthouse scores ≥95 (alle kategorier)
- Scroll event debounced for optimal performance
- CSS animations optimized med transform/opacity
- Reduced motion media queries implementeret

### Accessibility
- WCAG 2.1 AA compliance vedligeholdt fra Sprint 1
- Keyboard navigation: Alle features fuldt tilgængelige
- Screen reader support: ARIA labels + semantic HTML
- Focus management: Trap + restoration implementeret
- Reduced motion: Alle animationer kan deaktiveres

### Branch
- `feat/ui-enhancements-sprint2` (baseret på Sprint 1)
- Commit: 0ef3955
- Status: Ready for QA review

## [v1.1] – 2025-11-19
### Added
- Tilføjet `WORKFLOW_VALIDATION_REPORT.md` i `/docs/` - komplet validering af CI/CD workflow konfiguration

### Validated
- PR #3 workflow konfiguration bekræftet komplet og merged via PR #5
- Alle fire FTP secrets dokumenteret og korrekt anvendt: `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD`, `FTP_REMOTE_PATH`
- Workflow triggers bekræftet: kun main branch push og manual dispatch (ingen pull_request triggers)
- Deployment automation valideret: delete-index-html, ftp-deploy, og smoke tests funktionelle
- Smoke tests bekræftet: index.php serves korrekt, index.html returnerer 404/403

## [v1.0] – 2025-07-01
### Added
- Tilføjet `SYSTEM_BLUEPRINT_AIG_v1.0.pdf` i `/docs/`
- Tilføjet `aig_blueprint_v1.md` i `/docs/`
- Oprettet `/docs/reports/`-mappen med:
  - `v1.1_20250707_onboarding.md`
  - `v1.2_20250701_statusrapport.md`
  - `v1.3_20250705_sprintplan.md`
  - `v1.4_20250710_auditlog.md`
- Opdateret `README.md` med overview og links til blueprint og reports
- Masterprompt opdateret til v1.2 (`AIG_MASTERPROMPT_v1.2_20250630.md`)

### Changed
- CI/CD-workflow (`.github/workflows/ci.yml`) dokumenteret med security‐policy og secret‐placeholders
- `README.md` udvidet med “docs/ Oversigt” og historiksektion

### Fixed
- N/A for initial release
