/**
 * Agent Access Page Tests
 *
 * Tests for the agent-access.php page including:
 * - Hero section with translated texts (no raw i18n keys)
 * - GDI and TS24 console cards with proper translations
 * - CTA buttons visibility and accessibility on mobile
 * - Touch target sizes for CTAs
 *
 * @requires A running local server (e.g., php -S localhost:8000)
 */

const { test, expect } = require('@playwright/test');

// =====================================================
// AGENT ACCESS i18n TESTS
// =====================================================
test.describe('Agent Access Page - i18n', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/agent-access.php');
  });

  test('should not display raw i18n keys in hero section', async ({ page }) => {
    // Check hero section for raw keys
    const heroContent = await page.locator('.access-hero').textContent();
    
    // Should NOT contain raw i18n key patterns
    expect(heroContent).not.toContain('agent_access.');
    expect(heroContent).not.toContain('.hero.');
    expect(heroContent).not.toContain('.title');
    expect(heroContent).not.toContain('.lead');
    expect(heroContent).not.toContain('.eyebrow');
    expect(heroContent).not.toContain('.audit_notice');
  });

  test('should display translated hero title', async ({ page }) => {
    const heroTitle = page.locator('.access-hero__title');
    await expect(heroTitle).toBeVisible();
    
    const text = await heroTitle.textContent();
    // Should have actual translated text, not raw key
    expect(text.length).toBeGreaterThan(5);
    expect(text).not.toMatch(/^agent_access/);
    // Should match known translations (EN or DA)
    expect(text.toLowerCase()).toMatch(/choose|vælg|secure|sikker|access|adgang/i);
  });

  test('should display translated hero lead text', async ({ page }) => {
    const heroLead = page.locator('.access-hero__lead');
    await expect(heroLead).toBeVisible();
    
    const text = await heroLead.textContent();
    expect(text.length).toBeGreaterThan(20);
    expect(text).not.toMatch(/^agent_access/);
  });

  test('should not display raw i18n keys in GDI card', async ({ page }) => {
    const gdiCard = await page.locator('[data-console="gdi"]').textContent();
    
    expect(gdiCard).not.toContain('agent_access.');
    expect(gdiCard).not.toContain('.cards.');
    expect(gdiCard).not.toContain('.gdi.');
  });

  test('should display translated GDI card title', async ({ page }) => {
    const gdiTitle = page.locator('[data-console="gdi"] .access-card__title');
    await expect(gdiTitle).toBeVisible();
    
    const text = await gdiTitle.textContent();
    expect(text.length).toBeGreaterThan(5);
    expect(text).not.toMatch(/^agent_access/);
    // Should contain GDI or GreyEYE reference
    expect(text.toLowerCase()).toMatch(/gdi|greyeye|intelligence/i);
  });

  test('should not display raw i18n keys in TS24 card', async ({ page }) => {
    const ts24Card = await page.locator('[data-console="ts24"]').textContent();
    
    expect(ts24Card).not.toContain('agent_access.');
    expect(ts24Card).not.toContain('.cards.');
    expect(ts24Card).not.toContain('.ts24.');
  });

  test('should display translated TS24 card title', async ({ page }) => {
    const ts24Title = page.locator('[data-console="ts24"] .access-card__title');
    await expect(ts24Title).toBeVisible();
    
    const text = await ts24Title.textContent();
    expect(text.length).toBeGreaterThan(5);
    expect(text).not.toMatch(/^agent_access/);
    // Should contain TS24 or Intel24 reference
    expect(text.toLowerCase()).toMatch(/ts24|intel24|intelligence/i);
  });
});

// =====================================================
// AGENT ACCESS MOBILE TESTS
// =====================================================
test.describe('Agent Access Page - Mobile (320px)', () => {
  test.use({
    viewport: { width: 320, height: 568 }
  });

  test.beforeEach(async ({ page }) => {
    await page.goto('/agent-access.php');
  });

  test('should display GDI CTA button visible on mobile', async ({ page }) => {
    const gdiCta = page.locator('[data-console="gdi"] .access-card__cta');
    await expect(gdiCta).toBeVisible();
    
    // Check minimum touch target size (48px recommended)
    const box = await gdiCta.boundingBox();
    expect(box.height).toBeGreaterThanOrEqual(44); // Allow slight tolerance
  });

  test('should display TS24 CTA button visible on mobile', async ({ page }) => {
    const ts24Cta = page.locator('[data-console="ts24"] .access-card__cta');
    await expect(ts24Cta).toBeVisible();
    
    // Check minimum touch target size
    const box = await ts24Cta.boundingBox();
    expect(box.height).toBeGreaterThanOrEqual(44);
  });

  test('should make CTA buttons clickable on mobile', async ({ page }) => {
    // GDI CTA
    const gdiCta = page.locator('[data-console="gdi"] .access-card__cta');
    await expect(gdiCta).toBeEnabled();
    await expect(gdiCta).toHaveAttribute('href');
    
    // TS24 CTA
    const ts24Cta = page.locator('[data-console="ts24"] .access-card__cta');
    await expect(ts24Cta).toBeEnabled();
    await expect(ts24Cta).toHaveAttribute('href');
  });

  test('should display hero section properly on mobile', async ({ page }) => {
    const hero = page.locator('.access-hero');
    await expect(hero).toBeVisible();
    
    // Hero should not overflow
    const heroBox = await hero.boundingBox();
    expect(heroBox.width).toBeLessThanOrEqual(320);
  });
});

// =====================================================
// AGENT ACCESS DESKTOP TESTS
// =====================================================
test.describe('Agent Access Page - Desktop', () => {
  test.use({
    viewport: { width: 1280, height: 720 }
  });

  test.beforeEach(async ({ page }) => {
    await page.goto('/agent-access.php');
  });

  test('should display both console cards', async ({ page }) => {
    const gdiCard = page.locator('[data-console="gdi"]');
    const ts24Card = page.locator('[data-console="ts24"]');
    
    await expect(gdiCard).toBeVisible();
    await expect(ts24Card).toBeVisible();
  });

  test('should have proper meta title for SEO', async ({ page }) => {
    const title = await page.title();
    expect(title).not.toContain('agent_access.');
    expect(title.length).toBeGreaterThan(10);
  });
});
