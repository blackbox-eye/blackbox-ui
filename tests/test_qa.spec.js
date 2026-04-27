const { test, expect } = require('@playwright/test');

const viewports = [
    { width: 320, height: 568 },
    { width: 390, height: 844 },
];

const paths = [
    '/',
    '/products.php',
    '/contact.php',
    '/free-scan.php',
    '/demo.php'
];

test.describe('Mobile burger menu hotfix validation', () => {
    for (const v of viewports) {
        for (const p of paths) {
            test(`viewport: ${v.width}x${v.height} path: ${p}`, async ({ page }) => {
                await page.setViewportSize(v);
                await page.goto('http://localhost:8000' + p);
                await page.waitForLoadState('networkidle');

                const menu = page.locator('#mobile-menu');
                const overlay = page.locator('#mobile-menu-overlay');
                const btn = page.locator('#mobile-menu-button');

                // Initial state
                expect(await menu.evaluate(el => window.getComputedStyle(el).transform)).not.toContain('matrix(1, 0, 0, 1, 0, 0)');

                await btn.click();
                await page.waitForTimeout(400);

                // Open state
                expect(await menu.evaluate(el => window.getComputedStyle(el).transform)).toContain('matrix(1, 0, 0, 1, 0, 0)');
                expect(await overlay.evaluate(el => window.getComputedStyle(el).opacity)).toBe('1');

                // Close
                await overlay.click({ position: { x: 5, y: 5 }});
                await page.waitForTimeout(400);

                // Closed state
                expect(await menu.evaluate(el => window.getComputedStyle(el).transform)).not.toContain('matrix(1, 0, 0, 1, 0, 0)');
            });
        }
    }
});
