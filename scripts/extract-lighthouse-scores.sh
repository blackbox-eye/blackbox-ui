#!/bin/bash
# Script to extract Lighthouse scores from the artifacts
# This script helps analyze the Lighthouse CI results

set -e

echo "=== Lighthouse Score Extractor ==="
echo ""

# Check if artifacts directory exists
if [ ! -d ".lighthouseci" ]; then
  echo "❌ No .lighthouseci directory found. Run Lighthouse CI first."
  exit 1
fi

# Find all JSON reports
JSON_REPORTS=$(find .lighthouseci -name "*.json" 2>/dev/null || echo "")

if [ -z "$JSON_REPORTS" ]; then
  echo "❌ No Lighthouse JSON reports found."
  exit 1
fi

echo "📊 Found Lighthouse reports:"
echo ""

for report in $JSON_REPORTS; do
  echo "Processing: $report"
  
  # Extract scores using jq if available
  if command -v jq &> /dev/null; then
    PERF=$(jq -r '.categories.performance.score * 100 | floor' "$report" 2>/dev/null || echo "N/A")
    ACCESS=$(jq -r '.categories.accessibility.score * 100 | floor' "$report" 2>/dev/null || echo "N/A")
    BEST=$(jq -r '.categories["best-practices"].score * 100 | floor' "$report" 2>/dev/null || echo "N/A")
    SEO=$(jq -r '.categories.seo.score * 100 | floor' "$report" 2>/dev/null || echo "N/A")
    
    # Extract Core Web Vitals
    LCP=$(jq -r '.audits["largest-contentful-paint"].numericValue' "$report" 2>/dev/null || echo "N/A")
    FID=$(jq -r '.audits["max-potential-fid"].numericValue' "$report" 2>/dev/null || echo "N/A")
    CLS=$(jq -r '.audits["cumulative-layout-shift"].numericValue' "$report" 2>/dev/null || echo "N/A")
    
    echo "  Performance: $PERF"
    echo "  Accessibility: $ACCESS"
    echo "  Best Practices: $BEST"
    echo "  SEO: $SEO"
    echo "  ---"
    echo "  LCP (Largest Contentful Paint): ${LCP}ms"
    echo "  FID (First Input Delay): ${FID}ms"
    echo "  CLS (Cumulative Layout Shift): $CLS"
  else
    echo "  ⚠️  jq not installed, showing raw file location"
  fi
  echo ""
done

echo "✅ Done!"
