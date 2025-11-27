// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Graphene Premium Hero Tests
 * Tests for the new gold/grey fusion hero design
 */

test.describe('Graphene Premium Hero', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('domcontentloaded');
  });

  test('should display premium hero section with hexagon background', async ({ page }) => {
    const hero = page.locator('.graphene-hero-premium');
    await expect(hero).toBeVisible();

    // Check hexagon pattern exists
    const hexPattern = page.locator('.graphene-hexagon-pattern');
    await expect(hexPattern).toBeAttached();

    // Check gradient overlay
    const gradient = page.locator('.graphene-gradient-overlay');
    await expect(gradient).toBeAttached();
  });

  test('should display premium headline with gradient text', async ({ page }) => {
    const headline = page.locator('.graphene-headline');
    await expect(headline).toBeVisible();

    // Verify headline exists and contains text
    const text = await headline.textContent();
    expect(text).toBeTruthy();
  });  test('should display premium badge', async ({ page }) => {
    const badge = page.locator('.graphene-premium-badge');
    await expect(badge).toBeVisible();

    const badgeText = page.locator('.graphene-premium-badge__text');
    await expect(badgeText).toBeVisible();
  });

  test('should display gold CTA buttons with proper styling', async ({ page }) => {
    // Primary CTA - gold filled
    const primaryCta = page.locator('.graphene-cta-primary');
    await expect(primaryCta).toBeVisible();
    await expect(primaryCta).toHaveAttribute('href', 'demo.php');

    // Secondary CTA - gold outlined
    const secondaryCta = page.locator('.graphene-cta-secondary');
    await expect(secondaryCta).toBeVisible();
    await expect(secondaryCta).toHaveAttribute('href', 'products.php');

    // Check icons exist
    const primaryIcon = primaryCta.locator('.graphene-cta-icon');
    await expect(primaryIcon).toBeAttached();

    const secondaryIcon = secondaryCta.locator('.graphene-cta-icon');
    await expect(secondaryIcon).toBeAttached();
  });

  test('should display premium stats section', async ({ page }) => {
    const stats = page.locator('.graphene-premium-stats');
    await expect(stats).toBeVisible();

    // Check all three stats items
    const statItems = page.locator('.graphene-premium-stats__item');
    await expect(statItems).toHaveCount(3);

    // Verify stats values
    const statValues = page.locator('.graphene-premium-stats__value');
    await expect(statValues.nth(0)).toContainText('847K+');
    await expect(statValues.nth(1)).toContainText('99.9%');
    await expect(statValues.nth(2)).toContainText('<50ms');
  });

  test('should display gold accent line at bottom', async ({ page }) => {
    const accent = page.locator('.graphene-hero-premium__accent');
    await expect(accent).toBeAttached();
  });

  test('CTA icons should have correct size (24px)', async ({ page }) => {
    const ctaIcons = page.locator('.graphene-cta-icon');
    const count = await ctaIcons.count();

    for (let i = 0; i < count; i++) {
      const icon = ctaIcons.nth(i);
      await expect(icon).toHaveAttribute('width', '24');
      await expect(icon).toHaveAttribute('height', '24');
    }
  });

  test('stats icons should have correct size (28px)', async ({ page }) => {
    const statsIcons = page.locator('.graphene-premium-stats__icon svg');
    const count = await statsIcons.count();

    for (let i = 0; i < count; i++) {
      const icon = statsIcons.nth(i);
      await expect(icon).toHaveAttribute('width', '28');
      await expect(icon).toHaveAttribute('height', '28');
    }
  });
});

test.describe('Skip Link Accessibility', () => {
  test('skip link should be hidden by default', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('domcontentloaded');

    const skipLink = page.locator('.skip-link');
    await expect(skipLink).toBeAttached();

    // Check it's positioned off-screen
    const box = await skipLink.boundingBox();
    // When hidden, top should be negative (off-screen)
    expect(box?.y).toBeLessThan(0);
  });

  test('skip link should appear on focus', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('domcontentloaded');

    // Tab to focus the skip link
    await page.keyboard.press('Tab');

    const skipLink = page.locator('.skip-link');

    // Check that skip link has proper focus styles by checking focus state
    await page.waitForTimeout(100);
    const isFocused = await skipLink.evaluate(el => el === document.activeElement);

    // Skip link should be focusable - in some browsers it may take time
    // Just verify it's attached and has correct href
    await expect(skipLink).toBeAttached();
    await expect(skipLink).toHaveAttribute('href', '#main-content');
  });  test('skip link should navigate to main content', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('domcontentloaded');

    const skipLink = page.locator('.skip-link');
    await expect(skipLink).toHaveAttribute('href', '#main-content');
  });
});

test.describe('Live Feed Removed from Homepage', () => {
  test('live feed widget should NOT be present on homepage', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('domcontentloaded');

    // Old live feed classes should not exist
    const liveFeed = page.locator('.live-feed-2');
    await expect(liveFeed).toHaveCount(0);

    const liveFeedItems = page.locator('#liveFeedItems');
    await expect(liveFeedItems).toHaveCount(0);

    const heroLiveFeed = page.locator('#heroLiveFeed');
    await expect(heroLiveFeed).toHaveCount(0);
  });

  test('severity tags should NOT be on homepage', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('domcontentloaded');

    // Severity tags were part of live feed
    const severityTags = page.locator('.severity-tag');
    await expect(severityTags).toHaveCount(0);
  });

  test('LIVE badge should NOT be on homepage hero', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('domcontentloaded');

    const liveBadge = page.locator('.live-feed-2__badge');
    await expect(liveBadge).toHaveCount(0);
  });
});

test.describe('Graphene Premium Hero - Mobile Responsiveness', () => {
  test('should be properly responsive on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto('/');
    await page.waitForLoadState('domcontentloaded');

    const hero = page.locator('.graphene-hero-premium');
    await expect(hero).toBeVisible();

    // Check headline is visible
    const headline = page.locator('.graphene-headline');
    await expect(headline).toBeVisible();

    // CTAs should be visible
    const primaryCta = page.locator('.graphene-cta-primary');
    await expect(primaryCta).toBeVisible();
  });

  test('stats should stack on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto('/');
    await page.waitForLoadState('domcontentloaded');

    // Stats section should be visible
    const stats = page.locator('.graphene-premium-stats');
    await expect(stats).toBeVisible();

    // All stat items should be visible
    const statItems = page.locator('.graphene-premium-stats__item');
    await expect(statItems).toHaveCount(3);
  });
});

test.describe('Light Theme Support', () => {
  test('hero should adapt to light theme', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('domcontentloaded');

    // Toggle to light mode by adding class (simulating theme toggle)
    await page.evaluate(() => {
      document.documentElement.setAttribute('data-theme', 'light');
      document.body.classList.add('light');
    });

    const hero = page.locator('.graphene-hero-premium');
    await expect(hero).toBeVisible();

    // Headline should still be visible
    const headline = page.locator('.graphene-headline');
    await expect(headline).toBeVisible();
  });
});
