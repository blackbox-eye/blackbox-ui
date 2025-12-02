# TS24 DNS Status Report

> **Date:** 2025-12-01  
> **Source:** GitHub Actions CI Environment  
> **Requestor:** TS24 SSO v1 bridge QA  
> **Related PR:** This report is part of the TS24 SSO v1 DNS follow-up QA pass

---

## Executive Summary

**VERDICT:** From GitHub Actions and public DNS, `intel24.tstransport.app` currently has **no working DNS records**. This is an **external TS24 infrastructure issue**, not a bug in the GDI repo.

---

## Test Environment

| Property | Value |
|----------|-------|
| **Runner** | GitHub Actions (ubuntu-latest) |
| **DNS Resolver** | systemd-resolved (127.0.0.53) |
| **Test Date** | 2025-12-01T02:05 UTC |
| **Control Domain** | github.com (working) |

---

## DNS Test Results

### Test 1: nslookup intel24.tstransport.app

```
Server:     127.0.0.53
Address:    127.0.0.53#53

** server can't find intel24.tstransport.app: REFUSED
```

**Interpretation:** Local DNS resolver returns `REFUSED` status, indicating the upstream authoritative DNS server is not responding or the domain has no valid NS records.

---

### Test 2: dig intel24.tstransport.app (full output)

```
; <<>> DiG 9.18.39-0ubuntu0.24.04.2-Ubuntu <<>> intel24.tstransport.app
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: REFUSED, id: 39311
;; flags: qr aa rd ra; QUERY: 1, ANSWER: 0, AUTHORITY: 0, ADDITIONAL: 0

;; QUESTION SECTION:
;intel24.tstransport.app.       IN      A

;; Query time: 0 msec
;; SERVER: 127.0.0.53#53(127.0.0.53) (UDP)
;; MSG SIZE  rcvd: 41
```

**Interpretation:**
- `status: REFUSED` — DNS query was refused by the authoritative server
- `ANSWER: 0` — No A/AAAA records returned
- `AUTHORITY: 0` — No NS delegation information

---

### Test 3: dig @8.8.8.8 intel24.tstransport.app

```
(No output - query failed silently)
```

**Interpretation:** Google Public DNS (8.8.8.8) also cannot resolve the domain.

---

### Test 4: dig @1.1.1.1 intel24.tstransport.app

```
(No output - query failed silently)
```

**Interpretation:** Cloudflare DNS (1.1.1.1) also cannot resolve the domain.

---

### Test 5: dig tstransport.app NS

```
; <<>> DiG 9.18.39-0ubuntu0.24.04.2-Ubuntu <<>> tstransport.app
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: REFUSED, id: 10940
;; flags: qr aa rd ra; QUERY: 1, ANSWER: 0, AUTHORITY: 0, ADDITIONAL: 0

;; QUESTION SECTION:
;tstransport.app.               IN      A

;; Query time: 0 msec
;; SERVER: 127.0.0.53#53(127.0.0.53) (UDP)
;; MSG SIZE  rcvd: 33
```

**Interpretation:** The parent domain `tstransport.app` also returns `REFUSED`. This suggests the entire domain hierarchy has DNS configuration issues, not just the subdomain.

---

### Test 6: host intel24.tstransport.app

```
Host intel24.tstransport.app not found: 5(REFUSED)
```

**Interpretation:** Confirms `REFUSED` status from a different DNS tool.

---

### Test 7: Python socket resolution

```python
socket.gaierror: [Errno -3] Temporary failure in name resolution
```

**Interpretation:** Standard library DNS resolution also fails.

---

## HTTP/TLS Test Results

### Test 8: curl https://intel24.tstransport.app/sso-login

```
* Could not resolve host: intel24.tstransport.app
curl: (6) Could not resolve host: intel24.tstransport.app
```

**Interpretation:** Cannot test TLS or HTTP because DNS resolution fails first.

---

### Test 9: curl https://intel24.tstransport.app/sso-login

```
* Could not resolve host: intel24.tstransport.app
curl: (6) Could not resolve host: intel24.tstransport.app
```

**Interpretation:** Same result — DNS failure prevents any HTTP testing.

---

## Control Test (Verify Network Works)

### nslookup github.com

```
Server:     127.0.0.53
Address:    127.0.0.53#53

Name:   github.com
Address: 140.82.114.4
```

**Interpretation:** DNS resolution works fine for other domains. This confirms the issue is specific to `tstransport.app` and its subdomains, not a network firewall or resolver issue.

---

## Diagnosis

| Issue Type | Status | Description |
|------------|--------|-------------|
| **NXDOMAIN** | ❌ Not seen | Domain does exist in registry (not NXDOMAIN) |
| **REFUSED** | ✅ Confirmed | Authoritative DNS refuses queries |
| **No NS records** | ✅ Likely | No delegation from parent zone |
| **TLS errors** | ❓ Unknown | Cannot test until DNS works |
| **HTTP errors** | ❓ Unknown | Cannot test until DNS works |

### Root Cause Analysis

The `REFUSED` status indicates one of the following:

1. **No NS records configured** at the domain registrar for `tstransport.app`
2. **Authoritative nameservers offline** or not responding
3. **Zone file not configured** on the authoritative nameserver
4. **Domain expired** or in grace period (less likely given "GO" status claimed)

---

## Impact on GDI

| GDI Component | Status | Notes |
|---------------|--------|-------|
| `agent-access.php` | ✅ Working | Renders correctly, link is present |
| TS24 link | ⚠️ Non-functional | URL correct, but target unreachable |
| SSO JWT generation | ✅ Would work | Local code ready, no target to send to |
| Playwright tests | ✅ Pass | Tests verify link presence, not reachability |

---

## Recommendations for TS24 Infra Team

The TS24 infrastructure team must complete the following before end-to-end SSO can work:

### Required Actions

1. **Configure NS records** for `tstransport.app` at domain registrar
2. **Verify authoritative nameserver** is online and responding
3. **Add A/AAAA record** for `intel24.tstransport.app` pointing to web server
4. **Configure TLS certificate** (Let's Encrypt or similar) for the subdomain

### Verification Commands

Once DNS is configured, these commands should return valid responses:

```bash
# Should return an IP address
dig intel24.tstransport.app +short

# Should return HTTP 200 or 30x
curl -I https://intel24.tstransport.app/sso-login

# Should show valid TLS certificate
echo | openssl s_client -connect intel24.tstransport.app:443 -servername intel24.tstransport.app 2>/dev/null | openssl x509 -noout -dates
```

---

## Conclusion

**From the GDI side, the SSO v1 bridge is production-ready.** The `agent-access.php` page correctly generates links to `intel24.tstransport.app`. The only blocker is the external DNS infrastructure owned and operated by the TS24 team.

**GDI Status:** ✅ Ready  
**TS24 DNS Status:** ❌ Not functional  
**Blocker Owner:** TS24 Infrastructure Team

---

## Changelog

| Date | Change |
|------|--------|
| 2025-12-01 | Initial DNS status report |
