<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/env.php';
require_once __DIR__ . '/includes/i18n.php';

header('Content-Type: application/json; charset=utf-8');

const BBX_SCAN_ACTION = 'lead_scan';
const BBX_SCAN_RATE_LIMIT_WINDOW = 600;
const BBX_SCAN_RATE_LIMIT_MAX_ATTEMPTS = 8;

function bbx_scan_string_length(string $value): int
{
  return function_exists('mb_strlen') ? (int) mb_strlen($value, 'UTF-8') : strlen($value);
}

function bbx_scan_normalize_input(string $value): string
{
  $value = str_replace("\0", '', $value);
  $value = preg_replace("/\r\n?|\r/", "\n", $value) ?? $value;
  $value = preg_replace('/[\x00-\x1F\x7F]+/', ' ', $value) ?? $value;
  $value = preg_replace('/ {2,}/', ' ', $value) ?? $value;

  return trim($value);
}

function bbx_scan_log_security(string $event, array $context = []): void
{
  $payload = array_merge([
    'endpoint' => 'scan-submit',
    'event' => $event,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'origin' => substr((string) ($_SERVER['HTTP_ORIGIN'] ?? ''), 0, 200),
    'referer' => substr((string) ($_SERVER['HTTP_REFERER'] ?? ''), 0, 200),
    'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 200),
    'timestamp' => gmdate('c'),
  ], $context);

  $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  error_log('BBX SECURITY ' . ($encoded !== false ? $encoded : $event));
}

function bbx_scan_supported_content_type(): bool
{
  $contentType = trim((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
  if ($contentType === '') {
    return true;
  }

  $normalized = strtolower(trim(explode(';', $contentType, 2)[0]));

  return $normalized === 'application/x-www-form-urlencoded'
    || $normalized === 'multipart/form-data';
}

function bbx_scan_expected_host(): string
{
  return rtrim(strtolower(trim((string) ($_SERVER['HTTP_HOST'] ?? ''))), '.');
}

function bbx_scan_extract_header_host(string $headerValue): ?string
{
  $parts = parse_url($headerValue);
  $host = strtolower(trim((string) ($parts['host'] ?? '')));
  if ($host === '') {
    return null;
  }

  $port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';

  return rtrim($host, '.') . $port;
}

function bbx_scan_validate_request_source(): ?string
{
  $expectedHost = bbx_scan_expected_host();
  if ($expectedHost === '') {
    return null;
  }

  foreach (['HTTP_ORIGIN' => 'origin', 'HTTP_REFERER' => 'referer'] as $serverKey => $label) {
    $headerValue = trim((string) ($_SERVER[$serverKey] ?? ''));
    if ($headerValue === '') {
      continue;
    }

    $requestHost = bbx_scan_extract_header_host($headerValue);
    if ($requestHost === null || !hash_equals($expectedHost, $requestHost)) {
      return $label . '_mismatch';
    }
  }

  return null;
}

function bbx_scan_is_rate_limited(string $ip): bool
{
  $logDir = __DIR__ . '/logs';
  if (!is_dir($logDir) && !@mkdir($logDir, 0755, true) && !is_dir($logDir)) {
    bbx_scan_log_security('rate_limit_storage_unavailable');
    return false;
  }

  $filePath = $logDir . '/scan-submit-throttle.json';
  $handle = @fopen($filePath, 'c+');
  if ($handle === false) {
    bbx_scan_log_security('rate_limit_file_open_failed');
    return false;
  }

  $limited = false;
  $now = time();
  $cutoff = $now - BBX_SCAN_RATE_LIMIT_WINDOW;

  if (!flock($handle, LOCK_EX)) {
    fclose($handle);
    bbx_scan_log_security('rate_limit_lock_failed');
    return false;
  }

  $raw = stream_get_contents($handle);
  $data = json_decode($raw !== false && $raw !== '' ? $raw : '{}', true);
  if (!is_array($data)) {
    $data = [];
  }

  $pruned = [];
  foreach ($data as $storedIp => $timestamps) {
    if (!is_array($timestamps)) {
      continue;
    }

    $filtered = [];
    foreach ($timestamps as $timestamp) {
      $timestamp = is_numeric($timestamp) ? (int) $timestamp : 0;
      if ($timestamp >= $cutoff && $timestamp <= $now + 5) {
        $filtered[] = $timestamp;
      }
    }

    if ($filtered !== []) {
      $pruned[$storedIp] = array_values($filtered);
    }
  }

  $attempts = $pruned[$ip] ?? [];
  if (count($attempts) >= BBX_SCAN_RATE_LIMIT_MAX_ATTEMPTS) {
    $limited = true;
  } else {
    $attempts[] = $now;
    $pruned[$ip] = $attempts;
  }

  rewind($handle);
  ftruncate($handle, 0);
  fwrite($handle, json_encode($pruned, JSON_UNESCAPED_SLASHES));
  fflush($handle);
  flock($handle, LOCK_UN);
  fclose($handle);

  return $limited;
}

function bbx_scan_response(array $payload, int $status = 200): void
{
  http_response_code($status);
  echo json_encode($payload);
  exit;
}

function bbx_scan_validate_domain(string $domain): bool
{
  if ($domain === '') {
    return false;
  }

  // Basic domain validation (no protocol, no path)
  if (strpos($domain, '://') !== false) {
    return false;
  }

  $domain = strtolower($domain);
  $pattern = '/^(?!-)(?:[a-z0-9-]{1,63}\.)+[a-z]{2,}$/i';

  return (bool)preg_match($pattern, $domain);
}

function bbx_scan_log(array $entry): void
{
  $logDir = __DIR__ . '/logs';
  if (!is_dir($logDir)) {
    if (!mkdir($logDir, 0755, true) && !is_dir($logDir)) {
      return;
    }
  }

  $logFile = $logDir . '/scan-requests.log';
  $entry['timestamp'] = gmdate('c');

  file_put_contents($logFile, json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  bbx_scan_response([
    'success' => false,
    'message' => 'Method not allowed.',
  ], 405);
}

if (!bbx_scan_supported_content_type()) {
  bbx_scan_log_security('unsupported_content_type', [
    'content_type' => substr((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 0, 100),
  ]);
  bbx_scan_response([
    'success' => false,
    'message' => t('free_scan.errors.generic', 'Vi kunne ikke gennemføre scanningen. Prøv igen.'),
  ], 415);
}

$sourceFailure = bbx_scan_validate_request_source();
if ($sourceFailure !== null) {
  bbx_scan_log_security($sourceFailure, ['expected_host' => bbx_scan_expected_host()]);
  bbx_scan_response([
    'success' => false,
    'message' => t('free_scan.errors.generic', 'Vi kunne ikke gennemføre scanningen. Prøv igen.'),
  ], 403);
}

$requestIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (bbx_scan_is_rate_limited($requestIp)) {
  bbx_scan_log_security('rate_limited');
  bbx_scan_response([
    'success' => false,
    'message' => t('free_scan.errors.rate_limited', 'Du har nået maksimum for gratis scans i dag. Kontakt os for en fuld rapport.'),
  ], 429);
}

$domain = strtolower(bbx_scan_normalize_input((string) ($_POST['domain'] ?? '')));
$email = bbx_scan_normalize_input((string) ($_POST['email'] ?? ''));
$recaptchaToken = bbx_scan_normalize_input((string) ($_POST['recaptcha_token'] ?? ''));

$fieldLimits = [
  'domain' => 253,
  'email' => 254,
  'recaptcha_token' => 4096,
];

foreach ($fieldLimits as $field => $limit) {
  $value = match ($field) {
    'domain' => $domain,
    'email' => $email,
    default => $recaptchaToken,
  };

  if (bbx_scan_string_length($value) > $limit) {
    bbx_scan_log_security('field_too_long', ['field' => $field, 'limit' => $limit]);
    bbx_scan_response([
      'success' => false,
      'message' => t('free_scan.errors.generic', 'Vi kunne ikke gennemføre scanningen. Prøv igen.'),
    ], 422);
  }
}

if ($domain === '') {
  bbx_scan_log_security('missing_domain');
  bbx_scan_response([
    'success' => false,
    'message' => t('free_scan.validation.domain_required', 'Indtast et domæne.'),
    'field' => 'domain',
  ], 422);
}

if (!bbx_scan_validate_domain($domain)) {
  bbx_scan_log_security('invalid_domain');
  bbx_scan_response([
    'success' => false,
    'message' => t('free_scan.validation.domain_invalid', 'Angiv et gyldigt domæne (fx example.com).'),
    'field' => 'domain',
  ], 422);
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  bbx_scan_log_security('invalid_email');
  bbx_scan_response([
    'success' => false,
    'message' => t('free_scan.validation.email_invalid', 'Angiv en gyldig e-mailadresse.'),
    'field' => 'email',
  ], 422);
}

$recaptchaRequired = defined('BBX_RECAPTCHA_SECRET_KEY') && BBX_RECAPTCHA_SECRET_KEY !== '';
$recaptchaScore = null;

if ($recaptchaRequired) {
  if ($recaptchaToken === '') {
    bbx_scan_log_security('missing_recaptcha_token');
    bbx_scan_response([
      'success' => false,
      'message' => t('free_scan.errors.generic', 'Vi kunne ikke gennemføre scanningen. Prøv igen.'),
    ], 400);
  }

  $endpoint = 'https://www.google.com/recaptcha/api/siteverify';
  $payload = http_build_query([
    'secret' => BBX_RECAPTCHA_SECRET_KEY,
    'response' => $recaptchaToken,
    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
  ]);

  $ch = curl_init($endpoint);
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_SSL_VERIFYPEER => true,
  ]);

  $verifyResponse = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($verifyResponse === false || $httpCode !== 200) {
    bbx_scan_log_security('recaptcha_api_error', ['http_code' => $httpCode]);
    bbx_scan_response([
      'success' => false,
      'message' => t('free_scan.errors.generic', 'Vi kunne ikke gennemføre scanningen. Prøv igen.'),
    ], 503);
  }

  $decoded = json_decode($verifyResponse, true);
  if (!is_array($decoded) || !($decoded['success'] ?? false)) {
    bbx_scan_log_security('recaptcha_decode_or_success_failed');
    bbx_scan_response([
      'success' => false,
      'message' => t('free_scan.errors.generic', 'Vi kunne ikke gennemføre scanningen. Prøv igen.'),
    ], 400);
  }

  $recaptchaScore = isset($decoded['score']) ? (float)$decoded['score'] : null;
  $action = $decoded['action'] ?? '';
  $hostname = $decoded['hostname'] ?? ($_SERVER['HTTP_HOST'] ?? '');
  $expectedHostname = $_SERVER['HTTP_HOST'] ?? '';
  $hostValid = true;

  if ($hostname !== '' && $expectedHostname !== '') {
    $hostValid = strcasecmp($hostname, $expectedHostname) === 0;
  }

  if ($action !== '' && strcasecmp($action, BBX_SCAN_ACTION) !== 0) {
    bbx_scan_log_security('recaptcha_action_mismatch', ['action' => $action]);
    bbx_scan_response([
      'success' => false,
      'message' => t('free_scan.errors.generic', 'Vi kunne ikke gennemføre scanningen. Prøv igen.'),
    ], 400);
  }

  if (!$hostValid) {
    bbx_scan_log_security('recaptcha_hostname_mismatch', [
      'hostname' => $hostname,
      'expected_hostname' => $expectedHostname,
    ]);
    bbx_scan_response([
      'success' => false,
      'message' => t('free_scan.errors.generic', 'Vi kunne ikke gennemføre scanningen. Prøv igen.'),
    ], 400);
  }

  if ($recaptchaScore !== null && $recaptchaScore < 0.3) {
    bbx_scan_log_security('recaptcha_score_too_low', ['score' => $recaptchaScore]);
    bbx_scan_response([
      'success' => false,
      'message' => t('free_scan.errors.rate_limited', 'Du har nået maksimum for gratis scans i dag. Kontakt os for en fuld rapport.'),
    ], 429);
  }
}

$mockIssues = [
  [
    'severity' => 'high',
    'title' => 'TLS konfiguration accepterer forældede protokoller',
    'description' => 'Serveren understøtter TLS 1.0. Fjern legacy protokoller og aktiver TLS 1.2+.',
  ],
  [
    'severity' => 'medium',
    'title' => 'Manglende HTTP Security Headers',
    'description' => 'Content-Security-Policy og Strict-Transport-Security mangler. Tilføj hardenede sikkerhedspolitikker.',
  ],
  [
    'severity' => 'low',
    'title' => 'Eksponerede metadata i WHOIS',
    'description' => 'WHOIS-data afslører kontaktpersoner og mailservere. Overvej privat WHOIS eller registratorbeskyttelse.',
  ],
];

$scoreBase = 82;
$scoreModifier = max(0, 10 - strlen($domain) % 10);
$score = min(99, $scoreBase + $scoreModifier - count($mockIssues));

$planRecommendation = 'pricing.enterprise.standard.title';
if ($score < 70) {
  $planRecommendation = 'pricing.enterprise.premium.title';
} elseif ($score >= 90) {
  $planRecommendation = 'pricing.mvp.premium.title';
}

$response = [
  'success' => true,
  'report' => [
    'domain' => $domain,
    'score' => $score,
    'issues' => $mockIssues,
    'planRecommendation' => $planRecommendation,
    'recaptchaScore' => $recaptchaScore,
  ],
];

bbx_scan_log([
  'domain' => $domain,
  'email' => $email,
  'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
  'score' => $score,
  'issues' => array_column($mockIssues, 'severity'),
]);

bbx_scan_response($response);
