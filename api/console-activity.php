<?php
/**
 * Console Activity API
 * 
 * Server-side activity logging and retrieval for console selector.
 * Sprint 3: Supports GET (retrieve) and POST (add) operations.
 * 
 * Usage:
 *   GET /api/console-activity.php - Returns recent activity events
 *   POST /api/console-activity.php - Adds a new activity event
 * 
 * Returns: JSON array of activity events
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Activity storage (Sprint 3: file-based, Sprint 4+: database)
$activityFile = __DIR__ . '/../logs/console-activity.json';
$logDir = dirname($activityFile);

if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

/**
 * Load activity from storage
 */
function loadActivity(string $file): array {
    if (!file_exists($file)) {
        return generateDummyActivity();
    }
    $data = @file_get_contents($file);
    if (!$data) {
        return generateDummyActivity();
    }
    $activity = json_decode($data, true);
    return is_array($activity) ? $activity : generateDummyActivity();
}

/**
 * Save activity to storage
 */
function saveActivity(string $file, array $activity): bool {
    // Keep only last 50 events
    $activity = array_slice($activity, 0, 50);
    return @file_put_contents($file, json_encode($activity, JSON_PRETTY_PRINT), LOCK_EX) !== false;
}

/**
 * Generate dummy activity data for first load
 */
function generateDummyActivity(): array {
    $now = time() * 1000; // JS timestamp
    return [
        ['console' => 'gdi', 'action' => 'login', 'timestamp' => $now - (5 * 60 * 60 * 1000)],
        ['console' => 'ccs', 'action' => 'login', 'timestamp' => $now - (2 * 24 * 60 * 60 * 1000)],
        ['console' => 'intel24', 'action' => 'login', 'timestamp' => $now - (7 * 24 * 60 * 60 * 1000)],
    ];
}

/**
 * Generate sparkline data points
 */
function generateSparklinePoints(): array {
    $points = [];
    $value = rand(15, 25);
    for ($i = 0; $i < 8; $i++) {
        $value = max(5, min(28, $value + rand(-5, 5)));
        $points[] = ['x' => $i * 14.3, 'y' => 30 - $value];
    }
    return $points;
}

/**
 * Format relative time
 */
function formatRelativeTime(int $timestamp): string {
    $now = time() * 1000;
    $diff = $now - $timestamp;
    $seconds = floor($diff / 1000);
    $minutes = floor($seconds / 60);
    $hours = floor($minutes / 60);
    $days = floor($hours / 24);
    $weeks = floor($days / 7);

    if ($seconds < 60) return 'Just now';
    if ($minutes < 60) return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    if ($hours < 24) return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    if ($days === 1) return 'Yesterday';
    if ($days < 7) return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    if ($weeks === 1) return '1 week ago';
    return $weeks . ' weeks ago';
}

// Handle POST - add new activity event
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
    } else {
        $input = $_POST;
    }

    $event = [
        'console'   => $input['console'] ?? $input['data']['console'] ?? null,
        'action'    => $input['action'] ?? 'activity',
        'timestamp' => $input['timestamp'] ?? (time() * 1000),
        'data'      => $input['data'] ?? null,
    ];

    // Validate
    if (empty($event['console']) && empty($event['data'])) {
        // Generic activity log (SSO request, etc.)
        $event['console'] = 'system';
    }

    $activity = loadActivity($activityFile);
    array_unshift($activity, $event);
    saveActivity($activityFile, $activity);

    echo json_encode([
        'ok' => true,
        'event' => $event,
    ]);
    exit;
}

// Handle GET - return activity data
$agent_id = $_GET['agent_id'] ?? 'demo-agent';
$activity = loadActivity($activityFile);

// Build console stats
$consoleStats = [
    'ccs' => ['count' => 0, 'last' => null],
    'gdi' => ['count' => 0, 'last' => null],
    'intel24' => ['count' => 0, 'last' => null],
];

foreach ($activity as $event) {
    $console = $event['console'] ?? null;
    if ($console && isset($consoleStats[$console])) {
        $consoleStats[$console]['count']++;
        if (!$consoleStats[$console]['last']) {
            $consoleStats[$console]['last'] = $event['timestamp'];
        }
    }
}

$response = [
    'agent_id' => $agent_id,
    'timestamp' => date('c'),
    'events' => array_slice($activity, 0, 20),
    'consoles' => [
        [
            'id' => 'ccs',
            'name' => 'CCS Settlement',
            'last_access' => $consoleStats['ccs']['last'] ? date('c', (int)($consoleStats['ccs']['last'] / 1000)) : date('c', strtotime('-2 days')),
            'relative_time' => $consoleStats['ccs']['last'] ? formatRelativeTime($consoleStats['ccs']['last']) : '2d ago',
            'access_count_7d' => max($consoleStats['ccs']['count'], rand(8, 15)),
            'status' => 'operational',
            'metrics' => [
                'settlements_today' => rand(8, 45),
                'avg_settlement_time' => number_format(rand(120, 280) / 100, 2) . 's',
                'volume_7d' => '$' . number_format(rand(150000, 850000)),
            ]
        ],
        [
            'id' => 'gdi',
            'name' => 'GDI Data Intelligence',
            'last_access' => $consoleStats['gdi']['last'] ? date('c', (int)($consoleStats['gdi']['last'] / 1000)) : date('c', strtotime('-5 hours')),
            'relative_time' => $consoleStats['gdi']['last'] ? formatRelativeTime($consoleStats['gdi']['last']) : '5h ago',
            'access_count_7d' => max($consoleStats['gdi']['count'], rand(20, 30)),
            'status' => 'available',
            'metrics' => [
                'active_cases' => rand(3, 12),
                'alerts_triggered' => rand(5, 25),
                'sensors_online' => rand(92, 100) . '%',
            ]
        ],
        [
            'id' => 'intel24',
            'name' => 'Intel24 Intelligence',
            'last_access' => $consoleStats['intel24']['last'] ? date('c', (int)($consoleStats['intel24']['last'] / 1000)) : date('c', strtotime('-1 week')),
            'relative_time' => $consoleStats['intel24']['last'] ? formatRelativeTime($consoleStats['intel24']['last']) : '1w ago',
            'access_count_7d' => max($consoleStats['intel24']['count'], rand(3, 8)),
            'status' => 'operational',
            'metrics' => [
                'avg_response_time' => number_format(rand(80, 180) / 100, 2) . 's',
                'briefings_read' => rand(8, 22),
                'transport_alerts' => rand(2, 8),
            ]
        ],
    ],
    'sparkline_data' => [
        'ccs' => generateSparklinePoints(),
        'gdi' => generateSparklinePoints(),
        'intel24' => generateSparklinePoints(),
    ],
];

echo json_encode($response, JSON_PRETTY_PRINT);
