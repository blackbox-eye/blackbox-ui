# Sprint 2 Test Plan – UX Enhancements
**ALPHA Interface GUI – Blackbox EYE™**
**Version:** 1.0
**Date:** 2025-01-XX
**Branch:** `feat/ui-enhancements-sprint2`

---

## 📋 Executive Summary

Sprint 2 introduces four major UX improvements to the ALPHA Interface:
1. **Breadcrumb Navigation** – Semantic navigation with structured data
2. **Enhanced Mobile Menu** – Smooth animations and keyboard accessibility
3. **AI Loading States** – Professional spinners and skeleton screens
4. **Sticky CTA Button** – Scroll-triggered contact prompt

All features maintain **WCAG 2.1 AA compliance** and respect user motion preferences.

---

## 🎯 Test Objectives

- ✅ Verify all four features function correctly across devices
- ✅ Ensure keyboard navigation and screen reader compatibility
- ✅ Validate responsive design (mobile, tablet, desktop)
- ✅ Confirm Lighthouse score ≥95 (Performance, Accessibility, Best Practices, SEO)
- ✅ Test reduced motion preferences
- ✅ Verify AI loading states integrate seamlessly

---

## 🧪 Test Matrix

### 1. Breadcrumb Navigation

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **Visual Display** | Navigate to `/products.php` | Breadcrumb shows: `Hjem › Produkter` | ⏳ |
| **Home Page** | Visit `/index.php` | No breadcrumb displayed (empty) | ⏳ |
| **Deep Nesting** | Visit `/products.php?category=GreyEYE` | Breadcrumb shows full path with separators | ⏳ |
| **Structured Data** | View page source on `/products.php` | `<script type="application/ld+json">` with BreadcrumbList present | ⏳ |
| **ARIA Labels** | Inspect breadcrumb `<nav>` | Contains `aria-label="Breadcrumb"` | ⏳ |
| **Current Page** | Check last breadcrumb item | Has `aria-current="page"` attribute | ⏳ |
| **Responsive** | Resize to 360px width | Breadcrumb text wraps or truncates gracefully | ⏳ |
| **Keyboard Nav** | Tab through breadcrumb links | All links focusable with visible focus indicator | ⏳ |

**Pass Criteria:** 8/8 tests pass

---

### 2. Enhanced Mobile Menu

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **Open Animation** | Click hamburger icon (mobile) | Menu slides in from right with overlay fade-in | ⏳ |
| **Close Button** | Click close (×) icon in menu | Menu slides out, overlay fades out | ⏳ |
| **Overlay Click** | Click dark overlay (not menu) | Menu closes with animation | ⏳ |
| **ESC Key** | Press ESC while menu open | Menu closes and focus returns to hamburger | ⏳ |
| **Focus Trap** | Tab through open menu | Focus stays within menu, cycles to close button | ⏳ |
| **Focus Restore** | Close menu with ESC | Focus returns to hamburger button | ⏳ |
| **Reduced Motion** | Enable `prefers-reduced-motion` | Menu appears instantly (no slide animation) | ⏳ |
| **Touch Gestures** | Swipe left on menu | Menu closes (if implemented) or no error | ⏳ |
| **Screen Reader** | Navigate with NVDA/JAWS | Menu items announced correctly | ⏳ |

**Pass Criteria:** 8/9 tests pass (swipe optional)

---

### 3. AI Loading States

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **Quick Assessment** | Click "Få Øjeblikkelig Vurdering" | Animated spinner appears in result container | ⏳ |
| **Gemini Modal** | Click "Se Demo Scenarie" (PVE) | Spinner displays in modal during API call | ⏳ |
| **Recommendation** | Click "Få Anbefaling" | Skeleton screen shows (3 animated blocks) | ⏳ |
| **Case Analysis** | Enter text, click "Analysér" | Skeleton screen appears | ⏳ |
| **Spinner Animation** | Observe spinner (Quick Assessment) | Smooth rotation, respects reduced motion | ⏳ |
| **Skeleton Animation** | Watch skeleton (Recommendation) | Pulse/shimmer effect active | ⏳ |
| **Loading → Result** | Wait for API response (any feature) | Loader fades out, result fades in smoothly | ⏳ |
| **Error State** | Trigger API error (invalid input) | Loader disappears, error message shown | ⏳ |
| **Reduced Motion** | Enable motion preference + test | Animations disabled, static loader shown | ⏳ |

**Pass Criteria:** 9/9 tests pass

---

### 4. Sticky CTA Button

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **Initial State** | Load page, stay at top | Button hidden (not visible) | ⏳ |
| **Scroll Trigger** | Scroll down 50% of viewport | Button slides up from bottom-right | ⏳ |
| **Near Footer** | Scroll to bottom (within 200px) | Button hides to avoid footer overlap | ⏳ |
| **Scroll Up** | Scroll back to top 50% | Button fades out | ⏳ |
| **Mobile Text** | View on <640px screen | Shows "Kontakt" only (short text) | ⏳ |
| **Desktop Text** | View on ≥640px screen | Shows "Book Møde" (full text) | ⏳ |
| **Hover Effect** | Hover over button (desktop) | Scale up (105%), shadow glow intensifies | ⏳ |
| **Click Action** | Click button | Navigates to `contact.php` | ⏳ |
| **Keyboard Focus** | Tab to button | Visible focus ring, activates with Enter | ⏳ |
| **Reduced Motion** | Enable preference + scroll | Button appears instantly (no slide/fade) | ⏳ |
| **ARIA Label** | Inspect with screen reader | Announces "Book sikkerhedsmøde" | ⏳ |

**Pass Criteria:** 11/11 tests pass

---

### 5. AlphaBot Widget

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **Visibility (priority pages)** | Visit `index.php`, `about.php`, `products.php`, `cases.php`, `pricing.php`, `contact.php` | "Tal med AlphaBot" toggle visible bottom-left | ⏳ |
| **Hidden on ops pages** | Visit `agent-login.php` or `dashboard.php` | Widget absent to avoid operator UI | ⏳ |
| **Toggle Interaction** | Click "Tal med AlphaBot" | Panel slides in, textarea receives focus, aria-expanded="true" | ⏳ |
| **Close Controls** | Click × button or outside panel | Panel closes, focus returns to toggle, aria-expanded="false" | ⏳ |
| **ESC Dismissal** | Open panel, press ESC | Panel closes instantly, sticky CTA unaffected | ⏳ |
| **Sticky CTA Separation** | On mobile (375px) scroll until CTA shows | AlphaBot (bottom-left) and CTA (bottom-right) never overlap | ⏳ |
| **Reduced Motion** | Enable `prefers-reduced-motion`, toggle panel | Panel shows/hides without slide animation | ⏳ |
| **Keyboard Send** | Type message, press Enter (Shift+Enter for newline) | Message sent, send button disabled until response | ⏳ |
| **Screen Reader Labels** | With NVDA focus toggle & textarea | Announces button label + dialog role, log region reads replies | ⏳ |
| **Graceful Errors** | Simulate API failure (disconnect network) | Friendly fallback message displayed inside log | ⏳ |

**Pass Criteria:** 10/10 tests pass

---

## 📱 Device & Browser Matrix

### Minimum Testing Requirements

| Device Type | Screen Size | Browsers | Priority |
|-------------|-------------|----------|----------|
| **Desktop** | 1920×1080 | Chrome 120+, Firefox 121+, Edge 120+ | P0 |
| **Tablet** | 768×1024 (iPad) | Safari 17+, Chrome (iPad) | P1 |
| **Mobile** | 375×667 (iPhone SE) | Safari (iOS 17), Chrome (Android) | P0 |
| **Mobile** | 360×740 (Galaxy S21) | Samsung Internet, Chrome | P1 |

**P0 = Critical** | **P1 = High** | **P2 = Medium**

---

## ⌨️ Keyboard Accessibility Checklist

| Feature | Test | Expected Behavior | Status |
|---------|------|-------------------|--------|
| **Breadcrumb** | Tab through links | All links focusable, visible focus ring | ⏳ |
| **Mobile Menu** | Tab in open menu | Focus trapped, cycles through items | ⏳ |
| **Mobile Menu** | Press ESC | Closes menu, focus returns to trigger | ⏳ |
| **AlphaBot Widget** | Toggle + ESC | Panel toggles via button, ESC closes + focus returns | ⏳ |
| **Sticky CTA** | Tab to button | Receives focus, activates with Enter/Space | ⏳ |
| **Gemini Modal** | Tab in modal | Focus trapped, ESC closes | ⏳ |
| **Gemini Modal** | Press ESC | Closes modal, focus returns to trigger | ⏳ |
| **All Interactive** | Navigate without mouse | All features fully functional | ⏳ |

**Pass Criteria:** 8/8 tests pass

---

## 🔍 Screen Reader Testing

### Test Scenarios

1. **Breadcrumb Navigation**
   - NVDA/JAWS announces: "Breadcrumb navigation, landmark"
   - Each link announced with position: "Link, Hjem" → "Link, Produkter"
   - Current page: "Produkter, current page"

2. **Mobile Menu**
   - Hamburger: "Open navigation menu, button"
   - Close button: "Close navigation, button"
   - Menu items: "Link, Hjem | Link, Produkter" etc.

3. **Sticky CTA**
   - Announces: "Book sikkerhedsmøde, link"
   - Icon decorative (aria-hidden="true")

4. **AI Loading States**
   - Spinner container: Uses `aria-live="polite"` (verify)
   - Announces: "Loading" or "Indlæser" when visible

5. **AlphaBot Widget**
   - Toggle button announces expanded/collapsed state
   - Panel exposes `role="dialog"` with descriptive label
   - Message log (`role="log"`) reads new replies automatically

**Tools:** NVDA (Windows), JAWS (Windows), VoiceOver (macOS/iOS)

---

## 🚀 Performance Testing

### Lighthouse Audit Requirements

| Category | Target Score | Sprint 1 Baseline | Sprint 2 Target | Status |
|----------|--------------|-------------------|-----------------|--------|
| **Performance** | ≥90 | 92 | ≥90 | ⏳ |
| **Accessibility** | ≥95 | 96 | ≥95 | ⏳ |
| **Best Practices** | ≥95 | 98 | ≥95 | ⏳ |
| **SEO** | ≥95 | 100 | ≥95 | ⏳ |

**Test Pages:**

- `index.php` (homepage)
- `products.php` (breadcrumb test)
- `contact.php` (sticky CTA test)

**Pass Criteria:** All 4 categories ≥95 on all 3 pages

### WebPageTest Metrics

| Metric | Target | Measurement | Status |
|--------|--------|-------------|--------|
| First Contentful Paint (FCP) | <1.8s | TBD | ⏳ |
| Largest Contentful Paint (LCP) | <2.5s | TBD | ⏳ |
| Cumulative Layout Shift (CLS) | <0.1 | TBD | ⏳ |
| Time to Interactive (TTI) | <3.8s | TBD | ⏳ |

**Tool:** [WebPageTest.org](https://www.webpagetest.org/) (Dulles, VA - Cable)

---

## 🎨 Visual Regression Testing

### Screenshot Comparison Points

| Feature | Viewport | Reference State | Test State |
|---------|----------|-----------------|------------|
| Breadcrumb | 1920×1080 | Before Sprint 2 | After Sprint 2 |
| Breadcrumb | 375×667 | Before Sprint 2 | After Sprint 2 |
| Mobile Menu (open) | 375×667 | Sprint 1 menu | Sprint 2 slide-in |
| Sticky CTA | 375×667 | N/A (new) | Scrolled >50% |
| AI Spinner | 768×1024 | Old loader | New spinner |
| Skeleton Screen | 1920×1080 | Old loader | New skeleton |

**Tools:** Percy.io (optional), manual screenshot comparison

---

## 🧩 Cross-Feature Integration Tests

### Combined Feature Scenarios

1. **Breadcrumb + Mobile Menu**
   - Open mobile menu on `/products.php`
   - Verify breadcrumb stays visible (not covered)
   - Close menu → breadcrumb still functional

2. **Sticky CTA + Mobile Menu**
   - Scroll to trigger CTA button
   - Open mobile menu
   - Verify CTA doesn't interfere with menu (z-index)
   - Close menu → CTA still visible

3. **AI Loading + Sticky CTA**
   - Trigger Quick Assessment (spinner)
   - Scroll down to show sticky CTA
   - Verify both elements visible without overlap

4. **Breadcrumb + Sticky CTA + Reduced Motion**
   - Enable `prefers-reduced-motion`
   - Navigate to product page (breadcrumb static)
   - Scroll down (CTA appears instantly)
   - Click CTA → contact page loads

5. **AlphaBot + Sticky CTA**
   - On mobile viewport, scroll to show CTA and open AlphaBot
   - Confirm widgets anchor to opposite corners without overlap
   - Close AlphaBot while CTA remains visible

**Pass Criteria:** 5/5 scenarios work without conflicts

---

## 🎭 Reduced Motion Testing

### User Preference: `prefers-reduced-motion: reduce`

| Feature | Animation (Normal) | Behavior (Reduced Motion) | Status |
|---------|-------------------|---------------------------|--------|
| **Mobile Menu** | Slide-in (300ms) | Instant show/hide | ⏳ |
| **Overlay** | Fade-in (250ms) | Instant opacity change | ⏳ |
| **Sticky CTA** | Slide-up (300ms) | Instant visibility toggle | ⏳ |
| **AI Spinner** | 360° rotation | Static icon or no animation | ⏳ |
| **Skeleton Screen** | Pulse/shimmer | Static gradient | ⏳ |

**Testing Method:**

```css
/* Developer Tools → Rendering → Emulate CSS prefers-reduced-motion */
```

**Pass Criteria:** 5/5 features respect motion preference

---

## 📊 Analytics & Tracking Validation

### Event Tracking Requirements

| Feature | Event Name | Properties | Status |
|---------|-----------|------------|--------|
| Sticky CTA Click | `cta_click` | `source: 'sticky_button'` | ⏳ |
| Mobile Menu Open | `mobile_menu_open` | `method: 'hamburger/swipe'` | ⏳ |
| Breadcrumb Click | `breadcrumb_click` | `page: 'products'` | ⏳ |
| AI Loading Shown | `ai_loading_shown` | `type: 'spinner/skeleton'` | ⏳ |

**Tools:** Google Analytics 4, Plausible, or custom tracking

---

## 🐛 Known Issues & Edge Cases

### Documented Limitations

1. **Sticky CTA on Short Pages**
   - **Issue:** Pages shorter than 2 viewports may never trigger CTA
   - **Status:** Expected behavior (not a bug)
   - **Workaround:** CTA still in footer contact section

2. **Breadcrumb on Dynamic Pages**
   - **Issue:** AJAX-loaded content doesn't update breadcrumb
   - **Status:** Out of scope for Sprint 2
   - **Future:** Sprint 4 (Dynamic Content)

3. **Mobile Menu Swipe Gesture**
   - **Issue:** No swipe-to-close implemented
   - **Status:** P2 priority, not critical
   - **Future:** Sprint 3 (Advanced Interactions)

---

## ✅ Test Execution Log

### Test Session Template

```markdown
**Tester:** [Name]
**Date:** 2025-01-XX
**Environment:** [Browser/Device]
**Branch:** feat/ui-enhancements-sprint2
**Commit:** [SHA]

#### Test Results:
- Breadcrumb Navigation: ✅/❌ (X/8 passed)
- Mobile Menu: ✅/❌ (X/9 passed)
- AI Loading States: ✅/❌ (X/9 passed)
- Sticky CTA: ✅/❌ (X/11 passed)
- Keyboard Accessibility: ✅/❌ (X/7 passed)
- Lighthouse Score: Performance XX | A11y XX | BP XX | SEO XX

**Critical Issues:** [List]
**Minor Issues:** [List]
**Notes:** [Any observations]
```

---

## 📸 Visual Test Cases (Screenshots)

### Required Evidence

1. **Breadcrumb Navigation**
   - Desktop view: `/products.php` with breadcrumb visible
   - Mobile view: Breadcrumb wrapping on 375px screen
   - Schema.org JSON-LD in page source

2. **Enhanced Mobile Menu**
   - Menu closed (hamburger icon visible)
   - Menu open (slide-in complete, overlay visible)
   - Menu closing (mid-animation capture)

3. **AI Loading States**
   - Quick Assessment spinner (centered, rotating)
   - Recommendation skeleton (3 blocks with shimmer)
   - Gemini modal spinner

4. **Sticky CTA Button**
   - Hidden state (page top)
   - Visible state (scrolled 50%)
   - Hover state (desktop)
   - Mobile vs. desktop text difference

**Storage:** `/docs/reports/sprint2_visual_evidence/`

---

## 🚦 Sign-Off Criteria

### Sprint 2 Ready for Production

- [ ] All test matrices ≥90% pass rate
- [ ] Lighthouse scores ≥95 on all categories
- [ ] Zero critical accessibility violations (aXe/WAVE)
- [ ] Keyboard navigation fully functional
- [ ] Screen reader testing complete (2/3 tools)
- [ ] Reduced motion preference respected
- [ ] Cross-browser testing (Chrome, Firefox, Safari, Edge)
- [ ] Mobile testing (iOS Safari, Chrome Android)
- [ ] Visual regression approved
- [ ] Performance metrics within targets
- [ ] Code reviewed and approved
- [ ] Documentation updated (this file + CHANGELOG.md)

**Sign-Off:**

- Developer: _______________________ Date: _______
- QA Lead: _________________________ Date: _______
- Product Owner: ___________________ Date: _______

---

## 📝 Test Data & Resources

### Test URLs

```text
Local: http://localhost/ALPHA-Interface-GUI/
Staging: https://staging.blackbox-eye.dk/ (if applicable)
```

### Test Accounts

- Admin: [credentials in 1Password]
- Agent: [credentials in 1Password]

### AI Test Prompts

```text
Quick Assessment: "Hvad er jeres branche?" → "IT-sikkerhed"
Recommendation: Industry "Sundhedssektor" + 500 employees
Case Analysis: "Vi har oplevet gentagne angreb på vores VPN"
```

### Browser Extensions Needed

- aXe DevTools (Accessibility)
- Lighthouse (Performance)
- WAVE (Accessibility)
- React DevTools (if React added senere)

---

## 🔄 Continuous Testing Plan

### Automated Tests (Future Sprint)

```javascript
// Cypress E2E test examples (Sprint 3+)
describe('Sprint 2 Features', () => {
  it('displays breadcrumb on product page', () => {
    cy.visit('/products.php');
    cy.get('[aria-label="Breadcrumb"]').should('be.visible');
    cy.contains('Hjem').should('exist');
  });

  it('opens mobile menu with animation', () => {
    cy.viewport(375, 667);
    cy.get('#mobile-menu-toggle').click();
    cy.get('#mobile-menu').should('have.class', 'active');
  });

  it('shows sticky CTA after scroll', () => {
    cy.scrollTo(0, 500);
    cy.get('#sticky-cta').should('be.visible');
  });
});
```

---

## 📞 Support & Questions

**Test Coordinator:** [Name/Email]
**Dev Lead:** [Name/Email]
**Sprint 2 Slack:** #sprint2-ux-enhancements
**Bug Reports:** GitHub Issues with label `sprint-2`

---

**Last Updated:** 2025-01-XX
**Next Review:** Post-Sprint 2 Retrospective
