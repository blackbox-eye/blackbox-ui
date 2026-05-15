<?php
/**
 * Intel24 Access Request API
 * 
 * POST /api/intel24-request.php - Submit Intel24 access request
 * 
 * Stores requests in JSON file (production would use database)
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

const BBX_I24_MAX_BODY_BYTES = 16384;
const BBX_I24_RATE_LIMIT_WINDOW = 600;
const BBX_I24_RATE_LIMIT_MAX_ATTEMPTS = 5;
const BBX_I24_RATE_LIMIT_MAX_IPS = 500;

function bbx_i24_log_security(string $event, array $context = []): void
{
    $pairs = [];
    foreach ($context as $key => $value) {
        if (!is_scalar($value) || $value === '') {
            continue;
        }
        $pairs[] = $key . '=' . (string) $value;
    }

    error_log('intel24-request security: ' . $event . (empty($pairs) ? '' : ' ' . implode(' ', $pairs)));
}

function bbx_i24_normalize_host(string $host): string
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

function bbx_i24_normalize_port(?string $scheme, ?int $port): int
{
    if ($port !== null && $port > 0) {
        return $port;
    }

    return $scheme === 'http' ? 80 : 443;
}

function bbx_i24_parse_origin(string $value): ?array
{
    $parts = parse_url(trim($value));
    if (!is_array($parts) || empty($parts['host'])) {
        return null;
    }

    $scheme = isset($parts['scheme']) ? strtolower((string) $parts['scheme']) : 'https';
    $port = isset($parts['port']) ? (int) $parts['port'] : null;

    return [
        'scheme' => $scheme,
        'host' => bbx_i24_normalize_host((string) $parts['host']),
        'port' => bbx_i24_normalize_port($scheme, $port),
    ];
}

function bbx_i24_current_origin(): ?array
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
        'host' => bbx_i24_normalize_host($hostHeader),
        'port' => bbx_i24_normalize_port($scheme, $port),
    ];
}

function bbx_i24_is_allowed_request_origin(): bool
{
    $currentOrigin = bbx_i24_current_origin();
    if ($currentOrigin === null || $currentOrigin['host'] === '') {
        return true;
    }

    foreach (['HTTP_ORIGIN', 'HTTP_REFERER'] as $headerName) {
        $headerValue = $_SERVER[$headerName] ?? '';
        if (!is_string($headerValue) || trim($headerValue) === '') {
            continue;
        }

        $requestOrigin = bbx_i24_parse_origin($headerValue);
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

function bbx_i24_apply_cors_headers(): void
{
    $originHeader = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (!is_string($originHeader) || trim($originHeader) === '') {
        return;
    }

    $currentOrigin = bbx_i24_current_origin();
    $requestOrigin = bbx_i24_parse_origin($originHeader);
    if ($currentOrigin === null || $requestOrigin === null) {
        return;
    }

    if (
        $requestOrigin['scheme'] === $currentOrigin['scheme'] &&
        $requestOrigin['host'] === $currentOrigin['host'] &&
        $requestOrigin['port'] === $currentOrigin['port']
    ) {
        header('Access-Control-Allow-Origin: ' . trim($originHeader));
        header('Vary: Origin');
    }
}

function bbx_i24_client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ip = is_string($ip) ? trim($ip) : 'unknown';
    return $ip === '' ? 'unknown' : substr($ip, 0, 64);
}

function bbx_i24_normalize_text($value, int $maxLength): string
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

function bbx_i24_load_rate_limit_store(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }

    $data = json_decode((string) file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function bbx_i24_save_rate_limit_store(string $file, array $store): void
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
    $allowedIps = array_slice(array_keys($latestByIp), 0, BBX_I24_RATE_LIMIT_MAX_IPS);
    $boundedStore = [];
    foreach ($allowedIps as $ip) {
        $boundedStore[$ip] = array_values(array_slice($store[$ip], -BBX_I24_RATE_LIMIT_MAX_ATTEMPTS));
    }

    file_put_contents($file, json_encode($boundedStore, JSON_PRETTY_PRINT), LOCK_EX);
}

function bbx_i24_enforce_rate_limit(string $file): ?int
{
    $now = time();
    $windowStart = $now - BBX_I24_RATE_LIMIT_WINDOW;
    $ip = bbx_i24_client_ip();
    $store = bbx_i24_load_rate_limit_store($file);

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
    if (count($attempts) >= BBX_I24_RATE_LIMIT_MAX_ATTEMPTS) {
        bbx_i24_save_rate_limit_store($file, $store);
        $oldestAttempt = min($attempts);
        return max(1, BBX_I24_RATE_LIMIT_WINDOW - ($now - $oldestAttempt));
    }

    $attempts[] = $now;
    $store[$ip] = $attempts;
    bbx_i24_save_rate_limit_store($file, $store);

    return null;
}

bbx_i24_apply_cors_headers();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!bbx_i24_is_allowed_request_origin()) {
    bbx_i24_log_security('origin_rejected', ['ip' => bbx_i24_client_ip()]);
    http_response_code(403);
    echo json_encode(['error' => 'Request origin not allowed']);
    exit;
}

$rateLimitFile = __DIR__ . '/../data/intel24-request-throttle.json';
$rateLimitDir = dirname($rateLimitFile);
if (!is_dir($rateLimitDir) && !mkdir($rateLimitDir, 0755, true) && !is_dir($rateLimitDir)) {
    bbx_i24_log_security('throttle_store_unavailable');
    http_response_code(500);
    echo json_encode(['error' => 'Unable to process request']);
    exit;
}

$retryAfter = bbx_i24_enforce_rate_limit($rateLimitFile);
if ($retryAfter !== null) {
    bbx_i24_log_security('rate_limited', ['ip' => bbx_i24_client_ip()]);
    header('Retry-After: ' . $retryAfter);
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$inputLength = is_string($input) ? strlen($input) : 0;
if ($inputLength > BBX_I24_MAX_BODY_BYTES) {
    bbx_i24_log_security('invalid_body_size', ['ip' => bbx_i24_client_ip()]);
    http_response_code(413);
    echo json_encode(['error' => 'Request body too large']);
    exit;
}

$data = json_decode($input, true);

if (!is_array($data)) {
    bbx_i24_log_security('invalid_json', ['ip' => bbx_i24_client_ip()]);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$normalized = [
    'name' => bbx_i24_normalize_text($data['name'] ?? '', 120),
    'email' => strtolower(bbx_i24_normalize_text($data['email'] ?? '', 254)),
    'organization' => bbx_i24_normalize_text($data['organization'] ?? '', 160),
    'role' => bbx_i24_normalize_text($data['role'] ?? '', 120),
    'usecase' => bbx_i24_normalize_text($data['usecase'] ?? '', 1000),
    'request_id' => preg_replace('/[^A-Za-z0-9\-]/', '', bbx_i24_normalize_text($data['request_id'] ?? '', 64)) ?? '',
];

// Validate required fields
$required = ['name', 'email', 'organization', 'role'];
foreach ($required as $field) {
    if ($normalized[$field] === '') {
        bbx_i24_log_security('missing_required_field', ['field' => $field]);
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit;
    }
}

// Validate email
if (!filter_var($normalized['email'], FILTER_VALIDATE_EMAIL)) {
    bbx_i24_log_security('invalid_email', ['ip' => bbx_i24_client_ip()]);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

// Generate request ID if not provided
$request_id = $normalized['request_id'] !== ''
    ? $normalized['request_id']
    : 'I24-' . strtoupper(base_convert((string) time(), 10, 36)) . '-' . strtoupper(bin2hex(random_bytes(2)));

// Build request record
$request = [
    'request_id' => $request_id,
    'name' => htmlspecialchars($normalized['name'], ENT_QUOTES, 'UTF-8'),
    'email' => htmlspecialchars($normalized['email'], ENT_QUOTES, 'UTF-8'),
    'organization' => htmlspecialchars($normalized['organization'], ENT_QUOTES, 'UTF-8'),
    'role' => htmlspecialchars($normalized['role'], ENT_QUOTES, 'UTF-8'),
    'usecase' => htmlspecialchars($normalized['usecase'], ENT_QUOTES, 'UTF-8'),
    'console' => 'intel24',
    'status' => 'pending',
    'created_at' => date('c'),
    'ip_address' => bbx_i24_client_ip(),
    'user_agent' => bbx_i24_normalize_text($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 255)
];

// Store request (JSON file for demo, use DB in production)
$requests_file = __DIR__ . '/../data/intel24-requests.json';
$requests_dir = dirname($requests_file);

if (!is_dir($requests_dir)) {
    if (!mkdir($requests_dir, 0755, true) && !is_dir($requests_dir)) {
        bbx_i24_log_security('request_store_unavailable');
        http_response_code(500);
        echo json_encode(['error' => 'Unable to process request']);
        exit;
    }
}

$requests = [];
if (file_exists($requests_file)) {
    $requests = json_decode(file_get_contents($requests_file), true) ?: [];
}

if (!is_array($requests)) {
    $requests = [];
}

$requests[] = $request;

// Keep only last 100 requests
$requests = array_slice($requests, -100);

if (file_put_contents($requests_file, json_encode($requests, JSON_PRETTY_PRINT), LOCK_EX) === false) {
    bbx_i24_log_security('request_store_write_failed');
    http_response_code(500);
    echo json_encode(['error' => 'Unable to process request']);
    exit;
}

// Return success
http_response_code(201);
echo json_encode([
    'success' => true,
    'request_id' => $request_id,
    'status' => 'pending',
    'message' => 'Intel24 access request submitted successfully'
]);
