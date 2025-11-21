# reCAPTCHA v3 Enterprise Integration Test Plan

## Environment Configuration Required

**CRITICAL**: Set these environment variables on your server (typically via `.htaccess`):

```apache
SetEnv RECAPTCHA_SITE_KEY "your_site_key_here"
SetEnv RECAPTCHA_SECRET_KEY "your_secret_key_here"
SetEnv RECAPTCHA_PROJECT_ID "your_project_id_here"  # Enterprise API project
SetEnv RECAPTCHA_DEBUG "true"                       # Optional: Enable debug logging
```

**⚠️ Without these variables, the contact form will fail with "Invalid site key" errors.**

See `RECAPTCHA-SETUP-GUIDE.md` for detailed setup instructions.

## Test Scenarios

### 1. Success Flow Test

1. Open contact page (`/contact.php`).
2. Fill in valid form data (e.g., Name "Test User", Email `test@example.com`, Message "This is a test message").
3. Submit form.
4. Expected: Green success message appears and form resets.
5. `logs/contact-submissions.log` should contain a JSON line similar to:

  ```json
  {
    "timestamp": "2025-11-19T10:10:00Z",
    "ip": "203.0.113.5",
    "hostname": "example.com",
    "action": "contact",
    "score": 0.92,
    "success": true,
    "reason": "ok",
    "api_mode": "enterprise"
  }
  ```

### 2. Validation Error Test

1. Open contact page.
2. Leave name field empty.
3. Submit form.
4. Expected: Red error message "Udfyld venligst alle obligatoriske felter."
5. `logs/contact-submissions.log` should include `"success": false, "reason": "missing_required_fields"` for that request.

### 3. reCAPTCHA Failure Test (Debug Mode)

1. Open DevTools → Network tab.
2. Fill valid form data.
3. Block/clear the `recaptcha_token` before the request is sent.
4. Submit form.
5. Expected: Red error message "Security validation failed."
6. `logs/contact-submissions.log` should show `"reason": "missing_token"`. `error_log` should contain debug output describing the failure.

### 4. reCAPTCHA Service Unavailable Test

1. Temporarily set an invalid `RECAPTCHA_SECRET_KEY`.
2. Submit valid form data.
3. Expected: Red error message "Security validation failed."
4. `error_log` should show `CONTACT FORM ERROR: reCAPTCHA API request failed - HTTP ...`.
5. `logs/contact-submissions.log` should record `"reason": "api_error"` for the attempt.

## Log Analysis

Monitor `logs/contact-submissions.log` entries; each JSON line should include at minimum:

- `timestamp`: ISO 8601 format
- `ip`: Client IP address
- `hostname`: Reported by reCAPTCHA (or server host)
- `action`: Always `contact`
- `score`: 0.0‑1.0 (higher = more human-like)
- `success`: true/false
- `reason`: `ok`, `missing_token`, `score_too_low`, `invalid_hostname`, `api_error`, etc.
- `api_mode`: `enterprise`, `standard`, or `disabled`

## Expected Outcomes

✅ **All forms work with reCAPTCHA Enterprise API when project ID is configured**
✅ **Graceful fallback to standard API when project ID is missing**
✅ **Safe error messages never expose Google API responses to users**
✅ **Comprehensive logging captures all validation stages**
✅ **Debug mode provides detailed troubleshooting information**
✅ **Score threshold 0.5 blocks suspicious submissions**
✅ **Action validation ensures tokens are for "contact" form**

## Kontaktformular – log & mail selftest

1. Åbn `test/logtest.php` i browseren (`https://<din-host>/test/logtest.php`).
2. Forvent svarteksten `OK`. Hvis du ser en fejl, undersøg `error_log`.
3. Åbn `logs/contact-submissions.log` og verificér en ny JSON-linje med `"status":"selftest"`/`"reason":"selftest_ping"`.
4. Gennemgå `error_log` for eventuelle `CONTACT FORM MAIL DEBUG` eller `CONTACT FORM WARNING` beskeder.
5. (Valgfrit) Gentag testen med `RECAPTCHA_DEBUG=true` for at få ekstra logging.
