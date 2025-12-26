#!/usr/bin/env node
/**
 * Blog Intel - Enrich
 * 
 * Enriches posts with additional tags and risk level analysis.
 */

const fs = require('fs');

/**
 * Keyword-based risk level detection
 */
function assessRiskLevel(post) {
  const text = (post.title + ' ' + post.excerpt).toLowerCase();
  
  // Critical indicators
  if (
    text.match(/critical\s+infrastructure|national\s+security|state-?sponsored|zero-?day|ransomware\s+attack|data\s+breach.*million|massive\s+hack/i)
  ) {
    return 'critical';
  }
  
  // High indicators
  if (
    text.match(/ransomware|ddos\s+attack|cyber\s+attack|hacker|breach|malware|phishing|exploit|vulnerability|trojan/i)
  ) {
    return 'high';
  }
  
  // Low indicators
  if (
    text.match(/guide|best\s+practice|tutorial|awareness|training|webinar|conference|report\s+published/i)
  ) {
    return 'low';
  }
  
  // Default to medium
  return 'medium';
}

/**
 * Extract additional tags from content
 */
function extractTags(post) {
  const text = (post.title + ' ' + post.excerpt).toLowerCase();
  const additionalTags = [];
  
  // Threat types
  if (text.includes('ransomware')) additionalTags.push('ransomware');
  if (text.includes('ddos')) additionalTags.push('DDoS');
  if (text.includes('phishing')) additionalTags.push('phishing');
  if (text.includes('malware')) additionalTags.push('malware');
  if (text.includes('breach') || text.includes('leak')) additionalTags.push('data-breach');
  
  // Sectors
  if (text.match(/healthcare|hospital|health/i)) additionalTags.push('healthcare');
  if (text.match(/finance|bank|financial/i)) additionalTags.push('finance');
  if (text.match(/government|municipal|ministry/i)) additionalTags.push('government');
  if (text.match(/critical\s+infrastructure|water|energy|power/i)) additionalTags.push('critical-infrastructure');
  if (text.match(/OT|operational\s+technology|SCADA|ICS/i)) additionalTags.push('OT');
  
  // Compliance
  if (text.match(/NIS2|GDPR|ISO\s*27001|compliance/i)) additionalTags.push('compliance');
  
  // Threat actors
  if (text.match(/russia|russian|pro-russian/i)) additionalTags.push('russia');
  if (text.match(/china|chinese/i)) additionalTags.push('china');
  if (text.match(/iran|iranian/i)) additionalTags.push('iran');
  if (text.match(/north\s+korea|dprk/i)) additionalTags.push('north-korea');
  
  // Election/political
  if (text.match(/election|vote|political|party/i)) additionalTags.push('election');
  
  return additionalTags;
}

function enrich() {
  console.log('✨ Enriching posts...');
  
  const dedupedPath = './data/blog/.deduped.json';
  if (!fs.existsSync(dedupedPath)) {
    console.error('❌ No deduped data found');
    process.exit(1);
  }
  
  const posts = JSON.parse(fs.readFileSync(dedupedPath, 'utf8'));
  
  const enriched = posts.map(post => {
    // Assess risk level if not already set or is default
    if (!post.risk_level || post.risk_level === 'medium') {
      post.risk_level = assessRiskLevel(post);
    }
    
    // Extract and add additional tags
    const newTags = extractTags(post);
    post.tags = [...new Set([...post.tags, ...newTags])]; // Unique tags
    
    return post;
  });
  
  console.log(`✅ Enriched ${enriched.length} posts`);
  
  // Save enriched data
  const outputPath = './data/blog/.enriched.json';
  fs.writeFileSync(outputPath, JSON.stringify(enriched, null, 2));
  console.log(`📝 Saved to: ${outputPath}`);
}

enrich();
