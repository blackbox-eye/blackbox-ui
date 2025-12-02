<?php

/**
 * TS24 Healthcheck Stub
 *
 * Stub response for local testing of the TS24 SSO integration health check.
 * Serve via PHP's built-in server on port 8091.
 *
 * @endpoint GET /tools/ts24_health_stub.php
 * @returns  JSON
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
// Allow local development origins only – this is a stub for local testing
header('Access-Control-Allow-Origin: http://127.0.0.1:8000');

http_response_code(200);

require_once __DIR__ . '/../includes/env.php';

$expectedIss = defined('BBX_SITE_BASE_URL') ? BBX_SITE_BASE_URL : 'https://blackbox.codes';
$expectedAud = 'ts24';

$response = [
  'stub'             => true,
  'secretConfigured' => true,
  'usesHS256'        => true,
  'expectedIss'      => $expectedIss,
  'expectedAud'      => $expectedAud,
  'recentErrors'     => [],
  'notes'            => 'TS24 stub response for local testing',
  'timestamp'        => gmdate('c'),
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}';
