<?php

declare(strict_types=1);

/**
 * Intel Vault Upload API
 * 
 * Handles secure file uploads with AES-256-GCM encryption.
 * Part of ALPHA Interface GUI.
 */

// CORS headers for API
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

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
require_once __DIR__ . '/../includes/vault-encryption.php';
require_once __DIR__ . '/../db.php';

try {
    // Verify database connection
    if (!defined('BBX_DB_CONNECTED') || !BBX_DB_CONNECTED || !isset($pdo)) {
        throw new Exception('Database connection unavailable');
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'Filen overstiger upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'Filen overstiger MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'Filen blev kun delvist uploadet',
            UPLOAD_ERR_NO_FILE => 'Ingen fil blev uploadet',
            UPLOAD_ERR_NO_TMP_DIR => 'Manglende midlertidig mappe',
            UPLOAD_ERR_CANT_WRITE => 'Kunne ikke skrive filen til disk',
            UPLOAD_ERR_EXTENSION => 'En PHP-udvidelse stoppede uploaden'
        ];
        
        $errorCode = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
        $errorMsg = $errorMessages[$errorCode] ?? 'Ukendt uploadfejl';
        
        throw new Exception($errorMsg);
    }
    
    $file = $_FILES['file'];
    $agentId = (int) $_SESSION['agent_id'];
    
    // Validate file size
    $maxSize = vault_max_upload_size();
    if ($file['size'] > $maxSize) {
        throw new Exception('Filen er for stor. Max størrelse: ' . vault_format_size($maxSize));
    }
    
    // Validate MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $allowedTypes = vault_allowed_mime_types();
    
    if (!isset($allowedTypes[$mimeType])) {
        throw new Exception('Filtypen er ikke tilladt: ' . $mimeType);
    }
    
    // Get additional form data
    $classification = $_POST['classification'] ?? 'internal';
    $description = trim($_POST['description'] ?? '');
    $tags = isset($_POST['tags']) ? json_decode($_POST['tags'], true) : [];
    
    // Validate classification
    $validClassifications = array_keys(vault_classification_levels());
    if (!in_array($classification, $validClassifications, true)) {
        $classification = 'internal';
    }
    
    // Sanitize tags
    if (!is_array($tags)) {
        $tags = [];
    }
    $tags = array_filter(array_map('trim', $tags));
    $tags = array_slice($tags, 0, 10); // Max 10 tags
    
    // Read file content
    $content = file_get_contents($file['tmp_name']);
    if ($content === false) {
        throw new Exception('Kunne ikke læse den uploadede fil');
    }
    
    // Calculate hash before encryption
    $fileHash = hash('sha256', $content);
    
    // Generate document encryption key
    $keyData = vault_generate_document_key();
    
    // Encrypt the file content
    $encrypted = vault_encrypt_content($content, $keyData['key']);
    
    // Ensure upload directory exists
    $uploadDir = vault_ensure_upload_dir();
    
    // Generate unique filename
    $originalName = basename($file['name']);
    $originalExtension = pathinfo($originalName, PATHINFO_EXTENSION);
    $storedName = vault_generate_filename($originalExtension);
    $storedPath = $uploadDir . $storedName;
    
    // Write encrypted content to disk
    if (file_put_contents($storedPath, $encrypted['ciphertext']) === false) {
        throw new Exception('Kunne ikke gemme den krypterede fil');
    }
    
    // Generate document UUID
    $uuid = vault_generate_uuid();
    
    // Insert record into database
    $stmt = $pdo->prepare("
        INSERT INTO intel_vault_documents (
            uuid,
            original_name,
            stored_name,
            mime_type,
            file_size,
            file_hash,
            classification,
            encryption_key_id,
            encryption_iv,
            tags,
            description,
            uploaded_by,
            access_level
        ) VALUES (
            :uuid,
            :original_name,
            :stored_name,
            :mime_type,
            :file_size,
            :file_hash,
            :classification,
            :encryption_key_id,
            :encryption_iv,
            :tags,
            :description,
            :uploaded_by,
            'owner'
        )
    ");
    
    $stmt->execute([
        ':uuid' => $uuid,
        ':original_name' => $originalName,
        ':stored_name' => $storedName,
        ':mime_type' => $mimeType,
        ':file_size' => $file['size'],
        ':file_hash' => $fileHash,
        ':classification' => $classification,
        ':encryption_key_id' => $keyData['wrapped_key'],
        ':encryption_iv' => $encrypted['iv'],
        ':tags' => !empty($tags) ? json_encode($tags) : null,
        ':description' => !empty($description) ? $description : null,
        ':uploaded_by' => $agentId
    ]);
    
    $documentId = (int) $pdo->lastInsertId();
    
    // Log the upload action
    $auditStmt = $pdo->prepare("
        INSERT INTO intel_vault_audit_log (
            document_id,
            agent_id,
            action,
            ip_address,
            user_agent,
            details
        ) VALUES (
            :document_id,
            :agent_id,
            'upload',
            :ip_address,
            :user_agent,
            :details
        )
    ");
    
    $auditStmt->execute([
        ':document_id' => $documentId,
        ':agent_id' => $agentId,
        ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ':user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ':details' => json_encode([
            'original_name' => $originalName,
            'file_size' => $file['size'],
            'classification' => $classification
        ])
    ]);
    
    // Clear plaintext from memory
    $content = null;
    unset($content);
    sodium_memzero($keyData['key']);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'document' => [
            'uuid' => $uuid,
            'name' => $originalName,
            'size' => vault_format_size($file['size']),
            'classification' => $classification,
            'uploaded_at' => date('c')
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Intel Vault upload error: ' . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
