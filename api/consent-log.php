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

const BBX_CONSENT_MAX_BODY_BYTES = 2048;
const BBX_CONSENT_RATE_LIMIT_WINDOW = 300;
const BBX_CONSENT_RATE_LIMIT_MAX_ATTEMPTS = 60;
const BBX_CONSENT_RATE_LIMIT_MAX_IPS = 500;

function bbx_consent_log_security(string $event, array $context = []): void
{
  $pairs = [];
  foreach ($context as $key => $value) {
    if (!is_scalar($value) || $value === '') {
      continue;
    }
    $pairs[] = $key . '=' . (string) $value;
  }

  error_log('consent-log security: ' . $event . (empty($pairs) ? '' : ' ' . implode(' ', $pairs)));
}

function bbx_consent_normalize_host(string $host): string
{
  $host = strtolower(trim($host));
  if ($host === '') {
    return '';
  }

  if ($host[0] === '[') {
    $end = strpos($host, ']');
    if ($end !== false) {
      return substr($host, 1, $end - 1);
    }
  }

  if (substr_count($host, ':') === 1) {
    $parts = explode(':', $host, 2);
    return $parts[0];
  }

  return $host;
}

function bbx_consent_normalize_port(?string $scheme, ?int $port): int
{
  if ($port !== null && $port > 0) {
    return $port;
  }

  return $scheme === 'http' ? 80 : 443;
}

function bbx_consent_parse_origin(string $value): ?array
{
  $parts = parse_url(trim($value));
  if (!is_array($parts) || empty($parts['host'])) {
    return null;
  }

  $scheme = isset($parts['scheme']) ? strtolower((string) $parts['scheme']) : 'https';
  $port = isset($parts['port']) ? (int) $parts['port'] : null;

  return [
    'scheme' => $scheme,
    'host' => bbx_consent_normalize_host((string) $parts['host']),
    'port' => bbx_consent_normalize_port($scheme, $port),
  ];
}

function bbx_consent_current_origin(): ?array
{
  $hostHeader = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
  if (!is_string($hostHeader) || trim($hostHeader) === '') {
    return null;
  }

  $scheme = 'https';
  if (!empty($_SERVER['REQUEST_SCHEME'])) {
    $scheme = strtolower((string) $_SERVER['REQUEST_SCHEME']);
  } elseif (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
    $scheme = 'https';
  } elseif (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 80) {
    $scheme = 'http';
  }

  $port = null;
  if ($hostHeader[0] === '[') {
    $end = strpos($hostHeader, ']');
    if ($end !== false && isset($hostHeader[$end + 1]) && $hostHeader[$end + 1] === ':') {
      $port = (int) substr($hostHeader, $end + 2);
    }
  } elseif (substr_count($hostHeader, ':') === 1) {
    $parts = explode(':', $hostHeader, 2);
    $port = (int) $parts[1];
  } elseif (isset($_SERVER['SERVER_PORT'])) {
    $port = (int) $_SERVER['SERVER_PORT'];
  }

  return [
    'scheme' => $scheme,
    'host' => bbx_consent_normalize_host($hostHeader),
    'port' => bbx_consent_normalize_port($scheme, $port),
  ];
}

function bbx_consent_is_allowed_request_origin(): bool
{
  $currentOrigin = bbx_consent_current_origin();
  if ($currentOrigin === null || $currentOrigin['host'] === '') {
    return true;
  }

  foreach (['HTTP_ORIGIN', 'HTTP_REFERER'] as $headerName) {
    $headerValue = $_SERVER[$headerName] ?? '';
    if (!is_string($headerValue) || trim($headerValue) === '') {
      continue;
    }

    $requestOrigin = bbx_consent_parse_origin($headerValue);
    if ($requestOrigin === null) {
      return false;
    }

    if (
      $requestOrigin['scheme'] !== $currentOrigin['scheme'] ||
      $requestOrigin['host'] !== $currentOrigin['host'] ||
      $requestOrigin['port'] !== $currentOrigin['port']
    ) {
      return false;
    }
  }

  return true;
}

function bbx_consent_client_ip(): string
{
  $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
  $ip = is_string($ip) ? trim($ip) : 'unknown';
  return $ip === '' ? 'unknown' : substr($ip, 0, 64);
}

function bbx_consent_normalize_text($value, int $maxLength): string
{
  if (!is_scalar($value)) {
    return '';
  }

  $text = trim((string) $value);
  $text = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $text) ?? $text;
  $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

  if (function_exists('mb_substr')) {
    return mb_substr($text, 0, $maxLength);
  }

  return substr($text, 0, $maxLength);
}

function bbx_consent_load_rate_limit_store(string $file): array
{
  if (!file_exists($file)) {
    return [];
  }

  $data = json_decode((string) @file_get_contents($file), true);
  return is_array($data) ? $data : [];
}

function bbx_consent_save_rate_limit_store(string $file, array $store): void
{
  $latestByIp = [];
  foreach ($store as $ip => $timestamps) {
    if (!is_array($timestamps) || empty($timestamps)) {
      unset($store[$ip]);
      continue;
    }
    $latestByIp[$ip] = max($timestamps);
  }

  arsort($latestByIp);
  $allowedIps = array_slice(array_keys($latestByIp), 0, BBX_CONSENT_RATE_LIMIT_MAX_IPS);
  $boundedStore = [];
  foreach ($allowedIps as $ip) {
    $boundedStore[$ip] = array_values(array_slice($store[$ip], -BBX_CONSENT_RATE_LIMIT_MAX_ATTEMPTS));
  }

  @file_put_contents($file, json_encode($boundedStore, JSON_PRETTY_PRINT), LOCK_EX);
}

function bbx_consent_enforce_rate_limit(string $file): ?int
{
  $now = time();
  $windowStart = $now - BBX_CONSENT_RATE_LIMIT_WINDOW;
  $ip = bbx_consent_client_ip();
  $store = bbx_consent_load_rate_limit_store($file);

  foreach ($store as $storedIp => $timestamps) {
    if (!is_array($timestamps)) {
      unset($store[$storedIp]);
      continue;
    }

    $filtered = [];
    foreach ($timestamps as $timestamp) {
      $timestamp = (int) $timestamp;
      if ($timestamp >= $windowStart) {
        $filtered[] = $timestamp;
      }
    }

    if (empty($filtered)) {
      unset($store[$storedIp]);
      continue;
    }

    $store[$storedIp] = $filtered;
  }

  $attempts = $store[$ip] ?? [];
  if (count($attempts) >= BBX_CONSENT_RATE_LIMIT_MAX_ATTEMPTS) {
    bbx_consent_save_rate_limit_store($file, $store);
    $oldestAttempt = min($attempts);
    return max(1, BBX_CONSENT_RATE_LIMIT_WINDOW - ($now - $oldestAttempt));
  }

  $attempts[] = $now;
  $store[$ip] = $attempts;
  bbx_consent_save_rate_limit_store($file, $store);

  return null;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}

if (!bbx_consent_is_allowed_request_origin()) {
  bbx_consent_log_security('origin_rejected', ['ip' => bbx_consent_client_ip()]);
  http_response_code(403);
  echo json_encode(['error' => 'Request origin not allowed']);
  exit;
}

$rateLimitFile = __DIR__ . '/../logs/consent-log-throttle.json';
$rateLimitDir = dirname($rateLimitFile);
if (!is_dir($rateLimitDir)) {
  @mkdir($rateLimitDir, 0755, true);
}

$retryAfter = bbx_consent_enforce_rate_limit($rateLimitFile);
if ($retryAfter !== null) {
  bbx_consent_log_security('rate_limited', ['ip' => bbx_consent_client_ip()]);
  header('Retry-After: ' . $retryAfter);
  http_response_code(429);
  echo json_encode(['error' => 'Too many requests']);
  exit;
}

// Parse JSON body
$rawInput = file_get_contents('php://input');
$inputLength = is_string($rawInput) ? strlen($rawInput) : 0;
if ($inputLength > BBX_CONSENT_MAX_BODY_BYTES) {
  bbx_consent_log_security('body_too_large', ['ip' => bbx_consent_client_ip()]);
  http_response_code(413);
  echo json_encode(['error' => 'Request body too large']);
  exit;
}

$data = json_decode($rawInput, true);

if (!is_array($data)) {
  bbx_consent_log_security('invalid_json', ['ip' => bbx_consent_client_ip()]);
  http_response_code(400);
  echo json_encode(['error' => 'Invalid JSON']);
  exit;
}

$action = bbx_consent_normalize_text($data['action'] ?? 'unknown', 32);
$level = bbx_consent_normalize_text($data['level'] ?? 'unknown', 32);

// Validate action
$validActions = ['set', 'update', 'withdraw'];
if (!in_array($action, $validActions, true)) {
  bbx_consent_log_security('invalid_action', ['ip' => bbx_consent_client_ip()]);
  http_response_code(400);
  echo json_encode(['error' => 'Invalid action']);
  exit;
}

// ═══════════════════════════════════════════════════════════════════════════════
// NON-BLOCKING RESPONSE: Send response to client immediately, then log
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
