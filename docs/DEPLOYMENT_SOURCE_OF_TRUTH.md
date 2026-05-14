# Production Deployment Source Of Truth

Status: current owner-approved baseline for `blackbox-eye/blackbox-ui` production at `https://blackbox.codes`.

## Current production deployment source of truth

- Production is currently documented as repo-controlled deployment from `main` via `.github/workflows/ci.yml`, using FTP to origin with optional TLS/FTPS negotiation where available.
- This remains the canonical production deployment path unless later owner-approved evidence proves a manual cPanel step is required.
- Manual cPanel or FTP changes must not be treated as canonical unless explicitly owner-approved.

## Cloudflare role

- Cloudflare is in front of the origin as CDN, cache, and security edge.
- `.github/workflows/cloudflare-pages.yml` is not the current authoritative production deployment path.
- Cloudflare Pages should be treated as staging, preview, or experimental until separately owner-approved for production.

## Header ownership

- This document does not claim verified live header alignment or canonical header ownership.
- A separate dated header review is required before any header alignment or ownership claim is relied on as canonical.
- Cloudflare may add or override edge headers if configured separately, but Cloudflare header ownership must not be claimed unless verified.
- HSTS is an open security decision and is not yet locked as intentionally absent.

## `.htaccess.production` role

- `.htaccess.production` is treated as an intended production/reference template.
- Its alignment with `.htaccess` must be verified before relying on it.
- `.htaccess.production` is not treated as proven live runtime unless deployment evidence confirms it is the uploaded active file.

## Change control

- Owner approval is required before changing deployment path, header policy, `.htaccess`, `.htaccess.production`, or workflow behavior.

## Evidence basis

- Post-deploy validation for PR #129 recorded the confirmed blocking of the previously exposed Phase F paths. See `docs/audits/BLACKBOX_UI_PHASE_F_POST_DEPLOY_VALIDATION_PR129_v1.md`.
- Header ownership still requires a separate dated review artifact before it should be treated as canonical in governance documentation.

## Operational usage

- If production header behavior needs to change, start with the intended repo-controlled origin path documented here and validate live behavior after deploy.
- If deployment ownership changes, update this document first so downstream build, workflow, and audit docs stay aligned.
