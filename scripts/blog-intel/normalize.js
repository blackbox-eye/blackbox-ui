#!/usr/bin/env node
/**
 * Blog Intel - Normalize
 * 
 * Normalizes collected data to standard schema.
 */

const fs = require('fs');

function normalize() {
  console.log('🔧 Normalizing data...');
  
  const rawPath = './data/blog/.collected-raw.json';
  if (!fs.existsSync(rawPath)) {
    console.error('❌ No raw data found');
    process.exit(1);
  }
  
  const raw = JSON.parse(fs.readFileSync(rawPath, 'utf8'));
  
  // Normalize each post
  const normalized = raw.map(post => ({
    id: post.id,
    title: post.title?.trim() || 'Untitled',
    url: post.url?.trim() || '',
    source: post.source?.trim() || 'Unknown',
    country: post.country?.trim()?.toUpperCase() || '',
    region: post.region?.toLowerCase() || 'global',
    published_at: post.published_at || new Date().toISOString(),
    tags: Array.isArray(post.tags) ? post.tags.map(t => t.trim()) : [],
    risk_level: ['low', 'medium', 'high', 'critical'].includes(post.risk_level) ? post.risk_level : 'medium',
    excerpt: (post.excerpt || '').substring(0, 240),
    language: ['da', 'en', 'de', 'fr', 'es'].includes(post.language) ? post.language : 'en',
    collected_at: post.collected_at || new Date().toISOString()
  }));
  
  console.log(`✅ Normalized ${normalized.length} posts`);
  
  // Save normalized data
  const outputPath = './data/blog/.normalized.json';
  fs.writeFileSync(outputPath, JSON.stringify(normalized, null, 2));
  console.log(`📝 Saved to: ${outputPath}`);
}

normalize();
