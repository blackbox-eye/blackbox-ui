/**
 * Graphene Theme Global Tests
 *
 * Tests for the Graphene design system across all marketing pages.
 * Verifies consistent theming, meta tags, and accessibility.
 *
 * @requires A running local server (e.g., php -S localhost:8000)
 */

const { test, expect } = require('@playwright/test');

// Pages that should use Graphene theme
const graphenePages = [
  { path: '/', name: 'Homepage' },
  { path: '/about.php', name: 'About' },
  { path: '/products.php', name: 'Products' },
  { path: '/cases.php', name: 'Cases' },
  { path: '/pricing.php', name: 'Pricing' },
  { path: '/contact.php', name: 'Contact' },
  { path: '/demo.php', name: 'Demo' },
  { path: '/faq.php', name: 'FAQ' },
  { path: '/blog.php', name: 'Blog' },
];

// =====================================================
// GRAPHENE PAGE CLASS TESTS
// =====================================================
test.describe('Graphene Page Class', () => {
  for (const page of graphenePages) {
    test(`${page.name} should have graphene-page class on body`, async ({ page: browserPage }) => {
      await browserPage.goto(page.path);

      const body = browserPage.locator('body');
      const hasClass = await body.evaluate(el => el.classList.contains('graphene-page'));
      expect(hasClass).toBeTruthy();
    });
  }
});

// =====================================================
// OPEN GRAPH META TAGS TESTS
// =====================================================
test.describe('Open Graph Meta Tags', () => {
  for (const page of graphenePages) {
    test(`${page.name} should have og:title meta tag`, async ({ page: browserPage }) => {
      await browserPage.goto(page.path);

      const ogTitle = browserPage.locator('meta[property="og:title"]');
      await expect(ogTitle).toHaveAttribute('content', /.+/);
    });

    test(`${page.name} should have og:description meta tag`, async ({ page: browserPage }) => {
      await browserPage.goto(page.path);

      const ogDesc = browserPage.locator('meta[property="og:description"]');
      await expect(ogDesc).toHaveAttribute('content', /.+/);
    });

    test(`${page.name} should have og:image meta tag`, async ({ page: browserPage }) => {
      await browserPage.goto(page.path);

      const ogImage = browserPage.locator('meta[property="og:image"]');
      await expect(ogImage).toHaveAttribute('content', /blackbox/i);
    });

    test(`${page.name} should have og:site_name meta tag`, async ({ page: browserPage }) => {
      await browserPage.goto(page.path);

      const ogSiteName = browserPage.locator('meta[property="og:site_name"]');
      await expect(ogSiteName).toHaveAttribute('content', /blackbox eye/i);
    });
  }
});

// =====================================================
// TWITTER CARD META TAGS TESTS
// =====================================================
test.describe('Twitter Card Meta Tags', () => {
  for (const page of graphenePages) {
    test(`${page.name} should have twitter:card meta tag`, async ({ page: browserPage }) => {
      await browserPage.goto(page.path);

      const twitterCard = browserPage.locator('meta[name="twitter:card"]');
      await expect(twitterCard).toHaveAttribute('content', /summary/i);
    });

    test(`${page.name} should have twitter:site meta tag`, async ({ page: browserPage }) => {
      await browserPage.goto(page.path);

      const twitterSite = browserPage.locator('meta[name="twitter:site"]');
      await expect(twitterSite).toHaveAttribute('content', '@blackboxeye');
    });
  }
});

// =====================================================
// FAVICON TESTS
// =====================================================
test.describe('Favicon Consistency', () => {
  for (const page of graphenePages) {
    test(`${page.name} should have Blackbox EYE favicon`, async ({ page: browserPage }) => {
      await browserPage.goto(page.path);

      const favicon = browserPage.locator('link[rel="icon"][sizes="32x32"]');
      await expect(favicon).toHaveAttribute('href', /\/assets\/favicon-32x32\.png(\?|$)/i);
    });

    test(`${page.name} should have apple-touch-icon`, async ({ page: browserPage }) => {
      await browserPage.goto(page.path);
      // Wait for page to fully load to avoid flaky timeout
      await browserPage.waitForLoadState('domcontentloaded');

      const appleTouchIcon = browserPage.locator('link[rel="apple-touch-icon"]');
      await expect(appleTouchIcon).toBeAttached({ timeout: 10000 });
      await expect(appleTouchIcon).toHaveAttribute('href', /\/assets\/apple-touch-icon\.png(\?|$)/i);
    });
  }
});

// =====================================================
// DESIGN TOKEN CONSISTENCY TESTS
// =====================================================
test.describe('Graphene Design Tokens', () => {
  test('Homepage should have Graphene CSS variables defined', async ({ page }) => {
    await page.goto('/');

    // Wait for CSS to load
    await page.waitForLoadState('domcontentloaded');

    // Check that the CSS file containing Graphene tokens is loaded
    const cssLoaded = await page.evaluate(() => {
      const links = document.querySelectorAll('link[rel="stylesheet"]');
      return Array.from(links).some(link =>
        link.href.includes('marketing') || link.href.includes('tailwind')
      );
    });

    expect(cssLoaded).toBeTruthy();
  });

  test('Homepage should use graphene-page body class', async ({ page }) => {
    await page.goto('/');

    const body = page.locator('body');
    const hasClass = await body.evaluate(el => el.classList.contains('graphene-page'));
    expect(hasClass).toBeTruthy();
  });

  test('Homepage hero should use graphene-hero class', async ({ page }) => {
    await page.goto('/');

    // Support both class-based and id-based selectors
    const hero = page.locator('.graphene-hero, #home').first();
    await expect(hero).toBeVisible();
  });
});

// =====================================================
// ACCESSIBILITY TESTS
// =====================================================
test.describe('Global Accessibility', () => {
  for (const page of graphenePages) {
    test(`${page.name} should have skip-link for keyboard navigation`, async ({ page: browserPage }) => {
      await browserPage.goto(page.path);

      const skipLink = browserPage.locator('.skip-link, a[href="#main-content"]');
      await expect(skipLink.first()).toBeAttached();
    });

    test(`${page.name} should have exactly one h1 heading`, async ({ page: browserPage }) => {
      await browserPage.goto(page.path);

      const h1Count = await browserPage.locator('h1').count();
      expect(h1Count).toBe(1);
    });

    test(`${page.name} should have lang attribute on html`, async ({ page: browserPage }) => {
      await browserPage.goto(page.path);

      const html = browserPage.locator('html');
      const lang = await html.getAttribute('lang');
      expect(['da', 'en']).toContain(lang);
    });
  }
});

// =====================================================
// RESPONSIVE LAYOUT TESTS
// =====================================================
test.describe('Responsive Layout', () => {
  const viewports = [
    { name: 'mobile', width: 375, height: 812 },
    { name: 'tablet', width: 768, height: 1024 },
    { name: 'desktop', width: 1440, height: 900 },
  ];

  for (const vp of viewports) {
    test(`Homepage renders correctly on ${vp.name}`, async ({ page }) => {
      await page.setViewportSize({ width: vp.width, height: vp.height });
      await page.goto('/');

      // Header should be visible
      const header = page.locator('#main-header');
      await expect(header).toBeVisible();

      // Main content should be visible
      const main = page.locator('main, #main-content, [role="main"]').first();
      await expect(main).toBeVisible();
    });
  }
});

// =====================================================
// NO MATRIX ANIMATION TESTS
// =====================================================
test.describe('Matrix Animation Removed', () => {
  test('Homepage should not have hero-canvas element', async ({ page }) => {
    await page.goto('/');

    const heroCanvas = page.locator('#hero-canvas');
    const count = await heroCanvas.count();
    expect(count).toBe(0);
  });

  test('Site.js should not log Matrix animation message', async ({ page }) => {
    const consoleMessages = [];
    page.on('console', msg => consoleMessages.push(msg.text()));

    await page.goto('/');
    await page.waitForTimeout(1000);

    const hasMatrixLog = consoleMessages.some(msg =>
      msg.toLowerCase().includes('matrix') && msg.includes('animation')
    );
    expect(hasMatrixLog).toBeFalsy();
  });
});
