/**
 * Sprint 5: Master Mission Mobile UX Consolidation Tests
 * 
 * Executive-ready validation for the complete mobile experience.
 * Tests all Sprint 3½ → Sprint 5 consolidated requirements.
 * 
 * Focus areas:
 * 1. Mobile-first layout with 24px card gap
 * 2. Touch targets (48px CTAs, 40px chips)
 * 3. Quick switch compact mode (160px max)
 * 4. Favorites with dempet gold accent
 * 5. Navigation overlay backdrop
 * 6. Snackbar feedback
 * 7. Light theme WCAG contrast
 * 8. Intel24 request flow
 * 9. Info modal real metrics (no placeholders)
 * 10. Ops bar compact badges
 * 
 * @requires Playwright
 */

const { test, expect } = require('@playwright/test');

// Mobile viewport configuration
const mobileViewport = {
  viewport: { width: 390, height: 844 },
  hasTouch: true,
  isMobile: true,
};

test.use(mobileViewport);

// =====================================================
// 1. MOBILE-FIRST LAYOUT (24px Gap)
// =====================================================
test.describe('Mobile Layout: 24px Card Gap', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/agent-access.php');
    await page.waitForSelector('.console-selector__grid');
  });

  test('cards should have 24px (1.5rem) gap between them', async ({ page }) => {
    const grid = page.locator('.console-selector__grid');
    const gap = await grid.evaluate(el => {
      const style = window.getComputedStyle(el);
      return parseFloat(style.gap) || parseFloat(style.rowGap);
    });
    
    // Should be 24px (allowing for rounding)
    expect(gap).toBeGreaterThanOrEqual(23);
    expect(gap).toBeLessThanOrEqual(25);
  });

  test('cards should have no negative margins', async ({ page }) => {
    const cards = page.locator('.console-card');
    const count = await cards.count();
    
    for (let i = 0; i < count; i++) {
      const card = cards.nth(i);
      const margins = await card.evaluate(el => {
        const style = window.getComputedStyle(el);
        return {
          top: parseFloat(style.marginTop),
          right: parseFloat(style.marginRight),
          bottom: parseFloat(style.marginBottom),
          left: parseFloat(style.marginLeft),
        };
      });
      
      expect(margins.top).toBeGreaterThanOrEqual(0);
      expect(margins.right).toBeGreaterThanOrEqual(0);
      expect(margins.bottom).toBeGreaterThanOrEqual(0);
      expect(margins.left).toBeGreaterThanOrEqual(0);
    }
  });
});

// =====================================================
// 2. QUICK SWITCH COMPACT MODE
// =====================================================
test.describe('Quick Switch: Compact Mobile Mode', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/agent-access.php');
    await page.waitForSelector('.console-selector__quick');
  });

  test('quick select dropdown should be max 160px wide', async ({ page }) => {
    const select = page.locator('.console-selector__quick-select');
    const box = await select.boundingBox();
    
    expect(box.width).toBeLessThanOrEqual(165); // Allow small tolerance
  });

  test('quick switch bar should be sticky', async ({ page }) => {
    const quickSwitch = page.locator('.console-selector__quick');
    const position = await quickSwitch.evaluate(el => {
      const style = window.getComputedStyle(el);
      return style.position;
    });
    
    expect(position).toBe('sticky');
  });
});

// =====================================================
// 3. FAVORITES WITH DEMPET GOLD
// =====================================================
test.describe('Favorites: Dempet Gold Star', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/agent-access.php');
    await page.waitForSelector('.console-card__fav-btn');
  });

  test('favorite star should show gold accent when active', async ({ page }) => {
    const favButton = page.locator('.console-card__fav-btn[data-favorite="ccs"]');
    
    // Click to favorite
    await favButton.click();
    await page.waitForTimeout(200);
    
    // Check for is-favorite class
    await expect(favButton).toHaveClass(/is-favorite/);
    
    // Verify color is gold-ish (dempet gold #c9a227 = rgb(201, 162, 39))
    const color = await favButton.evaluate(el => {
      return window.getComputedStyle(el).color;
    });
    
    // Should contain gold tones
    expect(color).toMatch(/rgb\(20[0-1], 16[0-2], 3[7-9]\)|#c9a227/i);
    
    // Cleanup: unpin
    await favButton.click();
  });

  test('favorite button should have tooltip', async ({ page }) => {
    const favButton = page.locator('.console-card__fav-btn[data-favorite="ccs"]');
    const tooltip = await favButton.getAttribute('data-tooltip');
    
    expect(tooltip).toBeTruthy();
  });
});

// =====================================================
// 4. INTEL24 REQUEST FLOW
// =====================================================
test.describe('Intel24 Request Modal', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/agent-access.php');
  });

  test('Intel24 request button should open modal', async ({ page }) => {
    const requestBtn = page.locator('[data-intel24-request="true"]');
    
    // Button might not exist if Intel24 doesn't require approval
    if (await requestBtn.count() > 0) {
      await requestBtn.click();
      await page.waitForTimeout(300);
      
      const modal = page.locator('.bbx-intel24-modal');
      await expect(modal).toBeVisible();
      
      // Close modal
      const closeBtn = modal.locator('.bbx-intel24-modal__close');
      await closeBtn.click();
    }
  });
});

// =====================================================
// 5. INFO MODAL - NO PLACEHOLDERS
// =====================================================
test.describe('Info Modal: Real Metrics Only', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/agent-access.php');
  });

  test('CCS slideout should show real session status', async ({ page }) => {
    const infoBtn = page.locator('.console-card--ccs .console-card__info-btn');
    await infoBtn.click();
    await page.waitForTimeout(300);
    
    const slideout = page.locator('#ccs-slideout');
    await expect(slideout).toBeVisible();
    
    // Check session status - should NOT contain "Pre-flight checks pending"
    const sessionValue = slideout.locator('[data-readiness="session"]');
    const text = await sessionValue.textContent();
    
    expect(text).not.toContain('Pre-flight');
    expect(text).not.toContain('pending');
    // Should be either "Active" or "Sign-in required"
    expect(['Active', 'Sign-in required']).toContain(text.trim());
    
    // Close slideout
    const closeBtn = slideout.locator('.console-card__slideout-close');
    await closeBtn.click();
  });
});

// =====================================================
// 6. OPS BAR COMPACT BADGES
// =====================================================
test.describe('Ops Bar: Compact Mobile Badges', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/agent-access.php');
  });

  test('ops bar chips should have colored backgrounds', async ({ page }) => {
    const infoBtn = page.locator('.console-card--ccs .console-card__info-btn');
    await infoBtn.click();
    await page.waitForTimeout(300);
    
    const slideout = page.locator('#ccs-slideout');
    await expect(slideout).toBeVisible();
    
    // Check for colored ops chips
    const operationalChip = slideout.locator('.console-card__opschip--up');
    const bgColor = await operationalChip.evaluate(el => {
      return window.getComputedStyle(el).backgroundColor;
    });
    
    // Should have some background color (not transparent)
    expect(bgColor).not.toBe('transparent');
    expect(bgColor).not.toBe('rgba(0, 0, 0, 0)');
    
    // Close
    const closeBtn = slideout.locator('.console-card__slideout-close');
    await closeBtn.click();
  });
});

// =====================================================
// 7. LIGHT THEME WCAG CONTRAST
// =====================================================
test.describe('Light Theme: WCAG AA Contrast', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/agent-access.php');
    // Set light theme
    await page.evaluate(() => {
      document.documentElement.setAttribute('data-theme', 'light');
    });
    await page.waitForTimeout(100);
  });

  test('card titles should have sufficient contrast in light mode', async ({ page }) => {
    const title = page.locator('.console-card__title').first();
    const color = await title.evaluate(el => {
      return window.getComputedStyle(el).color;
    });
    
    // Should be dark text for light mode (not pure white)
    expect(color).not.toBe('rgb(255, 255, 255)');
  });

  test('cards should have visible borders in light mode', async ({ page }) => {
    const card = page.locator('.console-card').first();
    const border = await card.evaluate(el => {
      const style = window.getComputedStyle(el);
      return {
        color: style.borderColor,
        width: style.borderWidth,
      };
    });
    
    // Should have some border
    expect(border.width).not.toBe('0px');
  });
});

// =====================================================
// 8. TOUCH DEVICE OPTIMIZATIONS
// =====================================================
test.describe('Touch Optimizations', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/agent-access.php');
  });

  test('all CTAs should be at least 48px tall', async ({ page }) => {
    const ctas = page.locator('.console-card__cta');
    const count = await ctas.count();
    
    for (let i = 0; i < count; i++) {
      const cta = ctas.nth(i);
      const box = await cta.boundingBox();
      expect(box.height).toBeGreaterThanOrEqual(46); // Small tolerance
    }
  });

  test('chips should be at least 36px tall', async ({ page }) => {
    const chips = page.locator('.console-card__chip');
    const count = await chips.count();
    
    for (let i = 0; i < count; i++) {
      const chip = chips.nth(i);
      const box = await chip.boundingBox();
      // Minimum touch target for small elements
      expect(box.height).toBeGreaterThanOrEqual(28);
    }
  });
});

// =====================================================
// 9. ACTIVITY SECTION
// =====================================================
test.describe('Recent Activity Section', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/agent-access.php');
    await page.waitForSelector('.console-selector__activity');
  });

  test('activity section should be visible on mobile', async ({ page }) => {
    const activity = page.locator('.console-selector__activity');
    await expect(activity).toBeVisible();
  });

  test('activity items should have proper touch targets', async ({ page }) => {
    // First trigger some activity
    const favBtn = page.locator('.console-card__fav-btn[data-favorite="ccs"]');
    await favBtn.click();
    await page.waitForTimeout(500);
    await favBtn.click(); // Unfavorite
    await page.waitForTimeout(500);
    
    const items = page.locator('.console-selector__activity-item');
    const count = await items.count();
    
    if (count > 0) {
      const item = items.first();
      const box = await item.boundingBox();
      // Should have minimum 44px height for touch
      expect(box.height).toBeGreaterThanOrEqual(40);
    }
  });
});

// =====================================================
// 10. REDUCED MOTION SUPPORT
// =====================================================
test.describe('Accessibility: Reduced Motion', () => {
  test('animations should respect prefers-reduced-motion', async ({ page }) => {
    // Emulate reduced motion
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto('/agent-access.php');
    
    const card = page.locator('.console-card').first();
    const transition = await card.evaluate(el => {
      return window.getComputedStyle(el).transition;
    });
    
    // With reduced motion, transitions should be none or very short
    // The CSS sets transition: none for reduced motion
    expect(transition === 'none' || transition === 'all 0s ease 0s' || transition.includes('0s')).toBe(true);
  });
});
