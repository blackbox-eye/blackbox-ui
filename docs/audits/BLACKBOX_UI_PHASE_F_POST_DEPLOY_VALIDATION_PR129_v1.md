# BLACKBOX UI Phase F Post-Deploy Validation PR129 v1

## 1. Scope and mode

This document records the post-deploy live-safe validation result for PR #129 in `blackbox-eye/blackbox-ui`.

- Mode: DOCUMENTATION ONLY
- Source: previously captured live-safe validation evidence after merge and deploy
- Restrictions for this documentation task: no application code changes, no workflow changes, no package changes, no generated asset changes, no tests, and no live checks

## 2. Baseline

- PR #129: `security(server): block public internal paths`
- Merge commit: `27b2aff`
- Changed files: `.htaccess` and `.htaccess.production`

## 3. Validation method

- PowerShell `Invoke-WebRequest -Method Head`
- HEAD requests for target URLs, explicitly using `-Method Head`
- Example command: `Invoke-WebRequest -Method Head -Uri https://<host>/<path>`
- no POST, no auth, no scanners, no fuzzing, no intrusive tests

## 4. Pass/fail matrix

| Path | Result | Classification |
| --- | ---: | --- |
| `/` | `200` | expected public |
| `/about.php` | `200` | expected public |
| `/includes/` | `403` | protected |
| `/vendor/` | `403` | protected |
| `/logs/` | `403` | protected |
| `/data/` | `403` | protected |
| `/db/` | `403` | protected |
| `/test/` | `403` | protected |
| `/recaptcha-debug.php` | `403` | protected |
| `/deploy_trigger.txt` | `403` | protected |
| `/trigger_ftp.txt` | `403` | protected |
| `/test.txt` | `403` | protected |
| `/.htaccess.backup` | `403` | protected |

## 5. Confirmed result

PR #129 appears effective on live production for the confirmed Phase F exposure paths.

## 6. Remaining caveats

- Header discrepancy: CSP was not detected in the latest validation, while earlier Phase F evidence reported CSP present.
- HSTS was present in the latest validation, while earlier Phase F evidence reported it missing.
- Header behavior requires a separate focused review.
- This checkpoint does not decide deployment source-of-truth.
- This checkpoint does not cover CSRF, artifact deletion, or code-level route cleanup.

## 7. Recommended next actions

- Header/source-of-truth review
- Deployment source-of-truth decision
- Artifact deletion/cleanup PR if ownership is confirmed
- CSRF baseline PR
- Security/deploy-surface contract tests

## 8. No files changed except this documentation file

No files were changed for this task except this documentation file.
