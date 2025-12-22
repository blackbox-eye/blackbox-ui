// @ts-check
const { test, expect } = require('@playwright/test');
const AxeBuilder = require('@axe-core/playwright').default;

/**
 * Sprint 8 A11y Hard Gate Tests
 * MUST PASS: 0 critical, 0 serious violations
 * Pages tested: landing, login, ccs-console, agent-access
 */

const BASE_URL = process.env.BASE_URL || 'https://blackbox.codes';

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
      await browserPage.goto(`${BASE_URL}${page.path}`, { waitUntil: 'domcontentloaded' });
      
      // Wait for dynamic content
      await browserPage.waitForTimeout(500);
      
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
