# File Landscape — Blackbox EYE™ UI

> **Version:** 1.1 (Phase 1 QA v1.1)  
> **Updated:** 2025-12-23  
> **Chief QA Officer:** ALPHA-QA-Agent

---

## 1. Directory Structure Overview

```
blackbox-ui/
├── *.php                    # Page files (38 files)
├── api/                     # API endpoints
├── assets/
│   ├── css/                 # Stylesheets
│   │   └── components/      # Component CSS
│   ├── js/                  # JavaScript
│   └── bg/                  # Background assets
├── config/                  # Configuration files
├── db/                      # Database migrations/schema
├── docs/                    # Documentation (60+ files)
├── includes/                # PHP includes/components
├── lang/                    # i18n translation files
├── scripts/                 # Utility scripts
├── tests/                   # Playwright test suites
└── tools/                   # Development tools
```

---

## 2. CSS Architecture

### 2.1 Global Styling Entrypoints

| File | Size | Purpose | Load Condition |
|------|------|---------|----------------|
| `tailwind.full.css` | ~180KB | Tailwind base + utilities | All pages |
| `tokens.css` | ~8KB | Design tokens (colors, spacing, focus) | All pages |
| `custom-ui.css` | ~45KB | Component styles | All pages |
| `theme-overrides.css` | ~5KB | Light/dark theme adjustments | All pages |
| `marketing.css` | ~120KB | Marketing page styles (source) | Dev only |
| `marketing.min.css` | ~85KB | Marketing styles (minified) | Tier A/B |
| `admin.css` | ~25KB | Admin panel styles (source) | Dev only |
| `admin.min.css` | ~18KB | Admin styles (minified) | Tier C |
| `ccs-login.css` | ~8KB | CCS login dedicated styles | ccs-login.php |

### 2.2 Component CSS Files

Located in `assets/css/components/`:

| File | Purpose | Load Condition |
|------|---------|----------------|
| `motion-safe.css` | `prefers-reduced-motion` support | Always (global) |
| `hero-mobile.css` | Unified hero sections mobile | `@media (max-width: 768px)` |
| `mobile-nav-scale.css` | Nav scaling + touch targets | Always |
| `console-selector-mobile.css` | Console cards on mobile | agent-access.php |
| `ccs-login-mobile.css` | CCS login mobile polish | ccs-login.php |
| `bbx-icons.css` | Icon system + tooltips | On demand |
| `bbx-snackbar.css` | Toast notifications | On demand |

### 2.3 CSS Load Order (Critical)

**Marketing Pages:**
```html
<!-- 1. Base Framework -->
<link rel="stylesheet" href="/assets/css/tailwind.full.css">

<!-- 2. Design Tokens (must precede components) -->
<link rel="stylesheet" href="/assets/css/tokens.css">

<!-- 3. Component Library -->
<link rel="stylesheet" href="/assets/css/custom-ui.css">

<!-- 4. Theme Adjustments -->
<link rel="stylesheet" href="/assets/css/theme-overrides.css">

<!-- 5. Global Motion Safety -->
<link rel="stylesheet" href="/assets/css/components/motion-safe.css">

<!-- 6. Mobile-Only (conditional) -->
<link rel="stylesheet" href="/assets/css/components/hero-mobile.css" media="(max-width: 768px)">

<!-- 7. Mobile Nav Scaling -->
<link rel="stylesheet" href="/assets/css/components/mobile-nav-scale.css">

<!-- 8. Page-Specific (minified) -->
<link rel="stylesheet" href="/assets/css/marketing.min.css">
```

### 2.4 CSS Build Process

**Build Command:** `npm run build:css`

**PostCSS Pipeline:**
1. `postcss-import` — Resolves @import
2. `autoprefixer` — Vendor prefixes
3. `cssnano` — Minification

**Source → Output Mapping:**
| Source | Output | Trigger |
|--------|--------|---------|
| `marketing.css` | `marketing.min.css` | `npm run build:css` |
| `admin.css` | `admin.min.css` | `npm run build:css` |

**Cache Busting:**
Version controlled in `includes/site-header.php`:
```php
$css_version = '1.6.18';  // Increment on CSS changes
```

---

## 3. JavaScript Architecture

### 3.1 Script Files

| File | Size | Purpose | Load |
|------|------|---------|------|
| `site.js` | ~25KB | Main bundle (contact, AI, digital rain, menu) | Source |
| `site.min.js` | ~15KB | Minified main bundle | Production |
| `graphene-hero.js` | ~8KB | 3D hero canvas animation | index.php |
| `interface-menu.js` | ~3KB | Admin panel dropdown menus | Tier C |
| `password-toggle.js` | ~1KB | Show/hide password toggle | Login pages |
| `bbx-snackbar.js` | ~2KB | Toast notification system | On demand |
| `qa-mode.js` | ~2KB | QA overlay controls | Dev only |
| `router-guard.js` | ~1KB | SPA-like navigation guard | Future |

### 3.2 Script Load Pattern

```html
<!-- Footer scripts -->
<script src="/assets/js/site.min.js" defer></script>

<!-- Conditional scripts -->
<?php if ($current_page === 'index'): ?>
<script src="/assets/js/graphene-hero.js" defer></script>
<?php endif; ?>
```

---

## 4. PHP Include Structure

### 4.1 Core Includes

| File | Purpose | Used By |
|------|---------|---------|
| `env.php` | Environment variables, constants | All pages |
| `i18n.php` | Internationalization functions | All pages |
| `db.php` | PDO database connection | Pages with DB |
| `site-header.php` | Marketing header + meta | Tier A/B |
| `site-footer.php` | Marketing footer + scripts | Tier A/B |
| `admin-layout.php` | Admin header + nav | Tier C |
| `admin-footer.php` | Admin footer + scripts | Tier C |
| `header.php` | Simplified head (legacy) | gdi-login.php |

### 4.2 Functional Includes

| File | Purpose |
|------|---------|
| `jwt_helper.php` | JWT token validation for SSO |
| `logging.php` | Audit event logging |
| `sso_audit.php` | SSO-specific audit trail |
| `mail-helper.php` | PHPMailer wrapper |
| `apikey-helper.php` | API key CRUD operations |
| `vault-encryption.php` | AES-256-GCM encryption |
| `graphene-config.php` | Graphene theme settings |

### 4.3 UI Components

| File | Purpose |
|------|---------|
| `console-selector.php` | Reusable console card selector |
| `login-card-modular.php` | Modular login form with MFA |
| `mfa-modal.php` | MFA step flow modal |
| `sso-request-modal.php` | SSO request form modal |
| `intel24-request-modal.php` | Intel24 access request |
| `cookie-banner.php` | GDPR cookie consent |

---

## 5. Design Tokens

### 5.1 Color Tokens (`tokens.css`)

**Dark Theme (Default):**
```css
--color-bg: #0a0b0d;
--color-surface: #1a1c22;
--color-text: #e8e6e3;           /* 13.5:1 contrast */
--color-text-secondary: #a8a5a0; /* 7.2:1 contrast */
--color-primary: #c9a227;        /* Blackbox gold */
--color-focus-ring: #c9a227;
```

**Light Theme:**
```css
--color-bg: #f8f7f4;
--color-surface: #ffffff;
--color-text: #1a1a1a;           /* 15.3:1 contrast */
--color-text-secondary: #4a4a4a; /* 8.1:1 contrast */
--color-primary: #9a7b1f;        /* Darker gold */
--color-focus-ring: #9a7b1f;
```

### 5.2 Spacing Tokens

```css
--space-1: 0.25rem;   /* 4px */
--space-2: 0.5rem;    /* 8px */
--space-3: 0.75rem;   /* 12px */
--space-4: 1rem;      /* 16px */
--space-6: 1.5rem;    /* 24px */
--space-8: 2rem;      /* 32px */
--space-12: 3rem;     /* 48px */
--space-16: 4rem;     /* 64px */
```

### 5.3 Focus Styles (A11y)

```css
/* Keyboard focus indicator */
:focus-visible {
  outline: 2px solid var(--color-focus-ring);
  outline-offset: 2px;
  box-shadow: 0 0 0 3px var(--color-primary-soft);
}

/* Mouse click - no outline */
:focus:not(:focus-visible) {
  outline: none;
}
```

### 5.4 Brand-Locked Tokens

These tokens MUST NOT be modified without brand approval:

| Token | Value | Usage |
|-------|-------|-------|
| `--color-primary` | `#c9a227` (dark) / `#9a7b1f` (light) | Primary gold |
| `--bbx-gold` | `#8a7b6a` | Legacy grey-brown |
| Font: Chakra Petch | Display/logo | Headlines |
| Font: Inter | Body text | UI elements |

---

## 6. Mobile Responsiveness Analysis

### 6.1 Current Viewport Configuration

**Meta Tag:** `<meta name="viewport" content="width=device-width, initial-scale=1.0">`

**Breakpoints (Tailwind):**
```javascript
screens: {
  'xs': '480px',
  'sm': '640px',   // Tailwind default
  'md': '768px',   // Tablet breakpoint
  'lg': '1024px',  // Desktop
  'xl': '1280px'   // Large desktop
}
```

### 6.2 Mobile Touch Targets

Current implementation in `mobile-nav-scale.css`:

| Element | Min Height | Status |
|---------|------------|--------|
| `.nav-link-mobile` | 52px | ✅ WCAG compliant |
| `.nav-link-mobile--secondary` | 48px | ✅ WCAG compliant |
| `.language-switch` | 44px | ✅ WCAG compliant |
| `#mobile-menu-close` | 48px | ✅ WCAG compliant |
| CTA buttons | 48px | ✅ WCAG compliant |

### 6.3 Mobile Zoom Improvement Proposal

**Goal:** Achieve 75–80% effective scale on mobile (elements ~20% larger)

**Strategy 1: Base Font Size Adjustment**

```css
/* In tokens.css */
@media (max-width: 768px) {
  :root {
    font-size: 112.5%; /* 18px base instead of 16px */
  }
}
```

**Impact:** All rem-based values scale up 12.5%

**Strategy 2: Container Padding Reduction**

```javascript
// In tailwind.config.js
container: {
  padding: {
    DEFAULT: '0.75rem',  // Reduced from 1rem
    sm: '1rem',
    lg: '2rem'
  }
}
```

**Impact:** More content visible, less wasted edge space

**Strategy 3: Hero Font Clamp Adjustment**

```css
/* In hero-mobile.css */
.hero-gradient-text {
  font-size: clamp(1.75rem, 6vw + 0.5rem, 3.5rem) !important;
  /* Changed from clamp(1.5rem, 5vw + 0.5rem, 3.5rem) */
}
```

**Impact:** 16% larger hero headlines on smallest screens

### 6.4 Files Requiring Mobile Zoom Changes

| File | Change Required |
|------|-----------------|
| `assets/css/tokens.css` | Add `:root` mobile font-size |
| `tailwind.config.js` | Reduce container padding |
| `assets/css/components/hero-mobile.css` | Adjust clamp() minimums |
| `assets/css/components/mobile-nav-scale.css` | ✅ Already optimized |

---

## 7. Test Coverage Map

### 7.1 Test Suites by Purpose

| Suite | File | Coverage |
|-------|------|----------|
| A11y Hardgate | `a11y-hardgate.spec.js` | axe-core on all Tier A pages |
| Light/Dark Audit | `light-dark-audit.spec.js` | Theme visibility (28 tests) |
| Mobile UX Sprint 4 | `mobile-ux-sprint4.spec.js` | Console cards, touch targets |
| Mobile UX Sprint 5 | `mobile-ux-sprint5.spec.js` | Quick switch, nav drawer |
| QA Final | `qa-final.spec.js` | Burger menu, language switch |
| CCS Login | `ccs-login.spec.js` | Login flow, mobile viewport |
| Visual Regression | `visual.spec.js` | Screenshot comparison |

### 7.2 Viewport Test Matrix

| Test File | 375px | 768px | 1024px | 1280px |
|-----------|-------|-------|--------|--------|
| `light-dark-audit.spec.js` | ✅ | ✅ | — | ✅ |
| `mobile-ux-sprint5.spec.js` | ✅ (390×844) | — | — | — |
| `qa-screenshots.spec.js` | ✅ | — | — | ✅ |
| `visual.spec.js` | ✅ | ✅ | ✅ | ✅ |

---

## 8. Caching Strategy

### 8.1 Client-Side Caching

**Query String Versioning:**
```php
<link rel="stylesheet" href="/assets/css/marketing.min.css?v=1.6.18">
```

**Cache Headers (Recommended `.htaccess`):**
```apache
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType text/css "access plus 1 year"
  ExpiresByType application/javascript "access plus 1 year"
  ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>
```

### 8.2 Cache Invalidation

Increment `$css_version` in `includes/site-header.php` when:
1. Any CSS file is modified
2. After running `npm run build:css`
3. Before production deployment

---

## 9. Deployment Workflow

### 9.1 Pre-Commit Checklist

```bash
# 1. Resolve any merge conflicts
grep -rn "<<<<<<" assets/css/

# 2. Build minified CSS
npm run build:css

# 3. Run test suite
npm test -- --project=chromium

# 4. Verify no uncommitted changes
git status

# 5. Stage and commit
git add -A
git commit -m "type(scope): description"
```

### 9.2 Branch Strategy

| Branch | Purpose | Merge Target |
|--------|---------|--------------|
| `main` | Production | — |
| `sprint8-a11y-hardgate` | Current sprint | `main` |
| `feature/*` | Feature branches | Sprint branch |
| `hotfix/*` | Critical fixes | `main` |

---

## 10. Quick Reference

### Commands

```bash
# CSS build
npm run build:css

# Run all tests
npm test

# Run specific test
npm test -- tests/a11y-hardgate.spec.js --project=chromium

# Start dev server
php -S localhost:8000

# Check for conflicts
grep -rn "<<<<<<" .
```

### Key File Locations

| Purpose | Path |
|---------|------|
| Design tokens | `assets/css/tokens.css` |
| Mobile nav scaling | `assets/css/components/mobile-nav-scale.css` |
| Hero mobile | `assets/css/components/hero-mobile.css` |
| Marketing header | `includes/site-header.php` |
| i18n translations | `lang/da.json`, `lang/en.json` |
| Playwright config | `playwright.config.js` |
| Tailwind config | `tailwind.config.js` |

---

**Document Status:** ✅ Complete for Phase 1 QA v1.1
