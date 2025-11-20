# Contact Form Email Fix - Test Plan

## Quick Start Testing

### Step 1: Submit Test Form
1. Go to: `https://blackbox.codes/contact.php`
2. Fill in:
   - **Navn**: Test Bruger
   - **Email**: test@example.com
   - **Telefon**: +45 12 34 56 78 (optional)
   - **Besked**: Dette er en test besked for at verificere email levering
3. Click "Send forespørgsel"
4. Verify: Green success message appears ✅

### Step 2: Check Server Logs (via cPanel)
**File**: `/home/blackowu/public_html/error_log`

**Look for these entries**:
```
CONTACT FORM MAIL: Using PHP mail() function
CONTACT FORM MAIL DEBUG: Sending via mail() to ops@blackbox.codes
CONTACT FORM MAIL DEBUG: From: noreply@blackbox.codes
CONTACT FORM MAIL DEBUG: Subject: Ny henvendelse fra Blackbox EYE kontaktformular
CONTACT FORM MAIL DEBUG: Message-ID: <...@blackbox.codes>
CONTACT FORM MAIL DEBUG: mail() dispatched successfully
CONTACT FORM MAIL DEBUG: Mail sent successfully to ops@blackbox.codes
```

**Success Criteria**:
- ✅ See "Using PHP mail() function"
- ✅ See "mail() dispatched successfully"
- ✅ See "Mail sent successfully"
- ❌ NO "mail() failed" warnings

### Step 3: Check Submission Log
**File**: `/home/blackowu/public_html/logs/contact-submissions.log`

**Look for**: New JSON line with:
```json
{
  "timestamp": "2025-11-20T...",
  "ip": "X.X.X.X",
  "hostname": "blackbox.codes",
  "action": "contact",
  "score": 0.9,
  "success": true,
  "reason": "ok",
  "name": "Test Bruger",
  "email": "test@example.com",
  "mail_sent": true,
  "mail_recipient": "ops@blackbox.codes"
}
```

**Success Criteria**:
- ✅ New line added
- ✅ `"mail_sent": true`
- ✅ `"success": true`

### Step 4: Check Email Inbox
**Email**: ops@blackbox.codes (Proton Mail)

**Wait**: 5-10 minutes for delivery

**Look for**:
- Subject: "Ny henvendelse fra Blackbox EYE kontaktformular"
- From: Blackbox EYE <noreply@blackbox.codes>
- Reply-To: Test Bruger <test@example.com>
- Body contains: Name, Email, Phone, Message, Score, Hostname, API-mode

**Success Criteria**:
- ✅ Email received in inbox OR spam folder
- ✅ Reply-To is set to test@example.com
- ✅ All form data is present

---

## Expected Outcomes

### Scenario 1: Success (Enhanced mail() works)
✅ Form submits successfully  
✅ Logs show "mail() dispatched successfully"  
✅ Email arrives in ops@blackbox.codes within 10 minutes  
✅ Email may initially go to spam (normal for first few emails)  

**Action**: Mark email as "Not Spam" to improve future delivery

### Scenario 2: Partial Success (mail() works but emails go to spam)
✅ Form submits successfully  
✅ Logs show "mail() dispatched successfully"  
✅ Email arrives in spam folder  

**Action**: Mark as "Not Spam" and consider configuring SMTP (see below)

### Scenario 3: mail() Failed (Shared hosting blocks mail())
✅ Form submits successfully  
❌ Logs show "mail() failed"  
❌ No email received  

**Action**: Configure SMTP credentials (see SMTP Configuration below)

---

## SMTP Configuration (If Needed)

If enhanced mail() doesn't work, configure SMTP for guaranteed delivery.

### Via cPanel (Recommended)

1. Log into cPanel
2. Navigate to "PHP Variables" or "MultiPHP INI Editor"
3. Add these environment variables:

```
SMTP_HOST: smtp.protonmail.ch
SMTP_PORT: 587
SMTP_USERNAME: ops@blackbox.codes
SMTP_PASSWORD: [your-proton-app-password]
SMTP_SECURE: tls
```

4. Save changes
5. Test form again
6. Check logs - should now show "Using SMTP mode"

### Getting Proton Mail SMTP Password

1. Log into Proton Mail
2. Go to Settings → Account → Two-factor authentication
3. Enable 2FA if not already enabled
4. Go to Settings → Account → Mailbox passwords
5. Create an "App password" for "SMTP"
6. Use this password in SMTP_PASSWORD

### Verifying SMTP Mode

After configuring SMTP, submit test form and check logs:

```
CONTACT FORM MAIL: Using SMTP mode (host: smtp.protonmail.ch)
CONTACT FORM MAIL DEBUG: Sending via SMTP to ops@blackbox.codes
CONTACT FORM MAIL DEBUG: SMTP Host: smtp.protonmail.ch:587
CONTACT FORM MAIL DEBUG: SMTP mail sent successfully
```

**Success**: Email should arrive within 1-2 minutes

---

## Troubleshooting

### Problem: No "CONTACT FORM MAIL" logs at all

**Cause**: Code not deployed or PHP errors

**Check**:
1. Verify contact-submit.php was deployed (check file modification time)
2. Check for PHP errors at top of error_log
3. Try submitting form again

### Problem: "mail() failed" in logs

**Cause**: PHP mail() is disabled or misconfigured on server

**Solution**: Configure SMTP (see above)

### Problem: "mail() dispatched successfully" but no email

**Cause**: Mail sent but rejected by mail server or spam filtered

**Solutions**:
1. Wait 15 minutes (shared hosting can be slow)
2. Check spam folder thoroughly
3. Configure SMTP for better deliverability

### Problem: SMTP connection errors

**Check**:
1. SMTP_HOST is correct (smtp.protonmail.ch)
2. SMTP_PORT is 587 (not 465)
3. SMTP_SECURE is "tls" (not "ssl")
4. Username is full email address
5. Password is app password (not account password)

### Problem: Email goes to spam

**Short term**: Mark as "Not Spam"  
**Long term**: 
1. Configure SPF record for domain
2. Configure DKIM signing
3. Use SMTP with Proton Mail
4. Build sender reputation over time

---

## Success Checklist

After testing, verify all of these:

- [ ] Form submits successfully (green success message)
- [ ] error_log shows mail attempt (not "failed")
- [ ] logs/contact-submissions.log has new entry
- [ ] `"mail_sent": true` in log entry
- [ ] Email received in ops@blackbox.codes (inbox or spam)
- [ ] Reply-To header is correct
- [ ] All form data present in email

**If all checked**: ✅ Fix is working correctly

---

## Production Monitoring

### First 24 Hours
- Monitor error_log for any "mail() failed" messages
- Check that emails are being received
- Track if emails go to spam or inbox

### First Week
- Monitor delivery rate
- If < 80% delivery rate → Configure SMTP
- Track spam vs inbox placement

### Ongoing
- Set up alert for "CONTACT FORM WARNING"
- Monthly review of delivery metrics
- Quarterly review of spam placement

---

## Alternative Test (Test Environment)

If `/test/contact.php` exists:

1. Navigate to `https://blackbox.codes/test/contact.php`
2. Fill in test form
3. Check logs as above
4. Verify email delivery

This allows testing without affecting production metrics.

---

## Quick Reference

### Log Files
- Main log: `/home/blackowu/public_html/error_log`
- Submission log: `/home/blackowu/public_html/logs/contact-submissions.log`

### Success Indicators
- "mail() dispatched successfully" in error_log
- "mail_sent": true in contact-submissions.log
- Email in ops@blackbox.codes inbox

### Failure Indicators
- "mail() failed" in error_log
- "mail_sent": false in contact-submissions.log
- No email after 15 minutes

### Quick Fix
If mail() fails → Configure SMTP → Test again → Should work 100%

---

## Estimated Time

- Initial test: 5 minutes
- Wait for email: 5-10 minutes
- Total: 15 minutes

If SMTP configuration needed:
- Configure SMTP: 10 minutes
- Test again: 5 minutes
- Total: 30 minutes

---

## Support

For questions or issues:
1. Check CONTACT-FORM-EMAIL-FIX-ROOT-CAUSE.md (detailed analysis)
2. Review error_log for specific errors
3. Verify environment configuration

---

**Document Version**: 1.0  
**Date**: 2025-11-20  
**Agent**: ALPHA-Web-Diagnostics-Agent
