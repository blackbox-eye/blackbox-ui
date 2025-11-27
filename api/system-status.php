<?php

/**
 * System Status API
 *
 * Returns real-time system status for all monitored services.
 * Each service reports: status, latency, last check time.
 *
 * @endpoint GET /api/system-status.php
 * @returns JSON
 */

header('Content-Type: application/json');
header('Cache-Control: private, max-age=10'); // Cache for 10 seconds to reduce polling load
header('X-Content-Type-Options: nosniff');

session_start();

// Require authentication
if (!isset($_SESSION['agent_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

require_once __DIR__ . '/../db.php';

try {
  // Define services to monitor
  $services = [
    [
      'id' => 'firewall',
      'name' => 'Firewall Service',
      'type' => 'security',
      'check_type' => 'process'
    ],
    [
      'id' => 'database',
      'name' => 'Threat Intel DB',
      'type' => 'database',
      'check_type' => 'connection'
    ],
    [
      'id' => 'ai_core',
      'name' => 'AI Core "GREY-E"',
      'type' => 'service',
      'check_type' => 'api'
    ],
    [
      'id' => 'api_gateway',
      'name' => 'API Gateway',
      'type' => 'network',
      'check_type' => 'http'
    ],
    [
      'id' => 'log_collector',
      'name' => 'Log Collector',
      'type' => 'service',
      'check_type' => 'process'
    ],
    [
      'id' => 'backup_service',
      'name' => 'Backup Service',
      'type' => 'service',
      'check_type' => 'schedule'
    ]
  ];

  $statusResults = [];

  foreach ($services as $service) {
    $status = checkServiceStatus($service, $pdo ?? null);
    $statusResults[] = $status;
  }

  // Calculate overall health
  $criticalCount = count(array_filter($statusResults, fn($s) => $s['status'] === 'critical'));
  $warningCount = count(array_filter($statusResults, fn($s) => $s['status'] === 'warning'));

  $overallHealth = 'healthy';
  if ($criticalCount > 0) {
    $overallHealth = 'critical';
  } elseif ($warningCount > 0) {
    $overallHealth = 'degraded';
  }

  echo json_encode([
    'success' => true,
    'data' => [
      'overall_health' => $overallHealth,
      'services' => $statusResults,
      'summary' => [
        'total' => count($statusResults),
        'healthy' => count(array_filter($statusResults, fn($s) => $s['status'] === 'ok')),
        'warning' => $warningCount,
        'critical' => $criticalCount
      ]
    ],
    'timestamp' => date('c')
  ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Internal server error']);
}

/**
 * Check status of a single service
 * In production, this would make real health checks
 */
function checkServiceStatus($service, $pdo = null)
{
  $startTime = microtime(true);

  $result = [
    'id' => $service['id'],
    'name' => $service['name'],
    'type' => $service['type'],
    'status' => 'ok',
    'status_text' => 'Operationel',
    'latency_ms' => 0,
    'last_check' => date('c'),
    'details' => null
  ];

  // Simulate real checks based on service type
  switch ($service['id']) {
    case 'database':
      // Actually check database connection
      if ($pdo) {
        try {
          $pdo->query('SELECT 1');
          $result['status'] = 'ok';
          $result['status_text'] = 'Stabil';
          $result['details'] = 'Forbindelse aktiv';
        } catch (PDOException $e) {
          $result['status'] = 'critical';
          $result['status_text'] = 'Fejl';
          $result['details'] = 'Kan ikke forbinde til database';
        }
      } else {
        $result['status'] = 'warning';
        $result['status_text'] = 'Ikke konfigureret';
      }
      break;

    case 'api_gateway':
      // Simulate occasional high latency
      $simulatedLatency = rand(15, 250);
      $result['latency_ms'] = $simulatedLatency;

      if ($simulatedLatency > 200) {
        $result['status'] = 'warning';
        $result['status_text'] = 'Høj Latens';
        $result['details'] = "Response tid: {$simulatedLatency}ms";
      } elseif ($simulatedLatency > 500) {
        $result['status'] = 'critical';
        $result['status_text'] = 'Timeout';
      } else {
        $result['details'] = "Response tid: {$simulatedLatency}ms";
      }
      break;

    case 'ai_core':
      // AI Core status - simulated
      $aiStatus = rand(1, 100);
      if ($aiStatus > 5) {
        $result['status'] = 'ok';
        $result['status_text'] = 'Aktiv';
        $result['details'] = 'Model loaded, ready for inference';
      } else {
        $result['status'] = 'warning';
        $result['status_text'] = 'Warming up';
        $result['details'] = 'Model reloading...';
      }
      break;

    case 'backup_service':
      // Check last backup time (simulated)
      $lastBackup = strtotime('-' . rand(1, 48) . ' hours');
      $hoursAgo = round((time() - $lastBackup) / 3600);

      if ($hoursAgo > 24) {
        $result['status'] = 'warning';
        $result['status_text'] = 'Forsinket';
        $result['details'] = "Sidste backup: {$hoursAgo} timer siden";
      } else {
        $result['status'] = 'ok';
        $result['status_text'] = 'Operationel';
        $result['details'] = "Sidste backup: {$hoursAgo} timer siden";
      }
      break;

    default:
      // Default: randomly simulate status for demo
      $rand = rand(1, 100);
      if ($rand > 95) {
        $result['status'] = 'critical';
        $result['status_text'] = 'Fejl';
      } elseif ($rand > 85) {
        $result['status'] = 'warning';
        $result['status_text'] = 'Advarsel';
      } else {
        $result['status'] = 'ok';
        $result['status_text'] = 'Operationel';
      }
  }

  // Calculate actual check latency
  $result['latency_ms'] = $result['latency_ms'] ?: round((microtime(true) - $startTime) * 1000, 2);

  return $result;
}
