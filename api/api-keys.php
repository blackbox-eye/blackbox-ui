<?php

declare(strict_types=1);

/**
 * API Keys Management Endpoint
 *
 * Handles CRUD operations for API keys.
 * Part of ALPHA Interface GUI.
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

session_start();

// Check authentication
if (!isset($_SESSION['agent_id'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'error' => 'Authentication required']);
  exit;
}

// Load dependencies
require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../includes/apikey-helper.php';
require_once __DIR__ . '/../db.php';

// Verify database connection
if (!defined('BBX_DB_CONNECTED') || !BBX_DB_CONNECTED || !isset($pdo)) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Database unavailable']);
  exit;
}

$agentId = (int) $_SESSION['agent_id'];
$isAdmin = !empty($_SESSION['is_admin']);
$method = $_SERVER['REQUEST_METHOD'];

try {
  switch ($method) {
    case 'GET':
      handleGet($pdo, $agentId, $isAdmin);
      break;

    case 'POST':
      handlePost($pdo, $agentId, $isAdmin);
      break;

    case 'PUT':
    case 'PATCH':
      handleUpdate($pdo, $agentId, $isAdmin);
      break;

    case 'DELETE':
      handleDelete($pdo, $agentId, $isAdmin);
      break;

    default:
      http_response_code(405);
      echo json_encode(['success' => false, 'error' => 'Method not allowed']);
  }
} catch (Exception $e) {
  error_log('API Keys endpoint error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Internal server error']);
}

/**
 * GET - List API keys
 */
function handleGet(PDO $pdo, int $agentId, bool $isAdmin): void
{
  // Check if requesting specific key
  $keyId = $_GET['id'] ?? null;

  if ($keyId) {
    // Get single key
    $stmt = $pdo->prepare("
            SELECT
                id, key_id, key_hint, name, description, agent_id,
                scopes, rate_limit, allowed_ips, allowed_origins,
                last_used_at, last_used_ip, request_count,
                is_active, expires_at, created_at, revoked_at
            FROM api_keys
            WHERE id = :id
            AND (" . ($isAdmin ? "1=1" : "agent_id = :agent_id") . ")
            LIMIT 1
        ");

    $params = [':id' => $keyId];
    if (!$isAdmin) {
      $params[':agent_id'] = $agentId;
    }

    $stmt->execute($params);
    $key = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$key) {
      http_response_code(404);
      echo json_encode(['success' => false, 'error' => 'API key not found']);
      return;
    }

    // Parse JSON fields
    $key['scopes'] = $key['scopes'] ? json_decode($key['scopes'], true) : [];
    $key['allowed_ips'] = $key['allowed_ips'] ? json_decode($key['allowed_ips'], true) : [];
    $key['allowed_origins'] = $key['allowed_origins'] ? json_decode($key['allowed_origins'], true) : [];
    $key['masked_key'] = apikey_mask($key['key_id'], $key['key_hint']);

    echo json_encode(['success' => true, 'key' => $key]);
    return;
  }

  // List all keys
  if ($isAdmin) {
    $stmt = $pdo->prepare("
            SELECT
                k.id, k.key_id, k.key_hint, k.name, k.description, k.agent_id,
                k.scopes, k.rate_limit, k.last_used_at, k.request_count,
                k.is_active, k.expires_at, k.created_at, k.revoked_at,
                a.name AS agent_name
            FROM api_keys k
            JOIN agents a ON k.agent_id = a.id
            ORDER BY k.created_at DESC
        ");
    $stmt->execute();
  } else {
    $stmt = $pdo->prepare("
            SELECT
                id, key_id, key_hint, name, description, agent_id,
                scopes, rate_limit, last_used_at, request_count,
                is_active, expires_at, created_at, revoked_at
            FROM api_keys
            WHERE agent_id = :agent_id
            ORDER BY created_at DESC
        ");
    $stmt->execute([':agent_id' => $agentId]);
  }

  $keys = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Process keys
  foreach ($keys as &$key) {
    $key['scopes'] = $key['scopes'] ? json_decode($key['scopes'], true) : [];
    $key['masked_key'] = apikey_mask($key['key_id'], $key['key_hint']);
  }

  echo json_encode(['success' => true, 'keys' => $keys]);
}

/**
 * POST - Create new API key
 */
function handlePost(PDO $pdo, int $agentId, bool $isAdmin): void
{
  $input = json_decode(file_get_contents('php://input'), true);

  // Validate required fields
  $name = trim($input['name'] ?? '');
  if (empty($name)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Name is required']);
    return;
  }

  if (strlen($name) > 100) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Name too long (max 100 chars)']);
    return;
  }

  // Optional fields
  $description = trim($input['description'] ?? '');
  $scopes = $input['scopes'] ?? [];
  $rateLimit = isset($input['rate_limit']) ? (int) $input['rate_limit'] : 1000;
  $expiresAt = $input['expires_at'] ?? null;
  $allowedIps = $input['allowed_ips'] ?? [];

  // Validate scopes (only allow non-admin scopes for non-admins)
  if (!$isAdmin && !empty($scopes)) {
    $availableScopes = apikey_get_scopes($pdo, false);
    $validScopeNames = array_column($availableScopes, 'scope');

    foreach ($scopes as $scope) {
      if (!in_array($scope, $validScopeNames, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid scope: ' . $scope]);
        return;
      }
    }
  }

  // Generate the API key
  $keyData = apikey_generate();

  // Insert into database
  $stmt = $pdo->prepare("
        INSERT INTO api_keys (
            key_id,
            key_hash,
            key_hint,
            name,
            description,
            agent_id,
            scopes,
            rate_limit,
            allowed_ips,
            expires_at
        ) VALUES (
            :key_id,
            :key_hash,
            :key_hint,
            :name,
            :description,
            :agent_id,
            :scopes,
            :rate_limit,
            :allowed_ips,
            :expires_at
        )
    ");

  $stmt->execute([
    ':key_id' => $keyData['key_id'],
    ':key_hash' => $keyData['key_hash'],
    ':key_hint' => $keyData['key_hint'],
    ':name' => $name,
    ':description' => !empty($description) ? $description : null,
    ':agent_id' => $agentId,
    ':scopes' => !empty($scopes) ? json_encode($scopes) : null,
    ':rate_limit' => $rateLimit > 0 ? $rateLimit : null,
    ':allowed_ips' => !empty($allowedIps) ? json_encode($allowedIps) : null,
    ':expires_at' => $expiresAt
  ]);

  $id = (int) $pdo->lastInsertId();

  // Return the full key (only shown once!)
  echo json_encode([
    'success' => true,
    'message' => 'API key created successfully',
    'key' => [
      'id' => $id,
      'api_key' => $keyData['key'], // Full key - show only once!
      'key_id' => $keyData['key_id'],
      'name' => $name,
      'scopes' => $scopes,
      'created_at' => date('c')
    ]
  ]);
}

/**
 * PUT/PATCH - Update API key
 */
function handleUpdate(PDO $pdo, int $agentId, bool $isAdmin): void
{
  $input = json_decode(file_get_contents('php://input'), true);

  $id = (int) ($input['id'] ?? 0);
  if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Key ID required']);
    return;
  }

  // Check ownership
  $checkStmt = $pdo->prepare("
        SELECT id, agent_id FROM api_keys WHERE id = :id LIMIT 1
    ");
  $checkStmt->execute([':id' => $id]);
  $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

  if (!$existing) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'API key not found']);
    return;
  }

  if (!$isAdmin && (int) $existing['agent_id'] !== $agentId) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Permission denied']);
    return;
  }

  // Build update query
  $updates = [];
  $params = [':id' => $id];

  if (isset($input['name'])) {
    $updates[] = 'name = :name';
    $params[':name'] = trim($input['name']);
  }

  if (isset($input['description'])) {
    $updates[] = 'description = :description';
    $params[':description'] = trim($input['description']) ?: null;
  }

  if (isset($input['scopes'])) {
    $updates[] = 'scopes = :scopes';
    $params[':scopes'] = !empty($input['scopes']) ? json_encode($input['scopes']) : null;
  }

  if (isset($input['rate_limit'])) {
    $updates[] = 'rate_limit = :rate_limit';
    $params[':rate_limit'] = (int) $input['rate_limit'] > 0 ? (int) $input['rate_limit'] : null;
  }

  if (isset($input['is_active'])) {
    $updates[] = 'is_active = :is_active';
    $params[':is_active'] = (bool) $input['is_active'];
  }

  if (isset($input['allowed_ips'])) {
    $updates[] = 'allowed_ips = :allowed_ips';
    $params[':allowed_ips'] = !empty($input['allowed_ips']) ? json_encode($input['allowed_ips']) : null;
  }

  if (empty($updates)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No fields to update']);
    return;
  }

  $stmt = $pdo->prepare("UPDATE api_keys SET " . implode(', ', $updates) . " WHERE id = :id");
  $stmt->execute($params);

  echo json_encode(['success' => true, 'message' => 'API key updated']);
}

/**
 * DELETE - Revoke API key
 */
function handleDelete(PDO $pdo, int $agentId, bool $isAdmin): void
{
  $input = json_decode(file_get_contents('php://input'), true);
  $id = (int) ($input['id'] ?? $_GET['id'] ?? 0);

  if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Key ID required']);
    return;
  }

  // Check ownership
  $checkStmt = $pdo->prepare("
        SELECT id, agent_id FROM api_keys WHERE id = :id LIMIT 1
    ");
  $checkStmt->execute([':id' => $id]);
  $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

  if (!$existing) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'API key not found']);
    return;
  }

  if (!$isAdmin && (int) $existing['agent_id'] !== $agentId) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Permission denied']);
    return;
  }

  // Soft delete (revoke)
  $stmt = $pdo->prepare("
        UPDATE api_keys
        SET is_active = FALSE,
            revoked_at = NOW(),
            revoked_by = :agent_id
        WHERE id = :id
    ");

  $stmt->execute([
    ':id' => $id,
    ':agent_id' => $agentId
  ]);

  echo json_encode(['success' => true, 'message' => 'API key revoked']);
}
