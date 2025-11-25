<?php

/**
 * Blackbox EYE™ - Enterprise Logging System
 *
 * Structured logging with support for:
 * - Multiple log channels (agent, contact, security, consent)
 * - Automatic file rotation
 * - Correlation IDs for request tracing
 * - GDPR-compliant data handling
 */

declare(strict_types=1);

// Log levels following RFC 5424
define('BBX_LOG_EMERGENCY', 0);
define('BBX_LOG_ALERT', 1);
define('BBX_LOG_CRITICAL', 2);
define('BBX_LOG_ERROR', 3);
define('BBX_LOG_WARNING', 4);
define('BBX_LOG_NOTICE', 5);
define('BBX_LOG_INFO', 6);
define('BBX_LOG_DEBUG', 7);

/**
 * Get or generate a correlation ID for the current request
 */
function bbx_correlation_id(): string
{
    static $correlationId = null;
    if ($correlationId === null) {
        $correlationId = $_SERVER['HTTP_X_CORRELATION_ID']
            ?? $_SERVER['HTTP_X_REQUEST_ID']
            ?? bin2hex(random_bytes(8));
    }
    return $correlationId;
}

/**
 * Get the log directory path, creating it if necessary
 */
function bbx_log_directory(): string
{
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0750, true);
    }
    return $logDir;
}

/**
 * Rotate log file if it exceeds max size (default 10MB)
 */
function bbx_rotate_log_if_needed(string $logFile, int $maxBytes = 10485760): void
{
    if (!file_exists($logFile)) {
        return;
    }

    $size = @filesize($logFile);
    if ($size === false || $size < $maxBytes) {
        return;
    }

    // Keep up to 5 rotated files
    for ($i = 4; $i >= 0; $i--) {
        $old = $logFile . '.' . $i;
        $new = $logFile . '.' . ($i + 1);
        if (file_exists($old)) {
            if ($i === 4) {
                @unlink($old);
            } else {
                @rename($old, $new);
            }
        }
    }
    @rename($logFile, $logFile . '.0');
}

/**
 * Core logging function with structured output
 *
 * @param string $channel Log channel (agent, contact, security, consent, app)
 * @param int $level Log level (BBX_LOG_* constants)
 * @param string $event Event type/name
 * @param array $context Additional context data
 */
function bbx_log(string $channel, int $level, string $event, array $context = []): void
{
    $logDir = bbx_log_directory();
    if (!is_dir($logDir)) {
        // Fail silently - don't block page load for logging issues
        return;
    }

    $safeChannel = preg_replace('/[^a-zA-Z0-9_-]/', '_', $channel) ?: 'app';
    $logFile = $logDir . '/' . $safeChannel . '.log';

    // Rotate only ~1% of requests to reduce I/O overhead (probabilistic rotation)
    // This avoids filesize() check on every request while still rotating periodically
    if (mt_rand(1, 100) === 1) {
        bbx_rotate_log_if_needed($logFile);
    }

    // Level name mapping
    $levelNames = [
        BBX_LOG_EMERGENCY => 'EMERGENCY',
        BBX_LOG_ALERT => 'ALERT',
        BBX_LOG_CRITICAL => 'CRITICAL',
        BBX_LOG_ERROR => 'ERROR',
        BBX_LOG_WARNING => 'WARNING',
        BBX_LOG_NOTICE => 'NOTICE',
        BBX_LOG_INFO => 'INFO',
        BBX_LOG_DEBUG => 'DEBUG',
    ];

    // Build structured entry
    $entry = [
        'timestamp' => gmdate('c'),
        'level' => $levelNames[$level] ?? 'INFO',
        'correlation_id' => bbx_correlation_id(),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'route' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 200),
        'event' => $event,
    ];

    // Merge context, filtering out sensitive data
    $sensitiveKeys = ['password', 'token', 'secret', 'key', 'auth', 'credential', 'message_body'];
    foreach ($context as $key => $value) {
        $lowerKey = strtolower($key);
        $isSensitive = false;
        foreach ($sensitiveKeys as $sensitive) {
            if (strpos($lowerKey, $sensitive) !== false) {
                $isSensitive = true;
                break;
            }
        }
        if ($isSensitive) {
            $entry[$key] = '[REDACTED]';
        } elseif (is_scalar($value)) {
            $entry[$key] = $value;
        } elseif (is_array($value)) {
            $entry[$key] = json_encode($value, JSON_UNESCAPED_SLASHES);
        }
    }

    $jsonLine = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($jsonLine === false) {
        error_log('BBX_LOG ERROR: JSON encode failed for event ' . $event);
        return;
    }

    @file_put_contents($logFile, $jsonLine . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/**
 * Log agent events (legacy compatibility wrapper)
 */
function log_agent_event(string $agentId, string $eventType, array $details = []): void
{
    bbx_log('agent', BBX_LOG_INFO, $eventType, array_merge(['agent_id' => $agentId], $details));
}

/**
 * Log contact form submissions
 */
function bbx_log_contact(string $status, array $context = []): void
{
    $level = $status === 'success' ? BBX_LOG_INFO : BBX_LOG_WARNING;
    bbx_log('contact', $level, 'form_submission', array_merge(['status' => $status], $context));
}

/**
 * Log security events (reCAPTCHA failures, rate limits, etc.)
 */
function bbx_log_security(string $event, array $context = []): void
{
    bbx_log('security', BBX_LOG_WARNING, $event, $context);
}

/**
 * Log consent/cookie events (aggregated, no personal data)
 */
function bbx_log_consent(string $action, array $context = []): void
{
    // Only log aggregated consent data, no personal identifiers
    $safeContext = [
        'action' => $action,
        'timestamp' => gmdate('c'),
        'page' => $_SERVER['REQUEST_URI'] ?? 'unknown',
    ];
    if (isset($context['consent_type'])) {
        $safeContext['consent_type'] = $context['consent_type'];
    }
    if (isset($context['categories'])) {
        $safeContext['categories'] = $context['categories'];
    }
    bbx_log('consent', BBX_LOG_INFO, 'consent_' . $action, $safeContext);
}

/**
 * Log application errors
 */
function bbx_log_error(string $message, array $context = []): void
{
    bbx_log('app', BBX_LOG_ERROR, 'error', array_merge(['message' => $message], $context));
}
