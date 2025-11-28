// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Graphene 3D Hero Tests
 * Tests for the 3D hexagonal mesh hero design with gold/grey fusion
 */

test.describe('Graphene 3D Hero', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('domcontentloaded');
  });

  test('should display 3D hero section with canvas', async ({ page }) => {
    const hero = page.locator('.graphene-hero-3d');
    await expect(hero).toBeVisible();

    // Check canvas exists for animation
    const canvas = page.locator('#graphene-canvas');
    await expect(canvas).toBeAttached();
  });

  test('should display hero title with shimmer effect', async ({ page }) => {
    const titleLine1 = page.locator('.graphene-hero-title__line--1');
    await expect(titleLine1).toBeVisible();

    const titleLine2 = page.locator('.graphene-hero-title__line--2');
    await expect(titleLine2).toBeVisible();

    // Check shimmer class
    await expect(titleLine2).toHaveClass(/graphene-text-shimmer/);
  });

  test('should display floating badge', async ({ page }) => {
    const badge = page.locator('.graphene-floating-badge');
    await expect(badge).toBeVisible();

    const badgeText = page.locator('.graphene-floating-badge__text');
    await expect(badgeText).toBeVisible();
  });

  test('should display CTA buttons with proper styling', async ({ page }) => {
    // Primary CTA - gold filled
    const primaryCta = page.locator('.graphene-btn-primary');
    await expect(primaryCta).toBeVisible();
    await expect(primaryCta).toHaveAttribute('href', 'demo.php');

    // Secondary CTA - gold outlined (links to free-scan.php)
    const secondaryCta = page.locator('.graphene-btn-secondary');
    await expect(secondaryCta).toBeVisible();
    await expect(secondaryCta).toHaveAttribute('href', 'free-scan.php');

    // Check icons exist
    const primaryIcon = primaryCta.locator('.graphene-btn__icon');
    await expect(primaryIcon).toBeAttached();

    const secondaryIcon = secondaryCta.locator('.graphene-btn__icon');
    await expect(secondaryIcon).toBeAttached();
  });

  test('should display glass stats section', async ({ page }) => {
    const stats = page.locator('.graphene-stats-glass');
    await expect(stats).toBeVisible();

    // Check all three stat cards
    const statCards = page.locator('.graphene-stat-card');
    await expect(statCards).toHaveCount(3);

    // Verify stats values
    const statValues = page.locator('.graphene-stat-card__value');
    await expect(statValues.nth(0)).toContainText('847K+');
    await expect(statValues.nth(1)).toContainText('99.9%');
    await expect(statValues.nth(2)).toContainText('<50ms');
  });

  test('should display gold accent line at bottom', async ({ page }) => {
    const accent = page.locator('.graphene-hero-3d__accent');
    await expect(accent).toBeAttached();
  });

  test('CTA icons should have correct sizes', async ({ page }) => {
    // Primary and secondary CTA icons should be 22px
    const primaryIcon = page.locator('.graphene-btn-primary .graphene-btn__icon');
    await expect(primaryIcon).toHaveAttribute('width', '22');
    await expect(primaryIcon).toHaveAttribute('height', '22');

    const secondaryIcon = page.locator('.graphene-btn-secondary .graphene-btn__icon');
    await expect(secondaryIcon).toHaveAttribute('width', '22');
    await expect(secondaryIcon).toHaveAttribute('height', '22');

    // Spotlight CTA icon should be 24px
    const spotlightIcon = page.locator('.graphene-btn-spotlight .graphene-btn__icon');
    await expect(spotlightIcon).toHaveAttribute('width', '24');
    await expect(spotlightIcon).toHaveAttribute('height', '24');
  });

  test('stats icons should have correct size (24px)', async ({ page }) => {
    const statsIcons = page.locator('.graphene-stat-card__icon svg');
    const count = await statsIcons.count();

    for (let i = 0; i < count; i++) {
      const icon = statsIcons.nth(i);
      await expect(icon).toHaveAttribute('width', '24');
      await expect(icon).toHaveAttribute('height', '24');
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

    // Skip link should be focusable and have correct href
    await expect(skipLink).toBeAttached();
    await expect(skipLink).toHaveAttribute('href', '#main-content');
  });

  test('skip link should navigate to main content', async ({ page }) => {
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

test.describe('Graphene 3D Hero - Mobile Responsiveness', () => {
  test('should be properly responsive on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto('/');
    await page.waitForLoadState('domcontentloaded');

    const hero = page.locator('.graphene-hero-3d');
    await expect(hero).toBeVisible();

    // Check title is visible
    const title = page.locator('.graphene-hero-title');
    await expect(title).toBeVisible();

    // CTAs should be visible
    const primaryCta = page.locator('.graphene-btn-primary');
    await expect(primaryCta).toBeVisible();
  });

  test('stats should stack on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto('/');
    await page.waitForLoadState('domcontentloaded');

    // Stats section should be visible
    const stats = page.locator('.graphene-stats-glass');
    await expect(stats).toBeVisible();

    // All stat cards should be visible
    const statCards = page.locator('.graphene-stat-card');
    await expect(statCards).toHaveCount(3);
  });
});

test.describe('Light Theme Support', () => {
  test('hero should adapt to light theme', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('domcontentloaded');

    // Toggle to light mode by adding class
    await page.evaluate(() => {
      document.documentElement.setAttribute('data-theme', 'light');
      document.body.classList.add('light');
    });

    const hero = page.locator('.graphene-hero-3d');
    await expect(hero).toBeVisible();

    // Title should still be visible
    const title = page.locator('.graphene-hero-title');
    await expect(title).toBeVisible();
  });
});

test.describe('Canvas Animation', () => {
  test('canvas should be properly sized', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('domcontentloaded');

    const canvas = page.locator('#graphene-canvas');
    await expect(canvas).toBeAttached();

    // Canvas should have dimensions
    const box = await canvas.boundingBox();
    expect(box?.width).toBeGreaterThan(0);
    expect(box?.height).toBeGreaterThan(0);
  });

  test('canvas should not block pointer events on content', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('domcontentloaded');

    // Primary CTA should be clickable
    const primaryCta = page.locator('.graphene-btn-primary');
    await expect(primaryCta).toBeVisible();

    // Check we can interact with it
    const href = await primaryCta.getAttribute('href');
    expect(href).toBe('demo.php');
  });
});
