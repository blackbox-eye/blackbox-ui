<?php

/**
 * reCAPTCHA Enterprise Debug Tool
 * This file helps diagnose reCAPTCHA configuration issues
 */

require_once __DIR__ . '/includes/env.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== reCAPTCHA ENTERPRISE DEBUG ===\n\n";

// 1. Check environment variables
echo "1. ENVIRONMENT VARIABLES:\n";
echo "   RECAPTCHA_SITE_KEY: " . (BBX_RECAPTCHA_SITE_KEY !== '' ? substr(BBX_RECAPTCHA_SITE_KEY, 0, 20) . '...' : '[EMPTY]') . "\n";
echo "   RECAPTCHA_SECRET_KEY: " . (BBX_RECAPTCHA_SECRET_KEY !== '' ? substr(BBX_RECAPTCHA_SECRET_KEY, 0, 20) . '...' : '[EMPTY]') . "\n";
echo "   RECAPTCHA_PROJECT_ID: " . (BBX_RECAPTCHA_PROJECT_ID !== '' ? BBX_RECAPTCHA_PROJECT_ID : '[EMPTY]') . "\n";
echo "   RECAPTCHA_DEBUG: " . (BBX_DEBUG_RECAPTCHA ? 'true' : 'false') . "\n\n";

// 2. Check if keys are valid format
echo "2. KEY FORMAT VALIDATION:\n";
if (BBX_RECAPTCHA_SITE_KEY !== '') {
  echo "   Site Key Length: " . strlen(BBX_RECAPTCHA_SITE_KEY) . " chars\n";
  echo "   Site Key Format: " . (preg_match('/^[A-Za-z0-9_-]+$/', BBX_RECAPTCHA_SITE_KEY) ? 'Valid' : 'INVALID') . "\n";
} else {
  echo "   Site Key: NOT SET\n";
}

if (BBX_RECAPTCHA_SECRET_KEY !== '') {
  echo "   Secret Key Length: " . strlen(BBX_RECAPTCHA_SECRET_KEY) . " chars\n";
  echo "   Secret Key Format: " . (preg_match('/^[A-Za-z0-9_-]+$/', BBX_RECAPTCHA_SECRET_KEY) ? 'Valid' : 'INVALID') . "\n";
} else {
  echo "   Secret Key: NOT SET\n";
}

if (BBX_RECAPTCHA_PROJECT_ID !== '') {
  echo "   Project ID Length: " . strlen(BBX_RECAPTCHA_PROJECT_ID) . " chars\n";
  echo "   Project ID Format: " . (preg_match('/^[a-z0-9-]+$/', BBX_RECAPTCHA_PROJECT_ID) ? 'Valid' : 'INVALID') . "\n";
} else {
  echo "   Project ID: NOT SET\n";
}
echo "\n";

// 3. Build API endpoint
echo "3. API ENDPOINT:\n";
if (BBX_RECAPTCHA_PROJECT_ID !== '' && BBX_RECAPTCHA_SECRET_KEY !== '') {
  $endpoint = 'https://recaptchaenterprise.googleapis.com/v1/projects/' . BBX_RECAPTCHA_PROJECT_ID . '/assessments?key=' . BBX_RECAPTCHA_SECRET_KEY;
  echo "   Mode: Enterprise\n";
  echo "   URL: " . $endpoint . "\n\n";

  // 4. Test API endpoint with dummy data
  echo "4. TEST API CALL (with dummy token):\n";
  $testPayload = json_encode([
    'event' => [
      'token' => 'test_token_' . time(),
      'siteKey' => BBX_RECAPTCHA_SITE_KEY,
    ],
  ]);

  echo "   Payload: " . $testPayload . "\n";

  $ch = curl_init($endpoint);
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $testPayload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_SSL_VERIFYPEER => true,
  ]);

  $response = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $curlError = curl_error($ch);
  curl_close($ch);

  echo "   HTTP Code: " . $httpCode . "\n";
  echo "   cURL Error: " . ($curlError ?: 'None') . "\n";
  echo "   Response Body:\n";
  echo "   " . str_replace("\n", "\n   ", $response) . "\n\n";

  if ($httpCode === 200) {
    echo "   ✅ API Key is valid and working!\n";
  } elseif ($httpCode === 400) {
    echo "   ❌ API Key format error or invalid request\n";
  } elseif ($httpCode === 403) {
    echo "   ❌ API Key permissions error - check website restrictions!\n";
  } elseif ($httpCode === 401) {
    echo "   ❌ API Key authentication failed\n";
  } else {
    echo "   ❌ Unexpected error\n";
  }
} else {
  echo "   Mode: Standard (or incomplete Enterprise config)\n";
  echo "   Cannot test: Missing PROJECT_ID or SECRET_KEY\n";
}

echo "\n5. SERVER INFO:\n";
echo "   PHP Version: " . phpversion() . "\n";
echo "   cURL Enabled: " . (function_exists('curl_version') ? 'Yes' : 'No') . "\n";
if (function_exists('curl_version')) {
  $curlVersion = curl_version();
  echo "   cURL Version: " . $curlVersion['version'] . "\n";
  echo "   SSL Version: " . $curlVersion['ssl_version'] . "\n";
}
echo "   HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'unknown') . "\n";
echo "   SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'unknown') . "\n";
echo "   REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'unknown') . "\n";

echo "\n=== END DEBUG ===\n";
