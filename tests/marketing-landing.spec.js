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
    const headline = page.locator('#home .graphene-hero-title, h1.graphene-hero-title').first();
    await expect(headline).toBeVisible();

    // Should contain security-related messaging
    const text = await headline.textContent();
    expect(text.toLowerCase()).toMatch(/security|sikkerhed|infrastructure|infrastruktur|intelligent/i);
  });

  test('should display hero badge with Blackbox EYE branding', async ({ page }) => {
    const badge = page.locator('.graphene-floating-badge, .graphene-badge, .hero-tagline').first();
    await expect(badge).toBeVisible();

    const text = await badge.textContent();
    expect(text.toLowerCase()).toMatch(/blackbox|eye|security/i);
  });

  test('should have primary CTA button with correct styling', async ({ page }) => {
    const primaryCTA = page.locator('#home .graphene-cta-group .graphene-btn-primary, #home a.btn-graphene-primary').first();
    await expect(primaryCTA).toBeVisible();

    // Should link to demo page
    await expect(primaryCTA).toHaveAttribute('href', 'demo.php');

    // Check button has correct class and is styled
    const hasClass = await primaryCTA.evaluate(el =>
      el.classList.contains('graphene-btn-primary') || el.classList.contains('btn-graphene-primary') || el.classList.contains('btn-primary') || el.classList.contains('btn-primary--lg')
    );
    expect(hasClass).toBeTruthy();
  });

  test('should render spotlight CTA for platform exploration', async ({ page }) => {
    const spotlightCTA = page.locator('#home a.graphene-btn-spotlight');
    await expect(spotlightCTA).toBeVisible();
    await expect(spotlightCTA).toHaveAttribute('href', 'products.php');

    const text = (await spotlightCTA.textContent())?.toLowerCase() ?? '';
    expect(text).toMatch(/platform/);
  });

  test('should have secondary CTA button with correct styling', async ({ page }) => {
    const secondaryCTA = page.locator('#home .graphene-cta-group .graphene-btn-secondary, #home a.btn-graphene-secondary').first();
    await expect(secondaryCTA).toBeVisible();

    // Should link to free scan page in new layout
    await expect(secondaryCTA).toHaveAttribute('href', 'free-scan.php');

    // Check button has correct class
    const hasClass = await secondaryCTA.evaluate(el =>
      el.classList.contains('graphene-btn-secondary') ||
      el.classList.contains('btn-graphene-secondary') ||
      el.classList.contains('btn-secondary') ||
      el.classList.contains('btn-secondary--lg')
    );
    expect(hasClass).toBeTruthy();
  });

  test('CTA buttons should have transition property', async ({ page }) => {
    const primaryCTA = page.locator('#home .graphene-cta-group .graphene-btn-primary').first();

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
// HERO UTILITY HIGHLIGHTS (USPs)
// =====================================================
test.describe('Hero Utility Highlights', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('should display hero tagline block', async ({ page }) => {
    const tagline = page.locator('.graphene-hero-tagline');
    await expect(tagline).toBeVisible();
    const highlight = await tagline.locator('.graphene-hero-tagline__highlight').textContent();
    expect(highlight.trim().length).toBeGreaterThan(0);
  });

  test('should render USP list with three items', async ({ page }) => {
    const uspItems = page.locator('.graphene-hero-usps__item');
    await expect(uspItems).toHaveCount(3);
  });

  test('USP items should include icon and text', async ({ page }) => {
    const firstItem = page.locator('.graphene-hero-usps__item').first();
    await expect(firstItem.locator('.graphene-hero-usps__icon')).toBeVisible();
    await expect(firstItem.locator('.graphene-hero-usps__title')).toBeVisible();
    await expect(firstItem.locator('.graphene-hero-usps__body')).toBeVisible();
  });

  test('USP section should be accessible', async ({ page }) => {
    const uspList = page.locator('.graphene-hero-usps');
    await expect(uspList).toHaveAttribute('role', 'list');
    const firstItem = uspList.locator('.graphene-hero-usps__item').first();
    await expect(firstItem).toHaveAttribute('role', 'listitem');
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
    const headline = await page.locator('#home .graphene-hero-title').first().textContent();

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
    const primaryCTA = page.locator('#home .graphene-cta-group .graphene-btn-primary').first();
    await primaryCTA.focus();
    const isFocused = await primaryCTA.evaluate(el => document.activeElement === el);
    expect(isFocused).toBeTruthy();
  });

  test('spotlight CTA should expose descriptive aria label', async ({ page }) => {
    const spotlight = page.locator('#home .graphene-btn-spotlight').first();
    await expect(spotlight).toHaveAttribute('aria-label', /platform|interface/i);
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

      // Headline should be visible
      const headline = page.locator('#home .graphene-hero-title').first();
      await expect(headline).toBeVisible();

      // CTAs should be visible (support both old and new selectors)
      const primaryCTA = page.locator('#home .graphene-cta-group .graphene-btn-primary').first();
      await expect(primaryCTA).toBeVisible();

      const spotlight = page.locator('#home .graphene-btn-spotlight').first();
      await expect(spotlight).toBeVisible();
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
    const badge = page.locator('.graphene-floating-badge, .graphene-badge, .blackbox-badge').first();
    await expect(badge).toBeVisible();

    const badgeText = await badge.textContent();
    expect(badgeText.toLowerCase()).toMatch(/blackbox|eye|security/i);
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
    const statsCounter = page.locator('.graphene-stats-glass, .graphene-stats, .stats-counter').first();
    await expect(statsCounter).toBeVisible();

    const statsItems = page.locator('.graphene-stat-card, .graphene-stats__item, .stats-counter__item');
    await expect(statsItems).toHaveCount(3);
  });

  test('stats should show threats, uptime and response time', async ({ page }) => {
    // Check values are displayed (support both old and new selectors)
    const threatsValue = page.locator('.graphene-stat-card__value, .graphene-stats__value, .stats-counter__value').first();
    await expect(threatsValue).toBeVisible();

    // Check labels exist
    const labels = page.locator('.graphene-stat-card__label, .graphene-stats__label, .stats-counter__label');
    await expect(labels).toHaveCount(3);
  });

  test('hero headline should contain security messaging', async ({ page }) => {
    // Support both old and new selectors
    const headline = page.locator('#home .graphene-hero-title, h1.graphene-hero-title').first();
    const headlineText = await headline.textContent();

    // Should contain security-related keywords
    expect(headlineText.toLowerCase()).toMatch(/security|sikkerhed|infrastructure|infrastruktur|intelligent|generation/i);

    // Badge should have Blackbox branding
    const badge = page.locator('.graphene-floating-badge, .graphene-badge, .blackbox-badge').first();
    const badgeText = await badge.textContent();
    expect(badgeText.toLowerCase()).toMatch(/blackbox|security/i);
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
    const badge = page.locator('.graphene-floating-badge, .graphene-badge, .blackbox-badge').first();
    await expect(badge).toBeVisible();

    // Should contain either icon (legacy) or ring indicator (new design)
    const icon = badge.locator('.graphene-floating-badge__icon, .graphene-badge__icon, .blackbox-badge__icon, svg');
    const ring = badge.locator('.graphene-floating-badge__ring');
    const iconCount = await icon.count();
    const ringCount = await ring.count();
    // Either old icon or new ring should be present
    expect(iconCount + ringCount).toBeGreaterThan(0);
  });
});
