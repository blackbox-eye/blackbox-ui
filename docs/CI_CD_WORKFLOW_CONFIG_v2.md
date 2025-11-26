# 🔧 CI/CD Workflow Configuration v2.0

**Version:** 2.0  
**Dato:** 2025-11-26  
**Status:** Production Ready

## Oversigt

CI/CD workflow-konfiguration for ALPHA Interface GUI efter November 2025 opdateringerne.

### Ændringer i v2.0
- Timeout tilføjet til alle workflow jobs
- Secrets validering før deployment
- Artifact naming: bindestreg i stedet for underscore
- GITHUB_TOKEN validering i test-workflows

## Workflow Oversigt

| Workflow | Fil | Timeout | Formål |
|----------|-----|---------|--------|
| CI & Deploy | ci.yml | 15-25 min | Build, FTP deploy, smoke tests |
| Lighthouse | lighthouse.yml | 20 min | Performance audit |
| Visual Regression | visual-regression.yml | 30 min | Playwright screenshot tests |

## Timeout Configuration

ci.yml:
- build: 20 min
- delete-index-html: 15 min
- ftp-deploy: 20 min
- smoke-tests: 25 min

lighthouse.yml: 20 min
visual-regression.yml: 30 min

## Secrets Validering

Build job validerer: FTP_HOST, FTP_USERNAME, FTP_PASSWORD, FTP_REMOTE_PATH, CF_ZONE_ID, CF_API_TOKEN

## Artifact Naming

Brug bindestreg (-) i stedet for underscore (_):
- lighthouse-results
- visual-regression-report

## Changelog v2.0 (2025-11-26)
- Tilføjet timeout til alle jobs
- Implementeret secrets validering
- Ændret artifact navne
- Tilføjet GITHUB_TOKEN validering
