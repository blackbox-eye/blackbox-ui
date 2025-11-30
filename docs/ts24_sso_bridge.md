# TS24 SSO Bridge Documentation

> **Last updated:** 2025-11-30  
> **Version:** 1.0  
> **Related PR:** #61

## Overview

The TS24 SSO v1 bridge enables secure single sign-on authentication between the Blackbox EYE platform and the TS24/Intel24 Intelligence Console. This document describes the architecture, JWT handling, and security considerations.

## Architecture

```
┌─────────────────────┐    JWT    ┌─────────────────────┐
│   Blackbox EYE      │ ───────── │   TS24 Console      │
│   (agent-login.php) │  ?sso=    │   (tstransport.app) │
└─────────────────────┘           └─────────────────────┘
         │                                  │
         │                                  │
         ▼                                  ▼
┌─────────────────────┐           ┌─────────────────────┐
│   Audit Log         │           │   Session Manager   │
│   (sso_audit.php)   │           │                     │
└─────────────────────┘           └─────────────────────┘
```

## JWT Flow

### 1. Agent Login

When an agent authenticates at `agent-login.php`:

1. User credentials are validated against the GDI database
2. A JWT token is generated with the agent's claims
3. The token is stored in the session for SSO use

### 2. SSO Redirect to TS24

When an agent clicks the TS24 card on `agent-access.php`:

1. The system generates a time-limited JWT token
2. The link is constructed as: `https://intel24.tstransport.app/login?sso=<JWT>`
3. The card element includes `data-sso-active="true"` attribute
4. The agent is redirected to TS24 with the JWT

### 3. JWT Validation at TS24

The TS24 console:

1. Extracts the JWT from the `sso` query parameter
2. Validates the signature using HS256
3. Verifies the issuer (`iss`) matches expected value
4. Verifies the audience (`aud`) is correct
5. Checks token expiration (`exp`)
6. Creates a local session for the agent

## JWT Structure

### Header

```json
{
  "alg": "HS256",
  "typ": "JWT"
}
```

### Payload (Claims)

| Claim | Type | Description |
|-------|------|-------------|
| `iss` | string | Issuer: `https://blackbox.codes` |
| `aud` | string | Audience: `ts24` |
| `sub` | string | Subject: Agent ID |
| `exp` | number | Expiration timestamp (Unix epoch) |
| `iat` | number | Issued at timestamp |
| `nbf` | number | Not before timestamp |
| `jti` | string | Unique token identifier |
| `agent_name` | string | Agent display name |
| `clearance` | string | Security clearance level |
| `permissions` | array | List of allowed operations |

### Example Token Payload

```json
{
  "iss": "https://blackbox.codes",
  "aud": "ts24",
  "sub": "agent_12345",
  "exp": 1733004000,
  "iat": 1733000400,
  "nbf": 1733000400,
  "jti": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
  "agent_name": "Agent Smith",
  "clearance": "level-3",
  "permissions": ["read:intel", "read:alerts", "write:briefings"]
}
```

## Audit Logging

All SSO events are logged via `sso_audit.php`:

### Logged Events

| Event | Description |
|-------|-------------|
| `sso_token_generated` | JWT created for SSO redirect |
| `sso_redirect_initiated` | Agent redirected to TS24 |
| `sso_validation_success` | Token validated successfully |
| `sso_validation_failure` | Token validation failed |
| `sso_session_created` | TS24 session established |
| `sso_session_terminated` | Agent logged out |

### Audit Log Fields

| Field | Description |
|-------|-------------|
| `timestamp` | ISO 8601 timestamp |
| `event_type` | Type of SSO event |
| `agent_id` | Agent identifier (not full JWT) |
| `ip_address` | Client IP (anonymized last octet) |
| `user_agent` | Browser/client information |
| `jti` | JWT ID for correlation |
| `status` | Success/failure indicator |
| `error_code` | Error code if applicable |

### Security Note

The audit system logs only necessary claims for tracking purposes. The **full JWT payload is never logged** to prevent token exposure in log files.

## Security Considerations

### HS256 Key Management

| Aspect | Recommendation |
|--------|----------------|
| **Key Length** | Minimum 256 bits (32 bytes) |
| **Storage** | Environment variable or secrets manager |
| **Rotation** | Rotate quarterly or after suspected compromise |
| **Sharing** | Never commit to version control |

**Environment Variable:**

```bash
# In .env file (not committed)
TS24_SSO_SECRET=your-256-bit-secret-key-here
```

### Token TTL (Time To Live)

| Use Case | Recommended TTL |
|----------|-----------------|
| Standard SSO redirect | 5 minutes |
| Remember me (if implemented) | 24 hours |
| API tokens | 1 hour |

The short TTL for SSO redirects minimizes the window for token interception.

### Token Validation Checklist

When validating incoming tokens:

- [ ] Verify signature with shared secret
- [ ] Check `iss` matches expected issuer
- [ ] Check `aud` matches expected audience
- [ ] Verify `exp` is in the future
- [ ] Verify `nbf` is in the past
- [ ] Check `jti` hasn't been used (replay prevention)
- [ ] Validate required claims are present

### Logging Without Exposing Sensitive Data

**Do Log:**
- Agent ID (subject claim)
- Token ID (jti)
- Timestamp
- Event type
- Status

**Do NOT Log:**
- Full JWT token
- Token payload contents
- Signing secret
- Session tokens

## Configuration

### Required Environment Variables

| Variable | Description |
|----------|-------------|
| `TS24_CONSOLE_URL` | Base URL for TS24 console |
| `TS24_SSO_SECRET` | Shared secret for JWT signing |
| `SSO_TOKEN_TTL` | Token time-to-live in seconds |

### Example Configuration

```php
// config/sso.php
return [
    'ts24' => [
        'console_url' => bbx_env('TS24_CONSOLE_URL', 'https://intel24.tstransport.app/login'),
        'secret' => bbx_env('TS24_SSO_SECRET'),
        'token_ttl' => bbx_env('SSO_TOKEN_TTL', 300), // 5 minutes
        'algorithm' => 'HS256',
        'issuer' => 'https://blackbox.codes',
        'audience' => 'ts24'
    ]
];
```

## Integration with Agent Access Page

The `agent-access.php` page uses the SSO bridge:

```php
$ts24_console_url = bbx_env('TS24_CONSOLE_URL', 'https://intel24.tstransport.app/login');

// The CTA link includes SSO parameters when active
<a href="<?= htmlspecialchars($ts24_console_url) ?>"
   class="access-card__cta bbx-btn-pill"
   data-console-launch="ts24"
   data-sso-active="true"
   target="_blank"
   rel="noopener">
    <?= t('agent_access.cards.ts24.cta') ?>
</a>
```

## Risks and Mitigations

| Risk | Mitigation |
|------|------------|
| Token interception | Use HTTPS, short TTL |
| Replay attacks | Implement JTI tracking |
| Key compromise | Regular rotation, secrets manager |
| Clock skew | Allow 30-second tolerance on exp/nbf |
| Session hijacking | Bind token to user agent/IP hash |

## Testing

### Local Development

Use the health check stub for testing:

```bash
# Start stub server
php -S 127.0.0.1:8091

# Verify stub response
curl http://127.0.0.1:8091/tools/ts24_health_stub.php
```

### Health Check

```bash
npm run sso:health
```

Expected output should show:
- `Secret Configured: Yes`
- `Uses HS256: Yes`
- `Expected Issuer: https://blackbox.codes`
- `Expected Audience: ts24`

---

## Changelog

| Date | Change | PR |
|------|--------|----|
| 2025-11-30 | Added TS24 healthcheck stub | #61 |
| 2025-11-30 | Created SSO bridge documentation | Current |
