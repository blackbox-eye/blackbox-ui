<?php
// Helper til auditlogging af agent-events.
function log_agent_event(string $agentId, string $eventType, array $details = []): void
{
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        if (!mkdir($logDir, 0750, true) && !is_dir($logDir)) {
            return;
        }
    }

    $safeAgent = preg_replace('/[^a-zA-Z0-9_-]/', '_', $agentId);
    if ($safeAgent === '' || $safeAgent === null) {
        $safeAgent = 'unknown';
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    $entryDetails = array_merge(['ip' => $ip, 'ua' => $ua], $details);

    $detailPairs = [];
    foreach ($entryDetails as $key => $value) {
        if (!is_scalar($value)) {
            continue;
        }
        $sanitized = str_replace(["\n", "\r", '|'], ' ', (string) $value);
        $detailPairs[] = $key . '=' . $sanitized;
    }

    $timestamp = gmdate('c');
    $line = sprintf('%s | agent=%s | event=%s', $timestamp, $safeAgent, $eventType);
    if ($detailPairs) {
        $line .= ' | ' . implode(' | ', $detailPairs);
    }
    $line .= PHP_EOL;

    $logFile = $logDir . '/' . $safeAgent . '.log';
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}
