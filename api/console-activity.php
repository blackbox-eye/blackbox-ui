<?php
/**
 * Console Activity API (Stub)
 * 
 * Returns dummy recent activity data for console selector.
 * Sprint 4 will replace this with real data from session/db.
 * 
 * Usage: GET /api/console-activity.php
 * Returns: JSON array of recent console access events
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Access-Control-Allow-Origin: *');

// Simulate agent authentication (Sprint 2+ will use real auth)
$agent_id = $_GET['agent_id'] ?? 'demo-agent';

// Dummy activity data - replace with real DB queries in Sprint 4
$activity_data = [
    'agent_id' => $agent_id,
    'timestamp' => date('c'),
    'consoles' => [
        [
            'id' => 'ccs',
            'name' => 'CCS Settlement',
            'last_access' => date('c', strtotime('-2 days')),
            'relative_time' => '2d ago',
            'access_count_7d' => 12,
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
            'last_access' => date('c', strtotime('-5 hours')),
            'relative_time' => '5h ago',
            'access_count_7d' => 28,
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
            'last_access' => date('c', strtotime('-1 week')),
            'relative_time' => '1w ago',
            'access_count_7d' => 5,
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
    'favorites' => ['gdi'], // Mock favorites - in reality, pulled from localStorage/DB
];

/**
 * Generate dummy sparkline data points for 7 days
 */
function generateSparklinePoints(): array {
    $points = [];
    $value = rand(15, 25);
    for ($i = 0; $i < 8; $i++) {
        $value = max(5, min(28, $value + rand(-5, 5)));
        $points[] = [
            'x' => $i * 14.3, // Spread across 100 width
            'y' => 30 - $value, // Invert for SVG coordinate system
        ];
    }
    return $points;
}

// Output as JSON
echo json_encode($activity_data, JSON_PRETTY_PRINT);
