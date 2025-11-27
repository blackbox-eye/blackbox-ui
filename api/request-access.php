<?php

declare(strict_types=1);

/**
 * Request Access API Endpoint
 *
 * Handles operator access requests with reCAPTCHA v3, honeypot, and PHPMailer.
 * Sends notification to security team when a valid request is submitted.
 */

require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../includes/mail-helper.php';

header('Content-Type: application/json; charset=utf-8');

// Timeout constants for external API calls (in seconds)
define('BBX_RECAPTCHA_TIMEOUT', 5);
define('BBX_RECAPTCHA_CONNECT_TIMEOUT', 3);

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
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

// ───────────────────────────────────────────────────────────────────────────
// Honeypot bot detection - silent rejection for bots that fill hidden field
// ───────────────────────────────────────────────────────────────────────────
if ($rawInput['website_url'] !== '') {
    // Bot detected - return fake success to prevent detection
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
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Indtast en gyldig e-mailadresse.']);
    exit;
}

// Validate role if provided (must be one of allowed values)
$allowedRoles = ['observer', 'operator', 'admin', 'analyst', ''];
if (!in_array($rawInput['role'], $allowedRoles, true)) {
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
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Sikkerhedsvalidering fejlede.']);
        exit;
    }

    $decoded = json_decode($verifyResponse, true);
    if ($decoded === null) {
        error_log('REQUEST ACCESS ERROR: reCAPTCHA response decode failed');
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Sikkerhedsvalidering fejlede.']);
        exit;
    }

    $score = (float)($decoded['score'] ?? 0.0);
    $success = isset($decoded['success']) ? (bool)$decoded['success'] : false;
    $action = $decoded['action'] ?? null;
    $minScore = 0.5;

    if (!$success || $score < $minScore || $action !== 'request_access') {
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

$subject = 'Ny adgangsanmodning til GreyEYE™ Portal';

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
    '   NY ADGANGSANMODNING TIL GREYEYE™ PORTAL',
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
    'GreyEYE Security',
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

error_log('REQUEST ACCESS: Successfully processed request from ' . $safeEmail);

http_response_code(200);
echo json_encode([
    'success' => true,
    'status'  => 'ok',
    'message' => 'Din anmodning er modtaget. Vores sikkerhedsteam kontakter dig inden for 24-48 timer via krypteret e-mail.',
]);
exit;
