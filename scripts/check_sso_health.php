#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/sso_audit.php';

const DEFAULT_BASE_URL = 'http://127.0.0.1:8080';
const DEFAULT_TS24_PATH = '/api/auth/sso-health';

function build_context(): array
{
  return [
    'http' => [
      'timeout' => 8,
      'ignore_errors' => true,
      'header' => "User-Agent: SSOStackHealth/1.0\r\nAccept: application/json\r\n",
    ],
  ];
}

function fetch_http(string $url): array
{
  global $http_response_header;
  $context = stream_context_create(build_context());
  $body = @file_get_contents($url, false, $context);
  $headers = $http_response_header ?? [];
  $status = 0;
  if (isset($headers[0]) && preg_match('/HTTP\/[^\s]+\s+(\d{3})/i', $headers[0], $matches)) {
    $status = (int) $matches[1];
  }

  return [$body, $status];
}

$argvBase = $argv[1] ?? null;
$envBase = getenv('SSO_HEALTH_BASE_URL') ?: null;
$baseUrl = rtrim($argvBase ?: ($envBase ?: DEFAULT_BASE_URL), '/');
$healthUrl = $baseUrl . '/tools/sso_health.php';

[$payload, $gdiStatus] = fetch_http($healthUrl);
if ($payload === false) {
  fwrite(STDERR, "Unable to reach {$healthUrl} (HTTP {$gdiStatus}). Ensure the server is running.\n");
  echo "GDI SSO: FAIL - unreachable (HTTP {$gdiStatus})\n";
  echo "TS24 SSO: SKIPPED - GUI health unavailable\n";
  exit(2);
}

$data = json_decode($payload, true);
if (!is_array($data)) {
  fwrite(STDERR, "Unexpected response from {$healthUrl}: " . trim($payload) . "\n");
  echo "GDI SSO: FAIL - invalid JSON\n";
  echo "TS24 SSO: SKIPPED - GUI health invalid\n";
  exit(3);
}

$required = [
  'sso_enabled' => true,
  'has_secret' => true,
  'has_ts24_url' => true,
  'jwt_mint_ok' => true,
];

$failed = [];
foreach ($required as $key => $expected) {
  if (!array_key_exists($key, $data) || (bool) $data[$key] !== $expected) {
    $failed[] = $key;
  }
}

$gdiHealthy = $failed === [];
$gdiSummary = $gdiHealthy ? 'OK' : ('Failed flags: ' . implode(', ', $failed));
$gdiNotes = isset($data['notes']) && is_array($data['notes']) ? implode('; ', $data['notes']) : null;
if ($gdiNotes) {
  $gdiSummary .= " (notes: {$gdiNotes})";
}

$ts24Base = null;
if (!empty($data['ts24_console_url'])) {
  $ts24Base = $data['ts24_console_url'];
} elseif ($envTs24 = getenv('TS24_CONSOLE_URL')) {
  $ts24Base = rtrim($envTs24, '/');
}

$ts24Healthy = null;
$ts24Summary = 'Skipped - TS24 URL not configured';
if ($ts24Base) {
  $ts24HealthUrl = rtrim($ts24Base, '/') . DEFAULT_TS24_PATH;
  [$ts24Payload, $ts24Status] = fetch_http($ts24HealthUrl);

  if ($ts24Payload === false || $ts24Status >= 400) {
    $ts24Healthy = false;
    $ts24Summary = "Endpoint unreachable (HTTP {$ts24Status})";
  } else {
    $ts24Data = json_decode($ts24Payload, true);
    if (!is_array($ts24Data)) {
      $ts24Healthy = false;
      $ts24Summary = 'Invalid JSON from TS24 health endpoint';
    } else {
      $ts24Issues = [];
      if (empty($ts24Data['secretConfigured'])) {
        $ts24Issues[] = 'secretConfigured=false';
      }
      if (empty($ts24Data['usesHS256'])) {
        $ts24Issues[] = 'usesHS256=false';
      }

      $ts24Notes = [];
      if (empty($ts24Data['expectedIss'])) {
        $ts24Notes[] = 'expectedIss empty';
      }
      if (empty($ts24Data['expectedAud'])) {
        $ts24Notes[] = 'expectedAud empty';
      }
      if (!empty($ts24Data['recentErrors']) && is_array($ts24Data['recentErrors'])) {
        $recentList = array_slice(array_filter($ts24Data['recentErrors']), 0, 5);
        if ($recentList) {
          $ts24Notes[] = 'recentErrors=' . implode(',', $recentList);
        }
      }

      $ts24Healthy = $ts24Issues === [];
      $ts24Summary = $ts24Healthy ? 'OK' : ('Issues: ' . implode(', ', $ts24Issues));
      if ($ts24Notes) {
        $ts24Summary .= ' (notes: ' . implode(' | ', $ts24Notes) . ')';
      }
    }
  }
}

echo 'GDI SSO: ' . $gdiSummary . PHP_EOL;
echo 'TS24 SSO: ' . $ts24Summary . PHP_EOL;

$exitCode = 0;
if (!$gdiHealthy) {
  $exitCode = 4;
}
if ($ts24Healthy === false) {
  $exitCode = $exitCode === 0 ? 5 : $exitCode;
}

if ($exitCode !== 0) {
  bbx_log_sso_event('SSO_STACK_HEALTH_FAIL', [
    'gdi_status' => $gdiSummary,
    'ts24_status' => $ts24Summary,
    'ts24_url' => $ts24Base,
    'base_url' => $baseUrl,
  ]);
  fwrite(STDERR, "Stack healthcheck failed. See summary above.\n");
}

exit($exitCode);
