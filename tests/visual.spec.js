const { test, expect } = require('@playwright/test');

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
    await page.goto('https://blackbox.codes', { waitUntil: 'networkidle' });
    
    // Wait for header to be visible
    await page.waitForSelector('header, nav, .header, .navbar', { timeout: 5000 }).catch(() => {
      console.log(`Note: No header element found at ${vp.name}`);
    });
    
    // Take full page screenshot
    const filename = `artifacts/${browserName}-${vp.name}-${vp.width}x${vp.height}.png`;
    await page.screenshot({ path: filename, fullPage: true });
    
    // Take header-only screenshot if header exists
    const headerSelector = 'header, nav, .header, .navbar';
    const headerExists = await page.$(headerSelector);
    if (headerExists) {
      const headerFilename = `artifacts/${browserName}-${vp.name}-header-${vp.width}x${vp.height}.png`;
      await page.locator(headerSelector).first().screenshot({ path: headerFilename });
    }
  });
}
