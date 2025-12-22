const { defineConfig, devices } = require('@playwright/test');
const fs = require('fs');
const path = require('path');

// Check which browsers are actually installed by looking for binaries
function isBrowserInstalled(browser) {
  const cacheDir = path.join(process.env.HOME || '', '.cache', 'ms-playwright');
  if (!fs.existsSync(cacheDir)) return false;
  
  const dirs = fs.readdirSync(cacheDir);
  return dirs.some(d => d.startsWith(browser));
}

// Detect installed browsers - chromium required, others optional locally
const hasFirefox = process.env.CI || (isBrowserInstalled('firefox') && fs.existsSync(path.join(process.env.HOME || '', '.cache', 'ms-playwright', 'firefox-1497', 'firefox', 'firefox')));
const hasWebkit = process.env.CI || (isBrowserInstalled('webkit') && fs.existsSync(path.join(process.env.HOME || '', '.cache', 'ms-playwright', 'webkit-2227', 'pw_run.sh')));

module.exports = defineConfig({
  testDir: 'tests',
  timeout: 30000,
  retries: 1,
  // The PHP built-in server is effectively single-threaded; high parallelism can
  // cause request backlogs and test timeouts. Keep CI stable by limiting workers.
  workers: process.env.CI ? 1 : undefined,
  // Multi-reporter: line for terminal, JSON for shim parsing
  reporter: [
    ['line'],
    ['json', { outputFile: 'artifacts/test-results.json' }]
  ],
  use: {
    baseURL: process.env.BASE_URL || 'http://localhost:8000',
    headless: true,
    screenshot: 'only-on-failure'
  },
  webServer: {
    command: 'php -S localhost:8000',
    url: 'http://localhost:8000',
    reuseExistingServer: !process.env.CI,
    timeout: 60000,
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] }
    },
    // Firefox/WebKit are optional - skip if browsers not installed locally
    ...(hasFirefox ? [{
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] }
    }] : []),
    ...(hasWebkit ? [{
      name: 'webkit',
      timeout: 45000,
      retries: 2,
      use: { ...devices['Desktop Safari'], navigationTimeout: 45000 }
    }] : []),
    // Note: Edge uses Chromium engine, so chromium tests cover Edge behavior
    // Brave also uses Chromium engine with privacy enhancements
    // For dark mode testing, we can use color-scheme preference
    {
      name: 'chromium-dark',
      use: {
        ...devices['Desktop Chrome'],
        colorScheme: 'dark'
      }
    },
    // Sprint 4: Mobile WebKit tests (iPhone Safari/Brave)
    ...(hasWebkit ? [{
      name: 'webkit-mobile',
      use: {
        ...devices['iPhone 14'],
        hasTouch: true,
      }
    }] : []),
  ],
  outputDir: 'artifacts'
});
