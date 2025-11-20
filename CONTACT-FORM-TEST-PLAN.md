# Contact Form Testing & Validation Plan

## Overview
This document provides a comprehensive test plan for validating the contact form logging and mail functionality after the latest fixes.

## Changes Made

### 1. Enhanced Logging Function (`bbx_log_contact_submission`)
- **Always active logging**: Function entry/exit now logged regardless of debug flags
- **Directory creation**: Explicit logging when logs directory is created or fails
- **Error reporting**: Uses `error_get_last()` to capture detailed mkdir/file_put_contents errors
- **Fallback logging**: Always writes to error_log if file logging fails
- **Success confirmation**: Reports bytes written on successful log operations

### 2. Mail Recipient Configuration
- **Guaranteed fallback**: Defaults to `ops@blackbox.codes` if CONTACT_EMAIL is empty/null
- **Explicit logging**: Always logs the configured recipient address
- **Warning on default**: Logs warning when environment variable is not set

### 3. Mail Debug Logging
- **Always active**: Mail operations logged regardless of debug flags
- **Pre-send logging**: Logs recipient, subject, and from address before mail() call
- **Result logging**: Explicitly logs mail() return value (TRUE/FALSE)
- **Error context**: Additional warning message when mail() fails

## Manual Test Procedure

### Test 1: Basic Form Submission
**Objective**: Verify complete logging and mail flow

**Steps**:
1. Navigate to `https://blackbox.codes/contact.php`
2. Fill in the form:
   - **Name**: Test User
   - **Email**: test@example.com
   - **Message**: This is a test submission
3. Click "Send forespørgsel"
4. Verify green success message appears: "Tak for din henvendelse! Vi vender tilbage hurtigst muligt."

**Expected Results in `/home/blackowu/public_html/error_log`**:
```
CONTACT FORM DEBUG: entering bbx_log_contact_submission() with status=success reason=ok
CONTACT FORM DEBUG: logs directory does not exist, attempting to create: /home/blackowu/public_html/logs
CONTACT FORM DEBUG: Created log directory: /home/blackowu/public_html/logs
CONTACT FORM DEBUG: Successfully logged to: /home/blackowu/public_html/logs/contact-submissions.log (XXX bytes written)
CONTACT FORM DEBUG: mail recipient configured as: ops@blackbox.codes
CONTACT FORM MAIL DEBUG: about to send mail to ops@blackbox.codes
CONTACT FORM MAIL DEBUG: subject="Ny henvendelse fra Blackbox EYE kontaktformular"
CONTACT FORM MAIL DEBUG: from="noreply@blackbox.codes"
CONTACT FORM MAIL DEBUG: mail() dispatched successfully to ops@blackbox.codes
```

**Expected Results in `/home/blackowu/public_html/logs/contact-submissions.log`**:
```json
{"timestamp":"2025-11-20T21:XX:XX+00:00","ip":"X.X.X.X","hostname":"blackbox.codes","action":"contact","score":0.9,"success":true,"reason":"ok","name":"Test User","email":"test@example.com","phone":"","message_length":28,"has_phone":false,"expected_hostname":"blackbox.codes","mail_sent":true,"mail_recipient":"ops@blackbox.codes","api_mode":"enterprise"}
```

**Expected Results in Proton Mail**:
- New email in `ops@blackbox.codes` inbox
- Subject: "Ny henvendelse fra Blackbox EYE kontaktformular"
- Body contains: Name, Email, Message, Score, Hostname, API-mode

### Test 2: Subsequent Submission (Directory Exists)
**Objective**: Verify logging works when logs directory already exists

**Steps**:
1. Submit another form (same as Test 1 but different message)
2. Check logs

**Expected Results**:
- Should NOT see "logs directory does not exist" message
- Should see successful logging with bytes written
- New JSON line appended to contact-submissions.log
- New mail received

### Test 3: Verify Fallback Logging (Simulated Failure)
**Objective**: Verify fallback logging works when file operations fail

**Note**: This test requires temporarily making the logs directory unwritable or can be verified by reviewing the code logic.

**Expected Behavior**:
- If file_put_contents fails, error_log contains:
  ```
  CONTACT FORM LOG ERROR: Could not write to log file: /path/to/logs/contact-submissions.log
  CONTACT FORM LOG ERROR: file_put_contents() error: [error details]
  CONTACT FORM LOG FALLBACK: {JSON data}
  ```

### Test 4: reCAPTCHA Validation
**Objective**: Verify reCAPTCHA is working correctly

**Steps**:
1. Submit form normally (reCAPTCHA should pass)
2. Check logs for reCAPTCHA score and validation

**Expected Results**:
- Form accepts submission
- Logs show score (typically 0.7-0.9 for human interaction)
- action="contact", hostname matches

### Test 5: Mail Configuration Check
**Objective**: Verify mail recipient configuration

**Steps**:
1. Check that CONTACT_EMAIL environment variable is set in .htaccess
2. If not set, verify fallback to ops@blackbox.codes

**Expected Results**:
- If CONTACT_EMAIL is set: logs show configured address
- If CONTACT_EMAIL is NOT set: logs show warning + fallback to ops@blackbox.codes
- Mail always goes to correct recipient

## CI/CD Verification

### Verify Deployment Pipeline
**Objective**: Ensure CI/CD deploys the latest contact-submit.php

**Steps**:
1. Check GitHub Actions workflow run for this commit
2. Verify "🚀 Secure FTP Deploy" job completes successfully
3. Verify "🧪 Smoke Tests" job passes
4. Verify deployment timestamp in workflow logs

**Expected Results**:
- All CI/CD jobs green ✅
- contact-submit.php deployed to production
- Smoke tests pass for contact.php endpoint

### Verify File on Server
**Objective**: Confirm deployed file matches repository

**Steps** (via cPanel File Manager or SSH):
1. Navigate to `/home/blackowu/public_html/`
2. Open `contact-submit.php`
3. Verify lines 16-17 contain:
   ```php
   // Always log function entry for debugging
   error_log('CONTACT FORM DEBUG: entering bbx_log_contact_submission() with status=' . $status . ' reason=' . ($reason !== '' ? $reason : '[empty]'));
   ```
4. Verify lines 338-341 contain:
   ```php
   // Always log mail operations for debugging
   error_log('CONTACT FORM MAIL DEBUG: about to send mail to ' . $contactRecipient);
   error_log('CONTACT FORM MAIL DEBUG: subject="' . $subject . '"');
   error_log('CONTACT FORM MAIL DEBUG: from="' . $fromAddress . '"');
   ```

## Log Analysis Checklist

After running tests, verify the following in error_log:

- [ ] "CONTACT FORM DEBUG: entering bbx_log_contact_submission()" appears
- [ ] Either "Created log directory" OR successful write confirmation appears
- [ ] "CONTACT FORM DEBUG: Successfully logged to: ...log (XXX bytes written)" appears
- [ ] "CONTACT FORM DEBUG: mail recipient configured as: ops@blackbox.codes" appears
- [ ] "CONTACT FORM MAIL DEBUG: about to send mail to ops@blackbox.codes" appears
- [ ] "CONTACT FORM MAIL DEBUG: mail() dispatched successfully to ops@blackbox.codes" appears
- [ ] NO "CONTACT FORM WARNING: mail() failed" messages appear
- [ ] NO "CONTACT FORM LOG ERROR" messages appear (except in failure tests)

## Success Criteria

✅ All tests pass when:
1. **UI**: Green success message displays on form submission
2. **Logging**: 
   - logs/contact-submissions.log file exists and contains JSON lines
   - error_log shows complete trace from entry to mail dispatch
3. **Mail**: 
   - Email received in ops@blackbox.codes inbox
   - Email contains all form data
4. **CI/CD**: 
   - All GitHub Actions jobs complete successfully
   - Latest code deployed to production

## Troubleshooting

### If logs/contact-submissions.log is not created:
1. Check error_log for "CONTACT FORM LOG ERROR: mkdir() error:"
2. Verify directory permissions (parent directory must be writable)
3. Look for "CONTACT FORM LOG FALLBACK:" entries in error_log (contains the data that should have been in the file)

### If mail is not received:
1. Check error_log for "CONTACT FORM WARNING: mail() failed"
2. Verify server mail configuration (sendmail, SMTP)
3. Check spam/junk folder in Proton Mail
4. Verify ops@blackbox.codes is correct recipient
5. Check if mail() function is disabled on server

### If no logs appear at all:
1. Verify PHP error_log is configured and writable
2. Check if error_reporting is enabled
3. Verify contact-submit.php was actually deployed (check file modification date)
4. Test with a simple error_log() call at the top of contact-submit.php

## Post-Deployment Validation

After merging to main and deployment completes:

1. **Immediate Check** (within 5 minutes):
   - Submit test form
   - Verify success message
   - Check error_log for new entries

2. **Within 1 hour**:
   - Verify mail received
   - Verify logs/contact-submissions.log exists
   - Review error_log for any warnings

3. **Within 24 hours**:
   - Monitor for any real user submissions
   - Verify logging is working for production traffic
   - No error spikes in error_log

## Notes

- All logging now happens regardless of BBX_DEBUG_RECAPTCHA flag
- This ensures maximum observability in production
- Logs contain no sensitive data (passwords, tokens) but do include form submissions
- error_log will be more verbose, but this is intentional for diagnosability
- Logs directory is in .gitignore (correct - runtime-generated)

## Contact

For issues or questions about this test plan:
- Repository: AlphaAcces/ALPHA-Interface-GUI
- Agent: ALPHA-Web-Diagnostics-Agent
- Issue: Priority One - Contact Form Logging & Mail
