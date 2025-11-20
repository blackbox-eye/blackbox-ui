<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../includes/contact-log.php';

$logFile = __DIR__ . '/../logs/contact-submissions.log';
$beforeSize = is_file($logFile) ? filesize($logFile) : 0;

$hostname = $_SERVER['HTTP_HOST'] ?? 'local-selftest';

bbx_log_contact_submission(
  'selftest',
  [
    'score' => 0.99,
    'action' => 'selftest',
    'hostname' => $hostname,
    'api_mode' => 'selftest',
  ],
  'selftest_ping',
  [
    'name' => 'log-selftest',
    'email' => 'selftest@blackbox.codes',
    'phone' => '',
    'message_length' => 0,
    'has_phone' => false,
    'expected_hostname' => $hostname,
    'mail_sent' => false,
    'mail_recipient' => 'ops@blackbox.codes',
    'selftest' => true,
  ]
);

clearstatcache();
$afterSize = is_file($logFile) ? filesize($logFile) : 0;

if ($afterSize > $beforeSize) {
  header('Content-Type: text/plain; charset=utf-8');
  echo 'OK';
  exit;
}

http_response_code(500);
header('Content-Type: text/plain; charset=utf-8');
echo 'ERROR: Could not confirm log write';
