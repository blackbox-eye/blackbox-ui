# SMTP Configuration Guide

## When to Use SMTP

Configure SMTP if:
- Enhanced mail() still doesn't deliver emails after 15 minutes
- Emails consistently go to spam
- Server has mail() disabled
- You want guaranteed delivery

## Quick Setup (Recommended: Proton Mail)

Since `ops@blackbox.codes` already uses Proton Mail, using Proton SMTP ensures best deliverability.

### Step 1: Get Proton Mail App Password

1. Log into Proton Mail (ops@blackbox.codes)
2. Click Settings (gear icon) → Go to Settings
3. Navigate to: **Account** → **Security**
4. Scroll to: **Two-factor authentication**
5. If not enabled: Enable 2FA first
6. Navigate to: **Account** → **Mailbox passwords**
7. Click: **Add mailbox password**
8. Name: "Blackbox Contact Form"
9. Click: **Create**
10. **Copy the password** - you won't see it again!

### Step 2: Add to cPanel

**Method 1: Using PHP Variables (Recommended)**

1. Log into cPanel
2. Find: **Software** → **MultiPHP INI Editor**
3. Select domain: blackbox.codes
4. Click: **Editor Mode**
5. Scroll to bottom
6. Add these lines:
```
[Environment]
SMTP_HOST=smtp.protonmail.ch
SMTP_PORT=587
SMTP_USERNAME=ops@blackbox.codes
SMTP_PASSWORD=your-proton-app-password-here
SMTP_SECURE=tls
```
7. Click: **Save**

**Method 2: Using .htaccess (Less Secure)**

Add to `.htaccess` (but don't commit to git):
```apache
# SMTP Configuration for Contact Form
SetEnv SMTP_HOST "smtp.protonmail.ch"
SetEnv SMTP_PORT "587"
SetEnv SMTP_USERNAME "ops@blackbox.codes"
SetEnv SMTP_PASSWORD "your-proton-app-password"
SetEnv SMTP_SECURE "tls"
```

⚠️ **Security Warning**: Never commit passwords to git!

### Step 3: Test

1. Submit test form at https://blackbox.codes/contact.php
2. Check error_log - should see:
```
CONTACT FORM MAIL: Using SMTP mode (host: smtp.protonmail.ch)
CONTACT FORM MAIL DEBUG: SMTP mail sent successfully
```
3. Check email - should arrive within 1-2 minutes

---

## Alternative SMTP Providers

### Gmail

**Settings:**
```
SMTP_HOST: smtp.gmail.com
SMTP_PORT: 587
SMTP_USERNAME: your-email@gmail.com
SMTP_PASSWORD: [app-specific password]
SMTP_SECURE: tls
```

**Get App Password:**
1. Go to: https://myaccount.google.com/security
2. Enable 2-Step Verification
3. Go to: App passwords
4. Select: Mail → Other (Custom name)
5. Name: "Blackbox Contact Form"
6. Copy generated password

### Office365 / Outlook

**Settings:**
```
SMTP_HOST: smtp.office365.com
SMTP_PORT: 587
SMTP_USERNAME: your-email@outlook.com
SMTP_PASSWORD: your-password
SMTP_SECURE: tls
```

### SendGrid (Professional)

**Settings:**
```
SMTP_HOST: smtp.sendgrid.net
SMTP_PORT: 587
SMTP_USERNAME: apikey
SMTP_PASSWORD: [your-sendgrid-api-key]
SMTP_SECURE: tls
```

**Get API Key:**
1. Sign up at: https://sendgrid.com
2. Go to: Settings → API Keys
3. Create API Key with "Mail Send" permission
4. Copy API key and use as password

---

## Verification

After configuring SMTP, submit a test form and verify:

### In error_log:
```
✅ CONTACT FORM MAIL: Using SMTP mode (host: smtp.protonmail.ch)
✅ CONTACT FORM MAIL DEBUG: Sending via SMTP to ops@blackbox.codes
✅ CONTACT FORM MAIL DEBUG: SMTP Host: smtp.protonmail.ch:587
✅ CONTACT FORM MAIL DEBUG: SMTP mail sent successfully
✅ CONTACT FORM MAIL DEBUG: Mail sent successfully to ops@blackbox.codes
```

### In inbox:
✅ Email arrives within 1-2 minutes (not 10+ minutes)  
✅ Email goes to inbox (not spam)  
✅ Reply-To is correct  

---

## Troubleshooting SMTP

### Error: "SMTP connect() failed"

**Causes:**
- Firewall blocking port 587
- Incorrect SMTP_HOST
- Server doesn't support outbound SMTP

**Solutions:**
1. Try port 465 with SMTP_SECURE=ssl
2. Verify SMTP_HOST spelling
3. Contact hosting provider about SMTP access

### Error: "SMTP authentication failed"

**Causes:**
- Wrong username (must be full email address)
- Wrong password
- 2FA not enabled for app passwords

**Solutions:**
1. Verify SMTP_USERNAME is full email: ops@blackbox.codes
2. Generate new app password
3. Ensure 2FA is enabled in Proton Mail

### Error: "Could not instantiate mail function"

**Cause:** PHPMailer not loaded correctly

**Solution:**
1. Verify includes/PHPMailer/ directory exists
2. Check PHP error log for require_once errors
3. Verify file permissions

---

## Security Best Practices

### ✅ DO:
- Store passwords in cPanel environment variables
- Use app-specific passwords (not account password)
- Enable 2FA on email accounts
- Use TLS encryption (SMTP_SECURE=tls)
- Rotate passwords every 90 days

### ❌ DON'T:
- Commit passwords to git
- Share passwords in documentation
- Use account passwords for SMTP
- Disable SSL verification
- Store passwords in .htaccess (if possible)

---

## Performance Notes

### PHP mail() Mode:
- **Pros**: Fast (~2ms), no external dependencies
- **Cons**: Unreliable on shared hosting, may go to spam

### SMTP Mode:
- **Pros**: Reliable, better deliverability, professional
- **Cons**: Slower (~50-200ms), requires external service

**Recommendation**: Use SMTP for production (reliability > speed)

---

## Migration Path

### Phase 1 (Current):
- Deploy enhanced mail() fix
- Test on production
- Monitor for 24 hours

### Phase 2 (If needed):
- Configure SMTP with Proton Mail
- Test delivery
- Monitor for 1 week

### Phase 3 (Optional):
- Consider dedicated SMTP service (SendGrid)
- Set up monitoring and alerts
- Implement email queue

---

## Cost Analysis

### Proton Mail SMTP:
- **Cost**: FREE (included with account)
- **Limit**: Subject to Proton Mail account limits
- **Best for**: Current usage levels

### Gmail SMTP:
- **Cost**: FREE (personal account)
- **Limit**: 500 emails/day
- **Best for**: Low-medium volume

### SendGrid:
- **Cost**: FREE tier (100 emails/day)
- **Cost**: $15/month (40,000 emails/month)
- **Best for**: Professional use, high volume

**Recommendation**: Start with Proton Mail SMTP (free, already have account)

---

## Quick Reference Card

```
┌─────────────────────────────────────────┐
│     PROTON MAIL SMTP CONFIG            │
├─────────────────────────────────────────┤
│ SMTP_HOST: smtp.protonmail.ch          │
│ SMTP_PORT: 587                          │
│ SMTP_USERNAME: ops@blackbox.codes      │
│ SMTP_PASSWORD: [app-password]          │
│ SMTP_SECURE: tls                        │
└─────────────────────────────────────────┘

Get app password:
Settings → Security → Mailbox passwords

Add to cPanel:
Software → MultiPHP INI Editor

Test:
Submit form → Check error_log
Should see: "Using SMTP mode"
```

---

## Support

For issues:
1. Check error_log for specific error message
2. Verify SMTP credentials are correct
3. Test SMTP connection with different tool
4. Contact hosting provider about SMTP access

---

**Document Version**: 1.0  
**Date**: 2025-11-20  
**Agent**: ALPHA-Web-Diagnostics-Agent
