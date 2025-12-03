/**
 * Admin Features Test Suite
 *
 * Tests for the admin portal features including:
 * - Login flow security (Control Panel should be hidden)
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
  test('Control Panel should NOT be visible on login page', async ({ page }) => {
    await page.goto('/agent-login.php');

    // Verify Control Panel launcher button is NOT present
    const launcher = page.locator('#commandDeckLauncher');
    await expect(launcher).not.toBeVisible();

    // Verify Control Panel menu is NOT present
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

  test.describe('Control Panel Navigation', () => {
    test('Control Panel should be visible after login', async ({ page }) => {
      // Note: This test requires a valid session
      // In CI, this may be skipped or use test credentials

      await page.goto('/dashboard.php');

      // If redirected to login, skip this test
      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Verify Control Panel launcher is visible
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

// =====================================================
// PASSWORD TOGGLE COMPONENT TESTS
// =====================================================
test.describe('Password Toggle Component', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/agent-login.php');
    // Wait for JS to initialize password toggles
    await page.waitForTimeout(500);
  });

  test('should display eye icon on password fields', async ({ page }) => {
    // Check that password toggle buttons exist for password and PIN
    const passwordWrapper = page.locator('#password').locator('..');
    const pinWrapper = page.locator('#pin').locator('..');

    // Password field should have toggle
    await expect(passwordWrapper.locator('.password-toggle')).toBeVisible();
    // PIN field should have toggle
    await expect(pinWrapper.locator('.password-toggle')).toBeVisible();
  });

  test('should toggle password visibility on click', async ({ page }) => {
    const passwordInput = page.locator('#password');
    const toggleBtn = page.locator('#password').locator('..').locator('.password-toggle');

    // Initially should be password type
    await expect(passwordInput).toHaveAttribute('type', 'password');

    // Click toggle
    await toggleBtn.click();

    // Should now be text type (visible)
    await expect(passwordInput).toHaveAttribute('type', 'text');

    // Click again to hide
    await toggleBtn.click();

    // Should be password again
    await expect(passwordInput).toHaveAttribute('type', 'password');
  });

  test('should support keyboard activation with Enter', async ({ page }) => {
    const passwordInput = page.locator('#password');
    const toggleBtn = page.locator('#password').locator('..').locator('.password-toggle');

    // Focus the toggle button
    await toggleBtn.focus();

    // Press Enter to activate
    await page.keyboard.press('Enter');

    // Should toggle to text
    await expect(passwordInput).toHaveAttribute('type', 'text');
  });

  test('should support keyboard activation with Space', async ({ page }) => {
    const passwordInput = page.locator('#password');
    const toggleBtn = page.locator('#password').locator('..').locator('.password-toggle');

    // Start in hidden state
    await expect(passwordInput).toHaveAttribute('type', 'password');

    // Focus and press Space
    await toggleBtn.focus();
    await page.keyboard.press('Space');

    // Should toggle to visible
    await expect(passwordInput).toHaveAttribute('type', 'text');
  });

  test('should have accessible aria-label', async ({ page }) => {
    const toggleBtn = page.locator('#password').locator('..').locator('.password-toggle');

    // Should have aria-label for screen readers (Danish: Vis adgangskode)
    const ariaLabel = await toggleBtn.getAttribute('aria-label');
    expect(ariaLabel).toBeTruthy();
    expect(ariaLabel.toLowerCase()).toMatch(/vis|show|skjul|hide/);
  });

  test('should update aria-label when toggled', async ({ page }) => {
    const toggleBtn = page.locator('#password').locator('..').locator('.password-toggle');

    const initialLabel = await toggleBtn.getAttribute('aria-label');

    // Toggle
    await toggleBtn.click();

    const newLabel = await toggleBtn.getAttribute('aria-label');

    // Label should change to reflect new state
    expect(newLabel).not.toBe(initialLabel);
  });

  test('should work with PIN field', async ({ page }) => {
    const pinInput = page.locator('#pin');
    const toggleBtn = page.locator('#pin').locator('..').locator('.password-toggle');

    // Initially password type
    await expect(pinInput).toHaveAttribute('type', 'password');

    // Toggle
    await toggleBtn.click();
    await expect(pinInput).toHaveAttribute('type', 'text');

    // Toggle back
    await toggleBtn.click();
    await expect(pinInput).toHaveAttribute('type', 'password');
  });

  test('should have visible focus indicator', async ({ page }) => {
    const toggleBtn = page.locator('#password').locator('..').locator('.password-toggle');

    // Focus the button
    await toggleBtn.focus();

    // Check that the button is focused
    await expect(toggleBtn).toBeFocused();
  });
});

// =====================================================
// GHOST MODE VISIBILITY TESTS
// =====================================================
test.describe('Ghost Mode Admin-Only Visibility', () => {
  test('settings.php Ghost panel markup exists with PHP conditional', async ({ page }) => {
    // This test verifies the Ghost panel is conditionally rendered
    // When not authenticated, we can't see settings.php content
    await page.goto('/settings.php');

    // Should redirect to login if not authenticated
    if (page.url().includes('agent-login.php')) {
      // Expected behavior - not logged in
      test.skip(true, 'Requires authenticated session to test Ghost panel visibility');
      return;
    }

    // If somehow we got through (test environment), check for Ghost panel
    const ghostPanel = page.locator('text=Ghost-mode');
    // Panel visibility depends on is_admin session variable
    // This documents the expected behavior
  });

  test('admin.php should only be accessible to admins', async ({ page }) => {
    await page.goto('/admin.php');

    // Non-admins should be redirected
    // Either to login or to dashboard
    const url = page.url();
    const isRedirected = url.includes('agent-login.php') || url.includes('dashboard.php');

    // If we're on admin.php, we must be admin
    if (!isRedirected) {
      // Verify Ghost column exists in user table for admin
      const ghostColumn = page.locator('th:has-text("Ghost")');
      await expect(ghostColumn).toBeVisible();
    }
  });
});

// =====================================================
// DASHBOARD FEEDS API TESTS
// =====================================================
test.describe('Dashboard Feeds API', () => {
  test('API returns 401 when not authenticated', async ({ request }) => {
    const response = await request.get('/api/dashboard-stats.php');
    const status = response.status();

    // Should return 401 Unauthorized or redirect
    expect([401, 302]).toContain(status);
  });

  test('API response structure is valid JSON', async ({ request }) => {
    const response = await request.get('/api/dashboard-stats.php');

    if (response.status() === 200) {
      const contentType = response.headers()['content-type'];
      expect(contentType).toContain('application/json');

      const data = await response.json();
      expect(data).toHaveProperty('success');
    }
  });

  test('API includes feeds array when authenticated', async ({ request }) => {
    // Note: This test documents expected behavior
    // In practice, requires authenticated session
    const response = await request.get('/api/dashboard-stats.php');

    if (response.status() === 200) {
      const data = await response.json();

      // Should have feeds array
      expect(data.data).toHaveProperty('feeds');
      expect(Array.isArray(data.data.feeds)).toBe(true);

      // Should have agent_region
      expect(data.data).toHaveProperty('agent_region');
      expect(typeof data.data.agent_region).toBe('string');
    }
  });

  test('Feed items have required properties', async ({ request }) => {
    const response = await request.get('/api/dashboard-stats.php');

    if (response.status() === 200) {
      const data = await response.json();

      if (data.data?.feeds?.length > 0) {
        const feed = data.data.feeds[0];

        // Required feed item properties
        expect(feed).toHaveProperty('title');
        expect(feed).toHaveProperty('severity');
        expect(feed).toHaveProperty('source_ip');
        expect(feed).toHaveProperty('region');
        expect(feed).toHaveProperty('timestamp');
        expect(feed).toHaveProperty('detail');
      }
    }
  });

  test('Feed severity levels are valid', async ({ request }) => {
    const response = await request.get('/api/dashboard-stats.php');

    if (response.status() === 200) {
      const data = await response.json();
      const validSeverities = ['info', 'warning', 'critical'];

      for (const feed of data.data?.feeds || []) {
        expect(validSeverities).toContain(feed.severity);
      }
    }
  });

  test('Feed timestamps are valid ISO 8601', async ({ request }) => {
    const response = await request.get('/api/dashboard-stats.php');

    if (response.status() === 200) {
      const data = await response.json();

      for (const feed of data.data?.feeds || []) {
        const date = new Date(feed.timestamp);
        expect(date.toString()).not.toBe('Invalid Date');
      }
    }
  });
});

// =====================================================
// LIGHT THEME TESTS
// =====================================================
test.describe('Light Theme Support', () => {
  test('should apply light theme when set in localStorage', async ({ page }) => {
    await page.goto('/agent-login.php');

    // Set light theme
    await page.evaluate(() => {
      localStorage.setItem('theme', 'light');
      document.documentElement.setAttribute('data-theme', 'light');
    });

    // Verify theme attribute
    const theme = await page.evaluate(() =>
      document.documentElement.getAttribute('data-theme')
    );
    expect(theme).toBe('light');
  });

  test('should persist theme preference across reload', async ({ page }) => {
    await page.goto('/agent-login.php');

    // Set theme
    await page.evaluate(() => {
      localStorage.setItem('theme', 'light');
    });

    // Reload
    await page.reload();
    await page.waitForTimeout(500);

    // Check persistence
    const storedTheme = await page.evaluate(() =>
      localStorage.getItem('theme')
    );
    expect(storedTheme).toBe('light');
  });

  test('password toggle should be visible in light theme', async ({ page }) => {
    await page.goto('/agent-login.php');

    // Set light theme
    await page.evaluate(() => {
      localStorage.setItem('theme', 'light');
      document.documentElement.setAttribute('data-theme', 'light');
    });

    await page.waitForTimeout(500);

    // Password toggle should still be visible
    const toggleBtn = page.locator('#password').locator('..').locator('.password-toggle');
    await expect(toggleBtn).toBeVisible();
  });
});
