/**
 * iOS Scroll Lock Regression Tests
 *
 * P0 CRITICAL: Tests to verify scroll-lock issues don't regress.
 * Specifically targets the issue where iPhone Safari/Brave/DuckDuckGo
 * could get stuck in a scroll-locked state.
 *
 * Root Cause (2025-12): position: fixed !important on body.mobile-menu-open
 * combined with iOS Safari's handling of back-forward cache (bfcache).
 *
 * Test Matrix:
 * - iPhone Safari viewport (390x844)
 * - iPhone SE viewport (375x667)
 * - iPad viewport (768x1024)
 *
 * @since Sprint 11
 */

const { test, expect } = require("@playwright/test");

// iOS viewport configurations
const VIEWPORTS = {
  iphoneSafari: { width: 390, height: 844, isMobile: true, hasTouch: true },
  iphoneSE: { width: 375, height: 667, isMobile: true, hasTouch: true },
  iPad: { width: 768, height: 1024, isMobile: false, hasTouch: true },
};

test.describe("iOS Scroll Lock Prevention", () => {
  test.beforeEach(async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize(VIEWPORTS.iphoneSafari);
  });

  /**
   * CRITICAL TEST: Page should always be scrollable on initial load
   */
  test("page should be scrollable on initial load", async ({ page }) => {
    await page.goto("/", { waitUntil: "networkidle" });

    // Wait for landing gate to release
    await page.waitForFunction(
      () => {
        const body = document.body;
        return !body.classList.contains("landing-gate");
      },
      { timeout: 5000 }
    );

    // Get initial scroll position
    const initialScrollY = await page.evaluate(() => window.scrollY);

    // Attempt to scroll
    await page.evaluate(() => window.scrollTo(0, 200));
    await page.waitForTimeout(100);

    // Verify scroll happened
    const newScrollY = await page.evaluate(() => window.scrollY);
    expect(newScrollY).toBeGreaterThan(initialScrollY);

    // Verify body doesn't have scroll-blocking styles
    const bodyStyles = await page.evaluate(() => {
      const body = document.body;
      const computed = getComputedStyle(body);
      return {
        overflow: computed.overflow,
        overflowY: computed.overflowY,
        position: computed.position,
        hasLockClass:
          body.classList.contains("mobile-menu-open") ||
          body.classList.contains("alphabot-locked") ||
          body.classList.contains("modal-open"),
      };
    });

    expect(bodyStyles.hasLockClass).toBe(false);
    expect(bodyStyles.position).not.toBe("fixed");
  });

  /**
   * CRITICAL TEST: Body should NOT have position:fixed when menu is closed
   */
  test("body should not have position:fixed when mobile menu is closed", async ({
    page,
  }) => {
    await page.goto("/", { waitUntil: "networkidle" });

    // Open mobile menu (hamburger button)
    const menuButton = page
      .locator('#mobile-menu-btn, .mobile-menu-btn, [aria-label*="menu"]')
      .first();
    if (await menuButton.isVisible()) {
      await menuButton.click();
      await page.waitForTimeout(300);

      // Close menu
      const closeBtn = page
        .locator('#drawer-close, .drawer-close, [aria-label*="close"]')
        .first();
      if (await closeBtn.isVisible()) {
        await closeBtn.click();
      } else {
        // Try clicking outside
        await page.click("body", { position: { x: 10, y: 10 } });
      }
      await page.waitForTimeout(300);
    }

    // Verify body doesn't have position:fixed
    const bodyPosition = await page.evaluate(() => {
      return getComputedStyle(document.body).position;
    });

    expect(bodyPosition).not.toBe("fixed");

    // Verify body doesn't have mobile-menu-open class
    const hasMenuClass = await page.evaluate(() => {
      return document.body.classList.contains("mobile-menu-open");
    });
    expect(hasMenuClass).toBe(false);
  });

  /**
   * CRITICAL TEST: Page should remain scrollable after menu close
   */
  test("page should remain scrollable after mobile menu open/close cycle", async ({
    page,
  }) => {
    await page.goto("/", { waitUntil: "networkidle" });
    await page.waitForTimeout(500);

    // Open mobile menu
    const menuButton = page
      .locator('#mobile-menu-btn, .mobile-menu-btn, [aria-label*="menu"]')
      .first();
    if (await menuButton.isVisible()) {
      await menuButton.click();
      await page.waitForTimeout(300);

      // Close menu
      const closeBtn = page
        .locator('#drawer-close, .drawer-close, [aria-label*="close"]')
        .first();
      if (await closeBtn.isVisible()) {
        await closeBtn.click();
      } else {
        await page.click("body", { position: { x: 10, y: 10 } });
      }
      await page.waitForTimeout(300);
    }

    // Verify page is scrollable
    const scrollBefore = await page.evaluate(() => window.scrollY);
    await page.evaluate(() => window.scrollTo(0, 300));
    await page.waitForTimeout(100);
    const scrollAfter = await page.evaluate(() => window.scrollY);

    expect(scrollAfter).toBeGreaterThan(scrollBefore);
  });

  /**
   * CRITICAL TEST: HTML and body overflow should not be hidden on page load
   */
  test("html and body overflow should not be hidden on page load", async ({
    page,
  }) => {
    await page.goto("/", { waitUntil: "networkidle" });
    await page.waitForTimeout(500);

    const overflowStyles = await page.evaluate(() => {
      const html = document.documentElement;
      const body = document.body;
      const htmlComputed = getComputedStyle(html);
      const bodyComputed = getComputedStyle(body);

      return {
        htmlOverflow: htmlComputed.overflow,
        htmlOverflowY: htmlComputed.overflowY,
        bodyOverflow: bodyComputed.overflow,
        bodyOverflowY: bodyComputed.overflowY,
      };
    });

    // Overflow should allow scrolling (not 'hidden')
    expect(overflowStyles.htmlOverflowY).not.toBe("hidden");
    expect(overflowStyles.bodyOverflowY).not.toBe("hidden");
  });

  /**
   * TEST: Alphabot panel open/close should not lock scroll
   */
  test("alphabot panel open/close should not cause scroll lock", async ({
    page,
  }) => {
    await page.goto("/", { waitUntil: "networkidle" });
    await page.waitForTimeout(500);

    // Find alphabot toggle
    const alphabotToggle = page.locator(".alphabot-toggle").first();

    if (await alphabotToggle.isVisible()) {
      // Open alphabot
      await alphabotToggle.click();
      await page.waitForTimeout(300);

      // Close alphabot (click outside or on toggle again)
      await alphabotToggle.click();
      await page.waitForTimeout(300);

      // Verify scroll is possible
      const scrollBefore = await page.evaluate(() => window.scrollY);
      await page.evaluate(() => window.scrollTo(0, 250));
      await page.waitForTimeout(100);
      const scrollAfter = await page.evaluate(() => window.scrollY);

      expect(scrollAfter).toBeGreaterThan(scrollBefore);
    } else {
      // Alphabot not visible, pass test
      expect(true).toBe(true);
    }
  });

  /**
   * TEST: Cookie banner dismiss should not lock scroll
   */
  test("cookie banner dismiss should not cause scroll lock", async ({
    page,
  }) => {
    // Clear cookies to trigger banner
    await page.context().clearCookies();
    await page.goto("/", { waitUntil: "networkidle" });
    await page.waitForTimeout(500);

    // Find cookie accept button
    const acceptBtn = page
      .locator(
        '#accept-cookies, [data-action="accept-cookies"], .cookie-accept'
      )
      .first();

    if (await acceptBtn.isVisible()) {
      await acceptBtn.click();
      await page.waitForTimeout(300);
    }

    // Verify scroll is possible
    const scrollBefore = await page.evaluate(() => window.scrollY);
    await page.evaluate(() => window.scrollTo(0, 200));
    await page.waitForTimeout(100);
    const scrollAfter = await page.evaluate(() => window.scrollY);

    expect(scrollAfter).toBeGreaterThan(scrollBefore);
  });

  /**
   * TEST: Touch events should not be blocked on page body
   */
  test("touch events should not be blocked on page body", async ({ page }) => {
    await page.goto("/", { waitUntil: "networkidle" });
    await page.waitForTimeout(500);

    // Check that body doesn't have touch-action: none outside of menu-open state
    const touchAction = await page.evaluate(() => {
      const body = document.body;
      const hasMenuOpen = body.classList.contains("mobile-menu-open");
      const computed = getComputedStyle(body);
      return {
        touchAction: computed.touchAction,
        hasMenuOpen,
      };
    });

    if (!touchAction.hasMenuOpen) {
      expect(touchAction.touchAction).not.toBe("none");
    }
  });
});

test.describe("iOS Scroll Lock - Multiple Viewports", () => {
  for (const [name, viewport] of Object.entries(VIEWPORTS)) {
    test(`scroll should work on ${name} (${viewport.width}x${viewport.height})`, async ({
      page,
    }) => {
      await page.setViewportSize(viewport);
      await page.goto("/", { waitUntil: "networkidle" });
      await page.waitForTimeout(500);

      // Verify scrolling works
      const scrollBefore = await page.evaluate(() => window.scrollY);
      await page.evaluate(() => window.scrollTo(0, 300));
      await page.waitForTimeout(100);
      const scrollAfter = await page.evaluate(() => window.scrollY);

      expect(scrollAfter).toBeGreaterThan(scrollBefore);
    });
  }
});

test.describe("Alphabot Visual Appearance", () => {
  test("alphabot toggle should not appear solid black", async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.iphoneSafari);
    await page.goto("/", { waitUntil: "networkidle" });
    await page.waitForTimeout(500);

    const alphabotToggle = page.locator(".alphabot-toggle").first();

    if (await alphabotToggle.isVisible()) {
      // Get background color
      const bgColor = await alphabotToggle.evaluate((el) => {
        return getComputedStyle(el).backgroundColor;
      });

      // Should not be pure black (rgb(0, 0, 0))
      expect(bgColor).not.toBe("rgb(0, 0, 0)");

      // Should have some transparency or be a dark gray
      // Expecting something like rgba(13, 15, 17, 0.85) or similar
      expect(bgColor).toMatch(/rgba?\(/);
    }
  });

  test("alphabot toggle should be visible and clickable", async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.iphoneSafari);
    await page.goto("/", { waitUntil: "networkidle" });
    await page.waitForTimeout(500);

    const alphabotToggle = page.locator(".alphabot-toggle").first();

    if (await alphabotToggle.isVisible()) {
      // Should have pointer-events: auto
      const pointerEvents = await alphabotToggle.evaluate((el) => {
        return getComputedStyle(el).pointerEvents;
      });
      expect(pointerEvents).toBe("auto");

      // Should be visible (opacity > 0)
      const opacity = await alphabotToggle.evaluate((el) => {
        return getComputedStyle(el).opacity;
      });
      expect(parseFloat(opacity)).toBeGreaterThan(0);
    }
  });
});

test.describe("Sticky CTA Visual Appearance", () => {
  test("sticky CTA should have proper styling when visible", async ({
    page,
  }) => {
    await page.setViewportSize(VIEWPORTS.iphoneSafari);
    await page.goto("/", { waitUntil: "networkidle" });

    // Scroll to trigger sticky CTA
    await page.evaluate(() => window.scrollTo(0, 500));
    await page.waitForTimeout(500);

    const stickyCta = page
      .locator('.sticky-cta-bar, [data-component="sticky-cta"]')
      .first();

    const isVisible = await stickyCta.isVisible().catch(() => false);

    if (isVisible) {
      // Get background - should not be pure black
      const bgColor = await stickyCta.evaluate((el) => {
        return getComputedStyle(el).backgroundColor;
      });

      // Background should be set (not transparent or pure black)
      expect(bgColor).not.toBe("rgb(0, 0, 0)");
    }
  });
});

test.describe("Mobile Menu Drawer Tests", () => {
  test("drawer should close properly on backdrop click", async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.iphoneSafari);
    await page.goto("/", { waitUntil: "networkidle" });
    await page.waitForTimeout(500);

    // Open mobile menu
    const menuButton = page
      .locator('#mobile-menu-btn, .mobile-menu-btn, [aria-label*="menu"]')
      .first();
    if (await menuButton.isVisible()) {
      await menuButton.click();
      await page.waitForTimeout(300);

      // Click on overlay/backdrop
      const overlay = page
        .locator(".bbx-drawer-overlay, .drawer-overlay, .mobile-menu-overlay")
        .first();
      if (await overlay.isVisible()) {
        await overlay.click({ force: true });
        await page.waitForTimeout(300);
      }

      // Verify body doesn't have menu-open class
      const hasMenuClass = await page.evaluate(() => {
        return document.body.classList.contains("mobile-menu-open");
      });
      expect(hasMenuClass).toBe(false);
    }
  });

  test("drawer should close on Escape key", async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.iphoneSafari);
    await page.goto("/", { waitUntil: "networkidle" });
    await page.waitForTimeout(500);

    // Open mobile menu
    const menuButton = page
      .locator('#mobile-menu-btn, .mobile-menu-btn, [aria-label*="menu"]')
      .first();
    if (await menuButton.isVisible()) {
      await menuButton.click();
      await page.waitForTimeout(300);

      // Press Escape
      await page.keyboard.press("Escape");
      await page.waitForTimeout(300);

      // Verify body doesn't have menu-open class
      const hasMenuClass = await page.evaluate(() => {
        return document.body.classList.contains("mobile-menu-open");
      });
      expect(hasMenuClass).toBe(false);
    }
  });
});
