<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/env.php';
require_once __DIR__ . '/includes/contact-log.php';

header('Content-Type: application/json; charset=utf-8');

// Reject non-POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
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
    'name'           => trim($_POST['name']           ?? ''),
    'email'          => trim($_POST['email']          ?? ''),
    'phone'          => trim($_POST['phone']          ?? ''),
    'message'        => trim($_POST['message']        ?? ''),
    'recaptcha_token' => trim($_POST['recaptcha_token'] ?? ''),
];

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

// Basic validation
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

// reCAPTCHA
$recaptchaRequired = BBX_RECAPTCHA_SECRET_KEY !== '';
if ($recaptchaRequired) {
    $recaptchaMode = BBX_RECAPTCHA_PROJECT_ID !== '' ? 'enterprise' : 'standard';

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

    $isEnterpriseMode = BBX_RECAPTCHA_PROJECT_ID !== '';

    if ($isEnterpriseMode) {
        $verifyEndpoint = 'https://recaptchaenterprise.googleapis.com/v1/projects/' . BBX_RECAPTCHA_PROJECT_ID . '/assessments?key=' . BBX_RECAPTCHA_SECRET_KEY;
        $payload = json_encode([
            'event' => [
                'token'          => $rawInput['recaptcha_token'],
                'siteKey'        => BBX_RECAPTCHA_SITE_KEY,
                'expectedAction' => 'contact',
            ],
        ]);
        $headers = ['Content-Type: application/json'];
    } else {
        $verifyEndpoint = 'https://www.google.com/recaptcha/api/siteverify';
        $payload = http_build_query([
            'secret'   => BBX_RECAPTCHA_SECRET_KEY,
            'response' => $rawInput['recaptcha_token'],
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
        $headers = ['Content-Type: application/x-www-form-urlencoded'];
    }

    if (BBX_DEBUG_RECAPTCHA) {
        error_log('reCAPTCHA Debug - Mode: '     . ($isEnterpriseMode ? 'Enterprise' : 'Standard'));
        error_log('reCAPTCHA Debug - Endpoint: ' . $verifyEndpoint);
        error_log('reCAPTCHA Debug - Payload: '  . $payload);
    }

    $ch = curl_init($verifyEndpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $verifyResponse = curl_exec($ch);
    $httpCode       = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError      = curl_error($ch);
    curl_close($ch);

    if ($verifyResponse === false || $httpCode !== 200) {
        error_log('CONTACT FORM ERROR: reCAPTCHA API request failed - HTTP ' . $httpCode . ' - ' . $curlError);
        if (BBX_DEBUG_RECAPTCHA) {
            error_log('CONTACT FORM DEBUG: API Endpoint: ' . $verifyEndpoint);
        }
        bbx_log_contact_submission('recaptcha_error', [], 'api_error', array_merge($logContext, [
            'http_code'  => $httpCode,
            'curl_error' => $curlError,
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

    // Parse response
    if ($isEnterpriseMode) {
        $score     = (float)($decoded['riskAnalysis']['score'] ?? 0.0);
        $success   = isset($decoded['tokenProperties']['valid']) ? (bool)$decoded['tokenProperties']['valid'] : false;
        $action    = $decoded['tokenProperties']['action']   ?? null;
        $hostname  = $decoded['tokenProperties']['hostname'] ?? $expectedHostname;
        $reasons   = $decoded['riskAnalysis']['reasons']     ?? [];
    } else {
        $score     = (float)($decoded['score'] ?? 0.0);
        $success   = isset($decoded['success']) ? (bool)$decoded['success'] : false;
        $action    = $decoded['action']   ?? null;
        $hostname  = $decoded['hostname'] ?? $expectedHostname;
        $reasons   = $decoded['error-codes'] ?? [];
    }

    // Validate requirements
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

        bbx_log_contact_submission('recaptcha_error', [
            'score'    => $score,
            'action'   => $action,
            'hostname' => $hostname,
        ], $reasonText, array_merge($logContext, [
            'expected_action'   => 'contact',
            'min_score'        => $minScore,
            'api_mode'         => $isEnterpriseMode ? 'enterprise' : 'standard',
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
}

// Prepare and dispatch notification email once validation is complete
$contactRecipient = bbx_env('CONTACT_EMAIL', 'ops@blackbox.codes');
if ($contactRecipient === '') {
    $contactRecipient = 'ops@blackbox.codes';
}
$contactRecipient = str_replace(["\r", "\n"], '', $contactRecipient);

$subject     = 'Ny henvendelse fra Blackbox EYE kontaktformular';
$fromAddress = 'noreply@blackbox.codes';

$headerSafeEmail    = str_replace(["\r", "\n"], '', $rawInput['email']);
$headerSafeName     = str_replace(["\r", "\n"], '', $rawInput['name']);
$sanitizedMessage   = preg_replace("/[\r\n]+/", PHP_EOL, $rawInput['message']);

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
$emailBodyLines[] = 'API-mode: ' . $recaptchaMode;

$emailBody = implode(PHP_EOL, $emailBodyLines);
$emailBody = wordwrap($emailBody, 78, PHP_EOL);

$headers = [
    'From: Blackbox EYE <' . $fromAddress . '>',
    'Content-Type: text/plain; charset=UTF-8',
    'X-Mailer: PHP/' . phpversion(),
];

if ($headerSafeEmail !== '') {
    $headers[] = 'Reply-To: ' . $headerSafeEmail;
}

if (defined('BBX_DEBUG_RECAPTCHA') && BBX_DEBUG_RECAPTCHA) {
    error_log('CONTACT FORM MAIL DEBUG: about to send mail to ' . $contactRecipient);
}

$mailSent = mail($contactRecipient, $subject, $emailBody, implode("\r\n", $headers));

if (!$mailSent) {
    error_log('CONTACT FORM WARNING: mail() failed for contact submission to ' . $contactRecipient);
} elseif (defined('BBX_DEBUG_RECAPTCHA') && BBX_DEBUG_RECAPTCHA) {
    error_log('CONTACT FORM MAIL DEBUG: mail() dispatched to ' . $contactRecipient);
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
]));

http_response_code(200);
echo json_encode([
    'success' => true,
    'status'  => 'ok',
    'message' => 'Tak for din henvendelse! Vi vender tilbage hurtigst muligt.',
]);
exit;
