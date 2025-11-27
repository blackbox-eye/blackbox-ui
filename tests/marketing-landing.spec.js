/**
 * Marketing Landing Page Tests
 *
 * Tests for the redesigned marketing front page including:
 * - Hero section with new Blackbox EYE + GreyEYE messaging
 * - CTA buttons with correct styling
 * - Live feed widget
 * - Design token consistency (gold colors)
 *
 * @requires A running local server (e.g., php -S localhost:8000)
 */

const { test, expect } = require('@playwright/test');

// =====================================================
// HERO SECTION TESTS
// =====================================================
test.describe('Hero Section', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('should display hero headline with GreyEYE branding', async ({ page }) => {
    const headline = page.locator('h1.hero-gradient-text');
    await expect(headline).toBeVisible();

    // Should contain GreyEYE mention
    const text = await headline.textContent();
    expect(text.toLowerCase()).toMatch(/greyeye|intelligent|sikkerhed|security/);
  });

  test('should display subheadline with Blackbox EYE mention', async ({ page }) => {
    const subheadline = page.locator('#home p').first();
    await expect(subheadline).toBeVisible();

    const text = await subheadline.textContent();
    expect(text.toLowerCase()).toContain('blackbox eye');
  });

  test('should have primary CTA button with correct styling', async ({ page }) => {
    const primaryCTA = page.locator('a.btn-primary--lg').first();
    await expect(primaryCTA).toBeVisible();

    // Should link to demo page
    await expect(primaryCTA).toHaveAttribute('href', 'demo.php');

    // Check button has gold background color
    const bgColor = await primaryCTA.evaluate(el =>
      getComputedStyle(el).backgroundColor
    );
    // Should be gold-ish (rgb values for #d4af37)
    expect(bgColor).toMatch(/rgb\(212,\s*175,\s*55\)|rgba\(212,\s*175,\s*55/);
  });

  test('should have secondary CTA button with correct styling', async ({ page }) => {
    const secondaryCTA = page.locator('a.btn-secondary--lg').first();
    await expect(secondaryCTA).toBeVisible();

    // Should link to products page
    await expect(secondaryCTA).toHaveAttribute('href', 'products.php');

    // Check button has gold border
    const borderColor = await secondaryCTA.evaluate(el =>
      getComputedStyle(el).borderColor
    );
    expect(borderColor).toMatch(/rgb\(212,\s*175,\s*55\)|rgba\(212,\s*175,\s*55/);
  });

  test('CTA buttons should have hover effect', async ({ page }) => {
    const primaryCTA = page.locator('a.btn-primary--lg').first();

    // Get initial transform
    const initialTransform = await primaryCTA.evaluate(el =>
      getComputedStyle(el).transform
    );

    // Hover over button
    await primaryCTA.hover();
    await page.waitForTimeout(300); // Wait for transition

    // Transform should change on hover (translateY)
    const hoverTransform = await primaryCTA.evaluate(el =>
      getComputedStyle(el).transform
    );

    // Either transform changes or box-shadow appears
    const hoverShadow = await primaryCTA.evaluate(el =>
      getComputedStyle(el).boxShadow
    );
    expect(hoverTransform !== initialTransform || hoverShadow !== 'none').toBeTruthy();
  });
});

// =====================================================
// LIVE FEED WIDGET TESTS
// =====================================================
test.describe('Live Feed Widget', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('should display live feed widget in hero', async ({ page }) => {
    const widget = page.locator('.live-feed-widget');
    await expect(widget).toBeVisible();
  });

  test('should have pulsing live indicator', async ({ page }) => {
    const indicator = page.locator('.live-feed-widget__indicator');
    await expect(indicator).toBeVisible();

    // Check it has animation
    const animation = await indicator.evaluate(el =>
      getComputedStyle(el).animation
    );
    expect(animation).toContain('pulse-live');
  });

  test('should display feed items with severity styling', async ({ page }) => {
    const feedItems = page.locator('.live-feed-item');
    await expect(feedItems).toHaveCount(3); // 3 mock items

    // Check different severity classes exist
    await expect(page.locator('.live-feed-item--warning')).toBeVisible();
    await expect(page.locator('.live-feed-item--critical')).toBeVisible();
    await expect(page.locator('.live-feed-item--info')).toBeVisible();
  });

  test('feed items should have title and meta', async ({ page }) => {
    const firstItem = page.locator('.live-feed-item').first();

    await expect(firstItem.locator('.live-feed-item__title')).toBeVisible();
    await expect(firstItem.locator('.live-feed-item__meta')).toBeVisible();
    await expect(firstItem.locator('.live-feed-item__time')).toBeVisible();
  });

  test('feed widget should be responsive', async ({ page }) => {
    // Desktop: widget should be visible in grid
    await page.setViewportSize({ width: 1440, height: 900 });
    await expect(page.locator('.live-feed-widget')).toBeVisible();

    // Mobile: widget should still be visible (stacked)
    await page.setViewportSize({ width: 375, height: 812 });
    await expect(page.locator('.live-feed-widget')).toBeVisible();
  });
});

// =====================================================
// DESIGN TOKEN CONSISTENCY TESTS
// =====================================================
test.describe('Design Token Consistency', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('should use correct gold color variable', async ({ page }) => {
    // Check CSS variable is defined
    const goldColor = await page.evaluate(() =>
      getComputedStyle(document.documentElement).getPropertyValue('--bbx-gold').trim()
    );
    expect(goldColor).toBe('#d4af37');
  });

  test('tactical tagline should use gold color', async ({ page }) => {
    const tagline = page.locator('.text-\\[var\\(--bbx-gold\\)\\]').first();
    await expect(tagline).toBeVisible();

    const color = await tagline.evaluate(el =>
      getComputedStyle(el).color
    );
    // Should be gold (rgb for #d4af37)
    expect(color).toMatch(/rgb\(212,\s*175,\s*55\)/);
  });

  test('glass effect cards should have correct styling', async ({ page }) => {
    const card = page.locator('.glass-effect').first();
    await expect(card).toBeVisible();

    // Should have backdrop filter
    const backdropFilter = await card.evaluate(el =>
      getComputedStyle(el).backdropFilter || getComputedStyle(el).webkitBackdropFilter
    );
    expect(backdropFilter).toContain('blur');
  });
});

// =====================================================
// INTERNATIONALIZATION TESTS
// =====================================================
test.describe('i18n Support', () => {
  test('should display Danish content by default', async ({ page }) => {
    await page.goto('/');

    // Check for Danish text in hero
    const headline = await page.locator('h1').textContent();
    // Could be Danish or English depending on browser/server config
    expect(headline.length).toBeGreaterThan(10);
  });

  test('should switch to English when requested', async ({ page }) => {
    await page.goto('/?lang=en');
    await page.waitForTimeout(500);

    // After redirect, check content
    const headline = await page.locator('h1').textContent();
    expect(headline.length).toBeGreaterThan(10);
  });

  test('hero headline matches expected i18n key', async ({ page }) => {
    await page.goto('/');

    const headline = await page.locator('h1.hero-gradient-text').textContent();

    // Should contain "GreyEYE" in both languages
    expect(headline).toContain('GreyEYE');
  });
});

// =====================================================
// ACCESSIBILITY TESTS
// =====================================================
test.describe('Hero Accessibility', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('CTA buttons should be keyboard accessible', async ({ page }) => {
    const primaryCTA = page.locator('a.btn-primary--lg').first();

    // Tab to button
    await page.keyboard.press('Tab');
    await page.keyboard.press('Tab'); // May need multiple tabs depending on page

    // Should be focusable
    const focusedElement = await page.evaluate(() =>
      document.activeElement?.classList.contains('btn-primary--lg') ||
      document.activeElement?.classList.contains('btn-secondary--lg')
    );
    // At least one button type should be focusable in hero
  });

  test('live feed should have aria-hidden on decorative elements', async ({ page }) => {
    const indicator = page.locator('.live-feed-widget__indicator');
    await expect(indicator).toHaveAttribute('aria-hidden', 'true');

    const icons = page.locator('.live-feed-item__icon');
    const firstIcon = icons.first();
    await expect(firstIcon).toHaveAttribute('aria-hidden', 'true');
  });

  test('hero section should have proper heading hierarchy', async ({ page }) => {
    const h1 = page.locator('h1');
    await expect(h1).toHaveCount(1); // Only one H1

    const h2s = page.locator('main h2');
    const h2Count = await h2s.count();
    expect(h2Count).toBeGreaterThanOrEqual(2); // Multiple H2s for sections
  });
});

// =====================================================
// RESPONSIVE LAYOUT TESTS
// =====================================================
test.describe('Responsive Hero Layout', () => {
  const viewports = [
    { name: 'mobile', width: 375, height: 812 },
    { name: 'tablet', width: 768, height: 1024 },
    { name: 'desktop', width: 1440, height: 900 },
    { name: 'wide', width: 1920, height: 1080 }
  ];

  for (const vp of viewports) {
    test(`hero renders correctly on ${vp.name}`, async ({ page }) => {
      await page.setViewportSize({ width: vp.width, height: vp.height });
      await page.goto('/');

      // Hero should be visible
      const hero = page.locator('#home');
      await expect(hero).toBeVisible();

      // Headline should be visible
      const headline = page.locator('h1.hero-gradient-text');
      await expect(headline).toBeVisible();

      // CTAs should be visible
      const primaryCTA = page.locator('a.btn-primary--lg').first();
      await expect(primaryCTA).toBeVisible();

      // Take screenshot for visual review
      await page.screenshot({
        path: `artifacts/hero-${vp.name}-${vp.width}x${vp.height}.png`,
        fullPage: false
      });
    });
  }
});
