# GitHub Workflows Documentation

This directory contains CI/CD workflows for ALPHA Interface GUI.

## Workflow Overview

| Workflow | Trigger | Purpose |
|----------|---------|---------|
| `sprint5-smoke-test.yml` | Pull Request | Smoke tests on PR changes |
| `ci.yml` | Push to main | Full deploy via FTPS |
| `cloudflare-pages.yml` | Push/Manual | Cloudflare Pages deployment |
| `lighthouse.yml` | Push to main | Lighthouse performance audit |
| `visual-regression.yml` | Push/PR to main | Playwright visual tests |
| `codeql-analysis.yml` | Push/PR/Schedule | Security analysis |

## Concurrency Configuration

All workflows use **per-branch concurrency isolation** to prevent cross-PR cancellation:

```yaml
concurrency:
  group: workflow-name-${{ github.ref }}
  cancel-in-progress: true
```

This means:
- ✅ Multiple PRs can run tests in parallel
- ✅ New commits on the same branch cancel older runs
- ✅ No cross-branch interference

## Path Filters (IMPORTANT)

⚠️ **Never combine `paths:` and `paths-ignore:` in the same trigger!**

GitHub Actions does not support using both together. Choose one:

### Use `paths:` when you want to run ONLY on specific files:
```yaml
on:
  pull_request:
    paths:
      - 'assets/**'
      - '*.php'
```

### Use `paths-ignore:` when you want to run on EVERYTHING EXCEPT:
```yaml
on:
  pull_request:
    paths-ignore:
      - 'docs/**'
      - '**/*.md'
```

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
This was caused by shared concurrency groups across branches. 
Fixed with per-branch isolation: `group: workflow-${{ github.ref }}`

### Jobs Skipped unexpectedly
Check the `paths` or `paths-ignore` configuration. 
Never use both together in the same trigger.

### Draft PRs
Draft PRs may have limited CI execution. 
Mark as "Ready for review" to enable full checks.

### Cloudflare Deploy fails on PRs
This is expected - staging deploy skips PRs by design.
PR checks still run (smoke tests, visual regression).

## Manual Workflow Dispatch

All workflows support `workflow_dispatch` for manual triggering:

```bash
gh workflow run "CI & Deploy (Secure)"
gh workflow run "Cloudflare Pages Deploy" -f environment=staging
gh workflow run "Visual Regression"
```
