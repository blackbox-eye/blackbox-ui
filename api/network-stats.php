<?php

/**
 * Network Statistics API
 *
 * Returns network monitoring data including:
 * - Port utilization
 * - Bandwidth usage
 * - Connection statistics
 *
 * @endpoint GET /api/network-stats.php
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

try {
  // Define monitored ports
  $ports = [
    [
      'port' => 22,
      'name' => 'SSH',
      'service' => 'Secure Shell',
      'type' => 'management'
    ],
    [
      'port' => 443,
      'name' => 'HTTPS',
      'service' => 'Web Traffic',
      'type' => 'web'
    ],
    [
      'port' => 3306,
      'name' => 'MySQL',
      'service' => 'Database',
      'type' => 'database'
    ],
    [
      'port' => 9200,
      'name' => 'Elasticsearch',
      'service' => 'Search Engine',
      'type' => 'search'
    ],
    [
      'port' => 6379,
      'name' => 'Redis',
      'service' => 'Cache',
      'type' => 'cache'
    ],
    [
      'port' => 5432,
      'name' => 'PostgreSQL',
      'service' => 'Analytics DB',
      'type' => 'database'
    ]
  ];

  $networkStats = [];

  foreach ($ports as $port) {
    $stats = generatePortStats($port);
    $networkStats[] = $stats;
  }

  // Calculate bandwidth summary
  $totalConnections = array_sum(array_column($networkStats, 'connections'));
  $avgUtilization = round(array_sum(array_column($networkStats, 'utilization')) / count($networkStats), 1);

  // Generate historical data for charts (last 60 minutes, 5-min intervals)
  $historicalData = generateHistoricalData();

  echo json_encode([
    'success' => true,
    'data' => [
      'ports' => $networkStats,
      'summary' => [
        'total_connections' => $totalConnections,
        'average_utilization' => $avgUtilization,
        'bandwidth_in' => formatBandwidth(rand(50, 200) * 1024 * 1024),
        'bandwidth_out' => formatBandwidth(rand(30, 150) * 1024 * 1024),
        'packets_per_second' => rand(5000, 25000)
      ],
      'historical' => $historicalData
    ],
    'timestamp' => date('c')
  ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Internal server error']);
}

/**
 * Generate simulated stats for a port
 */
function generatePortStats($port)
{
  // Simulate realistic utilization based on port type
  $baseUtilization = match ($port['type']) {
    'web' => rand(60, 95),
    'database' => rand(40, 90),
    'management' => rand(10, 50),
    'cache' => rand(30, 70),
    'search' => rand(15, 45),
    default => rand(20, 60)
  };

  // Add some variance
  $utilization = min(100, max(0, $baseUtilization + rand(-10, 10)));

  // Determine status level based on utilization
  $level = 'low';
  if ($utilization > 80) {
    $level = 'high';
  } elseif ($utilization > 50) {
    $level = 'medium';
  }

  // Simulate connection count
  $connections = match ($port['type']) {
    'web' => rand(100, 1000),
    'database' => rand(20, 200),
    'management' => rand(1, 20),
    'cache' => rand(50, 300),
    'search' => rand(10, 100),
    default => rand(5, 50)
  };

  return [
    'port' => $port['port'],
    'name' => $port['name'],
    'service' => $port['service'],
    'type' => $port['type'],
    'utilization' => $utilization,
    'level' => $level,
    'connections' => $connections,
    'bytes_in' => rand(1000000, 100000000),
    'bytes_out' => rand(500000, 50000000),
    'packets_dropped' => rand(0, 10),
    'status' => $utilization < 95 ? 'healthy' : 'saturated'
  ];
}

/**
 * Generate historical data for charts
 */
function generateHistoricalData()
{
  $data = [
    'labels' => [],
    'cpu' => [],
    'memory' => [],
    'network' => []
  ];

  // Generate 12 data points (last 60 minutes, 5-min intervals)
  for ($i = 11; $i >= 0; $i--) {
    $data['labels'][] = (60 - $i * 5) . 'm';

    // Generate somewhat realistic trending data
    $baseCpu = 35 + sin($i / 2) * 15;
    $baseMemory = 45 + cos($i / 3) * 10;
    $baseNetwork = 50 + sin($i / 4) * 20;

    $data['cpu'][] = round(max(10, min(90, $baseCpu + rand(-5, 5))));
    $data['memory'][] = round(max(20, min(85, $baseMemory + rand(-3, 3))));
    $data['network'][] = round(max(15, min(95, $baseNetwork + rand(-8, 8))));
  }

  return $data;
}

/**
 * Format bandwidth for display
 */
function formatBandwidth($bytes)
{
  $units = ['B', 'KB', 'MB', 'GB', 'TB'];
  $unitIndex = 0;

  while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
    $bytes /= 1024;
    $unitIndex++;
  }

  return round($bytes, 1) . ' ' . $units[$unitIndex] . '/s';
}
