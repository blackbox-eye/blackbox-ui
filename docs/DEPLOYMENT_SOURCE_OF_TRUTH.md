# Production Deployment Source Of Truth

Status: current owner-approved baseline for `blackbox-eye/blackbox-ui` production at `https://blackbox.codes`.

## Current production deployment source of truth

- Production is currently documented as repo-controlled deployment from `main` via `.github/workflows/ci.yml`, using FTPS to the origin host.
- This remains the canonical production deployment path unless later owner-approved evidence proves a manual cPanel step is required.
- Manual cPanel or FTP changes must not be treated as canonical unless explicitly owner-approved.

## Cloudflare role

- Cloudflare is in front of the origin as CDN, cache, and security edge.
- `.github/workflows/cloudflare-pages.yml` is not the current authoritative production deployment path.
- Cloudflare Pages should be treated as staging, preview, or experimental until separately owner-approved for production.

## Header ownership

- Live `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`, and `Referrer-Policy` are intended to be repo-controlled through `.htaccess` and origin config.
- Cloudflare may add or override edge headers if configured separately, but Cloudflare header ownership must not be claimed unless verified.
- HSTS is an open security decision and is not yet locked as intentionally absent.

## `.htaccess.production` role

- `.htaccess.production` is treated as a production/reference template that must stay aligned with `.htaccess`.
- `.htaccess.production` is not treated as proven live runtime unless deployment evidence confirms it is the uploaded active file.

## Change control

- Owner approval is required before changing deployment path, header policy, `.htaccess`, `.htaccess.production`, or workflow behavior.

## Evidence basis

- Post-deploy validation for PR #129 recorded the confirmed blocking of the previously exposed Phase F paths. See `docs/audits/BLACKBOX_UI_PHASE_F_POST_DEPLOY_VALIDATION_PR129_v1.md`.
- The latest header/source-of-truth review found that live `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`, and `Referrer-Policy` match `.htaccess`, Cloudflare is in front of origin, and HSTS is not currently observed live.

## Operational usage

- If production header behavior needs to change, start with the repo-controlled origin path documented here and validate live behavior after deploy.
- If deployment ownership changes, update this document first so downstream build, workflow, and audit docs stay aligned.
