# GitHub Workflows Documentation

This directory contains CI/CD workflows for ALPHA Interface GUI.

## Workflow Overview

| Workflow | Trigger | Purpose |
|----------|---------|---------|
| `sprint5-smoke-test.yml` | Pull Request | Smoke tests + Lighthouse on PR changes |
| `ci.yml` | Push to main | Full deploy via FTPS |
| `cloudflare-pages.yml` | Push/Manual | Cloudflare Pages deployment |
| `lighthouse.yml` | Push to main | Lighthouse performance audit |

## Concurrency Configuration

All workflows use **per-branch concurrency isolation** to prevent cross-PR cancellation:

```yaml
concurrency:
  group: ${{ github.workflow }}-${{ github.ref }}
  cancel-in-progress: true
```

This means:
- ✅ Multiple PRs can run tests in parallel
- ✅ New commits on the same branch cancel older runs
- ✅ No cross-branch interference

## Path Filters

### `sprint5-smoke-test.yml`
Runs on changes to:
- `assets/**` - All frontend assets
- `includes/**` - PHP includes
- `*.php` - Root PHP files
- `lang/*.json` - Translation files

Ignores:
- `docs/**` - Documentation
- `**/*.md` - Markdown files

### Other Workflows
- `ci.yml` - Runs on all pushes to main
- `cloudflare-pages.yml` - Runs on push/PR to main
- `lighthouse.yml` - Runs on push to main

## Required Secrets

### FTP Deployment (`ci.yml`)
| Secret | Description |
|--------|-------------|
| `FTP_HOST` | FTP server hostname |
| `FTP_USERNAME` | FTP username |
| `FTP_PASSWORD` | FTP password |
| `FTP_REMOTE_PATH` | Remote deployment path |

### Cloudflare Pages (`cloudflare-pages.yml`)
| Secret | Description |
|--------|-------------|
| `CF_API_TOKEN` or `CLOUDFLARE_API_TOKEN` | Cloudflare API token |
| `CF_ACCOUNT_ID` or `CLOUDFLARE_ACCOUNT_ID` | Cloudflare account ID |
| `CF_PAGES_PROJECT_NAME` | Pages project name (default: blackbox-codes) |

### Optional Secrets
| Secret | Description |
|--------|-------------|
| `SITE_URL` | Production URL for tests |
| `CF_ZONE_ID` | Cloudflare zone for cache purge |
| `BBX_RECAPTCHA_SECRET_KEY` | reCAPTCHA v3 secret |

## Troubleshooting

### Jobs Cancelled with "higher priority waiting request"
This was caused by shared concurrency groups across branches. Now fixed with per-branch isolation.

### Jobs Skipped
Check the `paths` configuration matches your changed files.

### Draft PRs
Draft PRs may have limited CI execution. Mark as "Ready for review" to enable full checks.

## Manual Workflow Dispatch

All workflows support `workflow_dispatch` for manual triggering:

```bash
gh workflow run "CI & Deploy (Secure)"
gh workflow run "Cloudflare Pages Deploy" -f environment=staging
```
