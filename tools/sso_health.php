<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../includes/jwt_helper.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$response = [
  'sso_enabled' => false,
  'has_secret' => false,
  'has_ts24_url' => false,
  'ts24_console_url' => null,
  'ttl_seconds' => defined('BBX_JWT_TTL') ? (int) BBX_JWT_TTL : 600,
  'jwt_library_loaded' => bbx_jwt_library_available(),
  'jwt_mint_ok' => null,
  'notes' => [],
];

$secret = bbx_env('GDI_SSO_SECRET', bbx_env('JWT_SECRET', ''));
$ts24Url = defined('BBX_TS24_CONSOLE_URL') ? BBX_TS24_CONSOLE_URL : bbx_env('TS24_CONSOLE_URL', '');

$response['has_secret'] = $secret !== '';
$response['has_ts24_url'] = $ts24Url !== '';
$response['ts24_console_url'] = $ts24Url !== '' ? rtrim($ts24Url, '/') : null;
$response['sso_enabled'] = $response['has_secret'] && $response['has_ts24_url'];

if (!$response['has_secret']) {
  $response['notes'][] = 'Missing GDI_SSO_SECRET / JWT_SECRET';
}

if (!$response['has_ts24_url']) {
  $response['notes'][] = 'Missing TS24_CONSOLE_URL';
}

if (!$response['jwt_library_loaded']) {
  $response['notes'][] = 'firebase/php-jwt not available (vendor/autoload.php missing?)';
}

if ($response['sso_enabled'] && $response['jwt_library_loaded']) {
  try {
    $dummyAgent = [
      'agent_id' => 'diagnostic-agent',
      'id' => '0',
      'is_admin' => 0,
      'name' => 'Diagnostics Bot',
    ];
    $tokenBundle = bbx_issue_agent_sso_token($dummyAgent);
    $response['jwt_mint_ok'] = isset($tokenBundle['token'], $tokenBundle['expires_at']);
    if (!$response['jwt_mint_ok']) {
      $response['notes'][] = 'Token bundle missing expected keys.';
    }
  } catch (Throwable $exception) {
    $response['jwt_mint_ok'] = false;
    $response['notes'][] = 'JWT mint error: ' . $exception->getMessage();
  }
} else {
  $response['jwt_mint_ok'] = false;
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}';
