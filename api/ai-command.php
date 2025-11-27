<?php

/**
 * AI Command API
 *
 * Handles AI command submissions and returns command history.
 * POST: Submit new command
 * GET: Retrieve command history
 *
 * @endpoint /api/ai-command.php
 */

header('Content-Type: application/json');
header('Cache-Control: no-store'); // AI commands should never be cached
header('X-Content-Type-Options: nosniff');

session_start();

// Require authentication
if (!isset($_SESSION['agent_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

require_once __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
  switch ($method) {
    case 'POST':
      handleCommandSubmission();
      break;
    case 'GET':
      handleCommandHistory();
      break;
    default:
      http_response_code(405);
      echo json_encode(['error' => 'Method not allowed']);
  }
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
}

/**
 * Handle new command submission
 */
function handleCommandSubmission()
{
  global $pdo;

  $input = json_decode(file_get_contents('php://input'), true);
  $command = trim($input['command'] ?? '');

  if (empty($command)) {
    http_response_code(400);
    echo json_encode(['error' => 'Command is required']);
    return;
  }

  // Validate command length
  if (strlen($command) > 1000) {
    http_response_code(400);
    echo json_encode(['error' => 'Command too long (max 1000 characters)']);
    return;
  }

  $agentId = $_SESSION['agent_id'];
  $commandId = 'CMD-' . strtoupper(bin2hex(random_bytes(4)));

  // Parse command to determine type and action
  $commandType = detectCommandType($command);

  // Generate AI response (simulated for now)
  $response = generateAIResponse($command, $commandType);

  // Try to store in database
  $stored = false;
  if (isset($pdo)) {
    try {
      $stmt = $pdo->prepare("
                INSERT INTO ai_commands (command_id, agent_id, command, command_type, response, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'completed', NOW())
            ");
      $stmt->execute([$commandId, $agentId, $command, $commandType, $response['text']]);
      $stored = true;
    } catch (PDOException $e) {
      // Table doesn't exist, continue without storing
    }
  }

  echo json_encode([
    'success' => true,
    'data' => [
      'command_id' => $commandId,
      'command' => $command,
      'command_type' => $commandType,
      'response' => $response['text'],
      'confidence' => $response['confidence'] ?? null,
      'actions' => $response['actions'] ?? [],
      'stored' => $stored
    ],
    'timestamp' => date('c')
  ], JSON_PRETTY_PRINT);
}

/**
 * Handle command history request
 */
function handleCommandHistory()
{
  global $pdo;

  $limit = min((int)($_GET['limit'] ?? 10), 50);
  $commands = [];

  // Try to get from database
  if (isset($pdo)) {
    try {
      $stmt = $pdo->prepare("
                SELECT command_id, command, command_type, response, status, created_at
                FROM ai_commands
                WHERE agent_id = ?
                ORDER BY created_at DESC
                LIMIT ?
            ");
      $stmt->execute([$_SESSION['agent_id'], $limit]);
      $commands = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Format timestamps
      foreach ($commands as &$cmd) {
        $cmd['time_ago'] = timeAgo($cmd['created_at']);
      }
    } catch (PDOException $e) {
      // Table doesn't exist, use mock data
      $commands = getMockCommandHistory($limit);
    }
  } else {
    $commands = getMockCommandHistory($limit);
  }

  echo json_encode([
    'success' => true,
    'data' => $commands,
    'count' => count($commands),
    'timestamp' => date('c')
  ], JSON_PRETTY_PRINT);
}

/**
 * Detect command type from input
 */
function detectCommandType($command)
{
  $command = strtolower($command);

  if (strpos($command, 'analysér') !== false || strpos($command, 'analyze') !== false) {
    return 'analysis';
  }
  if (strpos($command, 'scan') !== false) {
    return 'scan';
  }
  if (strpos($command, 'blokér') !== false || strpos($command, 'block') !== false) {
    return 'action';
  }
  if (strpos($command, 'rapport') !== false || strpos($command, 'report') !== false) {
    return 'report';
  }
  if (strpos($command, 'status') !== false) {
    return 'status';
  }
  if (strpos($command, 'hjælp') !== false || strpos($command, 'help') !== false) {
    return 'help';
  }

  return 'query';
}

/**
 * Generate AI response based on command type
 */
function generateAIResponse($command, $type)
{
  $responses = [
    'analysis' => [
      'text' => "Analyserer data... Fandt 3 mistænkelige mønstre i netværkstrafikken. IP 185.220.101.42 viser tegn på automatiseret scanning. Anbefaler midlertidig blokering.",
      'confidence' => 0.87,
      'actions' => ['block_ip', 'create_alert', 'add_to_watchlist']
    ],
    'scan' => [
      'text' => "Scanning igangsat. Estimeret tid: 45 sekunder. Scanning omfatter port-analyse, vulnerability check og malware signature matching.",
      'confidence' => 0.95,
      'actions' => ['view_results', 'schedule_recurring']
    ],
    'action' => [
      'text' => "Handling udført. IP-adressen er nu blokeret i firewall. Blokering udløber automatisk om 24 timer medmindre den forlænges.",
      'confidence' => 1.0,
      'actions' => ['undo', 'extend_block', 'view_logs']
    ],
    'report' => [
      'text' => "Rapport genereret. Seneste 24 timer: 12 sikkerhedshændelser, 3 blokerede angreb, 99.8% uptime. Download fuld rapport via Intel Vault.",
      'confidence' => 0.92,
      'actions' => ['download_pdf', 'email_report', 'schedule_daily']
    ],
    'status' => [
      'text' => "Systemstatus: Alle kerneservices operationelle. API Gateway oplever let øget latens (avg. 145ms). Ingen kritiske alarmer aktive.",
      'confidence' => 0.99,
      'actions' => ['view_details', 'run_diagnostics']
    ],
    'help' => [
      'text' => "Tilgængelige kommandoer:\n• analysér [IP/domæne] - Analyser trafik\n• scan [mål] - Kør sikkerhedsscanning\n• blokér [IP] - Bloker IP-adresse\n• rapport [type] - Generer rapport\n• status - Vis systemstatus",
      'confidence' => 1.0,
      'actions' => []
    ],
    'query' => [
      'text' => "Forstået. Behandler forespørgsel... Baseret på tilgængelige data kan jeg bekræfte at systemet fungerer normalt. Har du brug for en specifik analyse?",
      'confidence' => 0.75,
      'actions' => ['clarify', 'suggest_commands']
    ]
  ];

  return $responses[$type] ?? $responses['query'];
}

/**
 * Get mock command history
 */
function getMockCommandHistory($limit)
{
  $mockCommands = [
    [
      'command_id' => 'CMD-A1B2C3D4',
      'command' => 'analysér trafik fra IP 185.220.101.42',
      'command_type' => 'analysis',
      'response' => 'Analyse færdig. Fandt mistænkelig aktivitet.',
      'status' => 'completed',
      'created_at' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
      'time_ago' => '5 min siden'
    ],
    [
      'command_id' => 'CMD-E5F6G7H8',
      'command' => 'status',
      'command_type' => 'status',
      'response' => 'Alle systemer operationelle.',
      'status' => 'completed',
      'created_at' => date('Y-m-d H:i:s', strtotime('-15 minutes')),
      'time_ago' => '15 min siden'
    ],
    [
      'command_id' => 'CMD-I9J0K1L2',
      'command' => 'scan port 443',
      'command_type' => 'scan',
      'response' => 'Port scan afsluttet. Ingen sårbarheder fundet.',
      'status' => 'completed',
      'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
      'time_ago' => '1 time siden'
    ],
    [
      'command_id' => 'CMD-M3N4O5P6',
      'command' => 'blokér 103.75.201.33',
      'command_type' => 'action',
      'response' => 'IP blokeret i 24 timer.',
      'status' => 'completed',
      'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
      'time_ago' => '2 timer siden'
    ]
  ];

  return array_slice($mockCommands, 0, $limit);
}

/**
 * Convert timestamp to human-readable format
 */
function timeAgo($datetime)
{
  $time = strtotime($datetime);
  $diff = time() - $time;

  if ($diff < 60) return $diff . ' sek siden';
  if ($diff < 3600) return floor($diff / 60) . ' min siden';
  if ($diff < 86400) return floor($diff / 3600) . ' time' . (floor($diff / 3600) > 1 ? 'r' : '') . ' siden';

  return date('d/m/Y H:i', $time);
}
