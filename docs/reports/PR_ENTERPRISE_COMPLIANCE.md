# 🚀 Enterprise Compliance & Security Hardening

## PR Summary
This PR implements enterprise-grade compliance features for contact flow, cookie consent, and security disclosure without modifying the existing header/hero/primary navigation files.

---

## 📋 Changes Overview

### 🔐 Enhanced Contact Flow

**`contact-submit.php`**
- Added honeypot field detection (`website_url`) for bot prevention
- Silent rejection with fake success response (prevents bot adaptation)
- Logs honeypot triggers with user-agent for analysis
- No UX changes for legitimate users

**`contact.php`**
- Added hidden honeypot field with CSS `display:none`
- Added `maxlength` attributes to all form fields (validation hardening)
- Added PGP public key section for encrypted communications
- Preserved all existing styling and functionality

---

### 🍪 Cookie Consent (GDPR/ePrivacy)

**NEW: `includes/cookie-banner.php`**
- GDPR-compliant cookie consent banner
- Three options: Accept All, Decline, Customize
- `localStorage` persistence (no tracking cookies needed for consent)
- `sendBeacon` API for consent logging (non-blocking)
- Self-contained CSS/JS (no modifications to forbidden files)
- Respects existing theme (works in dark/light mode)

**NEW: `api/consent-log.php`**
- Receives consent events via `sendBeacon`
- Logs: action (accept/decline/customize), timestamp, user-agent hash
- No PII stored, audit-compliant JSON logging

---

### 📄 Legal Pages

**NEW: `privacy.php`**
- Complete privacy policy page
- GDPR Article 13/14 compliant information
- Sections: Data processing, cookies, contact form data, retention, rights
- Uses existing site styling (site-header.php/site-footer.php)

**NEW: `terms.php`**
- Full terms of service / user agreement
- Danish legal compliance (governing law: Denmark)
- Sections: Service description, usage rights, IP, confidentiality, liability, termination

---

### 🛡️ Security Disclosure

**NEW: `.well-known/security.txt`**
- RFC 9116 compliant security policy
- Contact emails: security@blackbox.codes, disclosure@blackbox.codes
- PGP key URL reference
- Preferred languages: Danish, English

**NEW: `.well-known/pgp-key.asc`**
- Placeholder PGP public key file
- Instructions for generating real key
- To be replaced with actual 4096-bit RSA key before production

---

### 📝 Enhanced Logging

**`includes/logging.php`**
- Complete rewrite with enterprise logging features
- Structured JSON logging with ISO 8601 timestamps
- Log levels: DEBUG, INFO, WARN, ERROR, CRITICAL
- Correlation IDs for request tracing
- Automatic log rotation (size-based + file count limits)
- Specialized functions:
  - `bbx_log_contact_event()` - contact form submissions
  - `bbx_log_consent_event()` - cookie consent actions
  - `bbx_log_security_event()` - security incidents

---

### 🌐 i18n Updates

**`lang/da.json`** + **`lang/en.json`**
- Added footer translations:
  - `footer.privacy` - "Privatlivspolitik" / "Privacy Policy"
  - `footer.terms` - "Vilkår" / "Terms of Service"
  - `footer.recaptcha_notice` - reCAPTCHA notice text

---

### 🦶 Footer Updates

**`includes/site-footer.php`**
- Added privacy policy link
- Added terms of service link
- Included cookie-banner.php component
- Preserved all existing styling

---

## 🚫 Files NOT Modified (as requested)

- ❌ `includes/site-header.php`
- ❌ `assets/css/custom-ui.css`
- ❌ `assets/js/site.js` / `site.min.js`
- ❌ `index.php`
- ❌ `cases.php`
- ❌ `pricing.php`
- ❌ `demo.php`

---

## ✅ Testing Checklist

- [ ] Cookie banner appears on first visit
- [ ] Cookie banner respects Accept/Decline choice
- [ ] Cookie banner persists across page reloads
- [ ] Contact form submission works normally
- [ ] Honeypot rejects bot submissions silently
- [ ] Privacy page renders correctly
- [ ] Terms page renders correctly
- [ ] Footer links work in both languages
- [ ] `.well-known/security.txt` accessible
- [ ] Logging functions write to `logs/` directory
- [ ] Works in both dark and light themes

---

## 🔜 Post-Merge Tasks

1. **Generate real PGP key** and replace `.well-known/pgp-key.asc`
2. **Update CVR number** in `terms.php`
3. **Configure DMARC/SPF** for security@blackbox.codes
4. **Register** key on keyserver.ubuntu.com
5. **Verify** cookie banner in production environment

---

## Commit Message Suggestion

```
feat(compliance): enterprise cookie consent, privacy policy, security.txt

- Add GDPR-compliant cookie consent banner with accept/decline/customize
- Add privacy policy page (privacy.php) with data processing details
- Add terms of service page (terms.php) with legal framework
- Add .well-known/security.txt for vulnerability disclosure
- Add honeypot spam protection to contact form
- Enhance logging.php with enterprise features (rotation, correlation IDs)
- Add footer links for privacy/terms pages
- Add i18n translations for new footer elements

No changes to header/hero/navigation files as per requirements.
```
