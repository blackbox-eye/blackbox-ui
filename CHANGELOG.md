# CHANGELOG

Alle større ændringer i ALPHA Interface GUI (AIG) dokumenteres her, så audit og drift altid kan følge release-tracking.

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
