#!/usr/bin/env node
/**
 * Blog Intel - Deduplicate
 * 
 * Removes duplicates by URL and title hash.
 */

const fs = require('fs');
const crypto = require('crypto');

function dedupe() {
  console.log('🔍 Deduplicating...');
  
  const normalizedPath = './data/blog/.normalized.json';
  if (!fs.existsSync(normalizedPath)) {
    console.error('❌ No normalized data found');
    process.exit(1);
  }
  
  const posts = JSON.parse(fs.readFileSync(normalizedPath, 'utf8'));
  
  // Load existing posts to avoid duplicates across weeks
  const existingPath = './data/blog/posts.json';
  let existing = [];
  if (fs.existsSync(existingPath)) {
    const existingData = JSON.parse(fs.readFileSync(existingPath, 'utf8'));
    existing = existingData.posts || [];
  }
  
  const seen = new Set();
  const allPosts = [...existing, ...posts];
  
  // Dedupe by ID (URL + title hash)
  const deduped = allPosts.filter(post => {
    if (seen.has(post.id)) {
      return false;
    }
    seen.add(post.id);
    return true;
  });
  
  // Also dedupe by URL (in case title changed)
  const seenUrls = new Set();
  const finalDeduped = deduped.filter(post => {
    const normalizedUrl = post.url.toLowerCase().replace(/\/$/, '');
    if (seenUrls.has(normalizedUrl)) {
      return false;
    }
    seenUrls.add(normalizedUrl);
    return true;
  });
  
  console.log(`✅ Removed ${allPosts.length - finalDeduped.length} duplicates`);
  console.log(`📊 Total unique posts: ${finalDeduped.length}`);
  
  // Save deduped data
  const outputPath = './data/blog/.deduped.json';
  fs.writeFileSync(outputPath, JSON.stringify(finalDeduped, null, 2));
  console.log(`📝 Saved to: ${outputPath}`);
}

dedupe();
