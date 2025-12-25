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

test.describe('Cross-Browser Parity - Landing Page', () => {
  
  test.beforeEach(async ({ page, context }) => {
    // Clear consent to ensure deterministic state
    await context.clearCookies();
    await page.goto(BASE_URL, { waitUntil: 'networkidle' });
  });

  test.describe('Horizontal Overflow Prevention', () => {
    
    for (const [name, viewport] of Object.entries(VIEWPORTS)) {
      test(`no horizontal scrollbar on ${name} viewport`, async ({ page }) => {
        await page.setViewportSize(viewport);
        await page.goto(BASE_URL, { waitUntil: 'networkidle' });
        
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
        
          // Parse rgba values - must never be solid black (0,0,0,1) and must keep transparency
        const rgbaMatch = bgColor.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
        
        if (rgbaMatch) {
          const [, r, g, b, a = '1'] = rgbaMatch;
          const alpha = parseFloat(a);
          
          // Require transparency and no pure black
          const isPureBlack = parseInt(r) === 0 && parseInt(g) === 0 && parseInt(b) === 0;
          expect(alpha).toBeLessThan(1);
          expect(isPureBlack).toBe(false);
        }
      }
    });

    test('drawer overlay has backdrop-filter or fallback', async ({ page }) => {
      await page.setViewportSize(VIEWPORTS.mobile);
      
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
  });

  test.describe('Drawer Glass + Height', () => {
    test('drawer fills viewport and scrolls internally', async ({ page }) => {
      await page.setViewportSize(VIEWPORTS.mobile);
      const menuToggle = page.locator('.header-burger, [aria-label*="menu"], [aria-controls="mobile-menu"]').first();
      await expect(menuToggle).toBeVisible({ timeout: 5000 });
      await menuToggle.click();
      await page.waitForTimeout(400);

      const drawer = page.locator('#mobile-menu').first();
      const nav = drawer.locator('nav').first();
      const viewportHeight = await page.evaluate(() => window.innerHeight);

      const drawerMetrics = await drawer.evaluate((el) => {
        const rect = el.getBoundingClientRect();
        const style = window.getComputedStyle(el);
        return {
          height: rect.height,
          top: rect.top,
          overflowY: style.overflowY,
          background: style.backgroundColor,
        };
      });

      expect(drawerMetrics.height).toBeGreaterThanOrEqual(viewportHeight - 4);
      expect(drawerMetrics.top).toBe(0);
      expect(['auto', 'scroll']).toContain(drawerMetrics.overflowY);

      const navOverflow = await nav.evaluate((el) => window.getComputedStyle(el).overflowY);
      expect(['auto', 'scroll']).toContain(navOverflow);

      const headerPosition = await drawer.locator('.mobile-drawer-header, #mobile-menu > div:first-child').first().evaluate((el) => {
        return window.getComputedStyle(el).position;
      });
      expect(headerPosition).toBe('sticky');

      const drawerBgAlpha = await drawer.evaluate((el) => {
        const bg = window.getComputedStyle(el).backgroundColor;
        const match = bg.match(/rgba?\(\d+,\s*\d+,\s*\d+(?:,\s*([\d.]+))?\)/);
        return match ? parseFloat(match[1] || '1') : 1;
      });
      expect(drawerBgAlpha).toBeLessThan(1);
    });
  });

  test.describe('Cookie Consent Determinism', () => {
    
    test('cookie banner shows on first visit', async ({ page, context }) => {
      // Ensure clean state
      await context.clearCookies();
      await page.evaluate(() => localStorage.clear());
      
      await page.goto(BASE_URL, { waitUntil: 'networkidle' });
      
      // Wait for banner to appear (1s delay + animation)
      const banner = page.locator('#cookie-banner');
      await expect(banner).toBeVisible({ timeout: 3000 });
    });

    test('cookie banner does not reappear after consent', async ({ page, context }) => {
      await context.clearCookies();
      await page.evaluate(() => localStorage.clear());
      
      await page.goto(BASE_URL, { waitUntil: 'networkidle' });
      
      // Wait for and click accept
      const acceptBtn = page.locator('#cookie-accept-btn');
      await expect(acceptBtn).toBeVisible({ timeout: 3000 });
      await acceptBtn.click();
      
      // Wait for banner to hide
      await page.waitForTimeout(500);
      
      // Reload page
      await page.reload({ waitUntil: 'networkidle' });
      await page.waitForTimeout(2000); // Wait past the 1s show delay
      
      // Banner should not be visible
      const banner = page.locator('#cookie-banner');
      const isVisible = await banner.getAttribute('data-visible');
      expect(isVisible).not.toBe('true');
    });

    test('consent persists via both localStorage and cookie', async ({ page, context }) => {
      await context.clearCookies();
      await page.evaluate(() => localStorage.clear());
      
      await page.goto(BASE_URL, { waitUntil: 'networkidle' });
      
      // Accept cookies
      const acceptBtn = page.locator('#cookie-accept-btn');
      await expect(acceptBtn).toBeVisible({ timeout: 3000 });
      await acceptBtn.click();
      await page.waitForTimeout(500);
      
      // Verify localStorage
      const localStorageConsent = await page.evaluate(() => {
        return localStorage.getItem('bbx_cookie_consent');
      });
      expect(localStorageConsent).toBeTruthy();
      expect(JSON.parse(localStorageConsent)).toHaveProperty('level', 'all');
      
      // Verify cookie
      const cookies = await context.cookies();
      const consentCookie = cookies.find(c => c.name === 'bbx_consent');
      expect(consentCookie).toBeTruthy();
    });
  });

  test.describe('AI Assistant (Alphabot) Visibility', () => {
    
    test('alphabot widget is rendered on landing', async ({ page }) => {
      const alphabotWidget = page.locator('.alphabot-widget, .alphabot-container, [class*="alphabot"]').first();
      
      // Wait for page to fully load
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(500);
      
      // Check if alphabot element exists in DOM
      const exists = await alphabotWidget.count() > 0;
      expect(exists).toBe(true);
    });

    test('alphabot toggle is clickable', async ({ page }) => {
      const alphabotToggle = page.locator('.alphabot-toggle').first();
      
      await page.waitForLoadState('networkidle');
      
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

    test('cookie banner has glass or fallback styling', async ({ page, context }) => {
      await context.clearCookies();
      await page.evaluate(() => localStorage.clear());
      
      await page.goto(BASE_URL, { waitUntil: 'networkidle' });
      
      const banner = page.locator('#cookie-banner');
      await expect(banner).toBeVisible({ timeout: 3000 });
      
      const styles = await banner.evaluate((el) => {
        const computed = window.getComputedStyle(el);
        return {
          backdropFilter: computed.backdropFilter || computed.webkitBackdropFilter,
          background: computed.backgroundColor,
          zIndex: computed.zIndex
        };
      });
      
      // Should have background
      expect(styles.background).toBeTruthy();
      
      // Z-index should be 85 (above sticky CTA at 75)
      expect(parseInt(styles.zIndex)).toBeGreaterThanOrEqual(85);
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
    
    test('sticky CTA is below cookie banner', async ({ page, context }) => {
      await context.clearCookies();
      await page.evaluate(() => localStorage.clear());
      
      await page.goto(BASE_URL, { waitUntil: 'networkidle' });
      
      // Scroll to trigger sticky CTA
      await page.evaluate(() => window.scrollTo(0, 500));
      await page.waitForTimeout(500);
      
      const stickyCta = page.locator('.sticky-cta-bar, [data-component="sticky-cta"]').first();
      const cookieBanner = page.locator('#cookie-banner');
      
      if (await stickyCta.isVisible() && await cookieBanner.isVisible()) {
        const stickyZIndex = await stickyCta.evaluate((el) => {
          return parseInt(window.getComputedStyle(el).zIndex) || 0;
        });
        const bannerZIndex = await cookieBanner.evaluate((el) => {
          return parseInt(window.getComputedStyle(el).zIndex) || 0;
        });
        
        expect(bannerZIndex).toBeGreaterThan(stickyZIndex);
      }
    });
  });
});

test.describe('Mobile-Specific Parity', () => {
  
  test.use({ viewport: VIEWPORTS.mobile });

  test('touch targets are at least 36x36', async ({ page }) => {
    await page.goto(BASE_URL, { waitUntil: 'networkidle' });
    
    // Check key interactive elements - focus on critical CTA buttons
    const interactiveSelectors = [
      '.header-burger',
      '#cookie-accept-btn',
      '#cookie-decline-btn'
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
    await page.goto(BASE_URL, { waitUntil: 'networkidle' });
    
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
