# CI/CD Pipelines Documentation

> **Last updated:** 2025-11-30  
> **Version:** 1.0

## Overview

This document describes the CI/CD workflows in the ALPHA-Interface-GUI repository and when each workflow runs.

---

## Workflow Summary

| Workflow | File | Trigger | Purpose |
|----------|------|---------|---------|
| **Cloudflare Pages Deploy** | `cloudflare-pages.yml` | Push to main | Deploy to production |
| **CodeQL Analysis** | `codeql-analysis.yml` | Push/PR to main | Security scanning (JS + PHP) |
| **Visual Regression** | `visual-regression.yml` | Push/PR to main | UI consistency tests |
| **Lighthouse** | `lighthouse.yml` | Push/PR to main | Performance auditing |
| **CI** | `ci.yml` | Various | General CI tasks |
| **Sprint 5 Smoke Test** | `sprint5-smoke-test.yml` | Manual | Sprint-specific testing |

---

## Cloudflare Pages Deploy

**File:** `.github/workflows/cloudflare-pages.yml`

### Purpose
Deploys the site to Cloudflare Pages when changes are pushed to main.

### Trigger
- Push to `main` branch

### Steps
1. Checkout code
2. Build assets (Tailwind CSS)
3. Deploy to Cloudflare Pages
4. Report deployment URL

### Required Secrets
- `CLOUDFLARE_API_TOKEN`
- `CLOUDFLARE_ACCOUNT_ID`

---

## CodeQL Analysis

**File:** `.github/workflows/codeql-analysis.yml`

### Purpose
Performs static code analysis to detect security vulnerabilities in JavaScript and PHP code.

### Trigger
- Push to `main` branch
- Pull requests to `main` branch
- Scheduled (weekly)

### Languages Analyzed
- JavaScript
- PHP

### Steps
1. Checkout code
2. Initialize CodeQL
3. Run autobuild
4. Perform CodeQL analysis
5. Upload SARIF results

### Outputs
- Security alerts in GitHub Security tab
- SARIF report

---

## Visual Regression

**File:** `.github/workflows/visual-regression.yml`

### Purpose
Runs Playwright visual tests to ensure UI consistency across changes.

### Trigger
- Push to `main` branch (excluding docs, agents, markdown)
- Pull requests to `main` branch (same exclusions)
- Manual dispatch

### Path Exclusions
```yaml
paths-ignore:
  - '.github/agents/**'
  - 'docs/**'
  - '**/*.md'
  - '.well-known/**'
```

### Steps
1. **Checkout** - Clone repository
2. **Setup Node.js** - Configure Node 20 with npm cache
3. **Setup PHP** - Configure PHP 8.3
4. **Validate GitHub token** - Ensure auth available
5. **Create CI database stub** - Provide null PDO for testing
6. **Install dependencies** - `npm ci`
7. **Install Playwright browsers** - Download browser binaries
8. **Run visual tests** - Execute `npm run test:ci`
9. **Upload screenshots** - Archive test artifacts

### Timeout
- 30 minutes per job

### Artifacts
- `visual-screenshots` - Screenshots from failed tests

### Concurrency
```yaml
concurrency:
  group: visual-regression-${{ github.ref }}
  cancel-in-progress: true
```
This prevents multiple runs on the same branch, auto-canceling old runs.

---

## Lighthouse

**File:** `.github/workflows/lighthouse.yml`

### Purpose
Runs Lighthouse audits to measure performance, accessibility, SEO, and best practices.

### Trigger
- Push to `main` branch
- Pull requests to `main` branch

### Metrics Collected
- Performance score
- Accessibility score
- Best practices score
- SEO score

---

## When is a PR Ready to Merge?

A pull request is **ready for merge** when all of the following checks pass:

### Required Checks

- [ ] ✅ **Cloudflare Pages** - Preview deployment successful
- [ ] ✅ **CodeQL (JS)** - No security vulnerabilities
- [ ] ✅ **CodeQL (PHP)** - No security vulnerabilities
- [ ] ✅ **Visual Regression** - All visual tests pass

### Recommended Verification

- [ ] QA checklist completed (see `docs/qa_release_checklist.md`)
- [ ] SSO health check passes (`npm run sso:health`)
- [ ] No raw i18n keys visible on affected pages
- [ ] Mobile responsiveness verified

### Merge Criteria Summary

| Check | Status | Notes |
|-------|--------|-------|
| CI green | ✅ Required | All workflow checks must pass |
| Code review approved | ✅ Required | At least one approval |
| No merge conflicts | ✅ Required | Branch must be up to date |
| Tests pass | ✅ Required | Playwright tests must pass |

---

## Running Workflows Locally

### Visual Regression Tests

```bash
# Install dependencies
npm ci

# Install Playwright browsers
npx playwright install --with-deps

# Start PHP server
php -S localhost:8000 &

# Run tests
npm run test:ci
```

### SSO Health Check

```bash
# Start both servers
php -S localhost:8000 &
php -S 127.0.0.1:8091 &

# Run health check
npm run sso:health
```

### Tailwind Build

```bash
npm run build:tailwind
```

---

## Workflow Configuration Best Practices

### Timeout Limits
- Standard jobs: 30 minutes
- Long-running jobs: 60 minutes max
- Keep timeouts as low as practical to fail fast

### Concurrency
- Use `cancel-in-progress: true` for PR workflows
- Group by `${{ github.ref }}` to cancel old runs

### Artifact Retention
- Use `if-no-files-found: warn` for optional artifacts
- Set appropriate retention periods (default: 90 days)

### Security
- Minimal permissions: `contents: read` where possible
- Validate tokens before use
- Never log secrets

---

## SSO Health Check in CI

The SSO health check validates integration with TS24. In CI environments, this check is **non-blocking** because external DNS issues should not fail GDI builds.

### Blocking vs Non-Blocking

| Condition | CI Behaviour | Reason |
|-----------|--------------|--------|
| GDI local server fails | ❌ Blocking | GDI code issue |
| TS24 stub responds OK | ✅ Non-blocking | Local stub sufficient |
| TS24 external DNS fails | ⚠️ Warning only | External TS24 issue |
| Missing `GDI_SSO_SECRET` | ⚠️ Warning only | Optional in CI |

### Expected Log Output

When TS24 external DNS is unavailable (current state), CI logs will show:

```
⚠️ TS24 external endpoint not tested (expected in CI)
   Local stub: OK
   External DNS: Not tested
```

This is **expected behaviour** — the CI validates GDI code, not TS24 infrastructure.

### See Also

- `docs/sso_healthcheck.md` — Full SSO health check documentation
- `docs/ts24_dns_status_*.md` — DNS status reports

---

## Troubleshooting CI Failures

| Failure | Common Cause | Solution |
|---------|--------------|----------|
| Visual tests timeout | Server not starting | Check PHP version, port availability |
| CodeQL fails | Syntax errors | Fix code syntax |
| Deploy fails | Missing secrets | Verify Cloudflare tokens |
| npm ci fails | Lock file mismatch | Regenerate package-lock.json |
| SSO health warning | TS24 DNS down | External issue — see DNS report |

### Checking Logs

1. Go to the Actions tab in GitHub
2. Select the failed workflow run
3. Click on the failed job
4. Expand the failed step to see logs
5. Look for error messages or stack traces

---

## Changelog

| Date | Change | PR |
|------|--------|----|
| 2025-12-01 | Added SSO health check CI behaviour section | Current |
| 2025-11-30 | Created CI pipelines documentation | Current |
