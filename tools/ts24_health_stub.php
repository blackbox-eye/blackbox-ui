<?php
/**
 * TS24 Healthcheck Stub
 *
 * Stub response for local testing of the TS24 SSO integration health check.
 * This file should be served via PHP's built-in server on port 8091.
 *
 * @endpoint GET /tools/ts24_health_stub.php
 * @returns JSON
 */

header('Content-Type: application/json');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');

// Return stub health check response
$response = [
    'stub' => true,
    'secretConfigured' => true,
    'usesHS256' => true,
    'expectedIss' => 'https://blackbox.codes',
    'expectedAud' => 'ts24',
    'recentErrors' => [],
    'notes' => 'TS24 stub response for local testing',
    'timestamp' => date('c')
];

echo json_encode($response, JSON_PRETTY_PRINT);
