# ALPHA-QA RUN — ROUND 1 RESULTS (GUI SSO)

## Test Environment

- **URL:** `__ENTER_ENV_URL__`
- **Build / Commit Hash:** `__ENTER_COMMIT__`
- **QA_MODE Value:** `__0_or_1__`
- **Browser(s):** `__e.g. Chromium 114 / WebKit iOS__`
- **Credentials / Agent ID:** `__OPTIONAL__`

## Commands Executed

1. `npm run qa:gui`
2. `__ADDITIONAL_COMMAND__`
3. `__MANUAL_FLOW_NOTES__`

## Overall Result

- **Status:** `PASS | FAIL`
- **Summary:**
  1. `__LINE_1__`
  2. `__LINE_2__`
  3. `__LINE_3__`

## Detailed Test Report

| Scenario | Status | Notes |
| --- | --- | --- |
| Valid token → dashboard redirect | `OK/FAIL` | `__DETAIL__` |
| No token → redirect to login | `OK/FAIL` | `__DETAIL__` |
| Expired token → fallback | `OK/FAIL` | `__DETAIL__` |
| Invalid signature → 401 | `OK/FAIL` | `__DETAIL__` |
| Wrong issuer/audience → 401 | `OK/FAIL` | `__DETAIL__` |
| Malformed token → 401 | `OK/FAIL` | `__DETAIL__` |
| Cookie desync handling | `OK/FAIL` | `__DETAIL__` |
| Cold load (no cache) | `OK/FAIL` | `__DETAIL__` |
| Browser restart persistence | `OK/FAIL` | `__DETAIL__` |
| Mobile viewport (iPhone 12) | `OK/FAIL` | `__DETAIL__` |
| First paint & redirect latency budget | `OK/FAIL` | `__DETAIL__` |
| Logging hooks (console/network/screenshots) | `OK/FAIL` | `__DETAIL__` |

> Add extra rows if additional scenarios were covered.

## P0 / P1 / P2 Issues

| ID | Severity | Flow (TOKEN / COOKIE / ROUTING / UI / NGINX) | Description | Temporary Workaround |
| --- | --- | --- | --- | --- |
| `__ISSUE_ID__` | `P0/P1/P2` | `__FLOW__` | `__SUMMARY__` | `__WORKAROUND__` |

## Artefacts

- **Playwright Report:** `__PATH_OR_URL__`
- **Screenshots / Video:** `__PATH_OR_URL__`
- **Logs (ALPHA-SSO / War-room):** `__PATH_OR_URL__`

## Recommended TS24 Signal

- `QA PASS — APPROVED FOR RELEASE`
- `QA BLOCKER — P0`
- Notes: `__RISK_OR_DECISION__`
