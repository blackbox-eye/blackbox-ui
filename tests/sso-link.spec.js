/**
 * Agent Access → TS24 SSO smoke test
 *
 * Validates that the TS24 launch button appends an `sso` param
 * once an authenticated agent session is present and SSO config
 * (secret + TS24 URL) is enabled.
 */

const { test, expect } = require('@playwright/test');

const AGENT_ID = process.env.TEST_AGENT_ID || '';
const AGENT_PASSWORD = process.env.TEST_AGENT_PASSWORD || '';
const AGENT_PIN = process.env.TEST_AGENT_PIN || '';
// Canonical TS24 SSO entry - /login is manual fallback on TS24 side only
const EXPECTED_TS24_BASE = (process.env.TS24_CONSOLE_URL || 'https://intel24.tstransport.app/sso-login').replace(/\/$/, '');

async function loginAsTestAgent(page) {
  await page.goto('/agent-login.php');
  await expect(page.locator('#agent_id')).toBeVisible();

  await page.fill('#agent_id', AGENT_ID);
  await page.fill('#password', AGENT_PASSWORD);
  await page.fill('#pin', AGENT_PIN);

  await Promise.all([
    page.waitForURL('**/dashboard.php', { waitUntil: 'networkidle' }),
    page.locator('button.login-card__submit').click()
  ]);
}

test.describe('TS24 SSO link', () => {
  test.beforeEach(() => {
    test.skip(
      !AGENT_ID || !AGENT_PASSWORD || !AGENT_PIN,
      'TEST_AGENT_ID/PASSWORD/PIN must be defined for SSO smoke test'
    );
  });

  test('Agent Access TS24 button contains ?sso= token', async ({ page }) => {
    await loginAsTestAgent(page);

    await page.goto('/agent-access.php', { waitUntil: 'networkidle' });

    const ts24Link = page.locator('a[data-console-launch="ts24"]');
    await expect(ts24Link).toBeVisible();
    await expect(ts24Link).toHaveAttribute('data-sso-active', 'true');

    const href = await ts24Link.getAttribute('href');
    expect(href, 'TS24 link should include href').toBeTruthy();

    if (href) {
      expect(href.includes('sso='), 'TS24 link should include ?sso= param').toBeTruthy();
      const hrefBase = href.split('?')[0].replace(/\/$/, '');
      expect(
        hrefBase.startsWith(EXPECTED_TS24_BASE),
        `TS24 href should start with ${EXPECTED_TS24_BASE}`
      ).toBeTruthy();
    }
  });
});
