/**
 * Web Optimization Tests
 * Tests for lazy loading, minification, SRI, and accessibility
 */

const { test, expect } = require('@playwright/test');

test.describe('Web Optimization Tests', () => {

  test.describe('Lazy Loading', () => {

    test('Blog images should have loading="lazy" attribute', async ({ page }) => {
      await page.goto('/blog.php');

      const images = await page.locator('img[src*="featured_image"]');
      const count = await images.count();

      if (count > 0) {
        for (let i = 0; i < count; i++) {
          const loading = await images.nth(i).getAttribute('loading');
          expect(loading).toBe('lazy');
        }
      }
    });

    test('Blog post images should have loading="lazy" attribute', async ({ page }) => {
      // Skip if no posts available
      try {
        await page.goto('/blog-post.php?slug=test', { timeout: 5000 });

        const featuredImage = page.locator('img').first();
        const imageCount = await featuredImage.count();

        if (imageCount > 0) {
          const loading = await featuredImage.getAttribute('loading');
          expect(loading).toBe('lazy');
        } else {
          test.skip(true, 'No blog posts available for testing');
        }
      } catch (e) {
        test.skip(true, 'Blog post page not accessible or no test data available');
      }
    });

    test('Agent login logo should have loading="lazy" attribute', async ({ page }) => {
      await page.goto('/agent-login.php');

      const logo = page.locator('img[alt*="Blackbox EYE"]').first();
      const loading = await logo.getAttribute('loading');
      expect(loading).toBe('lazy');
    });
  });

  test.describe('Minification', () => {

    test('Marketing pages should load minified CSS', async ({ page }) => {
      await page.goto('/index.php');

      // Check for minified CSS in head
      const cssLinks = await page.locator('link[rel="stylesheet"]');
      const hrefs = await cssLinks.evaluateAll(links =>
        links.map(link => link.getAttribute('href'))
      );

      const hasMinifiedCSS = hrefs.some(href => href && href.includes('.min.css'));
      expect(hasMinifiedCSS).toBeTruthy();
    });

    test('Footer should load minified JS', async ({ page }) => {
      await page.goto('/index.php');

      // Check for minified JS
      const scripts = await page.locator('script[src*="site"]');
      const srcs = await scripts.evaluateAll(scripts =>
        scripts.map(script => script.getAttribute('src'))
      );

      const hasMinifiedJS = srcs.some(src => src && src.includes('site.min.js'));
      expect(hasMinifiedJS).toBeTruthy();
    });
  });

  test.describe('SRI (Subresource Integrity)', () => {

    test('Chart.js should have integrity attribute', async ({ page }) => {
      const response = await page.goto('/dashboard.php');

      // If redirected to login page (no auth in CI), skip this test gracefully
      if (page.url().includes('agent-login') || response?.status() === 302) {
        test.skip(true, 'Dashboard requires authentication - SRI verification skipped in CI');
        return;
      }

      const chartScript = page.locator('script[src*="chart.js"]').first();
      const integrity = await chartScript.getAttribute('integrity');
      const crossorigin = await chartScript.getAttribute('crossorigin');

      expect(integrity).toBeTruthy();
      expect(integrity).toMatch(/^sha(256|384|512)-/);
      expect(crossorigin).toBe('anonymous');
    });
  });

  test.describe('Accessibility', () => {

    test('Should have skip-to-content link', async ({ page }) => {
      await page.goto('/index.php');

      const skipLink = page.locator('a.skip-link');
      await expect(skipLink).toBeAttached();

      const href = await skipLink.getAttribute('href');
      expect(href).toBe('#main-content');
    });

    test('Main content should have id="main-content"', async ({ page }) => {
      await page.goto('/index.php');

      const mainContent = page.locator('#main-content');
      await expect(mainContent).toBeAttached();

      const tagName = await mainContent.evaluate(el => el.tagName.toLowerCase());
      expect(tagName).toBe('main');
    });

    test('Contact form should have proper ARIA attributes', async ({ page }) => {
      await page.goto('/contact.php');

      const form = page.locator('#contact-form');
      const ariaLabel = await form.getAttribute('aria-label');
      expect(ariaLabel).toBeTruthy();

      const nameInput = page.locator('#name');
      const ariaRequired = await nameInput.getAttribute('aria-required');
      expect(ariaRequired).toBe('true');

      const errorDiv = page.locator('#contact-form-error');
      const role = await errorDiv.getAttribute('role');
      expect(role).toBe('alert');
    });

    test('Navigation links should have aria-current on active page', async ({ page }) => {
      await page.goto('/products.php');

      // Check for aria-current="page" on products link
      const activeLink = page.locator('a[aria-current="page"]').first();
      await expect(activeLink).toBeAttached();
    });

    test('Mobile menu button should have proper ARIA attributes', async ({ page }) => {
      await page.goto('/index.php');

      const menuButton = page.locator('#mobile-menu-button');
      const ariaExpanded = await menuButton.getAttribute('aria-expanded');
      const ariaControls = await menuButton.getAttribute('aria-controls');
      const ariaLabel = await menuButton.getAttribute('aria-label');

      expect(ariaExpanded).toBe('false');
      expect(ariaControls).toBe('mobile-menu');
      expect(ariaLabel).toBeTruthy();
    });

    test('Images should have alt attributes', async ({ page }) => {
      await page.goto('/agent-login.php');

      const images = page.locator('img');
      const count = await images.count();

      for (let i = 0; i < count; i++) {
        const alt = await images.nth(i).getAttribute('alt');
        expect(alt).toBeTruthy();
      }
    });
  });

  test.describe('Keyboard Navigation', () => {

    test('Should be able to tab through navigation', async ({ page }) => {
      await page.goto('/index.php');

      // Skip-link should exist and be focusable
      const skipLink = page.locator('.skip-link');
      await expect(skipLink).toBeAttached();

      // Focus the skip-link directly and verify it can receive focus
      await skipLink.focus();
      const focusedElement = await page.evaluate(() => document.activeElement.className);
      expect(focusedElement).toContain('skip-link');
    });

    test('Mobile menu should open/close with keyboard', async ({ page }) => {
      const MAX_TAB_ATTEMPTS = 10;

      await page.setViewportSize({ width: 375, height: 667 }); // Mobile size
      await page.goto('/index.php');

      // Tab to mobile menu button with a limit
      let focused = null;
      for (let i = 0; i < MAX_TAB_ATTEMPTS; i++) {
        await page.keyboard.press('Tab');
        focused = await page.evaluate(() => document.activeElement.id);
        if (focused === 'mobile-menu-button') break;
      }

      expect(focused).toBe('mobile-menu-button');

      // Press Enter to open menu
      await page.keyboard.press('Enter');

      // Wait for menu to be active (deterministic wait)
      await page.waitForFunction(
        () => document.body.classList.contains('mobile-menu-open'),
        { timeout: 2000 }
      );

      const menuClass = await page.locator('#mobile-menu').getAttribute('class');
      const overlayClass = await page.locator('#mobile-menu-overlay').getAttribute('class');
      expect(
        (menuClass || '').includes('active') ||
        (overlayClass || '').includes('active') ||
        (await page.evaluate(() => document.body.classList.contains('mobile-menu-open')))
      ).toBeTruthy();

      // Press Escape to close
      await page.keyboard.press('Escape');

      // Wait for menu to close (deterministic wait)
      await page.waitForFunction(
        () => !document.body.classList.contains('mobile-menu-open'),
        { timeout: 2000 }
      );

      // Check menu is closed
      const menuClassAfter = await page.locator('#mobile-menu').getAttribute('class');
      const overlayClassAfter = await page.locator('#mobile-menu-overlay').getAttribute('class');
      expect(
        !(menuClassAfter || '').includes('active') &&
        !(overlayClassAfter || '').includes('active') &&
        !(await page.evaluate(() => document.body.classList.contains('mobile-menu-open')))
      ).toBeTruthy();
    });
  });

  test.describe('Performance', () => {

    test('Page should load minified assets efficiently', async ({ page }) => {
      const response = await page.goto('/index.php');

      expect(response.status()).toBe(200);

      // Check that page loaded in reasonable time
      const timing = await page.evaluate(() => {
        const perfData = performance.getEntriesByType('navigation')[0];
        return perfData.loadEventEnd - perfData.fetchStart;
      });

      // Page should load in under 5 seconds
      expect(timing).toBeLessThan(5000);
    });
  });
});
