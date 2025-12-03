# Blackbox EYE → TS24 SSO Hand-off

This note documents how the GDI (Blackbox EYE) login flow now mints and exposes short-lived JWT tokens for the TS24 console.

> **See also:** `docs/ts24_sso_bridge.md` for canonical TS24 entry URLs and ownership details.

## Dependencies

- PHP 7.4+
- [`firebase/php-jwt` ^6.10](https://github.com/firebase/php-jwt) (install with `composer require firebase/php-jwt:^6.10` and load via `vendor/autoload.php`).

## Environment Variables

| Key | Description |
| --- | --- |
| `GDI_SSO_SECRET` (preferred) / `JWT_SECRET` | HMAC secret used to sign the TS24 SSO token. Tokens are only minted when this value is defined. |
| `GDI_SSO_TTL_SECONDS` / `JWT_TTL_SECONDS` | Lifetime for the JWT in seconds. Defaults to 600 seconds if omitted. |
| `TS24_CONSOLE_URL` | Base URL to the TS24 console. Default: `https://intel24.blackbox.codes/sso-login` (canonical SSO entry, DNS verified 2025-12-02). |

## JWT Payload

Tokens are signed with **HS256** and include the following claims:

- `iss`: Origin site base URL (`BBX_SITE_BASE_URL`).
- `aud`: Target console URL (`BBX_TS24_CONSOLE_URL`).
- `sub`: Agent identifier (`agents.agent_id`).
- `uid`: Internal numeric agent ID.
- `name`: Best-effort display name (falls back to `agent_id`).
- `role`: `admin` or `operator`.
- `scope`: Array of allowed areas (e.g., `["dashboard","intel","admin"]`).
- `iat` / `nbf`: Issued-at timestamp.
- `exp`: `iat + TTL` (default 10 minutes) so TS24 can reject stale links.

## Storage & Transport

- On successful login the token and expiry are stored in the session under `gdi_sso_token` / `gdi_sso_token_exp`.
- A matching `gdi_sso_token` HttpOnly cookie is emitted (`Secure`, `SameSite=Lax`) so public pages such as `agent-access.php` can read it before the agent re-enters the portal. The cookie lifetime matches the JWT `exp`.
- If token generation fails (missing secret, dependency issue, etc.) the login still succeeds, but an `SSO_TOKEN_ISSUE_FAILED` log entry is written.

## Building the TS24 Link

`agent-access.php` calls `bbx_current_agent_jwt()` which pulls the session token, or decodes the cookie if present. When a valid token exists the TS24 card URL is built as:

```php
$ts24Url = rtrim(BBX_TS24_CONSOLE_URL, '/');
$separator = strpos($ts24Url, '?') === false ? '?' : '&';
$ts24Link = $ts24Url . $separator . 'sso=' . urlencode($token);
```

When no token is available we fall back to the base `TS24_CONSOLE_URL`, so agents can continue with the manual TS24 login screen.

## Failure Behaviour

- Missing `vendor/autoload.php` or `firebase/php-jwt` triggers a warning and skips token minting.
- Missing `GDI_SSO_SECRET` results in an error-log warning during bootstrap; tokens are not issued until the secret is configured.
- Invalid/expired cookies are cleared automatically to avoid looping failures.
