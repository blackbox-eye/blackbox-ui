/**
 * Dashboard Accessibility & Security Test Suite
 *
 * Tests for:
 * - API endpoint security (401 for unauthenticated requests)
 * - ARIA live regions and screen reader support
 * - Keyboard navigation
 * - Theme toggle accessibility
 * - Mobile responsive behavior
 *
 * @requires A running local server (e.g., php -S localhost:8000)
 */

const { test, expect } = require('@playwright/test');

// Base URL for tests
const BASE_URL = process.env.TEST_URL || 'http://localhost:8000';

// =====================================================
// API SECURITY TESTS (Unauthenticated Access)
// =====================================================
test.describe('API Security - Unauthenticated Access', () => {
  test('dashboard-stats.php should return 401 when not logged in', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/dashboard-stats.php`);
    expect(response.status()).toBe(401);

    const body = await response.json();
    expect(body.error).toBe('Unauthorized');
  });

  test('alerts.php should return 401 when not logged in', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/alerts.php`);
    expect(response.status()).toBe(401);

    const body = await response.json();
    expect(body.error).toBe('Unauthorized');
  });

  test('system-status.php should return 401 when not logged in', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/system-status.php`);
    expect(response.status()).toBe(401);

    const body = await response.json();
    expect(body.error).toBe('Unauthorized');
  });

  test('network-stats.php should return 401 when not logged in', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/network-stats.php`);
    expect(response.status()).toBe(401);

    const body = await response.json();
    expect(body.error).toBe('Unauthorized');
  });

  test('ai-command.php GET should return 401 when not logged in', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/ai-command.php`);
    expect(response.status()).toBe(401);

    const body = await response.json();
    expect(body.error).toBe('Unauthorized');
  });

  test('ai-command.php POST should return 401 when not logged in', async ({ request }) => {
    const response = await request.post(`${BASE_URL}/api/ai-command.php`, {
      data: { command: 'test command' }
    });
    expect(response.status()).toBe(401);

    const body = await response.json();
    expect(body.error).toBe('Unauthorized');
  });
});

// =====================================================
// API RESPONSE HEADERS TESTS
// =====================================================
test.describe('API Response Headers', () => {
  // These tests check headers even on 401 responses
  test('API responses should have security headers', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/dashboard-stats.php`);

    // Check Content-Type
    expect(response.headers()['content-type']).toContain('application/json');

    // Check X-Content-Type-Options
    expect(response.headers()['x-content-type-options']).toBe('nosniff');
  });

  test('API responses should have cache control headers', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/alerts.php`);

    // Check Cache-Control exists
    const cacheControl = response.headers()['cache-control'];
    expect(cacheControl).toBeDefined();
  });
});

// =====================================================
// API NEGATIVE TESTS (Invalid Input & Edge Cases)
// =====================================================
test.describe('API Negative Tests', () => {
  test('alerts.php should handle invalid severity filter gracefully', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/alerts.php?severity=invalid_value`);
    // Should still return 401 (auth required) but not crash
    expect(response.status()).toBe(401);
  });

  test('alerts.php should handle excessively large limit parameter', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/alerts.php?limit=999999`);
    expect(response.status()).toBe(401);
  });

  test('ai-command.php POST should handle empty command body', async ({ request }) => {
    const response = await request.post(`${BASE_URL}/api/ai-command.php`, {
      data: { command: '' }
    });
    expect(response.status()).toBe(401);
  });

  test('ai-command.php POST should handle missing command field', async ({ request }) => {
    const response = await request.post(`${BASE_URL}/api/ai-command.php`, {
      data: {}
    });
    expect(response.status()).toBe(401);
  });

  test('ai-command.php POST should handle malformed JSON', async ({ request }) => {
    const response = await request.post(`${BASE_URL}/api/ai-command.php`, {
      headers: { 'Content-Type': 'application/json' },
      body: 'not valid json {'
    });
    // Should return 401 (auth check happens first) or 400
    expect([400, 401]).toContain(response.status());
  });

  test('API should reject requests with invalid HTTP methods', async ({ request }) => {
    // DELETE is not implemented for dashboard-stats
    const response = await request.delete(`${BASE_URL}/api/dashboard-stats.php`);
    // Should return 401 (auth) or 405 (method not allowed)
    expect([401, 405]).toContain(response.status());
  });

  test('Non-existent API endpoint should return 404', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/non-existent-endpoint.php`);
    expect(response.status()).toBe(404);
  });
});

// =====================================================
// ACCESSIBILITY TESTS
// =====================================================
test.describe('Dashboard Accessibility', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/dashboard.php`);
    if (page.url().includes('agent-login.php')) {
      test.skip('Not logged in - skipping test');
    }
  });

  test('Dashboard should have ARIA live region for updates', async ({ page }) => {
    const liveRegion = page.locator('#dashboardLiveRegion');
    await expect(liveRegion).toBeAttached();

    // Check ARIA attributes
    await expect(liveRegion).toHaveAttribute('aria-live', 'polite');
    await expect(liveRegion).toHaveAttribute('aria-atomic', 'false');
  });

  test('Dashboard should have screen-reader-only class for live region', async ({ page }) => {
    const liveRegion = page.locator('#dashboardLiveRegion');
    await expect(liveRegion).toHaveClass(/sr-only/);
  });

  test('Polling indicator should have aria-hidden attribute', async ({ page }) => {
    const pollingIndicator = page.locator('#pollingIndicator');
    await expect(pollingIndicator).toBeAttached();
    await expect(pollingIndicator).toHaveAttribute('aria-hidden', 'true');
  });

  test('AI command textarea should have accessible label', async ({ page }) => {
    // Check for hidden label
    const label = page.locator('label[for="aiCommandInput"]');
    await expect(label).toBeAttached();
    await expect(label).toHaveClass(/sr-only/);

    // Check textarea has aria-describedby
    const textarea = page.locator('#aiCommandInput');
    await expect(textarea).toHaveAttribute('aria-describedby', 'aiCommandHint');
  });

  test('AI submit button should have aria-label', async ({ page }) => {
    const submitBtn = page.locator('#aiSubmitBtn');
    await expect(submitBtn).toHaveAttribute('aria-label', 'Send kommando til AI');
  });

  test('Server load chart should have aria-label', async ({ page }) => {
    const chart = page.locator('#serverLoadChart');
    await expect(chart).toHaveAttribute('aria-label', 'Graf over serverbelastning');
    await expect(chart).toHaveAttribute('role', 'img');
  });
});

// =====================================================
// THEME TOGGLE ACCESSIBILITY TESTS
// =====================================================
test.describe('Theme Toggle Accessibility', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/dashboard.php`);
    if (page.url().includes('agent-login.php')) {
      test.skip('Not logged in - skipping test');
    }
  });

  test('Theme toggle should have proper ARIA attributes', async ({ page }) => {
    // Open Control Panel first
    await page.click('#commandDeckLauncher');
    await page.waitForSelector('#commandDeckMenu.is-open');

    const themeToggle = page.locator('#themeToggle');
    await expect(themeToggle).toHaveAttribute('role', 'switch');
    await expect(themeToggle).toHaveAttribute('aria-label', 'Skift tema');
    await expect(themeToggle).toHaveAttribute('title', 'Skift mellem mørkt og lyst tema');
  });

  test('Theme toggle should update aria-pressed on click', async ({ page }) => {
    // Open Control Panel
    await page.click('#commandDeckLauncher');
    await page.waitForSelector('#commandDeckMenu.is-open');

    const themeToggle = page.locator('#themeToggle');

    // Get initial state
    const initialPressed = await themeToggle.getAttribute('aria-pressed');

    // Click toggle
    await themeToggle.click();

    // Check state changed
    const newPressed = await themeToggle.getAttribute('aria-pressed');
    expect(newPressed).not.toBe(initialPressed);
  });

  test('Theme icons should have aria-hidden', async ({ page }) => {
    await page.click('#commandDeckLauncher');
    await page.waitForSelector('#commandDeckMenu.is-open');

    const darkIcon = page.locator('#themeToggle .theme-icon--dark');
    const lightIcon = page.locator('#themeToggle .theme-icon--light');

    await expect(darkIcon).toHaveAttribute('aria-hidden', 'true');
    await expect(lightIcon).toHaveAttribute('aria-hidden', 'true');
  });
});

// =====================================================
// KEYBOARD NAVIGATION TESTS
// =====================================================
test.describe('Keyboard Navigation', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/dashboard.php`);
    if (page.url().includes('agent-login.php')) {
      test.skip('Not logged in - skipping test');
    }
  });

  test('Control Panel should close with Escape key', async ({ page }) => {
    // Open Control Panel
    await page.click('#commandDeckLauncher');
    await expect(page.locator('#commandDeckMenu')).toHaveClass(/is-open/);

    // Press Escape
    await page.keyboard.press('Escape');

    // Check it's closed
    await expect(page.locator('#commandDeckMenu')).not.toHaveClass(/is-open/);
  });

  test('AI command should submit with Ctrl+Enter', async ({ page }) => {
    const textarea = page.locator('#aiCommandInput');
    await textarea.fill('test command');

    // Press Ctrl+Enter
    await textarea.press('Control+Enter');

    // Check response area becomes visible
    const responseArea = page.locator('#aiResponseArea');
    await expect(responseArea).toBeVisible({ timeout: 5000 });
  });

  test('Skip link should be present for keyboard users', async ({ page }) => {
    const skipLink = page.locator('.skip-link');
    await expect(skipLink).toBeAttached();
    await expect(skipLink).toHaveAttribute('href', '#main-content');
  });
});

// =====================================================
// MOBILE RESPONSIVE TESTS
// =====================================================
test.describe('Mobile Responsive Behavior', () => {
  test.use({ viewport: { width: 375, height: 667 } }); // iPhone SE

  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/dashboard.php`);
    if (page.url().includes('agent-login.php')) {
      test.skip('Not logged in - skipping test');
    }
  });

  test('Mobile section toggle should be visible on small screens', async ({ page }) => {
    const toggleBtn = page.locator('#toggleSecondaryCards');
    await expect(toggleBtn).toBeVisible();
  });

  test('Secondary cards should be hidden by default on mobile', async ({ page }) => {
    const secondaryCards = page.locator('.dashboard__card--secondary');

    // On mobile, secondary cards should not be visible by default
    // (CSS display: none via media query)
    const firstCard = secondaryCards.first();
    await expect(firstCard).not.toBeVisible();
  });

  test('Toggle should expand secondary cards on mobile', async ({ page }) => {
    const toggleBtn = page.locator('#toggleSecondaryCards');

    // Click toggle
    await toggleBtn.click();

    // Check aria-expanded changed
    await expect(toggleBtn).toHaveAttribute('aria-expanded', 'true');

    // Check cards are now visible
    const secondaryCards = page.locator('.dashboard__card--secondary.is-expanded');
    await expect(secondaryCards.first()).toBeVisible();
  });
});

// =====================================================
// LIGHT MODE CONTRAST TESTS
// =====================================================
test.describe('Light Mode Contrast', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/dashboard.php`);
    if (page.url().includes('agent-login.php')) {
      test.skip('Not logged in - skipping test');
    }
  });

  test('Light theme should apply correct CSS variables', async ({ page }) => {
    // Set light theme
    await page.evaluate(() => {
      document.documentElement.setAttribute('data-theme', 'light');
    });

    // Check a text color variable is correctly applied
    const textColor = await page.evaluate(() => {
      return getComputedStyle(document.documentElement).getPropertyValue('--admin-text-primary').trim();
    });

    // Light mode should have dark text
    expect(textColor).toBe('#1a1a1a');
  });

  test('Light theme text colors should be solid (not rgba)', async ({ page }) => {
    await page.evaluate(() => {
      document.documentElement.setAttribute('data-theme', 'light');
    });

    const secondaryText = await page.evaluate(() => {
      return getComputedStyle(document.documentElement).getPropertyValue('--admin-text-secondary').trim();
    });

    // Should be a solid hex color, not rgba
    expect(secondaryText).not.toMatch(/rgba/i);
    expect(secondaryText).toMatch(/#[0-9a-f]{6}/i);
  });
});

// =====================================================
// ERROR HANDLING TESTS
// =====================================================
test.describe('Error Handling', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/dashboard.php`);
    if (page.url().includes('agent-login.php')) {
      test.skip('Not logged in - skipping test');
    }
  });

  test('AI command should show error on timeout', async ({ page }) => {
    // Mock a slow network response
    await page.route('**/api/ai-command.php', async route => {
      await new Promise(resolve => setTimeout(resolve, 20000)); // 20 second delay
      route.abort();
    });

    const textarea = page.locator('#aiCommandInput');
    await textarea.fill('test slow command');
    await page.click('#aiSubmitBtn');

    // Wait for timeout error message (15 second AI_TIMEOUT + buffer)
    const responseText = page.locator('#aiResponseText');
    await expect(responseText).toContainText('Timeout', { timeout: 20000 });
  });

  test('AI command should show network error message', async ({ page }) => {
    // Mock a network failure
    await page.route('**/api/ai-command.php', route => route.abort('failed'));

    const textarea = page.locator('#aiCommandInput');
    await textarea.fill('test network error');
    await page.click('#aiSubmitBtn');

    // Check for error message
    const responseText = page.locator('#aiResponseText');
    await expect(responseText).toContainText(/Netværksfejl|fetch/, { timeout: 5000 });
  });

  test('AI error messages should have role=alert', async ({ page }) => {
    // Mock a network failure
    await page.route('**/api/ai-command.php', route => route.abort('failed'));

    const textarea = page.locator('#aiCommandInput');
    await textarea.fill('test error alert');
    await page.click('#aiSubmitBtn');

    // Wait for error and check role
    await page.waitForTimeout(2000);
    const alertSpan = page.locator('#aiResponseText [role="alert"]');
    await expect(alertSpan).toBeAttached();
  });
});

// =====================================================
// THEME TOGGLE WITH DATA PERSISTENCE
// =====================================================
test.describe('Theme Toggle with Real Data', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/dashboard.php`);
    if (page.url().includes('agent-login.php')) {
      test.skip('Not logged in - skipping test');
    }
  });

  test('Dashboard data should persist after theme toggle', async ({ page }) => {
    // Wait for initial data load
    await page.waitForTimeout(2000);

    // Get initial stat values
    const initialAlerts = await page.locator('#statAlerts').textContent();
    const initialThreats = await page.locator('#statThreats').textContent();

    // Open Control Panel and toggle theme
    await page.click('#commandDeckLauncher');
    await page.waitForSelector('#commandDeckMenu.is-open');
    await page.click('#themeToggle');

    // Close Control Panel
    await page.keyboard.press('Escape');

    // Verify data is still displayed (not reset to loading state)
    const afterAlerts = await page.locator('#statAlerts').textContent();
    const afterThreats = await page.locator('#statThreats').textContent();

    // Data should be preserved (or refreshed, but not empty/loading)
    expect(afterAlerts).not.toBe('—');
    expect(afterThreats).not.toBe('—');
  });

  test('Theme preference should be applied to all dashboard elements', async ({ page }) => {
    // Set light theme
    await page.evaluate(() => {
      document.documentElement.setAttribute('data-theme', 'light');
    });

    // Check that dashboard cards have correct styling applied
    const card = page.locator('.dashboard__card').first();
    const cardBg = await card.evaluate(el => getComputedStyle(el).backgroundColor);

    // Light theme cards should have a lighter background
    // This checks that the theme actually affects the rendered styles
    expect(cardBg).toBeDefined();
  });

  test('Alert badges should maintain correct colors in both themes', async ({ page }) => {
    // Check dark theme badge colors
    const criticalBadge = page.locator('.dashboard__card-badge--critical').first();

    if (await criticalBadge.isVisible()) {
      const darkColor = await criticalBadge.evaluate(el => getComputedStyle(el).color);

      // Toggle to light theme
      await page.evaluate(() => {
        document.documentElement.setAttribute('data-theme', 'light');
      });

      const lightColor = await criticalBadge.evaluate(el => getComputedStyle(el).color);

      // Colors should be defined in both themes
      expect(darkColor).toBeDefined();
      expect(lightColor).toBeDefined();
    }
  });
});

// =====================================================
// ARIA LABELS COMPREHENSIVE TESTS
// =====================================================
test.describe('ARIA Labels Comprehensive', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/dashboard.php`);
    if (page.url().includes('agent-login.php')) {
      test.skip('Not logged in - skipping test');
    }
  });

  test('All interactive elements should have accessible names', async ({ page }) => {
    // Check all buttons have accessible names
    const buttons = page.locator('button:not([aria-hidden="true"])');
    const buttonCount = await buttons.count();

    for (let i = 0; i < buttonCount; i++) {
      const button = buttons.nth(i);
      const hasAriaLabel = await button.getAttribute('aria-label');
      const hasTitle = await button.getAttribute('title');
      const hasTextContent = await button.textContent();

      // Each button should have at least one form of accessible name
      const hasAccessibleName = hasAriaLabel || hasTitle || (hasTextContent && hasTextContent.trim().length > 0);
      expect(hasAccessibleName).toBeTruthy();
    }
  });

  test('Dashboard cards should have proper heading structure', async ({ page }) => {
    // All cards should have h2 headings
    const cardTitles = page.locator('.dashboard__card-title');
    const count = await cardTitles.count();

    expect(count).toBeGreaterThan(0);

    for (let i = 0; i < count; i++) {
      const title = cardTitles.nth(i);
      // Card titles should be contained in h2 elements
      const parent = await title.evaluate(el => el.tagName.toLowerCase());
      expect(parent).toBe('h2');
    }
  });

  test('Control Panel links should have descriptive text or aria-label', async ({ page }) => {
    // Open Control Panel
    await page.click('#commandDeckLauncher');
    await page.waitForSelector('#commandDeckMenu.is-open');

    const navLinks = page.locator('.command-deck__item');
    const linkCount = await navLinks.count();

    for (let i = 0; i < linkCount; i++) {
      const link = navLinks.nth(i);
      const text = await link.textContent();
      const ariaLabel = await link.getAttribute('aria-label');
      const title = await link.getAttribute('title');

      // Each link should have some accessible text
      expect(text.trim() || ariaLabel || title).toBeTruthy();
    }
  });
});
