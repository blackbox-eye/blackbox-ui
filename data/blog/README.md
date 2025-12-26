# Blog Data Directory

This directory contains the data-driven blog engine for Blackbox EYE.

## Structure

- `posts.json` - Generated blog posts from the weekly intel pipeline
- `sources.json` - Configuration for RSS feeds and intel sources
- `latest.json` - Top 20 most recent posts (generated)
- `schema.json` - JSON schema for validation (future)

## Data Flow

1. **Weekly automation** (`.github/workflows/blog-intel-weekly.yml`):
   - Collects intel from RSS/official feeds
   - Normalizes data (id, title, url, source, country, region, published_at, tags, risk_level, excerpt, language)
   - Deduplicates based on canonical URL + title hash
   - Enriches with tags and risk levels
   - Generates `posts.json` and `latest.json`
   - Creates PR for review

2. **Blog renderer** (`blog.php`):
   - Reads `posts.json` (static file)
   - No runtime scraping or database queries
   - Fast and stable rendering

## Sources Configuration

`sources.json` contains:
- Regional source configurations (DK, Nordic, EU, CH, UAE, Global)
- RSS/Atom feed URLs (preferred method)
- Seed articles for bootstrapping
- Robots.txt compliance rules

## Posts Schema

Each post in `posts.json` contains:
```json
{
  "id": "unique-hash",
  "title": "Article title",
  "url": "https://source.com/article",
  "source": "Source Name",
  "country": "DK",
  "region": "denmark",
  "published_at": "2025-11-13T10:00:00Z",
  "tags": ["DDoS", "municipalities"],
  "risk_level": "high|medium|low|critical",
  "excerpt": "Short excerpt (max 240 chars)",
  "language": "da|en"
}
```

## Compliance

- **Respect robots.txt**: Sources blocking scraping are accessed via RSS only
- **Paywall respect**: Only metadata + RSS summaries for paywalled content
- **No copyright infringement**: Short excerpts (max 240 chars) + link to source
- **Attribution**: All posts include source name and direct link

## Regional Coverage

- 🇩🇰 **Danmark**: SAMSIK, CFCS, Danske Kommuner, Dansk Erhverv, etc.
- 🇨🇭 **Schweiz**: Banking sector, federal cybersecurity
- 🇦🇪 **UAE**: Critical infrastructure, government agencies
- 🇪🇺 **Europa**: ENISA, national CERTs, industry reports
- 🌍 **Global**: Major incidents, threat intelligence

## DK Seed List (Nov-Dec 2025)

Initial Danish sources are seeded with articles from November-December 2025:
- DDoS wave against municipalities and businesses
- Election-related cyber threats
- Critical infrastructure (water sector)
- National cybersecurity strategy updates
- Nordic-Baltic cooperation

These seed articles will bootstrap the weekly automation pipeline.
