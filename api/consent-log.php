<?php

/**
 * Blackbox EYE™ - Consent Logging Endpoint
 *
 * Receives cookie consent events via sendBeacon
 * Logs aggregated consent data without personal identifiers
 *
 * Uses non-blocking response pattern to avoid holding client connections
 */

declare(strict_types=1);

// Allow script to continue after client disconnect
ignore_user_abort(true);

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

// ═══════════════════════════════════════════════════════════════════════════════
// NON-BLOCKING RESPONSE: Send response to client immediately, then log in background
// This prevents logging operations from blocking the HTTP response
// ═══════════════════════════════════════════════════════════════════════════════
http_response_code(200);
echo json_encode(['success' => true, 'status' => 'accepted']);

// Flush response to client before logging
if (function_exists('fastcgi_finish_request')) {
    // FastCGI (nginx + php-fpm): finish request and continue in background
    fastcgi_finish_request();
} else {
    // Apache/mod_php fallback: flush output buffers
    if (ob_get_level() > 0) {
        @ob_end_flush();
    }
    @flush();
}

// ═══════════════════════════════════════════════════════════════════════════════
// BACKGROUND LOGGING: Client has already received response
// ═══════════════════════════════════════════════════════════════════════════════
require_once __DIR__ . '/../includes/logging.php';

// Log consent event (no personal data)
bbx_log_consent($action, [
  'consent_type' => $level,
  'categories' => $level === 'all' ? ['essential', 'analytics', 'marketing'] : ['essential'],
]);
