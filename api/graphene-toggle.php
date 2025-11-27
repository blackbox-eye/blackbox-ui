<?php

/**
 * Graphene Theme Toggle API Endpoint
 *
 * Handles AJAX requests to switch between Standard and Strong theme modes.
 * Returns JSON response with new mode and CSS variables.
 *
 * @package BlackboxEYE
 * @subpackage API
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../includes/graphene-config.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode([
    'success' => false,
    'error' => 'Method not allowed. Use POST.',
  ]);
  exit;
}

// Get request data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate mode parameter
$mode = $data['mode'] ?? null;
if (!in_array($mode, ['standard', 'strong'], true)) {
  http_response_code(400);
  echo json_encode([
    'success' => false,
    'error' => 'Invalid mode. Use "standard" or "strong".',
  ]);
  exit;
}

// Get user identifier (session or IP)
session_start();
$updated_by = $_SESSION['agent_id'] ?? $_SERVER['REMOTE_ADDR'] ?? 'anonymous';

// Save the new mode
$result = bbx_graphene_set_mode($mode, $updated_by);

if (!$result) {
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'error' => 'Failed to save theme settings.',
  ]);
  exit;
}

// Load updated settings
$settings = bbx_graphene_load_settings();

// Return success response with new settings
echo json_encode([
  'success' => true,
  'mode' => $mode,
  'body_class' => bbx_graphene_body_class(),
  'css_vars' => bbx_graphene_css_vars(),
  'colors' => $settings['colors'],
  'effects' => $settings['effects'],
  'message' => $mode === 'strong'
    ? 'Graphene Strong mode activated'
    : 'Graphene Standard mode activated',
]);
