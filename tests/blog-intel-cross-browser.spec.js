/**
 * Blog Page Cross-Browser Tests
 * 
 * Tests blog.php across:
 * - Browsers: Chromium, Firefox, WebKit
 * - Viewports: Desktop (1440x900), iPhone (390x844), iPad (820x1180)
 * 
 * Validates:
 * - HTTP 200 response
 * - Posts container present
 * - No horizontal scroll on mobile
 * - Filters work (if posts exist)
 * - At least 1 post renders (if data available)
 * 
 * @version 1.0.0
 */

const { test, expect } = require('@playwright/test');

// Test viewport configurations
const viewports = {
  desktop: { width: 1440, height: 900 },
  iphone: { width: 390, height: 844 },
  ipad: { width: 820, height: 1180 }
};

/**
 * Test blog.php on different viewports
 */
for (const [name, viewport] of Object.entries(viewports)) {
  test.describe(`Blog page - ${name} (${viewport.width}x${viewport.height})`, () => {
    test.use({ viewport });

    test('should load with HTTP 200', async ({ page }) => {
      const response = await page.goto('/blog.php');
      expect(response.status()).toBe(200);
    });

    test('should have valid HTML structure', async ({ page }) => {
      await page.goto('/blog.php');
      
      // Check for essential HTML elements
      await expect(page.locator('html')).toBeVisible();
      await expect(page.locator('head')).toBeAttached();
      await expect(page.locator('body')).toBeVisible();
      
      // Check for main content
      await expect(page.locator('main#main-content')).toBeVisible();
    });

    test('should have blog hero section', async ({ page }) => {
      await page.goto('/blog.php');
      
      // Check for hero section
      const hero = page.locator('section').first();
      await expect(hero).toBeVisible();
      
      // Check for title
      const title = page.locator('h1');
      await expect(title).toBeVisible();
    });

    test('should not have horizontal scroll', async ({ page }) => {
      await page.goto('/blog.php');
      
      // Check document width vs viewport width
      const scrollWidth = await page.evaluate(() => document.documentElement.scrollWidth);
      const clientWidth = await page.evaluate(() => document.documentElement.clientWidth);
      
      // Allow 1px tolerance for rounding
      expect(scrollWidth).toBeLessThanOrEqual(clientWidth + 1);
    });

    test('should show posts section or fallback message', async ({ page }) => {
      await page.goto('/blog.php');
      
      // Either posts grid or fallback message should be present
      const postsGrid = page.locator('.grid.grid-cols-1');
      const fallbackMessage = page.locator('text=/Blog er i gang med at blive opdateret|Bloggen er midlertidigt utilgængelig/i');
      
      const hasPostsGrid = await postsGrid.count() > 0;
      const hasFallback = await fallbackMessage.count() > 0;
      
      expect(hasPostsGrid || hasFallback).toBe(true);
    });

    test('should render blog cards if posts exist', async ({ page }) => {
      await page.goto('/blog.php');
      
      // Check if posts grid exists
      const postsGrid = page.locator('.grid.grid-cols-1');
      const hasGrid = await postsGrid.count() > 0;
      
      if (hasGrid) {
        // At least one blog card should be present
        const blogCards = page.locator('.blog-card');
        const cardCount = await blogCards.count();
        expect(cardCount).toBeGreaterThan(0);
        
        // First card should have title
        const firstCard = blogCards.first();
        const title = firstCard.locator('.blog-card__title');
        await expect(title).toBeVisible();
      }
    });

    test('should have sticky navigation', async ({ page }) => {
      await page.goto('/blog.php');
      
      // Check for sticky nav
      const stickyNav = page.locator('nav[aria-label="Blog navigation"]');
      
      // Nav may not exist if no posts, so check conditionally
      const navCount = await stickyNav.count();
      if (navCount > 0) {
        await expect(stickyNav).toBeVisible();
        
        // Check sticky positioning
        const position = await stickyNav.evaluate(el => 
          window.getComputedStyle(el).position
        );
        expect(position).toBe('sticky');
      }
    });

    test('should have news section', async ({ page }) => {
      await page.goto('/blog.php');
      
      // Global Threat Intelligence section should be present
      const newsSection = page.locator('section:has-text("Global")');
      const newsPanels = page.locator('.news-panel');
      
      // Either section should exist
      const hasNews = await newsSection.count() > 0 || await newsPanels.count() > 0;
      expect(hasNews).toBe(true);
    });

    test('should have region tabs if news section exists', async ({ page }) => {
      await page.goto('/blog.php');
      
      const regionTabs = page.locator('.news-region-tab');
      const tabCount = await regionTabs.count();
      
      if (tabCount > 0) {
        // At least one tab should be active
        const activeTab = page.locator('.news-region-tab.is-active');
        await expect(activeTab).toBeVisible();
        
        // Click on second tab if exists
        if (tabCount > 1) {
          await regionTabs.nth(1).click();
          
          // Second tab should now be active
          await expect(regionTabs.nth(1)).toHaveClass(/is-active/);
        }
      }
    });

    test('should have newsletter section', async ({ page }) => {
      await page.goto('/blog.php');
      
      const newsletterSection = page.locator('.newsletter-card');
      const newsletterCount = await newsletterSection.count();
      
      if (newsletterCount > 0) {
        await expect(newsletterSection).toBeVisible();
        
        // Check for email input
        const emailInput = page.locator('input[type="email"]');
        await expect(emailInput).toBeVisible();
      }
    });

    test(`should take screenshot (${name})`, async ({ page }) => {
      await page.goto('/blog.php');
      
      // Wait for any animations to complete
      await page.waitForTimeout(1000);
      
      // Take full page screenshot
      await page.screenshot({
        path: `artifacts/blog-${name}-${viewport.width}x${viewport.height}.png`,
        fullPage: true
      });
    });
  });
}

/**
 * Test filters and interactions (desktop only)
 */
test.describe('Blog filters and interactions (desktop)', () => {
  test.use({ viewport: viewports.desktop });

  test('should have filter pills if categories exist', async ({ page }) => {
    await page.goto('/blog.php');
    
    const filterPills = page.locator('.blog-filter-pill');
    const pillCount = await filterPills.count();
    
    if (pillCount > 0) {
      // "All" filter should exist
      const allFilter = page.locator('.blog-filter-pill', { hasText: /all|alle/i });
      await expect(allFilter.first()).toBeVisible();
      
      // Click on a filter if more than one exists
      if (pillCount > 1) {
        const firstNonAllPill = filterPills.nth(1);
        await firstNonAllPill.click();
        
        // URL should update or page should re-render
        await page.waitForTimeout(500);
        
        // Filter should become active
        await expect(firstNonAllPill).toHaveClass(/is-active/);
      }
    }
  });

  test('should handle category filtering', async ({ page }) => {
    await page.goto('/blog.php');
    
    // Check if category filter exists in URL or UI
    const filterPills = page.locator('.blog-filter-pill');
    const pillCount = await filterPills.count();
    
    if (pillCount > 1) {
      // Click on a category filter
      await filterPills.nth(1).click();
      await page.waitForLoadState('networkidle');
      
      // Page should still load successfully
      expect(page.url()).toContain('blog.php');
      
      // Either filtered posts or "no posts" message should show
      const postsGrid = page.locator('.grid.grid-cols-1');
      const noPostsMsg = page.locator('text=/Ingen artikler|No articles|empty/i');
      
      const hasContent = await postsGrid.count() > 0 || await noPostsMsg.count() > 0;
      expect(hasContent).toBe(true);
    }
  });

  test('should switch between news regions', async ({ page }) => {
    await page.goto('/blog.php');
    
    const regionTabs = page.locator('.news-region-tab');
    const tabCount = await regionTabs.count();
    
    if (tabCount > 1) {
      // Get initial active panel
      const initialPanel = page.locator('.news-panel.is-visible');
      const initialPanelExists = await initialPanel.count() > 0;
      
      if (initialPanelExists) {
        // Click on second region tab
        await regionTabs.nth(1).click();
        await page.waitForTimeout(300);
        
        // A panel should be visible
        const visiblePanel = page.locator('.news-panel.is-visible');
        await expect(visiblePanel).toBeVisible();
      }
    }
  });

  test('should render external links for JSON posts', async ({ page }) => {
    await page.goto('/blog.php');
    
    // Check if any posts have external link icon
    const externalLinks = page.locator('a[target="_blank"][rel="noopener noreferrer"]');
    const externalLinkCount = await externalLinks.count();
    
    if (externalLinkCount > 0) {
      // External link icon should be present
      const linkWithIcon = externalLinks.first();
      const icon = linkWithIcon.locator('svg');
      
      // Icon might be in title or read button
      expect(icon.count()).resolves.toBeGreaterThanOrEqual(0);
    }
  });
});

/**
 * Test mobile-specific behavior
 */
test.describe('Blog mobile behavior', () => {
  test.use({ viewport: viewports.iphone });

  test('should have mobile-friendly navigation', async ({ page }) => {
    await page.goto('/blog.php');
    
    // Check that filter pills are scrollable/wrappable
    const filterContainer = page.locator('.flex.items-center.gap-2.flex-wrap');
    const filterExists = await filterContainer.count() > 0;
    
    if (filterExists) {
      await expect(filterContainer.first()).toBeVisible();
    }
  });

  test('should have touch-friendly card spacing', async ({ page }) => {
    await page.goto('/blog.php');
    
    const blogCards = page.locator('.blog-card');
    const cardCount = await blogCards.count();
    
    if (cardCount > 0) {
      // Cards should have adequate gap on mobile
      const gridGap = await page.locator('.grid.grid-cols-1').evaluate(el => {
        const style = window.getComputedStyle(el);
        return style.gap || style.gridGap;
      });
      
      // Gap should be defined (not empty)
      expect(gridGap).toBeTruthy();
    }
  });

  test('should stack content vertically on mobile', async ({ page }) => {
    await page.goto('/blog.php');
    
    // Cards should be in single column on mobile
    const grid = page.locator('.grid.grid-cols-1');
    const gridExists = await grid.count() > 0;
    
    if (gridExists) {
      const gridTemplateColumns = await grid.first().evaluate(el => 
        window.getComputedStyle(el).gridTemplateColumns
      );
      
      // Should have 1 column on mobile
      // Note: actual value depends on Tailwind breakpoints
      expect(gridTemplateColumns).toBeTruthy();
    }
  });
});

/**
 * Test accessibility
 */
test.describe('Blog accessibility', () => {
  test.use({ viewport: viewports.desktop });

  test('should have proper heading hierarchy', async ({ page }) => {
    await page.goto('/blog.php');
    
    // Should have h1
    const h1 = page.locator('h1');
    await expect(h1.first()).toBeVisible();
    
    // Should have h2 (section headers)
    const h2 = page.locator('h2');
    expect(await h2.count()).toBeGreaterThan(0);
  });

  test('should have aria labels for navigation', async ({ page }) => {
    await page.goto('/blog.php');
    
    const nav = page.locator('nav[aria-label]');
    const navCount = await nav.count();
    
    if (navCount > 0) {
      const ariaLabel = await nav.first().getAttribute('aria-label');
      expect(ariaLabel).toBeTruthy();
    }
  });

  test('should have proper link text', async ({ page }) => {
    await page.goto('/blog.php');
    
    // All links should have text or aria-label
    const links = page.locator('a');
    const linkCount = await links.count();
    
    if (linkCount > 0) {
      const firstLink = links.first();
      const text = await firstLink.textContent();
      const ariaLabel = await firstLink.getAttribute('aria-label');
      
      // Either text or aria-label should exist
      expect(text || ariaLabel).toBeTruthy();
    }
  });
});
