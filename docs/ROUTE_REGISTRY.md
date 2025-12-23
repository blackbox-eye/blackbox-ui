# Route Registry — Blackbox EYE™ UI

> **Version:** 1.1 (Phase 1 QA v1.1)  
> **Updated:** 2025-12-23  
> **Chief QA Officer:** ALPHA-QA-Agent

---

## 1. Overview

This document provides a comprehensive route registry for the `blackbox-eye/blackbox-ui` repository, categorizing all pages by tier priority, dependencies, and QA requirements.

### Tier System

| Tier | Priority | Description | QA Coverage |
|------|----------|-------------|-------------|
| **A** | Critical | Public marketing, homepage, key conversion pages | 100% — all viewport breakpoints, light/dark, DA/EN |
| **B** | High | Agent portal, login flows, consoles | 100% — desktop + mobile, auth flows |
| **C** | Medium | Admin panel, internal tools | Desktop-focused, auth-only |
| **D** | Low | API endpoints, scripts, utilities | Unit tests only |

---

## 2. Tier A — Critical Public Pages

These pages drive traffic and conversions. Must pass all QA gates (G0–G5).

| URL | File | Description | CSS Bundle | Auth |
|-----|------|-------------|------------|------|
| `/` | `index.php` | Homepage with 3D graphene hero, primary CTAs | marketing.min.css | ❌ |
| `/about.php` | `about.php` | Company mission, team, values | marketing.min.css | ❌ |
| `/products.php` | `products.php` | Product modules (PVE, EYE, IDMatrix, AUT) | marketing.min.css | ❌ |
| `/cases.php` | `cases.php` | Case studies with metrics | marketing.min.css | ❌ |
| `/pricing.php` | `pricing.php` | Pricing calculator with AI advisor | marketing.min.css | ❌ |
| `/contact.php` | `contact.php` | Contact form with reCAPTCHA v3 | marketing.min.css | ❌ |
| `/demo.php` | `demo.php` | Demo booking (Calendly integration) | marketing.min.css | ❌ |
| `/free-scan.php` | `free-scan.php` | Free vulnerability scan form | marketing.min.css | ❌ |

### Tier A Include Stack

```
includes/site-header.php
  ├── includes/env.php
  ├── includes/i18n.php
  ├── includes/graphene-config.php
  └── <head>
      ├── tailwind.full.css
      ├── tokens.css
      ├── custom-ui.css
      ├── theme-overrides.css
      ├── components/motion-safe.css
      ├── components/hero-mobile.css (≤768px)
      ├── components/mobile-nav-scale.css
      └── marketing.min.css
includes/site-footer.php
  └── assets/js/site.min.js
```

---

## 3. Tier B — Agent Portal Pages

Authentication-required pages for agents and operators.

| URL | File | Description | CSS Bundle | Auth |
|-----|------|-------------|------------|------|
| `/agent-access.php` | `agent-access.php` | Console selector hub (GDI, CCS, Intel24) | marketing.min.css + console-selector-mobile.css | JWT (optional) |
| `/gdi-login.php` | `gdi-login.php` | GDI login with MFA (password + PIN + token) | Custom standalone | ❌ |
| `/ccs-login.php` | `ccs-login.php` | CCS login portal (hexagon pattern) | ccs-login.css | ❌ |
| `/ccs-console.php` | `ccs-console.php` | CCS operational console | marketing.min.css + inline | Session |
| `/faq.php` | `faq.php` | FAQ with AI search | marketing.min.css | ❌ |
| `/blog.php` | `blog.php` | Blog listing with categories | marketing.min.css | ❌ |

### Tier B Component Includes

| Component | File | Purpose |
|-----------|------|---------|
| Console Selector | `includes/console-selector.php` | Reusable console card component |
| MFA Modal | `includes/mfa-modal.php` | Multi-factor auth step flow |
| Login Card | `includes/login-card-modular.php` | Modular login form with MFA support |
| JWT Helper | `includes/jwt_helper.php` | Token validation for SSO |
| SSO Audit | `includes/sso_audit.php` | Audit logging for SSO events |

---

## 4. Tier C — Admin Panel Pages

Internal administration requiring session authentication.

| URL | File | Description | CSS Bundle | Auth | Admin Only |
|-----|------|-------------|------------|------|------------|
| `/dashboard.php` | `dashboard.php` | Command center with alerts | admin.min.css | ✅ | ❌ |
| `/admin.php` | `admin.php` | User/agent management | admin.min.css | ✅ | ✅ |
| `/settings.php` | `settings.php` | Personal settings (PIN, ghost mode) | admin.min.css | ✅ | ❌ |
| `/api-keys.php` | `api-keys.php` | API key management | admin.min.css | ✅ | ❌ |
| `/intel-vault.php` | `intel-vault.php` | Encrypted document vault | admin.min.css | ✅ | ❌ |

### Tier C Layout Stack

```
includes/admin-layout.php
  ├── includes/env.php
  ├── includes/header.php (simplified)
  └── <head>
      ├── admin.min.css
      └── qa-mode.css (if QA mode)
includes/admin-footer.php
```

---

## 5. Tier D — API & Utilities

Backend endpoints and scripts. Not user-facing.

| URL | File | Description | Response |
|-----|------|-------------|----------|
| `/api/contact-submit.php` | `api/contact-submit.php` | Contact form handler | JSON |
| `/api/scan-submit.php` | `api/scan-submit.php` | Free scan handler | JSON |
| `/api/faq-search.php` | `api/faq-search.php` | FAQ search endpoint | JSON |
| `/logout.php` | `logout.php` | Session termination | Redirect |
| `/sitemap.php` | `sitemap.php` | Dynamic XML sitemap | XML |

---

## 6. CSS Load Order

Critical for cascade correctness. Order matters!

### Marketing Pages

1. `tailwind.full.css` — Base Tailwind utilities
2. `tokens.css` — Design tokens (colors, spacing, focus rings)
3. `custom-ui.css` — Component styles (buttons, cards, modals)
4. `theme-overrides.css` — Light/dark theme adjustments
5. `components/motion-safe.css` — `prefers-reduced-motion` support
6. `components/hero-mobile.css` — Hero sections (≤768px only)
7. `components/mobile-nav-scale.css` — Nav link scaling, touch targets
8. `marketing.min.css` — Marketing-specific styles

### Admin Pages

1. `admin.min.css` — Self-contained admin styles
2. `qa-mode.css` — QA overlay (conditional)

---

## 7. Mobile Responsiveness Standard (v1.1)

### Current State Analysis

**Viewport Meta Tag:** `width=device-width, initial-scale=1.0`

**Breakpoints (Tailwind):**
- `xs`: 480px
- `sm`: 640px
- `md`: 768px
- `lg`: 1024px
- `xl`: 1280px

### Mobile Zoom Recommendation (75–80% Scale Effect)

**Goal:** Make UI elements ~20% larger on mobile without affecting desktop.

**Implementation Strategy:**

```css
/* tokens.css or tailwind.config.js */
@media (max-width: 768px) {
  :root {
    /* Increase base font size for mobile */
    font-size: 112.5%; /* 18px base instead of 16px = ~12% larger */
  }
  
  /* Alternatively, adjust container padding */
  .container {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
  }
}
```

**Affected Files:**
- `tailwind.config.js` — Container padding adjustments
- `assets/css/tokens.css` — Base `font-size` on `:root`
- `assets/css/components/hero-mobile.css` — Already uses `clamp()`
- `assets/css/components/mobile-nav-scale.css` — +20% nav link scaling

**Current Implementation Status:**
- ✅ `hero-mobile.css` uses `clamp()` for responsive font sizing
- ✅ `mobile-nav-scale.css` has 52px touch targets (WCAG compliant)
- ⚠️ Base `font-size` not adjusted for mobile
- ⚠️ Container padding tight on small screens

---

## 8. QA Gates (G0–G5)

### G0 — Build Gate

| Check | Requirement | Tool |
|-------|-------------|------|
| CSS Build | `npm run build:css` succeeds | PostCSS + cssnano |
| No Lint Errors | ESLint passes | `npm run lint` |
| No TypeErrors | N/A (PHP project) | — |

### G1 — Accessibility Gate

| Check | Requirement | Tool |
|-------|-------------|------|
| axe-core | Zero critical violations | `a11y-hardgate.spec.js` |
| Keyboard Navigation | All interactive elements focusable | Manual + Playwright |
| Focus Visible | `:focus-visible` on all buttons/links | CSS audit |
| Color Contrast | WCAG 2.1 AA (4.5:1 text, 3:1 UI) | `tokens.css` validation |
| Touch Targets | ≥44×44px on mobile | `mobile-nav-scale.css` |

### G2 — Visual Regression Gate

| Check | Requirement | Tool |
|-------|-------------|------|
| Light Mode | Hero text readable, no transparency issues | `light-dark-audit.spec.js` |
| Dark Mode | Gold accents visible, glass effects | `light-dark-audit.spec.js` |
| Mobile (375px) | No horizontal overflow | Playwright mobile tests |
| Desktop (1280px) | Full nav visible, 5 links | Playwright desktop tests |

### G3 — Functional Gate

| Check | Requirement | Tool |
|-------|-------------|------|
| Contact Form | Submission succeeds with reCAPTCHA | E2E test |
| Language Switch | DA/EN toggle works | `qa-final.spec.js` |
| Theme Toggle | Light/dark persists in localStorage | Manual |
| Mobile Menu | Opens/closes without scroll lock | `qa-final.spec.js` |

### G4 — Performance Gate

| Check | Requirement | Tool |
|-------|-------------|------|
| LCP | < 2.5s on mobile | Lighthouse |
| CLS | < 0.1 | Lighthouse |
| CSS Size | marketing.min.css < 100KB | Build output |

### G5 — Security Gate

| Check | Requirement | Tool |
|-------|-------------|------|
| CSP Headers | script-src, style-src defined | cURL check |
| No Inline JS | Script tags use src attribute | Code audit |
| reCAPTCHA | v3 with score validation | `contact-submit.php` |
| XSS Prevention | `htmlspecialchars()` on all output | Code audit |

---

## 9. Authentication Flow

```
┌─────────────┐     ┌──────────────┐     ┌───────────────┐
│ Public      │     │ Agent Portal │     │ Admin Panel   │
│ Marketing   │     │              │     │               │
└─────┬───────┘     └──────┬───────┘     └───────┬───────┘
      │                    │                     │
      │ No Auth            │ JWT/Session         │ Session + Role
      ▼                    ▼                     ▼
  index.php            agent-access.php       dashboard.php
  about.php            gdi-login.php          admin.php
  products.php         ccs-login.php          settings.php
  cases.php            ccs-console.php
  pricing.php
  contact.php
```

---

## 10. Internationalization (i18n)

**Supported Languages:** DA (Danish), EN (English)

**Language Detection Priority:**
1. Query parameter: `?lang=da`
2. Cookie: `bbx-lang`
3. Browser: `Accept-Language`
4. Default: `en`

**Translation Files:** `lang/da.json`, `lang/en.json`

**Translation Function:** `t('key.path')` via `includes/i18n.php`

---

## Appendix A: File Manifest

| Directory | File Count | Purpose |
|-----------|------------|---------|
| `/` (root) | 38 PHP | Page files |
| `/includes/` | 26 PHP | Shared components |
| `/assets/css/` | 13 CSS | Stylesheets |
| `/assets/css/components/` | 7 CSS | Component styles |
| `/assets/js/` | 9 JS | Scripts |
| `/api/` | 5 PHP | API endpoints |
| `/lang/` | 2 JSON | Translation files |
| `/tests/` | 22 spec.js | Playwright tests |
| `/docs/` | 60+ files | Documentation |

---

## Appendix B: Quick Reference Commands

```bash
# Build CSS
npm run build:css

# Run all tests
npm test

# Run a11y hardgate only
npm test -- tests/a11y-hardgate.spec.js --project=chromium

# Run mobile UX tests
npm test -- tests/mobile-ux-sprint5.spec.js --project=chromium

# Start local server
php -S localhost:8000

# Check for merge conflicts
grep -rn "<<<<<<" assets/css/
```

---

**Document Status:** ✅ Complete for Phase 1 QA v1.1
