# 📰 Blackbox Blog Intel Engine

**Automated cybersecurity intelligence collection and blog rendering system.**

## 🎯 Mission

Transform Blackbox EYE's blog from a database-driven CMS into a fast, automated intelligence hub that collects, normalizes, and presents cybersecurity news from official sources across Denmark, Switzerland, UAE, and globally.

## 🚀 Key Features

### ✅ Phase A: Fail-Open Architecture
- **Graceful degradation**: Blog never returns HTTP 500
- **No database required**: Works without db.php
- **Dual-mode rendering**: Supports both legacy database and modern JSON
- **CI healthcheck**: Automated validation in deployment pipeline

### ✅ Phase B: Data-Driven Structure
- **Static JSON rendering**: Fast, cacheable, scalable
- **Standardized schema**: Normalized post format
- **External source links**: Direct attribution to original articles
- **Severity indicators**: Visual risk levels (critical, high, medium, low)

### ✅ Phase C: Weekly Automation
- **RSS/Atom parsing**: Legal, robots.txt-compliant collection
- **Multi-step pipeline**: Collect → Normalize → Dedupe → Enrich → Output
- **Automated PR creation**: Weekly updates for review
- **DK seed list**: Bootstrap with Nov-Dec 2025 Danish sources

### ✅ Phase E: Cross-Browser QA
- **Playwright test suite**: Chromium, Firefox, WebKit
- **Multiple viewports**: Desktop, iPhone, iPad
- **Visual regression**: Screenshot artifacts
- **Accessibility checks**: ARIA, headings, links

## 📂 Architecture

```
blackbox-ui/
├── blog.php                          # Main blog page (dual-mode)
├── includes/
│   └── blog-functions.php            # Blog helper functions
├── data/
│   └── blog/
│       ├── sources.json              # Feed configurations + DK seed list
│       ├── posts.json                # Generated posts (all)
│       ├── latest.json               # Generated posts (top 20)
│       └── README.md                 # Data structure documentation
├── scripts/
│   └── blog-intel/
│       ├── collect-feeds.js          # RSS/Atom parser
│       ├── normalize.js              # Schema normalization
│       ├── dedupe.js                 # Duplicate removal
│       ├── enrich.js                 # Tag + risk level enrichment
│       └── generate-outputs.js       # Final JSON generation
├── .github/
│   └── workflows/
│       ├── ci.yml                    # Main CI/CD (includes blog healthcheck)
│       └── blog-intel-weekly.yml     # Weekly automation
└── tests/
    └── blog-intel-cross-browser.spec.js  # Playwright test suite
```

## 🔄 Data Flow

### Weekly Automation Pipeline

```
┌─────────────────┐
│ sources.json    │
│ (Config + Seeds)│
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Collect Feeds   │  ← RSS/Atom parsing (respects robots.txt)
│ collect-feeds.js│
└────────┬────────┘
         │ .collected-raw.json
         ▼
┌─────────────────┐
│ Normalize       │  ← Standard schema conversion
│ normalize.js    │
└────────┬────────┘
         │ .normalized.json
         ▼
┌─────────────────┐
│ Deduplicate     │  ← URL + title hash deduplication
│ dedupe.js       │
└────────┬────────┘
         │ .deduped.json
         ▼
┌─────────────────┐
│ Enrich          │  ← Auto-tagging + risk assessment
│ enrich.js       │
└────────┬────────┘
         │ .enriched.json
         ▼
┌─────────────────┐
│ Generate Output │  ← posts.json + latest.json
│ generate-outputs│
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Create PR       │  ← Automated pull request for review
└─────────────────┘
```

### Blog Rendering Flow

```
blog.php
   │
   ├─ Database available?
   │  ├─ YES: Use bbx_get_blog_posts() [legacy]
   │  └─ NO: Use bbx_get_blog_posts_from_json() [new]
   │
   ├─ Transform JSON posts to standard format
   │  ├─ External links for intel sources
   │  ├─ Severity badges (critical/high/medium/low)
   │  └─ Source attribution
   │
   └─ Render:
      ├─ Blog cards (grid)
      ├─ News panels (curated regional intel)
      └─ Newsletter signup
```

## 🌍 Regional Coverage

### Denmark (DK)
- SAMSIK (Municipalities cybersecurity)
- CFCS/MSSB (National cybersecurity)
- Danske Kommuner (Local government)
- Dansk Erhverv (Business association)
- Danske Vandværker (Critical infrastructure - water)
- Sikkerdigital.dk (Awareness platform)

### Switzerland (CH)
- Coming soon (financial sector, NCSC)

### UAE (AE)
- Coming soon (critical infrastructure, government)

### Europe (EU)
- Coming soon (ENISA, national CERTs)

### Global
- Coming soon (major incidents, threat intel)

## 📊 Data Schema

### Post Object
```json
{
  "id": "unique-hash-16-chars",
  "title": "Article title",
  "url": "https://source.com/article",
  "source": "Source Name",
  "country": "DK",
  "region": "denmark",
  "published_at": "2025-11-13T10:00:00Z",
  "tags": ["DDoS", "municipalities", "election"],
  "risk_level": "high",
  "excerpt": "Short excerpt (max 240 chars)...",
  "language": "da",
  "collected_at": "2025-12-26T16:00:00Z"
}
```

### Risk Levels
- **🔴 CRITICAL**: Critical infrastructure, national security, massive breaches
- **🟠 HIGH**: Ransomware, DDoS attacks, cyber attacks, exploits
- **🟡 MEDIUM**: General threats, vulnerabilities, malware
- **🟢 LOW**: Guides, best practices, training, awareness

## 🛡️ Compliance & Ethics

### ✅ What We Do
- Parse **RSS/Atom feeds** (public, intended for syndication)
- Use **official sources** (government, industry associations, news)
- Respect **robots.txt** (no forbidden scraping)
- Provide **short excerpts** (max 240 chars, fair use)
- **Direct attribution** (source name + link)
- **Metadata only** for paywalled content (title, date, source, URL)

### ❌ What We Don't Do
- ❌ Scrape full articles
- ❌ Bypass paywalls
- ❌ Ignore robots.txt
- ❌ Copy substantial content
- ❌ Remove attribution
- ❌ Use for commercial redistribution

## 🧪 Testing

### Run Blog Tests
```bash
# All browsers + viewports
npm test -- tests/blog-intel-cross-browser.spec.js

# Specific browser
npx playwright test tests/blog-intel-cross-browser.spec.js --project=chromium

# Debug mode
npx playwright test tests/blog-intel-cross-browser.spec.js --headed
```

### Test Coverage
- ✅ HTTP 200 response
- ✅ HTML structure
- ✅ No horizontal scroll (mobile)
- ✅ Posts rendering (if data available)
- ✅ Graceful fallback (no data)
- ✅ Filter functionality
- ✅ Region tab switching
- ✅ External link indicators
- ✅ Accessibility (ARIA, headings)

## 🔧 Manual Testing

### Test Pipeline Locally
```bash
# Install feed parser
npm install --no-save rss-parser node-fetch@2

# Run pipeline steps
node scripts/blog-intel/collect-feeds.js
node scripts/blog-intel/normalize.js
node scripts/blog-intel/dedupe.js
node scripts/blog-intel/enrich.js
node scripts/blog-intel/generate-outputs.js

# Check generated files
cat data/blog/posts.json | jq '.posts | length'
cat data/blog/latest.json | jq '.posts[0]'
```

### Test Blog Rendering
```bash
# Start PHP server
php -S localhost:8000

# Visit in browser
open http://localhost:8000/blog.php

# Or test with curl
curl -I http://localhost:8000/blog.php
```

## 📅 Automation Schedule

### Weekly Collection
- **Runs**: Every Monday at 06:00 UTC
- **Trigger**: `.github/workflows/blog-intel-weekly.yml`
- **Output**: Pull request with new posts
- **Review**: Manual approval required

### Manual Trigger
```bash
# GitHub UI: Actions → Blog Intel Weekly → Run workflow
# Or via API:
gh workflow run blog-intel-weekly.yml
```

## 🚢 Deployment

### Phase 1: Database Fallback (Current)
- Blog works without database
- Shows graceful fallback message
- ✅ HTTP 200 always returned
- ✅ CI healthcheck passing

### Phase 2: Weekly Automation (Next)
- Merge first PR with posts from pipeline
- Blog renders JSON posts
- External links to sources
- Severity indicators visible

### Phase 3: Full Intel Hub (Future)
- Advanced filtering (multi-select)
- Client-side search
- Weekly Brief section
- RSS export
- Chain-of-custody metadata

## 🔗 Related Documentation

- [Data Structure](data/blog/README.md)
- [Testing Guide](docs/BLOG_TESTING.md)
- [CI/CD Pipeline](.github/workflows/ci.yml)
- [Weekly Automation](.github/workflows/blog-intel-weekly.yml)

## 📞 Support

For issues or questions:
- **GitHub Issues**: [blackbox-eye/blackbox-ui](https://github.com/blackbox-eye/blackbox-ui/issues)
- **Contact**: via contact.php on blackbox.codes

---

**🎉 Status**: Phases A, B, C, E Complete | Ready for Production
