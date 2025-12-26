#!/usr/bin/env node
/**
 * Blog Intel - Generate Outputs
 * 
 * Generates final posts.json and latest.json files.
 */

const fs = require('fs');

function generateOutputs() {
  console.log('📝 Generating output files...');
  
  const enrichedPath = './data/blog/.enriched.json';
  if (!fs.existsSync(enrichedPath)) {
    console.error('❌ No enriched data found');
    process.exit(1);
  }
  
  const posts = JSON.parse(fs.readFileSync(enrichedPath, 'utf8'));
  
  // Sort by published_at descending
  posts.sort((a, b) => {
    const dateA = new Date(a.published_at);
    const dateB = new Date(b.published_at);
    return dateB - dateA;
  });
  
  // Get unique regions
  const regions = [...new Set(posts.map(p => p.region))];
  
  // Calculate date range
  const dates = posts.map(p => new Date(p.published_at).getTime()).filter(d => !isNaN(d));
  const earliest = dates.length > 0 ? new Date(Math.min(...dates)).toISOString() : null;
  const latest = dates.length > 0 ? new Date(Math.max(...dates)).toISOString() : null;
  
  // Generate posts.json
  const postsOutput = {
    $schema: 'posts-schema',
    version: '1.0.0',
    generated_at: new Date().toISOString(),
    pipeline_version: '1.0.0',
    metadata: {
      total_posts: posts.length,
      regions: regions,
      date_range: {
        earliest: earliest,
        latest: latest
      }
    },
    posts: posts
  };
  
  fs.writeFileSync('./data/blog/posts.json', JSON.stringify(postsOutput, null, 2));
  console.log(`✅ Generated posts.json (${posts.length} posts)`);
  
  // Generate latest.json (top 20)
  const latestOutput = {
    $schema: 'posts-schema',
    version: '1.0.0',
    generated_at: new Date().toISOString(),
    pipeline_version: '1.0.0',
    metadata: {
      total_posts: Math.min(20, posts.length),
      regions: regions,
      date_range: {
        earliest: earliest,
        latest: latest
      }
    },
    posts: posts.slice(0, 20)
  };
  
  fs.writeFileSync('./data/blog/latest.json', JSON.stringify(latestOutput, null, 2));
  console.log(`✅ Generated latest.json (${Math.min(20, posts.length)} posts)`);
  
  // Clean up intermediate files
  [
    './data/blog/.collected-raw.json',
    './data/blog/.normalized.json',
    './data/blog/.deduped.json',
    './data/blog/.enriched.json'
  ].forEach(file => {
    if (fs.existsSync(file)) {
      fs.unlinkSync(file);
    }
  });
  
  console.log('🧹 Cleaned up intermediate files');
  
  // Summary
  console.log('\n════════════════════════════════════════');
  console.log('📊 Generation Summary');
  console.log('════════════════════════════════════════');
  console.log(`Total posts: ${posts.length}`);
  console.log(`Regions: ${regions.join(', ')}`);
  console.log(`Date range: ${earliest ? earliest.split('T')[0] : 'N/A'} to ${latest ? latest.split('T')[0] : 'N/A'}`);
  console.log('════════════════════════════════════════');
}

generateOutputs();
