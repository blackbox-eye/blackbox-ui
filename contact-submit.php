<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/env.php';
require_once __DIR__ . '/includes/contact-log.php';
require_once __DIR__ . '/includes/mail-helper.php';

header('Content-Type: application/json; charset=utf-8');

// Timeout constants for external API calls (in seconds)
// Keep these low to avoid Cloudflare 522 timeouts
define('BBX_RECAPTCHA_TIMEOUT', 5);
define('BBX_RECAPTCHA_CONNECT_TIMEOUT', 3);
define('BBX_CONTACT_RATE_LIMIT_WINDOW', 600);
define('BBX_CONTACT_RATE_LIMIT_MAX_ATTEMPTS', 5);

function bbx_contact_string_length(string $value): int
{
    return function_exists('mb_strlen') ? (int) mb_strlen($value, 'UTF-8') : strlen($value);
}

function bbx_contact_normalize_input(string $value, bool $allowMultiline = false): string
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

function bbx_contact_log_security(string $event, array $context = []): void
{
    $payload = array_merge([
        'endpoint' => 'contact-submit',
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

function bbx_contact_supported_content_type(): bool
{
    $contentType = trim((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
    if ($contentType === '') {
        return true;
    }

    $normalized = strtolower(trim(explode(';', $contentType, 2)[0]));

    return $normalized === 'application/x-www-form-urlencoded'
        || $normalized === 'multipart/form-data';
}

function bbx_contact_expected_host(): string
{
    $host = strtolower(trim((string) ($_SERVER['HTTP_HOST'] ?? '')));
    return rtrim($host, '.');
}

function bbx_contact_extract_header_host(string $headerValue): ?string
{
    $parts = parse_url($headerValue);
    $host = strtolower(trim((string) ($parts['host'] ?? '')));
    if ($host === '') {
        return null;
    }

    $port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';

    return rtrim($host, '.') . $port;
}

function bbx_contact_validate_request_source(): ?string
{
    $expectedHost = bbx_contact_expected_host();
    if ($expectedHost === '') {
        return null;
    }

    foreach (['HTTP_ORIGIN' => 'origin', 'HTTP_REFERER' => 'referer'] as $serverKey => $label) {
        $headerValue = trim((string) ($_SERVER[$serverKey] ?? ''));
        if ($headerValue === '') {
            continue;
        }

        $requestHost = bbx_contact_extract_header_host($headerValue);
        if ($requestHost === null || !hash_equals($expectedHost, $requestHost)) {
            return $label . '_mismatch';
        }
    }

    return null;
}

function bbx_contact_is_rate_limited(string $ip): bool
{
    $logDirectory = __DIR__ . '/logs';
    if (!is_dir($logDirectory) && !@mkdir($logDirectory, 0755, true) && !is_dir($logDirectory)) {
        bbx_contact_log_security('rate_limit_storage_unavailable');
        return false;
    }

    $filePath = $logDirectory . '/contact-submit-throttle.json';
    $handle = @fopen($filePath, 'c+');
    if ($handle === false) {
        bbx_contact_log_security('rate_limit_file_open_failed');
        return false;
    }

    $limited = false;
    $now = time();
    $cutoff = $now - BBX_CONTACT_RATE_LIMIT_WINDOW;

    if (!flock($handle, LOCK_EX)) {
        fclose($handle);
        bbx_contact_log_security('rate_limit_lock_failed');
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
    if (count($attempts) >= BBX_CONTACT_RATE_LIMIT_MAX_ATTEMPTS) {
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

// Reject non-POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

if (!bbx_contact_supported_content_type()) {
    bbx_contact_log_security('unsupported_content_type', [
        'content_type' => substr((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 0, 100),
    ]);
    http_response_code(415);
    echo json_encode(['success' => false, 'message' => 'Security validation failed.']);
    exit;
}

$sourceFailure = bbx_contact_validate_request_source();
if ($sourceFailure !== null) {
    bbx_contact_log_security($sourceFailure, [
        'expected_host' => bbx_contact_expected_host(),
    ]);
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Security validation failed.']);
    exit;
}

$requestIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (bbx_contact_is_rate_limited($requestIp)) {
    bbx_contact_log_security('rate_limited');
    bbx_log_contact_submission('throttled', [], 'rate_limited', ['ip' => $requestIp]);
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => 'Din henvendelse kunne ikke behandles lige nu. Prøv igen senere.',
    ]);
    exit;
}

// Debug: Log environment configuration
if (defined('BBX_DEBUG_RECAPTCHA') && BBX_DEBUG_RECAPTCHA) {
    error_log('CONTACT FORM DEBUG: Environment check');
    error_log('BBX_RECAPTCHA_SITE_KEY: '   . (BBX_RECAPTCHA_SITE_KEY   ? '[SET]'     : '[MISSING]'));
    error_log('BBX_RECAPTCHA_SECRET_KEY: ' . (BBX_RECAPTCHA_SECRET_KEY ? '[SET]'     : '[MISSING]'));
    error_log('BBX_RECAPTCHA_PROJECT_ID: ' . (BBX_RECAPTCHA_PROJECT_ID ?: '[MISSING]'));
}

$rawInput = [
    'name'            => trim($_POST['name']            ?? ''),
    'email'           => trim($_POST['email']           ?? ''),
    'phone'           => trim($_POST['phone']           ?? ''),
    'message'         => trim($_POST['message']         ?? ''),
    'recaptcha_token' => trim($_POST['recaptcha_token'] ?? ''),
    'website_url'     => trim($_POST['website_url']     ?? ''), // Honeypot field
];

$rawInput['name'] = bbx_contact_normalize_input($rawInput['name']);
$rawInput['email'] = bbx_contact_normalize_input($rawInput['email']);
$rawInput['phone'] = bbx_contact_normalize_input($rawInput['phone']);
$rawInput['message'] = bbx_contact_normalize_input($rawInput['message'], true);
$rawInput['recaptcha_token'] = bbx_contact_normalize_input($rawInput['recaptcha_token']);
$rawInput['website_url'] = bbx_contact_normalize_input($rawInput['website_url']);

$fieldLimits = [
    'name' => 120,
    'email' => 254,
    'phone' => 64,
    'message' => 5000,
    'recaptcha_token' => 4096,
    'website_url' => 200,
];

foreach ($fieldLimits as $field => $limit) {
    if (bbx_contact_string_length($rawInput[$field]) > $limit) {
        bbx_contact_log_security('field_too_long', ['field' => $field, 'limit' => $limit]);
        bbx_log_contact_submission('validation_error', [], 'field_too_long', [
            'field' => $field,
            'limit' => $limit,
        ]);
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Ugyldige inputdata.']);
        exit;
    }
}

$logContext = [
    'name'  => $rawInput['name'],
    'email' => $rawInput['email'],
    'phone' => $rawInput['phone'],
];

$expectedHostname = $_SERVER['HTTP_HOST'] ?? 'unknown';
$score           = null;
$action          = 'contact';
$hostname        = $expectedHostname;
$success         = false;
$recaptchaMode   = 'disabled';

// ───────────────────────────────────────────────────────────────────────────
// Honeypot bot detection - silent rejection for bots that fill hidden field
// ───────────────────────────────────────────────────────────────────────────
if ($rawInput['website_url'] !== '') {
    bbx_contact_log_security('honeypot_triggered');
    // Bot detected - log silently and return fake success to avoid detection
    bbx_log_contact_submission('honeypot_triggered', [], 'bot_detected', array_merge($logContext, [
        'honeypot_value' => substr($rawInput['website_url'], 0, 100), // truncate for log safety
        'user_agent'     => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    ]));

    // Return fake success to prevent bots from adapting
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'status'  => 'ok',
        'message' => 'Tak for din henvendelse! Vi vender tilbage hurtigst muligt.',
    ]);
    exit;
}

// Basic validation
if ($rawInput['name'] === '' || $rawInput['email'] === '' || $rawInput['message'] === '') {
    bbx_contact_log_security('missing_required_fields');
    bbx_log_contact_submission('validation_error', [], 'missing_required_fields', $logContext);
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Udfyld venligst alle obligatoriske felter.']);
    exit;
}

if (!filter_var($rawInput['email'], FILTER_VALIDATE_EMAIL)) {
    bbx_contact_log_security('invalid_email');
    bbx_log_contact_submission('validation_error', [], 'invalid_email', $logContext);
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Indtast en gyldig e-mailadresse.']);
    exit;
}

// reCAPTCHA v3 - Simple and reliable
$recaptchaRequired = BBX_RECAPTCHA_SECRET_KEY !== '';

// Log reCAPTCHA configuration status
error_log('CONTACT FORM DEBUG: reCAPTCHA configured=' . ($recaptchaRequired ? 'YES' : 'NO'));

if ($recaptchaRequired) {
    $recaptchaMode = 'standard_v3';
    if (BBX_RECAPTCHA_SITE_KEY === '') {
        error_log('CONTACT FORM ERROR: RECAPTCHA_SECRET_KEY is set but RECAPTCHA_SITE_KEY is missing');
        bbx_log_contact_submission('recaptcha_error', [], 'missing_env', $logContext);
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Security validation failed.']);
        exit;
    }

    if ($rawInput['recaptcha_token'] === '') {
        error_log('CONTACT FORM WARNING: Missing reCAPTCHA token in submission');
        bbx_contact_log_security('missing_recaptcha_token');
        bbx_log_contact_submission('recaptcha_error', [], 'missing_token', $logContext);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Security validation failed.']);
        exit;
    }

    // Standard reCAPTCHA v3 API
    $verifyEndpoint = 'https://www.google.com/recaptcha/api/siteverify';
    $payload = http_build_query([
        'secret'   => BBX_RECAPTCHA_SECRET_KEY,
        'response' => $rawInput['recaptcha_token'],
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);
    $headers = ['Content-Type: application/x-www-form-urlencoded'];

    if (BBX_DEBUG_RECAPTCHA) {
        error_log('reCAPTCHA Debug - Mode: Standard v3');
        error_log('reCAPTCHA Debug - Endpoint: ' . $verifyEndpoint);
        error_log('reCAPTCHA Debug - Payload: ' . $payload);
    }

    $ch = curl_init($verifyEndpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => BBX_RECAPTCHA_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => BBX_RECAPTCHA_CONNECT_TIMEOUT,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $verifyResponse = curl_exec($ch);
    $httpCode       = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError      = curl_error($ch);
    curl_close($ch);

    if ($verifyResponse === false || $httpCode !== 200) {
        error_log('CONTACT FORM ERROR: reCAPTCHA API request failed - HTTP ' . $httpCode . ' - ' . $curlError);
        bbx_contact_log_security('recaptcha_api_error', ['http_code' => $httpCode]);
        if (BBX_DEBUG_RECAPTCHA) {
            error_log('CONTACT FORM DEBUG: API Endpoint: ' . $verifyEndpoint);
            error_log('CONTACT FORM DEBUG: API Response Body: ' . substr($verifyResponse ?: 'empty', 0, 500));
        }
        bbx_log_contact_submission('recaptcha_error', [], 'api_error', array_merge($logContext, [
            'http_code'  => $httpCode,
            'curl_error' => $curlError,
            'response_preview' => substr($verifyResponse ?: 'empty', 0, 200),
        ]));
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Security validation failed. Check server logs for details.']);
        exit;
    }

    $decoded = json_decode($verifyResponse, true);
    if ($decoded === null) {
        error_log('CONTACT FORM ERROR: reCAPTCHA response decode failed - ' . json_last_error_msg());
        bbx_contact_log_security('recaptcha_decode_failed');
        if (BBX_DEBUG_RECAPTCHA) {
            error_log('CONTACT FORM DEBUG: Raw response: ' . substr($verifyResponse, 0, 500));
        }
        bbx_log_contact_submission('recaptcha_error', [], 'decode_failed', $logContext);
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Security validation failed.']);
        exit;
    }

    if (BBX_DEBUG_RECAPTCHA) {
        error_log('reCAPTCHA Debug - Response: ' . json_encode($decoded));
    }

    // Parse Standard v3 response
    $score     = (float)($decoded['score'] ?? 0.0);
    $success   = isset($decoded['success']) ? (bool)$decoded['success'] : false;
    $action    = $decoded['action']   ?? null;
    $hostname  = $decoded['hostname'] ?? $expectedHostname;
    $reasons   = $decoded['error-codes'] ?? [];    // Validate requirements
    $minScore    = 0.5;
    $actionValid = ($action === 'contact');
    $hostValid   = true;

    if ($hostname && $expectedHostname) {
        $hostValid = strcasecmp($hostname, $expectedHostname) === 0;
    }

    if (!$success || $score < $minScore || !$actionValid || !$hostValid) {
        $failureReasons = [];
        if (!$success)        $failureReasons[] = 'invalid_token';
        if ($score < $minScore) $failureReasons[] = 'score_too_low';
        if (!$actionValid)    $failureReasons[] = 'action_mismatch';
        if (!$hostValid)      $failureReasons[] = 'invalid_hostname';

        $reasonText = implode('_', $failureReasons);

        if (BBX_DEBUG_RECAPTCHA) {
            error_log('CONTACT FORM DEBUG: reCAPTCHA validation failed - ' . $reasonText);
            error_log('CONTACT FORM DEBUG: Score: ' . $score . ', Action: ' . $action . ', Success: ' . ($success ? 'true' : 'false'));
        }

        bbx_contact_log_security('recaptcha_validation_failed', [
            'reason' => $reasonText,
            'score' => $score,
            'action' => $action,
            'hostname' => $hostname,
        ]);

        bbx_log_contact_submission('recaptcha_error', [
            'score'    => $score,
            'action'   => $action,
            'hostname' => $hostname,
        ], $reasonText, array_merge($logContext, [
            'expected_action'   => 'contact',
            'min_score'        => $minScore,
            'api_mode'         => 'standard_v3',
            'expected_hostname' => $expectedHostname,
            'reasons'          => $reasons,
        ]));

        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Security validation failed.']);
        exit;
    }

    if (BBX_DEBUG_RECAPTCHA) {
        error_log('CONTACT FORM DEBUG: reCAPTCHA validation successful - Score: ' . $score . ', Action: ' . $action);
    }
} else {
    if (BBX_DEBUG_RECAPTCHA) {
        error_log('CONTACT FORM DEBUG: reCAPTCHA disabled (no secret key)');
    }
    $recaptchaMode = 'disabled';
}

// Prepare and dispatch notification email once validation is complete
// Get mail recipient from environment, with guaranteed fallback to ops@blackbox.codes
$contactRecipient = bbx_env('CONTACT_EMAIL', 'ops@blackbox.codes');
if ($contactRecipient === '') {
    $contactRecipient = 'ops@blackbox.codes';
    error_log('CONTACT FORM WARNING: CONTACT_EMAIL not set, using default: ops@blackbox.codes');
}
// Sanitize recipient to prevent header injection
$contactRecipient = str_replace(["\r", "\n"], '', $contactRecipient);

error_log('CONTACT FORM DEBUG: mail recipient configured as: ' . $contactRecipient);

// Prepare email content
$subject = 'Ny henvendelse fra Blackbox EYE kontaktformular';

$headerSafeEmail = str_replace(["\r", "\n"], '', $rawInput['email']);
$headerSafeName = str_replace(["\r", "\n"], '', $rawInput['name']);
$sanitizedMessage = preg_replace("/[\r\n]+/", PHP_EOL, $rawInput['message']);

$emailBodyLines = [
    'Ny henvendelse fra kontaktformularen på Blackbox EYE™',
    '',
    'Navn: '  . $headerSafeName,
    'Email: ' . $headerSafeEmail,
];

if ($rawInput['phone'] !== '') {
    $emailBodyLines[] = 'Telefon: ' . str_replace(["\r", "\n"], '', $rawInput['phone']);
}

$emailBodyLines[] = '';
$emailBodyLines[] = 'Besked:';
$emailBodyLines[] = $sanitizedMessage;
$emailBodyLines[] = '';
$emailBodyLines[] = '---';
$emailBodyLines[] = 'Score: '    . ($score     !== null ? number_format((float)$score, 2) : 'N/A');
$emailBodyLines[] = 'Hostname: ' . ($hostname  ?? $expectedHostname);
$emailBodyLines[] = 'API-mode: ' . ($recaptchaMode ?? 'standard_v3');

$emailBody = implode(PHP_EOL, $emailBodyLines);

// Always log mail operations for debugging
error_log('CONTACT FORM MAIL DEBUG: about to send mail to ' . $contactRecipient);
error_log('CONTACT FORM MAIL DEBUG: subject="' . $subject . '"');

// Use robust mail helper with automatic SMTP fallback
$smtpConfigured = bbx_env('SMTP_HOST', '') !== '' && bbx_env('SMTP_USERNAME', '') !== '' && bbx_env('SMTP_PASSWORD', '') !== '';
$mailTransport = $smtpConfigured ? 'smtp' : 'mail';
$mailSent = bbx_send_mail(
    $contactRecipient,
    $subject,
    $emailBody,
    'Blackbox EYE',
    '', // Will use noreply@{domain} automatically
    $headerSafeEmail,
    $headerSafeName
);

if (!$mailSent) {
    error_log('CONTACT FORM WARNING: Mail sending failed to ' . $contactRecipient);
    error_log('CONTACT FORM WARNING: Check server mail configuration or configure SMTP credentials');
    bbx_log_contact_submission('mail_error', [
        'score'    => $score    ?? null,
        'action'   => $action   ?? 'contact',
        'hostname' => $hostname ?? $expectedHostname,
        'api_mode' => $recaptchaMode,
    ], 'mail_dispatch_failed', array_merge($logContext, [
        'message_length'   => strlen($rawInput['message']),
        'has_phone'        => !empty($rawInput['phone']),
        'expected_hostname' => $expectedHostname,
        'mail_sent'        => false,
        'mail_recipient'   => $contactRecipient,
        'mail_transport'   => $mailTransport,
    ]));
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'status'  => 'mail_error',
        'message' => 'Din henvendelse blev modtaget, men vi kunne ikke sende notifikationen. Prøv igen senere eller kontakt os direkte.',
    ]);
    exit;
} else {
    error_log('CONTACT FORM MAIL DEBUG: Mail sent successfully to ' . $contactRecipient);
}

// Log successful submission
bbx_log_contact_submission('success', [
    'score'    => $score    ?? null,
    'action'   => $action   ?? 'contact',
    'hostname' => $hostname ?? $expectedHostname,
    'api_mode' => $recaptchaMode,
], 'ok', array_merge($logContext, [
    'message_length'   => strlen($rawInput['message']),
    'has_phone'        => !empty($rawInput['phone']),
    'expected_hostname' => $expectedHostname,
    'mail_sent'        => $mailSent,
    'mail_recipient'   => $contactRecipient,
    'mail_transport'   => $mailTransport,
]));

http_response_code(200);
echo json_encode([
    'success' => true,
    'status'  => 'ok',
    'message' => 'Tak for din henvendelse! Vi vender tilbage hurtigst muligt.',
]);
exit;
