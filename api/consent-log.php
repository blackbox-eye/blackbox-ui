<?php

/**
 * Blackbox EYE™ - Consent Logging Endpoint
 *
 * Receives cookie consent events via sendBeacon
 * Logs aggregated consent data without personal identifiers
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/logging.php';

header('Content-Type: application/json; charset=utf-8');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}

// Parse JSON body
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!is_array($data)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid JSON']);
  exit;
}

$action = $data['action'] ?? 'unknown';
$level = $data['level'] ?? 'unknown';

// Validate action
$validActions = ['set', 'update', 'withdraw'];
if (!in_array($action, $validActions, true)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid action']);
  exit;
}

// Return response immediately for fast TTFB (sub-1ms target)
http_response_code(200);
echo json_encode(['success' => true]);

// Flush output to client before logging (non-blocking)
if (function_exists('fastcgi_finish_request')) {
  fastcgi_finish_request();
}
ignore_user_abort(true);

// Log consent event (no personal data) - runs after response sent
bbx_log_consent($action, [
  'consent_type' => $level,
  'categories' => $level === 'all' ? ['essential', 'analytics', 'marketing'] : ['essential'],
]);
