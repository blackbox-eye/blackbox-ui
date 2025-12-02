
# GDI / TS24 SSO – End-to-End Test Plan

The steps below document how to manually validate the ALPHA Interface GUI single-sign-on flow for GreyEYE operators connecting to TS24. Follow them in order when smoke-testing deployments or debugging incidents. Automation coverage will be added in a future sprint.

See also `docs/sso_v1_signoff_gdi.md` for the GUI-side sign-off checklist that accompanies these scenarios.


## Prerequisites

- Valid GreyEYE agent account with dashboard access.
- `GDI_SSO_SECRET` (or fallback `JWT_SECRET`) and `TS24_CONSOLE_URL` configured in the target environment.
- Ability to inspect browser network traffic (DevTools) or server logs if troubleshooting.


## Test Matrix

| Scenario | Description | Expected Result |
| --- | --- | --- |
| Happy path login | Authenticate as agent, open TS24 link | Browser navigates to `TS24_CONSOLE_URL?sso=...` and TS24 accepts token, landing on operator console |
| Expired token | Reuse a link older than configured TTL | TS24 rejects token; GUI prompts for new login and regenerates fresh token |
| Missing secret | Unset `GDI_SSO_SECRET` and login | `agent-access.php` surfaces error banner, `tools/sso_health.php` reports `has_secret = false`; TS24 link disabled |
| Tampered token | Modify one character in `sso` query param | TS24 rejects token, throttling kicks in; GUI log captures verification failure |
| Multilingual labels | Switch locale to Danish and repeat happy path | UI strings render in Danish, token exchange still succeeds |


## Detailed Steps


### 1. Happy Path

> Tip: Run `php scripts/check_sso_health.php` first to ensure the environment is correctly configured before performing the manual flow.

1. Navigate to `/agent-login.php` and sign in with a valid operator account.
2. Confirm dashboard loads and `/agent-access.php` displays the TS24 command button.
3. Click the TS24 button; intercept the outgoing request in DevTools.
4. Verify the link matches `TS24_CONSOLE_URL?sso=<JWT>` and payload decodes to the current agent (audience `ts24`).
5. Confirm TS24 console loads without prompting for credentials.


### 2. Expired Token Handling

1. From the happy path, copy the `sso` link and wait longer than `BBX_JWT_TTL` (default 600 s).
2. Paste the stale link into a new tab.
3. Expected: TS24 rejects the token. The GUI should display a toast or inline error instructing the operator to relaunch from the portal.
4. Click the TS24 button again to ensure a fresh token restores access.


### 3. Missing Secret Safeguards

1. In a staging environment, temporarily remove `GDI_SSO_SECRET`.
2. Load `/tools/sso_health.php` and confirm it returns `sso_enabled = false` with notes about the missing secret.
3. Visit `/agent-access.php`; the TS24 button should be disabled or hidden with an error indicator.
4. Restore the secret and rerun the healthcheck to confirm the issue clears.


### 4. Tampered Token Defense

1. Perform a successful login and copy the generated `sso` link.
2. Manually alter one character in the JWT payload segment.
3. Load the modified URL.
4. Expected: TS24 rejects the link, and the GUI logs the failure (`jwt_mint_ok` remains true). No privileged session should be established.


### 5. Multilingual Regression (Optional)

1. Toggle the language switcher to Danish.
2. Repeat the happy path test to ensure translation keys resolve correctly and SSO is unaffected.


## SSO Healthcheck (GUI-side)

Prerequisites:

- Target environment is running and reachable (default base URL `http://127.0.0.1:8080`).
- PHP CLI available locally or in the CI runner.

Command:

```bash
php scripts/check_sso_health.php
```

Override the base URL by passing it as the first argument or via `SSO_HEALTH_BASE_URL`, for example:

```bash
SSO_HEALTH_BASE_URL="https://staging.alpha-eye.local" php scripts/check_sso_health.php
```

Interpret the result:

- The script now reports both layers, for example:

    ```text
    GDI SSO: OK
    TS24 SSO: OK
    ```

- Exit code `0` means both the GUI (secret, TS24 URL, JWT mint) **and** TS24’s `/api/auth/sso-health` endpoint agree the stack is healthy.
- Non-zero exit codes occur if either side is misconfigured (missing secret, JWT lib, TS24 health unreachable, `usesHS256=false`, etc.). Inspect `/tools/sso_health.php` locally and `{TS24_CONSOLE_URL}/api/auth/sso-health` remotely for JSON notes.

Triage hint: If the GUI half fails, verify env vars in the hosting control panel, confirm `vendor/autoload.php` exists, and ensure the TS24 base URL points to a live environment. If the TS24 half fails, check that `TS24_CONSOLE_URL` is reachable and that TS24’s `VITE_SSO_JWT_SECRET`, issuer/audience and HS256 config match the GreyEYE values.

### CI SSO Healthcheck

GitHub Actions now runs `php scripts/check_sso_health.php` inside the Visual Regression workflow before Playwright spins up. The job starts a PHP dev server (`php -S 127.0.0.1:8080 -t .`), hits the GUI health endpoint, then calls `{TS24_CONSOLE_URL}/api/auth/sso-health`. Any FAIL on either line blocks the pipeline. Even with this guardrail, perform the manual scenarios above whenever making significant SSO or portal changes.


## Observability & Tooling

- `/tools/sso_health.php` now reports configuration status, JWT minting ability and TTL to help operators validate environments quickly.
- Application logs include `JWT issuance` entries with timestamps; inspect them if tokens fail.

## Sign-off Checklist

- [ ] Healthcheck endpoint returns `sso_enabled = true` and `jwt_mint_ok = true`.
- [ ] Manual happy path succeeds twice in a row.
- [ ] Expired/tampered token tests fail safely without side effects.
- [ ] Missing-secret guardrails verified.
- [ ] Screenshots or logs archived in the release ticket.
