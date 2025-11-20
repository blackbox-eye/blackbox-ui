# reCAPTCHA Enterprise Setup Guide

## 🎯 Overview

This guide explains how to configure reCAPTCHA Enterprise for the Blackbox EYE contact form.

## 📋 Prerequisites

1. Google Cloud Platform account
2. reCAPTCHA Enterprise API enabled
3. Site key registered for domain `blackbox.codes`
4. Access to server `.htaccess` or environment variables

---

## 🔑 Required Environment Variables

Add these to your `.htaccess` file or server environment:

```apache
SetEnv RECAPTCHA_SITE_KEY "your_site_key_here"
SetEnv RECAPTCHA_SECRET_KEY "your_secret_key_here"
SetEnv RECAPTCHA_PROJECT_ID "your_project_id_here"
SetEnv RECAPTCHA_DEBUG "false"
```

### Variable Descriptions

| Variable | Description | Required | Example |
|----------|-------------|----------|---------|
| `RECAPTCHA_SITE_KEY` | Public site key for frontend | YES | `6LcXXXXXXXXXXXXX...` |
| `RECAPTCHA_SECRET_KEY` | Secret key for backend verification | YES | `6LcXXXXXXXXXXXXX...` |
| `RECAPTCHA_PROJECT_ID` | GCP project ID for Enterprise API | YES (for Enterprise) | `blackbox-eye-12345` |
| `RECAPTCHA_DEBUG` | Enable debug logging | NO | `true` or `false` |

---

## 🚀 Setup Steps

### Step 1: Create reCAPTCHA Enterprise Keys

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Enable **reCAPTCHA Enterprise API**
3. Navigate to **Security** > **reCAPTCHA Enterprise**
4. Click **Create Key**
5. Configure:
   - **Display name**: Blackbox EYE Contact Form
   - **Platform type**: Website
   - **Domains**: `blackbox.codes`, `www.blackbox.codes`
   - **reCAPTCHA type**: Score-based (recommended)
6. Save the **Site Key** and **API Key** (Secret Key)
7. Note your **Project ID** from the GCP project

### Step 2: Configure Server Environment

**Option A: Using .htaccess (cPanel)**

Add to `.htaccess` in the site root:

```apache
# reCAPTCHA Enterprise Configuration
SetEnv RECAPTCHA_SITE_KEY "6LcXXXXXXXXXXXXXXXXX"
SetEnv RECAPTCHA_SECRET_KEY "6LcXXXXXXXXXXXXXXXXX"
SetEnv RECAPTCHA_PROJECT_ID "your-project-id"
SetEnv RECAPTCHA_DEBUG "false"
```

**Option B: Using php.ini or server config**

```ini
env[RECAPTCHA_SITE_KEY] = "6LcXXXXXXXXXXXXXXXXX"
env[RECAPTCHA_SECRET_KEY] = "6LcXXXXXXXXXXXXXXXXX"
env[RECAPTCHA_PROJECT_ID] = "your-project-id"
env[RECAPTCHA_DEBUG] = "false"
```

### Step 3: Verify Configuration

1. Visit `/contact.php?RECAPTCHA_DEBUG=true`
2. Open browser console
3. Look for:
   ```
   [Contact Form] Configuration: {
     endpoint: "contact-submit.php",
     recaptchaSiteKey: "6LcXXXXXXXXXXXXX...",
     grecaptchaLoaded: true,
     enterpriseAvailable: true,
     debug: true
   }
   ```

### Step 4: Test Submission

1. Fill out contact form
2. Submit
3. Check console for:
   - `[reCAPTCHA] Contact form submit started`
   - `[reCAPTCHA] Using Enterprise reCAPTCHA API`
   - `[reCAPTCHA] Token generated (length: 2000+)`
   - `[reCAPTCHA] Response status: 200 OK`
   - `[reCAPTCHA] Parsed response: {success: true, ...}`

---

## 🐛 Troubleshooting

### Error: "Invalid site key or not loaded in api.js"

**Cause**: Site key is empty, invalid, or not registered for current domain

**Solutions**:
1. Verify `RECAPTCHA_SITE_KEY` is set in `.htaccess`
2. Confirm site key is registered for `blackbox.codes` in GCP Console
3. Check browser console for key value (first 20 chars)
4. Restart PHP/web server after changing `.htaccess`

### Error: "grecaptcha not loaded - script may be blocked"

**Cause**: reCAPTCHA script failed to load

**Solutions**:
1. Check if `RECAPTCHA_SITE_KEY` is empty (script won't load)
2. Verify no ad blockers are active
3. Check browser Network tab for failed script loads
4. Ensure site key is valid

### Error: "Missing reCAPTCHA token in submission"

**Cause**: Frontend couldn't generate token

**Solutions**:
1. Check console for frontend errors
2. Verify site key matches domain
3. Ensure script loaded successfully
4. Check if `grecaptcha.enterprise` is available

### Error: "reCAPTCHA API request failed - HTTP 400/401"

**Cause**: Invalid secret key or project ID

**Solutions**:
1. Verify `RECAPTCHA_SECRET_KEY` in `.htaccess`
2. Confirm `RECAPTCHA_PROJECT_ID` matches GCP project
3. Check API is enabled in GCP Console
4. Verify service account has correct permissions

### No Errors But Form Doesn't Submit

**Cause**: Script loading but execution failing silently

**Solutions**:
1. Enable debug mode: `?RECAPTCHA_DEBUG=true`
2. Set `window.RECAPTCHA_DEBUG = true` in console
3. Check for JavaScript errors
4. Verify `data-endpoint="contact-submit.php"` on form
5. Check Network tab for POST request

---

## 🔒 Security Best Practices

### Do:
✅ Keep secret key confidential  
✅ Use environment variables (not git)  
✅ Set minimum score threshold (0.5 recommended)  
✅ Validate hostname in backend  
✅ Log all validation attempts  
✅ Monitor for unusual patterns  

### Don't:
❌ Commit keys to git  
❌ Expose secret key in client code  
❌ Skip hostname validation  
❌ Trust score alone (use action + hostname)  
❌ Ignore failed validations  

---

## 📊 Monitoring

### Backend Logs (error_log)

When `RECAPTCHA_DEBUG=true`, you'll see:

```
CONTACT FORM DEBUG: reCAPTCHA configured=YES
reCAPTCHA Debug - Mode: Enterprise
reCAPTCHA Debug - Endpoint: https://recaptchaenterprise.googleapis.com/...
reCAPTCHA Debug - Response: {"tokenProperties":{"valid":true,...},"riskAnalysis":{"score":0.9,...}}
CONTACT FORM DEBUG: reCAPTCHA validation successful - Score: 0.9, Action: contact
```

### Contact Submission Logs

Check `logs/contact-submissions.log`:

```json
{
  "timestamp": "2025-11-21T10:30:00+00:00",
  "ip": "203.0.113.42",
  "hostname": "blackbox.codes",
  "action": "contact",
  "score": 0.92,
  "success": true,
  "reason": "ok",
  "name": "John Doe",
  "email": "john@example.com",
  "api_mode": "enterprise",
  "mail_sent": true,
  "mail_recipient": "ops@blackbox.codes"
}
```

---

## 🧪 Testing Checklist

After configuration, verify:

- [ ] Site key visible in page source (first 20 chars)
- [ ] reCAPTCHA Enterprise script loads without errors
- [ ] Console shows configuration object with all fields
- [ ] Form submission generates token
- [ ] POST request sent to `contact-submit.php`
- [ ] Response is JSON with `{success: true}`
- [ ] Log entry created in `logs/contact-submissions.log`
- [ ] Email received at `ops@blackbox.codes`
- [ ] error_log shows successful validation (if debug enabled)

---

## 📚 Additional Resources

- [reCAPTCHA Enterprise Documentation](https://cloud.google.com/recaptcha-enterprise/docs)
- [reCAPTCHA API Reference](https://cloud.google.com/recaptcha-enterprise/docs/reference/rest)
- [Score Interpretation Guide](https://cloud.google.com/recaptcha-enterprise/docs/interpret-assessment)

---

## 🆘 Support

If issues persist after following this guide:

1. Check `CONTACT-FORM-TEST-PLAN.md` for detailed test procedures
2. Review `CONTACT-FORM-FIX-ANALYSIS.md` for technical details
3. Enable debug mode and collect console + error_log output
4. Verify all environment variables are set correctly

---

*Last updated: 2025-11-21*  
*ALPHA Interface GUI - reCAPTCHA Enterprise Integration*
