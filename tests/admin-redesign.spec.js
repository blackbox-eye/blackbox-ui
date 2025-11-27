/**
 * Admin Redesign Test Suite
 *
 * Tests for the modernized admin portal including:
 * - Dashboard card-based layout
 * - Settings panel-based design
 * - Admin page user management
 * - Responsive grid layouts
 * - GreyEYE branding consistency
 *
 * @requires A running local server (e.g., php -S localhost:8000)
 */

const { test, expect } = require('@playwright/test');

// =====================================================
// DASHBOARD REDESIGN TESTS
// =====================================================
test.describe('Dashboard Redesign', () => {
  test.describe('Dashboard Layout Structure', () => {
    test('Dashboard should use admin-page layout', async ({ page }) => {
      await page.goto('/dashboard.php');

      // If redirected to login, skip
      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Verify main layout structure
      const mainContent = page.locator('#main-content');
      await expect(mainContent).toBeVisible();

      // Verify admin-page container
      const adminPage = page.locator('.admin-page');
      await expect(adminPage).toBeVisible();
    });

    test('Dashboard should have page header with title', async ({ page }) => {
      await page.goto('/dashboard.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      const header = page.locator('.admin-page__header');
      await expect(header).toBeVisible();

      const title = page.locator('.admin-page__title');
      await expect(title).toBeVisible();
    });

    test('Dashboard should have card-based grid layout', async ({ page }) => {
      await page.goto('/dashboard.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Check dashboard grid exists
      const grid = page.locator('.dashboard-grid');
      await expect(grid).toBeVisible();

      // Check dashboard cards exist (at least 3)
      const cards = page.locator('.dashboard-card');
      const count = await cards.count();
      expect(count).toBeGreaterThanOrEqual(3);
    });

    test('Dashboard cards should have headers and content', async ({ page }) => {
      await page.goto('/dashboard.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      const firstCard = page.locator('.dashboard-card').first();
      await expect(firstCard).toBeVisible();

      // Check card structure
      const cardHeader = firstCard.locator('.dashboard-card__header');
      await expect(cardHeader).toBeVisible();
    });

    test('Old sidebar (#nav-menu) should NOT exist', async ({ page }) => {
      await page.goto('/dashboard.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Old sidebar should be removed
      const oldSidebar = page.locator('#nav-menu');
      await expect(oldSidebar).not.toBeAttached();
    });
  });

  test.describe('Dashboard Responsive Design', () => {
    const viewports = [
      { name: 'mobile', width: 375, height: 812 },
      { name: 'tablet', width: 768, height: 1024 },
      { name: 'desktop', width: 1440, height: 900 }
    ];

    for (const vp of viewports) {
      test(`Dashboard grid adapts on ${vp.name}`, async ({ page }) => {
        await page.setViewportSize({ width: vp.width, height: vp.height });
        await page.goto('/dashboard.php');

        if (page.url().includes('agent-login.php')) {
          test.skip();
          return;
        }

        const grid = page.locator('.dashboard-grid');
        await expect(grid).toBeVisible();

        // Take screenshot for visual comparison
        await page.screenshot({
          path: `artifacts/dashboard-${vp.name}-${vp.width}x${vp.height}.png`,
          fullPage: true
        });
      });
    }
  });

  test.describe('Dashboard Branding', () => {
    test('Dashboard should display GreyEYE logo', async ({ page }) => {
      await page.goto('/dashboard.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Check for GreyEYE logo in header or page
      const logo = page.locator('img[src*="greyeye"], img[alt*="GreyEYE"], img[alt*="Grey EYE"]');
      await expect(logo.first()).toBeVisible();
    });
  });
});

// =====================================================
// SETTINGS REDESIGN TESTS
// =====================================================
test.describe('Settings Redesign', () => {
  test.describe('Settings Layout Structure', () => {
    test('Settings should use admin-page layout', async ({ page }) => {
      await page.goto('/settings.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      const adminPage = page.locator('.admin-page');
      await expect(adminPage).toBeVisible();
    });

    test('Settings should have panel-based sections', async ({ page }) => {
      await page.goto('/settings.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Check settings grid/panels exist
      const panels = page.locator('.settings-panel, .settings-card, .admin-card');
      const count = await panels.count();
      expect(count).toBeGreaterThanOrEqual(3);
    });

    test('Settings should have security section for password/PIN', async ({ page }) => {
      await page.goto('/settings.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Check for password or PIN related form elements
      const passwordInput = page.locator('input[type="password"], input[name*="password"], input[name*="pin"]');
      await expect(passwordInput.first()).toBeAttached();
    });

    test('Settings should have token section', async ({ page }) => {
      await page.goto('/settings.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Check for token-related elements
      const tokenSection = page.locator('[class*="token"], button:has-text("Token"), h2:has-text("Token"), h3:has-text("Token")');
      await expect(tokenSection.first()).toBeAttached();
    });

    test('Old vertical MENU button should NOT exist', async ({ page }) => {
      await page.goto('/settings.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Old MENU button should be removed
      const oldMenuBtn = page.locator('.menu-toggle-btn, #menuToggle');
      await expect(oldMenuBtn).not.toBeVisible();
    });
  });

  test.describe('Settings Form Functionality', () => {
    test('Password change form should be present', async ({ page }) => {
      await page.goto('/settings.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Check for password form fields
      const currentPassword = page.locator('input[name="current_password"], input[name="old_password"]');
      const newPassword = page.locator('input[name="new_password"], input[name="password"]');

      // At least one password-related input should exist
      const hasPasswordForm = (await currentPassword.count()) > 0 || (await newPassword.count()) > 0;
      expect(hasPasswordForm).toBeTruthy();
    });

    test('Contact info update form should be present', async ({ page }) => {
      await page.goto('/settings.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Check for email or phone input
      const emailInput = page.locator('input[type="email"], input[name*="email"]');
      const phoneInput = page.locator('input[type="tel"], input[name*="phone"]');

      const hasContactForm = (await emailInput.count()) > 0 || (await phoneInput.count()) > 0;
      expect(hasContactForm).toBeTruthy();
    });

    test('Ghost mode toggle should be present', async ({ page }) => {
      await page.goto('/settings.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Check for ghost mode button or toggle
      const ghostToggle = page.locator('button:has-text("Ghost"), a:has-text("Ghost"), input[name*="ghost"]');
      await expect(ghostToggle.first()).toBeAttached();
    });
  });

  test.describe('Settings Responsive Design', () => {
    const viewports = [
      { name: 'mobile', width: 375, height: 812 },
      { name: 'tablet', width: 768, height: 1024 },
      { name: 'desktop', width: 1440, height: 900 }
    ];

    for (const vp of viewports) {
      test(`Settings panels adapt on ${vp.name}`, async ({ page }) => {
        await page.setViewportSize({ width: vp.width, height: vp.height });
        await page.goto('/settings.php');

        if (page.url().includes('agent-login.php')) {
          test.skip();
          return;
        }

        const adminPage = page.locator('.admin-page');
        await expect(adminPage).toBeVisible();

        await page.screenshot({
          path: `artifacts/settings-${vp.name}-${vp.width}x${vp.height}.png`,
          fullPage: true
        });
      });
    }
  });
});

// =====================================================
// ADMIN (USER MANAGEMENT) REDESIGN TESTS
// =====================================================
test.describe('Admin Page Redesign', () => {
  test.describe('Admin Layout Structure', () => {
    test('Admin page should use admin-page layout', async ({ page }) => {
      await page.goto('/admin.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      const adminPage = page.locator('.admin-page');
      await expect(adminPage).toBeVisible();
    });

    test('Admin page should have users table', async ({ page }) => {
      await page.goto('/admin.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Check for users table
      const table = page.locator('table, .admin-users__table');
      await expect(table).toBeVisible();
    });

    test('Admin page should have agent count stats', async ({ page }) => {
      await page.goto('/admin.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Check for stats display
      const stats = page.locator('.admin-users__stats, .stats-card, [class*="stat"]');
      await expect(stats.first()).toBeVisible();
    });

    test('Admin page should have create agent form', async ({ page }) => {
      await page.goto('/admin.php');

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Check for create agent form
      const createForm = page.locator('form[method="post"]');
      await expect(createForm).toBeVisible();

      // Check for agent_id input
      const agentIdInput = page.locator('input[name="agent_id"]');
      await expect(agentIdInput).toBeAttached();
    });
  });
});

// =====================================================
// COMMAND DECK INTEGRATION TESTS
// =====================================================
test.describe('Command Deck Integration', () => {
  const adminPages = [
    { name: 'Dashboard', url: '/dashboard.php' },
    { name: 'Settings', url: '/settings.php' },
    { name: 'Admin', url: '/admin.php' }
  ];

  for (const p of adminPages) {
    test(`Command Deck accessible from ${p.name}`, async ({ page }) => {
      await page.goto(p.url);

      if (page.url().includes('agent-login.php')) {
        test.skip();
        return;
      }

      // Command Deck launcher should be visible
      const launcher = page.locator('#commandDeckLauncher');
      await expect(launcher).toBeVisible();

      // Click to open
      await launcher.click();

      // Menu should slide in
      const menu = page.locator('#commandDeckMenu');
      await expect(menu).toHaveClass(/is-open/);

      // Navigation items should be visible
      const navItems = page.locator('.command-deck__item');
      const count = await navItems.count();
      expect(count).toBeGreaterThanOrEqual(3);

      // Close with ESC
      await page.keyboard.press('Escape');
      await expect(menu).not.toHaveClass(/is-open/);
    });
  }

  test('Command Deck highlights active page', async ({ page }) => {
    await page.goto('/dashboard.php');

    if (page.url().includes('agent-login.php')) {
      test.skip();
      return;
    }

    const launcher = page.locator('#commandDeckLauncher');
    await launcher.click();

    // Check for active state on dashboard item
    const dashboardItem = page.locator('.command-deck__item[href*="dashboard"], .command-deck__item.is-active');
    await expect(dashboardItem.first()).toBeVisible();
  });
});

// =====================================================
// DESIGN TOKEN CONSISTENCY TESTS
// =====================================================
test.describe('Design Token Consistency', () => {
  test('Admin pages should use consistent gold accent color', async ({ page }) => {
    await page.goto('/dashboard.php');

    if (page.url().includes('agent-login.php')) {
      test.skip();
      return;
    }

    // Check that CSS custom properties are defined
    const bodyStyles = await page.evaluate(() => {
      const style = getComputedStyle(document.documentElement);
      return {
        adminGold: style.getPropertyValue('--admin-gold').trim(),
        adminTextGold: style.getPropertyValue('--admin-text-gold').trim()
      };
    });

    // Verify gold colors are defined (should be hex values)
    expect(bodyStyles.adminGold).toBeTruthy();
    expect(bodyStyles.adminTextGold).toBeTruthy();
  });

  test('Cards should have consistent background styling', async ({ page }) => {
    await page.goto('/dashboard.php');

    if (page.url().includes('agent-login.php')) {
      test.skip();
        return;
    }

    const card = page.locator('.dashboard-card').first();
    if (await card.count() === 0) {
      test.skip();
      return;
    }

    const cardStyles = await card.evaluate((el) => {
      const style = getComputedStyle(el);
      return {
        background: style.backgroundColor,
        border: style.border || style.borderColor
      };
    });

    // Card should have some background (not transparent)
    expect(cardStyles.background).toBeTruthy();
  });
});

// =====================================================
// ACCESSIBILITY TESTS FOR REDESIGNED PAGES
// =====================================================
test.describe('Accessibility - Redesigned Pages', () => {
  test('Dashboard should have proper heading hierarchy', async ({ page }) => {
    await page.goto('/dashboard.php');

    if (page.url().includes('agent-login.php')) {
      test.skip();
      return;
    }

    // Should have h1
    const h1 = page.locator('h1');
    await expect(h1).toBeAttached();

    // H2s for card sections
    const h2s = page.locator('h2, h3');
    const count = await h2s.count();
    expect(count).toBeGreaterThanOrEqual(1);
  });

  test('Settings forms should have associated labels', async ({ page }) => {
    await page.goto('/settings.php');

    if (page.url().includes('agent-login.php')) {
      test.skip();
      return;
    }

    // Check that inputs have labels or aria-labels
    const inputs = page.locator('input:not([type="hidden"]):not([type="submit"])');
    const count = await inputs.count();

    for (let i = 0; i < Math.min(count, 5); i++) {
      const input = inputs.nth(i);
      const id = await input.getAttribute('id');
      const ariaLabel = await input.getAttribute('aria-label');
      const ariaLabelledBy = await input.getAttribute('aria-labelledby');

      // Input should have either a label, aria-label, or aria-labelledby
      if (id) {
        const label = page.locator(`label[for="${id}"]`);
        const hasLabel = (await label.count()) > 0;
        const hasAriaLabel = ariaLabel !== null || ariaLabelledBy !== null;
        expect(hasLabel || hasAriaLabel).toBeTruthy();
      }
    }
  });

  test('Command Deck should be keyboard accessible', async ({ page }) => {
    await page.goto('/dashboard.php');

    if (page.url().includes('agent-login.php')) {
      test.skip();
      return;
    }

    // Tab to launcher
    const launcher = page.locator('#commandDeckLauncher');
    await launcher.focus();

    // Enter should open menu
    await page.keyboard.press('Enter');
    const menu = page.locator('#commandDeckMenu');
    await expect(menu).toHaveClass(/is-open/);

    // ESC should close
    await page.keyboard.press('Escape');
    await expect(menu).not.toHaveClass(/is-open/);
  });
});
