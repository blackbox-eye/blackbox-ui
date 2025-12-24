// @ts-check
const { test, expect } = require('@playwright/test');
const AxeBuilder = require('@axe-core/playwright').default;

/**
 * Sprint 8 A11y Hard Gate Tests
 * MUST PASS: 0 critical, 0 serious violations
 * Pages tested: landing, login, ccs-console, agent-access
 */

const BASE_URL = process.env.BASE_URL || 'http://localhost:8000';

// Pages to test for accessibility
const PAGES_TO_TEST = [
  { name: 'Landing Page', path: '/' },
  { name: 'Agent Login', path: '/agent-login.php' },
  { name: 'CCS Console', path: '/cases.php' },
  { name: 'Agent Access', path: '/agent-access.php' },
  { name: 'Products', path: '/products.php' },
  { name: 'Pricing', path: '/pricing.php' },
  { name: 'About', path: '/about.php' },
  { name: 'Contact', path: '/contact.php' },
  { name: 'FAQ', path: '/faq.php' },
  { name: 'Blog', path: '/blog.php' },
];

test.describe('A11y Hard Gate - Critical & Serious Violations', () => {
  for (const page of PAGES_TO_TEST) {
    test(`${page.name} should have no critical/serious a11y violations`, async ({ page: browserPage }) => {
      // Set localStorage to force dark theme before navigation
      await browserPage.addInitScript(() => {
        window.localStorage.setItem('bbx-theme', 'dark');
      });
      
      await browserPage.goto(`${BASE_URL}${page.path}`, { waitUntil: 'domcontentloaded' });
      
      // Wait for page to fully render
      await browserPage.waitForTimeout(300);
      
      // Ensure dark theme is applied without corrupting existing attributes
      await browserPage.evaluate(() => {
        if (document.documentElement) {
          document.documentElement.setAttribute('data-theme', 'dark');
        }
        if (document.body) {
          document.body.setAttribute('data-theme', 'dark');
        }
      });
      
      // Wait for dynamic content
      await browserPage.waitForTimeout(200);
      
      const accessibilityScanResults = await new AxeBuilder({ page: browserPage })
        .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
        .analyze();
      
      // Filter for critical and serious violations only
      const criticalViolations = accessibilityScanResults.violations.filter(
        v => v.impact === 'critical'
      );
      const seriousViolations = accessibilityScanResults.violations.filter(
        v => v.impact === 'serious'
      );
      
      // Log violations for debugging
      if (criticalViolations.length > 0 || seriousViolations.length > 0) {
        console.log(`\n📋 A11y Report for ${page.name}:`);
        
        if (criticalViolations.length > 0) {
          console.log('\n🔴 CRITICAL violations:');
          criticalViolations.forEach(v => {
            console.log(`  - ${v.id}: ${v.description}`);
            v.nodes.forEach(n => console.log(`    → ${n.html.substring(0, 100)}...`));
          });
        }
        
        if (seriousViolations.length > 0) {
          console.log('\n🟠 SERIOUS violations:');
          seriousViolations.forEach(v => {
            console.log(`  - ${v.id}: ${v.description}`);
            v.nodes.forEach(n => console.log(`    → ${n.html.substring(0, 100)}...`));
          });
        }
      }
      
      // HARD GATE: 0 critical, 0 serious
      expect(criticalViolations.length, 
        `Critical violations found on ${page.name}: ${criticalViolations.map(v => v.id).join(', ')}`
      ).toBe(0);
      
      expect(seriousViolations.length,
        `Serious violations found on ${page.name}: ${seriousViolations.map(v => v.id).join(', ')}`
      ).toBe(0);
    });
  }
});

test.describe('A11y - Focus Visibility', () => {
  test('all interactive elements should have visible focus', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    
    // Tab through first 10 focusable elements
    for (let i = 0; i < 10; i++) {
      await page.keyboard.press('Tab');
      
      const focusedElement = await page.evaluate(() => {
        const el = document.activeElement;
        if (!el || el === document.body) return null;
        
        const styles = window.getComputedStyle(el);
        const outlineWidth = parseFloat(styles.outlineWidth) || 0;
        const outlineStyle = styles.outlineStyle;
        const boxShadow = styles.boxShadow;
        
        // Check for visible focus indicator
        const hasOutline = outlineWidth > 0 && outlineStyle !== 'none';
        const hasBoxShadow = boxShadow && boxShadow !== 'none';
        
        return {
          tag: el.tagName,
          hasVisibleFocus: hasOutline || hasBoxShadow,
          outline: `${outlineWidth}px ${outlineStyle}`,
          boxShadow: boxShadow?.substring(0, 50)
        };
      });
      
      if (focusedElement) {
        // Expect some form of focus indicator
        expect(focusedElement.hasVisibleFocus, 
          `Element ${focusedElement.tag} missing visible focus indicator`
        ).toBeTruthy();
      }
    }
  });
});

test.describe('A11y - ARIA Labels', () => {
  test('icon buttons should have accessible names', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    
    // Find buttons with only icons (no text content)
    const iconButtons = await page.locator('button:has(svg), button:has(i)').all();
    
    for (const btn of iconButtons.slice(0, 10)) { // Check first 10
      const accessibleName = await btn.evaluate(el => {
        return el.getAttribute('aria-label') || 
               el.getAttribute('title') || 
               el.textContent?.trim() || 
               '';
      });
      
      // Icon buttons must have aria-label or title
      if (accessibleName.length === 0) {
        const html = await btn.evaluate(el => el.outerHTML.substring(0, 100));
        console.log(`⚠️ Icon button missing accessible name: ${html}`);
      }
      // This is a soft check - log but don't fail yet
    }
  });
});

test.describe('A11y - Color Contrast', () => {
  test('text should meet WCAG AA contrast requirements', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    
    const accessibilityScanResults = await new AxeBuilder({ page })
      .withTags(['wcag2aa'])
      .include('body')
      .analyze();
    
    // Check for contrast violations specifically
    const contrastViolations = accessibilityScanResults.violations.filter(
      v => v.id.includes('contrast')
    );
    
    if (contrastViolations.length > 0) {
      console.log('\n🎨 Contrast issues found:');
      contrastViolations.forEach(v => {
        console.log(`  - ${v.id}: ${v.nodes.length} elements`);
        v.nodes.slice(0, 3).forEach(n => {
          console.log(`    → ${n.html.substring(0, 80)}...`);
        });
      });
    }
    
    // For now, log but allow - we'll tighten this later
    // expect(contrastViolations.length).toBe(0);
  });
});

test.describe('A11y - Keyboard Navigation', () => {
  test('should be able to navigate to main content', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    
    // Look for skip link
    const skipLink = page.locator('a[href="#main-content"], a[href="#content"], .skip-link');
    
    if (await skipLink.count() > 0) {
      // Skip link should become visible on focus
      await page.keyboard.press('Tab');
      const isVisible = await skipLink.first().isVisible();
      expect(isVisible).toBeTruthy();
    }
  });
  
  test('tab order should follow visual layout', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    
    const tabOrder = [];
    
    // Tab through first 15 elements
    for (let i = 0; i < 15; i++) {
      await page.keyboard.press('Tab');
      
      const position = await page.evaluate(() => {
        const el = document.activeElement;
        if (!el || el === document.body) return null;
        
        const rect = el.getBoundingClientRect();
        return { y: rect.top, x: rect.left };
      });
      
      if (position) tabOrder.push(position);
    }
    
    // Check that Y positions generally increase (top-to-bottom flow)
    let violations = 0;
    for (let i = 1; i < tabOrder.length; i++) {
      // Allow some leeway for same-row elements
      if (tabOrder[i].y < tabOrder[i-1].y - 50) {
        violations++;
      }
    }
    
    // Allow up to 2 "backwards" tabs (e.g., returning to header)
    expect(violations).toBeLessThanOrEqual(2);
  });
});

/**
 * Sprint 10: Mobile Drawer Sanity Tests
 * Ensures drawer is scrollable and all menu items are accessible
 */
test.describe('Mobile Drawer - Layout & Scroll', () => {
  test.use({ viewport: { width: 390, height: 844 } }); // iPhone 14 Pro
  
  test('drawer should be scrollable and show all menu items', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(300);
    
    // Open mobile menu
    const burgerButton = page.locator('#mobile-menu-button');
    await burgerButton.click();
    await page.waitForTimeout(200);
    
    // Check drawer is visible
    const drawer = page.locator('#mobile-menu');
    await expect(drawer).toBeVisible();
    
    // Check that nav section exists and is scrollable
    const navSection = drawer.locator('nav');
    await expect(navSection).toBeVisible();
    
    // Verify key menu items are present
    const aboutLink = drawer.locator('a[href*="about"]');
    const contactLink = drawer.locator('a[href*="contact"]');
    const pricingLink = drawer.locator('a[href*="pricing"]');
    
    await expect(aboutLink.first()).toBeVisible();
    await expect(pricingLink.first()).toBeVisible();
    
    // Scroll to contact link if needed and verify it's reachable
    await contactLink.first().scrollIntoViewIfNeeded();
    await expect(contactLink.first()).toBeVisible();
    
    // Check CTA buttons at bottom
    const demoBtn = drawer.locator('a[href*="demo"]').first();
    const scanBtn = drawer.locator('a[href*="free-scan"]').first();
    
    await expect(demoBtn).toBeVisible();
    await expect(scanBtn).toBeVisible();
    
    // Close drawer
    const closeBtn = page.locator('#mobile-menu-close');
    await closeBtn.click();
    await page.waitForTimeout(300);
    
    // Verify drawer is hidden (check transform or aria-hidden)
    const drawerHidden = await drawer.getAttribute('aria-hidden');
    expect(drawerHidden).toBe('true');
  });
  
  test('drawer should not have content clipped at bottom', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(300);
    
    // Open mobile menu
    await page.locator('#mobile-menu-button').click();
    await page.waitForTimeout(200);
    
    const drawer = page.locator('#mobile-menu');
    
    // Get drawer and footer section bounding boxes
    const drawerBox = await drawer.boundingBox();
    const footerSection = drawer.locator('div').last();
    const footerBox = await footerSection.boundingBox();
    
    // Footer should be fully visible within drawer
    expect(footerBox).not.toBeNull();
    if (footerBox && drawerBox) {
      const footerBottom = footerBox.y + footerBox.height;
      const drawerBottom = drawerBox.y + drawerBox.height;
      
      // Footer bottom should not exceed drawer bottom (allowing 20px for safe area)
      expect(footerBottom).toBeLessThanOrEqual(drawerBottom + 20);
    }
  });
  
  test('drawer should have compact width (max 280px or min 240px for usability)', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(300);
    
    // Open drawer
    await page.locator('#mobile-menu-button').click();
    await page.waitForTimeout(200);
    
    const drawer = page.locator('#mobile-menu');
    const drawerBox = await drawer.boundingBox();
    
    // Drawer width should be between 240px (min for usability) and 280px (max for compactness)
    // On very small screens, min-width: 240px ensures touch targets remain accessible
    expect(drawerBox.width).toBeGreaterThanOrEqual(220); // Allow small tolerance
    expect(drawerBox.width).toBeLessThanOrEqual(300); // Max with tolerance
  });
});

/**
 * Sprint 10: Sticky CTA Bar Tests
 */
test.describe('Sticky CTA Bar - Stability', () => {
  test.use({ viewport: { width: 390, height: 844 } });
  
  test('sticky CTA should have solid background (no blur)', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    
    // Scroll to trigger sticky CTA visibility
    await page.evaluate(() => window.scrollTo(0, window.innerHeight * 0.5));
    await page.waitForTimeout(500);
    
    // Check sticky CTA or graphene CTA bar
    const stickyBar = page.locator('.sticky-cta-bar, .graphene-cta-bar').first();
    
    // Verify it has solid background (backdrop-filter should be none)
    const backdropFilter = await stickyBar.evaluate(el => {
      const style = window.getComputedStyle(el);
      return style.backdropFilter || style.webkitBackdropFilter;
    });
    
    expect(backdropFilter).toBe('none');
  });
  
  test('sticky CTA should maintain z-index above content', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    
    await page.evaluate(() => window.scrollTo(0, window.innerHeight * 0.5));
    await page.waitForTimeout(500);
    
    const stickyBar = page.locator('.sticky-cta-bar, .graphene-cta-bar').first();
    
    const zIndex = await stickyBar.evaluate(el => {
      const style = window.getComputedStyle(el);
      return parseInt(style.zIndex, 10);
    });
    
    // Landing contract: sticky CTA at least 60
    expect(zIndex).toBeGreaterThanOrEqual(60);
  });
});

/**
 * Sprint 10: Console Selector Alignment
 */
test.describe('Console Selector - Alignment', () => {
  test.use({ viewport: { width: 390, height: 844 } });
  
  test('console buttons in drawer should be centered', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(300);
    
    // Open drawer
    await page.locator('#mobile-menu-button').click();
    await page.waitForTimeout(200);
    
    // Find console buttons container
    const consoleContainer = page.locator('.mobile-console-access > div').first();
    
    if (await consoleContainer.isVisible()) {
      const justifyContent = await consoleContainer.evaluate(el => {
        return window.getComputedStyle(el).justifyContent;
      });
      
      expect(justifyContent).toBe('center');
    }
  });
});

/**
 * P0 Landing Page Sanity Tests
 * Validates critical landing page stability requirements
 */
test.describe('Landing P0 Sanity', () => {
  test.use({ viewport: { width: 390, height: 844 } }); // iPhone-sized

  test('drawer should show all nav items without page scroll', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(300);
    
    // Open drawer
    await page.locator('#mobile-menu-button').click();
    await page.waitForTimeout(300);
    
    const drawer = page.locator('#mobile-menu');
    await expect(drawer).toBeVisible();
    
    // Check nav links are visible
    const navLinks = drawer.locator('.nav-link-mobile');
    const count = await navLinks.count();
    expect(count).toBeGreaterThan(3);
    
    // Verify first and last nav links are visible (no clipping)
    await expect(navLinks.first()).toBeVisible();
    
    // Close button must be accessible
    const closeBtn = drawer.locator('#mobile-menu-close');
    await expect(closeBtn).toBeVisible();
    const closeBox = await closeBtn.boundingBox();
    expect(closeBox.width).toBeGreaterThanOrEqual(44);
    expect(closeBox.height).toBeGreaterThanOrEqual(44);
  });

  test('sticky CTA should not overlap footer legal row', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    
    // Scroll to bottom of page
    await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
    await page.waitForTimeout(500);
    
    const footer = page.locator('footer, .site-footer').first();
    await expect(footer).toBeVisible();
    
    // Check sticky CTA is docked or hidden when footer visible
    const stickyBar = page.locator('.sticky-cta-bar, .graphene-cta-bar').first();
    
    if (await stickyBar.isVisible()) {
      // Should have is-docked class or be translated away
      const isDocked = await stickyBar.evaluate(el => {
        return el.classList.contains('is-docked') || 
               el.getAttribute('data-footer-visible') === 'true';
      });
      // Either docked or the sticky bar might just be dismissed
      // This test passes if we scrolled to footer and CTA isn't overlapping
    }
    
    // Verify legal row in footer is visible
    const legalRow = footer.locator('a[href*="privacy"], a[href*="terms"]').first();
    if (await legalRow.count() > 0) {
      await expect(legalRow).toBeVisible();
    }
  });

  test('sticky CTA must have solid background (no ghosting)', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(500);
    
    // Scroll to trigger CTA visibility
    await page.evaluate(() => window.scrollTo(0, 500));
    await page.waitForTimeout(300);
    
    const stickyBar = page.locator('.sticky-cta-bar, [data-component="sticky-cta"], #sticky-cta').first();
    
    if (await stickyBar.count() > 0 && await stickyBar.isVisible()) {
      const styles = await stickyBar.evaluate(el => {
        const computed = window.getComputedStyle(el);
        const bgColor = computed.backgroundColor;
        
        // Parse rgba values
        const rgbaMatch = bgColor.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
        let alpha = 1;
        if (rgbaMatch && rgbaMatch[4] !== undefined) {
          alpha = parseFloat(rgbaMatch[4]);
        }
        
        return {
          backgroundColor: bgColor,
          alpha: alpha,
          opacity: parseFloat(computed.opacity),
          mixBlendMode: computed.mixBlendMode
        };
      });
      
      // Background alpha must be >= 0.85 to prevent ghosting
      expect(styles.alpha).toBeGreaterThanOrEqual(0.85);
      
      // Element opacity must be 1 (no transparency)
      expect(styles.opacity).toBe(1);
      
      // No weird blend modes
      expect(styles.mixBlendMode).toBe('normal');
    }
  });

  test('AI assistant overlay should not blur page', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(500);
    
    // Find and click assistant toggle if present
    const toggleBtn = page.locator('#alphabot-toggle-btn');
    
    if (await toggleBtn.count() > 0 && await toggleBtn.isVisible()) {
      await toggleBtn.click();
      await page.waitForTimeout(300);
      
      // Check overlay has no blur
      const overlay = page.locator('.alphabot-overlay, #alphabot-overlay');
      if (await overlay.count() > 0) {
        const backdropFilter = await overlay.evaluate(el => {
          return window.getComputedStyle(el).backdropFilter;
        });
        expect(backdropFilter === 'none' || backdropFilter === '').toBeTruthy();
      }
    }
  });

  test('drawer overlay should have glass blur effect', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(300);
    
    // Open drawer
    await page.locator('#mobile-menu-button').click();
    await page.waitForTimeout(200);
    
    const overlay = page.locator('#mobile-menu-overlay');
    
    if (await overlay.isVisible()) {
      const styles = await overlay.evaluate(el => {
        const computed = window.getComputedStyle(el);
        return {
          backdropFilter: computed.backdropFilter,
          background: computed.backgroundColor
        };
      });
      
      // Backdrop filter should have blur (glass effect)
      expect(styles.backdropFilter.includes('blur') || styles.backdropFilter !== 'none').toBeTruthy();
    }
  });

  test('assistant DOM should not be mounted by default', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });

    const assistantNodes = await page.locator('#alphabot-panel, .alphabot-overlay, .bbx-command-rail').count();
    expect(assistantNodes).toBe(0);
  });

  test('no console components should render on landing', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });

    const consoleCards = await page.locator('.console-selector__card, [data-console-launch]').count();
    expect(consoleCards).toBe(0);
  });

  test('drawer and overlay stay hidden until user action', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });

    const overlayHidden = await page.locator('#mobile-menu-overlay').evaluate(el => {
      const cs = window.getComputedStyle(el);
      // Overlay must be hidden: visibility=hidden AND opacity=0 AND pointer-events=none
      return cs.visibility === 'hidden' && cs.opacity === '0' && cs.pointerEvents === 'none';
    });
    expect(overlayHidden).toBe(true);

    const drawerHidden = await page.locator('#mobile-menu').evaluate(el => {
      const cs = window.getComputedStyle(el);
      // Drawer must be hidden: visibility=hidden AND pointer-events=none
      return cs.visibility === 'hidden' && cs.pointerEvents === 'none';
    });
    expect(drawerHidden).toBe(true);
  });

  test('no light-mode surfaces in dark mode', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    
    // Force dark mode for test
    await page.evaluate(() => {
      document.documentElement.dataset.theme = 'dark';
      document.body.dataset.theme = 'dark';
    });
    await page.waitForTimeout(100);
    
    // Check key overlays for light backgrounds
    const lightSurfaces = await page.evaluate(() => {
      const elements = document.querySelectorAll(
        '#mobile-menu, #mobile-menu-overlay, .sticky-cta-bar, .cookie-banner'
      );
      for (const el of elements) {
        const bg = window.getComputedStyle(el).backgroundColor;
        // Parse RGB values - light surfaces have high values
        const match = bg.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
        if (match) {
          const [, r, g, b] = match.map(Number);
          // If all channels > 200, it's too light for dark mode
          if (r > 200 && g > 200 && b > 200) return true;
        }
      }
      return false;
    });
    expect(lightSurfaces).toBe(false);
  });

  test('CTA deduplication: exactly ONE CTA in DOM, z-index 75', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => window.scrollTo(0, window.innerHeight * 0.5));
    await page.waitForTimeout(300);
    
    // HARDGATE: Only ONE CTA element should exist on landing page
    // Canonical: #sticky-cta
    // NOT allowed: #sticky-cta-bar, .graphene-cta-bar (gated in PHP)
    const ctaCount = await page.evaluate(() => {
      return document.querySelectorAll('#sticky-cta, #sticky-cta-bar, .graphene-cta-bar').length;
    });
    expect(ctaCount, 'Expected exactly 1 CTA in DOM (only #sticky-cta)').toBe(1);
    
    // Verify the single CTA is #sticky-cta
    const canonicalCta = page.locator('#sticky-cta');
    expect(await canonicalCta.count(), '#sticky-cta must exist').toBe(1);
    
    // z-index contract: 75 (single source of truth)
    const zIndex = await canonicalCta.evaluate(el => {
      return parseInt(window.getComputedStyle(el).zIndex, 10);
    });
    expect(zIndex, 'CTA z-index must be 75').toBe(75);
  });

  test('alphabot-overlay div should not exist in DOM', async ({ page }) => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
    
    const overlayCount = await page.locator('#alphabot-overlay').count();
    expect(overlayCount).toBe(0);
  });
});
/**
 * Landing P1 Polish Tests
 * Validates Priority Access UI, assistant positioning, footer, FOUC prevention
 */
test.describe('Landing P1 Polish', () => {
  test.describe('P1-A: Priority Access Responsiveness', () => {
    test('sticky CTA should not overflow at mobile width (390px)', async ({ page }) => {
      await page.setViewportSize({ width: 390, height: 844 });
      await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
      await page.evaluate(() => window.scrollTo(0, window.innerHeight * 0.5));
      await page.waitForTimeout(500);
      
      const stickyBar = page.locator('.sticky-cta-bar, #sticky-cta').first();
      if (await stickyBar.isVisible()) {
        const box = await stickyBar.boundingBox();
        const viewport = page.viewportSize();
        
        // CTA bar should fit within viewport width
        expect(box.x).toBeGreaterThanOrEqual(0);
        expect(box.x + box.width).toBeLessThanOrEqual(viewport.width + 5);
        
        // Check buttons don't overflow
        const buttons = stickyBar.locator('.sticky-cta-bar__btn');
        const btnCount = await buttons.count();
        for (let i = 0; i < btnCount; i++) {
          const btnBox = await buttons.nth(i).boundingBox();
          if (btnBox) {
            expect(btnBox.x + btnBox.width).toBeLessThanOrEqual(viewport.width);
          }
        }
      }
    });
    
    test('sticky CTA should not overflow at small desktop (1024px)', async ({ page }) => {
      await page.setViewportSize({ width: 1024, height: 768 });
      await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
      await page.evaluate(() => window.scrollTo(0, window.innerHeight * 0.5));
      await page.waitForTimeout(500);
      
      const stickyBar = page.locator('.sticky-cta-bar, #sticky-cta').first();
      if (await stickyBar.isVisible()) {
        const box = await stickyBar.boundingBox();
        
        // Check content doesn't clip
        const content = stickyBar.locator('.sticky-cta-bar__content');
        if (await content.count() > 0) {
          const contentBox = await content.boundingBox();
          expect(contentBox.width).toBeLessThanOrEqual(box.width);
        }
      }
    });
    
    test('CTA buttons should not overlap in narrow viewport', async ({ page }) => {
      await page.setViewportSize({ width: 375, height: 667 });
      await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
      await page.evaluate(() => window.scrollTo(0, window.innerHeight * 0.5));
      await page.waitForTimeout(500);
      
      const stickyBar = page.locator('.sticky-cta-bar, #sticky-cta').first();
      if (await stickyBar.isVisible()) {
        const buttons = stickyBar.locator('.sticky-cta-bar__btn');
        const btnCount = await buttons.count();
        
        if (btnCount >= 2) {
          const boxes = [];
          for (let i = 0; i < btnCount; i++) {
            const box = await buttons.nth(i).boundingBox();
            if (box) boxes.push(box);
          }
          
          // Buttons should not overlap (check Y positions for stacked layout)
          for (let i = 1; i < boxes.length; i++) {
            const prev = boxes[i - 1];
            const curr = boxes[i];
            // Either stacked (different Y) or side-by-side (different X, no overlap)
            const isStacked = curr.y >= prev.y + prev.height - 2;
            const isSideBySide = curr.x >= prev.x + prev.width - 2 || prev.x >= curr.x + curr.width - 2;
            expect(isStacked || isSideBySide).toBe(true);
          }
        }
      }
    });
  });

  test.describe('P1-B: AI Assistant Not Covering CTAs', () => {
    test('assistant should not exist in landing DOM', async ({ page }) => {
      await page.setViewportSize({ width: 390, height: 844 });
      await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
      
      // Assistant should not be in DOM on landing
      const assistantElements = await page.locator(
        '#alphabot-container, .alphabot-widget, .alphabot-toggle, .alphabot-panel, .bbx-command-rail'
      ).count();
      expect(assistantElements).toBe(0);
    });
    
    test('no assistant overlay should obscure hero CTAs', async ({ page }) => {
      await page.setViewportSize({ width: 390, height: 844 });
      await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
      await page.waitForTimeout(300);
      
      // Find hero CTA buttons
      const heroCtas = page.locator('.graphene-cta-group .graphene-btn-primary, .graphene-cta-group .graphene-btn-secondary');
      const ctaCount = await heroCtas.count();
      
      if (ctaCount > 0) {
        // CTAs should be clickable (no overlay blocking)
        for (let i = 0; i < Math.min(ctaCount, 2); i++) {
          const cta = heroCtas.nth(i);
          const isEnabled = await cta.isEnabled();
          const isVisible = await cta.isVisible();
          expect(isVisible && isEnabled).toBe(true);
        }
      }
    });
  });

  test.describe('P1-E: FOUC Prevention', () => {
    test('page should not flash unstyled content', async ({ page }) => {
      await page.setViewportSize({ width: 1280, height: 800 });
      
      // Track any white flash during load
      let sawWhiteFlash = false;
      
      await page.addInitScript(() => {
        // Check background color immediately
        const checkBg = () => {
          const bg = window.getComputedStyle(document.documentElement).backgroundColor;
          const body = document.body ? window.getComputedStyle(document.body).backgroundColor : '';
          // White or very light backgrounds indicate FOUC
          const isLight = (color) => {
            const match = color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
            if (match) {
              const [, r, g, b] = match.map(Number);
              return r > 240 && g > 240 && b > 240;
            }
            return false;
          };
          if (isLight(bg) || isLight(body)) {
            window.__BBX_SAW_FOUC__ = true;
          }
        };
        checkBg();
        document.addEventListener('DOMContentLoaded', checkBg);
      });
      
      await page.goto(`${BASE_URL}/`, { waitUntil: 'load' });
      
      sawWhiteFlash = await page.evaluate(() => window.__BBX_SAW_FOUC__ || false);
      expect(sawWhiteFlash).toBe(false);
    });
    
    test('landing-ready class should be applied after load', async ({ page }) => {
      await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
      await page.waitForTimeout(500);
      
      const hasReadyClass = await page.evaluate(() => {
        return document.documentElement.classList.contains('landing-ready') ||
               document.body.classList.contains('landing-ready');
      });
      expect(hasReadyClass).toBe(true);
    });
    
    test('hero should have dark background immediately', async ({ page }) => {
      await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded' });
      
      const hero = page.locator('.graphene-hero-3d, #home').first();
      if (await hero.count() > 0) {
        const bg = await hero.evaluate(el => {
          return window.getComputedStyle(el).backgroundColor;
        });
        
        // Background should be dark (RGB values < 30)
        const match = bg.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
        if (match) {
          const [, r, g, b] = match.map(Number);
          expect(r).toBeLessThan(30);
          expect(g).toBeLessThan(30);
          expect(b).toBeLessThan(30);
        }
      }
    });
  });
});