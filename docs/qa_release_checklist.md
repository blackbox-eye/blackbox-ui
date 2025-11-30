# QA Release Checklist

> **Last updated:** 2025-11-30  
> **Version:** 1.0

## Overview

This checklist provides manual QA steps to verify before merging PRs or releasing to production. Complete all applicable items before marking a release as ready.

---

## Pre-Release Checklist

### 1. SSO Health Checks

- [ ] **Start local servers**
  ```bash
  php -S localhost:8000 &
  php -S 127.0.0.1:8091 &
  ```

- [ ] **Run SSO health check**
  ```bash
  npm run sso:health
  ```

- [ ] **Verify output shows:**
  - ✅ GDI: OK
  - ✅ TS24: OK
  - Secret Configured: Yes
  - Uses HS256: Yes

### 2. Playwright Test Suites

- [ ] **Run full test suite**
  ```bash
  npm test
  ```

- [ ] **Check specific suites as needed:**
  ```bash
  # Agent access tests
  npm test -- tests/agent-access.spec.js
  
  # Marketing landing tests
  npm test -- tests/marketing-landing.spec.js
  
  # Graphene hero tests
  npm test -- tests/graphene-3d-hero.spec.js
  ```

- [ ] **Verify test output:**
  - expected === total
  - unexpected === 0
  - flaky tests acknowledged

### 3. i18n Verification

- [ ] **Agent Access page (`/agent-access.php`)**
  - [ ] No raw i18n keys visible (no `agent_access.` strings)
  - [ ] Hero title displays translated text
  - [ ] Hero lead text displays translated text
  - [ ] GDI card shows proper title, description, CTA
  - [ ] TS24 card shows proper title, description, CTA
  - [ ] Audit notice displays correctly

- [ ] **Test both languages:**
  - [ ] Switch to Danish: verify translations
  - [ ] Switch to English: verify translations

### 4. Mobile Responsiveness

- [ ] **Test at 320px viewport width:**
  - [ ] Hero section visible and readable
  - [ ] CTA buttons visible and not overlapping
  - [ ] Touch targets ≥ 48px height
  - [ ] Cards stack properly on small screens

- [ ] **Test at 768px viewport width (tablet):**
  - [ ] Layout adjusts appropriately
  - [ ] Navigation menu accessible

- [ ] **Test at 1280px viewport width (desktop):**
  - [ ] Console cards display side by side
  - [ ] Full navigation visible
  - [ ] Hero section properly sized

### 5. Visual Regression

- [ ] **Check for visual regressions:**
  - [ ] Hero section unchanged (unless intentional)
  - [ ] Footer consistent
  - [ ] Header layout correct
  - [ ] Colors match Graphene design system

### 6. Deployment Verification

- [ ] **Check Cloudflare Pages deploy log:**
  - [ ] Build completed successfully
  - [ ] No deployment errors
  - [ ] Preview URL accessible

- [ ] **Verify on preview/staging:**
  - [ ] Pages load without errors
  - [ ] Console (F12) shows no JavaScript errors
  - [ ] Network tab shows no failed requests

---

## Quick Verification Commands

```bash
# One-liner for basic verification
npm run sso:health && npm test -- tests/agent-access.spec.js

# Full pre-merge check
npm run sso:health && npm test
```

---

## Release Approval Criteria

A release is **ready for merge** when:

| Criterion | Status |
|-----------|--------|
| SSO health checks pass | ✅ Required |
| Playwright tests pass | ✅ Required |
| No raw i18n keys visible | ✅ Required |
| Mobile CTA buttons accessible | ✅ Required |
| Visual regression tests pass | ✅ Required |
| CodeQL (JS + PHP) passes | ✅ Required |
| Cloudflare Pages deploy succeeds | ✅ Required |

---

## Post-Release Verification

After merging to main:

- [ ] **Monitor Cloudflare Pages deployment**
- [ ] **Verify production site:**
  - [ ] https://blackbox.codes loads correctly
  - [ ] /agent-access shows translated content
  - [ ] No JavaScript console errors
- [ ] **Check for error alerts in monitoring**

---

## Troubleshooting

| Issue | Action |
|-------|--------|
| SSO health fails | Check PHP servers are running |
| Tests fail | Review test output, check for breaking changes |
| i18n keys showing | Verify JSON files have translations |
| Deploy fails | Check Cloudflare dashboard for errors |

---

## Changelog

| Date | Change | PR |
|------|--------|----|
| 2025-11-30 | Created QA release checklist | Current |
