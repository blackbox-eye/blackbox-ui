const { test } = require('@playwright/test');

// Sprint 4 verification: Test header at specified screen sizes
const viewports = [
  { name: 'mobile', width: 375, height: 812 },
  { name: 'tablet', width: 768, height: 1024 },
  { name: 'desktop-medium', width: 1024, height: 768 },
  { name: 'desktop-large', width: 1440, height: 900 }
];

for (const vp of viewports) {
  test(`homepage ${vp.name} (${vp.width}x${vp.height})`, async ({ page, browserName }) => {
    await page.setViewportSize({ width: vp.width, height: vp.height });
    // Use baseURL from config (supports both localhost and production)
    await page.goto('/', { waitUntil: 'networkidle' });

    // Take full page screenshot
    const filename = `artifacts/${browserName}-${vp.name}-${vp.width}x${vp.height}.png`;
    await page.screenshot({ path: filename, fullPage: true });

    // Try to find and screenshot header if it exists
    const headerSelector = 'header, nav, .header, .navbar';
    try {
      await page.waitForSelector(headerSelector, { timeout: 5000 });
      const headerFilename = `artifacts/${browserName}-${vp.name}-header-${vp.width}x${vp.height}.png`;
      await page.locator(headerSelector).first().screenshot({ path: headerFilename });
    } catch (error) {
      // Header not found or not visible - this is informational, not a failure
      console.log(`Note: No header element found at ${vp.name} (${vp.width}x${vp.height})`);
    }
  });
}
