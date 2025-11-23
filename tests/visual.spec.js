const { test } = require('@playwright/test');

const viewports = [
  { name: 'mobile', width: 375, height: 812 },
  { name: 'tablet', width: 768, height: 1024 },
  { name: 'desktop', width: 1366, height: 768 }
];

for (const vp of viewports) {
  test(`homepage ${vp.name}`, async ({ page, browserName }) => {
    await page.setViewportSize({ width: vp.width, height: vp.height });
    await page.goto('https://blackbox.codes', { waitUntil: 'networkidle' });
    const filename = `artifacts/${browserName}-${vp.name}.png`;
    await page.screenshot({ path: filename, fullPage: true });
  });
}
