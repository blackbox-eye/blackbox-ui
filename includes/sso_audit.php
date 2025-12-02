<?php

declare(strict_types=1);

require_once __DIR__ . '/logging.php';

if (!defined('BBX_SSO_AUDIT_FILE')) {
  define('BBX_SSO_AUDIT_FILE', bbx_log_directory() . '/sso_events.log');
}

/**
 * Write a structured JSON-line audit entry for SSO events.
 */
function bbx_log_sso_event(string $event, array $context = [], ?string $level = null): void
{
  static $defaultLevels = [
    'SSO_TOKEN_ISSUED' => 'INFO',
    'SSO_TOKEN_MINT_FAILED' => 'ERROR',
    'SSO_STACK_HEALTH_FAIL' => 'ERROR',
  ];

  $level = $level ?? ($defaultLevels[$event] ?? 'INFO');

  $logFile = BBX_SSO_AUDIT_FILE;
  $logDir = dirname($logFile);
  if (!is_dir($logDir)) {
    @mkdir($logDir, 0750, true);
  }

  $sessionAgentId = null;
  if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['agent_id'])) {
    $sessionAgentId = $_SESSION['agent_id'];
  }

  $agentId = $context['agent_id'] ?? $sessionAgentId;
  if ($agentId !== null) {
    $context['agent_id'] = $agentId;
  }

  if (PHP_SAPI === 'cli') {
    $ip = $context['ip'] ?? 'CLI';
    $userAgent = $context['user_agent'] ?? 'CLI';
  } else {
    $ip = $context['ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $userAgent = $context['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');
  }

  $entry = [
    'timestamp' => gmdate('c'),
    'source' => 'GDI_SSO',
    'event' => $event,
    'level' => strtoupper($level),
    'agent_id' => $agentId,
    'ip' => $ip,
    'user_agent' => substr((string) $userAgent, 0, 200),
  ];

  $payload = array_merge($entry, $context);
  $jsonLine = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

  if ($jsonLine === false) {
    error_log('BBX SSO AUDIT: JSON encoding failed for event ' . $event);
    return;
  }

  $result = @file_put_contents($logFile, $jsonLine . PHP_EOL, FILE_APPEND | LOCK_EX);
  if ($result === false) {
    error_log('BBX SSO AUDIT: Unable to write event ' . $event . ' to log file.');
  }
}
