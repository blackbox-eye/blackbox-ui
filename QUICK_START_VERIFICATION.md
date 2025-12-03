# Quick Start: Completing Sprint 4 Verification

## Overview

This guide walks you through completing the Sprint 4 verification audit. The automation is in place - you just need to trigger the workflows and collect the results.

## Prerequisites

✅ All code changes have been committed and pushed  
✅ Workflows are configured and ready to run  
✅ GitHub Actions has necessary secrets (SITE_URL, CF_ZONE_ID, CF_API_TOKEN)

---

## Step 1: Trigger Workflow Runs

### Option A: Automatic (Recommended)
Merge this PR to `main` branch. This will automatically trigger:
- Visual Regression workflow
- Lighthouse Audit workflow
- CI/CD Deploy workflow (if on main)

### Option B: Manual Trigger
1. Go to: https://github.com/AlphaAcces/blackbox-ui/actions
2. Select "Visual Regression" workflow
3. Click "Run workflow" dropdown
4. Click "Run workflow" button
5. Repeat for "Lighthouse Audit" workflow

---

## Step 2: Wait for Workflow Completion

Monitor the workflows at:
- https://github.com/AlphaAcces/blackbox-ui/actions/workflows/visual-regression.yml
- https://github.com/AlphaAcces/blackbox-ui/actions/workflows/lighthouse.yml

Expected duration: 2-5 minutes each

---

## Step 3: Download Artifacts

### Visual Regression Screenshots

1. Go to: https://github.com/AlphaAcces/blackbox-ui/actions/workflows/visual-regression.yml
2. Click on the most recent **successful** run (green checkmark)
3. Scroll to bottom "Artifacts" section
4. Click **"visual-screenshots"** to download ZIP file
5. Extract to a local folder: `./downloaded-artifacts/visual-screenshots/`

### Lighthouse Reports

1. Go to: https://github.com/AlphaAcces/blackbox-ui/actions/workflows/lighthouse.yml
2. Click on the most recent **successful** run
3. Scroll to bottom "Artifacts" section
4. Download the lighthouse report artifact
5. Extract to: `./downloaded-artifacts/lighthouse-report/`

---

## Step 4: Review Screenshots

Open the extracted screenshots and verify:

### Mobile (375×812px)
- [ ] Header visible with FAQ link
- [ ] Language buttons visible
- [ ] Mobile menu toggle present
- [ ] Navigation properly collapsed

**Screenshot files:**
- `chromium-mobile-375x812.png`
- `firefox-mobile-375x812.png`
- `webkit-mobile-375x812.png`
- `chromium-dark-mobile-375x812.png`

### Tablet (768×1024px)
- [ ] Header layout adapts properly
- [ ] FAQ link visible
- [ ] Language buttons visible
- [ ] Navigation may be expanded or collapsed

**Screenshot files:**
- `chromium-tablet-768x1024.png`
- `firefox-tablet-768x1024.png`
- `webkit-tablet-768x1024.png`
- `chromium-dark-tablet-768x1024.png`

### Desktop Medium (1024×768px)
- [ ] Full desktop header layout
- [ ] All navigation items visible
- [ ] FAQ link prominent
- [ ] Language buttons in correct position

**Screenshot files:**
- `chromium-desktop-medium-1024x768.png`
- `firefox-desktop-medium-1024x768.png`
- `webkit-desktop-medium-1024x768.png`
- `chromium-dark-desktop-medium-1024x768.png`

### Desktop Large (1440×900px)
- [ ] Full desktop header layout
- [ ] All navigation items visible with proper spacing
- [ ] FAQ link prominent
- [ ] Language buttons in correct position

**Screenshot files:**
- `chromium-desktop-large-1440x900.png`
- `firefox-desktop-large-1440x900.png`
- `webkit-desktop-large-1440x900.png`
- `chromium-dark-desktop-large-1440x900.png`

### Header-Specific Screenshots
Each viewport also has header-only screenshots with `-header-` in the filename for detailed inspection.

---

## Step 5: Review Lighthouse Reports

### Open HTML Report
1. Navigate to `./downloaded-artifacts/lighthouse-report/`
2. Open the HTML file in your browser
3. Review all 4 categories:
   - Performance
   - Accessibility
   - Best Practices
   - SEO

### Extract Scores (Automated)
```bash
cd /path/to/blackbox-ui
./scripts/extract-lighthouse-scores.sh
```

This will display:
- Performance score (target: ≥90)
- Accessibility score (target: ≥90)
- Best Practices score (target: ≥90)
- SEO score (target: ≥90)
- LCP - Largest Contentful Paint (target: <2500ms)
- FID - First Input Delay (target: <100ms)
- CLS - Cumulative Layout Shift (target: <0.1)

### Manual Review (If jq not available)
Open the HTML report and look for:
1. **Core Web Vitals** section
2. Note LCP, FID, CLS values
3. Check "Opportunities" for improvement suggestions
4. Review "Diagnostics" for potential issues

---

## Step 6: Update Documentation

### Update SPRINT4_VERIFICATION_AUDIT.md

Replace the "TBD" placeholders with actual values:

```markdown
### Performance Metrics

**Core Web Vitals:**
- **LCP** (Largest Contentful Paint): 1250 ms ← Insert actual value
- **FID** (First Input Delay): 45 ms ← Insert actual value
- **CLS** (Cumulative Layout Shift): 0.05 ← Insert actual value

**Lighthouse Scores:**
- **Performance**: 95/100 ← Insert actual value
- **Accessibility**: 98/100 ← Insert actual value
- **Best Practices**: 92/100 ← Insert actual value
- **SEO**: 100/100 ← Insert actual value
```

Check off completed items in the checklists:
```markdown
- [x] FAQ link visible and functional
- [x] Language buttons visible
- [x] Navigation menu responsive at all breakpoints
```

---

## Step 7: Manual Browser Testing (Optional but Recommended)

### Test in Real Browsers

1. **Chrome/Brave**
   - Open https://blackbox.codes
   - Press F12 (DevTools)
   - Press Ctrl+Shift+M (Device Toolbar)
   - Test at each viewport size
   - Enable dark mode in Brave to test appearance

2. **Firefox**
   - Open https://blackbox.codes
   - Press F12 (DevTools)
   - Enable Responsive Design Mode
   - Test at each viewport size

3. **Edge** (if available)
   - Similar to Chrome (Chromium-based)
   - Test at viewport sizes

### Take Manual Screenshots
If you want to supplement automated screenshots:
1. Set viewport to each size
2. Press F12, then Ctrl+Shift+P
3. Type "screenshot" and select "Capture full size screenshot"
4. Save with descriptive name

---

## Step 8: Run Verification Helper Script

```bash
cd /path/to/blackbox-ui
./scripts/verify-deployment.sh
```

This script will:
- Check if artifacts are downloaded
- Count screenshot files
- Analyze header screenshots by viewport
- Extract Lighthouse scores (if jq available)
- Display verification checklist
- Suggest next actions

---

## Step 9: Complete Release Checklist

In `SPRINT4_VERIFICATION_AUDIT.md`, complete the Pre-Release Checklist:

```markdown
### Pre-Release Checklist

- [x] All automated tests passing (Visual Regression + Lighthouse)
- [x] Header verified at all required screen sizes
- [x] Cross-browser screenshots reviewed and approved
- [x] Performance metrics meet acceptance criteria
- [x] Cloudflare cache purged
- [x] Database connectivity verified
- [x] Release notes prepared
- [x] CHANGELOG.md updated
```

---

## Step 10: Finalize Release

### Update CHANGELOG.md
Add entry for v1.0.0-sprint4 with:
- Date
- New features
- Improvements
- Bug fixes (if any)

### Tag Release
```bash
git tag -a v1.0.0-sprint4 -m "Sprint 4: Enhanced verification and testing infrastructure"
git push origin v1.0.0-sprint4
```

### Create GitHub Release
1. Go to: https://github.com/AlphaAcces/blackbox-ui/releases/new
2. Select tag: v1.0.0-sprint4
3. Title: "v1.0.0-sprint4 - Enhanced Verification & Testing"
4. Description: Copy from RELEASE_NOTES_v1.0.0-sprint4.md
5. Attach artifacts (optional):
   - visual-screenshots.zip
   - lighthouse-report.zip
6. Publish release

---

## Troubleshooting

### Workflow Failed
1. Click on the failed workflow run
2. Check the error logs
3. Common issues:
   - Missing secrets (SITE_URL, CF_ZONE_ID, CF_API_TOKEN)
   - Site unreachable (check if blackbox.codes is accessible)
   - Browser installation failed (usually auto-resolves on retry)

### No Artifacts Available
- Ensure workflow completed successfully (green checkmark)
- Artifacts expire after 90 days - check run date
- Re-run workflow if needed

### Screenshots Don't Show Header
- This could indicate a real issue with the site
- Check the live site manually
- Review the page HTML structure
- Update test selectors if header elements changed

---

## Summary

You have:
✅ Fixed Lighthouse workflow configuration  
✅ Enhanced visual regression tests with required viewports  
✅ Added cross-browser testing (Chrome, Firefox, Safari, Brave/dark)  
✅ Created comprehensive documentation and release notes  
✅ Added utility scripts for score extraction and verification  

**Next**: Trigger workflows, download artifacts, review results, and update documentation with actual metrics.

---

**Questions?** Check:
- SPRINT4_VERIFICATION_AUDIT.md - Detailed verification procedures
- RELEASE_NOTES_v1.0.0-sprint4.md - Complete sprint summary
- Scripts in `scripts/` directory for automation help

**Contact**: ops@blackbox.codes
