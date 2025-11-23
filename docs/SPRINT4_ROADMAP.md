# Sprint 4 Roadmap - Performance & Content Features
**Version:** 1.0  
**Date:** November 23, 2025  
**Sprint:** Sprint 4 (Performance + Content + Analytics)  
**Duration:** 2-3 weeks  
**Status:** 🟡 Planning Phase

---

## 🎯 Sprint 4 Objectives

### Primary Goals
1. **Performance Optimization** - Achieve Core Web Vitals "Good" ratings
2. **Content Management** - Launch blog CMS and FAQ system
3. **Lead Generation** - Implement analytics and conversion tracking
4. **SEO Enhancement** - Rich snippets and advanced meta tags
5. **AlphaBot Evolution** - Full internationalization and personalization

---

## 📋 Feature Breakdown

### 1. Performance Optimization (Priority: CRITICAL)

#### Core Web Vitals Targets
| Metric | Current | Target | Status |
|--------|---------|--------|--------|
| **LCP** (Largest Contentful Paint) | TBD | < 2.5s | ⏳ |
| **FID** (First Input Delay) | TBD | < 100ms | ⏳ |
| **CLS** (Cumulative Layout Shift) | TBD | < 0.1 | ⏳ |
| **TTFB** (Time to First Byte) | TBD | < 600ms | ⏳ |

#### Implementation Tasks
- [ ] **Image Optimization**
  - Implement lazy loading with `loading="lazy"` attribute
  - Convert images to WebP format with fallbacks
  - Add responsive images with `srcset` and `sizes`
  - Compress existing images (target: 80-90% quality)
  
- [ ] **JavaScript Optimization**
  - Minify `site.js` (remove comments, whitespace)
  - Implement code splitting for AI features
  - Defer non-critical JavaScript
  - Remove unused JavaScript libraries
  
- [ ] **CSS Optimization**
  - Inline critical CSS for above-the-fold content
  - Minify Tailwind CSS output
  - Remove unused CSS classes (PurgeCSS)
  - Implement CSS containment for animations
  
- [ ] **Resource Hints**
  ```html
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="dns-prefetch" href="https://www.google.com">
  <link rel="preload" href="/assets/js/site.js" as="script">
  ```
  
- [ ] **Compression**
  - Enable Gzip compression on server
  - Configure Brotli compression (better than Gzip)
  - Add `Cache-Control` headers for static assets

**Success Criteria:** All Core Web Vitals in "Good" range (green)

---

### 2. Blog CMS System (Priority: HIGH)

#### Features
- **Admin Interface** (password-protected)
  - Create/Edit/Delete blog posts
  - Rich text editor (Markdown + WYSIWYG)
  - Image upload and management
  - SEO meta fields (title, description, keywords)
  - Publish scheduling and draft system
  
- **Public Blog Interface**
  - Blog listing page with pagination
  - Individual blog post pages
  - Categories and tags filtering
  - Related posts recommendations
  - Social sharing buttons
  
- **Multi-language Support**
  - Full DA/EN translation support
  - Language-specific blog posts
  - Auto-translation suggestions via AI

#### Database Schema
```sql
CREATE TABLE blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(255) UNIQUE NOT NULL,
    title_da VARCHAR(255) NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    content_da TEXT NOT NULL,
    content_en TEXT NOT NULL,
    excerpt_da TEXT,
    excerpt_en TEXT,
    featured_image VARCHAR(255),
    category VARCHAR(100),
    tags JSON,
    author VARCHAR(100),
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    publish_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    meta_description_da TEXT,
    meta_description_en TEXT,
    views INT DEFAULT 0,
    INDEX idx_status_publish (status, publish_date),
    INDEX idx_category (category),
    FULLTEXT idx_search (title_da, title_en, content_da, content_en)
);
```

**Files to Create:**
- `blog.php` - Blog listing page
- `blog-post.php` - Individual post view
- `admin/blog-admin.php` - Admin interface
- `api/blog-api.php` - CRUD endpoints
- `includes/blog-functions.php` - Helper functions

---

### 3. FAQ Section + AI Search (Priority: HIGH)

#### Features
- **FAQ Database**
  - Categorized FAQ items (Security, Pricing, Technical, etc.)
  - Multi-language questions and answers
  - Admin interface for FAQ management
  
- **AI-Powered Search**
  - Natural language search using Gemini API
  - Semantic matching (not just keyword search)
  - Search suggestions as user types
  - Fallback to traditional search if AI unavailable
  
- **UI Components**
  - Accordion-style FAQ display
  - Search bar with live results
  - Category filter buttons
  - "Was this helpful?" feedback system

#### Database Schema
```sql
CREATE TABLE faq_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    question_da TEXT NOT NULL,
    question_en TEXT NOT NULL,
    answer_da TEXT NOT NULL,
    answer_en TEXT NOT NULL,
    keywords JSON,
    order_index INT DEFAULT 0,
    helpful_count INT DEFAULT 0,
    not_helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    FULLTEXT idx_search (question_da, question_en, answer_da, answer_en)
);
```

**AI Search Implementation:**
```javascript
async function searchFAQ(query, language) {
    const prompt = `User asks: "${query}". Find the most relevant FAQ from our database. Return FAQ ID or "NONE" if no match.`;
    const response = await callGemini(prompt);
    // Match AI response with FAQ database
    return matchedFAQs;
}
```

**Files to Create:**
- `faq.php` - FAQ page
- `admin/faq-admin.php` - Admin interface
- `api/faq-search.php` - AI search endpoint

---

### 4. Lead Generation & Analytics (Priority: MEDIUM)

#### Lead Capture Points
1. **Contact Form** - Existing (enhance tracking)
2. **Newsletter Signup** - Footer and dedicated page
3. **Demo Request** - Pricing page CTA
4. **Whitepaper Downloads** - Gated content
5. **AI Consultation** - AlphaBot lead qualification

#### Analytics Integration
- **Google Analytics 4**
  - Event tracking (form submissions, CTA clicks)
  - Enhanced ecommerce (pricing interactions)
  - User journey visualization
  - Conversion funnel analysis
  
- **Custom Analytics Dashboard**
  - Lead sources breakdown
  - Page performance metrics
  - AI feature usage statistics
  - Language preference distribution

#### Lead Database Schema
```sql
CREATE TABLE leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(255),
    company VARCHAR(255),
    phone VARCHAR(50),
    source VARCHAR(100), -- contact_form, newsletter, demo_request, etc.
    language VARCHAR(2),
    metadata JSON, -- Additional context (page, referrer, etc.)
    status ENUM('new', 'contacted', 'qualified', 'converted', 'lost') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status_created (status, created_at)
);
```

**A/B Testing Framework:**
```javascript
// Test different CTA button colors
const variant = Math.random() < 0.5 ? 'amber' : 'green';
logABTest('cta_color', variant);
```

**Files to Create:**
- `api/lead-capture.php` - Lead submission endpoint
- `admin/analytics-dashboard.php` - Custom analytics view
- `includes/analytics.php` - GA4 integration helpers

---

### 5. Advanced SEO Optimization (Priority: MEDIUM)

#### Rich Snippets (Schema.org)
**Article Schema** (for blog posts):
```json
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "Blog Post Title",
  "image": "https://blackbox.codes/assets/blog/image.jpg",
  "author": {
    "@type": "Organization",
    "name": "Blackbox EYE"
  },
  "publisher": {
    "@type": "Organization",
    "name": "Blackbox EYE",
    "logo": {
      "@type": "ImageObject",
      "url": "https://blackbox.codes/assets/logo.png"
    }
  },
  "datePublished": "2025-11-23",
  "dateModified": "2025-11-23"
}
```

**FAQ Schema**:
```json
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "What is Blackbox EYE?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Blackbox EYE is an enterprise cyber operations platform..."
      }
    }
  ]
}
```

**Product Schema** (for pricing tiers):
```json
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Blackbox EYE MVP-Premium",
  "offers": {
    "@type": "Offer",
    "price": "5999",
    "priceCurrency": "DKK",
    "availability": "https://schema.org/InStock"
  }
}
```

#### Enhanced Open Graph
```html
<meta property="og:site_name" content="Blackbox EYE">
<meta property="og:locale" content="da_DK">
<meta property="og:locale:alternate" content="en">
<meta property="article:published_time" content="2025-11-23T10:00:00Z">
<meta property="article:author" content="Blackbox EYE">
<meta property="article:section" content="Cybersecurity">
<meta property="article:tag" content="AI, Security, Enterprise">
```

#### Twitter Cards
```html
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@blackboxeye">
<meta name="twitter:creator" content="@blackboxeye">
<meta name="twitter:title" content="Page Title">
<meta name="twitter:description" content="Page Description">
<meta name="twitter:image" content="https://blackbox.codes/assets/og-image.jpg">
```

#### XML Sitemap Generation
**Dynamic sitemap.xml:**
```php
<?php
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

$pages = ['index', 'about', 'products', 'cases', 'pricing', 'contact', 'faq', 'blog'];
foreach ($pages as $page) {
    echo '<url>';
    echo '<loc>https://blackbox.codes/' . $page . '.php</loc>';
    echo '<changefreq>weekly</changefreq>';
    echo '<priority>0.8</priority>';
    echo '</url>';
}

// Add blog posts dynamically
$posts = getBlogPosts();
foreach ($posts as $post) {
    echo '<url>';
    echo '<loc>https://blackbox.codes/blog/' . $post['slug'] . '</loc>';
    echo '<lastmod>' . $post['updated_at'] . '</lastmod>';
    echo '<changefreq>monthly</changefreq>';
    echo '<priority>0.6</priority>';
    echo '</url>';
}

echo '</urlset>';
```

**Files to Create:**
- `sitemap.xml` or `sitemap.php` - Dynamic sitemap
- `robots.txt` - Enhanced robots directives
- `includes/structured-data.php` - Schema.org helpers

---

### 6. AlphaBot Enhancements (Priority: MEDIUM)

#### Multi-Language Support
**Current State:** English prompt, Danish/English responses  
**Target State:** Fully internationalized system

**Implementation:**
```javascript
// Detect user language from session/query
const userLanguage = i18n.lang; // 'da' or 'en'

// System prompt in user's language
const systemPrompt = userLanguage === 'da' 
    ? "Du er AlphaBot, en AI-sikkerhedsassistent for Blackbox EYE..."
    : "You are AlphaBot, an AI security assistant for Blackbox EYE...";

// User messages in their language
const userMessage = document.getElementById('alphabot-input').value;

// Response in same language
const conversation = [
    { role: 'system', content: systemPrompt },
    { role: 'user', content: userMessage }
];
```

#### Personalized Responses
**Context-Aware AI:**
```javascript
// Gather user context
const userContext = {
    language: currentLanguage,
    currentPage: window.location.pathname,
    industry: localStorage.getItem('user_industry'), // From pricing AI advisor
    previousQuestions: getConversationHistory(),
    sessionDuration: getSessionDuration()
};

// Enhanced prompt with context
const prompt = `
User context:
- Language: ${userContext.language}
- Current page: ${userContext.currentPage}
- Industry: ${userContext.industry || 'Unknown'}
- Previous topics: ${userContext.previousQuestions.join(', ')}

User asks: "${userMessage}"

Provide a personalized response considering their context.
`;
```

#### Conversation History Persistence
```javascript
// Save to localStorage
const saveConversation = () => {
    const history = conversation.map(msg => ({
        role: msg.role,
        content: msg.content,
        timestamp: Date.now()
    }));
    localStorage.setItem('alphabot_history', JSON.stringify(history));
};

// Load on page refresh
const loadConversation = () => {
    const saved = localStorage.getItem('alphabot_history');
    if (saved) {
        const history = JSON.parse(saved);
        // Filter messages from last 24 hours
        const recent = history.filter(msg => 
            Date.now() - msg.timestamp < 86400000
        );
        return recent;
    }
    return [];
};
```

#### Typing Indicators & Error Recovery
```javascript
// Show typing animation
const showTypingIndicator = () => {
    const typingHTML = `
        <div class="alphabot-message bot typing">
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="dot"></span>
        </div>
    `;
    messagesDiv.insertAdjacentHTML('beforeend', typingHTML);
};

// Error recovery with retry
const callAlphaBotWithRetry = async (maxRetries = 2) => {
    for (let attempt = 0; attempt < maxRetries; attempt++) {
        try {
            return await callAlphaBot();
        } catch (error) {
            if (attempt === maxRetries - 1) {
                appendMessage('bot', i18n.t('alphabot.error_final', 
                    'Beklager, jeg oplever tekniske problemer. Prøv igen senere.'));
            } else {
                // Retry with exponential backoff
                await new Promise(resolve => setTimeout(resolve, 1000 * (attempt + 1)));
            }
        }
    }
};
```

**Files to Update:**
- `assets/js/site.js` - AlphaBot enhancements
- `lang/da.json` - New AlphaBot translations
- `lang/en.json` - English equivalents

---

### 7. Footer UI/UX Enhancements (Priority: LOW)

#### Design Improvements
**Current Footer:** Basic layout with offices, contact, social links  
**Enhanced Footer:** Newsletter signup, better visual hierarchy, more CTAs

**New Layout Structure:**
```
[Newsletter Signup Section - Full Width]
[4 Columns: Brand | Products | Resources | Contact]
[Bottom Bar: Copyright | Legal Links | Social Icons]
```

#### Newsletter Signup Component
```html
<div class="bg-gradient-to-r from-amber-400/10 to-amber-600/10 border-t border-amber-400/20 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto text-center">
            <h3 class="text-2xl font-bold mb-3"><?= t('footer.newsletter.title') ?></h3>
            <p class="text-gray-400 mb-6"><?= t('footer.newsletter.description') ?></p>
            <form id="newsletter-form" class="flex gap-3">
                <input type="email" placeholder="<?= t('footer.newsletter.placeholder') ?>" 
                    class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-4 py-3">
                <button type="submit" class="bg-amber-400 text-black px-8 py-3 rounded-lg font-semibold">
                    <?= t('footer.newsletter.button') ?>
                </button>
            </form>
        </div>
    </div>
</div>
```

#### Enhanced Social Links
```html
<div class="flex gap-3">
    <a href="https://linkedin.com/company/blackboxeye" 
        class="group w-12 h-12 rounded-xl bg-gray-800 hover:bg-amber-400 flex items-center justify-center transition-all transform hover:scale-110"
        aria-label="LinkedIn">
        <svg class="w-5 h-5 text-gray-400 group-hover:text-black transition-colors">
            <!-- LinkedIn icon -->
        </svg>
    </a>
    <!-- Twitter, GitHub, etc. -->
</div>
```

**Files to Update:**
- `includes/site-footer.php` - Footer redesign
- `api/newsletter-subscribe.php` - Newsletter submission
- `lang/da.json` + `lang/en.json` - Newsletter translations

---

## 📊 Sprint 4 Timeline

### Week 1 (Nov 23-29)
- [x] Sprint planning and roadmap creation
- [ ] Performance optimization implementation
- [ ] Blog CMS database schema and admin interface
- [ ] FAQ database and basic UI

### Week 2 (Nov 30 - Dec 6)
- [ ] Complete Blog CMS with public interface
- [ ] AI-powered FAQ search implementation
- [ ] Lead generation tracking setup
- [ ] Google Analytics 4 integration

### Week 3 (Dec 7-13)
- [ ] Advanced SEO (rich snippets, sitemaps)
- [ ] AlphaBot enhancements (i18n, personalization)
- [ ] Footer redesign with newsletter
- [ ] Comprehensive testing and QA

---

## ✅ Success Criteria

### Must-Have (P0)
- [ ] Core Web Vitals: All metrics in "Good" range
- [ ] Blog CMS: Fully functional with admin interface
- [ ] FAQ Section: Live with AI search
- [ ] Analytics: Lead tracking operational
- [ ] AlphaBot: Multi-language support

### Should-Have (P1)
- [ ] Rich snippets for all pages
- [ ] Newsletter signup functional
- [ ] A/B testing framework ready
- [ ] XML sitemap generated

### Nice-to-Have (P2)
- [ ] Blog post scheduling
- [ ] Advanced analytics dashboard
- [ ] Social sharing analytics
- [ ] FAQ feedback system

---

## 🧪 Testing Plan

### Performance Testing
- Lighthouse CI for Core Web Vitals
- WebPageTest.org full analysis
- GTmetrix performance report

### Functional Testing
- Blog post creation/editing workflow
- FAQ search accuracy validation
- Lead submission verification
- Multi-language consistency check

### SEO Validation
- Rich Results Test (Google)
- Open Graph Debugger (Facebook)
- Twitter Card Validator
- XML sitemap validation

---

## 📈 Metrics & KPIs

| Metric | Current | Target | Priority |
|--------|---------|--------|----------|
| Lighthouse Score | TBD | 90+ | HIGH |
| LCP | TBD | < 2.5s | HIGH |
| Blog Posts | 0 | 10+ | MEDIUM |
| FAQ Items | 0 | 30+ | MEDIUM |
| Lead Conversion | TBD | 2%+ | LOW |
| Newsletter Signups | 0 | 50+/month | LOW |

---

## 🚀 Deployment Strategy

1. **Development** → Feature branches (`feat/sprint4-*`)
2. **Testing** → Staging environment validation
3. **Production** → Gradual rollout with monitoring
4. **Rollback Plan** → Git revert capability maintained

---

**Last Updated:** November 23, 2025  
**Sprint Lead:** GitHub Copilot  
**Status:** ✅ Planning Complete - Ready for Implementation
