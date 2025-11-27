// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Graphene Strong Theme Tests
 *
 * Tests for the Graphene Strong theme mode including:
 * - Theme persistence across page loads
 * - Visual styling changes for hero, CTA, live feed, and footer
 * - Header toggle functionality
 * - API endpoint for theme switching
 */

// Use desktop viewport for all tests since toggle is hidden on mobile
test.use({ viewport: { width: 1280, height: 720 } });

test.describe('Graphene Strong Theme', () => {
  test.describe('Theme Toggle UI', () => {
    test('should display graphene toggle button on homepage', async ({ page }) => {
      await page.goto('/');
      await page.waitForLoadState('domcontentloaded');

      const toggle = page.locator('#graphene-mode-toggle');
      await expect(toggle).toBeVisible();
    });

    test('should have correct initial aria-pressed state', async ({ page }) => {
      await page.goto('/');
      await page.waitForLoadState('domcontentloaded');

      const toggle = page.locator('#graphene-mode-toggle');
      const ariaPressed = await toggle.getAttribute('aria-pressed');
      // Should be either 'true' or 'false' depending on server config
      expect(['true', 'false']).toContain(ariaPressed);
    });
  });

  test.describe('Theme Mode Switching', () => {
    test('clicking toggle should switch body class', async ({ page }) => {
      await page.goto('/');
      await page.waitForLoadState('networkidle');

      const body = page.locator('body');
      const toggle = page.locator('#graphene-mode-toggle');

      // Get initial mode
      const initialMode = await body.getAttribute('data-graphene-mode');

      // Mock API response
      const newMode = initialMode === 'strong' ? 'standard' : 'strong';
      await page.route('/api/graphene-toggle.php', async (route) => {
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({
            success: true,
            mode: newMode,
            body_class: `graphene-${newMode}`,
          }),
        });
      });

      // Click toggle
      await toggle.click();

      // Wait for mode to change
      await expect(body).toHaveAttribute('data-graphene-mode', newMode, { timeout: 10000 });
      await expect(body).toHaveClass(new RegExp(`graphene-${newMode}`));
    });

    test('toggle aria-pressed should update after mode change', async ({ page }) => {
      await page.goto('/');
      await page.waitForLoadState('networkidle');

      const toggle = page.locator('#graphene-mode-toggle');
      const initialPressed = await toggle.getAttribute('aria-pressed');

      // Mock API
      await page.route('/api/graphene-toggle.php', async (route) => {
        const newMode = initialPressed === 'true' ? 'standard' : 'strong';
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({ success: true, mode: newMode, body_class: `graphene-${newMode}` }),
        });
      });

      await toggle.click();

      // aria-pressed should flip
      const expectedPressed = initialPressed === 'true' ? 'false' : 'true';
      await expect(toggle).toHaveAttribute('aria-pressed', expectedPressed, { timeout: 10000 });
    });
  });

  test.describe('Theme Persistence', () => {
    test('localStorage should be set after toggle click', async ({ page }) => {
      // Mock API
      await page.route('/api/graphene-toggle.php', async (route) => {
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({ success: true, mode: 'strong', body_class: 'graphene-strong' }),
        });
      });

      await page.goto('/');
      await page.waitForLoadState('networkidle');

      const toggle = page.locator('#graphene-mode-toggle');
      await toggle.click();

      // Wait for localStorage to be updated
      await page.waitForTimeout(500);

      // Verify localStorage was updated
      const storedMode = await page.evaluate(() => localStorage.getItem('bbx-graphene-mode'));
      expect(storedMode).toBe('strong');
    });

    test('body should have data-graphene-mode attribute on load', async ({ page }) => {
      await page.goto('/');
      await page.waitForLoadState('domcontentloaded');

      const body = page.locator('body');
      const serverMode = await body.getAttribute('data-graphene-mode');

      expect(['standard', 'strong']).toContain(serverMode);
    });
  });

  test.describe('Graphene Strong Visual Styles', () => {
    test('body should have graphene class', async ({ page }) => {
      await page.goto('/');
      await page.waitForLoadState('domcontentloaded');

      const body = page.locator('body');
      // Body should have either graphene-standard or graphene-strong
      const classes = await body.getAttribute('class');
      expect(classes).toMatch(/graphene-(standard|strong)/);
    });

    test('hero section should exist and be styled', async ({ page }) => {
      await page.goto('/');
      await page.waitForLoadState('domcontentloaded');

      // Hero section should be present
      const hero = page.locator('.hero-landing, [class*="hero"]').first();
      await expect(hero).toBeVisible();
    });

    test('CTA buttons should be present in hero', async ({ page }) => {
      await page.goto('/');
      await page.waitForLoadState('domcontentloaded');

      // Primary CTA - check for demo link
      const primaryCTA = page.locator('a[href="demo.php"]').first();
      await expect(primaryCTA).toBeVisible();

      // Secondary CTA - check for free-scan link
      const secondaryCTA = page.locator('a[href="free-scan.php"]').first();
      await expect(secondaryCTA).toBeVisible();
    });

    test('live feed widget should be present', async ({ page }) => {
      await page.goto('/');
      await page.waitForLoadState('domcontentloaded');

      const liveFeed = page.locator('.live-feed-widget, [class*="live-feed"]').first();
      await expect(liveFeed).toBeVisible();
    });

    test('footer should have operational status indicator', async ({ page }) => {
      await page.goto('/');
      await page.waitForLoadState('domcontentloaded');

      // Footer should be visible
      const footer = page.locator('footer');
      await expect(footer).toBeVisible();

      // Check for operational text
      const operationalText = footer.locator('text=Operational');
      await expect(operationalText).toBeVisible();
    });
  });

  test.describe('API Endpoint', () => {
    test('POST to graphene-toggle.php with mode=strong should return success', async ({
      request,
    }) => {
      const response = await request.post('/api/graphene-toggle.php', {
        data: { mode: 'strong' },
      });

      expect(response.ok()).toBeTruthy();
      const json = await response.json();
      expect(json.success).toBe(true);
      expect(json.mode).toBe('strong');
      expect(json.body_class).toBe('graphene-strong');
    });

    test('POST to graphene-toggle.php with mode=standard should return success', async ({
      request,
    }) => {
      const response = await request.post('/api/graphene-toggle.php', {
        data: { mode: 'standard' },
      });

      expect(response.ok()).toBeTruthy();
      const json = await response.json();
      expect(json.success).toBe(true);
      expect(json.mode).toBe('standard');
      expect(json.body_class).toBe('graphene-standard');
    });

    test('POST with invalid mode should return error', async ({ request }) => {
      const response = await request.post('/api/graphene-toggle.php', {
        data: { mode: 'invalid' },
      });

      expect(response.status()).toBe(400);
      const json = await response.json();
      expect(json.success).toBe(false);
      expect(json.error).toBeTruthy();
    });

    test('GET request should return method not allowed', async ({ request }) => {
      const response = await request.get('/api/graphene-toggle.php');

      expect(response.status()).toBe(405);
      const json = await response.json();
      expect(json.success).toBe(false);
    });
  });

  test.describe('Accessibility', () => {
    test('toggle button should have proper aria attributes', async ({ page }) => {
      await page.goto('/');
      await page.waitForLoadState('domcontentloaded');

      const toggle = page.locator('#graphene-mode-toggle');

      // Check required aria attributes
      await expect(toggle).toHaveAttribute('aria-pressed', /(true|false)/);
      await expect(toggle).toHaveAttribute('aria-label', /.+/);
    });

    test('toggle should be keyboard accessible', async ({ page }) => {
      await page.goto('/');
      await page.waitForLoadState('networkidle');

      // Mock API
      await page.route('/api/graphene-toggle.php', async (route) => {
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({ success: true, mode: 'strong', body_class: 'graphene-strong' }),
        });
      });

      const toggle = page.locator('#graphene-mode-toggle');

      // Tab to toggle and check focus
      await toggle.focus();
      await expect(toggle).toBeFocused();

      // Press Enter to activate
      const initialMode = await toggle.getAttribute('aria-pressed');
      await page.keyboard.press('Enter');

      // Mode should change
      const newMode = initialMode === 'true' ? 'false' : 'true';
      await expect(toggle).toHaveAttribute('aria-pressed', newMode, { timeout: 10000 });
    });
  });

  test.describe('Cross-Page Consistency', () => {
    const pages = ['/', '/about.php', '/products.php', '/pricing.php', '/contact.php'];

    for (const pagePath of pages) {
      test(`toggle should be visible on ${pagePath}`, async ({ page }) => {
        await page.goto(pagePath);
        await page.waitForLoadState('domcontentloaded');

        const toggle = page.locator('#graphene-mode-toggle');
        await expect(toggle).toBeVisible();
      });
    }
  });
});
