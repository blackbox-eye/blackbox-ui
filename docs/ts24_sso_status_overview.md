# TS24 SSO v1 Bridge Status Overview

> **Date:** 2025-12-01  
> **Version:** 1.0  
> **Repository:** AlphaAcces/ALPHA-Interface-GUI  
> **Related PRs:** #60, #61, #63, Current

---

## Executive Summary

**From the GDI side, SSO v1 bridge and documentation are production-ready.**

The only current blocker for real user traffic is the external DNS for `intel24.tstransport.app`, which must be fixed by the TS24 infrastructure team before end-to-end SSO can work.

---

## Status Summary

| Component | Status | Owner | Notes |
|-----------|--------|-------|-------|
| GDI SSO link building | 🟢 **Green** | GDI (this repo) | Uses `/sso-login` canonical URL |
| GDI `agent-access.php` | 🟢 **Green** | GDI | Renders correctly, link present |
| SSO health stub in CI | 🟢 **Green** | GDI | Local stub OK; non-blocking in CI |
| Visual regression tests | 🟢 **Green** | GDI | Tests pass, verify link presence |
| JWT token minting | 🟢 **Green** | GDI | Ready (requires `GDI_SSO_SECRET`) |
| Documentation | 🟢 **Green** | GDI | Updated with /sso-login canonical |
| DNS for `intel24.tstransport.app` | 🔴 **Red** | TS24 infra team | No A/AAAA/NS records; REFUSED |
| TLS for `intel24.tstransport.app` | ⚪ **Unknown** | TS24 ops team | Cannot test (DNS down) |
| `/sso-login` endpoint | ⚪ **Unknown** | TS24 app team | Cannot test (DNS down) |

---

## GO/NO-GO Statement

### GDI Side: ✅ GO

- All code changes merged to `main`
- Documentation updated with canonical `/sso-login` URL
- CI pipelines hardened for external DNS failures
- Visual regression tests passing

### TS24 Side: ❌ NO-GO (Blocker: DNS)

The following must be completed by TS24 infrastructure before end-to-end testing:

1. Configure NS records for `tstransport.app`
2. Add A/AAAA record for `intel24.tstransport.app`
3. Provision TLS certificate
4. Deploy `/sso-login` endpoint

---

## Detailed Component Status

### 1. GDI SSO Link Building

**File:** `agent-access.php`

```php
$ts24_console_url = bbx_env('TS24_CONSOLE_URL', 'https://intel24.tstransport.app/sso-login');
```

**Status:** ✅ Working

- Default URL updated to `/sso-login` (canonical entry point)
- Can be overridden via `TS24_CONSOLE_URL` environment variable
- Link renders correctly in UI

---

### 2. SSO Health Check (CI)

**Files:**
- `scripts/sso-health.js` — Node.js health checker
- `tools/ts24_health_stub.php` — Local stub for CI testing

**Status:** ✅ Working (with stub)

The health check uses a local stub in CI to avoid failing on external DNS issues. This design ensures:

- GDI code issues are caught (blocking)
- TS24 external DNS issues are logged but non-blocking

---

### 3. Visual Regression Tests

**File:** `tests/agent-access.spec.js`

**Status:** ✅ Passing

Tests verify:
- i18n translations render correctly
- GDI and TS24 cards are visible
- CTA buttons have proper touch targets
- Links have `href` attributes

Tests do **not** verify external reachability (by design).

---

### 4. DNS for intel24.tstransport.app

**Status:** 🔴 REFUSED

From multiple DNS resolvers (local, 8.8.8.8, 1.1.1.1), all queries return `REFUSED` status:

```
;; ->>HEADER<<- opcode: QUERY, status: REFUSED, id: 39311
;; flags: qr aa rd ra; QUERY: 1, ANSWER: 0, AUTHORITY: 0, ADDITIONAL: 0
```

**Root Cause:** No NS delegation or authoritative nameserver issue.

**Owner:** TS24 infrastructure team

See: `docs/ts24_dns_status_20251201.md` for full DNS test output.

---

## TS24 Infrastructure Checklist

For TS24 infra to deliver GO, they must demonstrate:

- [ ] `dig intel24.tstransport.app` returns A/AAAA record
- [ ] `curl -I https://intel24.tstransport.app/sso-login` returns HTTP 200/30x with valid TLS
- [ ] GDI link from `https://blackbox.codes/agent-access.php` → TS24 works in a browser
- [ ] JWT tokens from GDI are accepted by TS24 `/sso-login` endpoint

### Verification Commands

```bash
# DNS check (should return IP address)
dig intel24.tstransport.app +short

# HTTP check (should return 200 or redirect)
curl -I https://intel24.tstransport.app/sso-login

# TLS check (should show valid certificate)
echo | openssl s_client -connect intel24.tstransport.app:443 \
  -servername intel24.tstransport.app 2>/dev/null | \
  openssl x509 -noout -dates
```

---

## Related Documentation

| Document | Purpose |
|----------|---------|
| [docs/ts24_dns_status_20251201.md](ts24_dns_status_20251201.md) | DNS test results with raw outputs |
| [docs/ts24_sso_bridge.md](ts24_sso_bridge.md) | SSO architecture and JWT flow |
| [docs/sso_healthcheck.md](sso_healthcheck.md) | Health check script documentation |
| [docs/ci_pipelines.md](ci_pipelines.md) | CI/CD workflow configuration |

---

## Responsibility Matrix

| Task | GDI Team | TS24 Team |
|------|----------|-----------|
| Generate SSO links | ✅ | |
| Mint JWT tokens | ✅ | |
| Audit SSO events | ✅ | |
| Maintain `/sso-login` docs | ✅ | |
| Configure domain DNS | | ✅ |
| Provision TLS certificates | | ✅ |
| Deploy SSO endpoint | | ✅ |
| Validate JWT tokens | | ✅ |

---

## Timeline

| Date | Event | Owner |
|------|-------|-------|
| 2025-11-30 | PR #60, #61 merged (SSO v1 bridge) | GDI |
| 2025-11-30 | PR #63 merged (connectivity analysis) | GDI |
| 2025-12-01 | DNS still returning REFUSED | TS24 (pending) |
| 2025-12-01 | This QA follow-up completed | GDI |
| TBD | TS24 DNS configured | TS24 |
| TBD | End-to-end SSO testing | Both |

---

## Conclusion

The GDI side of the TS24 SSO v1 bridge is **complete and production-ready**.

All that remains is for the TS24 infrastructure team to:

1. Fix DNS for `intel24.tstransport.app`
2. Deploy a working `/sso-login` endpoint with valid TLS

Once these external dependencies are resolved, end-to-end SSO will work automatically — no further GDI changes are needed.

---

## Changelog

| Date | Change |
|------|--------|
| 2025-12-01 | Initial consolidated status overview |
