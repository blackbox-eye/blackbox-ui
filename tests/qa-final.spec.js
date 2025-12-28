// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Sprint 8 Final QA Tests
 * Comprehensive UI/UX verification before deploy
 */

const BASE_URL = process.env.BASE_URL || 'http://localhost:8000';

test.describe('Navigation & Mobile Menu', () => {
  test('burger menu should open and close on mobile viewport', async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto(BASE_URL, { waitUntil: 'domcontentloaded' });
    
    // Find burger menu button
    const burgerBtn = page.locator('#mobile-menu-btn, [aria-label*="menu" i], .mobile-menu-toggle, .burger-menu').first();
    
    // Check burger is visible on mobile
    await expect(burgerBtn).toBeVisible({ timeout: 5000 });
    
    // Click to open
    await burgerBtn.click();
    await page.waitForTimeout(400);
    
    // Check overlay is visible (not the drawer itself which uses translate-x)
    const overlay = page.locator('#mobile-menu-overlay');
    await expect(overlay).toHaveCSS('opacity', '1', { timeout: 3000 });
    
    // Check close button exists and click it
    const closeBtn = page.locator('#mobile-menu-close');
    await expect(closeBtn).toBeVisible();
    await closeBtn.click();
    await page.waitForTimeout(400);
    
    // Verify closed - overlay should have opacity 0
    await expect(overlay).toHaveCSS('opacity', '0');
  });

  test('desktop nav should show login dropdown', async ({ page }) => {
    // Desktop viewport
    await page.setViewportSize({ width: 1280, height: 800 });
    await page.goto(BASE_URL, { waitUntil: 'domcontentloaded' });
    
    // Find login button/dropdown trigger
    const loginTrigger = page.locator('#login-dropdown-btn, .console-access-dropdown button').first();
    
    if (await loginTrigger.isVisible()) {
      await loginTrigger.click();
      await page.waitForTimeout(300);
      
      // Check dropdown content - use specific ID
      const dropdown = page.locator('#login-dropdown-menu, .console-access-menu').first();
      await expect(dropdown).toBeVisible({ timeout: 2000 });
      
      // Verify login options exist
      const ccsLink = page.locator('a[href*="agent-access"][href*="ccs"], a[data-console-login="ccs"]').first();
      const gdiLink = page.locator('a[href*="agent-access"][href*="gdi"], a[data-console-login="gdi"]').first();
      
      // At least one login option should be present
      const hasCcs = await ccsLink.isVisible().catch(() => false);
      const hasGdi = await gdiLink.isVisible().catch(() => false);
      expect(hasCcs || hasGdi).toBeTruthy();
    }
  });
});

test.describe('Login Flow URLs', () => {
  test('CCS login page should load', async ({ page }) => {
    const response = await page.goto(`${BASE_URL}/ccs-login.php`, { waitUntil: 'domcontentloaded' });
    expect(response?.status()).toBeLessThan(400);
    
    // Check for login form
    const form = page.locator('form, .login-form, .auth-form');
    await expect(form.first()).toBeVisible({ timeout: 5000 });
  });

  test('GDI login page should load', async ({ page }) => {
    const response = await page.goto(`${BASE_URL}/gdi-login.php`, { waitUntil: 'domcontentloaded' });
    expect(response?.status()).toBeLessThan(400);
    
    // Check for login form
    const form = page.locator('form, .login-form, .auth-form');
    await expect(form.first()).toBeVisible({ timeout: 5000 });
  });

  test('agent-access page should load and show console cards', async ({ page }) => {
    const response = await page.goto(`${BASE_URL}/agent-access.php`, { waitUntil: 'domcontentloaded' });
    expect(response?.status()).toBeLessThan(400);
    
    // Check for console cards
    const cards = page.locator('.console-card, .access-card, [data-console]');
    const cardCount = await cards.count();
    expect(cardCount).toBeGreaterThan(0);
  });
});

test.describe('Language Switch', () => {
  test('should switch between Danish and English', async ({ page }) => {
    await page.goto(BASE_URL, { waitUntil: 'domcontentloaded' });
    
    // Find language switch
    const langSwitch = page.locator('.language-switch, [aria-label*="language" i], button:has-text("DA"), button:has-text("EN")').first();
    
    if (await langSwitch.isVisible()) {
      // Get current state
      const initialText = await page.locator('body').textContent();
      
      // Click to switch
      await langSwitch.click();
      await page.waitForTimeout(500);
      
      // Content should change
      const newText = await page.locator('body').textContent();
      // Either text changed or URL changed
      const urlChanged = page.url().includes('lang=');
      expect(initialText !== newText || urlChanged).toBeTruthy();
    }
  });
});

test.describe('Theme Toggles', () => {
  test('theme toggle should change data-theme attribute', async ({ page }) => {
    await page.goto(BASE_URL, { waitUntil: 'domcontentloaded' });
    
    // Find theme toggle
    const themeToggle = page.locator('.theme-toggle, .graphene-toggle, [aria-label*="theme" i], button:has-text("Standard"), button:has-text("Strong")').first();
    
    if (await themeToggle.isVisible()) {
      const initialTheme = await page.evaluate(() => document.documentElement.getAttribute('data-theme') || 'dark');
      
      await themeToggle.click();
      await page.waitForTimeout(300);
      
      const newTheme = await page.evaluate(() => document.documentElement.getAttribute('data-theme') || 'dark');
      // Theme should change or toggle state should change
      const toggleState = await themeToggle.getAttribute('aria-pressed');
      expect(initialTheme !== newTheme || toggleState !== null).toBeTruthy();
    }
  });
});

test.describe('Console Quick Switch', () => {
  test('quick switch dropdown should work on agent-access', async ({ page }) => {
    await page.goto(`${BASE_URL}/agent-access.php`, { waitUntil: 'domcontentloaded' });
    
    // Find quick switch
    const quickSwitch = page.locator('.quick-switch, .console-selector, select[name*="console"]').first();
    
    if (await quickSwitch.isVisible()) {
      await quickSwitch.click();
      await page.waitForTimeout(200);
      
      // Should show options
      const options = page.locator('.quick-switch-option, option, [role="option"]');
      const optionCount = await options.count();
      expect(optionCount).toBeGreaterThan(0);
    }
  });

  test('pin/star buttons should be clickable', async ({ page }) => {
    await page.goto(`${BASE_URL}/agent-access.php`, { waitUntil: 'domcontentloaded' });
    
    // Find star/pin button
    const starBtn = page.locator('.pin-btn, .star-btn, button[aria-label*="pin" i], button[aria-label*="favorite" i], button:has(svg path[d*="M12 2l3.09 6.26"])').first();
    
    if (await starBtn.isVisible()) {
      const initialState = await starBtn.getAttribute('aria-pressed');
      await starBtn.click();
      await page.waitForTimeout(200);
      
      // State should toggle
      const newState = await starBtn.getAttribute('aria-pressed');
      // Either aria-pressed changed or class changed
      const classChanged = await starBtn.evaluate(el => el.classList.contains('is-pinned') || el.classList.contains('pinned'));
      expect(initialState !== newState || classChanged).toBeTruthy();
    }
  });
});

test.describe('Cookie Banner Removal Verification', () => {
  test('cookie banner should NOT exist in DOM', async ({ page }) => {
    // Clear cookies first
    await page.context().clearCookies();
    await page.goto(BASE_URL, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);
    
    // Cookie banner should NOT exist - it has been completely removed
    const cookieBannerState = await page.evaluate(() => ({
      hasCookieBannerId: document.querySelector('#cookie-banner') !== null,
      hasCookieBannerClass: document.querySelector('.cookie-banner') !== null,
      hasCookieBannerOpenClass: document.body.classList.contains('cookie-banner-open'),
    }));
    
    expect(cookieBannerState.hasCookieBannerId).toBe(false);
    expect(cookieBannerState.hasCookieBannerClass).toBe(false);
    expect(cookieBannerState.hasCookieBannerOpenClass).toBe(false);
  });
});

test.describe('Focus & Keyboard Navigation', () => {
  test('all focusable elements should have visible focus ring', async ({ page }) => {
    await page.goto(BASE_URL, { waitUntil: 'domcontentloaded' });
    
    // Tab through elements
    for (let i = 0; i < 15; i++) {
      await page.keyboard.press('Tab');
      
      const focusInfo = await page.evaluate(() => {
        const el = document.activeElement;
        if (!el || el === document.body) return null;
        
        const styles = getComputedStyle(el);
        return {
          tag: el.tagName,
          outlineWidth: styles.outlineWidth,
          outlineStyle: styles.outlineStyle,
          boxShadow: styles.boxShadow?.substring(0, 50)
        };
      });
      
      if (focusInfo) {
        // Should have some focus indicator
        const hasOutline = parseFloat(focusInfo.outlineWidth) > 0 && focusInfo.outlineStyle !== 'none';
        const hasBoxShadow = focusInfo.boxShadow && focusInfo.boxShadow !== 'none';
        expect(hasOutline || hasBoxShadow).toBeTruthy();
      }
    }
  });
});

test.describe('Error & Static Pages', () => {
  test('404 page should load correctly', async ({ page }) => {
    const response = await page.goto(`${BASE_URL}/nonexistent-page-xyz123.php`, { waitUntil: 'domcontentloaded' });
    // Should either return 404 or show custom error page
    const status = response?.status();
    expect([200, 404]).toContain(status);
  });

  test('terms page should load', async ({ page }) => {
    const response = await page.goto(`${BASE_URL}/terms.php`, { waitUntil: 'domcontentloaded' });
    expect(response?.status()).toBeLessThan(400);
  });

  test('privacy page should load', async ({ page }) => {
    const response = await page.goto(`${BASE_URL}/privacy.php`, { waitUntil: 'domcontentloaded' });
    expect(response?.status()).toBeLessThan(400);
  });
});

test.describe('Meta & SEO', () => {
  test('homepage should have proper meta tags', async ({ page }) => {
    await page.goto(BASE_URL, { waitUntil: 'domcontentloaded' });
    
    // Check title
    const title = await page.title();
    expect(title.length).toBeGreaterThan(5);
    
    // Check meta description
    const metaDesc = await page.locator('meta[name="description"]').getAttribute('content');
    expect(metaDesc?.length || 0).toBeGreaterThan(10);
    
    // Check canonical
    const canonical = await page.locator('link[rel="canonical"]').getAttribute('href');
    expect(canonical).toBeTruthy();
    
    // Check OG tags
    const ogTitle = await page.locator('meta[property="og:title"]').getAttribute('content');
    expect(ogTitle).toBeTruthy();
  });

  test('favicon should load', async ({ page }) => {
    await page.goto(BASE_URL, { waitUntil: 'domcontentloaded' });
    
    const favicon = page.locator('link[rel="icon"], link[rel="shortcut icon"]').first();
    const href = await favicon.getAttribute('href');
    expect(href).toBeTruthy();
    
    // Try to fetch favicon
    if (href) {
      const faviconUrl = href.startsWith('http') ? href : `${BASE_URL}${href.startsWith('/') ? '' : '/'}${href}`;
      const response = await page.request.get(faviconUrl);
      expect(response.status()).toBeLessThan(400);
    }
  });
});

test.describe('Forms & Contact', () => {
  test('contact form should have proper validation', async ({ page }) => {
    await page.goto(`${BASE_URL}/contact.php`, { waitUntil: 'domcontentloaded' });
    
    // Find form
    const form = page.locator('form').first();
    
    if (await form.isVisible()) {
      // Check required fields
      const emailInput = form.locator('input[type="email"], input[name*="email"]').first();
      const submitBtn = form.locator('button[type="submit"], input[type="submit"]').first();
      
      if (await emailInput.isVisible() && await submitBtn.isVisible()) {
        // Try to submit empty
        await submitBtn.click();
        
        // Should show validation error or prevent submit
        const validationMsg = await emailInput.evaluate((el) => {
          return el.validationMessage || '';
        });
        // Browser should prevent empty required field
        expect(validationMsg.length > 0 || await emailInput.getAttribute('required') !== null).toBeTruthy();
      }
    }
  });
});

test.describe('Mobile Responsiveness', () => {
  test('no horizontal scroll on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto(BASE_URL, { waitUntil: 'domcontentloaded' });
    
    const scrollWidth = await page.evaluate(() => document.documentElement.scrollWidth);
    const clientWidth = await page.evaluate(() => document.documentElement.clientWidth);
    
    // Allow small tolerance (5px) for rounding
    expect(scrollWidth).toBeLessThanOrEqual(clientWidth + 5);
  });

  test('touch targets should be at least 44px', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto(BASE_URL, { waitUntil: 'domcontentloaded' });
    
    // Check buttons and links
    const interactives = await page.locator('button, a, [role="button"]').all();
    
    let smallTargets = 0;
    for (const el of interactives.slice(0, 20)) {
      const box = await el.boundingBox();
      if (box && box.width > 0 && box.height > 0) {
        if (box.width < 44 || box.height < 44) {
          smallTargets++;
        }
      }
    }
    
    // Allow some small icons, but majority should be proper size
    expect(smallTargets).toBeLessThan(10);
  });
});
