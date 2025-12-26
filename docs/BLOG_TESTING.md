# Blog Intel Engine - Testing Guide

This document describes the Playwright test suite for the Blog Intel Engine (blog.php).

## Test Coverage

### Cross-Browser Testing
Tests run on:
- ✅ **Chromium** (Chrome, Edge, Brave)
- ✅ **Firefox**
- ✅ **WebKit** (Safari, Mobile Safari)

### Viewport Testing
Tests cover:
1. **Desktop**: 1440x900 (standard widescreen)
2. **iPhone**: 390x844 (iPhone 14/15 size)
3. **iPad**: 820x1180 (iPad Air/Pro size)

## Test Suites

### 1. Core Functionality Tests
- HTTP 200 response
- Valid HTML structure
- Blog hero section
- Posts section or fallback message
- News section (global intel)
- Newsletter section

### 2. Mobile Responsiveness
- No horizontal scroll
- Mobile-friendly navigation
- Touch-friendly card spacing
- Vertical content stacking
- Filter pill wrapping

### 3. Interactive Features
- Category filtering (if available)
- Region tab switching
- External link rendering for JSON posts
- Filter pill activation

### 4. Accessibility
- Proper heading hierarchy (h1, h2, h3)
- ARIA labels on navigation
- Meaningful link text
- Keyboard navigation support

### 5. Visual Regression
- Full-page screenshots for each viewport/browser combination
- Artifacts saved to `artifacts/blog-*.png`

## Running Tests

### Run all blog tests
```bash
npm test -- tests/blog-intel-cross-browser.spec.js
```

### Run specific browser
```bash
npx playwright test tests/blog-intel-cross-browser.spec.js --project=chromium
npx playwright test tests/blog-intel-cross-browser.spec.js --project=firefox
npx playwright test tests/blog-intel-cross-browser.spec.js --project=webkit
```

### Run specific viewport tests
```bash
npx playwright test tests/blog-intel-cross-browser.spec.js -g "desktop"
npx playwright test tests/blog-intel-cross-browser.spec.js -g "iphone"
npx playwright test tests/blog-intel-cross-browser.spec.js -g "ipad"
```

### Debug mode (headed browser)
```bash
npx playwright test tests/blog-intel-cross-browser.spec.js --headed --project=chromium
```

### Generate test report
```bash
npx playwright test tests/blog-intel-cross-browser.spec.js --reporter=html
npx playwright show-report
```

## CI/CD Integration

Tests run automatically on:
- Pull requests
- Pushes to main branch
- Manual workflow dispatch

### GitHub Actions Workflow
Tests are integrated into `.github/workflows/ci.yml`:
- Runs after deployment
- Validates blog.php is accessible
- Captures screenshots on failure
- Uploads artifacts for review

## Test Data Scenarios

Tests handle two scenarios:

### 1. Empty Blog (No Posts)
When `posts.json` is empty or database unavailable:
- ✅ HTTP 200 response
- ✅ Graceful fallback message displayed
- ✅ No JavaScript errors
- ✅ News section still renders (curated content)

### 2. Blog with Posts
When posts exist (database or JSON):
- ✅ Posts grid renders
- ✅ Blog cards display
- ✅ Filters work (if categories/tags exist)
- ✅ External links for JSON intel posts
- ✅ Pagination (if >9 posts)

## Screenshot Artifacts

Screenshots are captured for each combination:
- `artifacts/blog-desktop-1440x900.png`
- `artifacts/blog-iphone-390x844.png`
- `artifacts/blog-ipad-820x1180.png`

These artifacts are:
- Uploaded to GitHub Actions artifacts
- Useful for visual regression review
- Prove cross-browser/viewport compatibility

## Expected Test Results

### Passing Criteria
✅ All tests pass on at least Chromium  
✅ No horizontal scroll on any viewport  
✅ HTTP 200 on all scenarios  
✅ Graceful fallback when no posts  
✅ External links work for JSON posts  

### Known Limitations
- Firefox/WebKit may not be installed locally (CI will test)
- Some tests are conditional (depend on data availability)
- Newsletter form submission not tested (requires backend)

## Troubleshooting

### Browsers not installed
```bash
npx playwright install chromium firefox webkit
```

### PHP server not starting
Check if port 8000 is available:
```bash
lsof -i :8000
kill -9 <PID>
```

### Tests timing out
Increase timeout in playwright.config.js:
```javascript
timeout: 60000  // 60 seconds
```

## Future Enhancements

- [ ] Test RSS feed export (/blog.rss.xml)
- [ ] Test chain-of-custody metadata display
- [ ] Test Weekly Brief section
- [ ] Test advanced filtering (multi-select)
- [ ] Test client-side search functionality
- [ ] Performance testing (Lighthouse CI)
- [ ] Accessibility testing (axe-core)

## Related Files

- `tests/blog-intel-cross-browser.spec.js` - Main test suite
- `playwright.config.js` - Playwright configuration
- `.github/workflows/ci.yml` - CI/CD pipeline
- `blog.php` - Blog page under test
- `data/blog/posts.json` - Test data source
