# Write Endpoint Register

Status: repo-observable write and mutation endpoint register for `blackbox-eye/blackbox-ui`.

Last reviewed: 2026-05-15.

This register is documentation only. It does not claim that any endpoint is secure.

The register includes POST-style write endpoints and repo-visible non-POST mutation surfaces.

Evidence labels:

- `REPO-VERIFIED`: route method or behavior is visible in inspected code
- `DOCUMENT-SUPPORTED`: risk or follow-up is supported by canonical docs or audit checkpoints
- `NOT VERIFIED`: protection is not proven in the inspected surface

## Endpoint table

| Endpoint/file | Method if known | Apparent purpose | Auth/session requirement if visible | CSRF status | Rate-limit / bot protection status if visible | Data sensitivity | Evidence label | Risk level | Recommended next action |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `contact-submit.php` | `POST` | Public contact submission, mail dispatch, logging | No auth visible | No visible CSRF contract | reCAPTCHA v3 and honeypot visible; no explicit rate limit visible | Personal contact data and free-text message | REPO-VERIFIED | High | Include in a dedicated public-submission hardening PR |
| `scan-submit.php` | `POST` | Public scan request, lead logging, mock report response | No auth visible | No visible CSRF contract | reCAPTCHA visible; no honeypot or explicit rate limit visible | Domain, optional email, lead telemetry | REPO-VERIFIED | Medium/High | Include in public submission hardening PR |
| `gdi-login.php` | `POST` | Operator login, session establishment, token issuance | Public login route | No visible CSRF contract | Brute-force/rate-limit not visible in inspected slice | Credentials, session, SSO token | REPO-VERIFIED | High | Treat as separate auth/security surface, not part of generic write PR |
| `ccs-login.php` | `POST` | Preview login POST, session error message, redirect | Public preview route | No visible CSRF contract | No bot/rate-limit visible | Credentials preview and auth posture | REPO-VERIFIED | Medium/High | Keep in separate auth/CCS security review |
| `blog-post.php` | `GET` | Public blog post route; `bbx_get_blog_post()` increments `views` on successful load | No auth visible | N/A as public GET-triggered counter mutation; still a non-POST mutation surface | Not applicable | Low content sensitivity; view-count integrity | REPO-VERIFIED | Low/Medium | Include in non-POST mutation-surface review; do not treat as read-only |
| `access-requests.php` | `POST` | Admin review of access requests | Session auth and admin required visible | No visible CSRF contract on authenticated mutation | Internal route; no rate-limit visible | Access-review status and notes | REPO-VERIFIED | High | Include in authenticated admin mutation CSRF PR |
| `admin.php` | `POST` | Admin create, toggle, and delete agent records | Session auth and admin required visible | No visible CSRF contract on authenticated mutation | Internal route; no rate-limit visible | Agent credentials, token, admin state | REPO-VERIFIED | Critical | Split into dedicated admin mutation hardening PR |
| `settings.php` | `POST` | Self-service credential change, token regeneration, self-deactivation | Session auth required visible | No visible CSRF contract on authenticated mutation | Internal route; no rate-limit visible | Password, PIN, token, account state | REPO-VERIFIED | Critical | Include in self-service mutation CSRF PR |
| `change-agentid.php` | `POST` | Self-service agent ID change | Session auth required visible | No visible CSRF contract on authenticated mutation | Internal route; no rate-limit visible | Identity/account state | REPO-VERIFIED | High | Include in self-service mutation PR |
| `update-contact.php` | `POST` | Self-service email/contact update | Session auth required visible | No visible CSRF contract on authenticated mutation | Internal route; no rate-limit visible | Contact/account data | REPO-VERIFIED | High | Include in self-service mutation PR |
| `toggle-ghost.php` | `POST` | Self-service ghost-mode toggle on the current agent record | Session auth required visible | No visible CSRF contract on authenticated mutation | Internal route; no rate-limit visible | Account visibility/state | REPO-VERIFIED | High | Include in self-service mutation PR |
| `api/ai-command.php` | `GET`, `POST` | Submit AI command and store command history | Session auth required visible | No visible CSRF contract on `POST` | No rate-limit visible | Command content, command history | REPO-VERIFIED | High | Separate authenticated JSON mutation hardening PR |
| `api/alerts.php` | `GET` | Return active alerts | Session auth required visible | N/A for current GET-only behavior | Not applicable | Sensitive alert data | REPO-VERIFIED | Low as write surface | Keep out of first write-endpoint PR unless method expands |
| `api/api-keys.php` | `GET`, `POST`, `PUT`, `PATCH`, `DELETE` | API key CRUD and revoke operations | Session auth required; admin distinctions visible | No visible CSRF contract on write methods | No rate-limit visible | API keys, scopes, IP restrictions | REPO-VERIFIED | Critical | Dedicated API key CRUD hardening PR |
| `api/consent-log.php` | `POST` | Consent event logging via sendBeacon | No auth visible | No visible CSRF contract | No bot or rate-limit visible | Low to medium consent telemetry | REPO-VERIFIED | Medium | Separate public logging-endpoint review |
| `api/console-activity.php` | `GET`, `POST`, `OPTIONS` | Retrieve and append console activity to log storage | No auth visible in inspected file | No visible CSRF contract on `POST` | No rate-limit visible; `Access-Control-Allow-Origin: *` visible | Activity events and console usage metadata | REPO-VERIFIED | High | Dedicated console activity hardening PR; review wildcard CORS |
| `api/dashboard-stats.php` | `GET` | Return dashboard statistics | Session auth required visible | N/A for current GET-only behavior | Not applicable | Operational metrics | REPO-VERIFIED | Low as write surface | Keep out of write-surface PR unless method expands |
| `api/faq-feedback.php` | `POST` | FAQ helpfulness counter update | No auth visible | No visible CSRF contract | No bot or rate-limit visible | Low data sensitivity, but DB mutation | REPO-VERIFIED | Medium | Separate low-risk public mutation hardening PR |
| `api/faq-search.php` | `POST` | FAQ search query endpoint with AI/fulltext fallback | No auth visible | No visible CSRF contract; no persistent write visible in inspected slice | No bot or rate-limit visible | Query text and external AI request context | REPO-VERIFIED | Medium | Keep out of first write PR or treat as public POST abuse-control review |
| `api/graphene-toggle.php` | `POST` | Save theme or mode preference | Session or IP-based actor visible; strict auth not visible | No visible CSRF contract | No bot or rate-limit visible | Preference/config state | REPO-VERIFIED | Medium/High | Separate settings or preference write hardening PR |
| `api/intel24-request.php` | `POST`, `OPTIONS` | Intel24 access request persisted to JSON file | No auth visible | No visible CSRF contract | No bot or rate-limit visible; `Access-Control-Allow-Origin: *` visible | PII and access-request data | REPO-VERIFIED | High | Separate public request-endpoint hardening PR; review wildcard CORS |
| `api/network-stats.php` | `GET` | Return network monitoring stats | Session auth required visible | N/A for current GET-only behavior | Not applicable | Sensitive operational telemetry | REPO-VERIFIED | Low as write surface | Keep out of write-surface PR unless method expands |
| `api/request-access.php` | `POST` | Access request email plus DB insert | No auth visible | No visible CSRF contract | reCAPTCHA v3 and honeypot visible; no explicit rate limit visible | PII, organization, reason, request record | REPO-VERIFIED | High | Separate public access-request hardening PR |
| `api/sso-request.php` | `POST` | Mock SSO request plus file logging | No auth visible | No visible CSRF contract | No bot or rate-limit visible | Org, email, domain, provider, notes | REPO-VERIFIED | High | Separate SSO request hardening PR |
| `api/system-status.php` | `GET` | Return system health | Session auth required visible | N/A for current GET-only behavior | Not applicable | Sensitive operational status | REPO-VERIFIED | Low as write surface | Keep out of write-surface PR unless method expands |
| `api/vault-delete.php` | `POST` | Soft-delete vault document and insert audit log | Session auth required; owner/admin authorization visible | No visible CSRF contract on authenticated mutation | No rate-limit visible | High-value document state and audit trail | REPO-VERIFIED | Critical | Dedicated vault delete hardening PR |
| `api/vault-download.php` | `GET` | Authenticated vault download/decrypt plus `accessed_at` update and audit-log insert | Session auth required; owner/admin authorization visible | No visible CSRF contract; GET-triggered authenticated mutation updates access metadata and audit records | No rate-limit visible | Very high document sensitivity plus access/audit metadata | REPO-VERIFIED | High | Include in dedicated non-POST mutation review with vault operations; do not treat as read-only |
| `api/vault-upload.php` | `POST` | Upload, encrypt, store file, DB insert, audit log | Session auth required visible | No visible CSRF contract on authenticated mutation | File validation visible; no rate-limit or bot protection visible | Very high-value document content and metadata | REPO-VERIFIED | Critical | Dedicated vault upload hardening PR |

## Boundary notes

- `agent-access.php` was inspected as part of the approved surface and no write or mutation handler was visible in the inspected route.
- GET-triggered mutations are included as non-POST mutation surfaces when repo-visible code updates counters, access timestamps, or audit records.
- Pure GET-only read endpoints are listed only to show the boundary between mutation surfaces and adjacent read-only APIs.
- `NOT VERIFIED` applies to security properties that were not proven in inspected code, especially CSRF contracts, origin enforcement, rate limiting, and broader infra controls.
