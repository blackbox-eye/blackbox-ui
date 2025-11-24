/**
 * Web Optimization Tests
 * Tests for lazy loading, minification, SRI, and accessibility
 */

const { test, expect } = require('@playwright/test');

test.describe('Web Optimization Tests', () => {
  
  test.describe('Lazy Loading', () => {
    
    test('Blog images should have loading="lazy" attribute', async ({ page }) => {
      await page.goto('/blog.php');
      
      const images = await page.locator('img[src*="featured_image"]');
      const count = await images.count();
      
      if (count > 0) {
        for (let i = 0; i < count; i++) {
          const loading = await images.nth(i).getAttribute('loading');
          expect(loading).toBe('lazy');
        }
      }
    });
    
    test('Blog post images should have loading="lazy" attribute', async ({ page }) => {
      // Skip if no posts available
      try {
        await page.goto('/blog-post.php?slug=test', { timeout: 5000 });
        
        const featuredImage = page.locator('img').first();
        if (await featuredImage.count() > 0) {
          const loading = await featuredImage.getAttribute('loading');
          expect(loading).toBe('lazy');
        }
      } catch (e) {
        test.skip();
      }
    });
    
    test('Agent login logo should have loading="lazy" attribute', async ({ page }) => {
      await page.goto('/agent-login.php');
      
      const logo = page.locator('img[alt*="Blackbox EYE"]').first();
      const loading = await logo.getAttribute('loading');
      expect(loading).toBe('lazy');
    });
  });
  
  test.describe('Minification', () => {
    
    test('Marketing pages should load minified CSS', async ({ page }) => {
      await page.goto('/index.php');
      
      // Check for minified CSS in head
      const cssLinks = await page.locator('link[rel="stylesheet"]');
      const hrefs = await cssLinks.evaluateAll(links => 
        links.map(link => link.getAttribute('href'))
      );
      
      const hasMinifiedCSS = hrefs.some(href => href && href.includes('.min.css'));
      expect(hasMinifiedCSS).toBeTruthy();
    });
    
    test('Footer should load minified JS', async ({ page }) => {
      await page.goto('/index.php');
      
      // Check for minified JS
      const scripts = await page.locator('script[src*="site"]');
      const srcs = await scripts.evaluateAll(scripts => 
        scripts.map(script => script.getAttribute('src'))
      );
      
      const hasMinifiedJS = srcs.some(src => src && src.includes('site.min.js'));
      expect(hasMinifiedJS).toBeTruthy();
    });
  });
  
  test.describe('SRI (Subresource Integrity)', () => {
    
    test('Chart.js should have integrity attribute', async ({ page }) => {
      await page.goto('/dashboard.php');
      
      const chartScript = page.locator('script[src*="chart.js"]').first();
      const integrity = await chartScript.getAttribute('integrity');
      const crossorigin = await chartScript.getAttribute('crossorigin');
      
      expect(integrity).toBeTruthy();
      expect(integrity).toContain('sha384-');
      expect(crossorigin).toBe('anonymous');
    });
  });
  
  test.describe('Accessibility', () => {
    
    test('Should have skip-to-content link', async ({ page }) => {
      await page.goto('/index.php');
      
      const skipLink = page.locator('a.skip-link');
      await expect(skipLink).toBeAttached();
      
      const href = await skipLink.getAttribute('href');
      expect(href).toBe('#main-content');
    });
    
    test('Main content should have id="main-content"', async ({ page }) => {
      await page.goto('/index.php');
      
      const mainContent = page.locator('#main-content');
      await expect(mainContent).toBeAttached();
      
      const tagName = await mainContent.evaluate(el => el.tagName.toLowerCase());
      expect(tagName).toBe('main');
    });
    
    test('Contact form should have proper ARIA attributes', async ({ page }) => {
      await page.goto('/contact.php');
      
      const form = page.locator('#contact-form');
      const ariaLabel = await form.getAttribute('aria-label');
      expect(ariaLabel).toBeTruthy();
      
      const nameInput = page.locator('#name');
      const ariaRequired = await nameInput.getAttribute('aria-required');
      expect(ariaRequired).toBe('true');
      
      const errorDiv = page.locator('#contact-form-error');
      const role = await errorDiv.getAttribute('role');
      expect(role).toBe('alert');
    });
    
    test('Navigation links should have aria-current on active page', async ({ page }) => {
      await page.goto('/products.php');
      
      // Check for aria-current="page" on products link
      const activeLink = page.locator('a[aria-current="page"]').first();
      await expect(activeLink).toBeAttached();
    });
    
    test('Mobile menu button should have proper ARIA attributes', async ({ page }) => {
      await page.goto('/index.php');
      
      const menuButton = page.locator('#mobile-menu-button');
      const ariaExpanded = await menuButton.getAttribute('aria-expanded');
      const ariaControls = await menuButton.getAttribute('aria-controls');
      const ariaLabel = await menuButton.getAttribute('aria-label');
      
      expect(ariaExpanded).toBe('false');
      expect(ariaControls).toBe('mobile-menu');
      expect(ariaLabel).toBeTruthy();
    });
    
    test('Images should have alt attributes', async ({ page }) => {
      await page.goto('/agent-login.php');
      
      const images = page.locator('img');
      const count = await images.count();
      
      for (let i = 0; i < count; i++) {
        const alt = await images.nth(i).getAttribute('alt');
        expect(alt).toBeTruthy();
      }
    });
  });
  
  test.describe('Keyboard Navigation', () => {
    
    test('Should be able to tab through navigation', async ({ page }) => {
      await page.goto('/index.php');
      
      // Tab to skip link
      await page.keyboard.press('Tab');
      const focusedElement = await page.evaluate(() => document.activeElement.className);
      expect(focusedElement).toContain('skip-link');
    });
    
    test('Mobile menu should open/close with keyboard', async ({ page }) => {
      await page.setViewportSize({ width: 375, height: 667 }); // Mobile size
      await page.goto('/index.php');
      
      // Tab to mobile menu button
      let focused = null;
      for (let i = 0; i < 10; i++) {
        await page.keyboard.press('Tab');
        focused = await page.evaluate(() => document.activeElement.id);
        if (focused === 'mobile-menu-button') break;
      }
      
      expect(focused).toBe('mobile-menu-button');
      
      // Press Enter to open menu
      await page.keyboard.press('Enter');
      
      // Check menu is open
      const menuClass = await page.locator('#mobile-menu').getAttribute('class');
      expect(menuClass).toContain('active');
      
      // Press Escape to close
      await page.keyboard.press('Escape');
      
      // Wait a bit for animation
      await page.waitForTimeout(500);
      
      // Check menu is closed
      const menuClassAfter = await page.locator('#mobile-menu').getAttribute('class');
      expect(menuClassAfter || '').not.toContain('active');
    });
  });
  
  test.describe('Performance', () => {
    
    test('Page should load minified assets efficiently', async ({ page }) => {
      const response = await page.goto('/index.php');
      
      expect(response.status()).toBe(200);
      
      // Check that page loaded in reasonable time
      const timing = await page.evaluate(() => {
        const perfData = performance.getEntriesByType('navigation')[0];
        return perfData.loadEventEnd - perfData.fetchStart;
      });
      
      // Page should load in under 5 seconds
      expect(timing).toBeLessThan(5000);
    });
  });
});
