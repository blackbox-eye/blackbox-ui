<?php
/**
 * SSO Request API Endpoint
 * 
 * Handles SSO access requests for enterprise tenants.
 * Sprint 3: Mock endpoint that returns success + logs activity.
 * 
 * POST /api/sso-request.php
 * Body: company, email, domain, provider, notes, console
 * Returns: { ok: true, request_id: "SSO-XXXXX" }
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Parse input (support both JSON and form data)
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
} else {
    $input = $_POST;
}

$data = [
    'company'  => trim($input['company'] ?? ''),
    'email'    => trim($input['email'] ?? ''),
    'domain'   => trim($input['domain'] ?? ''),
    'provider' => trim($input['provider'] ?? ''),
    'notes'    => trim($input['notes'] ?? ''),
    'console'  => trim($input['console'] ?? 'ccs'),
];

// Validate required fields
$requiredFields = ['company', 'email'];
$missing = [];
foreach ($requiredFields as $field) {
    if ($data[$field] === '') {
        $missing[] = $field;
    }
}

if (!empty($missing)) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'error' => 'Missing required fields',
        'missing' => $missing,
    ]);
    exit;
}

// Validate email
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Invalid email address']);
    exit;
}

// Validate provider if provided
$allowedProviders = ['azure', 'google', 'okta', 'other', ''];
if (!in_array($data['provider'], $allowedProviders, true)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Invalid provider']);
    exit;
}

// Generate request ID
$requestId = 'SSO-' . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 8));

// Log the request (Sprint 3 mock - in production, save to DB)
$logEntry = [
    'request_id'  => $requestId,
    'type'        => 'sso_request',
    'data'        => $data,
    'ip'          => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'timestamp'   => date('c'),
];

// Log to file for Sprint 3 (production would use DB)
$logFile = __DIR__ . '/../logs/sso-requests.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
@file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);

// Return success
echo json_encode([
    'ok'         => true,
    'request_id' => $requestId,
    'message'    => 'SSO request received. Our team will contact you within 24-48 hours.',
    'console'    => $data['console'],
]);
