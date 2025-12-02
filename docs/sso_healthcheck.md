# SSO Health Check Documentation

> **Last updated:** 2025-11-30  
> **Version:** 1.0  
> **Related PR:** #61

## Overview

The SSO Health Check script (`scripts/sso-health.js`) validates that the SSO system components (GDI and TS24) are accessible and returning valid responses. This is an essential preflight check before running visual regression tests or deploying changes.

## Endpoints

The health check validates two primary endpoints:

### 1. GDI (GreyEYE Data Intelligence)

| Property | Value |
|----------|-------|
| **Name** | GDI |
| **URL** | `http://127.0.0.1:8000` |
| **Port** | 8000 |
| **Description** | Main GUI application |

The GDI endpoint serves the main web application. A successful health check returns any HTTP 2xx/3xx status code.

### 2. TS24 (Intel24 Intelligence Console)

| Property | Value |
|----------|-------|
| **Name** | TS24 |
| **URL** | `http://127.0.0.1:8091/tools/ts24_health_stub.php` |
| **Port** | 8091 |
| **Description** | TS24 SSO integration |

The TS24 endpoint is a stub that simulates the health check response from the production TS24 SSO integration.

## Expected JSON Response Fields

The TS24 health endpoint returns a JSON response with the following fields:

| Field | Type | Description |
|-------|------|-------------|
| `stub` | boolean | Indicates this is a stub response (true in development) |
| `secretConfigured` | boolean | Whether the SSO secret is configured |
| `usesHS256` | boolean | Whether HS256 algorithm is used for JWT signing |
| `expectedIss` | string | Expected JWT issuer (e.g., `https://blackbox.codes`) |
| `expectedAud` | string | Expected JWT audience (e.g., `ts24`) |
| `recentErrors` | array | List of recent error events (empty in healthy state) |
| `notes` | string | Additional information about the response |
| `timestamp` | string | ISO 8601 timestamp of the response |

### Example Response

```json
{
  "stub": true,
  "secretConfigured": true,
  "usesHS256": true,
  "expectedIss": "https://blackbox.codes",
  "expectedAud": "ts24",
  "recentErrors": [],
  "notes": "TS24 stub response for local testing",
  "timestamp": "2025-11-30T12:00:00+00:00"
}
```

## Environment Variables

The health check script uses the following configuration:

| Variable | Default | Description |
|----------|---------|-------------|
| `REQUEST_TIMEOUT_MS` | `5000` | Timeout for health check requests in milliseconds |

Currently, the endpoints are hardcoded in the script. Future versions may support:

| Variable | Purpose |
|----------|---------|
| `SSO_HEALTH_BASE_URL` | Base URL for health checks |
| `GDI_SSO_SECRET` | Secret for GDI SSO validation |

## CLI Usage

### Running the Health Check

```bash
# Using npm script (recommended)
npm run sso:health

# Direct Node.js execution
node scripts/sso-health.js
```

### Starting Required Servers

Before running the health check, ensure both PHP servers are running:

```bash
# Terminal 1: Start GDI server on port 8000
php -S localhost:8000

# Terminal 2: Start TS24 stub server on port 8091
php -S 127.0.0.1:8091
```

Or use the combined approach:

```bash
# Start both servers in background (Unix/Linux/macOS)
php -S localhost:8000 &
php -S 127.0.0.1:8091 &

# Run health check
npm run sso:health
```

### Interpreting Results

#### Successful Output (All OK)

```
🔍 SSO Health Check

==================================================

✅ GDI (Main GUI application)
   URL: http://127.0.0.1:8000
   Status: OK
   HTTP Code: 200
   Latency: 15ms

✅ TS24 (TS24 SSO integration)
   URL: http://127.0.0.1:8091/tools/ts24_health_stub.php
   Status: OK
   HTTP Code: 200
   Latency: 8ms
   Stub: Yes
   Secret Configured: Yes
   Uses HS256: Yes
   Expected Issuer: https://blackbox.codes
   Expected Audience: ts24
   Recent Errors: 0

==================================================

✅ All health checks passed!
```

#### Failed Output

```
🔍 SSO Health Check

==================================================

✅ GDI (Main GUI application)
   URL: http://127.0.0.1:8000
   Status: OK
   HTTP Code: 200
   Latency: 12ms

❌ TS24 (TS24 SSO integration)
   URL: http://127.0.0.1:8091/tools/ts24_health_stub.php
   Status: UNAVAILABLE
   Error: connect ECONNREFUSED 127.0.0.1:8091

==================================================

❌ Some health checks failed!

Make sure both servers are running:
  1. PHP server on port 8000: php -S localhost:8000
  2. PHP server on port 8091: php -S 127.0.0.1:8091
```

### Exit Codes

| Code | Meaning |
|------|---------|
| `0` | All health checks passed |
| `1` | One or more health checks failed |

## CI/CD Integration

The health check is used as a preflight step in the Visual Regression workflow. When integrated in CI:

1. The workflow starts the PHP servers
2. Runs `npm run sso:health` as a preflight check
3. Only proceeds to visual tests if health checks pass

See `docs/ci_pipelines.md` for more details on CI/CD workflow integration.

### CI Behaviour: Blocking vs Non-Blocking Conditions

In CI environments (GitHub Actions), certain SSO-related failures should be **non-blocking** to prevent external infrastructure issues from failing otherwise-valid builds.

#### Blocking Conditions (Hard Fail)

These conditions indicate a real problem in the GDI codebase and should fail the CI:

| Condition | Reason |
|-----------|--------|
| PHP syntax errors | Code is broken |
| Missing required GDI files | Deployment issue |
| Test assertion failures | Regression detected |
| Security vulnerabilities | Must be fixed |

#### Non-Blocking Conditions (Warning Only)

These conditions are **external dependencies** that GDI cannot control:

| Condition | Log Output | Reason |
|-----------|------------|--------|
| TS24 DNS resolution fails | `⚠️ TS24 external DNS unreachable` | TS24 infra issue |
| Missing `GDI_SSO_SECRET` in CI | `⚠️ GDI_SSO_SECRET not configured` | Optional secret |
| TS24 endpoint unreachable | `⚠️ intel24.blackbox.codes not responding` | External service |

#### Example Log Output When TS24 DNS Is Down

```
🔍 SSO Health Check

==================================================

✅ GDI (Main GUI application)
   URL: http://127.0.0.1:8000
   Status: OK
   HTTP Code: 200
   Latency: 15ms

⚠️ TS24 (TS24 SSO integration)
   URL: http://127.0.0.1:8091/tools/ts24_health_stub.php
   Status: STUB_OK
   Note: Using local stub (TS24 external endpoint not tested in CI)

==================================================

✅ Health check passed (with warnings)
   - TS24 external DNS: Not tested (expected in CI)
```

## Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| `ECONNREFUSED` | Server not running on the expected port |
| `TIMEOUT` | Server unresponsive; check for blocking operations |
| `vendor/autoload.php` missing | Run `composer install` first |
| HTTP 500 errors | Check PHP error logs |
| TS24 DNS REFUSED | External issue - see `docs/ts24_dns_status_*.md` |

### Debug Mode

For verbose output, you can modify the script or check the raw HTTP responses:

```bash
# Test GDI endpoint manually
curl -v http://127.0.0.1:8000

# Test TS24 endpoint manually
curl -v http://127.0.0.1:8091/tools/ts24_health_stub.php
```

---

## Changelog

| Date | Change | PR |
|------|--------|----|
| 2025-12-01 | Added CI behaviour section for blocking vs non-blocking conditions | Current |
| 2025-11-30 | Added TS24 healthcheck stub and sso:health script | #61 |
| 2025-11-30 | Created documentation | Current |
