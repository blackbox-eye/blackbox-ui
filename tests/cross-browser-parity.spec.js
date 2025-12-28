/**
 * Cross-Browser Parity Tests for Landing Page
 * 
 * Validates consistent behavior across:
 * - Safari iOS, Brave, DuckDuckGo (iOS)
 * - Chrome/Chromium, Firefox (Desktop)
 * 
 * Tests critical cross-browser requirements:
 * 1. No horizontal scrollbars
 * 2. Drawer overlay never solid black
 * 3. Cookie consent deterministic
 * 4. AI Assistant (Alphabot) visible
 * 5. Glass effects have proper fallbacks
 */

// @ts-check
const { test, expect } = require('@playwright/test');

const BASE_URL = process.env.PLAYWRIGHT_TEST_BASE_URL || 'http://localhost:8000';

// Viewport configurations for cross-browser testing
const VIEWPORTS = {
  mobile: { width: 390, height: 844 },  // iPhone 14 Pro
  tablet: { width: 768, height: 1024 }, // iPad
  desktop: { width: 1440, height: 900 }
};

async function gotoHome(page) {
  await page.goto(BASE_URL, { waitUntil: 'domcontentloaded' });
  await page.waitForLoadState('networkidle', { timeout: 5000 }).catch(() => {});
  await page.locator('#main-header, header#main-header, main, body').first().waitFor({ state: 'visible', timeout: 8000 });
}

/**
 * Helper: Cookie consent handling - DEPRECATED
 * Cookie banner has been completely removed from the codebase.
 * This function is kept for backward compatibility but is now a no-op.
 */
async function ensureCookieConsentHandled(page) {
  // Cookie banner no longer exists - this is a no-op for backward compatibility
  return;
}

test.describe('Cross-Browser Parity - Landing Page', () => {
  
  test.beforeEach(async ({ page, context }) => {
    // Clear cookies for consistent state
    await context.clearCookies();
    await gotoHome(page);
  });

  test.describe('Horizontal Overflow Prevention', () => {
    
    for (const [name, viewport] of Object.entries(VIEWPORTS)) {
      test(`no horizontal scrollbar on ${name} viewport`, async ({ page }) => {
        await page.setViewportSize(viewport);
        await gotoHome(page);
        
        // Check document scroll dimensions
        const hasHorizontalScroll = await page.evaluate(() => {
          return document.documentElement.scrollWidth > document.documentElement.clientWidth;
        });
        
        expect(hasHorizontalScroll).toBe(false);
      });
    }

    test('body and html have overflow-x: hidden', async ({ page }) => {
      const htmlOverflow = await page.evaluate(() => {
        return window.getComputedStyle(document.documentElement).overflowX;
      });
      const bodyOverflow = await page.evaluate(() => {
        return window.getComputedStyle(document.body).overflowX;
      });
      
      expect(htmlOverflow).toBe('hidden');
      expect(bodyOverflow).toBe('hidden');
    });
  });

  test.describe('Drawer Overlay Transparency', () => {
    
    test('drawer overlay never shows solid black', async ({ page }) => {
      await page.setViewportSize(VIEWPORTS.mobile);
      await ensureCookieConsentHandled(page);
      
      // Wait for drawer toggle to be visible
      const menuToggle = page.locator('.header-burger, [aria-label*="menu"], [aria-controls="mobile-menu"]').first();
      await expect(menuToggle).toBeVisible({ timeout: 5000 });
      
      // Open the mobile menu
      await menuToggle.click();
      await page.waitForTimeout(400); // Wait for animation
      
      // Check overlay background color
      const overlay = page.locator('#mobile-menu-overlay, .bbx-drawer-overlay').first();
      
      if (await overlay.isVisible()) {
        const bgColor = await overlay.evaluate((el) => {
          return window.getComputedStyle(el).backgroundColor;
        });
        
        // Parse rgba values - must never be solid opaque black (0,0,0,1)
        const rgbaMatch = bgColor.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
        
        if (rgbaMatch) {
          const [, r, g, b, a = '1'] = rgbaMatch;
          const alpha = parseFloat(a);
          const isPureBlack = parseInt(r) === 0 && parseInt(g) === 0 && parseInt(b) === 0;
          
          // Solid opaque black is forbidden - transparent black OR non-black are OK
          const isSolidOpaqueBlack = isPureBlack && alpha >= 1;
          expect(isSolidOpaqueBlack).toBe(false);
        }
      }
    });

    test('drawer overlay has backdrop-filter or fallback', async ({ page }) => {
      await page.setViewportSize(VIEWPORTS.mobile);
      await ensureCookieConsentHandled(page);
      
      const menuToggle = page.locator('.header-burger, [aria-label*="menu"]').first();
      if (await menuToggle.isVisible()) {
        await menuToggle.click();
        await page.waitForTimeout(400);
        
        const overlay = page.locator('#mobile-menu-overlay, .bbx-drawer-overlay').first();
        
        if (await overlay.isVisible()) {
          const styles = await overlay.evaluate((el) => {
            const computed = window.getComputedStyle(el);
            return {
              backdropFilter: computed.backdropFilter || computed.webkitBackdropFilter,
              background: computed.backgroundColor
            };
          });
          
          // Either has backdrop-filter OR has semi-transparent background as fallback
          const hasBlur = styles.backdropFilter && styles.backdropFilter !== 'none';
          const hasTransparentBg = styles.background && styles.background.includes('rgba');
          
          expect(hasBlur || hasTransparentBg).toBe(true);
        }
      }
    });

    test('drawer uses viewport height with contained scroll', async ({ page }) => {
      await page.setViewportSize(VIEWPORTS.mobile);
      await ensureCookieConsentHandled(page);

      const menuToggle = page.locator('.header-burger, [aria-label*="menu"], [aria-controls="mobile-menu"]').first();
      await expect(menuToggle).toBeVisible({ timeout: 5000 });
      await menuToggle.click();
      await page.waitForTimeout(400);

      const drawer = page.locator('#mobile-menu').first();
      await expect(drawer).toBeVisible();

      const drawerBox = await drawer.boundingBox();
      const viewport = page.viewportSize();

      if (drawerBox && viewport) {
        expect(drawerBox.height).toBeGreaterThanOrEqual(viewport.height - 80);
      }

      // Drawer should handle overflow properly - hidden, auto, scroll, or overlay
      const overflowY = await drawer.evaluate((el) => window.getComputedStyle(el).overflowY);
      expect(['auto', 'scroll', 'overlay', 'hidden']).toContain(overflowY);
    });
  });

  test.describe('Cookie Banner Complete Removal Verification', () => {
    
    test('cookie banner should NOT exist in DOM', async ({ page, context }) => {
      // Ensure clean state
      await context.clearCookies();
      await page.evaluate(() => localStorage.clear());
      
      await gotoHome(page);
      await page.waitForTimeout(1000);  // Wait past the old 1s show delay
      
      // Cookie banner should NOT exist
      const cookieBannerState = await page.evaluate(() => ({
        hasCookieBannerId: document.querySelector('#cookie-banner') !== null,
        hasCookieBannerClass: document.querySelector('.cookie-banner') !== null,
        hasCookieBannerComponent: document.querySelector('[data-component="cookie-banner"]') !== null,
        hasCookieAcceptBtn: document.querySelector('#cookie-accept-btn') !== null,
        hasCookieDeclineBtn: document.querySelector('#cookie-decline-btn') !== null,
        hasCookieBannerOpenClass: document.body.classList.contains('cookie-banner-open'),
      }));
      
      expect(cookieBannerState.hasCookieBannerId).toBe(false);
      expect(cookieBannerState.hasCookieBannerClass).toBe(false);
      expect(cookieBannerState.hasCookieBannerComponent).toBe(false);
      expect(cookieBannerState.hasCookieAcceptBtn).toBe(false);
      expect(cookieBannerState.hasCookieDeclineBtn).toBe(false);
      expect(cookieBannerState.hasCookieBannerOpenClass).toBe(false);
    });

    test('scroll should work immediately without cookie banner interference', async ({ page, context }) => {
      await context.clearCookies();
      await page.evaluate(() => localStorage.clear());
      
      await gotoHome(page);
      
      // Scroll should work immediately (no cookie banner blocking first swipe)
      const scrollBefore = await page.evaluate(() => window.scrollY);
      await page.evaluate(() => window.scrollTo(0, 300));
      await page.waitForTimeout(100);
      const scrollAfter = await page.evaluate(() => window.scrollY);
      
      expect(scrollAfter).toBeGreaterThan(scrollBefore);
    });
  });

  test.describe('AI Assistant (Alphabot) Visibility', () => {
    
    test('alphabot widget is rendered on landing', async ({ page }) => {
      // Wait for page to fully load
      await page.waitForLoadState('networkidle');
      await ensureCookieConsentHandled(page);
      await page.waitForTimeout(500);
      
      const alphabotWidget = page.locator('.alphabot-widget, .alphabot-container, [class*="alphabot"]').first();
      
      // Check if alphabot element exists in DOM
      const exists = await alphabotWidget.count() > 0;
      expect(exists).toBe(true);

      // Should not be forcibly hidden
      if (exists) {
        await expect(alphabotWidget).toBeVisible();
      }
    });

    test('alphabot toggle is clickable', async ({ page }) => {
      await page.waitForLoadState('networkidle');
      await ensureCookieConsentHandled(page);
      
      const alphabotToggle = page.locator('.alphabot-toggle').first();
      
      if (await alphabotToggle.isVisible()) {
        // Should be clickable
        await expect(alphabotToggle).toBeEnabled();
        
        // Click to open panel
        await alphabotToggle.click();
        await page.waitForTimeout(300);
        
        // Panel should appear
        const panel = page.locator('.alphabot-panel');
        await expect(panel).toBeVisible({ timeout: 2000 });
      }
    });

    test('alphabot panel layout is not collapsed', async ({ page }) => {
      await page.waitForLoadState('networkidle');
      await ensureCookieConsentHandled(page);
      
      const alphabotToggle = page.locator('.alphabot-toggle').first();

      if (await alphabotToggle.isVisible()) {
        await alphabotToggle.click();
        const panel = page.locator('.alphabot-panel').first();
        await expect(panel).toBeVisible({ timeout: 2000 });

        const box = await panel.boundingBox();
        expect(box?.height || 0).toBeGreaterThan(200);
        expect(box?.width || 0).toBeGreaterThan(200);
      }
    });

    test('alphabot overlay uses blur or transparency', async ({ page }) => {
      await page.waitForLoadState('networkidle');
      await ensureCookieConsentHandled(page);
      
      const alphabotToggle = page.locator('.alphabot-toggle').first();

      if (await alphabotToggle.isVisible()) {
        await alphabotToggle.click();
        const overlay = page.locator('.alphabot-overlay, #alphabot-overlay').first();
        await expect(overlay).toBeVisible({ timeout: 2000 });

        const styles = await overlay.evaluate((el) => {
          const computed = window.getComputedStyle(el);
          return {
            opacity: parseFloat(computed.opacity || '1'),
            backdrop: computed.backdropFilter || computed.webkitBackdropFilter || 'none',
            background: computed.backgroundColor,
          };
        });

        const hasBlur = styles.backdrop && styles.backdrop !== 'none';
        const hasTransparency = styles.background.includes('rgba') || styles.opacity < 1;
        expect(hasBlur || hasTransparency).toBe(true);
      }
    });
  });

  test.describe('Glass Effect Fallbacks', () => {
    
    test('header has backdrop-filter or solid fallback', async ({ page }) => {
      const header = page.locator('#main-header').first();
      
      const styles = await header.evaluate((el) => {
        const computed = window.getComputedStyle(el);
        return {
          backdropFilter: computed.backdropFilter || computed.webkitBackdropFilter,
          background: computed.backgroundColor,
          backgroundImage: computed.backgroundImage
        };
      });
      
      // Either has blur effect OR has opaque fallback background
      const hasBlur = styles.backdropFilter && styles.backdropFilter !== 'none';
      const hasBackground = styles.background && (
        styles.background.includes('rgb') || 
        styles.backgroundImage !== 'none'
      );
      
      expect(hasBlur || hasBackground).toBe(true);
    });
  });

  test.describe('Footer Spacing', () => {
    
    test('footer has no unexpected bottom margin', async ({ page }) => {
      const footer = page.locator('footer, .site-footer').first();
      
      if (await footer.isVisible()) {
        const marginBottom = await footer.evaluate((el) => {
          return window.getComputedStyle(el).marginBottom;
        });
        
        expect(marginBottom).toBe('0px');
      }
    });

    test('page scrolls to footer', async ({ page }) => {
      // This test verifies the page can scroll properly and footer is reachable
      const footer = page.locator('footer, .site-footer').first();
      
      // Scroll footer into view
      await footer.scrollIntoViewIfNeeded();
      await page.waitForTimeout(300);
      
      // Footer should be visible after scroll
      await expect(footer).toBeVisible();
    });
  });

  test.describe('Sticky CTA Z-Index Contract', () => {
    test('sticky CTA appears after ~30% scroll', async ({ page, context }) => {
      await context.clearCookies();
      await page.evaluate(() => {
        sessionStorage.removeItem('bbxStickyCtaDismissed');
        localStorage.clear();
      });

      await gotoHome(page);
      const stickyCta = page.locator('[data-component="sticky-cta"]');

      await page.evaluate(() => {
        window.scrollTo(0, window.innerHeight * 0.32);
      });

      await expect(stickyCta).toHaveAttribute('data-visible', 'true', { timeout: 1500 });
    });
    
    test('sticky CTA has proper z-index', async ({ page, context }) => {
      await context.clearCookies();
      await page.evaluate(() => localStorage.clear());
      
      await gotoHome(page);
      
      // Scroll to trigger sticky CTA
      await page.evaluate(() => window.scrollTo(0, 500));
      await page.waitForTimeout(500);
      
      const stickyCta = page.locator('.sticky-cta-bar, [data-component="sticky-cta"]').first();
      
      if (await stickyCta.isVisible()) {
        const stickyZIndex = await stickyCta.evaluate((el) => {
          return parseInt(window.getComputedStyle(el).zIndex) || 0;
        });
        
        // Sticky CTA should have a reasonable z-index (at least 60 per z-index contract)
        expect(stickyZIndex).toBeGreaterThanOrEqual(60);
      }
    });
  });
});

test.describe('Mobile-Specific Parity', () => {
  
  test.use({ viewport: VIEWPORTS.mobile });

  test('touch targets are at least 36x36', async ({ page }) => {
    await gotoHome(page);
    
    // Check key interactive elements - focus on critical CTA buttons
    const interactiveSelectors = [
      '.header-burger'
    ];
    
    for (const selector of interactiveSelectors) {
      const element = page.locator(selector).first();
      
      if (await element.isVisible()) {
        const box = await element.boundingBox();
        
        if (box) {
          // Accept 36px minimum for header controls (Apple HIG allows 44pt but we use smaller on dense UIs)
          expect(box.width).toBeGreaterThanOrEqual(36);
          expect(box.height).toBeGreaterThanOrEqual(36);
        }
      }
    }
  });

  test('no text is cut off at mobile widths', async ({ page }) => {
    await gotoHome(page);
    
    // Check hero title is not overflowing
    const heroTitle = page.locator('.graphene-hero-title, h1').first();
    
    if (await heroTitle.isVisible()) {
      const overflow = await heroTitle.evaluate((el) => {
        const computed = window.getComputedStyle(el);
        return computed.overflow !== 'visible' && el.scrollWidth > el.clientWidth;
      });
      
      expect(overflow).toBe(false);
    }
  });
});
