<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/env.php';
require_once __DIR__ . '/includes/i18n.php';

header('Content-Type: application/json; charset=utf-8');

const BBX_SCAN_ACTION = 'lead_scan';

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

$domain = trim($_POST['domain'] ?? '');
$email = trim($_POST['email'] ?? '');
$recaptchaToken = trim($_POST['recaptcha_token'] ?? '');

if ($domain === '') {
  bbx_scan_response([
    'success' => false,
    'message' => t('free_scan.validation.domain_required', 'Indtast et domæne.'),
    'field' => 'domain',
  ], 422);
}

if (!bbx_scan_validate_domain($domain)) {
  bbx_scan_response([
    'success' => false,
    'message' => t('free_scan.validation.domain_invalid', 'Angiv et gyldigt domæne (fx example.com).'),
    'field' => 'domain',
  ], 422);
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
    bbx_scan_response([
      'success' => false,
      'message' => t('free_scan.errors.generic', 'Vi kunne ikke gennemføre scanningen. Prøv igen.'),
    ], 503);
  }

  $decoded = json_decode($verifyResponse, true);
  if (!is_array($decoded) || !($decoded['success'] ?? false)) {
    bbx_scan_response([
      'success' => false,
      'message' => t('free_scan.errors.generic', 'Vi kunne ikke gennemføre scanningen. Prøv igen.'),
    ], 400);
  }

  $recaptchaScore = isset($decoded['score']) ? (float)$decoded['score'] : null;
  $action = $decoded['action'] ?? '';
  if ($action !== '' && strcasecmp($action, BBX_SCAN_ACTION) !== 0) {
    bbx_scan_response([
      'success' => false,
      'message' => t('free_scan.errors.generic', 'Vi kunne ikke gennemføre scanningen. Prøv igen.'),
    ], 400);
  }

  if ($recaptchaScore !== null && $recaptchaScore < 0.3) {
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
