# Sprint 3 Test Plan - Internationalization & Pricing Updates
**Version:** 1.0  
**Date:** November 22, 2025  
**Sprint:** Sprint 3 (i18n + Pricing Validation)  
**Branch:** feat/sprint3-i18n-pricing

---

## 📋 Test Overview

### Objectives
- Validate internationalization (i18n) system with Danish/English language switching
- Verify pricing data accuracy across all 6 tiers
- Confirm language persistence via PHP sessions
- Test UI consistency across both languages
- Validate CTA behavior (20% scroll threshold from Sprint 2)

### Scope
**In Scope:**
- Language switcher (DA/EN toggle in header)
- Translation accuracy (navigation, pricing, headings)
- Session-based language persistence
- Browser language detection fallback
- Pricing data integrity (1.799, 3.499, 5.999, 9.900, 18.900, 39.000 DKK)
- User limits per tier (10, 25, 50, 100, 250, unlimited)
- Subscription terms and support levels

**Out of Scope:**
- Database translation storage
- Admin panel language management
- Email template translations
- Real-time translation API integration

---

## 🧪 Test Categories

### 1. Language Switching Tests

| Test ID | Test Case | Expected Result | Priority | Status |
|---------|-----------|-----------------|----------|--------|
| L01 | Click DA button in header | Page reloads in Danish, DA button highlighted | HIGH | ⏳ |
| L02 | Click EN button in header | Page reloads in English, EN button highlighted | HIGH | ⏳ |
| L03 | Switch DA→EN→DA | Language persists across page navigations | HIGH | ⏳ |
| L04 | Test with Danish browser locale | Auto-detects Danish on first visit | MEDIUM | ⏳ |
| L05 | Test with English browser locale | Auto-detects English on first visit | MEDIUM | ⏳ |
| L06 | Refresh page after language switch | Language remains unchanged (session persistence) | HIGH | ⏳ |
| L07 | Clear session/cookies and reload | Defaults to Danish (fallback) | LOW | ⏳ |
| L08 | Mobile viewport language switcher | Toggle visible and functional on mobile | HIGH | ⏳ |

### 2. Translation Accuracy Tests

| Test ID | Component | Danish Text | English Text | Status |
|---------|-----------|-------------|--------------|--------|
| T01 | Header Menu - About | "Om Os" | "About" | ⏳ |
| T02 | Header Menu - Products | "Produkter" | "Products" | ⏳ |
| T03 | Header Menu - Cases | "Kundecases" | "Case Studies" | ⏳ |
| T04 | Header Menu - Pricing | "Priser" | "Pricing" | ⏳ |
| T05 | Header Menu - Contact | "Kontakt" | "Contact" | ⏳ |
| T06 | Header CTA | "Agent Login" | "Agent Login" | ⏳ |
| T07 | Pricing Hero | "Licenser & abonnementer" | "Licenses & Subscriptions" | ⏳ |
| T08 | Pricing Intro | "Løsninger tilpasset jeres risikoprofil" | "Solutions tailored to your risk profile" | ⏳ |
| T09 | AI Advisor Title | "AI Sikkerhedsrådgiver" | "AI Security Advisor" | ⏳ |
| T10 | AI Advisor Button | "✨ Få AI-anbefaling" | "✨ Get AI Recommendation" | ⏳ |
| T11 | Enterprise Section | "Enterprise Løsninger" | "Enterprise Solutions" | ⏳ |
| T12 | Custom CTA | "Tal med vores rådgivere" | "Talk to Our Advisors" | ⏳ |

### 3. Pricing Data Validation Tests

| Test ID | Package | Price (DKK) | Users | Support | Subscription | Status |
|---------|---------|-------------|-------|---------|--------------|--------|
| P01 | MVP-Basis | 1.799 | 10 | Email (weekdays) | Monthly, no commitment | ⏳ |
| P02 | MVP-Pro | 3.499 | 25 | Chat (9-17 weekdays) | Monthly, 3 months notice | ⏳ |
| P03 | MVP-Premium | 5.999 | 50 | Priority support | Monthly, 6 months notice | ⏳ |
| P04 | Standard | 9.900 | 100 | Email & chat | Annual, quarterly billing | ⏳ |
| P05 | Premium | 18.900 | 250 | Priority 24/5 | Annual, monthly billing | ⏳ |
| P06 | Enterprise | 39.000 | Unlimited | VIP 24/7 + SLA | Annual, flexible billing | ⏳ |

### 4. Session Persistence Tests

| Test ID | Test Case | Expected Result | Priority | Status |
|---------|-----------|-----------------|----------|--------|
| S01 | Set language to EN, navigate to About | English text displayed on About page | HIGH | ⏳ |
| S02 | Set language to EN, navigate to Pricing | English pricing labels displayed | HIGH | ⏳ |
| S03 | Set language to DA, open new tab | Danish text in new tab (same session) | MEDIUM | ⏳ |
| S04 | Language switch, close browser, reopen | Language resets to browser default | LOW | ⏳ |

### 5. UI Consistency Tests

| Test ID | Test Case | Expected Result | Priority | Status |
|---------|-----------|-----------------|----------|--------|
| U01 | Language switcher visual state | Active language has amber background | HIGH | ⏳ |
| U02 | Navigation alignment (DA/EN) | Text alignment consistent across languages | MEDIUM | ⏳ |
| U03 | Pricing card heights (EN) | All cards same height despite longer English text | HIGH | ⏳ |
| U04 | Mobile menu translation | Mobile nav shows translated menu items | HIGH | ⏳ |
| U05 | Breadcrumb translation | Breadcrumbs display translated page names | MEDIUM | ⏳ |

### 6. Regression Tests (Sprint 2 Features)

| Test ID | Feature | Expected Result | Status |
|---------|---------|-----------------|--------|
| R01 | Sticky CTA visibility | Appears after 20% scroll (not 50%) | ⏳ |
| R02 | AlphaBot command rail | No overlap with content on any page | ⏳ |
| R03 | Page spacing | pt-16 spacing (not pt-24) on all pages | ⏳ |
| R04 | Mobile responsiveness | All features work on mobile/tablet/desktop | ⏳ |

---

## 🔧 Test Environment

### Required Setup
```bash
# Local development server
php -S localhost:8000

# Test URLs
http://localhost:8000/pricing.php?lang=da
http://localhost:8000/pricing.php?lang=en
http://localhost:8000/about.php
```

### Browser Testing Matrix
- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Edge (latest)
- ✅ Safari (iOS/macOS)

### Device Testing
- Desktop (1920x1080, 1440x900)
- Tablet (iPad, 768x1024)
- Mobile (iPhone, 375x667)

---

## 📊 Test Execution Checklist

### Pre-Test Setup
- [ ] Clear browser cache and cookies
- [ ] Verify feat/sprint3-i18n-pricing branch is checked out
- [ ] Confirm local PHP server running on port 8000
- [ ] Check lang/da.json and lang/en.json files exist

### Test Execution Steps
1. **Language Switching**
   - [ ] Test DA→EN switch on all pages
   - [ ] Test EN→DA switch on all pages
   - [ ] Verify visual toggle state
   - [ ] Check mobile viewport toggle

2. **Translation Validation**
   - [ ] Compare all navigation labels DA/EN
   - [ ] Check pricing page headers DA/EN
   - [ ] Verify AI advisor text DA/EN
   - [ ] Test CTA button text DA/EN

3. **Pricing Accuracy**
   - [ ] Verify all 6 package prices match specification
   - [ ] Check user limits (10, 25, 50, 100, 250, unlimited)
   - [ ] Validate subscription terms
   - [ ] Confirm support levels

4. **Session Persistence**
   - [ ] Set language, navigate between pages
   - [ ] Refresh browser, verify language
   - [ ] Open new tab, check language
   - [ ] Clear session, verify fallback

5. **Regression Testing**
   - [ ] Verify Sticky CTA at 20% scroll
   - [ ] Check AlphaBot positioning
   - [ ] Confirm pt-16 spacing
   - [ ] Test mobile responsiveness

---

## 🐛 Bug Report Template

```markdown
**Bug ID:** SPRINT3-XXX
**Severity:** Critical / High / Medium / Low
**Component:** Language Switcher / Translation / Pricing / Session
**Description:** [What went wrong]
**Steps to Reproduce:**
1. Navigate to [page]
2. Click [element]
3. Observe [issue]
**Expected:** [What should happen]
**Actual:** [What actually happened]
**Screenshot:** [Attach if applicable]
**Browser:** Chrome 120 / Firefox 121 / etc.
**Device:** Desktop / Mobile / Tablet
```

---

## ✅ Test Sign-Off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Developer | GitHub Copilot | 2025-11-22 | ✓ |
| QA Lead | [Pending] | [Pending] | [Pending] |
| Product Owner | [Pending] | [Pending] | [Pending] |

---

## 📝 Notes

- All pricing amounts are **excluding VAT** (+ moms / + VAT)
- Language fallback order: Session → Browser → Default (Danish)
- Translation keys use dot notation (e.g., `pricing.mvp.basis.title`)
- Session expires when browser closes (session cookie)
- Enterprise pricing marked with asterisk: "39.000 DKK*" (*Or by arrangement)

---

**Test Execution Date:** [To be filled]  
**Test Duration:** [Estimated 2-3 hours]  
**Pass Rate Target:** 95% (38/40 tests must pass)
