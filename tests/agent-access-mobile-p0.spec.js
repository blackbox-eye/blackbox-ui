/**
 * Agent Access Mobile Layout P0 Tests
 * 
 * Tests for mobile layout stability across devices and browsers:
 * - Desktop (1440x900)
 * - iPhone (390x844)
 * - iPad (820x1180)
 * 
 * Assertions:
 * - Quick Switch doesn't overlay hero/CTA
 * - Sticky CTA doesn't cover console cards
 * - No horizontal scroll
 * - Safe area handling
 * - Z-index stacking correctness
 */

const { test, expect } = require('@playwright/test');

const VIEWPORTS = {
  desktop: { width: 1440, height: 900 },
  iphone: { width: 390, height: 844 },
  ipad: { width: 820, height: 1180 }
};

// Test across all viewports
for (const [deviceName, viewport] of Object.entries(VIEWPORTS)) {
  test.describe(`Agent Access - ${deviceName} (${viewport.width}x${viewport.height})`, () => {
    test.beforeEach(async ({ page }) => {
      await page.setViewportSize(viewport);
      await page.goto('/agent-access.php');
      await page.waitForLoadState('domcontentloaded');
    });

    test('should load page without errors', async ({ page }) => {
      // Check main content is visible
      const mainContent = page.locator('#main-content.agent-access-page');
      await expect(mainContent).toBeVisible({ timeout: 10000 });
      
      // Check page has loaded (title contains some text)
      const title = await page.title();
      expect(title.length).toBeGreaterThan(0);
    });

    test('should not have horizontal scroll', async ({ page }) => {
      // Get page dimensions
      const scrollWidth = await page.evaluate(() => document.documentElement.scrollWidth);
      const clientWidth = await page.evaluate(() => document.documentElement.clientWidth);
      
      expect(scrollWidth).toBeLessThanOrEqual(clientWidth + 1); // +1 for rounding
    });

    if (deviceName !== 'desktop') {
      test('Quick Switch should not overlay hero section', async ({ page }) => {
        // Get bounding boxes
        const heroBox = await page.locator('.access-hero').boundingBox();
        const quickSwitchBox = await page.locator('.console-selector__quick').boundingBox();
        
        // Both should exist
        expect(heroBox).toBeTruthy();
        expect(quickSwitchBox).toBeTruthy();
        
        if (heroBox && quickSwitchBox) {
          // Quick switch should be below hero (or at least not overlapping)
          // Allow some overlap tolerance (e.g., 5px for borders/shadows)
          expect(quickSwitchBox.y).toBeGreaterThanOrEqual(heroBox.y + heroBox.height - 5);
        }
      });

      test('Quick Switch should be in normal flow (not sticky)', async ({ page }) => {
        const quickSwitch = page.locator('.console-selector__quick');
        
        // Get computed style
        const position = await quickSwitch.evaluate(el => 
          window.getComputedStyle(el).position
        );
        
        // Should be relative or static, not fixed or sticky
        expect(position).not.toBe('fixed');
        expect(position).not.toBe('sticky');
      });

      test('Console cards should not be covered by sticky CTA', async ({ page }) => {
        // Scroll to bottom to make sticky CTA visible (if implemented)
        await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
        await page.waitForTimeout(500); // Allow sticky CTA to appear
        
        // Get all console cards
        const cards = page.locator('.console-card');
        const cardCount = await cards.count();
        
        expect(cardCount).toBeGreaterThan(0);
        
        // Check if sticky CTA exists and is visible
        const stickyCTA = page.locator('#sticky-cta, .sticky-cta-bar');
        const isStickyCTAVisible = await stickyCTA.isVisible().catch(() => false);
        
        if (isStickyCTAVisible) {
          const ctaBox = await stickyCTA.boundingBox();
          
          // Check last card is not covered by sticky CTA
          const lastCard = cards.nth(cardCount - 1);
          const lastCardBox = await lastCard.boundingBox();
          
          if (ctaBox && lastCardBox) {
            // Last card should not overlap with sticky CTA
            // CTA is at bottom, so card should be above it
            expect(lastCardBox.y + lastCardBox.height).toBeLessThanOrEqual(ctaBox.y + 10);
          }
        }
      });

      test('Console cards should be clickable and not obscured', async ({ page }) => {
        // Test that each console card CTA is clickable
        const ctas = page.locator('.console-card__cta');
        const ctaCount = await ctas.count();
        
        expect(ctaCount).toBeGreaterThanOrEqual(3); // CCS, GDI, Intel24
        
        for (let i = 0; i < Math.min(ctaCount, 3); i++) {
          const cta = ctas.nth(i);
          await expect(cta).toBeVisible();
          
          // Check that element is not obscured
          const isClickable = await cta.evaluate(el => {
            const rect = el.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;
            const elementAtPoint = document.elementFromPoint(centerX, centerY);
            return el.contains(elementAtPoint);
          });
          
          expect(isClickable).toBeTruthy();
        }
      });

      test('Quick Switch dropdown should be accessible', async ({ page }) => {
        const quickSwitch = page.locator('.console-selector__quick-select');
        await expect(quickSwitch).toBeVisible();
        
        // Should have proper touch target size (min 40px height)
        const box = await quickSwitch.boundingBox();
        expect(box).toBeTruthy();
        if (box) {
          expect(box.height).toBeGreaterThanOrEqual(40);
        }
        
        // Should be clickable
        await quickSwitch.click();
        
        // Should have focus
        await expect(quickSwitch).toBeFocused();
      });

      test('should handle safe area insets properly', async ({ page }) => {
        // Check if body or main content has safe-area-inset applied
        const mainContent = page.locator('.agent-access-page');
        
        const paddingLeft = await mainContent.evaluate(el => 
          window.getComputedStyle(el).paddingLeft
        );
        const paddingRight = await mainContent.evaluate(el => 
          window.getComputedStyle(el).paddingRight
        );
        
        // Should have some padding (at least 1rem = 16px)
        expect(parseInt(paddingLeft)).toBeGreaterThanOrEqual(16);
        expect(parseInt(paddingRight)).toBeGreaterThanOrEqual(16);
      });
    }

    test('Console cards should be visible and properly styled', async ({ page }) => {
      // Wait for console cards to be visible
      const cards = page.locator('.console-card');
      await expect(cards.first()).toBeVisible({ timeout: 5000 });
      
      const count = await cards.count();
      expect(count).toBeGreaterThanOrEqual(3); // CCS, GDI, Intel24
      
      // Check each card has required elements
      for (let i = 0; i < count; i++) {
        const card = cards.nth(i);
        await expect(card.locator('.console-card__title')).toBeVisible();
        await expect(card.locator('.console-card__cta')).toBeVisible();
      }
    });

    test('should not have z-index stacking issues', async ({ page }) => {
      // Hero should be on top of selector
      const hero = page.locator('.access-hero');
      const selector = page.locator('.console-selector');
      
      if (await hero.isVisible() && await selector.isVisible()) {
        const heroZIndex = await hero.evaluate(el => 
          window.getComputedStyle(el).zIndex
        );
        const selectorZIndex = await selector.evaluate(el => 
          window.getComputedStyle(el).zIndex
        );
        
        // Hero should have higher z-index than selector
        expect(parseInt(heroZIndex) || 0).toBeGreaterThanOrEqual(parseInt(selectorZIndex) || 0);
      }
    });

    test('should take screenshot for visual verification', async ({ page, browserName }, testInfo) => {
      // Wait for page to be fully loaded
      await page.waitForLoadState('networkidle', { timeout: 10000 }).catch(() => {});
      
      // Take full page screenshot
      const screenshot = await page.screenshot({
        fullPage: true,
        animations: 'disabled'
      });
      
      // Attach with browser and device name
      await testInfo.attach(`screenshot-${browserName}-${deviceName}-${viewport.width}x${viewport.height}`, {
        body: screenshot,
        contentType: 'image/png'
      });
    });
  });
}

// Cross-browser specific tests (only on desktop to save time)
test.describe('Agent Access - Cross-browser parity', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.desktop);
    await page.goto('/agent-access.php');
    await page.waitForLoadState('domcontentloaded');
  });

  test('should render consistently across browsers', async ({ page, browserName }) => {
    // Basic rendering test
    const mainContent = page.locator('#main-content.agent-access-page');
    await expect(mainContent).toBeVisible();
    
    // Check console cards render
    const cards = page.locator('.console-card');
    const count = await cards.count();
    expect(count).toBeGreaterThanOrEqual(3);
    
    // Browser-specific notes
    console.log(`Testing on: ${browserName}`);
  });
});
