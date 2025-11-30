/**
 * SSO Health Check Script
 *
 * Validates that the SSO system components (GDI and TS24) are accessible
 * and returning valid responses.
 *
 * Usage: npm run sso:health
 */

const http = require('http');

// Configuration
const REQUEST_TIMEOUT_MS = 5000;

const HEALTH_ENDPOINTS = [
  {
    name: 'GDI',
    url: 'http://127.0.0.1:8000',
    description: 'Main GUI application'
  },
  {
    name: 'TS24',
    url: 'http://127.0.0.1:8091/tools/ts24_health_stub.php',
    description: 'TS24 SSO integration'
  }
];

/**
 * Check health of a single endpoint
 * @param {Object} endpoint - Endpoint configuration
 * @returns {Promise<Object>} Health check result
 */
function checkEndpoint(endpoint) {
  return new Promise((resolve) => {
    const startTime = Date.now();
    const request = http.get(endpoint.url, { timeout: REQUEST_TIMEOUT_MS }, (res) => {
      const latency = Date.now() - startTime;
      let data = '';

      res.on('data', (chunk) => {
        data += chunk;
      });

      res.on('end', () => {
        let jsonResponse = null;
        try {
          jsonResponse = JSON.parse(data);
        } catch {
          // Not JSON, that's okay for some endpoints
        }

        resolve({
          name: endpoint.name,
          description: endpoint.description,
          status: res.statusCode >= 200 && res.statusCode < 400 ? 'OK' : 'ERROR',
          statusCode: res.statusCode,
          latency: `${latency}ms`,
          response: jsonResponse,
          url: endpoint.url
        });
      });
    });

    request.on('error', (err) => {
      resolve({
        name: endpoint.name,
        description: endpoint.description,
        status: 'UNAVAILABLE',
        error: err.message,
        url: endpoint.url
      });
    });

    request.on('timeout', () => {
      request.destroy();
      resolve({
        name: endpoint.name,
        description: endpoint.description,
        status: 'TIMEOUT',
        error: `Request timed out after ${REQUEST_TIMEOUT_MS}ms`,
        url: endpoint.url
      });
    });
  });
}

/**
 * Run health checks on all endpoints
 */
async function runHealthChecks() {
  console.log('\n🔍 SSO Health Check\n');
  console.log('='.repeat(50));

  const results = [];
  let allOk = true;

  for (const endpoint of HEALTH_ENDPOINTS) {
    const result = await checkEndpoint(endpoint);
    results.push(result);

    const statusIcon = result.status === 'OK' ? '✅' : '❌';
    console.log(`\n${statusIcon} ${result.name} (${result.description})`);
    console.log(`   URL: ${result.url}`);
    console.log(`   Status: ${result.status}`);

    if (result.statusCode) {
      console.log(`   HTTP Code: ${result.statusCode}`);
    }
    if (result.latency) {
      console.log(`   Latency: ${result.latency}`);
    }
    if (result.error) {
      console.log(`   Error: ${result.error}`);
    }
    if (result.response && result.name === 'TS24') {
      // Validate TS24 stub response
      const ts24 = result.response;
      console.log(`   Stub: ${ts24.stub ? 'Yes' : 'No'}`);
      console.log(`   Secret Configured: ${ts24.secretConfigured ? 'Yes' : 'No'}`);
      console.log(`   Uses HS256: ${ts24.usesHS256 ? 'Yes' : 'No'}`);
      console.log(`   Expected Issuer: ${ts24.expectedIss}`);
      console.log(`   Expected Audience: ${ts24.expectedAud}`);
      console.log(`   Recent Errors: ${ts24.recentErrors?.length || 0}`);
    }

    if (result.status !== 'OK') {
      allOk = false;
    }
  }

  console.log('\n' + '='.repeat(50));

  if (allOk) {
    console.log('\n✅ All health checks passed!\n');
    process.exit(0);
  } else {
    console.log('\n❌ Some health checks failed!\n');
    console.log('Make sure both servers are running:');
    console.log('  1. PHP server on port 8000: php -S localhost:8000');
    console.log('  2. PHP server on port 8091: php -S 127.0.0.1:8091\n');
    process.exit(1);
  }
}

// Run health checks
runHealthChecks();
