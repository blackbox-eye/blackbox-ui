#!/bin/bash
# Manual Verification Helper Script
# This script guides you through the manual verification steps for Sprint 4

set -e

echo "═══════════════════════════════════════════════════════════════"
echo "  Sprint 4 - Manual Verification Helper"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}This script will help you verify the Sprint 4 deployment.${NC}"
echo ""

# Step 1: Check if artifacts exist
echo -e "${YELLOW}Step 1: Checking for downloaded artifacts...${NC}"
echo ""

ARTIFACTS_DIR="./downloaded-artifacts"
VISUAL_SCREENSHOTS="$ARTIFACTS_DIR/visual-screenshots"
LIGHTHOUSE_REPORTS="$ARTIFACTS_DIR/lighthouse-report"

if [ ! -d "$ARTIFACTS_DIR" ]; then
  echo -e "${RED}⚠️  Artifacts directory not found!${NC}"
  echo ""
  echo "Please download artifacts from GitHub Actions first:"
  echo "  1. Go to: https://github.com/AlphaAcces/blackbox-ui/actions"
  echo "  2. Click on 'Visual Regression' workflow"
  echo "  3. Select the latest successful run"
  echo "  4. Download 'visual-screenshots' artifact"
  echo "  5. Extract to ./downloaded-artifacts/visual-screenshots/"
  echo ""
  echo "  6. Click on 'Lighthouse Audit' workflow"
  echo "  7. Select the latest successful run"
  echo "  8. Download lighthouse reports artifact"
  echo "  9. Extract to ./downloaded-artifacts/lighthouse-report/"
  echo ""
  echo "Then run this script again."
  exit 1
fi

# Check visual screenshots
if [ -d "$VISUAL_SCREENSHOTS" ]; then
  SCREENSHOT_COUNT=$(find "$VISUAL_SCREENSHOTS" -name "*.png" | wc -l)
  echo -e "${GREEN}✓ Visual screenshots found: $SCREENSHOT_COUNT files${NC}"
else
  echo -e "${RED}✗ Visual screenshots not found at: $VISUAL_SCREENSHOTS${NC}"
fi

# Check lighthouse reports
if [ -d "$LIGHTHOUSE_REPORTS" ]; then
  REPORT_COUNT=$(find "$LIGHTHOUSE_REPORTS" -name "*.html" -o -name "*.json" | wc -l)
  echo -e "${GREEN}✓ Lighthouse reports found: $REPORT_COUNT files${NC}"
else
  echo -e "${RED}✗ Lighthouse reports not found at: $LIGHTHOUSE_REPORTS${NC}"
fi

echo ""

# Step 2: Verify header screenshots
echo -e "${YELLOW}Step 2: Analyzing header screenshots...${NC}"
echo ""

if [ -d "$VISUAL_SCREENSHOTS" ]; then
  echo "Header screenshots by viewport:"
  for viewport in mobile tablet desktop-medium desktop-large; do
    HEADER_SCREENSHOTS=$(find "$VISUAL_SCREENSHOTS" -name "*-${viewport}-header-*.png" 2>/dev/null)
    COUNT=$(echo "$HEADER_SCREENSHOTS" | grep -c ".png" || echo "0")
    if [ "$COUNT" -gt 0 ]; then
      echo -e "  ${GREEN}✓${NC} $viewport: $COUNT browser(s)"
    else
      echo -e "  ${RED}✗${NC} $viewport: No screenshots found"
    fi
  done
  echo ""
  echo "Please manually review these screenshots to verify:"
  echo "  □ FAQ link is visible"
  echo "  □ Language buttons are visible"
  echo "  □ Navigation is properly laid out"
  echo "  □ Mobile menu toggle works (mobile/tablet)"
  echo ""
fi

# Step 3: Extract Lighthouse scores
echo -e "${YELLOW}Step 3: Extracting Lighthouse scores...${NC}"
echo ""

if [ -d "$LIGHTHOUSE_REPORTS" ] && command -v jq &> /dev/null; then
  JSON_REPORTS=$(find "$LIGHTHOUSE_REPORTS" -name "*.json" 2>/dev/null)
  
  if [ -n "$JSON_REPORTS" ]; then
    for report in $JSON_REPORTS; do
      echo "Report: $(basename $report)"
      
      PERF=$(jq -r '.categories.performance.score * 100 | floor' "$report" 2>/dev/null || echo "N/A")
      ACCESS=$(jq -r '.categories.accessibility.score * 100 | floor' "$report" 2>/dev/null || echo "N/A")
      BEST=$(jq -r '.categories["best-practices"].score * 100 | floor' "$report" 2>/dev/null || echo "N/A")
      SEO=$(jq -r '.categories.seo.score * 100 | floor' "$report" 2>/dev/null || echo "N/A")
      
      LCP=$(jq -r '.audits["largest-contentful-paint"].numericValue' "$report" 2>/dev/null || echo "N/A")
      # Note: max-potential-fid is deprecated, using total-blocking-time as alternative
      TBT=$(jq -r '.audits["total-blocking-time"].numericValue' "$report" 2>/dev/null || echo "N/A")
      FID=$(jq -r '.audits["max-potential-fid"].numericValue' "$report" 2>/dev/null || echo "$TBT")
      CLS=$(jq -r '.audits["cumulative-layout-shift"].numericValue' "$report" 2>/dev/null || echo "N/A")
      
      echo "  Performance: $PERF/100"
      echo "  Accessibility: $ACCESS/100"
      echo "  Best Practices: $BEST/100"
      echo "  SEO: $SEO/100"
      echo "  ---"
      echo "  LCP: ${LCP}ms (target: <2500ms)"
      echo "  FID: ${FID}ms (target: <100ms)"
      echo "  CLS: $CLS (target: <0.1)"
      echo ""
      
      # Check if metrics meet targets
      if [ "$PERF" != "N/A" ] && [ "$PERF" -ge 90 ]; then
        echo -e "  ${GREEN}✓ Performance score meets target (≥90)${NC}"
      elif [ "$PERF" != "N/A" ]; then
        echo -e "  ${YELLOW}⚠ Performance score below target: $PERF/100${NC}"
      fi
    done
  else
    echo -e "${YELLOW}No JSON reports found. Check HTML reports manually.${NC}"
  fi
elif [ ! command -v jq &> /dev/null ]; then
  echo -e "${YELLOW}jq not installed. Please install jq or check HTML reports manually.${NC}"
fi

echo ""

# Step 4: Summary
echo -e "${YELLOW}Step 4: Next Actions${NC}"
echo ""
echo "Manual verification checklist:"
echo "  □ Review all visual regression screenshots"
echo "  □ Verify header visibility at all screen sizes"
echo "  □ Check Lighthouse reports in browser"
echo "  □ Update SPRINT4_VERIFICATION_AUDIT.md with findings"
echo "  □ Test live site at https://blackbox.codes manually"
echo ""
echo "Files to update:"
echo "  • SPRINT4_VERIFICATION_AUDIT.md (add actual metrics)"
echo "  • CHANGELOG.md (if needed)"
echo ""

echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}Verification helper complete!${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
