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
header('X-Content-Type-Options: nosniff');

const BBX_SSO_REQUEST_MAX_BODY_BYTES = 4096;
const BBX_SSO_REQUEST_MAX_COMPANY_LENGTH = 160;
const BBX_SSO_REQUEST_MAX_EMAIL_LENGTH = 254;
const BBX_SSO_REQUEST_MAX_DOMAIN_LENGTH = 253;
const BBX_SSO_REQUEST_MAX_PROVIDER_LENGTH = 32;
const BBX_SSO_REQUEST_MAX_NOTES_LENGTH = 1000;
const BBX_SSO_REQUEST_MAX_CONSOLE_LENGTH = 32;
const BBX_SSO_REQUEST_RATE_LIMIT_WINDOW = 300;
const BBX_SSO_REQUEST_RATE_LIMIT_MAX_ATTEMPTS = 10;
const BBX_SSO_REQUEST_RATE_LIMIT_MAX_IPS = 500;

function bbx_sso_request_respond(int $statusCode, array $payload): void
{
    http_response_code($statusCode);

    $json = json_encode($payload, JSON_INVALID_UTF8_SUBSTITUTE);
    if (!is_string($json)) {
        http_response_code(500);
        echo '{"ok":false,"error":"Internal server error"}';
        exit;
    }

    echo $json;
    exit;
}

function bbx_sso_request_declared_body_too_large(int $maxBytes): bool
{
    $contentLength = $_SERVER['CONTENT_LENGTH'] ?? null;
    if (!is_scalar($contentLength) || !is_numeric((string) $contentLength)) {
        return false;
    }

    return (int) $contentLength > $maxBytes;
}

function bbx_sso_request_read_bounded_body(int $maxBytes, bool &$tooLarge = false): ?string
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

function bbx_sso_request_log_security(string $event, array $context = []): void
{
    $safeContext = [];
    foreach ($context as $key => $value) {
        if (is_scalar($value)) {
            $safeContext[(string) $key] = $value;
        }
    }

    $contextJson = json_encode($safeContext, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    error_log(
        'sso-request security: ' . $event .
        (is_string($contextJson) && $contextJson !== '{}' ? ' ' . $contextJson : '')
    );
}

// Accepts only parse_url() host components. Reject malformed raw fragments such as
// slash/whitespace input or empty bracket hosts; no host:port stripping is performed here.
// De-brackets IPv6 addresses after validating the bracketed host shape.
function bbx_sso_request_normalize_host(string $host): string
{
    $host = strtolower(trim($host));
    if ($host === '' || preg_match('/[\/\s]/u', $host) === 1) {
        return '';
    }

    if ($host[0] === '[') {
        $end = strpos($host, ']');
        if ($end === false || $end !== strlen($host) - 1 || $end === 1) {
            return '';
        }

        return substr($host, 1, $end - 1);
    }

    if (strpos($host, ']') !== false) {
        return '';
    }

    return $host;
}

function bbx_sso_request_normalize_port(?string $scheme, ?int $port): int
{
    if ($port !== null && $port > 0) {
        return $port;
    }

    return $scheme === 'http' ? 80 : 443;
}

function bbx_sso_request_parse_origin(string $value): ?array
{
    $parts = parse_url(trim($value));
    if (!is_array($parts) || empty($parts['host'])) {
        return null;
    }

    $scheme = isset($parts['scheme']) ? strtolower((string) $parts['scheme']) : 'https';
    $port = isset($parts['port']) ? (int) $parts['port'] : null;
    $host = bbx_sso_request_normalize_host((string) $parts['host']);
    if ($host === '') {
        return null;
    }

    return [
        'scheme' => $scheme,
        'host' => $host,
        'port' => bbx_sso_request_normalize_port($scheme, $port),
    ];
}

function bbx_sso_request_current_origin(): ?array
{
    $hostHeader = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
    if (!is_string($hostHeader) || trim($hostHeader) === '') {
        return null;
    }

    // Default to http; only elevate to https when there is positive evidence.
    // This avoids misidentifying non-standard HTTP ports (e.g. 8080) as https.
    $scheme = 'http';
    if (!empty($_SERVER['REQUEST_SCHEME'])) {
        $scheme = strtolower((string) $_SERVER['REQUEST_SCHEME']);
    } elseif (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        $scheme = 'https';
    } elseif (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) {
        $scheme = 'https';
    }

    $parts = parse_url('//' . trim($hostHeader));
    if (!is_array($parts) || empty($parts['host'])) {
        return null;
    }

    $port = isset($parts['port']) ? (int) $parts['port'] : null;
    $host = bbx_sso_request_normalize_host((string) $parts['host']);
    if ($host === '') {
        return null;
    }

    return [
        'scheme' => $scheme,
        'host' => $host,
        'port' => bbx_sso_request_normalize_port($scheme, $port),
    ];
}

function bbx_sso_request_is_allowed_request_origin(): bool
{
    $currentOrigin = bbx_sso_request_current_origin();
    if ($currentOrigin === null || $currentOrigin['host'] === '') {
        return false;
    }

    // This is a state-changing POST endpoint. At least one origin header must be present
    // and must match the current origin. Requests with neither Origin nor Referer are rejected.
    $headerFound = false;
    foreach (['HTTP_ORIGIN', 'HTTP_REFERER'] as $headerName) {
        $headerValue = $_SERVER[$headerName] ?? '';
        if (!is_string($headerValue) || trim($headerValue) === '') {
            continue;
        }

        $headerFound = true;
        $requestOrigin = bbx_sso_request_parse_origin($headerValue);
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

    if (!$headerFound) {
        return false;
    }

    return true;
}

function bbx_sso_request_client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ip = is_string($ip) ? trim($ip) : 'unknown';
    return $ip === '' ? 'unknown' : substr($ip, 0, 64);
}

// Single-line field normalizer for company/email/domain/provider/console values only.
// Notes must use bbx_sso_request_normalize_notes() so \n and \t survive normalization.
function bbx_sso_request_normalize_text($value, int $maxLength, bool $lowercase = false): string
{
    if (!is_scalar($value)) {
        return '';
    }

    $text = trim((string) $value);
    $text = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $text) ?? $text;
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

    if ($lowercase) {
        $text = strtolower($text);
    }

    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $maxLength);
    }

    return substr($text, 0, $maxLength);
}

function bbx_sso_request_normalize_notes($value, int $maxLength): string
{
    if (!is_scalar($value)) {
        return '';
    }

    $text = (string) $value;
    // Strip unsafe control characters; preserve newlines (\n, \r) and tabs (\t).
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $text) ?? $text;
    // Collapse runs of plain spaces only — tabs and newlines are intentionally kept.
    $text = preg_replace('/ +/u', ' ', $text) ?? $text;
    // Trim only leading/trailing spaces (not tabs) from each line, then from the whole string.
    $lines = preg_split('/\r?\n/', $text) ?? [$text];
    $lines = array_map(static fn(string $l): string => trim($l, ' '), $lines);
    $text = trim(implode("\n", $lines), ' ');

    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $maxLength);
    }

    return substr($text, 0, $maxLength);
}

function bbx_sso_request_is_valid_domain(string $domain): bool
{
    if ($domain === '') {
        return true;
    }

    if ($domain === 'localhost' || filter_var($domain, FILTER_VALIDATE_IP) !== false) {
        return true;
    }

    if (filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false) {
        return true;
    }

    return preg_match('/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*$/', $domain) === 1;
}

function bbx_sso_request_is_valid_console(string $console): bool
{
    if ($console === '') {
        return false;
    }

    return preg_match('/^[a-z0-9][a-z0-9_-]{0,31}$/', $console) === 1;
}

function bbx_sso_request_ensure_directory(string $file): bool
{
    $directory = dirname($file);
    if (is_dir($directory)) {
        return true;
    }

    return @mkdir($directory, 0755, true) || is_dir($directory);
}

function bbx_sso_request_enforce_rate_limit(string $file, ?int &$retryAfter = null): bool
{
    $retryAfter = null;
    $now = time();
    $windowStart = $now - BBX_SSO_REQUEST_RATE_LIMIT_WINDOW;
    $ip = bbx_sso_request_client_ip();

    if (!bbx_sso_request_ensure_directory($file)) {
        return false;
    }

    // Single flock-protected cycle: load → prune stale entries → check → append → save.
    // Covers all steps within one exclusive lock to prevent TOCTOU races.
    $handle = @fopen($file, 'c+b');
    if ($handle === false) {
        return false;
    }

    if (!flock($handle, LOCK_EX)) {
        fclose($handle);
        return false;
    }

    $releaseAndFail = static function ($lockedHandle) use (&$retryAfter): bool {
        $retryAfter = null;
        flock($lockedHandle, LOCK_UN);
        fclose($lockedHandle);
        return false;
    };

    $raw = stream_get_contents($handle);
    $store = (is_string($raw) && $raw !== '') ? json_decode($raw, true) : null;
    $store = is_array($store) ? $store : [];

    // Prune stale entries for every IP inside the lock.
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
        } else {
            $store[$storedIp] = $filtered;
        }
    }

    $attempts = $store[$ip] ?? [];
    $allowed = count($attempts) < BBX_SSO_REQUEST_RATE_LIMIT_MAX_ATTEMPTS;

    if (!$allowed) {
        $oldest = min($attempts);
        $retryAfter = max(1, BBX_SSO_REQUEST_RATE_LIMIT_WINDOW - ($now - $oldest));
    } else {
        $attempts[] = $now;
        $store[$ip] = $attempts;

        // Bound total tracked IPs to prevent unbounded store growth.
        if (count($store) > BBX_SSO_REQUEST_RATE_LIMIT_MAX_IPS) {
            $latestByIp = [];
            foreach ($store as $storeIp => $timestamps) {
                $latestByIp[$storeIp] = max($timestamps);
            }
            arsort($latestByIp);
            $allowedIps = array_slice(array_keys($latestByIp), 0, BBX_SSO_REQUEST_RATE_LIMIT_MAX_IPS);
            $bounded = [];
            foreach ($allowedIps as $allowedIp) {
                $bounded[$allowedIp] = array_values(
                    array_slice($store[$allowedIp], -BBX_SSO_REQUEST_RATE_LIMIT_MAX_ATTEMPTS)
                );
            }
            $store = $bounded;
        }
    }

    $encodedStore = json_encode($store, JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE);
    if (!is_string($encodedStore)) {
        return $releaseAndFail($handle);
    }

    if (!ftruncate($handle, 0)) {
        return $releaseAndFail($handle);
    }

    if (!rewind($handle)) {
        return $releaseAndFail($handle);
    }

    $bytesWritten = fwrite($handle, $encodedStore);
    if ($bytesWritten === false || $bytesWritten < strlen($encodedStore) || !fflush($handle)) {
        return $releaseAndFail($handle);
    }

    flock($handle, LOCK_UN);
    fclose($handle);

    return $allowed;
}

function bbx_sso_request_generate_request_id(): string
{
    try {
        return 'SSO-' . strtoupper(bin2hex(random_bytes(4)));
    } catch (Throwable $exception) {
        return 'SSO-' . strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 8));
    }
}

function bbx_sso_request_write_log(string $file, array $entry): bool
{
    if (!bbx_sso_request_ensure_directory($file)) {
        return false;
    }

    $encodedEntry = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    if (!is_string($encodedEntry)) {
        return false;
    }

    return @file_put_contents($file, $encodedEntry . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    bbx_sso_request_respond(405, ['ok' => false, 'error' => 'Method not allowed']);
}

if (!bbx_sso_request_is_allowed_request_origin()) {
    bbx_sso_request_log_security('blocked_origin', ['ip' => bbx_sso_request_client_ip()]);
    bbx_sso_request_respond(403, ['ok' => false, 'error' => 'Invalid request origin']);
}

$rateLimitFile = __DIR__ . '/../logs/sso-request-throttle.json';
$retryAfter = null;
if (!bbx_sso_request_enforce_rate_limit($rateLimitFile, $retryAfter)) {
    if ($retryAfter !== null) {
        header('Retry-After: ' . (string) $retryAfter);
        bbx_sso_request_log_security('rate_limited', ['ip' => bbx_sso_request_client_ip(), 'retry_after' => $retryAfter]);
        bbx_sso_request_respond(429, ['ok' => false, 'error' => 'Too many requests']);
    }

    bbx_sso_request_log_security('rate_limit_store_failure', ['ip' => bbx_sso_request_client_ip()]);
    bbx_sso_request_respond(500, ['ok' => false, 'error' => 'Unable to process request']);
}

// Parse input (support both JSON and form data)
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$contentType = is_string($contentType) ? strtolower(trim($contentType)) : '';
if (strpos($contentType, 'application/json') !== false) {
    if (bbx_sso_request_declared_body_too_large(BBX_SSO_REQUEST_MAX_BODY_BYTES)) {
        bbx_sso_request_respond(413, ['ok' => false, 'error' => 'Request body too large']);
    }

    $tooLarge = false;
    $rawBody = bbx_sso_request_read_bounded_body(BBX_SSO_REQUEST_MAX_BODY_BYTES, $tooLarge);
    if ($tooLarge) {
        bbx_sso_request_respond(413, ['ok' => false, 'error' => 'Request body too large']);
    }

    if ($rawBody === null) {
        bbx_sso_request_respond(400, ['ok' => false, 'error' => 'Unable to read request body']);
    }

    if (trim($rawBody) === '') {
        $input = [];
    } else {
        $decoded = json_decode($rawBody, true);
        if (!is_array($decoded)) {
            bbx_sso_request_respond(400, ['ok' => false, 'error' => 'Invalid JSON body']);
        }

        $input = $decoded;
    }
} else {
    $input = is_array($_POST) ? $_POST : [];
}

$data = [
    'company'  => bbx_sso_request_normalize_text($input['company'] ?? '', BBX_SSO_REQUEST_MAX_COMPANY_LENGTH),
    'email'    => bbx_sso_request_normalize_text($input['email'] ?? '', BBX_SSO_REQUEST_MAX_EMAIL_LENGTH, true),
    'domain'   => bbx_sso_request_normalize_text($input['domain'] ?? '', BBX_SSO_REQUEST_MAX_DOMAIN_LENGTH, true),
    'provider' => bbx_sso_request_normalize_text($input['provider'] ?? '', BBX_SSO_REQUEST_MAX_PROVIDER_LENGTH, true),
    'notes'    => bbx_sso_request_normalize_notes($input['notes'] ?? '', BBX_SSO_REQUEST_MAX_NOTES_LENGTH),
    'console'  => bbx_sso_request_normalize_text($input['console'] ?? 'ccs', BBX_SSO_REQUEST_MAX_CONSOLE_LENGTH, true),
];

if ($data['console'] === '') {
    $data['console'] = 'ccs';
}

// Validate required fields
$requiredFields = ['company', 'email'];
$missing = [];
foreach ($requiredFields as $field) {
    if ($data[$field] === '') {
        $missing[] = $field;
    }
}

if (!empty($missing)) {
    bbx_sso_request_respond(422, [
        'ok' => false,
        'error' => 'Missing required fields',
        'missing' => $missing,
    ]);
}

// Validate email
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    bbx_sso_request_respond(422, ['ok' => false, 'error' => 'Invalid email address']);
}

if (!bbx_sso_request_is_valid_domain($data['domain'])) {
    bbx_sso_request_respond(422, ['ok' => false, 'error' => 'Invalid domain']);
}

// Validate provider if provided
$allowedProviders = ['azure', 'google', 'okta', 'other', ''];
if (!in_array($data['provider'], $allowedProviders, true)) {
    bbx_sso_request_respond(422, ['ok' => false, 'error' => 'Invalid provider']);
}

if (!bbx_sso_request_is_valid_console($data['console'])) {
    bbx_sso_request_respond(422, ['ok' => false, 'error' => 'Invalid console']);
}

// Generate request ID
$requestId = bbx_sso_request_generate_request_id();

// Log the request (Sprint 3 mock - in production, save to DB)
$logEntry = [
    'request_id'  => $requestId,
    'type'        => 'sso_request',
    'data'        => $data,
    'ip'          => bbx_sso_request_client_ip(),
    'user_agent'  => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'), 0, 512),
    'timestamp'   => date('c'),
];

// Log to file for Sprint 3 (production would use DB).
// Log write failure is non-fatal: the request_id has been generated and validated;
// throttle-store failure (above) is fail-closed, log-file failure is not.
$logFile = __DIR__ . '/../logs/sso-requests.log';
if (!bbx_sso_request_write_log($logFile, $logEntry)) {
    bbx_sso_request_log_security('log_write_failed', ['ip' => bbx_sso_request_client_ip()]);
}

// Return success
bbx_sso_request_respond(200, [
    'ok'         => true,
    'request_id' => $requestId,
    'message'    => 'SSO request received. Our team will contact you within 24-48 hours.',
    'console'    => $data['console'],
]);
