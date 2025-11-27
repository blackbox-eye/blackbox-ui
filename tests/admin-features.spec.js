/**
 * Admin Features Test Suite
 *
 * Tests for the admin portal features including:
 * - Login flow security (Command Deck should be hidden)
 * - Request Access functionality
 * - Intel Vault operations
 * - API Keys management
 *
 * @requires A running local server (e.g., php -S localhost:8000)
 */

const { test, expect } = require('@playwright/test');

// =====================================================
// LOGIN SECURITY TESTS
// =====================================================
test.describe('Login Page Security', () => {
  test('Command Deck should NOT be visible on login page', async ({ page }) => {
    await page.goto('/agent-login.php');

    // Verify Command Deck launcher button is NOT present
    const launcher = page.locator('#commandDeckLauncher');
    await expect(launcher).not.toBeVisible();

    // Verify Command Deck menu is NOT present
    const menu = page.locator('#commandDeckMenu');
    await expect(menu).not.toBeAttached();

    // Verify overlay is NOT present
    const overlay = page.locator('#commandDeckOverlay');
    await expect(overlay).not.toBeAttached();
  });

  test('Login card should be centered and visible', async ({ page }) => {
    await page.goto('/agent-login.php');

    const loginCard = page.locator('.login-card');
    await expect(loginCard).toBeVisible();

    // Check that login form fields exist
    await expect(page.locator('#agent_id')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('input[name="pin"]')).toBeVisible();
  });

  test('Back link should navigate to homepage', async ({ page }) => {
    await page.goto('/agent-login.php');

    const backLink = page.locator('.back-link');
    await expect(backLink).toBeVisible();
    await expect(backLink).toHaveAttribute('href', 'index.php');
  });

  test('Request Access modal should be functional', async ({ page }) => {
    await page.goto('/agent-login.php');

    // Find and click request access button
    const requestBtn = page.locator('#requestAccessInit');
    if (await requestBtn.isVisible()) {
      await requestBtn.click();

      // Verify overlay appears
      const overlay = page.locator('#requestAccessOverlay');
      await expect(overlay).toHaveClass(/is-visible/);

      // Close modal
      const closeBtn = page.locator('#requestAccessClose');
      await closeBtn.click();
      await expect(overlay).not.toHaveClass(/is-visible/);
    }
  });
});

// =====================================================
// FAVICON CONSISTENCY TESTS
// =====================================================
test.describe('Favicon Consistency', () => {
  const pages = [
    { name: 'Homepage', url: '/' },
    { name: 'Login', url: '/agent-login.php' },
    { name: 'About', url: '/about.php' },
    { name: 'Products', url: '/products.php' },
    { name: 'Pricing', url: '/pricing.php' }
  ];

  for (const p of pages) {
    test(`${p.name} page should have favicon`, async ({ page }) => {
      await page.goto(p.url);

      // Check for favicon link tag
      const favicon = page.locator('link[rel="icon"][type="image/png"]');
      await expect(favicon.first()).toBeAttached();

      const href = await favicon.first().getAttribute('href');
      expect(href).toContain('BlackboxEYE');
    });
  }
});

// =====================================================
// AUTHENTICATED ADMIN TESTS (require valid session)
// These tests document expected behavior but may skip
// if no valid authentication is available
// =====================================================
test.describe('Authenticated Admin Features', () => {
  // Helper to check if we're authenticated
  const isAuthenticated = async (page) => {
    await page.goto('/dashboard.php');
    return !page.url().includes('agent-login.php');
  };

  test.describe('Command Deck Navigation', () => {
    test('Command Deck should be visible after login', async ({ page }) => {
      // Note: This test requires a valid session
      // In CI, this may be skipped or use test credentials

      await page.goto('/dashboard.php');

      // If redirected to login, skip this test
      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Verify Command Deck launcher is visible
      const launcher = page.locator('#commandDeckLauncher');
      await expect(launcher).toBeVisible();

      // Click to open menu
      await launcher.click();

      // Verify menu slides in
      const menu = page.locator('#commandDeckMenu');
      await expect(menu).toHaveClass(/is-open/);

      // Verify nav items are visible
      await expect(page.locator('.command-deck__item').first()).toBeVisible();

      // Close with ESC
      await page.keyboard.press('Escape');
      await expect(menu).not.toHaveClass(/is-open/);
    });
  });

  test.describe('Request Access Admin Page', () => {
    test('Request Access page should load for admins', async ({ page }) => {
      await page.goto('/access-requests.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Check page title
      await expect(page).toHaveTitle(/Access Requests|Adgangsanmodninger/);

      // Check table exists
      const table = page.locator('table, .requests-list');
      await expect(table).toBeVisible();
    });
  });

  test.describe('Intel Vault Page', () => {
    test('Intel Vault page should load for admins', async ({ page }) => {
      await page.goto('/intel-vault.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Check page title
      await expect(page).toHaveTitle(/Intel Vault/);

      // Check upload form exists
      const uploadForm = page.locator('form[enctype="multipart/form-data"]');
      await expect(uploadForm).toBeVisible();

      // Check passphrase field
      await expect(page.locator('input[name="passphrase"]')).toBeVisible();
    });
  });

  test.describe('API Keys Page', () => {
    test('API Keys page should load for admins', async ({ page }) => {
      await page.goto('/api-keys.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Check page title
      await expect(page).toHaveTitle(/API Keys|API-nøgler/);

      // Check create key form exists
      const createForm = page.locator('#create-key-form, form');
      await expect(createForm).toBeVisible();
    });
  });
});

// =====================================================
// ACCESSIBILITY TESTS
// =====================================================
test.describe('Accessibility', () => {
  test('Login page should have skip link', async ({ page }) => {
    await page.goto('/agent-login.php');

    const skipLink = page.locator('.skip-link');
    await expect(skipLink).toBeAttached();
    await expect(skipLink).toHaveAttribute('href', '#main-content');
  });

  test('Images should have alt attributes', async ({ page }) => {
    await page.goto('/agent-login.php');

    const images = page.locator('img');
    const count = await images.count();

    for (let i = 0; i < count; i++) {
      const img = images.nth(i);
      const alt = await img.getAttribute('alt');
      // Alt should exist (can be empty for decorative images)
      expect(alt).not.toBeNull();
    }
  });

  test('Form inputs should have labels', async ({ page }) => {
    await page.goto('/agent-login.php');

    // Check agent_id has label (visible or sr-only)
    const agentIdLabel = page.locator('label[for="agent_id"]');
    await expect(agentIdLabel).toBeAttached();
  });
});

// =====================================================
// RESPONSIVE LAYOUT TESTS
// =====================================================
test.describe('Responsive Login Layout', () => {
  const viewports = [
    { name: 'mobile', width: 375, height: 812 },
    { name: 'tablet', width: 768, height: 1024 },
    { name: 'desktop', width: 1440, height: 900 }
  ];

  for (const vp of viewports) {
    test(`Login card renders correctly on ${vp.name}`, async ({ page }) => {
      await page.setViewportSize({ width: vp.width, height: vp.height });
      await page.goto('/agent-login.php');

      const loginCard = page.locator('.login-card');
      await expect(loginCard).toBeVisible();

      // Take screenshot for visual comparison
      await page.screenshot({
        path: `artifacts/login-${vp.name}-${vp.width}x${vp.height}.png`,
        fullPage: false
      });
    });
  }
});
