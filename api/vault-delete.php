<?php

declare(strict_types=1);

/**
 * Intel Vault Delete API
 *
 * Handles secure document deletion (soft delete).
 * Part of ALPHA Interface GUI.
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'error' => 'Method not allowed']);
  exit;
}

session_start();

// Check authentication
if (!isset($_SESSION['agent_id'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'error' => 'Authentication required']);
  exit;
}

// Load dependencies
require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../db.php';

try {
  // Get JSON body
  $input = json_decode(file_get_contents('php://input'), true);

  if (!$input || empty($input['uuid'])) {
    throw new Exception('Ugyldigt dokument-ID');
  }

  $uuid = $input['uuid'];

  if (!preg_match('/^[a-f0-9-]{36}$/i', $uuid)) {
    throw new Exception('Ugyldigt dokument-ID format');
  }

  // Verify database connection
  if (!defined('BBX_DB_CONNECTED') || !BBX_DB_CONNECTED || !isset($pdo)) {
    throw new Exception('Database connection unavailable');
  }

  $agentId = (int) $_SESSION['agent_id'];
  $isAdmin = !empty($_SESSION['is_admin']);

  // Fetch document
  $stmt = $pdo->prepare("
        SELECT id, uuid, uploaded_by, stored_name, deleted_at
        FROM intel_vault_documents
        WHERE uuid = :uuid
        LIMIT 1
    ");

  $stmt->execute([':uuid' => $uuid]);
  $document = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$document) {
    throw new Exception('Dokument ikke fundet');
  }

  if ($document['deleted_at'] !== null) {
    throw new Exception('Dokumentet er allerede slettet');
  }

  // Check permissions (owner or admin can delete)
  if (!$isAdmin && (int) $document['uploaded_by'] !== $agentId) {
    throw new Exception('Du har ikke tilladelse til at slette dette dokument');
  }

  // Soft delete the document
  $deleteStmt = $pdo->prepare("
        UPDATE intel_vault_documents
        SET deleted_at = NOW(), deleted_by = :agent_id
        WHERE id = :id
    ");

  $deleteStmt->execute([
    ':agent_id' => $agentId,
    ':id' => $document['id']
  ]);

  // Log the delete action
  $auditStmt = $pdo->prepare("
        INSERT INTO intel_vault_audit_log (
            document_id,
            agent_id,
            action,
            ip_address,
            user_agent
        ) VALUES (
            :document_id,
            :agent_id,
            'delete',
            :ip_address,
            :user_agent
        )
    ");

  $auditStmt->execute([
    ':document_id' => $document['id'],
    ':agent_id' => $agentId,
    ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
    ':user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)
  ]);

  // Note: Physical file deletion could be done via a cleanup cron job
  // For now, we keep the encrypted file for potential recovery

  echo json_encode([
    'success' => true,
    'message' => 'Dokumentet blev slettet'
  ]);
} catch (Exception $e) {
  error_log('Intel Vault delete error: ' . $e->getMessage());

  http_response_code(400);
  echo json_encode([
    'success' => false,
    'error' => $e->getMessage()
  ]);
}
