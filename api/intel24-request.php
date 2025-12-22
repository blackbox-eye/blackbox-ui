<?php
/**
 * Intel24 Access Request API
 * 
 * POST /api/intel24-request.php - Submit Intel24 access request
 * 
 * Stores requests in JSON file (production would use database)
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// CORS headers for local development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Validate required fields
$required = ['name', 'email', 'organization', 'role'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit;
    }
}

// Validate email
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

// Generate request ID if not provided
$request_id = $data['request_id'] ?? 'I24-' . strtoupper(base_convert(time(), 10, 36)) . '-' . strtoupper(bin2hex(random_bytes(2)));

// Build request record
$request = [
    'request_id' => $request_id,
    'name' => htmlspecialchars($data['name']),
    'email' => htmlspecialchars($data['email']),
    'organization' => htmlspecialchars($data['organization']),
    'role' => htmlspecialchars($data['role']),
    'usecase' => htmlspecialchars($data['usecase'] ?? ''),
    'console' => 'intel24',
    'status' => 'pending',
    'created_at' => date('c'),
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
];

// Store request (JSON file for demo, use DB in production)
$requests_file = __DIR__ . '/../data/intel24-requests.json';
$requests_dir = dirname($requests_file);

if (!is_dir($requests_dir)) {
    mkdir($requests_dir, 0755, true);
}

$requests = [];
if (file_exists($requests_file)) {
    $requests = json_decode(file_get_contents($requests_file), true) ?: [];
}

$requests[] = $request;

// Keep only last 100 requests
$requests = array_slice($requests, -100);

file_put_contents($requests_file, json_encode($requests, JSON_PRETTY_PRINT));

// Return success
http_response_code(201);
echo json_encode([
    'success' => true,
    'request_id' => $request_id,
    'status' => 'pending',
    'message' => 'Intel24 access request submitted successfully'
]);
