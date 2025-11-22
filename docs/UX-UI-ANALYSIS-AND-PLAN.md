# UX/UI Analyse og Forbedringsplan – ALPHA Interface GUI

**Version:** 1.0
**Dato:** 2025-11-22
**Ansvarlig:** ALPHA‑UX‑Frontend‑Agent
**Status:** Initial analyse og handlingsplan

---

## 1. Executive Summary

Denne rapport præsenterer en omfattende UX/UI-analyse af ALPHA Interface GUI (Blackbox EYE™) med fokus på:
- Tilgængelighed (WCAG 2.1 AA compliance)
- Brugeroplevelse og informationsarkitektur
- Performance og responsivitet
- Designkonsistens og skalerbarhed

Analysen identificerer 6 hovedområder med i alt 23 konkrete forbedringspunkter, prioriteret efter impact og implementeringskompleksitet.

---

## 2. Nuværende Design-Status

### 2.1 Farvepalette & Visuel Identitet

**Eksisterende CSS-variabler (`:root`):**
```css
--bg-color: #101419              /* Mørk baggrund */
--primary-accent: #FFC700        /* Gul/guld primær */
--glitch-dark-green: #003d00     /* Glitch-effekt grøn */
--glitch-fire: #ffac00           /* Glitch-effekt orange */
--text-high-emphasis: #EAEAEA    /* Hvid tekst, høj kontrast */
--text-medium-emphasis: #9CA3AF  /* Grå tekst, medium kontrast */
--glass-border: rgba(255,255,255,0.1)
--glass-bg: rgba(22,28,39,0.6)
--digital-rain-color: #008000    /* Matrix-regn grøn */
```

**Legacy variabler fra `style.css`:**
```css
--bg-dark: #041004
--text-light: #cde
--green: #0a5
--gold: #d4af37
--gold-dark: #bfa650
```

**⚠️ Problem:** To separate farvesystemer uden konsistens mellem `style.css` (login/admin) og `site-header.php` inline styles (public site).

---

### 2.2 Typografi

**Primære fonte:**
- **Display/Headlines:** 'Chakra Petch' (700 weight, logo-glitch)
- **Body/UI:** 'Inter' (300–900 weights)
- **Fallback:** sans-serif

**Font-størrelse skalering:**
- Bruger `clamp()` visse steder for responsivitet
- **Problem:** Inkonsistent skalering på tværs af sider (fx login bruger `80%` root font-size)

**Læsbarhed:**
- Linjehøjde ikke eksplicit defineret mange steder
- Paragraf-spacing varierer

---

### 2.3 Layout & Komponentarkitektur

**Komponenter identificeret:**
1. **Header:** `site-header.php` – fixed, glitch-logo, glass-effect on scroll
2. **Footer:** `site-footer.php` – 4-kolonne grid, responsiv
3. **Forms:** Contact form med reCAPTCHA v3
4. **Cards:** `.glass-effect` kortkomponenter (produkter, cases, priser)
5. **Modals:** AI/Gemini modal (trusselsscenarieanalyse)
6. **Login/Admin panels:** `.login-panel`, `.admin-panel`

**Grid-system:**
- Tailwind CSS grid utilities (dynamisk 1-5 kolonner)
- Ingen custom grid-definitioner

**Responsivitet:**
- Mobile-first breakpoints: `sm:`, `md:`, `lg:`, `xl:`
- Mobile menu: JavaScript toggle (off-canvas)

---

### 2.4 Interaktivitet & JavaScript

**Core scripts:**
- `assets/js/site.js` (9KB, 630 linjer)
- Functionality: Contact form, AI/Gemini integration, digital rain canvas, mobile menu, scroll effects

**Performance observations:**
- Digital rain canvas kører kontinuerligt (33ms interval)
- Ingen lazy-loading af tunge AI-features
- reCAPTCHA debug-logging kan skabe console-spam

---

### 2.5 Tilgængelighed (Aktuel Status)

**✅ Positive tiltag:**
- Semantic HTML5 elements (`<header>`, `<nav>`, `<main>`, `<footer>`, `<article>`, `<aside>`)
- `aria-label` på glitch-logo
- `aria-expanded` på mobile menu button
- `aria-current="page"` på aktiv navigation
- `role="alert"` på error-beskeder
- `:focus-visible` outline styles defineret
- `<label>` for alle form inputs

**❌ Mangler/problemer:**
1. **Keyboard navigation:** Ingen skip-link til hovedindhold
2. **Form validation:** Native HTML5 validation, men ingen visuelt tilgængelige fejlbeskeder ved siden af felter
3. **Focus management:** Modal/AlphaBot focus trap mangler
4. **ARIA-live regions:** Success/error-beskeder bruger ikke `aria-live`
5. **Color contrast:** Visse tekst-kombinationer failer WCAG AA (fx grå på mørk baggrund)
6. **Alt-text:** Ingen billeder i koden, men logo mangler muligvis tekstalternativ
7. **Canvas accessibility:** Digital rain canvas har ingen alternativ
8. **Language switching:** Ingen sprogvælger implementeret (planlagt dansk/engelsk)
9. **Reduced motion:** Ingen `prefers-reduced-motion` respekt

---

## 3. Prioriteret Forbedringsplan

### Prioriteringskategorier:
- **P0 (Kritisk):** Påvirker tilgængelighed/compliance eller kernefunktionalitet
- **P1 (Høj):** Betydelig UX-forbedring, moderat indsats
- **P2 (Medium):** Nice-to-have, lav complexity
- **P3 (Lav):** Fremtidige forbedringer, høj kompleksitet

---

## 4. Fase 1: Tilgængelighed & WCAG-compliance (P0)

### 4.1 Skip Navigation Link
**Problem:** Tastaturbrugere skal tabbe gennem hele navigation for at nå indhold
**Løsning:** Tilføj skip-link som første element i `<body>`

```html
<a href="#main-content" class="skip-link">Spring til hovedindhold</a>
```

```css
.skip-link {
  position: absolute;
  top: -40px;
  left: 0;
  background: var(--primary-accent);
  color: #000;
  padding: 8px 16px;
  text-decoration: none;
  font-weight: bold;
  z-index: 100;
}
.skip-link:focus {
  top: 0;
}
```

**Fil:** `includes/site-header.php` (efter `<body>`)
**Estimat:** 15 min

---

### 4.2 ARIA Live Regions for Formular-feedback
**Problem:** Success/error-beskeder ikke annonceret til skærmlæsere
**Løsning:** Tilføj `aria-live="polite"` og `aria-atomic="true"`

```html
<div id="contact-form-error"
     class="hidden mt-4 text-center text-red-400 border border-red-500/60 rounded-md p-4 text-sm"
     role="alert"
     aria-live="polite"
     aria-atomic="true">
</div>
```

**Filer:** `contact.php`, `assets/js/site.js`
**Estimat:** 30 min

---

### 4.3 Forbedret Kontrast (WCAG AA)
**Problem:** Flere tekst-kombinationer failer 4.5:1 ratio

**Kritiske fixes:**
| Element | Nuværende | Foreslået | Ratio |
|---------|-----------|-----------|-------|
| `.text-gray-400` på `#101419` | #9CA3AF | #B0B8C6 | 4.52:1 |
| Footer links | `text-gray-400` | `text-gray-300` | 7.1:1 |
| Placeholder text | `#567` (legacy) | `#8B92A0` | 4.6:1 |

**Implementering:** Global søg-og-erstat i `style.css` og Tailwind classes
**Estimat:** 2 timer (inkl. test)

---

### 4.4 Focus Trap for Modals
**Problem:** Tastaturnavigation kan forlade modal-vinduet
**Løsning:** Implementer focus trap med cyclic tab-navigation

```javascript
// I site.js, efter modal åbning
const focusableElements = modalContent.querySelectorAll(
  'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
);
const firstElement = focusableElements[0];
const lastElement = focusableElements[focusableElements.length - 1];

modalContent.addEventListener('keydown', (e) => {
  if (e.key === 'Tab') {
    if (e.shiftKey && document.activeElement === firstElement) {
      e.preventDefault();
      lastElement.focus();
    } else if (!e.shiftKey && document.activeElement === lastElement) {
      e.preventDefault();
      firstElement.focus();
    }
  }
});

firstElement.focus();
```

**Fil:** `assets/js/site.js` (geminiModal, alphabotContainer)
**Estimat:** 1 time

---

### 4.5 Prefers-Reduced-Motion Support
**Problem:** Animationer kan forårsage ubehag for brugere med vestibulære lidelser

**Implementering:**
```css
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }

  .section-fade-in {
    opacity: 1;
    transform: none;
  }

  #hero-canvas {
    display: none; /* Stop digital rain */
  }
}
```

**Fil:** `includes/site-header.php` (inline styles)
**Estimat:** 45 min

---

### 4.6 Form Validation Error Display
**Problem:** HTML5 validation bobler ikke vises synligt ved tastaturnavigation

**Løsning:** Custom validation med inline error messages

```html
<div class="form-field">
  <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
    Email <span class="text-red-400" aria-label="påkrævet">*</span>
  </label>
  <input type="email" id="email" name="email" required
         aria-describedby="email-error"
         class="block w-full ...">
  <div id="email-error" class="hidden text-red-400 text-xs mt-1" role="alert">
    <!-- Dynamisk fejlbesked -->
  </div>
</div>
```

**JavaScript:**
```javascript
// I site.js, submit handler
contactForm.querySelectorAll('input[required], textarea[required]').forEach(field => {
  if (!field.validity.valid) {
    const errorDiv = document.getElementById(`${field.id}-error`);
    errorDiv.textContent = field.validationMessage;
    errorDiv.classList.remove('hidden');
    field.setAttribute('aria-invalid', 'true');
  }
});
```

**Filer:** `contact.php`, `assets/js/site.js`
**Estimat:** 2 timer

---

## 5. Fase 2: UX-forbedringer & Navigation (P1)

### 5.1 Breadcrumb Navigation
**Problem:** Ingen kontekstuel navigation på dybereliggende sider
**Fordel:** Forbedrer orienterbarhed og SEO

**Implementering:**
```html
<nav aria-label="Breadcrumb" class="container mx-auto px-4 pt-24 pb-4">
  <ol class="flex items-center space-x-2 text-sm">
    <li><a href="/" class="text-gray-400 hover:text-white">Hjem</a></li>
    <li class="text-gray-600">/</li>
    <li class="text-white" aria-current="page">Produkter</li>
  </ol>
</nav>
```

**Structured data:**
```json
{
  "@type": "BreadcrumbList",
  "itemListElement": [...]
}
```

**Filer:** `includes/site-header.php` (ny funktion), alle sider
**Estimat:** 3 timer

---

### 5.2 Forbedret Mobile Menu UX
**Problem:** Mobile menu fylder hele skærm og blokkerer scrolling

**Forbedringer:**
1. Slide-in animation i stedet for toggle
2. Close button mere prominent
3. Overlay med dimming
4. Trap focus i menuen

**Estimat:** 2 timer

---

### 5.3 Loading States for AI-features
**Problem:** AlphaBot/Gemini requests uden visuelt feedback før loader vises

**Løsning:** Optimistic UI updates + skeleton screens

```html
<div class="skeleton-loader">
  <div class="skeleton-line"></div>
  <div class="skeleton-line"></div>
  <div class="skeleton-line short"></div>
</div>
```

**Estimat:** 1.5 timer

---

### 5.4 Sticky CTA (Call-to-Action)
**Problem:** Kontakt-knap gemt i scroll på lange sider

**Løsning:** Floating action button (bottom-right)

```html
<a href="contact.php"
   class="fixed bottom-6 right-6 bg-amber-400 text-black p-4 rounded-full shadow-2xl hover:scale-110 transition-transform z-40"
   aria-label="Kontakt os">
  <svg><!-- Mail icon --></svg>
</a>
```

**Kun synlig efter 50% scroll**

**Estimat:** 1 time

---

## 6. Fase 3: Performance & Optimering (P1-P2)

### 6.1 Digital Rain Performance
**Problem:** Kontinuerlig canvas rendering (~30 FPS) påvirker battery/CPU

**Løsninger:**
1. Pause når tab er inaktiv (Visibility API)
2. Reducér opdateringsfrekvens til 20 FPS (50ms)
3. Lazy-initialize kun når hero er synlig (Intersection Observer)

```javascript
let animationId;
const drawRain = () => {
  // ... existing code
  animationId = requestAnimationFrame(drawRain);
};

document.addEventListener('visibilitychange', () => {
  if (document.hidden) {
    cancelAnimationFrame(animationId);
  } else {
    drawRain();
  }
});
```

**Estimat:** 2 timer

---

### 6.2 Lazy Loading for AI-komponenter
**Problem:** AlphaBot og Gemini-scripts loader ved pageload selvom ikke brugt

**Løsning:** Dynamic import når bruger interagerer

```javascript
alphaToggleBtn?.addEventListener('click', async () => {
  if (!window.alphabotInitialized) {
    const { initAlphaBot } = await import('./alphabot-module.js');
    initAlphaBot(alphaContainer, messagesDiv, inputEl);
    window.alphabotInitialized = true;
  }
  alphaContainer.classList.toggle('open');
});
```

**Estimat:** 3 timer (refactoring)

---

### 6.3 Image Optimization
**Problem:** Ingen billeder endnu, men logo skal optimeres

**Checklist:**
- [ ] Logo som SVG i stedet for PNG
- [ ] Lazy-load future images (`loading="lazy"`)
- [ ] Responsive images med `<picture>` + WebP
- [ ] Icon system (SVG sprite eller icon font)

**Estimat:** 2 timer (når billeder tilføjes)

---

## 7. Fase 4: Design Konsistens & Skalerbarhed (P2)

### 7.1 Unified CSS Variable System
**Problem:** To separate farvesystemer (`style.css` legacy + `site-header.php` moderne)

**Løsning:** Fælles variabledefinition

```css
:root {
  /* Brand colors */
  --brand-dark: #101419;
  --brand-gold: #FFC700;
  --brand-gold-dark: #bfa650;
  --brand-green: #00aa55;

  /* Semantic colors */
  --color-bg-primary: var(--brand-dark);
  --color-bg-glass: rgba(22, 28, 39, 0.6);
  --color-text-high: #EAEAEA;
  --color-text-medium: #B0B8C6; /* Fixed contrast */
  --color-text-low: #6B7280;
  --color-accent-primary: var(--brand-gold);
  --color-accent-success: #22c55e;
  --color-accent-error: #ef4444;

  /* Effects */
  --effect-glitch-green: #003d00;
  --effect-glitch-fire: #ffac00;
  --effect-digital-rain: #008000;

  /* Glass morphism */
  --glass-border: rgba(255, 255, 255, 0.1);
  --glass-bg: var(--color-bg-glass);
}
```

**Migrer alle legacy references:**
- `--bg-dark` → `--color-bg-primary`
- `--gold` → `--color-accent-primary`

**Estimat:** 4 timer (search-replace + test)

---

### 7.2 Component Library Documentation
**Problem:** Ingen dokumentation af UI-komponenter

**Løsning:** Opret `docs/COMPONENT-LIBRARY.md`

**Indhold:**
- Glass-effect kort
- Knap-varianter (primary, secondary, danger)
- Form-elementer
- Alert/notification boxes
- Modal patterns
- Typografi-hierarki

**Inkluder:**
- HTML markup examples
- CSS classes
- Accessibility notes
- Do's and Don'ts

**Estimat:** 3 timer

---

### 7.3 Dark Mode Toggle (Fremtidig)
**Status:** Planlagt men ikke implementeret

**Implementering:**
```javascript
const themeToggle = document.getElementById('theme-toggle');
const root = document.documentElement;

themeToggle?.addEventListener('click', () => {
  const isDark = root.getAttribute('data-theme') === 'dark';
  root.setAttribute('data-theme', isDark ? 'light' : 'dark');
  localStorage.setItem('theme', isDark ? 'light' : 'dark');
});

// På pageload
const savedTheme = localStorage.getItem('theme') || 'dark';
root.setAttribute('data-theme', savedTheme);
```

**CSS:**
```css
[data-theme="light"] {
  --color-bg-primary: #ffffff;
  --color-text-high: #1a1a1a;
  /* ... */
}
```

**Estimat:** 5 timer (komplet implementering)

---

## 8. Fase 5: Internationalisering (P2)

### 8.1 Language Switcher (Dansk/Engelsk)
**Problem:** Kun danske tekster, ingen sprogvælger

**Løsning:**
1. PHP language detection og switching
2. JSON language files
3. Header language toggle

```php
// includes/i18n.php
function bbx_get_text($key, $lang = 'da') {
    $translations = [
        'da' => json_decode(file_get_contents(__DIR__ . '/lang/da.json'), true),
        'en' => json_decode(file_get_contents(__DIR__ . '/lang/en.json'), true)
    ];
    return $translations[$lang][$key] ?? $key;
}

$current_lang = $_SESSION['lang'] ?? 'da';
function t($key) {
    global $current_lang;
    return bbx_get_text($key, $current_lang);
}
```

**Header toggle:**
```html
<div class="language-switcher">
  <button data-lang="da" class="active">DA</button>
  <button data-lang="en">EN</button>
</div>
```

**Estimat:** 8 timer (setup + translations)

---

## 9. Test & Kvalitetssikring

### 9.1 Accessibility Audit Tools
**Værktøjer:**
- [ ] WAVE browser extension
- [ ] axe DevTools
- [ ] Lighthouse accessibility score (target: 95+)
- [ ] NVDA/VoiceOver skærmlæser-test
- [ ] Keyboard-only navigation test

**Estimat:** 3 timer pr. iteration

---

### 9.2 Cross-browser Testing
**Browsers:**
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile Safari (iOS 15+)
- Chrome Android (latest)

**Responsive breakpoints:**
- Mobile: 375px, 414px
- Tablet: 768px, 1024px
- Desktop: 1280px, 1440px, 1920px

**Estimat:** 4 timer

---

### 9.3 Performance Benchmarks
**Metrics:**
- Lighthouse Performance score: >90
- First Contentful Paint (FCP): <1.5s
- Largest Contentful Paint (LCP): <2.5s
- Cumulative Layout Shift (CLS): <0.1
- Total Blocking Time (TBT): <200ms

**Estimat:** 2 timer (baseline + optimering)

---

## 10. Implementeringsplan & Tidsestimat

### Sprint 1 (Tilgængelighed - P0)
**Mål:** WCAG 2.1 AA compliance
**Varighed:** 1 uge

| Opgave | Estimat | Status |
|--------|---------|--------|
| Skip link | 15 min | ⏳ |
| ARIA live regions | 30 min | ⏳ |
| Kontrast fixes | 2 timer | ⏳ |
| Focus trap | 1 time | ⏳ |
| Reduced motion | 45 min | ⏳ |
| Form validation | 2 timer | ⏳ |
| **Total** | **~7 timer** | |

---

### Sprint 2 (UX & Navigation - P1)
**Mål:** Forbedret brugeroplevelse
**Varighed:** 1 uge

| Opgave | Estimat | Status |
|--------|---------|--------|
| Breadcrumbs | 3 timer | ⏳ |
| Mobile menu UX | 2 timer | ⏳ |
| AI loading states | 1.5 timer | ⏳ |
| Sticky CTA | 1 time | ⏳ |
| **Total** | **~7.5 timer** | |

---

### Sprint 3 (Performance - P1/P2)
**Mål:** Optimeret performance
**Varighed:** 1 uge

| Opgave | Estimat | Status |
|--------|---------|--------|
| Digital rain opt. | 2 timer | ⏳ |
| AI lazy loading | 3 timer | ⏳ |
| Image optimization | 2 timer | ⏳ |
| **Total** | **~7 timer** | |

---

### Sprint 4 (Design System - P2)
**Mål:** Konsistent designsystem
**Varighed:** 1.5 uge

| Opgave | Estimat | Status |
|--------|---------|--------|
| CSS variables migration | 4 timer | ⏳ |
| Component docs | 3 timer | ⏳ |
| Dark mode (optional) | 5 timer | ⏳ |
| **Total** | **~12 timer** | |

---

### Sprint 5 (i18n - P2/P3)
**Mål:** Flersprogethed
**Varighed:** 1.5 uge

| Opgave | Estimat | Status |
|--------|---------|--------|
| Language system | 8 timer | ⏳ |
| Translations | 4 timer | ⏳ |
| **Total** | **~12 timer** | |

---

## 11. Preview & Feedback Strategi

### 11.1 Automatisk Preview ved PR
**Setup:** GitHub Actions workflow

```yaml
name: UI Preview

on:
  pull_request:
    paths:
      - '**.css'
      - '**.php'
      - '**.js'

jobs:
  preview:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Deploy to Vercel Preview
        uses: amondnet/vercel-action@v20
        with:
          vercel-token: ${{ secrets.VERCEL_TOKEN }}
          vercel-org-id: ${{ secrets.ORG_ID }}
          vercel-project-id: ${{ secrets.PROJECT_ID }}
```

**Output:** Preview URL i PR-kommentar

---

### 11.2 Visuel Regression Testing
**Tool:** Percy.io eller Chromatic

**Setup:**
1. Screenshot baseline i `main` branch
2. Automatisk sammenligning ved PR
3. Visual diff rapporteres i PR

**Estimat:** 2 timer setup

---

### 11.3 Manual Review Checklist
Ved hver PR til `main`:

```markdown
## UI/UX Review Checklist

### Visuel
- [ ] Screenshot af ændringer vedlagt
- [ ] Mobile og desktop visninger dækket
- [ ] Dark mode testet (hvis relevant)
- [ ] Print-layout kontrolleret

### Funktionalitet
- [ ] Alle interaktive elementer testet
- [ ] Forms validerer korrekt
- [ ] Error states vises korrekt
- [ ] Loading states fungerer

### Tilgængelighed
- [ ] Keyboard navigation virker
- [ ] Skærmlæser-test gennemført
- [ ] Kontrast kontrolleret
- [ ] Focus indicators synlige

### Performance
- [ ] Lighthouse score >90
- [ ] Ingen console errors
- [ ] Network requests optimeret
```

---

## 12. Succeskriterier

### 12.1 Kvantitative Metrics
- **Lighthouse Accessibility:** 95+ (nuværende: ukendt)
- **Lighthouse Performance:** 90+ (nuværende: ukendt)
- **WCAG 2.1 AA compliance:** 100% (nuværende: ~70%)
- **Mobile usability score:** 100/100
- **PageSpeed Insights Mobile:** >75

---

### 12.2 Kvalitative Metrics
- Bruger-feedback via kontaktformular (track "svært at bruge" kommentarer)
- Session recordings (Hotjar/Microsoft Clarity) viser færre forvirrede flows
- Bounce rate reduktion på mobile (<60%)
- Tid til completion af kontaktformular (<2 min)

---

## 13. Risici & Mitigering

### 13.1 Backwards Compatibility
**Risiko:** Ændringer bryder eksisterende admin-side (`style.css` legacy)

**Mitigering:**
- Opret separat `admin-styles.css` for legacy UI
- Test admin-login og dashboard efter hver ændring
- Gradvis migration, ikke "big bang" rewrite

---

### 13.2 Performance Regression
**Risiko:** Nye features forringer load time

**Mitigering:**
- Performance budget (max 200KB initial load)
- Lighthouse CI som blocker i GitHub Actions
- Bundle size monitoring (bundlesize.io)

---

### 13.3 Scope Creep
**Risiko:** "Bare lige" features forsinker launch

**Mitigering:**
- Streng prioritering (P0 først, P3 sidst)
- 2-ugers sprint cycles med review
- Feature freeze 1 uge før release

---

## 14. Næste Skridt

### Umiddelbart (før kode-ændringer)
1. ✅ Opret `feat/ui-enhancements` branch
2. ✅ Baseline Lighthouse audit (gem rapport)
3. ✅ Opsæt Percy/Chromatic for visual regression
4. ✅ Kommunikér plan til stakeholders (GitHub Discussion)

### Uge 1 (Sprint 1)
1. Implementér P0 tilgængelighedsforbedringer
2. Daglig commit med beskrivende messages
3. Preview URL i PR efter 3 dages arbejde
4. Få feedback fra projektleder

---

## 15. Kontakt & Godkendelse

**Denne plan kræver godkendelse før implementering.**

**Spørgsmål eller feedback:**
- GitHub Issue: #[nummer]
- Email: ops@blackbox.codes
- Slack: #aig-frontend-team

**Forventet review-tid:** 2-3 hverdage

---

**Version log:**
- v1.0 (2025-11-22): Initial analyse og handlingsplan

**Næste revision:** Efter Sprint 1 completion
