# reCAPTCHA Standard v3 Setup Guide

## 🎯 Overview
This guide documents how to configure Google reCAPTCHA **Standard v3** for the Blackbox EYE contact form. The backend now calls `https://www.google.com/recaptcha/api/siteverify`, so no Google Cloud project ID is required.

---

## 📋 Prerequisites
- Google account with access to the [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin/create)
- Domains `blackbox.codes` and `www.blackbox.codes` verified in the reCAPTCHA console
- Server access to manage environment variables (`.htaccess`, cPanel MultiPHP INI Editor, or Apache config)

---

## 🔑 Environment Variables
Add these values to your server environment. Use `.htaccess`, the cPanel MultiPHP INI Editor (preferred), or another secure mechanism.

| Variable | Required | Example | Notes |
|----------|----------|---------|-------|
| `RECAPTCHA_SITE_KEY` | ✅ | `6LeXXXX...` | Public key embedded in the frontend |
| `RECAPTCHA_SECRET_KEY` | ✅ | `6LeXXXX...` | Secret used by the backend verification endpoint |
| `RECAPTCHA_PROJECT_ID` | ➖ | *(leave unset)* | Only required if you re-enable Enterprise |
| `RECAPTCHA_DEBUG` | ➖ | `false` | Enables verbose logging for troubleshooting |

📌 **Do not** store these values in version control. Prefer the MultiPHP INI Editor to keep secrets out of the web root.

---

## 🚀 Setup Steps

### Step 1 – Create Standard v3 Keys
1. Visit the [reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin/create).
2. Choose **reCAPTCHA v3**.
3. Enter a label (e.g. `Blackbox EYE Contact Form`).
4. Add the domains `blackbox.codes` and `www.blackbox.codes`.
5. Accept the terms and submit.
6. Copy both the **Site key** and **Secret key**.

### Step 2 – Configure Server Environment

**Option A: `.htaccess` (quick start)**
```apache
# reCAPTCHA Standard v3
SetEnv RECAPTCHA_SITE_KEY "6LeXXXXXXXXXXXXXXXXXXXX"
SetEnv RECAPTCHA_SECRET_KEY "6LeXXXXXXXXXXXXXXXXXXXX"
SetEnv RECAPTCHA_DEBUG "false"
```
Leave `RECAPTCHA_PROJECT_ID` unset for Standard v3.

**Option B: MultiPHP INI Editor (recommended)**
```
[Environment]
RECAPTCHA_SITE_KEY="6LeXXXXXXXXXXXXXXXXXXXX"
RECAPTCHA_SECRET_KEY="6LeXXXXXXXXXXXXXXXXXXXX"
RECAPTCHA_DEBUG="false"
```
Using the INI editor keeps secrets off disk and works reliably with php-fpm.

### Step 3 – Verify Frontend
1. Open `https://blackbox.codes/contact.php?RECAPTCHA_DEBUG=true`.
2. Open DevTools ▸ **Console**.
3. You should see:
   ```
   [Contact Form] Configuration: {
     endpoint: "contact-submit.php",
     recaptchaSiteKey: "6Le7iBMsAAAAACp8jtY4J...",
     grecaptchaLoaded: true,
     enterpriseAvailable: false,
     debug: true
   }
   ```
4. Submitting the form should log `Parsed response: { success: true, ... }`.

### Step 4 – Verify Backend
1. Check `error_log` after a submission with `RECAPTCHA_DEBUG=true`.
2. A successful validation looks like:
   ```
   CONTACT FORM DEBUG: reCAPTCHA validation successful - Score: 0.90, Action: contact
   ```
3. The contact log (`logs/contact-submissions.log`) should include `"api_mode": "standard_v3"`.

---

## 🐛 Troubleshooting

**"Invalid site key"**
- Confirm the site key matches the reCAPTCHA console.
- Ensure the domain is whitelisted in the console.

**"grecaptcha not loaded"**
- Check for script blockers.
- Ensure `<script src="https://www.google.com/recaptcha/api.js" async defer></script>` is present.

**"Missing reCAPTCHA token"**
- Token acquisition timed out (>5s).
- Look for `[reCAPTCHA] RECAPTCHA FRONTEND ERROR` in the console.
- Verify the site key is non-empty and the action is `contact`.

**Low score (<0.5)**
- Google flagged the request.
- Consider additional checks (honeypot) or review logs for abuse patterns.

**PHP still reports missing env vars**
- Confirm the updated `includes/env.php` is deployed (it now checks `$_SERVER` and `$_ENV`).
- If using `.htaccess`, reload php-fpm or re-save in MultiPHP.

---

## 🔒 Security Notes
- Keep `RECAPTCHA_SECRET_KEY` out of git and out of the document root.
- Disable `RECAPTCHA_DEBUG` after troubleshooting to reduce log noise.
- Validate hostname (`blackbox.codes`) and action (`contact`) server-side (already enforced in `contact-submit.php`).
- Rotate keys if you suspect leaks or after major infrastructure changes.

---

## 📊 Monitoring & Logs
With debug enabled you should see lines like:
```
CONTACT FORM DEBUG: reCAPTCHA configured=YES
reCAPTCHA Debug - Mode: Standard v3
reCAPTCHA Debug - Endpoint: https://www.google.com/recaptcha/api/siteverify
reCAPTCHA Debug - Response: {"success":true,"score":0.9,"action":"contact","hostname":"blackbox.codes"}
```
Contact submissions log (`logs/contact-submissions.log`) entry example:
```json
{
  "timestamp": "2025-11-21T02:21:05+00:00",
  "hostname": "blackbox.codes",
  "action": "contact",
  "score": 0.9,
  "api_mode": "standard_v3",
  "mail_sent": true
}
```

---

## 🧪 Testing Checklist
- [ ] Site key present in markup (first 6–8 chars match console)
- [ ] Console shows `enterpriseAvailable: false`
- [ ] POST to `contact-submit.php` returns `{ success: true }`
- [ ] `error_log` records successful validation details
- [ ] `logs/contact-submissions.log` records `standard_v3`
- [ ] Notification email received at `ops@blackbox.codes`

---

## 📚 Helpful Links
- [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin)
- [reCAPTCHA v3 Documentation](https://developers.google.com/recaptcha/docs/v3)
- [SMTP Deployment Guide](SMTP-DEPLOYMENT-GUIDE.md)
- [Contact Form Test Plan](CONTACT-FORM-EMAIL-TEST-PLAN.md)

---

*Last updated: 2025-11-21*
*Maintainer: Blackbox UI Team*
