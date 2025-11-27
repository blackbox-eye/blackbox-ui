<?php

/**
 * Active Alerts API
 *
 * Returns list of active security alerts for the dashboard.
 * Supports filtering by severity and pagination.
 *
 * @endpoint GET /api/alerts.php
 * @param severity string Optional filter: 'critical', 'warning', 'info'
 * @param limit int Optional limit (default: 10)
 * @returns JSON array of alerts
 */

header('Content-Type: application/json');
header('Cache-Control: private, max-age=5'); // Cache for 5 seconds - alerts are more time-sensitive
header('X-Content-Type-Options: nosniff');

session_start();

// Require authentication
if (!isset($_SESSION['agent_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

require_once __DIR__ . '/../db.php';

$severity = $_GET['severity'] ?? null;
$limit = min((int)($_GET['limit'] ?? 10), 50);

try {
  $alerts = [];

  // Try to get real alerts from database
  if (isset($pdo)) {
    try {
      $sql = "SELECT id, title, description, severity, target, source_ip, created_at, status
                    FROM alerts
                    WHERE status = 'active'";
      $params = [];

      if ($severity && in_array($severity, ['critical', 'warning', 'info'])) {
        $sql .= " AND severity = ?";
        $params[] = $severity;
      }

      $sql .= " ORDER BY
                      CASE severity
                        WHEN 'critical' THEN 1
                        WHEN 'warning' THEN 2
                        ELSE 3
                      END,
                      created_at DESC
                      LIMIT ?";
      $params[] = $limit;

      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Format timestamps
      foreach ($alerts as &$alert) {
        $alert['time_ago'] = timeAgo($alert['created_at']);
      }
    } catch (PDOException $e) {
      // Table doesn't exist, use mock data
      $alerts = getMockAlerts($severity, $limit);
    }
  } else {
    // No database, use mock data
    $alerts = getMockAlerts($severity, $limit);
  }

  // Format for frontend - use 'data' key for consistency
  echo json_encode([
    'success' => true,
    'data' => $alerts,
    'count' => count($alerts),
    'timestamp' => date('c')
  ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Internal server error']);
}

/**
 * Generate mock alerts for demo purposes
 */
function getMockAlerts($severityFilter = null, $limit = 10)
{
  $mockAlerts = [
    [
      'id' => 'ALR-001',
      'title' => 'Brute Force Angreb Opdaget',
      'description' => 'Flere fejlede SSH login-forsøg fra samme IP',
      'severity' => 'critical',
      'target' => 'SSH på SRV-01',
      'source_ip' => '185.220.101.42',
      'created_at' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
      'status' => 'active',
      'time_ago' => '2 min siden'
    ],
    [
      'id' => 'ALR-002',
      'title' => 'Anormal Udgående Trafik',
      'description' => 'Uventet stor datamængde sendt til ekstern server',
      'severity' => 'critical',
      'target' => 'DB-CLUSTER-03',
      'source_ip' => '10.0.1.45',
      'created_at' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
      'status' => 'active',
      'time_ago' => '5 min siden'
    ],
    [
      'id' => 'ALR-003',
      'title' => 'Flere Fejlede Logins',
      'description' => '15 fejlede login-forsøg på admin portal',
      'severity' => 'warning',
      'target' => 'Admin Portal',
      'source_ip' => '192.168.1.100',
      'created_at' => date('Y-m-d H:i:s', strtotime('-12 minutes')),
      'status' => 'active',
      'time_ago' => '12 min siden'
    ],
    [
      'id' => 'ALR-004',
      'title' => 'Usædvanlig Port Scanning',
      'description' => 'Systematisk scanning af porte 1-1024',
      'severity' => 'warning',
      'target' => 'Firewall Gateway',
      'source_ip' => '103.75.201.33',
      'created_at' => date('Y-m-d H:i:s', strtotime('-18 minutes')),
      'status' => 'active',
      'time_ago' => '18 min siden'
    ],
    [
      'id' => 'ALR-005',
      'title' => 'SSL Certifikat Udløber Snart',
      'description' => 'api.example.com certifikat udløber om 7 dage',
      'severity' => 'info',
      'target' => 'API Gateway',
      'source_ip' => null,
      'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
      'status' => 'active',
      'time_ago' => '1 time siden'
    ]
  ];

  // Filter by severity if specified
  if ($severityFilter) {
    $mockAlerts = array_filter($mockAlerts, fn($a) => $a['severity'] === $severityFilter);
  }

  return array_slice(array_values($mockAlerts), 0, $limit);
}

/**
 * Convert timestamp to human-readable "time ago" format
 */
function timeAgo($datetime)
{
  $time = strtotime($datetime);
  $diff = time() - $time;

  if ($diff < 60) return $diff . ' sek siden';
  if ($diff < 3600) return floor($diff / 60) . ' min siden';
  if ($diff < 86400) return floor($diff / 3600) . ' time' . (floor($diff / 3600) > 1 ? 'r' : '') . ' siden';
  if ($diff < 604800) return floor($diff / 86400) . ' dag' . (floor($diff / 86400) > 1 ? 'e' : '') . ' siden';

  return date('d/m/Y', $time);
}
