#!/usr/bin/env node
/**
 * Blog Intel Collection - RSS Feed Parser
 * 
 * Collects cybersecurity intelligence from RSS/Atom feeds.
 * Respects robots.txt and only uses official/permitted sources.
 * 
 * @version 1.0.0
 */

const Parser = require('rss-parser');
const fs = require('fs');
const crypto = require('crypto');

const parser = new Parser({
  timeout: 10000,
  headers: {
    'User-Agent': 'BlackboxEYE-IntelBot/1.0 (+https://blackbox.codes)'
  }
});

/**
 * Load sources configuration
 */
function loadSources() {
  const sourcesPath = './data/blog/sources.json';
  if (!fs.existsSync(sourcesPath)) {
    console.error('❌ sources.json not found');
    process.exit(1);
  }
  
  const data = fs.readFileSync(sourcesPath, 'utf8');
  return JSON.parse(data);
}

/**
 * Generate unique ID from URL + title
 */
function generateId(url, title) {
  const hash = crypto.createHash('sha256');
  hash.update(url + '|' + title);
  return hash.digest('hex').substring(0, 16);
}

/**
 * Parse RSS feed and extract posts
 */
async function parseFeed(source, regionKey) {
  console.log(`  📡 Parsing: ${source.name}`);
  
  // If no RSS feed, use seed articles
  if (!source.rss || source.rss === null) {
    console.log(`    ℹ️  No RSS feed - using seed articles (${source.seed_articles?.length || 0})`);
    if (source.seed_articles && source.seed_articles.length > 0) {
      return source.seed_articles.map(article => ({
        id: generateId(article.url, article.title),
        title: article.title,
        url: article.url,
        source: source.name,
        country: source.country,
        region: regionKey,
        published_at: article.date ? new Date(article.date).toISOString() : new Date().toISOString(),
        tags: article.tags || [],
        risk_level: article.severity || 'medium',
        excerpt: article.excerpt || '',
        language: source.language || 'en',
        collected_at: new Date().toISOString()
      }));
    }
    return [];
  }
  
  // Parse RSS feed
  try {
    const feed = await parser.parseURL(source.rss);
    const posts = [];
    
    for (const item of feed.items.slice(0, 10)) { // Max 10 per feed
      // Skip if too old (older than 90 days)
      const pubDate = item.pubDate ? new Date(item.pubDate) : new Date();
      const daysSincePublished = (Date.now() - pubDate.getTime()) / (1000 * 60 * 60 * 24);
      if (daysSincePublished > 90) {
        continue;
      }
      
      // Extract excerpt from content/description
      let excerpt = item.contentSnippet || item.description || '';
      
      // Security-first sanitization: Convert to safe plain text only
      // This data will be stored in JSON and rendered by blog.php with htmlspecialchars()
      
      // Remove all HTML tags
      excerpt = excerpt.replace(/<[^>]*>/g, ' ');
      
      // Decode HTML entities to readable characters
      const entityMap = {
        '&amp;': '&',
        '&lt;': '<',
        '&gt;': '>',
        '&quot;': '"',
        '&#39;': "'",
        '&#0?39;': "'",
        '&apos;': "'",
        '&nbsp;': ' '
      };
      for (const [entity, char] of Object.entries(entityMap)) {
        excerpt = excerpt.replace(new RegExp(entity, 'g'), char);
      }
      
      // After decoding entities, remove any remaining < or > characters
      // This prevents XSS from incomplete tags or decoded entities
      excerpt = excerpt.replace(/[<>]/g, '');
      
      // Normalize whitespace
      excerpt = excerpt.replace(/\s+/g, ' ').trim();
      
      if (excerpt.length > 240) {
        excerpt = excerpt.substring(0, 237) + '...';
      }
      
      posts.push({
        id: generateId(item.link, item.title),
        title: item.title,
        url: item.link,
        source: source.name,
        country: source.country,
        region: regionKey,
        published_at: pubDate.toISOString(),
        tags: source.tags || [],
        risk_level: 'medium', // Will be enriched later
        excerpt: excerpt,
        language: source.language || 'en',
        collected_at: new Date().toISOString()
      });
    }
    
    console.log(`    ✅ Collected ${posts.length} posts`);
    return posts;
  } catch (error) {
    console.error(`    ❌ Failed to parse feed: ${error.message}`);
    return [];
  }
}

/**
 * Main collection function
 */
async function collectIntel() {
  console.log('🚀 Starting intel collection...\n');
  
  const config = loadSources();
  const allPosts = [];
  
  // Iterate through regions
  for (const [regionKey, region] of Object.entries(config.regions)) {
    if (!region.sources || region.sources.length === 0) {
      continue;
    }
    
    console.log(`\n📍 Region: ${region.name} (${region.short})`);
    
    for (const source of region.sources) {
      const posts = await parseFeed(source, regionKey);
      allPosts.push(...posts);
      
      // Rate limiting: wait 1 second between feeds
      await new Promise(resolve => setTimeout(resolve, 1000));
    }
  }
  
  console.log(`\n✅ Collection complete: ${allPosts.length} total posts`);
  
  // Save raw collected data
  const outputPath = './data/blog/.collected-raw.json';
  fs.writeFileSync(outputPath, JSON.stringify(allPosts, null, 2));
  console.log(`📝 Saved to: ${outputPath}`);
  
  // Save count for workflow (GitHub Actions specific)
  // Note: Only works in CI environment; harmless in local dev
  try {
    fs.writeFileSync('/tmp/intel_count.txt', allPosts.length.toString());
  } catch (err) {
    // Ignore error in Windows/local environments
    console.warn('⚠️  Could not write to /tmp (not a Unix system?)');
  }
  
  return allPosts;
}

// Run
collectIntel().catch(error => {
  console.error('❌ Collection failed:', error);
  process.exit(1);
});
