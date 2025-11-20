<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/env.php';

header('Content-Type: application/json; charset=utf-8');

/**
 * Log contact form submission with standardized format
 */
function bbx_log_contact_submission(string $status, array $recaptcha_data = [], string $reason = '', array $extra = []): void
{
    $logDirectory = __DIR__ . '/logs';

    // Ensure log directory exists
    if (!is_dir($logDirectory)) {
        if (!mkdir($logDirectory, 0755, true)) {
            error_log('CONTACT FORM LOG ERROR: Could not create log directory: ' . $logDirectory);
            return;
        }
        error_log('CONTACT FORM: Created log directory: ' . $logDirectory);
    }

    $logFile = $logDirectory . '/contact-submissions.log';

    // Create standardized log entry
    $entry = [
        'timestamp' => gmdate('c'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'hostname' => $recaptcha_data['hostname'] ?? $_SERVER['HTTP_HOST'] ?? 'unknown',
        'action' => $recaptcha_data['action'] ?? 'contact',
        'score' => $recaptcha_data['score'] ?? null,
        'success' => $status === 'success',
        'reason' => $reason !== '' ? $reason : ($status === 'success' ? 'ok' : $status)
    ];

    // Add extra fields if provided
    if (!empty($extra)) {
        $entry = array_merge($entry, $extra);
    }

    $jsonLine = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($jsonLine === false) {
        error_log('CONTACT FORM ERROR: Could not encode log entry to JSON');
        return;
    }

    // Write to log file with error handling
    if (file_put_contents($logFile, $jsonLine . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
        $entry['success'] = false;
        $entry['reason'] = 'log_failure';
        $fallback = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: $jsonLine;
        error_log('CONTACT FORM LOG ERROR: Could not write to log file: ' . $logFile);
        error_log('CONTACT FORM LOG ERROR: Fallback entry => ' . $fallback);
    } else if (BBX_DEBUG_RECAPTCHA) {
        error_log('CONTACT FORM DEBUG: Successfully logged to: ' . $logFile);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Debug: Log environment configuration
if (BBX_DEBUG_RECAPTCHA) {
    error_log('CONTACT FORM DEBUG: Environment check');
    error_log('BBX_RECAPTCHA_SITE_KEY: ' . (BBX_RECAPTCHA_SITE_KEY ? '[SET]' : '[MISSING]'));
    error_log('BBX_RECAPTCHA_SECRET_KEY: ' . (BBX_RECAPTCHA_SECRET_KEY ? '[SET]' : '[MISSING]'));
    error_log('BBX_RECAPTCHA_PROJECT_ID: ' . (BBX_RECAPTCHA_PROJECT_ID ?: '[MISSING]'));
}

$rawInput = [
    'name' => trim($_POST['name'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'phone' => trim($_POST['phone'] ?? ''),
    'message' => trim($_POST['message'] ?? ''),
    'recaptcha_token' => trim($_POST['recaptcha_token'] ?? ''),
];

$logContext = [
    'name' => $rawInput['name'],
    'email' => $rawInput['email'],
    'phone' => $rawInput['phone'],
];

$expectedHostname = $_SERVER['HTTP_HOST'] ?? 'unknown';
$score = null;
$action = 'contact';
$hostname = $expectedHostname;
$success = false;
$recaptchaMode = 'disabled';

if ($rawInput['name'] === '' || $rawInput['email'] === '' || $rawInput['message'] === '') {
    bbx_log_contact_submission('validation_error', [], 'missing_required_fields', $logContext);
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Udfyld venligst alle obligatoriske felter.']);
    exit;
}

if (!filter_var($rawInput['email'], FILTER_VALIDATE_EMAIL)) {
    bbx_log_contact_submission('validation_error', [], 'invalid_email', $logContext);
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Indtast en gyldig e-mailadresse.']);
    exit;
}

$recaptchaRequired = BBX_RECAPTCHA_SECRET_KEY !== '';
if ($recaptchaRequired) {
    $recaptchaMode = BBX_RECAPTCHA_PROJECT_ID !== '' ? 'enterprise' : 'standard';
    // Validate environment configuration
    if (BBX_RECAPTCHA_SITE_KEY === '') {
        error_log('CONTACT FORM ERROR: RECAPTCHA_SECRET_KEY is set but RECAPTCHA_SITE_KEY is missing');
        bbx_log_contact_submission('recaptcha_error', [], 'missing_env', $logContext);
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Security validation failed.']);
        exit;
    }

    if ($rawInput['recaptcha_token'] === '') {
        if (BBX_DEBUG_RECAPTCHA) {
            error_log('CONTACT FORM DEBUG: Missing reCAPTCHA token in submission');
        }
        bbx_log_contact_submission('recaptcha_error', [], 'missing_token', $logContext);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Security validation failed.']);
        exit;
    }

    // Use Enterprise API if project ID is available, otherwise fall back to standard API
    $isEnterpriseMode = BBX_RECAPTCHA_PROJECT_ID !== '';

    if ($isEnterpriseMode) {
        $verifyEndpoint = 'https://recaptchaenterprise.googleapis.com/v1/projects/' . BBX_RECAPTCHA_PROJECT_ID . '/assessments?key=' . BBX_RECAPTCHA_SECRET_KEY;
        $payload = json_encode([
            'event' => [
                'token' => $rawInput['recaptcha_token'],
                'siteKey' => BBX_RECAPTCHA_SITE_KEY,
                'expectedAction' => 'contact'
            ]
        ]);
        $headers = ['Content-Type: application/json'];
    } else {
        $verifyEndpoint = 'https://www.google.com/recaptcha/api/siteverify';
        $payload = http_build_query([
            'secret' => BBX_RECAPTCHA_SECRET_KEY,
            'response' => $rawInput['recaptcha_token'],
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
        $headers = ['Content-Type: application/x-www-form-urlencoded'];
    }

    if (BBX_DEBUG_RECAPTCHA) {
        error_log('reCAPTCHA Debug - Mode: ' . ($isEnterpriseMode ? 'Enterprise' : 'Standard'));
        error_log('reCAPTCHA Debug - Endpoint: ' . $verifyEndpoint);
        error_log('reCAPTCHA Debug - Payload: ' . $payload);
    }

    $ch = curl_init($verifyEndpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $verifyResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($verifyResponse === false || $httpCode !== 200) {
        error_log('CONTACT FORM ERROR: reCAPTCHA API request failed - HTTP ' . $httpCode . ' - ' . $curlError);
        if (BBX_DEBUG_RECAPTCHA) {
            error_log('CONTACT FORM DEBUG: API Endpoint: ' . $verifyEndpoint);
        }
        bbx_log_contact_submission('recaptcha_error', [], 'api_error', array_merge($logContext, [
            'http_code' => $httpCode,
            'curl_error' => $curlError
        ]));
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Security validation failed.']);
        exit;
    }

    $decoded = json_decode($verifyResponse, true);
    if ($decoded === null) {
        error_log('CONTACT FORM ERROR: reCAPTCHA response decode failed - ' . json_last_error_msg());
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

    // Parse response based on API type
    if ($isEnterpriseMode) {
        $score = (float)($decoded['riskAnalysis']['score'] ?? 0.0);
        $success = isset($decoded['tokenProperties']['valid']) ? (bool)$decoded['tokenProperties']['valid'] : false;
        $action = $decoded['tokenProperties']['action'] ?? null;
        $hostname = $decoded['tokenProperties']['hostname'] ?? $expectedHostname;
        $reasons = $decoded['riskAnalysis']['reasons'] ?? [];
    } else {
        $score = (float)($decoded['score'] ?? 0.0);
        $success = isset($decoded['success']) ? (bool)$decoded['success'] : false;
        $action = $decoded['action'] ?? null;
        $hostname = $decoded['hostname'] ?? $expectedHostname;
        $reasons = $decoded['error-codes'] ?? [];
    }

    // Validate requirements
    $minScore = 0.5;
    $actionValid = ($action === 'contact');
    $hostValid = true;
    if ($hostname && $expectedHostname) {
        $hostValid = strcasecmp($hostname, $expectedHostname) === 0;
    }

    if (!$success || $score < $minScore || !$actionValid || !$hostValid) {
        $failureReasons = [];
        if (!$success) $failureReasons[] = 'invalid_token';
        if ($score < $minScore) $failureReasons[] = 'score_too_low';
        if (!$actionValid) $failureReasons[] = 'action_mismatch';
        if (!$hostValid) $failureReasons[] = 'invalid_hostname';

        $reasonText = implode('_', $failureReasons);

        if (BBX_DEBUG_RECAPTCHA) {
            error_log('CONTACT FORM DEBUG: reCAPTCHA validation failed - ' . $reasonText);
            error_log('CONTACT FORM DEBUG: Score: ' . $score . ', Action: ' . $action . ', Success: ' . ($success ? 'true' : 'false'));
        }

        bbx_log_contact_submission('recaptcha_error', [
            'score' => $score,
            'action' => $action,
            'hostname' => $hostname
        ], $reasonText, array_merge($logContext, [
            'expected_action' => 'contact',
            'min_score' => $minScore,
            'api_mode' => $isEnterpriseMode ? 'enterprise' : 'standard',
            'expected_hostname' => $expectedHostname
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
}

// Log successful submission
bbx_log_contact_submission('success', [
    'score' => $score ?? null,
    'action' => $action ?? 'contact',
    'hostname' => $hostname ?? $expectedHostname,
    'api_mode' => $recaptchaMode
], 'ok', array_merge($logContext, [
    'message_length' => strlen($rawInput['message']),
    'has_phone' => !empty($rawInput['phone']),
    'expected_hostname' => $expectedHostname
]));

http_response_code(200);
echo json_encode(['success' => true, 'status' => 'ok']);
exit;
