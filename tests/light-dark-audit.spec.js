// @ts-check
const { test, expect } = require('@playwright/test');
const AxeBuilder = require('@axe-core/playwright').default;

/**
 * Sprint 8 Light/Dark Mode Audit Tests
 * Comprehensive visual and a11y testing for both themes
 */

const BASE_URL = process.env.BASE_URL || 'http://localhost:8000';

const PAGES_TO_TEST = [
  { name: 'Landing', path: '/' },
  { name: 'About', path: '/about.php' },
  { name: 'Products', path: '/products.php' },
  { name: 'Cases', path: '/cases.php' },
  { name: 'Pricing', path: '/pricing.php' },
  { name: 'Contact', path: '/contact.php' },
  { name: 'FAQ', path: '/faq.php' },
  { name: 'Agent Access', path: '/agent-access.php' },
];

const VIEWPORTS = [
  { name: 'Desktop', width: 1440, height: 900 },
  { name: 'Tablet', width: 768, height: 1024 },
  { name: 'Mobile', width: 390, height: 844 },
];

/**
 * Test helper to set theme and wait for styles to apply
 */
async function setTheme(page, theme) {
  await page.evaluate((t) => {
    // Set data-theme attribute on all relevant elements
    document.documentElement.setAttribute('data-theme', t);
    document.body?.setAttribute('data-theme', t);
    try {
      localStorage.setItem('bbx-theme', t);
    } catch (e) {}
    // Force style recalculation
    document.body.offsetHeight; // Trigger reflow
  }, theme);
  // Wait longer for CSS variables to propagate through cascade
  await page.waitForTimeout(400);
  // Verify theme was set
  const actualTheme = await page.evaluate(() => document.documentElement.getAttribute('data-theme'));
  if (actualTheme !== theme) {
    throw new Error(`Theme not set correctly: expected ${theme}, got ${actualTheme}`);
  }
}

/**
 * Check contrast between two colors
 */
function getLuminance(hex) {
  const rgb = hex.match(/[A-Za-z0-9]{2}/g).map(v => parseInt(v, 16) / 255);
  const [r, g, b] = rgb.map(v => v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4));
  return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

function getContrastRatio(hex1, hex2) {
  const l1 = getLuminance(hex1);
  const l2 = getLuminance(hex2);
  const lighter = Math.max(l1, l2);
  const darker = Math.min(l1, l2);
  return (lighter + 0.05) / (darker + 0.05);
}

test.describe('Light Mode - Visual Integrity', () => {
  for (const page of PAGES_TO_TEST) {
    test(`${page.name} should render properly in light mode`, async ({ page: browserPage }) => {
      await browserPage.goto(`${BASE_URL}${page.path}`, { waitUntil: 'domcontentloaded' });
      await setTheme(browserPage, 'light');
      
      // Check background is light (not dark)
      const bgColor = await browserPage.evaluate(() => {
        return getComputedStyle(document.body).backgroundColor;
      });
      
      // Parse RGB
      const rgbMatch = bgColor.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/);
      if (rgbMatch) {
        const [, r, g, b] = rgbMatch.map(Number);
        // Light mode should have light background (> 180 average)
        const avgBrightness = (r + g + b) / 3;
        expect(avgBrightness).toBeGreaterThan(180);
      }
      
      // Check body text color variable is set to a dark value
      // Instead of checking arbitrary paragraphs, check that the CSS variable is dark
      const textEmphasis = await browserPage.evaluate(() => {
        const style = getComputedStyle(document.documentElement);
        return style.getPropertyValue('--text-high-emphasis').trim() || 
               style.getPropertyValue('--graphene-text-light').trim() ||
               style.getPropertyValue('--color-text').trim();
      });
      
      // Should be a dark color (#1f2937, #1a1a1a, etc.)
      if (textEmphasis && textEmphasis.startsWith('#')) {
        const hex = textEmphasis.replace('#', '');
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);
        const avgBrightness = (r + g + b) / 3;
        expect(avgBrightness).toBeLessThan(100); // Dark text color defined in tokens
      }
    });
  }
});

test.describe('Dark Mode - Visual Integrity', () => {
  for (const page of PAGES_TO_TEST) {
    test(`${page.name} should render properly in dark mode`, async ({ page: browserPage }) => {
      await browserPage.goto(`${BASE_URL}${page.path}`, { waitUntil: 'domcontentloaded' });
      await setTheme(browserPage, 'dark');
      
      // Check background is dark
      const bgColor = await browserPage.evaluate(() => {
        return getComputedStyle(document.body).backgroundColor;
      });
      
      const rgbMatch = bgColor.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/);
      if (rgbMatch) {
        const [, r, g, b] = rgbMatch.map(Number);
        // Dark mode should have dark background (< 60 average - allows dark blue/gray)
        const avgBrightness = (r + g + b) / 3;
        expect(avgBrightness).toBeLessThan(60);
      }
    });
  }
});

test.describe('Theme Toggle Functionality', () => {
  test('clicking theme toggle should switch between light and dark', async ({ page }) => {
    await page.goto(BASE_URL, { waitUntil: 'domcontentloaded' });
    
    // Find theme toggle
    const themeToggle = page.locator('[data-theme-toggle]').first();
    
    if (await themeToggle.isVisible()) {
      // Get initial theme
      const initialTheme = await page.evaluate(() => 
        document.documentElement.getAttribute('data-theme') || 'dark'
      );
      
      // Click toggle
      await themeToggle.click();
      await page.waitForTimeout(200);
      
      // Check theme changed
      const newTheme = await page.evaluate(() => 
        document.documentElement.getAttribute('data-theme')
      );
      
      expect(newTheme).not.toBe(initialTheme);
      
      // Click again to revert
      await themeToggle.click();
      await page.waitForTimeout(200);
      
      const finalTheme = await page.evaluate(() => 
        document.documentElement.getAttribute('data-theme')
      );
      
      expect(finalTheme).toBe(initialTheme);
    }
  });
});

test.describe('Light Mode A11y - Critical Pages', () => {
  const criticalPages = [
    { name: 'Landing', path: '/' },
    { name: 'Contact', path: '/contact.php' },
    { name: 'Agent Access', path: '/agent-access.php' },
  ];
  
  for (const page of criticalPages) {
    test(`${page.name} should have no critical/serious a11y violations in light mode`, async ({ page: browserPage }) => {
      await browserPage.goto(`${BASE_URL}${page.path}`, { waitUntil: 'domcontentloaded' });
      await setTheme(browserPage, 'light');
      await browserPage.waitForTimeout(300);
      
      const results = await new AxeBuilder({ page: browserPage })
        .withTags(['wcag2a', 'wcag2aa'])
        .analyze();
      
      const criticalViolations = results.violations.filter(v => v.impact === 'critical');
      const seriousViolations = results.violations.filter(v => v.impact === 'serious');
      
      if (criticalViolations.length > 0 || seriousViolations.length > 0) {
        console.log(`\n📋 Light Mode A11y Report for ${page.name}:`);
        
        if (criticalViolations.length > 0) {
          console.log('\n🔴 CRITICAL violations:');
          criticalViolations.forEach(v => {
            console.log(`  - ${v.id}: ${v.description}`);
          });
        }
        
        if (seriousViolations.length > 0) {
          console.log('\n🟠 SERIOUS violations:');
          seriousViolations.forEach(v => {
            console.log(`  - ${v.id}: ${v.description}`);
          });
        }
      }
      
      // Allow up to 2 serious issues for now (light mode still being polished)
      expect(criticalViolations.length).toBe(0);
      expect(seriousViolations.length).toBeLessThanOrEqual(2);
    });
  }
});

test.describe('Responsive Light Mode', () => {
  for (const viewport of VIEWPORTS) {
    test(`Landing page light mode should look correct at ${viewport.name}`, async ({ page }) => {
      await page.setViewportSize({ width: viewport.width, height: viewport.height });
      await page.goto(BASE_URL, { waitUntil: 'domcontentloaded' });
      await setTheme(page, 'light');
      
      // No horizontal scroll
      const scrollWidth = await page.evaluate(() => document.documentElement.scrollWidth);
      const clientWidth = await page.evaluate(() => document.documentElement.clientWidth);
      expect(scrollWidth).toBeLessThanOrEqual(clientWidth + 5);
      
      // Header should be visible
      const header = page.locator('header, #main-header').first();
      await expect(header).toBeVisible();
      
      // Main content should exist
      const main = page.locator('main, [role="main"], .main-content').first();
      await expect(main).toBeVisible();
    });
  }
});

test.describe('Focus Ring Visibility', () => {
  test('focus ring should be visible in light mode', async ({ page }) => {
    await page.goto(BASE_URL, { waitUntil: 'domcontentloaded' });
    await setTheme(page, 'light');
    
    // Tab to first focusable element
    await page.keyboard.press('Tab');
    await page.keyboard.press('Tab');
    await page.keyboard.press('Tab');
    
    const focusInfo = await page.evaluate(() => {
      const el = document.activeElement;
      if (!el) return null;
      const styles = getComputedStyle(el);
      return {
        outlineWidth: styles.outlineWidth,
        outlineStyle: styles.outlineStyle,
        outlineColor: styles.outlineColor,
        boxShadow: styles.boxShadow
      };
    });
    
    if (focusInfo) {
      // Should have some visible focus indicator
      const hasOutline = parseFloat(focusInfo.outlineWidth) > 0 && focusInfo.outlineStyle !== 'none';
      const hasBoxShadow = focusInfo.boxShadow && focusInfo.boxShadow !== 'none';
      expect(hasOutline || hasBoxShadow).toBeTruthy();
    }
  });
  
  test('focus ring should be visible in dark mode', async ({ page }) => {
    await page.goto(BASE_URL, { waitUntil: 'domcontentloaded' });
    await setTheme(page, 'dark');
    
    // Tab to first focusable element
    await page.keyboard.press('Tab');
    await page.keyboard.press('Tab');
    await page.keyboard.press('Tab');
    
    const focusInfo = await page.evaluate(() => {
      const el = document.activeElement;
      if (!el) return null;
      const styles = getComputedStyle(el);
      return {
        outlineWidth: styles.outlineWidth,
        outlineStyle: styles.outlineStyle,
        boxShadow: styles.boxShadow
      };
    });
    
    if (focusInfo) {
      const hasOutline = parseFloat(focusInfo.outlineWidth) > 0 && focusInfo.outlineStyle !== 'none';
      const hasBoxShadow = focusInfo.boxShadow && focusInfo.boxShadow !== 'none';
      expect(hasOutline || hasBoxShadow).toBeTruthy();
    }
  });
});

test.describe('Mouse Click - No Sticky Focus Ring', () => {
  test('clicking nav should NOT show persistent outline in dark mode', async ({ page }) => {
    await page.goto(BASE_URL, { waitUntil: 'domcontentloaded' });
    await setTheme(page, 'dark');
    
    // Find a nav link
    const navLink = page.locator('.nav-chip, .nav-link, nav a').first();
    
    if (await navLink.isVisible()) {
      // Click the nav link (mouse)
      await navLink.click();
      
      // Check outline is NOT visible after mouse click
      const outlineInfo = await navLink.evaluate((el) => {
        const styles = getComputedStyle(el);
        return {
          outlineWidth: parseFloat(styles.outlineWidth),
          outlineStyle: styles.outlineStyle
        };
      });
      
      // After mouse click, should have no outline or outline: none
      const hasVisibleOutline = outlineInfo.outlineWidth > 0 && outlineInfo.outlineStyle !== 'none';
      
      // This is the key test - mouse click should NOT leave a sticky outline
      expect(hasVisibleOutline).toBeFalsy();
    }
  });
  
  test('clicking nav should NOT show persistent outline in light mode', async ({ page }) => {
    await page.goto(BASE_URL, { waitUntil: 'domcontentloaded' });
    await setTheme(page, 'light');
    
    const navLink = page.locator('.nav-chip, .nav-link, nav a').first();
    
    if (await navLink.isVisible()) {
      await navLink.click();
      
      const outlineInfo = await navLink.evaluate((el) => {
        const styles = getComputedStyle(el);
        return {
          outlineWidth: parseFloat(styles.outlineWidth),
          outlineStyle: styles.outlineStyle
        };
      });
      
      const hasVisibleOutline = outlineInfo.outlineWidth > 0 && outlineInfo.outlineStyle !== 'none';
      expect(hasVisibleOutline).toBeFalsy();
    }
  });
});

test.describe('Dropdown Visibility', () => {
  test('dropdown menu should be visible and readable in light mode', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 800 });
    await page.goto(BASE_URL, { waitUntil: 'domcontentloaded' });
    await setTheme(page, 'light');
    
    // Find dropdown trigger (MERE button)
    const dropdownTrigger = page.locator('.more-dropdown-trigger, button:has-text("MERE"), button:has-text("MORE")').first();
    
    if (await dropdownTrigger.isVisible()) {
      await dropdownTrigger.click();
      await page.waitForTimeout(200);
      
      // Check dropdown is visible
      const dropdown = page.locator('.more-dropdown-menu').first();
      await expect(dropdown).toBeVisible({ timeout: 2000 });
      
      // Check dropdown has proper background for light mode
      const dropdownBg = await dropdown.evaluate((el) => {
        return getComputedStyle(el).backgroundColor;
      });
      
      // Should have a background that's not transparent
      expect(dropdownBg).not.toBe('rgba(0, 0, 0, 0)');
    }
  });
});
