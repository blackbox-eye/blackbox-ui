<?php

declare(strict_types=1);

/**
 * Intel Vault Download API
 * 
 * Handles secure file downloads with decryption.
 * Part of ALPHA Interface GUI.
 */

session_start();

// Check authentication
if (!isset($_SESSION['agent_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

// Load dependencies
require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../includes/vault-encryption.php';
require_once __DIR__ . '/../db.php';

try {
    // Get document UUID
    $uuid = $_GET['uuid'] ?? '';
    
    if (empty($uuid) || !preg_match('/^[a-f0-9-]{36}$/i', $uuid)) {
        throw new Exception('Ugyldigt dokument-ID');
    }
    
    // Verify database connection
    if (!defined('BBX_DB_CONNECTED') || !BBX_DB_CONNECTED || !isset($pdo)) {
        throw new Exception('Database connection unavailable');
    }
    
    $agentId = (int) $_SESSION['agent_id'];
    $isAdmin = !empty($_SESSION['is_admin']);
    
    // Fetch document record
    $stmt = $pdo->prepare("
        SELECT 
            id,
            uuid,
            original_name,
            stored_name,
            mime_type,
            file_size,
            encryption_key_id,
            encryption_iv,
            uploaded_by,
            access_level,
            deleted_at
        FROM intel_vault_documents
        WHERE uuid = :uuid
        LIMIT 1
    ");
    
    $stmt->execute([':uuid' => $uuid]);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$document) {
        throw new Exception('Dokument ikke fundet');
    }
    
    // Check if deleted
    if ($document['deleted_at'] !== null) {
        throw new Exception('Dokumentet er blevet slettet');
    }
    
    // Check access permissions
    $hasAccess = false;
    
    if ($isAdmin) {
        $hasAccess = true;
    } elseif ((int) $document['uploaded_by'] === $agentId) {
        $hasAccess = true;
    } elseif ($document['access_level'] === 'all') {
        $hasAccess = true;
    }
    // TODO: Add team/department checks when those features are implemented
    
    if (!$hasAccess) {
        throw new Exception('Du har ikke adgang til dette dokument');
    }
    
    // Get the encrypted file path
    $uploadDir = vault_ensure_upload_dir();
    $filePath = $uploadDir . $document['stored_name'];
    
    if (!file_exists($filePath)) {
        error_log("Vault file not found: " . $filePath);
        throw new Exception('Dokumentfilen blev ikke fundet');
    }
    
    // Read encrypted content
    $encrypted = file_get_contents($filePath);
    if ($encrypted === false) {
        throw new Exception('Kunne ikke læse dokumentet');
    }
    
    // Unwrap document key
    $documentKey = vault_unwrap_document_key($document['encryption_key_id']);
    
    // Decrypt content
    $content = vault_decrypt_content(
        $encrypted,
        $documentKey,
        $document['encryption_iv']
    );
    
    // Clear sensitive data from memory
    sodium_memzero($documentKey);
    
    // Update last accessed timestamp
    $updateStmt = $pdo->prepare("
        UPDATE intel_vault_documents 
        SET accessed_at = NOW() 
        WHERE id = :id
    ");
    $updateStmt->execute([':id' => $document['id']]);
    
    // Log the download action
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
            'download',
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
    
    // Set headers for file download
    header('Content-Type: ' . $document['mime_type']);
    header('Content-Length: ' . strlen($content));
    header('Content-Disposition: attachment; filename="' . addslashes($document['original_name']) . '"');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');
    
    // Output decrypted content
    echo $content;
    
} catch (Exception $e) {
    error_log('Intel Vault download error: ' . $e->getMessage());
    
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
