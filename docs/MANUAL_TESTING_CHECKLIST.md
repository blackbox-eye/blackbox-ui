# Manual Testing & Verification Checklist
**Platform:** ALPHA Interface GUI (Blackbox EYE™)  
**Date:** 2025-11-24  
**Testing Version:** Post Web Optimization Sprint  
**Agent:** ALPHA-Web-Diagnostics-Agent

---

## Test Environment Setup

### Prerequisites
- [ ] Access to staging/production environment
- [ ] Modern browsers (Chrome, Firefox, Safari, Edge)
- [ ] Mobile devices or browser DevTools responsive mode
- [ ] Screen reader software (optional but recommended)
- [ ] Network throttling tools (Chrome DevTools)

### Test Accounts
- [ ] Regular user account
- [ ] Admin account (for dashboard testing)
- [ ] Test email for contact form

---

## 1. Image Lazy Loading Tests

### Desktop Testing (Chrome DevTools)

#### Test 1.1: Logo Image (agent-login.php)
```
Steps:
1. Open Chrome DevTools (F12)
2. Navigate to Network tab → Filter "Img"
3. Visit https://blackbox.codes/agent-login.php
4. Check that logo.png has "loading: lazy" in request headers

Expected Result:
✅ Logo loads with lazy loading attribute
✅ Image has explicit width="96" height="96"
✅ No layout shift when image loads

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail
```

#### Test 1.2: Blog Featured Images (blog.php)
```
Steps:
1. Open Chrome DevTools → Network tab
2. Visit https://blackbox.codes/blog.php
3. Scroll down slowly
4. Observe when images start loading

Expected Result:
✅ Images load only when scrolling into viewport
✅ Network tab shows images loading progressively
✅ No layout shift (aspect-video containers maintain space)

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail
```

#### Test 1.3: Blog Post Featured Image
```
Steps:
1. Visit any blog post (blog-post.php)
2. Check featured image at top of article

Expected Result:
✅ Featured image has loading="lazy"
✅ Image loads smoothly without layout shift

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail
```

### Mobile Testing (iOS Safari / Chrome Android)

#### Test 1.4: Mobile Lazy Loading
```
Steps:
1. Open site on mobile device (or DevTools mobile emulation)
2. Navigate to blog.php
3. Scroll slowly through blog posts

Expected Result:
✅ Images load progressively on scroll
✅ Smooth scrolling performance
✅ No janky behavior

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail
```

---

## 2. CSS/JS Minification Tests

### Desktop Testing

#### Test 2.1: Marketing CSS Loading
```
Steps:
1. Open Chrome DevTools → Network tab
2. Visit https://blackbox.codes (home page)
3. Filter by "CSS"
4. Check loaded CSS file

Expected Result:
✅ Loads marketing.min.css (NOT marketing.css)
✅ File size approximately 1.4 KB
✅ Response headers show gzip compression

Actual Result:
- File loaded: _____________
- File size: _____________
Status: ⬜ Pass  ⬜ Fail
```

#### Test 2.2: Admin CSS Loading
```
Steps:
1. Open Chrome DevTools → Network tab
2. Visit https://blackbox.codes/dashboard.php (requires login)
3. Filter by "CSS"
4. Check loaded CSS file

Expected Result:
✅ Loads admin.min.css (NOT admin.css)
✅ File size approximately 2.6 KB
✅ Gzip compression active

Actual Result:
- File loaded: _____________
- File size: _____________
Status: ⬜ Pass  ⬜ Fail
```

#### Test 2.3: JavaScript Loading
```
Steps:
1. Open Chrome DevTools → Network tab
2. Visit any page
3. Filter by "JS"
4. Check site.min.js

Expected Result:
✅ Loads site.min.js (NOT site.js)
✅ File size approximately 18 KB
✅ Gzip/Brotli compression active

Actual Result:
- File loaded: _____________
- File size: _____________
Status: ⬜ Pass  ⬜ Fail
```

### Performance Impact

#### Test 2.4: Page Load Speed
```
Steps:
1. Open Chrome DevTools → Lighthouse
2. Run Performance audit on homepage
3. Check metrics

Expected Result:
✅ Performance score ≥ 85
✅ LCP < 2.5s
✅ FID < 100ms
✅ CLS < 0.1

Actual Result:
- Performance score: _____________
- LCP: _____________
- FID: _____________
- CLS: _____________
Status: ⬜ Pass  ⬜ Fail
```

---

## 3. CDN Security & SRI Tests

### Chart.js Testing (Dashboard)

#### Test 3.1: Chart.js Version & Crossorigin
```
Steps:
1. Open Chrome DevTools → Network tab
2. Visit https://blackbox.codes/dashboard.php
3. Filter by "JS"
4. Check Chart.js request

Expected Result:
✅ Loads chart.js@4.4.1 (specific version)
✅ Has crossorigin="anonymous" attribute
✅ Charts render correctly on dashboard

Actual Result:
- Version: _____________
- Crossorigin: _____________
- Charts working: _____________
Status: ⬜ Pass  ⬜ Fail
```

### Calendly Testing (Demo Page)

#### Test 3.2: Calendly Crossorigin
```
Steps:
1. Open Chrome DevTools → Network tab
2. Visit https://blackbox.codes/demo.php
3. Check widget.css and widget.js

Expected Result:
✅ Both have crossorigin="anonymous"
✅ Calendly widget loads and functions
✅ No CORS errors in console

Actual Result:
- Crossorigin present: _____________
- Widget functional: _____________
- Console errors: _____________
Status: ⬜ Pass  ⬜ Fail
```

### CSP Headers

#### Test 3.3: Content Security Policy
```
Steps:
1. Open Chrome DevTools → Network tab
2. Visit any page
3. Check Response Headers for CSP

Expected Result:
✅ Content-Security-Policy header present
✅ Includes approved CDN domains
✅ No CSP violations in console

Actual Result:
- CSP header present: _____________
- Console violations: _____________
Status: ⬜ Pass  ⬜ Fail
```

---

## 4. Accessibility (WCAG 2.1 AA+) Tests

### Contact Form Testing

#### Test 4.1: Keyboard Navigation
```
Steps:
1. Visit https://blackbox.codes/contact.php
2. Use only keyboard (Tab, Enter, Esc)
3. Navigate through form

Expected Result:
✅ Tab moves through all form fields
✅ Focus indicators visible on all elements
✅ Submit button activates with Enter
✅ Can complete entire form with keyboard only

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail
```

#### Test 4.2: ARIA Labels & Autocomplete
```
Steps:
1. Open Chrome DevTools → Elements
2. Visit contact.php
3. Inspect form elements

Expected Result:
✅ Form has aria-label
✅ Required fields have aria-required="true"
✅ Name field has autocomplete="name"
✅ Email field has autocomplete="email"
✅ Phone field has autocomplete="tel"

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail
```

#### Test 4.3: Error/Success Messages
```
Steps:
1. Visit contact.php
2. Submit form with invalid data
3. Submit form with valid data

Expected Result:
✅ Error message has role="alert"
✅ Success message has role="status"
✅ Both have aria-live="polite"
✅ Screen readers announce messages

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail
```

### Navigation Testing

#### Test 4.4: Skip Link
```
Steps:
1. Visit homepage
2. Press Tab key once (before any other interaction)
3. Check for skip link

Expected Result:
✅ Skip link appears at top of page
✅ Text: "Skip til hovedindhold" (Danish) or equivalent
✅ Pressing Enter jumps to main content

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail
```

#### Test 4.5: ARIA Current Page
```
Steps:
1. Visit https://blackbox.codes/products.php
2. Inspect navigation menu

Expected Result:
✅ Products link has aria-current="page"
✅ Visual indicator shows current page
✅ Screen reader announces current page

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail
```

#### Test 4.6: Mobile Menu
```
Steps:
1. Resize browser to mobile width (< 768px)
2. Click hamburger menu button
3. Test keyboard navigation

Expected Result:
✅ Menu button has aria-expanded attribute
✅ Menu button has aria-controls="mobile-menu"
✅ Pressing Escape closes menu
✅ Focus trapped within open menu

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail
```

### Color Contrast

#### Test 4.7: Text Contrast Ratios
```
Steps:
1. Open Chrome DevTools → Lighthouse
2. Run Accessibility audit
3. Check contrast issues

Expected Result:
✅ No contrast issues reported
✅ High emphasis text: 13.5:1 ratio
✅ Medium emphasis text: 4.52:1 ratio
✅ Accent text: 10.8:1 ratio

Actual Result:
- Contrast issues: _____________
- Lighthouse score: _____________
Status: ⬜ Pass  ⬜ Fail
```

### Screen Reader Testing (Optional)

#### Test 4.8: NVDA/JAWS (Windows)
```
Steps:
1. Enable NVDA or JAWS
2. Navigate contact form
3. Test form submission

Expected Result:
✅ All labels read correctly
✅ Required fields announced
✅ Error messages announced
✅ Success message announced

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail  ⬜ Not Tested
```

#### Test 4.9: VoiceOver (macOS/iOS)
```
Steps:
1. Enable VoiceOver (Cmd+F5)
2. Navigate site with VoiceOver
3. Test form interaction

Expected Result:
✅ Navigation announced correctly
✅ Form fields labeled properly
✅ Buttons described accurately
✅ Status messages announced

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail  ⬜ Not Tested
```

---

## 5. HTTP/2 & Compression Tests

#### Test 5.1: HTTP/2 Protocol
```
Steps:
1. Open terminal
2. Run: curl -I --http2 https://blackbox.codes
3. Check protocol in response

Expected Result:
✅ Response shows HTTP/2 or HTTP/2.0
✅ Connection multiplexing active
✅ Header compression (HPACK)

Actual Result:
- Protocol: _____________
Status: ⬜ Pass  ⬜ Fail
```

#### Test 5.2: Gzip Compression
```
Steps:
1. Open Chrome DevTools → Network
2. Check any CSS/JS file
3. Look at Response Headers

Expected Result:
✅ Content-Encoding: gzip or br (Brotli)
✅ Original size vs transferred size shows compression
✅ Typical compression: 60-80% reduction

Actual Result:
- Content-Encoding: _____________
- Compression ratio: _____________
Status: ⬜ Pass  ⬜ Fail
```

#### Test 5.3: Browser Caching
```
Steps:
1. Open Chrome DevTools → Network
2. Visit homepage
3. Refresh page (Ctrl+R)
4. Check if assets loaded from cache

Expected Result:
✅ CSS cached for 1 month (2592000 seconds)
✅ JS cached for 1 month
✅ Images cached for 1 year
✅ 304 Not Modified responses on refresh

Actual Result:
- CSS cache: _____________
- JS cache: _____________
- Image cache: _____________
Status: ⬜ Pass  ⬜ Fail
```

---

## 6. Cross-Browser Testing

### Desktop Browsers

#### Test 6.1: Google Chrome
```
Browser Version: _____________
Features Tested:
- [ ] Lazy loading works
- [ ] Minified assets load
- [ ] Accessibility features work
- [ ] No console errors
- [ ] Performance acceptable

Status: ⬜ Pass  ⬜ Fail
Notes: _____________
```

#### Test 6.2: Mozilla Firefox
```
Browser Version: _____________
Features Tested:
- [ ] Lazy loading works
- [ ] Minified assets load
- [ ] Accessibility features work
- [ ] No console errors
- [ ] Performance acceptable

Status: ⬜ Pass  ⬜ Fail
Notes: _____________
```

#### Test 6.3: Safari
```
Browser Version: _____________
Features Tested:
- [ ] Lazy loading works
- [ ] Minified assets load
- [ ] Accessibility features work
- [ ] No console errors
- [ ] Performance acceptable

Status: ⬜ Pass  ⬜ Fail
Notes: _____________
```

#### Test 6.4: Microsoft Edge
```
Browser Version: _____________
Features Tested:
- [ ] Lazy loading works
- [ ] Minified assets load
- [ ] Accessibility features work
- [ ] No console errors
- [ ] Performance acceptable

Status: ⬜ Pass  ⬜ Fail
Notes: _____________
```

### Mobile Browsers

#### Test 6.5: iOS Safari
```
iOS Version: _____________
Features Tested:
- [ ] Lazy loading on scroll
- [ ] Touch navigation works
- [ ] Mobile menu functional
- [ ] Text readable (no zoom needed)
- [ ] Forms work correctly

Status: ⬜ Pass  ⬜ Fail
Notes: _____________
```

#### Test 6.6: Chrome Android
```
Android Version: _____________
Features Tested:
- [ ] Lazy loading on scroll
- [ ] Touch navigation works
- [ ] Mobile menu functional
- [ ] Text readable (no zoom needed)
- [ ] Forms work correctly

Status: ⬜ Pass  ⬜ Fail
Notes: _____________
```

---

## 7. Performance Regression Tests

#### Test 7.1: Lighthouse Performance
```
Test Environment: Desktop (simulated)
URL: https://blackbox.codes

Before Optimization (baseline):
- Performance: ~75
- Accessibility: ~85
- Best Practices: ~80
- SEO: ~90

After Optimization:
- Performance: _____________
- Accessibility: _____________
- Best Practices: _____________
- SEO: _____________

Improvement:
- Performance: _____________ (+/-)
- Accessibility: _____________ (+/-)

Status: ⬜ Pass (improved)  ⬜ Fail (regressed)
```

#### Test 7.2: PageSpeed Insights
```
URL: https://blackbox.codes

Mobile Score:
- Performance: _____________
- Accessibility: _____________
- Best Practices: _____________
- SEO: _____________

Desktop Score:
- Performance: _____________
- Accessibility: _____________
- Best Practices: _____________
- SEO: _____________

Status: ⬜ Pass  ⬜ Fail
```

#### Test 7.3: Core Web Vitals
```
Metrics from Chrome DevTools or PageSpeed Insights:

LCP (Largest Contentful Paint):
- Target: < 2.5s
- Actual: _____________
- Status: ⬜ Pass  ⬜ Fail

FID (First Input Delay):
- Target: < 100ms
- Actual: _____________
- Status: ⬜ Pass  ⬜ Fail

CLS (Cumulative Layout Shift):
- Target: < 0.1
- Actual: _____________
- Status: ⬜ Pass  ⬜ Fail
```

---

## 8. Regression Testing (Existing Features)

#### Test 8.1: Contact Form Submission
```
Steps:
1. Fill out contact form with valid data
2. Submit form
3. Check email received

Expected Result:
✅ Form submits successfully
✅ Success message displayed
✅ Email received at ops@blackbox.codes
✅ reCAPTCHA validation works

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail
```

#### Test 8.2: AlphaBot Widget
```
Steps:
1. Visit homepage
2. Click AlphaBot toggle
3. Send test message

Expected Result:
✅ Widget opens/closes smoothly
✅ Messages send and receive
✅ AI responses work correctly
✅ No JavaScript errors

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail
```

#### Test 8.3: Language Switching
```
Steps:
1. Visit homepage
2. Click "EN" in language switcher
3. Verify page content changes
4. Click "DA" to switch back

Expected Result:
✅ Language switches correctly
✅ Content updates in chosen language
✅ Language preference saved

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail
```

#### Test 8.4: Dashboard Charts
```
Steps:
1. Login to dashboard
2. Verify charts display correctly

Expected Result:
✅ Chart.js loads from CDN
✅ Charts render without errors
✅ Data displays accurately
✅ No console errors

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail
```

---

## 9. Security Testing

#### Test 9.1: HTTPS Enforcement
```
Steps:
1. Try accessing http://blackbox.codes
2. Verify redirect to https://

Expected Result:
✅ HTTP redirects to HTTPS (301)
✅ HSTS header present
✅ Secure connection established

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail
```

#### Test 9.2: Security Headers
```
Steps:
1. Open DevTools → Network
2. Check Response Headers
3. Verify security headers present

Expected Result:
✅ X-Frame-Options: SAMEORIGIN
✅ X-Content-Type-Options: nosniff
✅ X-XSS-Protection: 1; mode=block
✅ Referrer-Policy: strict-origin-when-cross-origin
✅ Content-Security-Policy present

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail
```

#### Test 9.3: XSS Prevention
```
Steps:
1. Try submitting form with <script> tag
2. Verify input sanitization

Expected Result:
✅ Script tags escaped/removed
✅ No script execution
✅ Safe error handling

Actual Result: _____________
Status: ⬜ Pass  ⬜ Fail
```

---

## 10. Final Validation

### Test Summary

| Category | Tests Passed | Tests Failed | Not Tested | Total |
|----------|--------------|--------------|------------|-------|
| Lazy Loading | ___ | ___ | ___ | 4 |
| Minification | ___ | ___ | ___ | 4 |
| CDN Security | ___ | ___ | ___ | 3 |
| Accessibility | ___ | ___ | ___ | 9 |
| HTTP/2 | ___ | ___ | ___ | 3 |
| Cross-Browser | ___ | ___ | ___ | 6 |
| Performance | ___ | ___ | ___ | 3 |
| Regression | ___ | ___ | ___ | 4 |
| Security | ___ | ___ | ___ | 3 |
| **TOTAL** | **___** | **___** | **___** | **39** |

### Overall Status
- ⬜ All Critical Tests Passed (ready for production)
- ⬜ Minor Issues Found (document and fix)
- ⬜ Major Issues Found (rollback and fix)

### Issues Discovered
```
Issue 1:
- Description: _____________
- Severity: ⬜ Critical  ⬜ High  ⬜ Medium  ⬜ Low
- Action: _____________

Issue 2:
- Description: _____________
- Severity: ⬜ Critical  ⬜ High  ⬜ Medium  ⬜ Low
- Action: _____________
```

---

## Sign-off

### Tester Information
- **Name:** _____________
- **Date:** _____________
- **Time Spent:** _____________
- **Environment:** _____________

### Approval
- [ ] All critical tests passed
- [ ] Issues documented and assigned
- [ ] Ready for production deployment

**Signature:** _____________  
**Date:** _____________

---

## Appendix: Quick Reference

### Common DevTools Shortcuts
- **Open DevTools:** F12 or Cmd+Option+I (Mac)
- **Network Tab:** Ctrl+Shift+E / Cmd+Option+E
- **Console:** Ctrl+Shift+J / Cmd+Option+J
- **Performance:** Ctrl+Shift+E then Performance tab

### Testing URLs
- Homepage: https://blackbox.codes
- Contact: https://blackbox.codes/contact.php
- Blog: https://blackbox.codes/blog.php
- Dashboard: https://blackbox.codes/dashboard.php (requires login)
- Demo: https://blackbox.codes/demo.php

### Expected File Sizes (Minified)
- marketing.min.css: ~1.4 KB
- admin.min.css: ~2.6 KB
- site.min.js: ~18 KB
- logo.png: ~375 KB

### Contact for Issues
- **Email:** ops@blackbox.codes
- **Team:** ALPHA Web Diagnostics
- **Documentation:** See WEB_DIAGNOSTICS_REPORT.md

---

**Version:** 1.0.0  
**Last Updated:** 2025-11-24

