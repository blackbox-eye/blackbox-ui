const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
  testDir: 'tests',
  timeout: 30000,
  retries: 1,
  use: {
    baseURL: process.env.BASE_URL || 'http://localhost:8000',
    headless: true,
    screenshot: 'only-on-failure'
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] }
    },
    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] }
    },
    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] }
    },
    // Note: Edge uses Chromium engine, so chromium tests cover Edge behavior
    // Brave also uses Chromium engine with privacy enhancements
    // For dark mode testing, we can use color-scheme preference
    {
      name: 'chromium-dark',
      use: {
        ...devices['Desktop Chrome'],
        colorScheme: 'dark'
      }
    }
  ],
  outputDir: 'artifacts'
});
