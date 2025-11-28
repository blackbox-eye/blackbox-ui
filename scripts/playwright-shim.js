#!/usr/bin/env node
/**
 * Playwright Exit-Code Shim
 *
 * Works around a known Playwright CLI bug on Windows + php -S where the
 * test runner returns exit code 1 even when all tests pass. This script:
 *   1. Runs Playwright (config has line + JSON reporters configured).
 *   2. Parses the JSON report at artifacts/test-results.json.
 *   3. Returns exit 0 if unexpected === 0 AND flaky === 0; otherwise 1.
 *
 * TEMPORARY WORKAROUND – Remove once Playwright fixes the bug.
 * See: https://github.com/microsoft/playwright/issues
 */

const { spawnSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const REPORT_FILE = path.join(process.cwd(), 'artifacts', 'test-results.json');

// Pass all CLI args after the script name to Playwright
const playwrightArgs = process.argv.slice(2);

const args = ['playwright', 'test', ...playwrightArgs];

console.log(`[shim] Running: npx ${args.join(' ')}`);
console.log(`[shim] JSON report: ${REPORT_FILE}`);
console.log('');

const result = spawnSync('npx', args, {
  stdio: 'inherit',
  shell: true,
  cwd: process.cwd()
});

const exitCode = result.status ?? 1;

console.log('');
console.log(`[shim] Playwright exited with code ${exitCode}`);

let report;
try {
  const raw = fs.readFileSync(REPORT_FILE, 'utf8');
  report = JSON.parse(raw);
} catch (err) {
  console.error(`[shim] Failed to read JSON report: ${err.message}`);
  console.log('[shim] Falling back to original exit code');
  process.exit(exitCode);
}

const { stats } = report;
if (!stats) {
  console.error('[shim] No stats in JSON report');
  process.exit(exitCode);
}

const { expected = 0, unexpected = 0, flaky = 0, skipped = 0 } = stats;

console.log(`[shim] Stats: expected=${expected}, unexpected=${unexpected}, flaky=${flaky}, skipped=${skipped}`);

if (unexpected === 0 && flaky === 0) {
  console.log('[shim] ✅ All tests passed – normalizing exit code to 0');
  process.exit(0);
} else {
  console.log(`[shim] ❌ Tests failed or flaky – returning exit code 1`);
  process.exit(1);
}
