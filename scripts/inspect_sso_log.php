#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/logging.php';
require_once __DIR__ . '/../includes/sso_audit.php';

$defaultLines = 20;
$arg = $argv[1] ?? null;

if ($arg === '-h' || $arg === '--help') {
  echo "Usage: php scripts/inspect_sso_log.php [lines]\n";
  echo "Displays the most recent SSO audit entries (oldest to newest).\n";
  echo "Default lines: {$defaultLines}\n";
  exit(0);
}

$linesToShow = $defaultLines;
if ($arg !== null) {
  $parsed = (int) $arg;
  if ($parsed > 0) {
    $linesToShow = $parsed;
  }
}

$logFile = defined('BBX_SSO_AUDIT_FILE') ? BBX_SSO_AUDIT_FILE : (bbx_log_directory() . '/sso_events.log');

if (!file_exists($logFile)) {
  echo "No SSO audit events logged yet ({$logFile}).\n";
  exit(0);
}

$lines = [];
$handle = @fopen($logFile, 'r');
if ($handle === false) {
  fwrite(STDERR, "Unable to open {$logFile} for reading.\n");
  exit(1);
}

try {
  while (($line = fgets($handle)) !== false) {
    $lines[] = rtrim($line, "\r\n");
    if (count($lines) > $linesToShow) {
      array_shift($lines);
    }
  }
} finally {
  fclose($handle);
}

echo "Showing last " . count($lines) . " event(s) from {$logFile}\n";
foreach ($lines as $eventLine) {
  echo $eventLine . PHP_EOL;
}
