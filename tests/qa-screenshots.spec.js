/**
 * QA Screenshot Generator - Sprint 7 Final Polish
 * Captures key states for release-gate validation
 */
const { test, expect } = require('@playwright/test');

const BASE_URL = 'http://localhost:8000';
const ARTIFACT_DIR = 'artifacts/qa-sprint7';

// Desktop 1440px
test.describe('Desktop Screenshots (1440px)', () => {
  test.use({ viewport: { width: 1440, height: 900 } });

  test('agent-access page top/mid/bottom', async ({ page }) => {
    await page.goto(`${BASE_URL}/agent-access.php`);
    await page.waitForLoadState('networkidle');
    
    // Top - hero and quick switch
    await page.screenshot({ path: `${ARTIFACT_DIR}/desktop-agent-access-top.png`, fullPage: false });
    
    // Scroll to middle (cards)
    await page.evaluate(() => window.scrollTo(0, 400));
    await page.waitForTimeout(300);
    await page.screenshot({ path: `${ARTIFACT_DIR}/desktop-agent-access-mid.png`, fullPage: false });
    
    // Scroll to bottom (activity)
    await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
    await page.waitForTimeout(300);
    await page.screenshot({ path: `${ARTIFACT_DIR}/desktop-agent-access-bottom.png`, fullPage: false });
  });

  test('info modal open', async ({ page }) => {
    await page.goto(`${BASE_URL}/agent-access.php`);
    await page.waitForLoadState('networkidle');
    
    // Click info button on CCS card
    const infoBtn = page.locator('#ccs .console-card__info-btn');
    await infoBtn.click();
    await page.waitForTimeout(500);
    
    await page.screenshot({ path: `${ARTIFACT_DIR}/desktop-info-modal-open.png`, fullPage: false });
  });

  test('ccs-login before and after MFA trigger', async ({ page }) => {
    await page.goto(`${BASE_URL}/ccs-login.php`);
    await page.waitForLoadState('networkidle');
    
    // Before MFA - initial state (MFA notice should be hidden)
    await page.screenshot({ path: `${ARTIFACT_DIR}/desktop-ccs-login-before.png`, fullPage: false });
    
    // Fill form
    await page.fill('#ccs-email', 'demo@test.com');
    await page.fill('#ccs-password', 'DemoPass123!');
    
    // Click submit to trigger MFA
    await page.click('.ccs-login__submit');
    await page.waitForTimeout(1500);
    
    // After - MFA modal should appear
    await page.screenshot({ path: `${ARTIFACT_DIR}/desktop-ccs-login-after-mfa.png`, fullPage: false });
  });

  test('sso request modal success', async ({ page }) => {
    await page.goto(`${BASE_URL}/ccs-login.php`);
    await page.waitForLoadState('networkidle');
    
    // Click SSO request button
    await page.click('[data-testid="sso-request-btn"]');
    await page.waitForTimeout(500);
    
    // Fill form with valid data
    await page.fill('#sso-company', 'Demo Corporation');
    await page.fill('#sso-email', 'admin@demo.corp');
    
    await page.screenshot({ path: `${ARTIFACT_DIR}/desktop-sso-modal-filled.png`, fullPage: false });
    
    // Submit (DEMO mode will succeed)
    await page.click('[data-testid="sso-submit-btn"]');
    await page.waitForTimeout(1200);
    
    await page.screenshot({ path: `${ARTIFACT_DIR}/desktop-sso-modal-success.png`, fullPage: false });
  });
});

// Mobile 375px (iPhone)
test.describe('Mobile Screenshots (375px)', () => {
  test.use({ viewport: { width: 375, height: 812 } });

  test('agent-access mobile stacked cards', async ({ page }) => {
    await page.goto(`${BASE_URL}/agent-access.php`);
    await page.waitForLoadState('networkidle');
    
    await page.screenshot({ path: `${ARTIFACT_DIR}/mobile-agent-access-top.png`, fullPage: false });
    
    // Scroll to see cards stacked
    await page.evaluate(() => window.scrollTo(0, 300));
    await page.waitForTimeout(300);
    await page.screenshot({ path: `${ARTIFACT_DIR}/mobile-agent-access-cards.png`, fullPage: false });
    
    // Scroll to activity
    await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
    await page.waitForTimeout(300);
    await page.screenshot({ path: `${ARTIFACT_DIR}/mobile-agent-access-activity.png`, fullPage: false });
  });

  test('ccs-login mobile', async ({ page }) => {
    await page.goto(`${BASE_URL}/ccs-login.php`);
    await page.waitForLoadState('networkidle');
    
    await page.screenshot({ path: `${ARTIFACT_DIR}/mobile-ccs-login.png`, fullPage: false });
  });

  test('pin snackbar mobile', async ({ page }) => {
    await page.goto(`${BASE_URL}/agent-access.php`);
    await page.waitForLoadState('networkidle');
    
    // Click pin button
    const pinBtn = page.locator('#ccs [data-favorite="ccs"]');
    await pinBtn.click();
    await page.waitForTimeout(800);
    
    await page.screenshot({ path: `${ARTIFACT_DIR}/mobile-pin-snackbar.png`, fullPage: false });
  });
});

// Light theme
test.describe('Light Theme Screenshots', () => {
  test.use({ viewport: { width: 1440, height: 900 } });

  test('agent-access light theme', async ({ page }) => {
    await page.goto(`${BASE_URL}/agent-access.php`);
    await page.waitForLoadState('networkidle');
    
    // Toggle to light theme
    await page.evaluate(() => {
      document.documentElement.setAttribute('data-theme', 'light');
    });
    await page.waitForTimeout(300);
    
    await page.screenshot({ path: `${ARTIFACT_DIR}/light-agent-access.png`, fullPage: false });
  });
});
