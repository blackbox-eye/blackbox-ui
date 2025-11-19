# CHANGELOG

Alle større ændringer i ALPHA Interface GUI (AIG) dokumenteres her, så audit og drift altid kan følge release-tracking.

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
