/**
 * Dashboard Dynamic Features Test Suite
 *
 * Tests for the dynamic dashboard functionality including:
 * - Real-time API integration
 * - Threat overview hero card
 * - Alerts display with severity badges
 * - System status monitoring
 * - Network stats visualization
 * - AI command interface
 * - Theme toggle functionality
 *
 * @requires A running local server (e.g., php -S localhost:8000)
 */

const { test, expect } = require('@playwright/test');

// Base URL for tests
const BASE_URL = process.env.TEST_URL || 'http://localhost:8000';

// Helper to check if logged in
async function isLoggedIn(page) {
  await page.goto(`${BASE_URL}/dashboard.php`);
  return !page.url().includes('agent-login.php');
}

// Helper to login (assumes test credentials exist)
async function login(page, agentId = 'test_agent', password = 'test_password') {
  await page.goto(`${BASE_URL}/agent-login.php`);
  await page.fill('input[name="agent_id"]', agentId);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await page.waitForURL(/dashboard\.php|agent-login\.php/, { timeout: 5000 });
}

// =====================================================
// DASHBOARD API INTEGRATION TESTS
// =====================================================
test.describe('Dashboard API Integration', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/dashboard.php`);
    if (page.url().includes('agent-login.php')) {
      test.skip('Not logged in - skipping test');
    }
  });

  test('Dashboard stats should load via API', async ({ page }) => {
    // Wait for stats to be populated (not showing "—")
    const statAlerts = page.locator('#statAlerts');
    await expect(statAlerts).toBeVisible();

    // Wait for content to load (either a number or still loading)
    await page.waitForTimeout(2000);

    // Stats should have been updated from placeholder
    const alertsText = await statAlerts.textContent();
    expect(alertsText).not.toBe('—');
  });

  test('Dashboard should have threat overview hero card', async ({ page }) => {
    const threatHero = page.locator('.dashboard__threat-hero');
    await expect(threatHero).toBeVisible();

    // Check threat score element exists
    const threatScore = page.locator('#threatScore');
    await expect(threatScore).toBeVisible();

    // Check threat status badge exists
    const threatStatus = page.locator('#threatStatus');
    await expect(threatStatus).toBeVisible();
  });

  test('Dashboard should display active alerts', async ({ page }) => {
    const alertsContainer = page.locator('#alertsContainer');
    await expect(alertsContainer).toBeVisible();

    // Wait for alerts to load
    await page.waitForTimeout(2000);

    // Either alerts or empty state should be shown
    const hasAlerts = await page.locator('.dashboard__alert').count() > 0;
    const hasEmptyState = await alertsContainer.locator('svg').count() > 0;

    expect(hasAlerts || hasEmptyState).toBeTruthy();
  });

  test('Dashboard should display system status', async ({ page }) => {
    const statusList = page.locator('#systemStatusList');
    await expect(statusList).toBeVisible();

    // Wait for status to load
    await page.waitForTimeout(2000);

    // Check badge shows status
    const badge = page.locator('#systemHealthBadge');
    const badgeText = await badge.textContent();
    expect(badgeText.length).toBeGreaterThan(0);
  });

  test('Dashboard should display network stats', async ({ page }) => {
    const networkContainer = page.locator('#networkContainer');
    await expect(networkContainer).toBeVisible();

    // Wait for network data to load
    await page.waitForTimeout(2000);
  });
});

// =====================================================
// AI COMMAND INTERFACE TESTS
// =====================================================
test.describe('AI Command Interface', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/dashboard.php`);
    if (page.url().includes('agent-login.php')) {
      test.skip('Not logged in - skipping test');
    }
  });

  test('AI command interface should be visible', async ({ page }) => {
    const aiCard = page.locator('.dashboard__card--ai');
    await expect(aiCard).toBeVisible();

    // Check input field exists
    const input = page.locator('#aiCommandInput');
    await expect(input).toBeVisible();

    // Check form exists
    const form = page.locator('#aiCommandForm');
    await expect(form).toBeVisible();
  });

  test('AI command input should accept text', async ({ page }) => {
    const input = page.locator('#aiCommandInput');
    await expect(input).toBeVisible();

    // Type a test command
    await input.fill('Test command for GREY-E');

    // Verify input value
    const value = await input.inputValue();
    expect(value).toBe('Test command for GREY-E');
  });

  test('AI command should show response area on submit', async ({ page }) => {
    const input = page.locator('#aiCommandInput');
    const form = page.locator('#aiCommandForm');

    // Type and submit command
    await input.fill('Analyze test');
    await form.evaluate(f => f.dispatchEvent(new Event('submit')));

    // Response area should become visible
    const responseArea = page.locator('#aiResponseArea');
    await expect(responseArea).toBeVisible({ timeout: 5000 });
  });

  test('AI command log should display history', async ({ page }) => {
    const commandLog = page.locator('#aiCommandLog');
    await expect(commandLog).toBeVisible();

    // Wait for history to load
    await page.waitForTimeout(2000);
  });
});

// =====================================================
// THEME TOGGLE TESTS
// =====================================================
test.describe('Theme Toggle', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/dashboard.php`);
    if (page.url().includes('agent-login.php')) {
      test.skip('Not logged in - skipping test');
    }
    // Clear localStorage theme preference
    await page.evaluate(() => localStorage.removeItem('greyeye-theme'));
  });

  test('Theme toggle button should be visible in Command Deck', async ({ page }) => {
    // First open the Command Deck
    const launcher = page.locator('#commandDeckLauncher');
    await expect(launcher).toBeVisible();
    await launcher.click();

    // Wait for menu to open
    await page.waitForSelector('.command-deck.is-open');

    // Check theme toggle exists
    const themeToggle = page.locator('#themeToggle');
    await expect(themeToggle).toBeVisible();
  });

  test('Theme should default to dark mode', async ({ page }) => {
    // Check document has dark theme
    const theme = await page.evaluate(() =>
      document.documentElement.getAttribute('data-theme')
    );

    expect(theme).toBe('dark');
  });

  test('Clicking theme toggle should switch to light mode', async ({ page }) => {
    // Open Command Deck
    const launcher = page.locator('#commandDeckLauncher');
    await launcher.click();
    await page.waitForSelector('.command-deck.is-open');

    // Click theme toggle
    const themeToggle = page.locator('#themeToggle');
    await themeToggle.click();

    // Check theme changed to light
    const theme = await page.evaluate(() =>
      document.documentElement.getAttribute('data-theme')
    );

    expect(theme).toBe('light');
  });

  test('Theme preference should persist in localStorage', async ({ page }) => {
    // Open Command Deck and click toggle
    await page.locator('#commandDeckLauncher').click();
    await page.waitForSelector('.command-deck.is-open');
    await page.locator('#themeToggle').click();

    // Wait a moment for localStorage to be updated
    await page.waitForTimeout(500);

    // Check localStorage
    const storedTheme = await page.evaluate(() =>
      localStorage.getItem('greyeye-theme')
    );

    expect(storedTheme).toBe('light');
  });

  test('Theme should load from localStorage on page refresh', async ({ page }) => {
    // Set theme preference in localStorage
    await page.evaluate(() => localStorage.setItem('greyeye-theme', 'light'));

    // Reload page
    await page.reload();

    // Check theme is light
    const theme = await page.evaluate(() =>
      document.documentElement.getAttribute('data-theme')
    );

    expect(theme).toBe('light');
  });

  test('Light theme should have correct CSS variables', async ({ page }) => {
    // Set light theme
    await page.evaluate(() => {
      document.documentElement.setAttribute('data-theme', 'light');
    });

    // Check background color is light
    const bgColor = await page.evaluate(() =>
      getComputedStyle(document.documentElement).getPropertyValue('--admin-bg-primary').trim()
    );

    // Light theme background should be light colored
    expect(bgColor).toBe('#f5f5f7');
  });
});

// =====================================================
// API ENDPOINT TESTS
// =====================================================
test.describe('API Endpoints', () => {
  test('dashboard-stats.php should return JSON', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/dashboard-stats.php`);

    // If not logged in, expect 401 or redirect
    if (response.status() === 401 || response.status() === 302) {
      expect([401, 302]).toContain(response.status());
      return;
    }

    // Otherwise expect JSON
    const contentType = response.headers()['content-type'];
    expect(contentType).toContain('application/json');

    const data = await response.json();
    expect(data).toHaveProperty('success');
  });

  test('alerts.php should return JSON with alerts array', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/alerts.php`);

    if (response.status() === 401 || response.status() === 302) {
      expect([401, 302]).toContain(response.status());
      return;
    }

    const contentType = response.headers()['content-type'];
    expect(contentType).toContain('application/json');

    const data = await response.json();
    expect(data).toHaveProperty('success');
    if (data.success) {
      expect(data).toHaveProperty('data');
      expect(Array.isArray(data.data)).toBeTruthy();
    }
  });

  test('system-status.php should return service statuses', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/system-status.php`);

    if (response.status() === 401 || response.status() === 302) {
      expect([401, 302]).toContain(response.status());
      return;
    }

    const data = await response.json();
    expect(data).toHaveProperty('success');
    if (data.success && data.data) {
      expect(data.data).toHaveProperty('services');
    }
  });

  test('network-stats.php should return port data', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/network-stats.php`);

    if (response.status() === 401 || response.status() === 302) {
      expect([401, 302]).toContain(response.status());
      return;
    }

    const data = await response.json();
    expect(data).toHaveProperty('success');
    if (data.success && data.data) {
      expect(data.data).toHaveProperty('ports');
    }
  });

  test('ai-command.php GET should return command history', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/ai-command.php`);

    if (response.status() === 401 || response.status() === 302) {
      expect([401, 302]).toContain(response.status());
      return;
    }

    const data = await response.json();
    expect(data).toHaveProperty('success');
    if (data.success) {
      expect(data).toHaveProperty('data');
      expect(Array.isArray(data.data)).toBeTruthy();
    }
  });
});

// =====================================================
// RESPONSIVE DESIGN TESTS
// =====================================================
test.describe('Dashboard Responsive Design', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/dashboard.php`);
    if (page.url().includes('agent-login.php')) {
      test.skip('Not logged in - skipping test');
    }
  });

  test('Dashboard should be responsive on mobile', async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 812 });

    // Dashboard grid should stack on mobile
    const grid = page.locator('.dashboard__grid');
    await expect(grid).toBeVisible();

    // Cards should be full width on mobile
    const card = page.locator('.dashboard__card').first();
    const box = await card.boundingBox();

    // Card width should be close to viewport width minus padding
    expect(box.width).toBeGreaterThan(300);
  });

  test('Stats row should stack on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });

    const stats = page.locator('.dashboard__stats');
    await expect(stats).toBeVisible();
  });

  test('Threat hero should be responsive', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });

    const threatHero = page.locator('.dashboard__threat-hero');
    await expect(threatHero).toBeVisible();

    // Score and details should stack
    const threatScore = page.locator('.dashboard__threat-score');
    await expect(threatScore).toBeVisible();
  });
});

// =====================================================
// SERVER LOAD CHART TESTS
// =====================================================
test.describe('Server Load Chart', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/dashboard.php`);
    if (page.url().includes('agent-login.php')) {
      test.skip('Not logged in - skipping test');
    }
  });

  test('Server load chart should render', async ({ page }) => {
    // Wait for Chart.js to load
    await page.waitForTimeout(2000);

    const chart = page.locator('#serverLoadChart');
    await expect(chart).toBeVisible();
  });

  test('Chart canvas should have dimensions', async ({ page }) => {
    await page.waitForTimeout(2000);

    const chart = page.locator('#serverLoadChart');
    const box = await chart.boundingBox();

    expect(box.width).toBeGreaterThan(100);
    expect(box.height).toBeGreaterThan(50);
  });
});
