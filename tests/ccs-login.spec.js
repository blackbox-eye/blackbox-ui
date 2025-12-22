/**
 * CCS Login Portal Tests
 * 
 * Sprint 1 - Core structure and UX validation
 * Tests for page load, form elements, CTAs, and mobile viewport
 */

import { test, expect } from '@playwright/test';

// =====================================================
// CCS LOGIN - PAGE LOAD TESTS
// =====================================================
test.describe('CCS Login Page - Load', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/ccs-login.php');
  });

  test('should load the CCS login page', async ({ page }) => {
    await expect(page).toHaveTitle(/CCS Login/i);
  });

  test('should display the hero headline', async ({ page }) => {
    const headline = page.locator('.ccs-login__headline');
    await expect(headline).toBeVisible();
    await expect(headline).toContainText('Cross-Currency Settlement');
    await expect(headline).toContainText('Fiat & Crypto Unified');
  });

  test('should display the institutional subtext', async ({ page }) => {
    const subtext = page.locator('.ccs-login__subtext');
    await expect(subtext).toBeVisible();
    await expect(subtext).toContainText('Institutional-grade settlement infrastructure');
  });

  test('should display all three value proposition features', async ({ page }) => {
    const features = page.locator('.ccs-login__feature');
    await expect(features).toHaveCount(3);
    
    await expect(features.nth(0)).toContainText('Multi-asset ledger');
    await expect(features.nth(1)).toContainText('Near-instant settlement');
    await expect(features.nth(2)).toContainText('Audit-grade security');
  });
});

// =====================================================
// CCS LOGIN - FORM ELEMENTS TESTS
// =====================================================
test.describe('CCS Login Page - Form Elements', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/ccs-login.php');
  });

  test('should display email/username input', async ({ page }) => {
    const emailInput = page.locator('[data-testid="ccs-email-input"]');
    await expect(emailInput).toBeVisible();
    await expect(emailInput).toHaveAttribute('type', 'text');
    await expect(emailInput).toHaveAttribute('required', '');
  });

  test('should display password input', async ({ page }) => {
    const passwordInput = page.locator('[data-testid="ccs-password-input"]');
    await expect(passwordInput).toBeVisible();
    await expect(passwordInput).toHaveAttribute('type', 'password');
    await expect(passwordInput).toHaveAttribute('required', '');
  });

  test('should have password toggle button', async ({ page }) => {
    const toggle = page.locator('[data-testid="password-toggle"]');
    await expect(toggle).toBeVisible();
    await expect(toggle).toHaveAttribute('aria-label', 'Toggle password visibility');
  });

  test('should toggle password visibility', async ({ page }) => {
    const passwordInput = page.locator('[data-testid="ccs-password-input"]');
    const toggle = page.locator('[data-testid="password-toggle"]');
    
    // Initially password type
    await expect(passwordInput).toHaveAttribute('type', 'password');
    
    // Click toggle
    await toggle.click();
    await expect(passwordInput).toHaveAttribute('type', 'text');
    
    // Click again
    await toggle.click();
    await expect(passwordInput).toHaveAttribute('type', 'password');
  });

  test('should display password strength indicator', async ({ page }) => {
    const strengthIndicator = page.locator('[data-testid="password-strength"]');
    await expect(strengthIndicator).toBeVisible();
  });

  test('should update password strength on input', async ({ page }) => {
    const passwordInput = page.locator('[data-testid="ccs-password-input"]');
    const strengthFill = page.locator('.ccs-login__strength-fill');
    const strengthText = page.locator('.ccs-login__strength-text');
    
    // Empty - no strength
    await expect(strengthFill).toHaveAttribute('data-strength', '0');
    
    // Weak password
    await passwordInput.fill('123');
    await expect(strengthText).toContainText(/weak/i);
    
    // Stronger password
    await passwordInput.fill('MyP@ssword123!');
    // Should show moderate or strong
    const text = await strengthText.textContent();
    expect(text?.toLowerCase()).toMatch(/moderate|strong/i);
  });

  test('should display login form', async ({ page }) => {
    const form = page.locator('[data-testid="ccs-login-form"]');
    await expect(form).toBeVisible();
    await expect(form).toHaveAttribute('method', 'post');
  });

  test('should display submit button', async ({ page }) => {
    const submitBtn = page.locator('[data-testid="ccs-submit-btn"]');
    await expect(submitBtn).toBeVisible();
    await expect(submitBtn).toHaveText('Log in');
    await expect(submitBtn).toHaveAttribute('type', 'submit');
  });

  test('should have touch-safe input heights (min 44px)', async ({ page }) => {
    const emailInput = page.locator('[data-testid="ccs-email-input"]');
    const passwordInput = page.locator('[data-testid="ccs-password-input"]');
    const submitBtn = page.locator('[data-testid="ccs-submit-btn"]');
    
    const emailBox = await emailInput.boundingBox();
    const passwordBox = await passwordInput.boundingBox();
    const submitBox = await submitBtn.boundingBox();
    
    expect(emailBox?.height).toBeGreaterThanOrEqual(44);
    expect(passwordBox?.height).toBeGreaterThanOrEqual(44);
    expect(submitBox?.height).toBeGreaterThanOrEqual(44);
  });
});

// =====================================================
// CCS LOGIN - CTA BUTTONS TESTS
// =====================================================
test.describe('CCS Login Page - CTA Buttons', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/ccs-login.php');
  });

  test('should display Request Access button', async ({ page }) => {
    const requestBtn = page.locator('[data-testid="request-access-btn"]');
    await expect(requestBtn).toBeVisible();
    await expect(requestBtn).toContainText('Request CCS Access');
  });

  test('should display Request Access section', async ({ page }) => {
    const section = page.locator('[data-testid="request-access-section"]');
    await expect(section).toBeVisible();
    await expect(section).toContainText('New to CCS?');
    await expect(section).toContainText('qualified financial institutions');
  });

  test('should have Request Access link to contact page', async ({ page }) => {
    const requestBtn = page.locator('[data-testid="request-access-btn"]');
    await expect(requestBtn).toHaveAttribute('href', /contact\.php.*ccs-access-request/);
  });
});

// =====================================================
// CCS LOGIN - SSO SECTION TESTS
// =====================================================
test.describe('CCS Login Page - SSO Section', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/ccs-login.php');
  });

  test('should display SSO section', async ({ page }) => {
    const ssoSection = page.locator('[data-testid="sso-section"]');
    await expect(ssoSection).toBeVisible();
    await expect(ssoSection).toContainText('Or continue with');
  });

  test('should display Azure AD button (disabled state)', async ({ page }) => {
    const azureBtn = page.locator('[data-testid="sso-azure"]');
    await expect(azureBtn).toBeVisible();
    await expect(azureBtn).toContainText('Azure AD');
    await expect(azureBtn).toHaveClass(/is-disabled/);
  });

  test('should display Google Workspace button (disabled state)', async ({ page }) => {
    const googleBtn = page.locator('[data-testid="sso-google"]');
    await expect(googleBtn).toBeVisible();
    await expect(googleBtn).toContainText('Google Workspace');
    await expect(googleBtn).toHaveClass(/is-disabled/);
  });

  test('should display SSO request link', async ({ page }) => {
    const note = page.locator('.ccs-login__sso-note');
    await expect(note).toBeVisible();
    await expect(note).toContainText('Request SSO access');
    const link = note.locator('.ccs-login__sso-link');
    await expect(link).toHaveAttribute('href', /contact\.php.*sso-request/);
  });
});

// =====================================================
// CCS LOGIN - MFA STEP INDICATOR TESTS (Sprint 2)
// =====================================================
test.describe('CCS Login Page - MFA Step Indicator', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/ccs-login.php');
  });

  test('should display MFA step indicator', async ({ page }) => {
    const mfaStep = page.locator('[data-testid="mfa-step"]');
    await expect(mfaStep).toBeVisible();
    await expect(mfaStep).toContainText('Step 2 of 2');
    await expect(mfaStep).toContainText('Multi-factor authentication');
  });

  test('should display MFA privacy notice', async ({ page }) => {
    const privacy = page.locator('.ccs-login__mfa-privacy');
    await expect(privacy).toBeVisible();
    await expect(privacy).toContainText('never stored');
  });
});

// =====================================================
// CCS LOGIN - TRUST & COMPLIANCE TESTS
// =====================================================
test.describe('CCS Login Page - Trust & Compliance', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/ccs-login.php');
  });

  test('should display trust footer', async ({ page }) => {
    const footer = page.locator('[data-testid="trust-footer"]');
    await expect(footer).toBeVisible();
  });

  test('should display certification badges', async ({ page }) => {
    const certifications = page.locator('[data-testid="certifications"]');
    await expect(certifications).toBeVisible();
    
    await expect(certifications).toContainText('PCI DSS');
    await expect(certifications).toContainText('ISO 27001');
    await expect(certifications).toContainText('SOC 2');
  });

  test('should display Lerian Midaz ledger statement', async ({ page }) => {
    const statement = page.locator('.ccs-login__ledger-statement');
    await expect(statement).toBeVisible();
    await expect(statement).toContainText('Lerian Midaz ledger');
    await expect(statement).toContainText('logged immutably');
  });

  test('should display Privacy Policy link', async ({ page }) => {
    const privacyLink = page.locator('.ccs-login__legal-link', { hasText: 'Privacy Policy' });
    await expect(privacyLink).toBeVisible();
    await expect(privacyLink).toHaveAttribute('href', 'privacy.php');
  });

  test('should display Terms of Service link', async ({ page }) => {
    const termsLink = page.locator('.ccs-login__legal-link', { hasText: 'Terms of Service' });
    await expect(termsLink).toBeVisible();
    await expect(termsLink).toHaveAttribute('href', 'terms.php');
  });
});

// =====================================================
// CCS LOGIN - MOBILE VIEWPORT TESTS
// =====================================================
test.describe('CCS Login Page - Mobile (375px)', () => {
  test.use({
    viewport: { width: 375, height: 812 }
  });

  test.beforeEach(async ({ page }) => {
    await page.goto('/ccs-login.php');
  });

  test('should display login form on mobile', async ({ page }) => {
    const form = page.locator('[data-testid="ccs-login-form"]');
    await expect(form).toBeVisible();
  });

  test('should have touch-safe buttons on mobile', async ({ page }) => {
    const submitBtn = page.locator('[data-testid="ccs-submit-btn"]');
    const requestBtn = page.locator('[data-testid="request-access-btn"]');
    
    const submitBox = await submitBtn.boundingBox();
    const requestBox = await requestBtn.boundingBox();
    
    expect(submitBox?.height).toBeGreaterThanOrEqual(44);
    expect(requestBox?.height).toBeGreaterThanOrEqual(44);
  });

  test('should display SSO buttons on mobile', async ({ page }) => {
    const azureBtn = page.locator('[data-testid="sso-azure"]');
    const googleBtn = page.locator('[data-testid="sso-google"]');
    
    await expect(azureBtn).toBeVisible();
    await expect(googleBtn).toBeVisible();
    
    const azureBox = await azureBtn.boundingBox();
    const googleBox = await googleBtn.boundingBox();
    
    expect(azureBox?.height).toBeGreaterThanOrEqual(44);
    expect(googleBox?.height).toBeGreaterThanOrEqual(44);
  });

  test('should display features on mobile', async ({ page }) => {
    const features = page.locator('.ccs-login__feature');
    await expect(features.first()).toBeVisible();
  });

  test('should display header on mobile', async ({ page }) => {
    const header = page.locator('.ccs-login__header');
    await expect(header).toBeVisible();
    
    const logo = page.locator('.ccs-login__logo');
    await expect(logo).toBeVisible();
  });
});

// =====================================================
// CCS LOGIN - NARROW MOBILE (320px) TESTS
// =====================================================
test.describe('CCS Login Page - Narrow Mobile (320px)', () => {
  test.use({
    viewport: { width: 320, height: 568 }
  });

  test.beforeEach(async ({ page }) => {
    await page.goto('/ccs-login.php');
  });

  test('should not overflow on narrow screens', async ({ page }) => {
    // Check that body doesn't have horizontal scrollbar
    const bodyWidth = await page.evaluate(() => document.body.scrollWidth);
    const viewportWidth = await page.evaluate(() => window.innerWidth);
    
    expect(bodyWidth).toBeLessThanOrEqual(viewportWidth + 1); // +1 for rounding
  });

  test('should display form inputs properly on narrow mobile', async ({ page }) => {
    const emailInput = page.locator('[data-testid="ccs-email-input"]');
    const passwordInput = page.locator('[data-testid="ccs-password-input"]');
    
    await expect(emailInput).toBeVisible();
    await expect(passwordInput).toBeVisible();
    
    const emailBox = await emailInput.boundingBox();
    const passwordBox = await passwordInput.boundingBox();
    
    // Should fit within viewport (with some padding)
    expect(emailBox?.width).toBeLessThanOrEqual(300);
    expect(passwordBox?.width).toBeLessThanOrEqual(300);
  });
});

// =====================================================
// CCS LOGIN - ACCESSIBILITY TESTS
// =====================================================
test.describe('CCS Login Page - Accessibility', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/ccs-login.php');
  });

  test('should have skip link', async ({ page }) => {
    const skipLink = page.locator('.skip-link');
    await expect(skipLink).toHaveAttribute('href', '#main-content');
  });

  test('should have main landmark', async ({ page }) => {
    const main = page.locator('main#main-content');
    await expect(main).toBeVisible();
  });

  test('should have labels for form inputs', async ({ page }) => {
    const emailLabel = page.locator('label[for="ccs-email"]');
    const passwordLabel = page.locator('label[for="ccs-password"]');
    
    await expect(emailLabel).toBeVisible();
    await expect(passwordLabel).toBeVisible();
  });

  test('should have ARIA label on password toggle', async ({ page }) => {
    const toggle = page.locator('[data-testid="password-toggle"]');
    await expect(toggle).toHaveAttribute('aria-label');
  });

  test('should have proper heading hierarchy', async ({ page }) => {
    const h1 = page.locator('h1');
    const h2 = page.locator('h2');
    const h3 = page.locator('h3');
    
    await expect(h1).toHaveCount(1);
    expect(await h2.count()).toBeGreaterThanOrEqual(1);
    expect(await h3.count()).toBeGreaterThanOrEqual(1);
  });
});
