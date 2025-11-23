# Sprint 5 — Web/UX Implementation Plan

**Version**: v1.0.0-sprint5  
**Dato**: 2025-11-23  
**Status**: Planning Phase  
**Agent**: ALPHA-Web-Diagnostics-Agent

---

## Executive Summary

Sprint 5 fokuserer på at transformere ALPHA Interface GUI (Blackbox EYE™) fra en teknisk platform til en **fuldt konverterings-optimeret** website med moderne UX/UI, SEO-fundamenter og lead-generation capabilities. 

Baseret på UX/UI-analyse, marketing/branding-rapport og forretningsplanen (concept_plan.md) er følgende prioriteter identificeret:

1. **Navigation & Informationsarkitektur** – Forenklet menu-struktur med max 5 hovedpunkter
2. **Lead-Generation Elementer** – Demo-booking, priskalkulator og "Gratis sårbarhedstjek" CTA
3. **Content & SEO** – Blog/knowledge center med threat intel, whitepapers og case studies
4. **Tilgængelighed & Performance** – WCAG 2.1 AA compliance, Core Web Vitals optimering

Sprint 5 skal levere en MVP-version af disse elementer, der kan itereres i efterfølgende sprints.

---

## 1. Navigation & Informationsarkitektur

### 1.1 Ny Menu-struktur

**Problem:**  
Nuværende navigation har 7+ items og er uoverskuelig, især på mobil. UX-audit anbefaler max 5 hovedpunkter for at reducere kognitiv belastning.

**Foreslået struktur:**

```
HOVEDMENU (max 5 items):
├── Løsninger          (ny gruppering)
│   ├── GreyEYE AI-Platform
│   ├── Penetration & Vulnerability Engine (PVE)
│   ├── ID-Matrix
│   ├── Awareness Training (AUT)
│   └── Operational Bridge
│
├── Cases & Resultater (ny side)
│   ├── Kundecase: Offentlig sektor
│   ├── Kundecase: Enterprise
│   └── Threat Reports (seneste fund)
│
├── Priser & Demo      (kombineret lead-gen)
│   ├── Pakke-oversigt (MVP, Standard, Premium, Enterprise)
│   ├── Priskalkulator (selvbetjent estimator)
│   └── Book Demo (Calendly integration)
│
├── Ressourcer         (content hub)
│   ├── Knowledge Center / Blog
│   ├── Whitepapers & Downloads
│   ├── FAQ
│   └── Compliance Guide (NIS2, GDPR, ISO 27001)
│
└── Om & Kontakt       (trust signals)
    ├── Om Blackbox EYE
    ├── Team & Advisory Board
    ├── Certificeringer & Partnere
    └── Kontakt os
```

**Implementering:**

- **Fil:** `includes/site-header.php`
- **Ny fil:** `solutions.php`, `resources.php`, `pricing-demo.php`
- **Teknik:** PHP-baseret dynamisk menu med aktiv state detection
- **Responsivt:** Mobile burger-menu med slide-in animation

**Scope:**
- Opdater `site-header.php` med ny menu-struktur
- Opret placeholder-sider for nye menu-items
- Implementer breadcrumb-navigation på alle undersider
- Tilføj structured data (schema.org BreadcrumbList)

**Estimeret Kompleksitet:** Medium  
**Effort:** 8–12 timer (inkl. test)  
**Afhængigheder:** Breadcrumb PHP-funktion (fra Sprint 2 backlog)

---

### 1.2 Sticky CTA-knap ("Book Demo")

**Problem:**  
Kontaktformular er gemt i scroll på lange sider. Brugere skal navigere manuelt til `/contact.php` for at initiere kontakt.

**Løsning:**  
Implementer en **floating action button (FAB)** i nederste højre hjørne, der vises efter 50% scroll og linker til demo-booking eller kontakt.

**Design:**
```html
<a href="#demo-booking" 
   id="sticky-cta"
   class="fixed bottom-6 right-6 bg-amber-400 text-black px-6 py-4 rounded-full shadow-2xl hover:scale-110 transition-transform z-40 opacity-0"
   aria-label="Book sikkerhedsdemo">
  <svg class="w-5 h-5 inline-block mr-2"><!-- Calendar icon --></svg>
  <span class="hidden lg:inline">Book Demo</span>
  <span class="lg:hidden">Demo</span>
</a>
```

**Funktionalitet:**
- Vises ved 50% viewport scroll (JavaScript IntersectionObserver eller scroll event)
- Skjules når footer er synlig (inden for 200px)
- Responsiv tekst: "Demo" (mobil), "Book Demo" (desktop)
- Smooth scroll til booking-formular eller åbner modal

**Scope:**
- JavaScript scroll handler i `assets/js/site.js`
- CSS for FAB med hover/active states
- Integration med demo-booking flow

**Estimeret Kompleksitet:** Low  
**Effort:** 2–3 timer  
**Afhængigheder:** Demo-booking flow (sektion 2.1)

---

### 1.3 Mobil Burger-menu Optimering

**Problem:**  
Nuværende mobile menu toggle fylder hele skærm uden transition og låser scrolling.

**Forbedringer:**

1. **Slide-in animation** (fra højre) i stedet for instant show/hide
2. **Dark overlay** med fade-in/out effekt over hovedindhold
3. **ESC-tast lukker menu** med fokus-restaurering
4. **Klik udenfor menu lukker den** (overlay click handler)
5. **Focus trap** inden i åben menu (Tab cykler gennem items)

**Teknisk implementering:**

```javascript
// I site.js
const mobileMenu = document.getElementById('mobile-menu');
const menuOverlay = document.createElement('div');
menuOverlay.className = 'fixed inset-0 bg-black/70 z-30 hidden';

toggleBtn.addEventListener('click', () => {
  mobileMenu.classList.toggle('translate-x-full');
  menuOverlay.classList.toggle('hidden');
  document.body.style.overflow = mobileMenu.classList.contains('translate-x-full') ? '' : 'hidden';
});

menuOverlay.addEventListener('click', closeMobileMenu);
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && !mobileMenu.classList.contains('translate-x-full')) {
    closeMobileMenu();
  }
});
```

**Scope:**
- Refactor mobile menu JavaScript
- CSS transitions med transform/opacity
- ARIA-attributter (aria-expanded, aria-hidden)
- Keyboard navigation support

**Estimeret Kompleksitet:** Medium  
**Effort:** 4–6 timer  
**Afhængigheder:** Ingen

---

## 2. Lead-Generation Elementer

### 2.1 Demo-Booking Flow (Calendly Integration)

**Mål:**  
Gør det nemt for potentielle kunder at booke et møde med sales/sikkerhedsrådgivere uden email ping-pong.

**Løsning:**  
Integrér **Calendly** (eller lignende scheduling tool) på en dedikeret "Book Demo" side og som modal fra sticky CTA.

**Implementering:**

**Option A: Embedded Calendly Widget**
```html
<!-- På pricing-demo.php eller dedikeret /demo.php -->
<div class="calendly-embed-wrapper">
  <div class="calendly-inline-widget" 
       data-url="https://calendly.com/blackbox-eye/demo" 
       style="min-width:320px;height:700px;">
  </div>
</div>
<script type="text/javascript" src="https://assets.calendly.com/assets/external/widget.js" async></script>
```

**Option B: PopUp Widget fra CTA**
```javascript
// Fra sticky CTA eller "Book Demo" knap
Calendly.initPopupWidget({
  url: 'https://calendly.com/blackbox-eye/demo'
});
```

**Pre-fill Data:**  
Hvis bruger allerede har udfyldt kontaktformular, send data til Calendly via UTM-params eller custom fields:
- Navn
- Email
- Virksomhed
- Interesseområde (MVP, Premium, Enterprise)

**Scope:**
- Opret Calendly-konto og konfigurér event-typer (30 min demo, 60 min deep-dive)
- Implementer widget på `demo.php` eller `pricing-demo.php`
- Test responsivitet på mobil/tablet
- Tilføj tracking (Google Analytics event ved booking)

**Estimeret Kompleksitet:** Low  
**Effort:** 3–4 timer (inkl. Calendly setup)  
**Afhængigheder:** Calendly Pro-konto (eller gratis tier)

---

### 2.2 Priskort + Priskalkulator-skelet

**Mål:**  
Transparent prissætning øger konvertering. UX-audit fremhæver at Darktrace/SentinelOne viser priser eller tilbyder kalkulatorer.

**Fase 1: Statiske Priskort (Sprint 5 MVP)**

Baseret på `concept_plan.md`, skal følgende pakker præsenteres:

**MVP-segment:**
- MVP-Basis: 1.799 DKK/md
- MVP-Pro: 3.499 DKK/md
- MVP-Premium: 5.999 DKK/md

**Premium-segment:**
- Standard: 9.900 DKK/md
- Premium: 18.900 DKK/md
- Enterprise: 39.900 DKK/md (eller efter aftale)

**Design:**  
3-kolonne grid (desktop), stacked cards (mobil) med:
- Pakkenavn & pris
- Liste af inkluderede moduler (GreyEYE, PVE, ID-Matrix, AUT osv.)
- Målgruppe (SMV, Enterprise, Offentlig sektor)
- CTA-knap: "Vælg pakke" → demo-booking

**Eksempel markup:**
```html
<div class="pricing-card glass-effect">
  <div class="pricing-header">
    <h3 class="text-2xl font-bold">Premium</h3>
    <div class="price">
      <span class="amount">18.900</span>
      <span class="currency">DKK</span>
      <span class="period">/md</span>
    </div>
  </div>
  <ul class="features">
    <li>✅ GreyEYE AI-assistent (24/7)</li>
    <li>✅ PVE (automatisk pentest)</li>
    <li>✅ ID-Matrix adgangskontrol</li>
    <li>✅ AUT træningsmodul</li>
    <li>✅ Prioriteret support</li>
  </ul>
  <a href="#demo-booking" class="cta-button">Vælg Premium</a>
</div>
```

**Scope:**
- Opdater eksisterende `pricing.php` eller opret ny `pricing-demo.php`
- Design priskort med Tailwind CSS (glass-effect)
- Tilføj hover-effekter og highlight på "Premium" (most popular)
- Structured data (schema.org Offer)

**Estimeret Kompleksitet:** Low  
**Effort:** 4–6 timer

---

**Fase 2: Interaktiv Priskalkulator (Sprint 6+)**

En selvbetjent kalkulator hvor kunde kan:
- Vælge antal brugere/enheder
- Vælge moduler (á la carte eller pakke)
- Tilføje add-ons (ekstra pentest, VIP support)
- Få estimeret månedspris i realtid

**Teknologi:**  
JavaScript/React-baseret kalkulator med pricing logic i frontend (kan senere integreres med backend API for dynamisk pricing).

**Estimeret Kompleksitet:** High  
**Effort:** 12–16 timer (scope for fremtidigt sprint)

---

### 2.3 "Gratis Sårbarhedstjek" CTA

**Mål:**  
Lead-magnet der giver værdi upfront og indfanger email for nurturing.

**Koncept:**  
En simpel formular hvor virksomheder kan indtaste deres domæne og få en **gratis AI-pentest report** (basic version af PVE output).

**Flow:**
1. Bruger indtaster domæne (fx `example.com`)
2. Systemet kører en basic vulnerability scan (DNS check, SSL rating, common CVEs)
3. Email sendes med PDF-rapport + CTA til at booke demo for fuld PVE-scan

**Implementering (MVP i Sprint 5):**

- **Formular:** På index.php hero-sektion eller dedikeret `/free-scan.php`
- **Backend:** PHP script der caller eksterne API'er:
  - SSL Labs API (gratis SSL check)
  - Shodan API (basic exposure check)
  - Eller mock data for MVP
- **Output:** Email med simpel tekstrapport (PDF-generering i senere sprint)

**Eksempel markup:**
```html
<form id="vulnerability-check-form" class="max-w-md mx-auto mt-8">
  <h3 class="text-xl font-semibold mb-4">Få gratis sårbarhedstjek</h3>
  <div class="flex gap-2">
    <input type="text" 
           name="domain" 
           placeholder="example.com" 
           class="flex-1 px-4 py-3 bg-gray-800 border border-gray-700 rounded-md"
           required>
    <button type="submit" class="px-6 py-3 bg-amber-400 text-black font-semibold rounded-md hover:bg-amber-500">
      Tjek nu
    </button>
  </div>
  <p class="text-xs text-gray-500 mt-2">Vi gemmer ikke dit domæne. Rapporten sendes til din email.</p>
</form>
```

**Scope:**
- Formular på index.php eller dedikeret side
- PHP backend til at kalde SSL Labs API
- Email-integration (reuse eksisterende SMTP fra contact-submit.php)
- Basic rate limiting (max 3 scans per IP per dag)

**Estimeret Kompleksitet:** Medium  
**Effort:** 6–8 timer  
**Afhængigheder:** SMTP-konfiguration fra Sprint 4

---

## 3. Content & SEO

### 3.1 Blog / Knowledge Center Skelet

**Mål:**  
Etablere thought leadership og tiltrække organisk trafik via SEO-optimeret content.

**Struktur:**

```
/resources/
  ├── index.php           (Knowledge Center oversigt)
  ├── blog/
  │   ├── index.php       (Blog listing med pagination)
  │   └── [slug].php      (Enkelt blogpost template)
  ├── whitepapers/
  │   ├── index.php       (Whitepaper library)
  │   └── download.php    (Gated download med email-capture)
  └── threat-reports/
      └── index.php       (Månedlige threat intel rapporter)
```

**Content Database:**

Opret ny MySQL tabel:
```sql
CREATE TABLE blog_posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) UNIQUE NOT NULL,
  excerpt TEXT,
  content LONGTEXT NOT NULL,
  author VARCHAR(100),
  category ENUM('threat-intel', 'tutorials', 'case-study', 'news') DEFAULT 'news',
  published_at DATETIME,
  updated_at DATETIME,
  featured_image VARCHAR(255),
  meta_description VARCHAR(160),
  tags VARCHAR(255),
  INDEX(slug),
  INDEX(published_at)
);
```

**MVP Features:**

- Blog listing med pagination (10 posts per side)
- Enkelt blogpost template med:
  - Featured image
  - Publish date & author
  - Table of contents (auto-genereret fra H2/H3)
  - Social share buttons (LinkedIn, Twitter/X)
  - Related posts (samme kategori)
- Kategorier: Threat Intel, Tutorials, Case Studies, News
- Search funktion (simpel LIKE-query i MVP)

**Scope:**
- Database migration script
- Blog listing page (`/resources/blog/index.php`)
- Blog post template (`/resources/blog/post.php` med dynamic slug routing)
- Admin interface til at oprette posts (simpel CRUD i eksisterende admin.php)
- RSS feed (`/resources/blog/feed.xml`)

**Estimeret Kompleksitet:** High  
**Effort:** 16–20 timer  
**Afhængigheder:** Database access, eksisterende auth system

---

### 3.2 Første 3–5 Indholdssektioner

**Planlæg content pipeline for Q1 2026:**

| Kategori | Emne | Format | Estimated Length | SEO Target |
|----------|------|--------|------------------|------------|
| **Threat Intel** | "Top 10 OWASP sårbarheder i 2025" | Blog post | 1500 ord | "OWASP sårbarheder" |
| **Case Study** | "Hvordan Kommune X reducerede angrebsfladen med 70%" | Blog post + PDF | 2000 ord | "cybersikkerhed kommune" |
| **Whitepaper** | "NIS2 Compliance Guide for offentlige institutioner" | Gated PDF | 3000 ord | "NIS2 compliance" |
| **Tutorial** | "Sådan implementerer du Zero Trust med ID-Matrix" | Blog post + video | 2500 ord | "zero trust arkitektur" |
| **Threat Report** | "Månedlig trusselsintelligens: November 2025" | Blog post | 1000 ord | "threat intelligence rapport" |

**Content Creation Workflow:**

1. **Research & Outline** (1–2 timer per post)
2. **Draft skrivning** (3–5 timer per post)
3. **Technical review** (1 time)
4. **SEO optimization** (title, meta description, internal links)
5. **Billeder & grafik** (custom graphics eller stock photos)
6. **Publicering & promotion** (LinkedIn, Twitter, email newsletter)

**Scope:**
- Opret content calendar i Google Sheets eller Notion
- Skriv mindst 2–3 initial posts for launch
- Design blog post template med SEO best practices
- Publicer første whitepaper som lead-magnet

**Estimeret Kompleksitet:** High (content creation er ressourcekrævende)  
**Effort:** 20–30 timer for første 3 posts + whitepaper  
**Afhængigheder:** Copywriter eller AI-assisteret content generation

---

### 3.3 SEO Fundamenter: robots.txt, Sitemap & Schema.org

**Problem:**  
Nuværende `robots.txt` blokerer søgemaskiner, og der er ingen XML-sitemap eller structured data.

#### 3.3.1 Opdater robots.txt

**Nuværende:**
```
User-agent: *
Disallow: /
```

**Ny (åben for indexering):**
```
User-agent: *
Allow: /
Disallow: /admin.php
Disallow: /api/
Disallow: /db/
Disallow: /includes/
Disallow: /.well-known/
Disallow: /test*
Crawl-delay: 1

Sitemap: https://blackbox.codes/sitemap.xml
```

**Scope:**
- Opdater `/robots.txt`
- Test med Google Search Console

**Estimeret Kompleksitet:** Low  
**Effort:** 30 minutter

---

#### 3.3.2 Generer XML Sitemap

**Dynamisk PHP sitemap-generator:**

```php
<?php
// sitemap.php
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://blackbox.codes/</loc>
    <lastmod><?= date('Y-m-d') ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
  </url>
  <!-- Static pages -->
  <url><loc>https://blackbox.codes/about.php</loc><priority>0.8</priority></url>
  <url><loc>https://blackbox.codes/pricing.php</loc><priority>0.9</priority></url>
  <url><loc>https://blackbox.codes/contact.php</loc><priority>0.7</priority></url>
  
  <!-- Dynamic blog posts -->
  <?php
  // Hent blog posts fra database
  $posts = $db->query("SELECT slug, updated_at FROM blog_posts WHERE published_at IS NOT NULL ORDER BY published_at DESC");
  foreach ($posts as $post) {
    echo "<url>";
    echo "<loc>https://blackbox.codes/resources/blog/{$post['slug']}.php</loc>";
    echo "<lastmod>{$post['updated_at']}</lastmod>";
    echo "<changefreq>monthly</changefreq>";
    echo "<priority>0.6</priority>";
    echo "</url>";
  }
  ?>
</urlset>
```

**Scope:**
- Opret dynamisk sitemap.php
- Tilføj til robots.txt
- Submit til Google Search Console & Bing Webmaster Tools

**Estimeret Kompleksitet:** Low  
**Effort:** 2–3 timer  
**Afhængigheder:** Database connection (db.php)

---

#### 3.3.3 Implementer Schema.org Structured Data

**Typer af structured data:**

1. **Organization** (på alle sider)
```json
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Blackbox EYE",
  "url": "https://blackbox.codes",
  "logo": "https://blackbox.codes/assets/images/logo.svg",
  "sameAs": [
    "https://www.linkedin.com/company/blackbox-eye"
  ],
  "contactPoint": {
    "@type": "ContactPoint",
    "telephone": "+45-XXXXXXXX",
    "contactType": "Sales",
    "email": "ops@blackbox.codes"
  }
}
```

2. **Product/Service** (på pricing.php)
```json
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "GreyEYE Premium Package",
  "description": "AI-drevet cybersikkerhedsplatform med 24/7 overvågning",
  "offers": {
    "@type": "Offer",
    "price": "18900",
    "priceCurrency": "DKK",
    "priceSpecification": {
      "@type": "UnitPriceSpecification",
      "billingDuration": "P1M"
    }
  }
}
```

3. **BreadcrumbList** (på alle undersider)
4. **Article** (på blog posts)
5. **FAQPage** (på faq.php)

**Implementering:**  
Tilføj JSON-LD script tags i `<head>` via PHP-funktion i `includes/site-header.php`.

**Scope:**
- Opret `includes/structured-data.php` med helper-funktioner
- Implementer på 5–10 vigtigste sider
- Test med Google Rich Results Test

**Estimeret Kompleksitet:** Medium  
**Effort:** 6–8 timer  
**Afhængigheder:** Ingen

---

## 4. Tilgængelighed & Performance

### 4.1 WCAG 2.1 AA Compliance – P0 Fixes

**Baseret på UX-audit, følgende kritiske fixes:**

#### 4.1.1 Skip Navigation Link

Tilføj skip-link som første element i `<body>`:

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

**Estimeret Kompleksitet:** Low  
**Effort:** 30 minutter

---

#### 4.1.2 Forbedret Kontrast (WCAG AA)

**Kritiske fixes:**

| Element | Nuværende | Foreslået | Ratio |
|---------|-----------|-----------|-------|
| `.text-gray-400` på `#101419` | #9CA3AF | #B0B8C6 | 4.52:1 ✅ |
| Footer links | `text-gray-400` | `text-gray-300` | 7.1:1 ✅ |
| Placeholder text | `#567` | `#8B92A0` | 4.6:1 ✅ |

**Implementering:**  
Global search-replace i `style.css` og opdater Tailwind utility classes.

**Estimeret Kompleksitet:** Low  
**Effort:** 2 timer (inkl. test med Lighthouse/axe-core)

---

#### 4.1.3 ARIA Live Regions for Formular-feedback

Tilføj `aria-live="polite"` på success/error-beskeder:

```html
<div id="contact-form-error"
     class="hidden mt-4 text-center text-red-400 border border-red-500/60 rounded-md p-4 text-sm"
     role="alert"
     aria-live="polite"
     aria-atomic="true">
</div>
```

**Estimeret Kompleksitet:** Low  
**Effort:** 1 time

---

#### 4.1.4 Focus Trap for Modals

Implementer focus trap i AlphaBot og Gemini modals:

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
```

**Estimeret Kompleksitet:** Medium  
**Effort:** 2 timer

---

#### 4.1.5 Prefers-Reduced-Motion Support

Stop animationer for brugere med vestibulære lidelser:

```css
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }

  #hero-canvas {
    display: none; /* Stop digital rain */
  }
}
```

**Estimeret Kompleksitet:** Low  
**Effort:** 1 time

---

**Total for WCAG P0 Fixes:**  
**Estimeret Kompleksitet:** Medium  
**Total Effort:** 6–8 timer

---

### 4.2 Core Web Vitals & Performance Optimering

**Mål:**  
Opnå Lighthouse Performance score > 90 på desktop og > 80 på mobil.

#### 4.2.1 Lazy Loading for Billeder (Fremtidige)

Når billeder tilføjes:

```html
<img src="image.webp" 
     alt="Beskrivelse" 
     loading="lazy" 
     width="800" 
     height="600">
```

**Estimeret Kompleksitet:** Low  
**Effort:** 1 time

---

#### 4.2.2 Minificering & Bundling

**Assets der skal optimeres:**

- `assets/js/site.js` (9KB) → minify til ~6KB
- `style.css` (13KB) → minify + purge unused Tailwind classes
- Inline CSS i `site-header.php` → ekstraher til separat fil

**Værktøjer:**

- **JavaScript:** Terser eller UglifyJS
- **CSS:** cssnano eller PurgeCSS
- **HTML:** html-minifier (optional)

**Implementering:**

Opret build script `scripts/build-assets.sh`:

```bash
#!/bin/bash
# Minify JavaScript
npx terser assets/js/site.js -o assets/js/site.min.js --compress --mangle

# Minify CSS
npx cssnano style.css style.min.css

echo "Assets minified successfully"
```

Opdater `site-header.php` til at loade `.min.js` og `.min.css` i production.

**Estimeret Kompleksitet:** Low  
**Effort:** 2–3 timer  
**Afhængigheder:** Node.js & npm

---

#### 4.2.3 HTTP/2 & Brotli Compression

**Serverside configuration:**

Hvis Apache/Nginx understøtter HTTP/2 og Brotli:

**.htaccess (Apache):**
```apache
# Enable HTTP/2
Protocols h2 http/1.1

# Enable Brotli compression
<IfModule mod_brotli.c>
  AddOutputFilterByType BROTLI_COMPRESS text/html text/plain text/css application/javascript application/json
</IfModule>

# Fallback til gzip
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/plain text/css application/javascript application/json
</IfModule>
```

**Estimeret Kompleksitet:** Low  
**Effort:** 1 time (inkl. test)  
**Afhængigheder:** Server support for Brotli

---

#### 4.2.4 Browser Caching

**Opdater .htaccess:**

```apache
<IfModule mod_expires.c>
  ExpiresActive On
  
  # Images
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType image/webp "access plus 1 year"
  ExpiresByType image/svg+xml "access plus 1 year"
  
  # CSS & JavaScript
  ExpiresByType text/css "access plus 1 month"
  ExpiresByType application/javascript "access plus 1 month"
  
  # Fonts
  ExpiresByType font/woff2 "access plus 1 year"
  
  # HTML (kort cache for dynamisk indhold)
  ExpiresByType text/html "access plus 1 hour"
</IfModule>

# Cache-Control headers
<FilesMatch "\.(css|js|jpg|jpeg|png|webp|svg|woff2)$">
  Header set Cache-Control "public, max-age=31536000, immutable"
</FilesMatch>
```

**Estimeret Kompleksitet:** Low  
**Effort:** 1 time

---

#### 4.2.5 Digital Rain Performance Optimering

**Problem:**  
Kontinuerlig canvas rendering (~30 FPS) påvirker battery/CPU.

**Løsninger:**

1. Pause når tab er inaktiv (Visibility API)
2. Reducér opdateringsfrekvens til 20 FPS (50ms)
3. Lazy-initialize kun når hero er synlig (Intersection Observer)

```javascript
// I site.js
let animationId;
const drawRain = () => {
  // ... existing code
  animationId = requestAnimationFrame(drawRain);
};

// Pause ved tab switch
document.addEventListener('visibilitychange', () => {
  if (document.hidden) {
    cancelAnimationFrame(animationId);
  } else {
    drawRain();
  }
});

// Lazy init med Intersection Observer
const heroCanvas = document.getElementById('hero-canvas');
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting && !window.rainInitialized) {
      initDigitalRain();
      window.rainInitialized = true;
    }
  });
});
observer.observe(heroCanvas);
```

**Estimeret Kompleksitet:** Low  
**Effort:** 2 timer

---

**Total for Performance Optimering:**  
**Estimeret Kompleksitet:** Medium  
**Total Effort:** 8–12 timer

---

## 5. Samlet Scope & Estimater

### 5.1 Effort Distribution

| Område | Tasks | Estimeret Kompleksitet | Total Effort (timer) |
|--------|-------|------------------------|---------------------|
| **Navigation & IA** | Menu-struktur, Sticky CTA, Mobil-burger | Medium | 14–21 timer |
| **Lead-Gen** | Demo-booking, Priskort, Sårbarhedstjek | Medium | 13–18 timer |
| **Content & SEO** | Blog skelet, 3 posts, robots.txt, sitemap, schema.org | High | 36–50 timer |
| **Tilgængelighed** | WCAG P0 fixes (skip-link, kontrast, ARIA, focus trap) | Medium | 6–8 timer |
| **Performance** | Minificering, caching, lazy-loading, digital rain | Medium | 8–12 timer |
| **Testing & QA** | Cross-browser test, Lighthouse audit, accessibility audit | Low | 6–8 timer |
| **Dokumentation** | Opdater README, opret component library docs | Low | 4–6 timer |

**Total Estimat:** 87–123 timer (10–15 arbejdsdage for 1 udvikler)

---

### 5.2 Sprint 5 Prioritering (MVP Scope)

**Must-Have (Launch Blockers):**

1. ✅ Ny menu-struktur med 5 hovedpunkter
2. ✅ Sticky CTA-knap ("Book Demo")
3. ✅ Demo-booking flow (Calendly integration)
4. ✅ Priskort (statiske)
5. ✅ Blog skelet med 2–3 initial posts
6. ✅ robots.txt + XML sitemap
7. ✅ WCAG P0 fixes (skip-link, kontrast, ARIA)
8. ✅ Minificering + caching

**Should-Have (Post-Launch Sprint 5.1):**

- Priskalkulator (interaktiv)
- "Gratis sårbarhedstjek" flow (med SSL Labs API)
- 5+ blog posts + første whitepaper
- Schema.org på alle sider
- Focus trap for modals
- Digital rain performance optimering

**Nice-to-Have (Sprint 6+):**

- Mobile app for Security Ops
- Multi-tenant support (MSP white-label)
- AI chatbot for 24/7 support
- Video content (product demos, tutorials)

---

### 5.3 Afhængigheder & Risici

**Kritiske Afhængigheder:**

1. **Database tilgængelig** – Blog posts kræver MySQL tabel
2. **SMTP konfigureret** – Email til demo-booking og sårbarhedstjek
3. **Calendly-konto** – Demo-booking integration
4. **SSL Labs API access** – Gratis sårbarhedstjek (eller mock i MVP)
5. **Content creation ressourcer** – Mindst 2–3 blog posts skal skrives

**Risici:**

| Risiko | Sandsynlighed | Impact | Mitigation |
|--------|--------------|--------|------------|
| Content creation forsinkes | Høj | Medium | Start med 2 posts i stedet for 5; AI-assisteret skrivning |
| Calendly integration fejler | Lav | Lav | Fallback til simpel email-formular |
| Performance mål ikke nået | Medium | Medium | Iterativ optimering i Sprint 5.1 |
| WCAG compliance gaps | Lav | Høj | Kør automatiseret axe-core audit før launch |

---

## 6. Testing & Validering

### 6.1 Acceptance Criteria

**Navigation & IA:**
- [ ] Menu har max 5 hovedpunkter
- [ ] Sticky CTA vises ved 50% scroll
- [ ] Mobile burger-menu lukker ved ESC eller overlay click
- [ ] Breadcrumbs vises på alle undersider

**Lead-Gen:**
- [ ] Calendly widget loader korrekt på demo.php
- [ ] Priskort viser alle 6 pakker med korrekte priser
- [ ] "Gratis sårbarhedstjek" formular sender email

**Content & SEO:**
- [ ] Blog listing viser minimum 2 posts
- [ ] Enkelt blog post har TOC, share buttons og related posts
- [ ] robots.txt åbner for indexering
- [ ] XML sitemap genereres dynamisk
- [ ] Schema.org validerer i Google Rich Results Test

**Tilgængelighed:**
- [ ] Skip-link vises ved Tab-fokus
- [ ] Kontrast ratio > 4.5:1 for al tekst (axe-core scan passerer)
- [ ] ARIA live regions annoncerer success/error beskeder

**Performance:**
- [ ] Lighthouse Desktop Performance score > 90
- [ ] Lighthouse Mobile Performance score > 80
- [ ] LCP < 2.5s, FID < 100ms, CLS < 0.1
- [ ] Assets er minified og cached (verificer med Network tab)

---

### 6.2 Test Protocol

**Cross-Browser:**
- Chrome (desktop + mobile)
- Firefox (desktop)
- Safari (macOS + iOS)
- Edge (desktop)

**Screen Sizes:**
- Mobile: 375×667 (iPhone SE)
- Tablet: 768×1024 (iPad)
- Desktop: 1920×1080

**Accessibility Tools:**
- axe DevTools (browser extension)
- WAVE (web accessibility evaluation tool)
- NVDA/JAWS screen reader test (Windows)
- VoiceOver test (macOS/iOS)

**Performance Tools:**
- Lighthouse CI (automated)
- WebPageTest (real-world mobile test)
- Chrome DevTools Performance tab

---

## 7. Post-Sprint 5 Roadmap

**Sprint 5.1 (Iteration) – Uge 1–2 efter launch:**
- Fix bugs opdaget i production
- Implementer interaktiv priskalkulator
- Tilføj 2–3 flere blog posts
- Optimér SEO baseret på Search Console data

**Sprint 6 (Content Expansion) – Uge 3–6:**
- Publicér første whitepaper (gated download)
- Lancér månedlig threat intel rapport
- Tilføj video content (embedded YouTube)
- Start email newsletter for leads

**Sprint 7 (Advanced Features) – Uge 7–10:**
- Multi-language support (dansk/engelsk toggle)
- AI chatbot for 24/7 support (fallback til email)
- Partner/certificering badges på forsiden
- Team & advisory board profiler

---

## 8. Konklusion

Sprint 5 er det **mest omfattende sprint** hidtil, da det fundamentalt transformerer websitet fra en teknisk demo til en **marketing-klar SaaS-platform**.

**Key Takeaways:**

1. **Navigation** – Fra 7+ items til 5 klare kategorier (Løsninger, Cases, Priser & Demo, Ressourcer, Om & Kontakt)
2. **Lead-Gen** – Tre conversion points (Demo-booking, Priskort, Gratis sårbarhedstjek)
3. **Content** – Blog skelet med 2–3 initial posts etablerer thought leadership
4. **SEO** – robots.txt, sitemap og schema.org åbner for Google-indexering
5. **WCAG** – P0 accessibility fixes sikrer compliance før launch

**Next Steps:**

1. **Godkend plan** – Review med stakeholders
2. **Prioriter MVP scope** – Beslut hvilke "Should-Have" features kan vente til Sprint 5.1
3. **Start udvikling** – Begynd med navigation & IA (højeste impact)
4. **Content creation** – Parallel track til at skrive 2–3 blog posts
5. **Iterativ testing** – Test efter hver task, ikke kun ved sprint-slut

Med denne plan kan Sprint 5 levere en **produktionsklar, SEO-optimeret website** der understøtter forretningens vækstmål og positionerer Blackbox EYE som en seriøs spiller i cybersikkerhed.

---

**Udarbejdet af:** ALPHA-Web-Diagnostics-Agent  
**Dato:** 2025-11-23  
**Version:** 1.0.0-sprint5  
**Status:** Ready for Review
