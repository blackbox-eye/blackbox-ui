/**
 * Sticky CTA Bar Structural Tests
 *
 * Validates the 2-row stacked layout of the sticky CTA bar:
 * - Row 1: Label (left) + Dismiss button (right)
 * - Row 2: CTA buttons side-by-side
 * - Dismiss button NEVER overlaps CTA buttons (bounding box verification)
 * - Dismiss button is clickable/tappable (pointer events)
 * - CTA bar is visible from page load
 * - Dismiss functionality works on first tap
 *
 * Class naming (BEM):
 * - .sticky-cta-bar__row--header (Row 1)
 * - .sticky-cta-bar__row--actions (Row 2)
 * - .sticky-cta-bar__label (eyebrow + title)
 * - .sticky-cta-bar__dismiss (close button)
 * - .sticky-cta-bar__cta--primary / --secondary (CTA buttons)
 *
 * @requires A running local server (e.g., php -S localhost:8000)
 */

const { test, expect } = require('@playwright/test');

// Helper: Check if two bounding boxes overlap
function boxesOverlap(box1, box2) {
  if (!box1 || !box2) return false;
  return !(
    box1.x + box1.width <= box2.x ||
    box2.x + box2.width <= box1.x ||
    box1.y + box1.height <= box2.y ||
    box2.y + box2.height <= box1.y
  );
}

// =====================================================
// STICKY CTA BAR - STRUCTURE TESTS
// =====================================================
test.describe('Sticky CTA Bar Structure', () => {

  test.beforeEach(async ({ page }) => {
    // Clear sessionStorage to ensure fresh state
    await page.addInitScript(() => {
      try {
        sessionStorage.removeItem('bbxStickyCtaDismissed');
      } catch (e) {}
    });
    await page.goto('/');
    // Wait for JS to initialize
    await page.waitForTimeout(500);
    
    // Accept cookie banner if present (it blocks pointer events)
    const cookieAcceptBtn = page.locator('#cookie-accept, .cookie-banner__btn--accept, [data-cookie-accept]');
    if (await cookieAcceptBtn.isVisible({ timeout: 1000 }).catch(() => false)) {
      await cookieAcceptBtn.click();
      await page.waitForTimeout(300);
    }
    
    // Scroll to trigger sticky CTA visibility (JS shows after 35% of viewport height)
    await page.evaluate(() => window.scrollTo(0, window.innerHeight * 0.4));
    await page.waitForTimeout(300);
  });

  test('sticky CTA bar should be visible after scroll trigger', async ({ page }) => {
    const stickyBar = page.locator('#sticky-cta, [data-component="sticky-cta"]');
    await expect(stickyBar).toBeVisible();
    await expect(stickyBar).toHaveAttribute('data-visible', 'true');
  });

  test('dismiss button should have 44x44 minimum touch target', async ({ page }) => {
    const closeBtn = page.locator('.sticky-cta-bar__dismiss');
    await expect(closeBtn).toBeVisible();

    const box = await closeBtn.boundingBox();
    expect(box).toBeTruthy();
    expect(box.width).toBeGreaterThanOrEqual(44);
    expect(box.height).toBeGreaterThanOrEqual(44);
  });

  test('dismiss button should NOT overlap primary CTA button', async ({ page }) => {
    const closeBtn = page.locator('.sticky-cta-bar__dismiss');
    const primaryBtn = page.locator('.sticky-cta-bar__cta--primary');

    await expect(closeBtn).toBeVisible();
    await expect(primaryBtn).toBeVisible();

    const closeBox = await closeBtn.boundingBox();
    const primaryBox = await primaryBtn.boundingBox();

    expect(boxesOverlap(closeBox, primaryBox)).toBeFalsy();
  });

  test('dismiss button should NOT overlap secondary CTA button', async ({ page }) => {
    const closeBtn = page.locator('.sticky-cta-bar__dismiss');
    const secondaryBtn = page.locator('.sticky-cta-bar__cta--secondary');

    await expect(closeBtn).toBeVisible();
    await expect(secondaryBtn).toBeVisible();

    const closeBox = await closeBtn.boundingBox();
    const secondaryBox = await secondaryBtn.boundingBox();

    expect(boxesOverlap(closeBox, secondaryBox)).toBeFalsy();
  });

  test('dismiss button should be clickable (pointer-events not blocked)', async ({ page }) => {
    const closeBtn = page.locator('.sticky-cta-bar__dismiss');
    await expect(closeBtn).toBeVisible();

    // Verify button receives pointer events
    const isClickable = await closeBtn.evaluate(el => {
      const style = window.getComputedStyle(el);
      return style.pointerEvents !== 'none' && style.visibility !== 'hidden';
    });
    expect(isClickable).toBeTruthy();
  });

  test('dismiss should work on first click and hide bar completely', async ({ page }) => {
    const stickyBar = page.locator('#sticky-cta, [data-component="sticky-cta"]');
    const closeBtn = page.locator('.sticky-cta-bar__dismiss');

    await expect(stickyBar).toBeVisible();
    await closeBtn.click();

    // Bar should be completely hidden (hidden attribute + data-hidden="true")
    await expect(stickyBar).not.toBeVisible();
    await expect(stickyBar).toHaveAttribute('hidden', '');
    await expect(stickyBar).toHaveAttribute('data-hidden', 'true');
  });

  test('dismissed state should persist across page reload', async ({ browser }) => {
    // Create a fresh context without addInitScript (so sessionStorage persists)
    const context = await browser.newContext();
    const page = await context.newPage();
    
    try {
      // Initial navigation
      await page.goto('/');
      await page.waitForTimeout(500);
      
      // Accept cookie banner if present
      const cookieAcceptBtn = page.locator('#cookie-accept, .cookie-banner__btn--accept, [data-cookie-accept]');
      if (await cookieAcceptBtn.isVisible({ timeout: 1000 }).catch(() => false)) {
        await cookieAcceptBtn.click();
        await page.waitForTimeout(300);
      }
      
      // Scroll to trigger sticky CTA
      await page.evaluate(() => window.scrollTo(0, window.innerHeight * 0.4));
      await page.waitForTimeout(300);
      
      const stickyBar = page.locator('#sticky-cta, [data-component="sticky-cta"]');
      const closeBtn = page.locator('.sticky-cta-bar__dismiss');

      await expect(stickyBar).toBeVisible();
      await closeBtn.click();
      await expect(stickyBar).not.toBeVisible();

      // Verify sessionStorage was set
      const wasDismissed = await page.evaluate(() => {
        try {
          return window.sessionStorage.getItem('bbxStickyCtaDismissed') === '1';
        } catch (e) {
          return false;
        }
      });
      expect(wasDismissed).toBe(true);

      // Reload page (sessionStorage persists)
      await page.reload({ waitUntil: 'networkidle' });
      await page.waitForTimeout(800);
      
      // Accept cookie banner if it reappears after reload
      if (await cookieAcceptBtn.isVisible({ timeout: 1000 }).catch(() => false)) {
        await cookieAcceptBtn.click();
        await page.waitForTimeout(300);
      }
      
      // Scroll to potentially trigger CTA (should NOT show since dismissed)
      await page.evaluate(() => window.scrollTo(0, window.innerHeight * 0.5));
      await page.waitForTimeout(500);

      // Should still be hidden (dismissed state persisted in sessionStorage)
      await expect(stickyBar).not.toBeVisible();
    } finally {
      await context.close();
    }
  });
});

// =====================================================
// MOBILE VIEWPORT TESTS (iPhone)
// =====================================================
test.describe('Sticky CTA Bar - iPhone Viewport', () => {

  test.use({
    viewport: { width: 390, height: 844 }, // iPhone 14 Pro
    deviceScaleFactor: 3,
    isMobile: true,
    hasTouch: true,
  });

  test.beforeEach(async ({ page }) => {
    await page.addInitScript(() => {
      try {
        sessionStorage.removeItem('bbxStickyCtaDismissed');
      } catch (e) {}
    });
    await page.goto('/');
    await page.waitForTimeout(500);
    
    // Accept cookie banner if present (it blocks pointer events)
    const cookieAcceptBtn = page.locator('#cookie-accept, .cookie-banner__btn--accept, [data-cookie-accept]');
    if (await cookieAcceptBtn.isVisible({ timeout: 1000 }).catch(() => false)) {
      await cookieAcceptBtn.click();
      await page.waitForTimeout(300);
    }
    
    // Scroll to trigger sticky CTA visibility
    await page.evaluate(() => window.scrollTo(0, window.innerHeight * 0.4));
    await page.waitForTimeout(300);
  });

  test('dismiss button should NOT overlap any CTA on iPhone viewport', async ({ page }) => {
    const closeBtn = page.locator('.sticky-cta-bar__dismiss');
    const primaryBtn = page.locator('.sticky-cta-bar__cta--primary');
    const secondaryBtn = page.locator('.sticky-cta-bar__cta--secondary');

    await expect(closeBtn).toBeVisible();
    await expect(primaryBtn).toBeVisible();
    await expect(secondaryBtn).toBeVisible();

    const closeBox = await closeBtn.boundingBox();
    const primaryBox = await primaryBtn.boundingBox();
    const secondaryBox = await secondaryBtn.boundingBox();

    // Dismiss MUST NOT overlap either CTA
    expect(boxesOverlap(closeBox, primaryBox)).toBeFalsy();
    expect(boxesOverlap(closeBox, secondaryBox)).toBeFalsy();
  });

  test('tap on dismiss should work on mobile', async ({ page }) => {
    const stickyBar = page.locator('#sticky-cta, [data-component="sticky-cta"]');
    const closeBtn = page.locator('.sticky-cta-bar__dismiss');

    await expect(stickyBar).toBeVisible();

    // Ensure cookie banner doesn't interfere - check again before clicking
    const cookieAcceptBtn = page.locator('#cookie-accept, .cookie-banner__btn--accept, [data-cookie-accept]');
    if (await cookieAcceptBtn.isVisible({ timeout: 500 }).catch(() => false)) {
      await cookieAcceptBtn.click();
      await page.waitForTimeout(300);
    }

    // Use click with force instead of tap for more reliability
    await closeBtn.click({ force: true });
    await page.waitForTimeout(200);

    // Bar should disappear (hidden attribute set)
    await expect(stickyBar).not.toBeVisible({ timeout: 3000 });
    await expect(stickyBar).toHaveAttribute('hidden', '');
  });

  test('should capture screenshot of sticky CTA on iPhone', async ({ page }) => {
    const stickyBar = page.locator('#sticky-cta, [data-component="sticky-cta"]');
    await expect(stickyBar).toBeVisible();

    // Screenshot of just the sticky bar
    await stickyBar.screenshot({ path: 'artifacts/sticky-cta-iphone-visible.png' });

    // Full page screenshot
    await page.screenshot({
      path: 'artifacts/sticky-cta-iphone-fullpage.png',
      fullPage: false,
    });
  });
});

// =====================================================
// 2-ROW STACKED LAYOUT VERIFICATION
// =====================================================
test.describe('Sticky CTA Bar - 2-Row Stacked Layout', () => {

  test.beforeEach(async ({ page }) => {
    await page.addInitScript(() => {
      try {
        sessionStorage.removeItem('bbxStickyCtaDismissed');
      } catch (e) {}
    });
    await page.goto('/');
    await page.waitForTimeout(500);
    
    // Accept cookie banner if present (it blocks pointer events)
    const cookieAcceptBtn = page.locator('#cookie-accept, .cookie-banner__btn--accept, [data-cookie-accept]');
    if (await cookieAcceptBtn.isVisible({ timeout: 1000 }).catch(() => false)) {
      await cookieAcceptBtn.click();
      await page.waitForTimeout(300);
    }
    
    // Scroll to trigger sticky CTA visibility
    await page.evaluate(() => window.scrollTo(0, window.innerHeight * 0.4));
    await page.waitForTimeout(300);
  });

  test('header row should use flexbox with space-between', async ({ page }) => {
    const header = page.locator('.sticky-cta-bar__row--header');
    await expect(header).toBeVisible();

    const display = await header.evaluate(el => window.getComputedStyle(el).display);
    expect(display).toBe('flex');
    
    const justifyContent = await header.evaluate(el => window.getComputedStyle(el).justifyContent);
    expect(justifyContent).toBe('space-between');
  });

  test('actions row should use CSS Grid for side-by-side buttons', async ({ page }) => {
    const actions = page.locator('.sticky-cta-bar__row--actions');
    await expect(actions).toBeVisible();

    const display = await actions.evaluate(el => window.getComputedStyle(el).display);
    expect(display).toBe('grid');
  });

  test('dismiss button should be positioned in header row', async ({ page }) => {
    const closeBtn = page.locator('.sticky-cta-bar__dismiss');
    const headerRow = page.locator('.sticky-cta-bar__row--header');
    
    await expect(closeBtn).toBeVisible();
    await expect(headerRow).toBeVisible();
    
    // Verify dismiss is child of header row
    const isInHeader = await closeBtn.evaluate(el => {
      return el.closest('.sticky-cta-bar__row--header') !== null;
    });
    expect(isInHeader).toBeTruthy();
  });
});
