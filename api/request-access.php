<?php

declare(strict_types=1);

/**
 * Request Access API Endpoint
 *
 * Handles operator access requests with reCAPTCHA v3, honeypot, and PHPMailer.
 * Saves requests to database and sends notification to security team.
 */
header('Content-Type: application/json; charset=utf-8');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
  exit;
}

require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../includes/mail-helper.php';
require_once __DIR__ . '/../db.php';

// Timeout constants for external API calls (in seconds)
define('BBX_RECAPTCHA_TIMEOUT', 5);
define('BBX_RECAPTCHA_CONNECT_TIMEOUT', 3);
define('BBX_REQUEST_ACCESS_RATE_LIMIT_WINDOW', 900);
define('BBX_REQUEST_ACCESS_RATE_LIMIT_MAX_ATTEMPTS', 5);
define('BBX_REQUEST_ACCESS_RATE_LIMIT_MAX_BUCKETS', 512);

function bbx_request_access_string_length(string $value): int
{
  return function_exists('mb_strlen') ? (int) mb_strlen($value, 'UTF-8') : strlen($value);
}

function bbx_request_access_normalize_input(string $value, bool $allowMultiline = false): string
{
  $value = str_replace("\0", '', $value);
  $value = preg_replace("/\r\n?|\r/", "\n", $value) ?? $value;

  if ($allowMultiline) {
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value) ?? $value;
    return trim($value);
  }

  $value = preg_replace('/[\x00-\x1F\x7F]+/', ' ', $value) ?? $value;
  $value = preg_replace('/ {2,}/', ' ', $value) ?? $value;

  return trim($value);
}

function bbx_request_access_truncate_input(string $value, int $limit): string
{
  return function_exists('mb_substr')
    ? (string) mb_substr($value, 0, $limit, 'UTF-8')
    : substr($value, 0, $limit);
}

function bbx_request_access_sanitize_header_value(?string $value, int $limit = 200): string
{
  $sanitized = bbx_request_access_normalize_input((string) $value);
  if ($sanitized === '') {
    return '';
  }

  return bbx_request_access_string_length($sanitized) > $limit
    ? bbx_request_access_truncate_input($sanitized, $limit)
    : $sanitized;
}

function bbx_request_access_canonicalize_host(string $host, ?int $port = null): ?string
{
  $host = trim(strtolower($host), " \t\n\r\0\x0B[]");
  $host = rtrim($host, '.');
  if ($host === '') {
    return null;
  }

  if ($port === null || $port === 80 || $port === 443) {
    return $host;
  }

  return $host . ':' . $port;
}

function bbx_request_access_parse_host_components(string $value): ?array
{
  $sanitized = bbx_request_access_sanitize_header_value($value, 255);
  if ($sanitized === '') {
    return null;
  }

  $parts = parse_url('//' . $sanitized);
  if (!is_array($parts) || !isset($parts['host'])) {
    return null;
  }

  $port = isset($parts['port']) && is_numeric($parts['port']) ? (int) $parts['port'] : null;

  return [
    'host' => (string) $parts['host'],
    'port' => $port,
  ];
}

function bbx_request_access_log_security(string $event, array $context = []): void
{
  $originHeader = bbx_request_access_sanitize_header_value($_SERVER['HTTP_ORIGIN'] ?? null);
  $refererHeader = bbx_request_access_sanitize_header_value($_SERVER['HTTP_REFERER'] ?? null);
  $originHost = $originHeader !== '' ? bbx_request_access_extract_header_host($originHeader) : null;
  $refererHost = $refererHeader !== '' ? bbx_request_access_extract_header_host($refererHeader) : null;

  $payload = array_merge([
    'endpoint' => 'api/request-access',
    'event' => $event,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'origin_host' => $originHost ?? '',
    'origin_present' => $originHeader !== '',
    'origin_malformed' => $originHeader !== '' && $originHost === null,
    'referer_host' => $refererHost ?? '',
    'referer_present' => $refererHeader !== '',
    'referer_malformed' => $refererHeader !== '' && $refererHost === null,
    'user_agent' => bbx_request_access_sanitize_header_value($_SERVER['HTTP_USER_AGENT'] ?? null),
    'timestamp' => gmdate('c'),
  ], $context);

  $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  error_log('BBX SECURITY ' . ($encoded !== false ? $encoded : $event));
}

function bbx_request_access_supported_content_type(): bool
{
  $contentType = trim((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
  if ($contentType === '') {
    return true;
  }

  $normalized = strtolower(trim(explode(';', $contentType, 2)[0]));

  return $normalized === 'application/x-www-form-urlencoded'
    || $normalized === 'multipart/form-data';
}

function bbx_request_access_expected_host(): string
{
  $components = bbx_request_access_parse_host_components((string) ($_SERVER['HTTP_HOST'] ?? ''));
  if (!is_array($components)) {
    return '';
  }

  return bbx_request_access_canonicalize_host($components['host'], $components['port']) ?? '';
}

function bbx_request_access_expected_hostname(): string
{
  $components = bbx_request_access_parse_host_components((string) ($_SERVER['HTTP_HOST'] ?? ''));
  if (!is_array($components)) {
    return '';
  }

  return bbx_request_access_canonicalize_host($components['host']) ?? '';
}

function bbx_request_access_extract_header_host(string $headerValue): ?string
{
  $sanitized = bbx_request_access_sanitize_header_value($headerValue, 2048);
  if ($sanitized === '') {
    return null;
  }

  $parts = parse_url($sanitized);
  if (!is_array($parts) || !isset($parts['host'])) {
    return null;
  }

  $port = isset($parts['port']) && is_numeric($parts['port']) ? (int) $parts['port'] : null;

  return bbx_request_access_canonicalize_host((string) $parts['host'], $port);
}

function bbx_request_access_bound_rate_limit_store(array $buckets, int $maxBuckets, ?string $protectedIp = null): array
{
  if (count($buckets) <= $maxBuckets) {
    return $buckets;
  }

  uasort($buckets, static function (array $left, array $right): int {
    return max($right) <=> max($left);
  });

  if ($protectedIp !== null && isset($buckets[$protectedIp])) {
    $protectedBucket = [$protectedIp => $buckets[$protectedIp]];
    unset($buckets[$protectedIp]);
    $buckets = $protectedBucket + $buckets;
  }

  return array_slice($buckets, 0, $maxBuckets, true);
}

function bbx_request_access_validate_request_source(): ?string
{
  $expectedHost = bbx_request_access_expected_host();
  if ($expectedHost === '') {
    return null;
  }

  foreach (['HTTP_ORIGIN' => 'origin', 'HTTP_REFERER' => 'referer'] as $serverKey => $label) {
    $headerValue = trim((string) ($_SERVER[$serverKey] ?? ''));
    if ($headerValue === '') {
      continue;
    }

    $requestHost = bbx_request_access_extract_header_host($headerValue);
    if ($requestHost === null || !hash_equals($expectedHost, $requestHost)) {
      return $label . '_mismatch';
    }
  }

  return null;
}

function bbx_request_access_is_rate_limited(string $ip): bool
{
  $logDir = __DIR__ . '/../logs';
  if (!is_dir($logDir) && !@mkdir($logDir, 0755, true) && !is_dir($logDir)) {
    bbx_request_access_log_security('rate_limit_storage_unavailable');
    return false;
  }

  $filePath = $logDir . '/request-access-throttle.json';
  $handle = @fopen($filePath, 'c+');
  if ($handle === false) {
    bbx_request_access_log_security('rate_limit_file_open_failed');
    return false;
  }

  $limited = false;
  $now = time();
  $cutoff = $now - BBX_REQUEST_ACCESS_RATE_LIMIT_WINDOW;

  if (!flock($handle, LOCK_EX)) {
    fclose($handle);
    bbx_request_access_log_security('rate_limit_lock_failed');
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

  $pruned = bbx_request_access_bound_rate_limit_store($pruned, BBX_REQUEST_ACCESS_RATE_LIMIT_MAX_BUCKETS);

  $attempts = $pruned[$ip] ?? [];
  if (count($attempts) >= BBX_REQUEST_ACCESS_RATE_LIMIT_MAX_ATTEMPTS) {
    $limited = true;
  } else {
    $attempts[] = $now;
    $pruned[$ip] = $attempts;
    $pruned = bbx_request_access_bound_rate_limit_store($pruned, BBX_REQUEST_ACCESS_RATE_LIMIT_MAX_BUCKETS, $ip);
  }

  rewind($handle);
  ftruncate($handle, 0);
  fwrite($handle, json_encode($pruned, JSON_UNESCAPED_SLASHES));
  fflush($handle);
  flock($handle, LOCK_UN);
  fclose($handle);

  return $limited;
}

if (!bbx_request_access_supported_content_type()) {
  bbx_request_access_log_security('unsupported_content_type', [
    'content_type' => substr((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 0, 100),
  ]);
  http_response_code(415);
  echo json_encode(['success' => false, 'message' => 'Sikkerhedsvalidering fejlede.']);
  exit;
}

$sourceFailure = bbx_request_access_validate_request_source();
if ($sourceFailure !== null) {
  bbx_request_access_log_security($sourceFailure, [
    'expected_host' => bbx_request_access_expected_host(),
  ]);
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Sikkerhedsvalidering fejlede.']);
  exit;
}

$requestIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (bbx_request_access_is_rate_limited($requestIp)) {
  bbx_request_access_log_security('rate_limited');
  http_response_code(429);
  echo json_encode([
    'success' => false,
    'message' => 'Din anmodning kunne ikke behandles lige nu. Prøv igen senere.',
  ]);
  exit;
}

// Parse input
$rawInput = [
  'name'            => trim($_POST['name']            ?? ''),
  'email'           => trim($_POST['email']           ?? ''),
  'organization'    => trim($_POST['organization']    ?? ''),
  'role'            => trim($_POST['role']            ?? ''),
  'reason'          => trim($_POST['reason']          ?? ''),
  'recaptcha_token' => trim($_POST['recaptcha_token'] ?? ''),
  'website_url'     => trim($_POST['website_url']     ?? ''), // Honeypot field
];

$rawInput['name'] = bbx_request_access_normalize_input($rawInput['name']);
$rawInput['email'] = bbx_request_access_normalize_input($rawInput['email']);
$rawInput['organization'] = bbx_request_access_normalize_input($rawInput['organization']);
$rawInput['role'] = bbx_request_access_normalize_input($rawInput['role']);
$rawInput['reason'] = bbx_request_access_normalize_input($rawInput['reason'], true);
$rawInput['recaptcha_token'] = bbx_request_access_normalize_input($rawInput['recaptcha_token']);
$rawInput['website_url'] = bbx_request_access_normalize_input($rawInput['website_url']);

$fieldLimits = [
  'name' => 120,
  'email' => 254,
  'organization' => 160,
  'role' => 32,
  'reason' => 3000,
  'recaptcha_token' => 4096,
  'website_url' => 200,
];

foreach ($fieldLimits as $field => $limit) {
  if (bbx_request_access_string_length($rawInput[$field]) > $limit) {
    bbx_request_access_log_security('field_too_long', ['field' => $field, 'limit' => $limit]);
    http_response_code(422);
    echo json_encode([
      'success' => false,
      'message' => 'Ugyldige inputdata.',
    ]);
    exit;
  }
}

// ───────────────────────────────────────────────────────────────────────────
// Honeypot bot detection - silent rejection for bots that fill hidden field
// ───────────────────────────────────────────────────────────────────────────
if ($rawInput['website_url'] !== '') {
  // Bot detected - return fake success to prevent detection
  bbx_request_access_log_security('honeypot_triggered');
  error_log('REQUEST ACCESS: Honeypot triggered - bot detected');
  http_response_code(200);
  echo json_encode([
    'success' => true,
    'status'  => 'ok',
    'message' => 'Din anmodning er modtaget. Vi kontakter dig inden for 24-48 timer.',
  ]);
  exit;
}

// ───────────────────────────────────────────────────────────────────────────
// Basic validation
// ───────────────────────────────────────────────────────────────────────────
$requiredFields = ['name', 'email', 'organization', 'reason'];
$missingFields = [];

foreach ($requiredFields as $field) {
  if ($rawInput[$field] === '') {
    $missingFields[] = $field;
  }
}

if (!empty($missingFields)) {
  bbx_request_access_log_security('missing_required_fields', ['missing' => $missingFields]);
  http_response_code(422);
  echo json_encode([
    'success' => false,
    'message' => 'Udfyld venligst alle obligatoriske felter.',
    'missing' => $missingFields,
  ]);
  exit;
}

// Validate email format
if (!filter_var($rawInput['email'], FILTER_VALIDATE_EMAIL)) {
  bbx_request_access_log_security('invalid_email');
  http_response_code(422);
  echo json_encode(['success' => false, 'message' => 'Indtast en gyldig e-mailadresse.']);
  exit;
}

// Validate role if provided (must be one of allowed values)
$allowedRoles = ['observer', 'operator', 'admin', 'analyst', ''];
if (!in_array($rawInput['role'], $allowedRoles, true)) {
  bbx_request_access_log_security('invalid_role', ['role' => $rawInput['role']]);
  http_response_code(422);
  echo json_encode(['success' => false, 'message' => 'Ugyldig rolle valgt.']);
  exit;
}

// ───────────────────────────────────────────────────────────────────────────
// reCAPTCHA v3 Verification
// ───────────────────────────────────────────────────────────────────────────
$score = null;
$recaptchaRequired = BBX_RECAPTCHA_SECRET_KEY !== '';

if ($recaptchaRequired) {
  if ($rawInput['recaptcha_token'] === '') {
    error_log('REQUEST ACCESS: Missing reCAPTCHA token');
    bbx_request_access_log_security('missing_recaptcha_token');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Sikkerhedsvalidering fejlede.']);
    exit;
  }

  $verifyEndpoint = 'https://www.google.com/recaptcha/api/siteverify';
  $payload = http_build_query([
    'secret'   => BBX_RECAPTCHA_SECRET_KEY,
    'response' => $rawInput['recaptcha_token'],
    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
  ]);

  $ch = curl_init($verifyEndpoint);
  curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => BBX_RECAPTCHA_TIMEOUT,
    CURLOPT_CONNECTTIMEOUT => BBX_RECAPTCHA_CONNECT_TIMEOUT,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_SSL_VERIFYPEER => true,
  ]);

  $verifyResponse = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $curlError = curl_error($ch);
  curl_close($ch);

  if ($verifyResponse === false || $httpCode !== 200) {
    error_log('REQUEST ACCESS ERROR: reCAPTCHA API failed - HTTP ' . $httpCode . ' - ' . $curlError);
    bbx_request_access_log_security('recaptcha_api_error', ['http_code' => $httpCode]);
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Sikkerhedsvalidering fejlede.']);
    exit;
  }

  $decoded = json_decode($verifyResponse, true);
  if ($decoded === null) {
    error_log('REQUEST ACCESS ERROR: reCAPTCHA response decode failed');
    bbx_request_access_log_security('recaptcha_decode_failed');
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Sikkerhedsvalidering fejlede.']);
    exit;
  }

  $score = (float)($decoded['score'] ?? 0.0);
  $success = isset($decoded['success']) ? (bool)$decoded['success'] : false;
  $action = $decoded['action'] ?? null;
  $hostname = bbx_request_access_canonicalize_host((string) ($decoded['hostname'] ?? '')) ?? '';
  $expectedHostname = bbx_request_access_expected_hostname();
  $minScore = 0.5;
  $hostValid = true;

  if ($hostname !== '' && $expectedHostname !== '') {
    $hostValid = hash_equals($expectedHostname, $hostname);
  }

  if (!$success || $score < $minScore || $action !== 'request_access' || !$hostValid) {
    bbx_request_access_log_security('recaptcha_validation_failed', [
      'score' => $score,
      'action' => $action,
      'hostname' => $hostname,
      'expected_hostname' => $expectedHostname,
    ]);
    error_log('REQUEST ACCESS: reCAPTCHA validation failed - Score: ' . $score . ', Action: ' . $action);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Sikkerhedsvalidering fejlede.']);
    exit;
  }

  if (BBX_DEBUG_RECAPTCHA) {
    error_log('REQUEST ACCESS: reCAPTCHA passed - Score: ' . $score);
  }
}

// ───────────────────────────────────────────────────────────────────────────
// Prepare and send notification email
// ───────────────────────────────────────────────────────────────────────────
$securityEmail = bbx_env('SECURITY_EMAIL', bbx_env('CONTACT_EMAIL', 'ops@blackbox.codes'));
if ($securityEmail === '') {
  $securityEmail = 'ops@blackbox.codes';
}

$subject = 'Ny adgangsanmodning til Blackbox EYE™ Portal';

// Sanitize for email headers
$safeName = str_replace(["\r", "\n"], '', $rawInput['name']);
$safeEmail = str_replace(["\r", "\n"], '', $rawInput['email']);
$safeOrg = str_replace(["\r", "\n"], '', $rawInput['organization']);
$safeRole = $rawInput['role'] !== '' ? $rawInput['role'] : 'Ikke specificeret';
$safeReason = preg_replace("/[\r\n]+/", PHP_EOL, $rawInput['reason']);

$roleLabels = [
  'observer' => 'Observer (kun læseadgang)',
  'operator' => 'Operator (standard)',
  'admin'    => 'Administrator',
  'analyst'  => 'Analytiker',
];
$roleDisplay = $roleLabels[$rawInput['role']] ?? $safeRole;

$emailBodyLines = [
  '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━',
  '   NY ADGANGSANMODNING TIL BLACKBOX EYE™ INTELLIGENCE PORTAL',
  '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━',
  '',
  '▸ KONTAKTOPLYSNINGER',
  '  Navn:         ' . $safeName,
  '  E-mail:       ' . $safeEmail,
  '  Organisation: ' . $safeOrg,
  '',
  '▸ ØNSKET ADGANG',
  '  Rolle:        ' . $roleDisplay,
  '',
  '▸ BEGRUNDELSE',
  str_repeat('─', 60),
  $safeReason,
  str_repeat('─', 60),
  '',
  '▸ SIKKERHEDSDATA',
  '  reCAPTCHA:    ' . ($score !== null ? 'Verificeret (score: ' . number_format($score, 2) . ')' : 'Deaktiveret'),
  '  IP-adresse:   ' . ($_SERVER['REMOTE_ADDR'] ?? 'Ukendt'),
  '  Tidspunkt:    ' . date('Y-m-d H:i:s T'),
  '  User-Agent:   ' . substr($_SERVER['HTTP_USER_AGENT'] ?? 'Ukendt', 0, 100),
  '',
  '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━',
  '⚠ Alle adgangsanmodninger skal verificeres manuelt.',
  '  Svar brugeren via krypteret e-mail (PGP/GPG) når godkendt.',
  '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━',
];

$emailBody = implode(PHP_EOL, $emailBodyLines);

error_log('REQUEST ACCESS: Sending notification to ' . $securityEmail);

$mailSent = bbx_send_mail(
  $securityEmail,
  $subject,
  $emailBody,
  'Blackbox EYE Security',
  '', // Will use noreply@{domain} automatically
  $safeEmail,
  $safeName
);

if (!$mailSent) {
  error_log('REQUEST ACCESS ERROR: Mail sending failed to ' . $securityEmail);
  http_response_code(502);
  echo json_encode([
    'success' => false,
    'status'  => 'mail_error',
    'message' => 'Din anmodning blev modtaget, men vi kunne ikke sende notifikationen. Prøv igen senere.',
  ]);
  exit;
}

// ───────────────────────────────────────────────────────────────────────────
// Save request to database
// ───────────────────────────────────────────────────────────────────────────
$dbSaved = false;
$requestId = null;

if (defined('BBX_DB_CONNECTED') && BBX_DB_CONNECTED && isset($pdo)) {
  try {
    $roleValue = $rawInput['role'] !== '' ? $rawInput['role'] : 'unspecified';

    $stmt = $pdo->prepare("
      INSERT INTO access_requests
        (name, email, organization, role, reason, ip_address, user_agent, recaptcha_score, status)
      VALUES
        (:name, :email, :organization, :role, :reason, :ip_address, :user_agent, :recaptcha_score, 'pending')
    ");

    $stmt->execute([
      ':name'            => $safeName,
      ':email'           => $safeEmail,
      ':organization'    => $safeOrg,
      ':role'            => $roleValue,
      ':reason'          => $rawInput['reason'],
      ':ip_address'      => $_SERVER['REMOTE_ADDR'] ?? null,
      ':user_agent'      => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
      ':recaptcha_score' => $score,
    ]);

    $requestId = $pdo->lastInsertId();
    $dbSaved = true;

    error_log('REQUEST ACCESS: Saved to database with ID ' . $requestId);
  } catch (PDOException $e) {
    error_log('REQUEST ACCESS WARNING: Database save failed - ' . $e->getMessage());
    // Continue anyway - email was sent successfully
  }
} else {
  error_log('REQUEST ACCESS WARNING: Database not connected - request not saved');
}

error_log('REQUEST ACCESS: Successfully processed request from ' . $safeEmail);

http_response_code(200);
echo json_encode([
  'success' => true,
  'status'  => 'ok',
  'message' => 'Din anmodning er modtaget. Vores sikkerhedsteam kontakter dig inden for 24-48 timer via krypteret e-mail.',
]);
exit;
