const { test, expect } = require('@playwright/test');

async function getCssProperty(locator, property) {
  return locator.evaluate((el, prop) => getComputedStyle(el)[prop], property);
}

test.describe('Frontpage responsive sanity', () => {
  const breakpoints = [320, 360, 390, 414, 480, 540, 768, 1024, 1280];

  test('brand assets resolve (no 404)', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 900 });
    await page.goto('/', { waitUntil: 'networkidle' });

    const assetUrls = [
      new URL('/assets/new_logo_white_BBX.svg', page.url()).toString(),
      new URL('/assets/new_logo_black_BBX.svg', page.url()).toString(),
      new URL('/assets/icon_box.png', page.url()).toString(),
    ];

    for (const url of assetUrls) {
      const resp = await page.request.get(url);
      expect(resp.status(), `Expected 2xx for ${url}`).toBeGreaterThanOrEqual(200);
      expect(resp.status(), `Expected 2xx for ${url}`).toBeLessThan(300);
    }

    const logoWhite = page.locator('img.header-logo--white');
    const logoBlack = page.locator('img.header-logo--black');
    await expect(logoWhite).toBeAttached();
    await expect(logoBlack).toBeAttached();

    // Only one is visible depending on current theme.
    const whiteVisible = await logoWhite.isVisible();
    const blackVisible = await logoBlack.isVisible();
    expect(whiteVisible || blackVisible).toBeTruthy();

    // Ensure whichever is visible has actually loaded.
    if (whiteVisible) {
      await expect.poll(() => logoWhite.evaluate((img) => img.complete)).toBe(true);
    }
    if (blackVisible) {
      await expect.poll(() => logoBlack.evaluate((img) => img.complete)).toBe(true);
    }
  });

  test('header and badge stay stable across breakpoints without horizontal overflow', async ({ page }) => {
    for (const width of breakpoints) {
      await page.setViewportSize({ width, height: 900 });
      await page.goto('/', { waitUntil: 'networkidle' });

      const header = page.locator('.bbx-header');
      await expect(header).toBeVisible();

      const headerBox = await header.boundingBox();
      expect(headerBox).not.toBeNull();
      expect(headerBox.height).toBeLessThan(200);

      const hasOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
      expect(hasOverflow).toBeFalsy();

      const badgeWrapper = page.locator('.graphene-floating-badge');
      await expect(badgeWrapper).toBeVisible();

      const wrapperBorderTopWidth = await getCssProperty(badgeWrapper, 'borderTopWidth');
      expect(wrapperBorderTopWidth).toBe('0px');

      const wrapperBoxShadow = await getCssProperty(badgeWrapper, 'boxShadow');
      expect(wrapperBoxShadow === 'none' || wrapperBoxShadow === '').toBeTruthy();

      const badgeInner = page.locator('.graphene-floating-badge__inner');
      await expect(badgeInner).toBeVisible();
      const innerRadius = await getCssProperty(badgeInner, 'borderRadius');
      expect(innerRadius).not.toBe('0px');
    }
  });

  test('assistant rail stays fixed on smallest viewport', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 900 });
    await page.goto('/', { waitUntil: 'networkidle' });

    const rail = page.locator('.bbx-command-rail');
    await expect(rail).toBeVisible();

    const railPosition = await getCssProperty(rail, 'position');
    expect(railPosition).toBe('fixed');

    const toggle = page.locator('#alphabot-toggle-btn');
    await expect(toggle).toBeVisible();
  });
});
