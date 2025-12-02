import { test, expect } from '@playwright/test';
import type { Page, TestInfo } from '@playwright/test';

const AGENT_ID = process.env.TEST_AGENT_ID ?? '';
const AGENT_PASSWORD = process.env.TEST_AGENT_PASSWORD ?? '';
const AGENT_PIN = process.env.TEST_AGENT_PIN ?? '';
const QA_MODE_ENABLED = process.env.QA_MODE === '1' || process.env.QA_MODE === 'true';
const TS24_BASE_URL = (process.env.TS24_CONSOLE_URL || 'https://intel24.blackbox.codes/sso-login').replace(/\/$/, '');

const credentialsAvailable = Boolean(AGENT_ID && AGENT_PASSWORD && AGENT_PIN);
const diagnosticsStore = new WeakMap<TestInfo, { console: string[]; network: string[] }>();

async function loginAsOperator(page: Page) {
  await page.goto('/agent-login.php', { waitUntil: 'domcontentloaded' });
  await expect(page.locator('#agent_id')).toBeVisible();

  await page.fill('#agent_id', AGENT_ID);
  await page.fill('#password', AGENT_PASSWORD);
  await page.fill('#pin', AGENT_PIN);

  await Promise.all([
    page.waitForURL('**/dashboard.php', { waitUntil: 'networkidle' }),
    page.locator('button.login-card__submit').click()
  ]);
}

function registerDiagnostics(page: Page, testInfo: TestInfo) {
  const logBucket = { console: [] as string[], network: [] as string[] };
  page.on('console', (message) => {
    logBucket.console.push(`[${message.type()}] ${message.text()}`);
  });
  page.on('requestfinished', async (request) => {
    const response = await request.response();
    const status = response ? response.status() : 0;
    logBucket.network.push(`FINISHED ${status} ${request.method()} ${request.url()}`);
  });
  page.on('requestfailed', (request) => {
    logBucket.network.push(`FAILED ${request.method()} ${request.url()} :: ${request.failure()?.errorText ?? 'unknown error'}`);
  });
  diagnosticsStore.set(testInfo, logBucket);
}

async function attachDiagnostics(testInfo: TestInfo, page: Page) {
  const payload = diagnosticsStore.get(testInfo);
  if (!payload) {
    return;
  }
  await testInfo.attach('console-log', {
    body: payload.console.join('\n') || 'no console output',
    contentType: 'text/plain'
  });
  await testInfo.attach('network-log', {
    body: payload.network.join('\n') || 'no network events captured',
    contentType: 'text/plain'
  });
  if (testInfo.status !== testInfo.expectedStatus) {
    const screenshot = await page.screenshot({ fullPage: true });
    await testInfo.attach('failure-screenshot', {
      body: screenshot,
      contentType: 'image/png'
    });
    const guardSnapshot = await page.evaluate(() => {
      return (window as unknown as { BBXRouterGuard?: any }).BBXRouterGuard?.debug?.snapshot() ?? {};
    });
    await testInfo.attach('router-guard-state', {
      body: JSON.stringify(guardSnapshot, null, 2),
      contentType: 'application/json'
    });
  }
}

async function waitForGuard(page: Page) {
  await page.waitForFunction(() => Boolean((window as unknown as { BBXRouterGuard?: unknown }).BBXRouterGuard), null, {
    timeout: 3000
  });
}

async function issueMockToken(page: Page, payloadOverrides: Record<string, unknown> = {}, headerOverrides: Record<string, unknown> = {}) {
  return page.evaluate(({ payloadOverrides: payload, headerOverrides: header }) => {
    const baseHeader = { alg: 'HS256', typ: 'JWT', ...header };
    const basePayload = {
      iss: (window as any).BBX_ROUTER_CONFIG?.expectedIssuer || 'https://alpha.local',
      aud: (window as any).BBX_ROUTER_CONFIG?.expectedAudience || 'https://intel24.blackbox.codes/sso-login',
      iat: Math.floor(Date.now() / 1000),
      nbf: Math.floor(Date.now() / 1000),
      exp: Math.floor(Date.now() / 1000) + 600,
      ...payload
    };

    const encode = (object: Record<string, unknown>) => {
      const json = JSON.stringify(object);
      return btoa(json).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
    };

    return `${encode(baseHeader)}.${encode(basePayload)}.qa-signature`;
  }, { payloadOverrides, headerOverrides });
}

async function injectToken(page: Page, token: string, options?: { expiresAt?: number; storage?: 'session' | 'local'; fingerprint?: string }) {
  await waitForGuard(page);
  await page.evaluate(({ token, options: opts }) => {
    const guard = (window as any).BBXRouterGuard;
    guard?.debug?.injectToken(token, opts ?? {});
  }, { token, options });
}

async function evaluateNavigation(page: Page, target: string) {
  await waitForGuard(page);
  return page.evaluate((path) => {
    return (window as any).BBXRouterGuard?.evaluateNavigation(path);
  }, target);
}

async function ensureTs24Route(page: Page) {
  await page.context().route(`${TS24_BASE_URL}**`, async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'text/html',
      body: '<html><title>TS24 QA Stub</title><body>TS24 OK</body></html>'
    });
  });
}

test.describe('GUI SSO v1 (QA harness)', () => {
  test.beforeEach(async ({ page }, testInfo) => {
    test.skip(!QA_MODE_ENABLED, 'QA_MODE must be enabled for GUI SSO QA harness');
    test.skip(!credentialsAvailable, 'TEST_AGENT_* env vars are required for GUI SSO QA automation');
    registerDiagnostics(page, testInfo);
    await page.context().clearCookies();
    await page.goto('/agent-login.php', { waitUntil: 'domcontentloaded' });
    await waitForGuard(page);
  });

  test.afterEach(async ({ page }, testInfo) => {
    await attachDiagnostics(testInfo, page);
  });

  test('Core SSO flow issues token, injects storage and redirects to TS24 dashboard', async ({ page }) => {
    await ensureTs24Route(page);
    await loginAsOperator(page);

    await page.goto('/agent-access.php', { waitUntil: 'networkidle' });

    const ts24Button = page.locator('a[data-console-launch="ts24"]');
    await expect(ts24Button).toHaveAttribute('data-sso-active', 'true');

    const redirectStart = Date.now();
    const [popup] = await Promise.all([
      page.waitForEvent('popup'),
      ts24Button.click()
    ]);

    await popup.waitForLoadState('domcontentloaded');
    const redirectDuration = Date.now() - redirectStart;
    expect(redirectDuration).toBeLessThan(200);
    expect(popup.url()).toContain(TS24_BASE_URL);

    const cookies = await page.context().cookies();
    const ssoCookie = cookies.find((cookie) => cookie.name === 'gdi_sso_token');
    expect(ssoCookie?.secure).toBeTruthy();
    expect(ssoCookie?.httpOnly).toBeTruthy();
    expect((ssoCookie?.sameSite || '').toLowerCase()).toBe('lax');

    const decision = await evaluateNavigation(page, '/dashboard.php');
    expect(decision?.action).toBe('allow');
  });

  test('Missing token triggers guard redirect to login', async ({ page }) => {
    await page.evaluate(() => {
      (window as any).BBXRouterGuard?.debug?.purgeTokens();
    });
    const decision = await evaluateNavigation(page, '/dashboard.php');
    expect(decision?.action).toBe('redirect');
    expect(decision?.target).toContain('/agent-login');
    expect(decision?.reason).toBe('missing');
  });

  test('Expired token forces guard fallback', async ({ page }) => {
    const token = await issueMockToken(page, { exp: Math.floor(Date.now() / 1000) - 5 });
    await injectToken(page, token, { expiresAt: Date.now() - 5000 });
    const decision = await evaluateNavigation(page, '/dashboard.php');
    expect(decision?.reason).toBe('expired');
    expect(decision?.action).toBe('redirect');
  });

  test('Invalid signature (unsupported alg) rejected', async ({ page }) => {
    const token = await issueMockToken(page, {}, { alg: 'none' });
    await injectToken(page, token);
    const decision = await evaluateNavigation(page, '/dashboard.php');
    expect(decision?.reason).toBe('algorithm');
    expect(decision?.action).toBe('redirect');
  });

  test('Wrong issuer blocks navigation', async ({ page }) => {
    const token = await issueMockToken(page, { iss: 'https://forged.issuer' });
    await injectToken(page, token);
    const decision = await evaluateNavigation(page, '/dashboard.php');
    expect(decision?.reason).toBe('issuer');
  });

  test('Wrong audience blocks navigation', async ({ page }) => {
    const token = await issueMockToken(page, { aud: 'https://unknown.target' });
    await injectToken(page, token);
    const decision = await evaluateNavigation(page, '/dashboard.php');
    expect(decision?.reason).toBe('audience');
  });

  test('Malformed token (missing segments) rejected', async ({ page }) => {
    await injectToken(page, 'malformed-token-without-segments');
    const decision = await evaluateNavigation(page, '/dashboard.php');
    expect(decision?.reason).toBe('malformed');
    expect(decision?.action).toBe('redirect');
  });

  test('Cookie desync detected and forces logout', async ({ page }) => {
    await page.evaluate(() => {
      (window as any).BBXRouterGuard?.debug?.overrideCookieFingerprint('server-copy');
    });
    const token = await issueMockToken(page);
    await injectToken(page, token, { fingerprint: 'local-only' });
    const decision = await evaluateNavigation(page, '/agent-access.php');
    expect(decision?.reason).toBe('cookie-desync');
    expect(decision?.target).toContain('/agent-login');
  });

  test('Cold load without cache redirects to login', async ({ page }) => {
    await page.context().clearCookies();
    await page.evaluate(() => {
      window.localStorage.clear();
      window.sessionStorage.clear();
    });
    const decision = await evaluateNavigation(page, '/dashboard.php');
    expect(decision?.reason).toBe('missing');
  });

  test('Browser restart keeps session when localStorage token exists', async ({ page }) => {
    const token = await issueMockToken(page);
    await injectToken(page, token, { storage: 'local', expiresAt: Date.now() + 60_000 });
    await page.reload({ waitUntil: 'domcontentloaded' });
    const decision = await evaluateNavigation(page, '/dashboard.php');
    expect(decision?.action).toBe('allow');
    expect(decision?.reason).toBe('ok');
  });

  test('Mobile viewport (iPhone 12) SSO redirect functions', async ({ page, browserName }) => {
    test.skip(browserName !== 'webkit', 'Mobile viewport validation executed on WebKit profile');
    await ensureTs24Route(page);
    await page.setViewportSize({ width: 390, height: 844 });
    await loginAsOperator(page);
    await page.goto('/agent-access.php', { waitUntil: 'networkidle' });
    const ts24Button = page.locator('a[data-console-launch="ts24"]');
    await expect(ts24Button).toBeVisible();
    const [popup] = await Promise.all([
      page.waitForEvent('popup'),
      ts24Button.click()
    ]);
    await popup.waitForLoadState('domcontentloaded');
    expect(popup.url()).toContain(TS24_BASE_URL);
  });

  test('Performance budget respected (dashboard paint + redirect timing)', async ({ page }) => {
    await ensureTs24Route(page);
    await loginAsOperator(page);
    await page.goto('/agent-access.php', { waitUntil: 'networkidle' });
    const ts24Button = page.locator('a[data-console-launch="ts24"]');

    const clickTime = Date.now();
    const [popup] = await Promise.all([
      page.waitForEvent('popup'),
      ts24Button.click()
    ]);
    await popup.waitForLoadState('load');
    const redirectDuration = Date.now() - clickTime;
    expect(redirectDuration).toBeLessThan(200);

    const firstPaint = await page.evaluate(() => {
      const entry = performance.getEntriesByType('navigation')[0] as PerformanceNavigationTiming | undefined;
      return entry ? entry.responseStart - entry.startTime : performance.timing.responseStart - performance.timing.navigationStart;
    });
    expect(firstPaint).toBeLessThan(1200);

    const doubleNavCount = await page.evaluate(() => {
      return (window as any).BBXRouterGuard?.debug?.metrics?.doubleNavigate ?? 0;
    });
    expect(doubleNavCount).toBe(0);
  });
});
