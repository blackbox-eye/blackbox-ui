<?php
/**
 * Console Activity API
 * 
 * Server-side activity logging and retrieval for console selector.
 * Sprint 3: Supports GET (retrieve) and POST (add) operations.
 * 
 * Usage:
 *   GET /api/console-activity.php - Returns recent activity events
 *   POST /api/console-activity.php - Adds a new activity event
 * 
 * Returns: JSON array of activity events
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

const BBX_CA_MAX_JSON_BYTES = 8192;
const BBX_CA_RATE_LIMIT_WINDOW = 300;
const BBX_CA_GET_MAX_ATTEMPTS = 120;
const BBX_CA_POST_MAX_ATTEMPTS = 40;
const BBX_CA_RATE_LIMIT_MAX_KEYS = 800;
const BBX_CA_MAX_DATA_DEPTH = 3;
const BBX_CA_MAX_DATA_ITEMS = 20;

function bbx_ca_declared_body_too_large(int $maxBytes): bool
{
    $contentLength = $_SERVER['CONTENT_LENGTH'] ?? null;
    if (!is_scalar($contentLength) || !is_numeric((string) $contentLength)) {
        return false;
    }

    return (int) $contentLength > $maxBytes;
}

function bbx_ca_read_bounded_body(int $maxBytes, bool &$tooLarge = false): ?string
{
    $tooLarge = false;
    $stream = fopen('php://input', 'rb');
    if ($stream === false) {
        return null;
    }

    $body = stream_get_contents($stream, $maxBytes + 1);
    fclose($stream);

    if (!is_string($body)) {
        return null;
    }

    if (strlen($body) > $maxBytes) {
        $tooLarge = true;
        return null;
    }

    return $body;
}

function bbx_ca_log_security(string $event, array $context = []): void
{
    $pairs = [];
    foreach ($context as $key => $value) {
        if (!is_scalar($value) || $value === '') {
            continue;
        }
        $pairs[] = $key . '=' . (string) $value;
    }

    error_log('console-activity security: ' . $event . (empty($pairs) ? '' : ' ' . implode(' ', $pairs)));
}

function bbx_ca_normalize_host(string $host): string
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

function bbx_ca_normalize_port(?string $scheme, ?int $port): int
{
    if ($port !== null && $port > 0) {
        return $port;
    }

    return $scheme === 'http' ? 80 : 443;
}

function bbx_ca_parse_origin(string $value): ?array
{
    $parts = parse_url(trim($value));
    if (!is_array($parts) || empty($parts['host'])) {
        return null;
    }

    $scheme = isset($parts['scheme']) ? strtolower((string) $parts['scheme']) : 'https';
    $port = isset($parts['port']) ? (int) $parts['port'] : null;

    return [
        'scheme' => $scheme,
        'host' => bbx_ca_normalize_host((string) $parts['host']),
        'port' => bbx_ca_normalize_port($scheme, $port),
    ];
}

function bbx_ca_current_origin(): ?array
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
        'host' => bbx_ca_normalize_host($hostHeader),
        'port' => bbx_ca_normalize_port($scheme, $port),
    ];
}

function bbx_ca_is_allowed_request_origin(): bool
{
    $currentOrigin = bbx_ca_current_origin();
    if ($currentOrigin === null || $currentOrigin['host'] === '') {
        return true;
    }

    foreach (['HTTP_ORIGIN', 'HTTP_REFERER'] as $headerName) {
        $headerValue = $_SERVER[$headerName] ?? '';
        if (!is_string($headerValue) || trim($headerValue) === '') {
            continue;
        }

        $requestOrigin = bbx_ca_parse_origin($headerValue);
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

function bbx_ca_apply_cors_headers(): void
{
    $originHeader = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (!is_string($originHeader) || trim($originHeader) === '') {
        return;
    }

    $currentOrigin = bbx_ca_current_origin();
    $requestOrigin = bbx_ca_parse_origin($originHeader);
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

function bbx_ca_client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ip = is_string($ip) ? trim($ip) : 'unknown';
    return $ip === '' ? 'unknown' : substr($ip, 0, 64);
}

function bbx_ca_normalize_text($value, int $maxLength): string
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

function bbx_ca_normalize_key($value): string
{
    $key = bbx_ca_normalize_text($value, 40);
    $key = preg_replace('/[^A-Za-z0-9_.-]/', '_', $key) ?? $key;
    return $key === '' ? 'key' : $key;
}

function bbx_ca_normalize_event_data($value, int $depth = BBX_CA_MAX_DATA_DEPTH)
{
    if ($value === null) {
        return null;
    }

    if ($depth <= 0) {
        return null;
    }

    if (is_array($value)) {
        $normalized = [];
        $index = 0;
        foreach ($value as $key => $item) {
            if ($index >= BBX_CA_MAX_DATA_ITEMS) {
                break;
            }
            $normalizedKey = is_string($key) ? bbx_ca_normalize_key($key) : (string) $index;
            $normalized[$normalizedKey] = bbx_ca_normalize_event_data($item, $depth - 1);
            $index++;
        }
        return $normalized;
    }

    if (is_bool($value) || is_int($value) || is_float($value)) {
        return $value;
    }

    return bbx_ca_normalize_text($value, 255);
}

function bbx_ca_normalize_timestamp($value): int
{
    $now = (int) floor(microtime(true) * 1000);
    if (!is_scalar($value) || !is_numeric((string) $value)) {
        return $now;
    }

    $timestamp = (int) $value;
    if ($timestamp > 0 && $timestamp < 100000000000) {
        $timestamp *= 1000;
    }

    $min = $now - (30 * 24 * 60 * 60 * 1000);
    $max = $now + (24 * 60 * 60 * 1000);
    if ($timestamp < $min || $timestamp > $max) {
        return $now;
    }

    return $timestamp;
}

function bbx_ca_load_rate_limit_store(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }

    $data = json_decode((string) @file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function bbx_ca_save_rate_limit_store(string $file, array $store): bool
{
    $latestByKey = [];
    foreach ($store as $bucketKey => $timestamps) {
        if (!is_array($timestamps) || empty($timestamps)) {
            unset($store[$bucketKey]);
            continue;
        }
        $latestByKey[$bucketKey] = max($timestamps);
    }

    arsort($latestByKey);
    $allowedKeys = array_slice(array_keys($latestByKey), 0, BBX_CA_RATE_LIMIT_MAX_KEYS);
    $boundedStore = [];
    foreach ($allowedKeys as $bucketKey) {
        $boundedStore[$bucketKey] = array_values($store[$bucketKey]);
    }

    $encodedStore = json_encode($boundedStore, JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE);
    if (!is_string($encodedStore)) {
        return false;
    }

    return @file_put_contents($file, $encodedStore, LOCK_EX) !== false;
}

function bbx_ca_enforce_rate_limit(string $file, string $bucketKey, int $maxAttempts, ?int &$retryAfter = null): bool
{
    $retryAfter = null;
    $now = time();
    $windowStart = $now - BBX_CA_RATE_LIMIT_WINDOW;
    $store = bbx_ca_load_rate_limit_store($file);

    foreach ($store as $storedKey => $timestamps) {
        if (!is_array($timestamps)) {
            unset($store[$storedKey]);
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
            unset($store[$storedKey]);
            continue;
        }

        $store[$storedKey] = $filtered;
    }

    $attempts = $store[$bucketKey] ?? [];
    if (count($attempts) >= $maxAttempts) {
        if (!bbx_ca_save_rate_limit_store($file, $store)) {
            return false;
        }
        $oldestAttempt = min($attempts);
        $retryAfter = max(1, BBX_CA_RATE_LIMIT_WINDOW - ($now - $oldestAttempt));
        return true;
    }

    $attempts[] = $now;
    $store[$bucketKey] = $attempts;
    if (!bbx_ca_save_rate_limit_store($file, $store)) {
        return false;
    }

    return true;
}

bbx_ca_apply_cors_headers();

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Activity storage (Sprint 3: file-based, Sprint 4+: database)
$activityFile = __DIR__ . '/../logs/console-activity.json';
$logDir = dirname($activityFile);

if (!is_dir($logDir) && !@mkdir($logDir, 0755, true) && !is_dir($logDir)) {
    bbx_ca_log_security('log_dir_unavailable');
    http_response_code(500);
    echo json_encode(['error' => 'Unable to process request']);
    exit;
}

$rateLimitFile = $logDir . '/console-activity-throttle.json';

if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS' && !bbx_ca_is_allowed_request_origin()) {
    bbx_ca_log_security('origin_rejected', ['ip' => bbx_ca_client_ip(), 'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown']);
    http_response_code(403);
    echo json_encode(['error' => 'Request origin not allowed']);
    exit;
}

$bucketKey = ($_SERVER['REQUEST_METHOD'] ?? 'unknown') . '|' . bbx_ca_client_ip();
$maxAttempts = $_SERVER['REQUEST_METHOD'] === 'GET' ? BBX_CA_GET_MAX_ATTEMPTS : BBX_CA_POST_MAX_ATTEMPTS;
$retryAfter = null;
if (!bbx_ca_enforce_rate_limit($rateLimitFile, $bucketKey, $maxAttempts, $retryAfter)) {
    bbx_ca_log_security('throttle_store_write_failed', ['ip' => bbx_ca_client_ip(), 'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown']);
    http_response_code(500);
    echo json_encode(['error' => 'Unable to process request']);
    exit;
}

if ($retryAfter !== null) {
    bbx_ca_log_security('rate_limited', ['ip' => bbx_ca_client_ip(), 'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown']);
    header('Retry-After: ' . $retryAfter);
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests']);
    exit;
}

/**
 * Load activity from storage
 */
function loadActivity(string $file): array {
    if (!file_exists($file)) {
        return generateDummyActivity();
    }
    $data = @file_get_contents($file);
    if (!$data) {
        return generateDummyActivity();
    }
    $activity = json_decode($data, true);
    return is_array($activity) ? $activity : generateDummyActivity();
}

/**
 * Save activity to storage
 */
function saveActivity(string $file, array $activity): bool {
    // Keep only last 50 events
    $activity = array_slice($activity, 0, 50);
    $encodedActivity = json_encode($activity, JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE);
    if (!is_string($encodedActivity)) {
        return false;
    }

    return @file_put_contents($file, $encodedActivity, LOCK_EX) !== false;
}

/**
 * Generate dummy activity data for first load
 */
function generateDummyActivity(): array {
    $now = time() * 1000; // JS timestamp
    return [
        ['console' => 'gdi', 'action' => 'login', 'timestamp' => $now - (5 * 60 * 60 * 1000)],
        ['console' => 'ccs', 'action' => 'login', 'timestamp' => $now - (2 * 24 * 60 * 60 * 1000)],
        ['console' => 'intel24', 'action' => 'login', 'timestamp' => $now - (7 * 24 * 60 * 60 * 1000)],
    ];
}

/**
 * Generate sparkline data points
 */
function generateSparklinePoints(): array {
    $points = [];
    $value = rand(15, 25);
    for ($i = 0; $i < 8; $i++) {
        $value = max(5, min(28, $value + rand(-5, 5)));
        $points[] = ['x' => $i * 14.3, 'y' => 30 - $value];
    }
    return $points;
}

/**
 * Format relative time
 */
function formatRelativeTime(int $timestamp): string {
    $now = time() * 1000;
    $diff = $now - $timestamp;
    $seconds = floor($diff / 1000);
    $minutes = floor($seconds / 60);
    $hours = floor($minutes / 60);
    $days = floor($hours / 24);
    $weeks = floor($days / 7);

    if ($seconds < 60) return 'Just now';
    if ($minutes < 60) return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    if ($hours < 24) return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    if ($days === 1) return 'Yesterday';
    if ($days < 7) return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    if ($weeks === 1) return '1 week ago';
    return $weeks . ' weeks ago';
}

// Handle POST - add new activity event
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        if (bbx_ca_declared_body_too_large(BBX_CA_MAX_JSON_BYTES)) {
            bbx_ca_log_security('body_too_large', ['ip' => bbx_ca_client_ip()]);
            http_response_code(413);
            echo json_encode(['error' => 'Request body too large']);
            exit;
        }

        $bodyTooLarge = false;
        $rawInput = bbx_ca_read_bounded_body(BBX_CA_MAX_JSON_BYTES, $bodyTooLarge);
        if ($bodyTooLarge) {
            bbx_ca_log_security('body_too_large', ['ip' => bbx_ca_client_ip()]);
            http_response_code(413);
            echo json_encode(['error' => 'Request body too large']);
            exit;
        }

        if ($rawInput === null) {
            bbx_ca_log_security('body_read_failed', ['ip' => bbx_ca_client_ip()]);
            http_response_code(500);
            echo json_encode(['error' => 'Unable to process request']);
            exit;
        }

        $input = json_decode($rawInput, true);
        if (!is_array($input)) {
            bbx_ca_log_security('invalid_json', ['ip' => bbx_ca_client_ip()]);
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            exit;
        }
    } else {
        $input = $_POST;
    }

    $inputData = is_array($input['data'] ?? null) ? $input['data'] : null;
    $console = bbx_ca_normalize_text($input['console'] ?? ($inputData['console'] ?? null), 40);
    $action = bbx_ca_normalize_text($input['action'] ?? 'activity', 60);
    $data = bbx_ca_normalize_event_data($input['data'] ?? null);

    $event = [
        'console'   => $console !== '' ? $console : null,
        'action'    => $action !== '' ? $action : 'activity',
        'timestamp' => bbx_ca_normalize_timestamp($input['timestamp'] ?? null),
        'data'      => $data,
    ];

    // Validate
    if (empty($event['console']) && empty($event['data'])) {
        // Generic activity log (SSO request, etc.)
        $event['console'] = 'system';
    }

    $activity = loadActivity($activityFile);
    array_unshift($activity, $event);
    if (!saveActivity($activityFile, $activity)) {
        bbx_ca_log_security('write_failed');
        http_response_code(500);
        echo json_encode(['error' => 'Unable to store activity']);
        exit;
    }

    echo json_encode([
        'ok' => true,
        'event' => $event,
    ]);
    exit;
}

// Handle GET - return activity data
$agent_id = bbx_ca_normalize_text($_GET['agent_id'] ?? 'demo-agent', 80);
$activity = loadActivity($activityFile);

// Build console stats
$consoleStats = [
    'ccs' => ['count' => 0, 'last' => null],
    'gdi' => ['count' => 0, 'last' => null],
    'intel24' => ['count' => 0, 'last' => null],
];

foreach ($activity as $event) {
    $console = $event['console'] ?? null;
    if ($console && isset($consoleStats[$console])) {
        $consoleStats[$console]['count']++;
        if (!$consoleStats[$console]['last']) {
            $consoleStats[$console]['last'] = $event['timestamp'];
        }
    }
}

$response = [
    'agent_id' => $agent_id,
    'timestamp' => date('c'),
    'events' => array_slice($activity, 0, 20),
    'consoles' => [
        [
            'id' => 'ccs',
            'name' => 'CCS Settlement',
            'last_access' => $consoleStats['ccs']['last'] ? date('c', (int)($consoleStats['ccs']['last'] / 1000)) : date('c', strtotime('-2 days')),
            'relative_time' => $consoleStats['ccs']['last'] ? formatRelativeTime($consoleStats['ccs']['last']) : '2d ago',
            'access_count_7d' => max($consoleStats['ccs']['count'], rand(8, 15)),
            'status' => 'operational',
            'metrics' => [
                'settlements_today' => rand(8, 45),
                'avg_settlement_time' => number_format(rand(120, 280) / 100, 2) . 's',
                'volume_7d' => '$' . number_format(rand(150000, 850000)),
            ]
        ],
        [
            'id' => 'gdi',
            'name' => 'GDI Data Intelligence',
            'last_access' => $consoleStats['gdi']['last'] ? date('c', (int)($consoleStats['gdi']['last'] / 1000)) : date('c', strtotime('-5 hours')),
            'relative_time' => $consoleStats['gdi']['last'] ? formatRelativeTime($consoleStats['gdi']['last']) : '5h ago',
            'access_count_7d' => max($consoleStats['gdi']['count'], rand(20, 30)),
            'status' => 'available',
            'metrics' => [
                'active_cases' => rand(3, 12),
                'alerts_triggered' => rand(5, 25),
                'sensors_online' => rand(92, 100) . '%',
            ]
        ],
        [
            'id' => 'intel24',
            'name' => 'Intel24 Intelligence',
            'last_access' => $consoleStats['intel24']['last'] ? date('c', (int)($consoleStats['intel24']['last'] / 1000)) : date('c', strtotime('-1 week')),
            'relative_time' => $consoleStats['intel24']['last'] ? formatRelativeTime($consoleStats['intel24']['last']) : '1w ago',
            'access_count_7d' => max($consoleStats['intel24']['count'], rand(3, 8)),
            'status' => 'operational',
            'metrics' => [
                'avg_response_time' => number_format(rand(80, 180) / 100, 2) . 's',
                'briefings_read' => rand(8, 22),
                'transport_alerts' => rand(2, 8),
            ]
        ],
    ],
    'sparkline_data' => [
        'ccs' => generateSparklinePoints(),
        'gdi' => generateSparklinePoints(),
        'intel24' => generateSparklinePoints(),
    ],
];

echo json_encode($response, JSON_PRETTY_PRINT);
