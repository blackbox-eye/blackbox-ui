<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
http_response_code(200);

require_once __DIR__ . '/../includes/env.php';

$expectedIss = defined('BBX_SITE_BASE_URL') ? BBX_SITE_BASE_URL : 'https://blackbox.codes';
$expectedAud = 'ts24';

$response = [
  'stub' => true,
  'secretConfigured' => true,
  'usesHS256' => true,
  'expectedIss' => $expectedIss,
  'expectedAud' => $expectedAud,
  'recentErrors' => [],
  'notes' => 'TS24 stub response for local testing',
  'timestamp' => gmdate('Y-m-d\TH:i:s'),
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}';
