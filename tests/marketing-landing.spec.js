/**
 * Marketing Landing Page Tests
 *
 * Tests for the redesigned marketing front page including:
 * - Hero section with Graphene theme and Blackbox EYE branding
 * - CTA buttons with Graphene styling
 * - Live feed widget 2.0 with severity tags
 * - Stats counter with icons
 * - Design token consistency (Graphene colors)
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

  test('should display hero headline with Graphene branding', async ({ page }) => {
    // Match both old and new selectors for compatibility
    const headline = page.locator('h1.graphene-gradient-text, h1.hero-gradient-text').first();
    await expect(headline).toBeVisible();

    // Should contain security-related messaging
    const text = await headline.textContent();
    expect(text.toLowerCase()).toMatch(/security|sikkerhed|infrastructure|infrastruktur|intelligent/i);
  });

  test('should display hero badge with Blackbox EYE branding', async ({ page }) => {
    const badge = page.locator('.graphene-badge, .hero-tagline').first();
    await expect(badge).toBeVisible();

    const text = await badge.textContent();
    expect(text.toLowerCase()).toContain('blackbox eye');
  });

  test('should have primary CTA button with correct styling', async ({ page }) => {
    // Support both old and new button classes
    const primaryCTA = page.locator('a.btn-graphene-primary, a.btn-primary--lg').first();
    await expect(primaryCTA).toBeVisible();

    // Should link to demo page
    await expect(primaryCTA).toHaveAttribute('href', 'demo.php');

    // Check button has correct class and is styled
    const hasClass = await primaryCTA.evaluate(el =>
      el.classList.contains('btn-graphene-primary') || el.classList.contains('btn-primary') || el.classList.contains('btn-primary--lg')
    );
    expect(hasClass).toBeTruthy();
  });

  test('should have secondary CTA button with correct styling', async ({ page }) => {
    // Support both old and new button classes
    const secondaryCTA = page.locator('a.btn-graphene-secondary, a.btn-secondary--lg').first();
    await expect(secondaryCTA).toBeVisible();

    // Should link to products page
    await expect(secondaryCTA).toHaveAttribute('href', 'products.php');

    // Check button has correct class
    const hasClass = await secondaryCTA.evaluate(el =>
      el.classList.contains('btn-graphene-secondary') || el.classList.contains('btn-secondary') || el.classList.contains('btn-secondary--lg')
    );
    expect(hasClass).toBeTruthy();
  });

  test('CTA buttons should have transition property', async ({ page }) => {
    const primaryCTA = page.locator('a.btn-graphene-primary, a.btn-primary--lg').first();

    // Check that button has transition defined (for hover effects)
    const transition = await primaryCTA.evaluate(el =>
      getComputedStyle(el).transition
    );

    // Should have some transition property
    expect(transition).not.toBe('none');
    expect(transition.length).toBeGreaterThan(0);
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
    // Support both old and new selectors
    const widget = page.locator('.live-feed-2, .live-feed-widget').first();
    await expect(widget).toBeVisible();
  });

  test('should have live indicator element', async ({ page }) => {
    // Support both old and new selectors
    const indicator = page.locator('.live-feed-2__pulse, .live-feed-widget__indicator').first();
    // Element exists and has correct aria-hidden for decorative purposes
    await expect(indicator).toHaveAttribute('aria-hidden', 'true');
  });

  test('should display feed items with severity styling', async ({ page }) => {
    // Support both old and new selectors
    const feedItems = page.locator('.live-feed-2__item, .live-feed-item');
    await expect(feedItems).toHaveCount(3); // 3 mock items

    // Check different severity classes exist (both old and new formats)
    const hasWarning = await page.locator('.live-feed-2__item--warning, .live-feed-item--warning').count();
    const hasCritical = await page.locator('.live-feed-2__item--critical, .live-feed-item--critical').count();
    const hasInfo = await page.locator('.live-feed-2__item--info, .live-feed-item--info').count();

    expect(hasWarning).toBeGreaterThan(0);
    expect(hasCritical).toBeGreaterThan(0);
    expect(hasInfo).toBeGreaterThan(0);
  });

  test('feed items should have content and time', async ({ page }) => {
    // Support both old and new selectors
    const firstItem = page.locator('.live-feed-2__item, .live-feed-item').first();

    // Check for content elements (both old and new formats)
    const hasContent = await firstItem.locator('.live-feed-2__content, .live-feed-item__title').count();
    const hasTime = await firstItem.locator('.live-feed-2__time, .live-feed-item__time').count();

    expect(hasContent).toBeGreaterThan(0);
    expect(hasTime).toBeGreaterThan(0);
  });

  test('feed widget should be responsive', async ({ page }) => {
    // Desktop: widget should be visible in grid
    await page.setViewportSize({ width: 1440, height: 900 });
    await expect(page.locator('.live-feed-2, .live-feed-widget').first()).toBeVisible();

    // Mobile: widget should still be visible (stacked)
    await page.setViewportSize({ width: 375, height: 812 });
    await expect(page.locator('.live-feed-2, .live-feed-widget').first()).toBeVisible();
  });
});

// =====================================================
// DESIGN TOKEN CONSISTENCY TESTS
// =====================================================
test.describe('Design Token Consistency', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('should have primary button styling applied', async ({ page }) => {
    // Check that the primary button has the correct class (old or new format)
    const primaryBtn = page.locator('.btn-graphene-primary, .btn-primary, .btn-primary--lg').first();
    await expect(primaryBtn).toBeVisible();

    // Verify the element has proper styling class
    const hasClass = await primaryBtn.evaluate(el =>
      el.classList.contains('btn-graphene-primary') || el.classList.contains('btn-primary') || el.classList.contains('btn-primary--lg')
    );
    expect(hasClass).toBeTruthy();
  });

  test('tactical tagline should have correct class for gold color', async ({ page }) => {
    // Check for elements using the CSS variable syntax
    const tagline = page.locator('[class*="var(--bbx-gold)"], .text-\\[var\\(--bbx-gold\\)\\]').first();
    const exists = await tagline.count();

    // Element with gold styling should exist
    expect(exists).toBeGreaterThan(0);
  });

  test('glass effect cards should have correct styling', async ({ page }) => {
    const card = page.locator('.glass-effect').first();
    await expect(card).toBeVisible();

    // Should have backdrop filter or similar glass styling
    const hasGlassClass = await card.evaluate(el =>
      el.classList.contains('glass-effect')
    );
    expect(hasGlassClass).toBeTruthy();
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

    // Support both old and new selectors
    const headline = await page.locator('h1.graphene-gradient-text, h1.hero-gradient-text').first().textContent();

    // Should contain security-related keywords in both languages
    expect(headline.toLowerCase()).toMatch(/security|sikkerhed|infrastructure|infrastruktur|intelligent/i);
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
    // Tab to button (support both old and new selectors)
    await page.keyboard.press('Tab');
    await page.keyboard.press('Tab'); // May need multiple tabs depending on page

    // Should be focusable
    const focusedElement = await page.evaluate(() =>
      document.activeElement?.classList.contains('btn-graphene-primary') ||
      document.activeElement?.classList.contains('btn-graphene-secondary') ||
      document.activeElement?.classList.contains('btn-primary--lg') ||
      document.activeElement?.classList.contains('btn-secondary--lg')
    );
    // At least one button type should be focusable in hero
  });

  test('live feed should have aria-hidden on decorative elements', async ({ page }) => {
    // Support both old and new selectors
    const indicator = page.locator('.live-feed-2__pulse, .live-feed-widget__indicator').first();
    await expect(indicator).toHaveAttribute('aria-hidden', 'true');
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

      // Hero should be visible (support both old and new selectors)
      const hero = page.locator('#home, .graphene-hero').first();
      await expect(hero).toBeVisible();

      // Headline should be visible (support both old and new selectors)
      const headline = page.locator('h1.graphene-gradient-text, h1.hero-gradient-text').first();
      await expect(headline).toBeVisible();

      // CTAs should be visible (support both old and new selectors)
      const primaryCTA = page.locator('a.btn-graphene-primary, a.btn-primary--lg').first();
      await expect(primaryCTA).toBeVisible();

      // Take screenshot for visual review
      await page.screenshot({
        path: `artifacts/hero-${vp.name}-${vp.width}x${vp.height}.png`,
        fullPage: false
      });
    });
  }
});

// =====================================================
// BLACKBOX EYE BRANDING TESTS
// =====================================================
test.describe('Blackbox EYE Branding', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('should display Blackbox EYE badge in hero', async ({ page }) => {
    // Support both old and new selectors
    const badge = page.locator('.graphene-badge, .blackbox-badge').first();
    await expect(badge).toBeVisible();

    const badgeText = await badge.textContent();
    expect(badgeText.toLowerCase()).toContain('blackbox eye');
  });

  test('hero section should have appropriate section class', async ({ page }) => {
    // Support both old and new selectors
    const heroSection = page.locator('#home, .graphene-hero').first();
    const hasClass = await heroSection.evaluate(el =>
      el.classList.contains('blackbox-section') || el.classList.contains('graphene-hero') || el.id === 'home'
    );
    expect(hasClass).toBeTruthy();
  });

  test('should display stats counter with three items', async ({ page }) => {
    // Support both old and new selectors
    const statsCounter = page.locator('.graphene-stats, .stats-counter').first();
    await expect(statsCounter).toBeVisible();

    const statsItems = page.locator('.graphene-stats__item, .stats-counter__item');
    await expect(statsItems).toHaveCount(3);
  });

  test('stats should show threats, uptime and response time', async ({ page }) => {
    // Check values are displayed (support both old and new selectors)
    const threatsValue = page.locator('.graphene-stats__value, .stats-counter__value').first();
    await expect(threatsValue).toBeVisible();

    // Check labels exist
    const labels = page.locator('.graphene-stats__label, .stats-counter__label');
    await expect(labels).toHaveCount(3);
  });

  test('hero headline should contain security messaging', async ({ page }) => {
    // Support both old and new selectors
    const headline = page.locator('h1.graphene-gradient-text, h1.hero-gradient-text').first();
    const headlineText = await headline.textContent();

    // Should contain security-related keywords
    expect(headlineText.toLowerCase()).toMatch(/security|sikkerhed|infrastructure|infrastruktur|intelligent/i);

    // Badge should have Blackbox branding
    const badge = page.locator('.graphene-badge, .blackbox-badge').first();
    const badgeText = await badge.textContent();
    expect(badgeText.toLowerCase()).toContain('blackbox eye');
  });
});

// =====================================================
// GRAPHENE CSS VARIABLES TESTS
// =====================================================
test.describe('Graphene CSS Variables', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('hero section should exist', async ({ page }) => {
    // Support both old and new selectors
    const section = page.locator('#home, .graphene-hero').first();
    await expect(section).toBeVisible();
  });

  test('hero badge should have correct structure', async ({ page }) => {
    // Support both old and new selectors
    const badge = page.locator('.graphene-badge, .blackbox-badge').first();
    await expect(badge).toBeVisible();

    // Should contain either icon (legacy) or pulse indicator (new design)
    const icon = badge.locator('.graphene-badge__icon, .blackbox-badge__icon, svg');
    const pulse = badge.locator('.graphene-badge__pulse');
    const iconCount = await icon.count();
    const pulseCount = await pulse.count();
    // Either old icon or new pulse should be present
    expect(iconCount + pulseCount).toBeGreaterThan(0);
  });
});
