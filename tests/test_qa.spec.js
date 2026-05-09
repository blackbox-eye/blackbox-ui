const { test, expect } = require('@playwright/test');

const viewports = [
    { width: 320, height: 568 },
    { width: 390, height: 844 },
    { width: 768, height: 1024 },
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
                await page.goto(p);
                await page.waitForLoadState('networkidle');

                const menu = page.locator('#mobile-menu');
                const overlay = page.locator('#mobile-menu-overlay');
                const btn = page.locator('#mobile-menu-button');
                const overlayOpacity = () => overlay.evaluate(el => getComputedStyle(el).opacity);
                const menuTransform = () => menu.evaluate(el => getComputedStyle(el).transform);

                // Initial state
                const initialOverlayOpacity = await overlayOpacity();
                const initialMenuTransform = await menuTransform();

                await btn.click();

                // Open state
                await expect.poll(overlayOpacity).not.toBe(initialOverlayOpacity);
                await expect.poll(menuTransform).not.toBe(initialMenuTransform);

                // Close
                await overlay.click({ position: { x: 5, y: 5 }});

                // Closed state
                await expect.poll(overlayOpacity).toBe(initialOverlayOpacity);
            });
        }
    }
});
