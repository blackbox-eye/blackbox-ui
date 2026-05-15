# CSRF And Write Endpoint Baseline

Status: docs-only security governance baseline for CSRF and write-endpoint hardening in `blackbox-eye/blackbox-ui`.

Last reviewed: 2026-05-15.

This document is a governance baseline only. It does not approve runtime or security-code changes by itself.

## 1. Purpose

This document defines the current security baseline and implementation gate for CSRF and public/internal write endpoints before any runtime or security PR is approved.

The goal is to prevent speculative security edits, avoid bundled security waves, and require future implementation to be split into narrow surfaces with explicit validation.

## 2. Scope

This baseline covers repo-visible write or mutation-capable routes that were inspected in the approved surface for this task, including:

- public form or request handlers
- authenticated admin or self-service mutations
- `api/` endpoints that accept `POST`, `PUT`, `PATCH`, or `DELETE`
- login or access flows with visible `POST` behavior
- upload, delete, logging, or activity-write endpoints

GET-only routes are included in the register only where needed to define the write-surface boundary.

## 3. Evidence basis

Evidence labels used by this baseline:

- `REPO-VERIFIED`: method, mutation behavior, or auth/session requirement is visible in inspected repo code
- `DOCUMENT-SUPPORTED`: risk or follow-up need is supported by canonical repo docs or audit checkpoints
- `NOT VERIFIED`: a security property is not proven by the inspected code or docs

This baseline is based on:

- [../audits/BLACKBOX_UI_TECHNICAL_AUDIT_CHECKPOINT_A_E_v1.md](../audits/BLACKBOX_UI_TECHNICAL_AUDIT_CHECKPOINT_A_E_v1.md)
- [../audits/BLACKBOX_UI_PHASE_F_POST_DEPLOY_VALIDATION_PR129_v1.md](../audits/BLACKBOX_UI_PHASE_F_POST_DEPLOY_VALIDATION_PR129_v1.md)
- repo-visible endpoint behavior in inspected routes and `api/` handlers
- absence of a visible CSRF helper or canonical CSRF contract in the approved inspected include surface

This document does not claim that any endpoint is currently secure.

## 4. Current known risks from audit checkpoint A-E

From Phase B of the locked audit checkpoint:

- missing CSRF contract on authenticated mutations
- public write endpoints need hardening
- CORS wildcard exists on write endpoints
- information leakage candidates were identified
- direct access hardening was not proven

Phase F post-deploy validation also explicitly left CSRF and code-level route cleanup unresolved.

## 5. CSRF baseline requirement

- Any browser-reachable endpoint that performs an authenticated mutation through ambient session state must be treated as requiring a CSRF contract before it can be considered hardened.
- JSON endpoints are not exempt if they rely on browser session cookies or other ambient auth state.
- Public unauthenticated submission endpoints are not automatically classified as classic CSRF surfaces, but they still require explicit abuse-control and origin-handling review before being treated as hardened.
- No endpoint in this baseline is marked as having a repo-verified CSRF contract.

## 6. Write endpoint hardening requirements

Future implementation PRs must address the relevant subset of the following requirements for the specific security surface they touch:

- explicit method restriction
- explicit auth/session gate where the route is intended to be authenticated
- explicit CSRF protection for authenticated mutations
- clear treatment of public unauthenticated submissions as abuse-sensitive write surfaces
- input validation and bounded error handling
- origin and CORS review for any browser-callable write endpoint
- rate-limit, bot-protection, or equivalent abuse-control review where public writes exist
- upload/delete authorization and audit controls for file surfaces
- no claim of endpoint hardening without endpoint-specific validation evidence

## 7. Allowed future implementation scope

Allowed future security implementation must be split by one security surface per PR. Examples:

- public contact and access-request submissions
- authenticated admin or self-service mutations
- API key CRUD
- console activity or request logging endpoints
- Intel Vault upload and delete
- consent or settings-preference write endpoints
- login or auth-adjacent POST flows as a separate auth/security PR

## 8. Forbidden future implementation scope

Future implementation PRs must not:

- combine CSRF hardening with unrelated UI cleanup
- combine public form hardening with legal/claims or deployment work
- combine auth/login changes with general write-endpoint hardening unless explicitly owner-approved
- mix vault upload/delete, API key CRUD, and public request handlers in one PR
- claim deployment or live production hardening without the required validation boundary

## 9. Validation requirements

For this baseline PR:

- markdown diagnostics on the new docs
- docs-only scope check
- no app tests required

For future implementation PRs:

- use the smallest behavior-scoped validation available for the touched endpoint surface
- do not use intrusive live POST testing against production without explicit owner approval
- do not claim an endpoint is hardened unless the validation evidence matches the touched surface
- where applicable, distinguish repo-verified, document-supported, and not-verified claims in the PR package

## 10. Stop conditions

Stop and return for review if:

- the requested fix requires runtime changes outside the approved endpoint surface
- auth, SSO, CCS activation, deployment, or legal/claims work begins to mix into the same PR
- a single PR begins touching multiple security surfaces
- secure behavior cannot be proven without broader workflow, infra, or hosting changes
- the implementation would require intrusive live POST testing that has not been approved

## 11. Rollback / no-deploy guidance

- This baseline PR is docs-only and has no deploy.
- Future implementation PRs must include an endpoint-specific rollback or no-deploy note in the PR package.
- If a security change cannot be rolled back cleanly within the touched surface, stop and split the work further.

## 12. Future implementation PR split

Recommended split for future security work:

1. Public submission endpoints: `contact-submit.php`, `scan-submit.php`, `api/request-access.php`, `api/intel24-request.php`, `api/sso-request.php`, `api/faq-feedback.php`, `api/faq-search.php`, `api/consent-log.php`, and any public logging or activity POSTs.
2. Authenticated admin and self-service mutations: `access-requests.php`, `admin.php`, `settings.php`, `change-agentid.php`, `update-contact.php`, `api/graphene-toggle.php`, `api/ai-command.php`.
3. High-sensitivity API CRUD and vault operations: `api/api-keys.php`, `api/vault-upload.php`, `api/vault-delete.php`.
4. Auth-adjacent POST flows: `gdi-login.php` and `ccs-login.php`, separately reviewed from general CSRF work.
