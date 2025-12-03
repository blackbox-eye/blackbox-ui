# Blackbox UI — QA Support Guide (SSO v1)

Version: 2025-12-01 · Maintainer: GUI QA Team (GDI)

This guide mirrors TS24 backend QA expectations but tailors each step to the Blackbox UI. Use it whenever the TS24 platform declares QA-support mode.

---

## A. SSO Flow (Frontend Perspective)

```text
   GDI Client       ALPHA GUI          SSO Gateway           TS24 Dashboard
      |                 |                   |                       |
      | 1. Navigate     |                   |                       |
      |---------------->|                   |                       |
      |                 | 2. Render login   |                       |
      |                 | card + CTA        |                       |
      |                 |------------------>| 3. Auth UI handshake  |
      |                 |                   |                       |
      |                 | 4. Token inject   |<----------------------|
      |                 | (session/local)   |                       |
      |                 |------------------>| 5. Redirect payload   |
      |                 |                   |---------------------->|
      |                 | 6. Route to       |                       | 7. Dashboard OK
      |<----------------|    /ts24-bridge   |                       |
```

---

## B. GUI QA Test Scenarios (10)

1. **Token persistence** – Validate token stored in sessionStorage/localStorage + cookie with identical payload/expiry.
2. **Missing token redirect** – Force `token=null` before guard; expect immediate redirect to `/agent-login` with warning banner.
3. **Expired token fallback** – Inject token with past `exp`; guard must purge storage and request new token.
4. **UI routing integrity** – Navigate between `/dashboard`, `/admin`, `/settings`; ensure guard reenforces token without stale redirects.
5. **TS24 redirect validation** – Confirm final navigation target equals `https://intel24.blackbox.codes/sso-login` (or stub) before dashboard load.
6. **Broken SSO error view** – Simulate 500 from SSO; GUI must show fail-safe view with retry + ops contact.
7. **Race condition stress** – Fire parallel navigation + token fetch; guard should serialize and avoid double redirects.
8. **Mobile breakpoint redirect** – Emulate 375px width (iPhone) and ensure CTA, spinner, and redirect remain accessible.
9. **Browser refresh resilience** – Refresh mid-flow; session token should survive and continue redirect.
10. **Cookie integrity** – Inspect `SameSite=None; Secure; HttpOnly` attributes; confirm mismatches trigger logout.

---

## C. Troubleshooting Playbook

- **Network tab trace** – Filter by Initiator "SSO"; capture request waterfall (login → token → dashboard). Export HAR for TS24.
- **Routing loop detection** – Watch console for repeated `/agent-login → /dashboard → /agent-login`; guard should hard-stop after 2 loops with alert.
- **CORS / redirect faults** – Verify response headers (`access-control-allow-origin`, `location`) on 302 chain when hitting TS24 stub/prod.
- **Token mismatch without reload** – Modify token in storage while keeping cookie; guard must trigger `bbx:token-mismatch` event and purge state without requiring F5.

---

## D. GUI QA Checklist (8)

1. CTA click → SSO → Dashboard (full flow) succeeds twice consecutively.
2. Mobile (375px) + Desktop (1440px) coverage with identical outcomes.
3. Cold load (cache cleared) and hot reload (HMR) both honor guard logic.
4. Cookie expiry simulation (set `maxAge = -1`) forces re-auth without white screen.
5. Dashboard load time ≤ 1.2s after TS24 redirects complete.
6. Proxy behaviour – Verify Cloudflare proxied domain still allows Secure cookies + redirects.
7. Hard reload during SSO (Ctrl+Shift+R) resumes flow without manual login.
8. Redirect fail-safe – If TS24 unreachable, GUI surfaces fallback banner + retry button.

---

## E. Post-QA Hooks

- **Cleanup branch** – Remove temporary QA instrumentation (`QA_MODE` toggles, console traces) before merge to `main`.
- **Patch pipeline** – Queue `qa_gui_patch` in CI once TS24 signs off; includes building Tailwind + Playwright regressions.
- **Regression kit** – Archive HAR files, Playwright traces, and console logs to `/artifacts/qa-gui/<date>` for future audits.

---

Always align timestamps, endpoints, and guard behaviour with the latest TS24 backend bulletins. For escalation, contact `qa-gui@alphaacc.es` with HAR + console logs.
