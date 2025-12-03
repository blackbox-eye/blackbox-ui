<?php

declare(strict_types=1);

/**
 * Intel Vault Encryption Helper
 *
 * Provides AES-256-GCM encryption for secure document storage.
 * Part of Blackbox UI.
 */

// Load environment configuration
require_once __DIR__ . '/env.php';

/**
 * Encryption configuration
 */
define('VAULT_CIPHER', 'aes-256-gcm');
define('VAULT_KEY_LENGTH', 32); // 256 bits
define('VAULT_IV_LENGTH', 12);  // GCM recommended IV length
define('VAULT_TAG_LENGTH', 16); // Authentication tag length
define('VAULT_UPLOAD_DIR', __DIR__ . '/../uploads/vault/');

/**
 * Get or generate the master encryption key.
 * In production, this should be stored securely (HSM, KMS, etc.)
 *
 * @return string Binary encryption key
 */
function vault_get_master_key(): string
{
  $key = bbx_env('VAULT_ENCRYPTION_KEY', '');

  if (empty($key)) {
    // Fall back to derived key from app secret
    $appSecret = bbx_env('APP_SECRET', 'default-insecure-key');
    $key = hash('sha256', $appSecret . '-vault-encryption', true);
  } else {
    // Decode base64 key from environment
    $key = base64_decode($key);
  }

  if (strlen($key) !== VAULT_KEY_LENGTH) {
    throw new RuntimeException('Invalid vault encryption key length');
  }

  return $key;
}

/**
 * Generate a unique document encryption key.
 * Each document gets its own key for better security isolation.
 *
 * @return array{key: string, key_id: string} Binary key and its identifier
 */
function vault_generate_document_key(): array
{
  $key = random_bytes(VAULT_KEY_LENGTH);
  $keyId = bin2hex(random_bytes(16));

  // Encrypt the document key with master key for storage
  $masterKey = vault_get_master_key();
  $iv = random_bytes(VAULT_IV_LENGTH);
  $tag = '';

  $encryptedKey = openssl_encrypt(
    $key,
    VAULT_CIPHER,
    $masterKey,
    OPENSSL_RAW_DATA,
    $iv,
    $tag,
    '',
    VAULT_TAG_LENGTH
  );

  if ($encryptedKey === false) {
    throw new RuntimeException('Failed to encrypt document key');
  }

  // Store wrapped key: iv + tag + encrypted_key (all base64 encoded together)
  $wrappedKey = base64_encode($iv . $tag . $encryptedKey);

  return [
    'key' => $key,
    'key_id' => $keyId,
    'wrapped_key' => $wrappedKey
  ];
}

/**
 * Unwrap a document encryption key using the master key.
 *
 * @param string $wrappedKey Base64-encoded wrapped key
 * @return string Binary document key
 */
function vault_unwrap_document_key(string $wrappedKey): string
{
  $decoded = base64_decode($wrappedKey);

  if ($decoded === false || strlen($decoded) < VAULT_IV_LENGTH + VAULT_TAG_LENGTH + 1) {
    throw new RuntimeException('Invalid wrapped key format');
  }

  $iv = substr($decoded, 0, VAULT_IV_LENGTH);
  $tag = substr($decoded, VAULT_IV_LENGTH, VAULT_TAG_LENGTH);
  $encryptedKey = substr($decoded, VAULT_IV_LENGTH + VAULT_TAG_LENGTH);

  $masterKey = vault_get_master_key();

  $key = openssl_decrypt(
    $encryptedKey,
    VAULT_CIPHER,
    $masterKey,
    OPENSSL_RAW_DATA,
    $iv,
    $tag
  );

  if ($key === false) {
    throw new RuntimeException('Failed to decrypt document key');
  }

  return $key;
}

/**
 * Encrypt file content using AES-256-GCM.
 *
 * @param string $content File content to encrypt
 * @param string $key Binary encryption key
 * @return array{ciphertext: string, iv: string} Encrypted data and IV (hex)
 */
function vault_encrypt_content(string $content, string $key): array
{
  $iv = random_bytes(VAULT_IV_LENGTH);
  $tag = '';

  $ciphertext = openssl_encrypt(
    $content,
    VAULT_CIPHER,
    $key,
    OPENSSL_RAW_DATA,
    $iv,
    $tag,
    '',
    VAULT_TAG_LENGTH
  );

  if ($ciphertext === false) {
    throw new RuntimeException('Encryption failed: ' . openssl_error_string());
  }

  // Prepend tag to ciphertext for storage
  return [
    'ciphertext' => $tag . $ciphertext,
    'iv' => bin2hex($iv)
  ];
}

/**
 * Decrypt file content using AES-256-GCM.
 *
 * @param string $ciphertext Encrypted content (tag prepended)
 * @param string $key Binary encryption key
 * @param string $iv Initialization vector (hex)
 * @return string Decrypted content
 */
function vault_decrypt_content(string $ciphertext, string $key, string $iv): string
{
  $ivBinary = hex2bin($iv);

  if ($ivBinary === false || strlen($ivBinary) !== VAULT_IV_LENGTH) {
    throw new RuntimeException('Invalid IV format');
  }

  // Extract tag from beginning of ciphertext
  $tag = substr($ciphertext, 0, VAULT_TAG_LENGTH);
  $encrypted = substr($ciphertext, VAULT_TAG_LENGTH);

  $plaintext = openssl_decrypt(
    $encrypted,
    VAULT_CIPHER,
    $key,
    OPENSSL_RAW_DATA,
    $ivBinary,
    $tag
  );

  if ($plaintext === false) {
    throw new RuntimeException('Decryption failed: ' . openssl_error_string());
  }

  return $plaintext;
}

/**
 * Generate a unique filename for encrypted storage.
 *
 * @param string $extension Original file extension
 * @return string Unique filename
 */
function vault_generate_filename(string $extension = ''): string
{
  $uuid = sprintf(
    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000,
    mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff)
  );

  // Encrypted files use .vault extension
  return $uuid . '.vault';
}

/**
 * Generate a UUID v4.
 *
 * @return string UUID string
 */
function vault_generate_uuid(): string
{
  return sprintf(
    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000,
    mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff)
  );
}

/**
 * Ensure the vault upload directory exists and is secure.
 *
 * @return string Path to upload directory
 */
function vault_ensure_upload_dir(): string
{
  $dir = VAULT_UPLOAD_DIR;

  if (!is_dir($dir)) {
    if (!mkdir($dir, 0750, true)) {
      throw new RuntimeException('Failed to create vault upload directory');
    }

    // Create .htaccess to deny direct access
    $htaccess = $dir . '.htaccess';
    file_put_contents($htaccess, "Deny from all\n");

    // Create index.php as additional protection
    file_put_contents($dir . 'index.php', "<?php http_response_code(403); exit('Forbidden');");
  }

  return $dir;
}

/**
 * Get allowed MIME types for upload.
 *
 * @return array<string, string> MIME type => extension mapping
 */
function vault_allowed_mime_types(): array
{
  return [
    // Documents
    'application/pdf' => 'pdf',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'application/vnd.ms-excel' => 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    'application/vnd.ms-powerpoint' => 'ppt',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
    'text/plain' => 'txt',
    'text/csv' => 'csv',
    'application/rtf' => 'rtf',

    // Images
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
    'image/svg+xml' => 'svg',

    // Archives
    'application/zip' => 'zip',
    'application/x-rar-compressed' => 'rar',
    'application/x-7z-compressed' => '7z',

    // Data
    'application/json' => 'json',
    'application/xml' => 'xml',
    'text/xml' => 'xml',
  ];
}

/**
 * Get max upload size in bytes.
 *
 * @return int Max file size
 */
function vault_max_upload_size(): int
{
  // Default 50MB, can be overridden in environment
  return (int) bbx_env('VAULT_MAX_FILE_SIZE', 52428800);
}

/**
 * Format file size for display.
 *
 * @param int $bytes File size in bytes
 * @return string Formatted size
 */
function vault_format_size(int $bytes): string
{
  $units = ['B', 'KB', 'MB', 'GB', 'TB'];
  $i = 0;

  while ($bytes >= 1024 && $i < count($units) - 1) {
    $bytes /= 1024;
    $i++;
  }

  return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Get classification levels with labels.
 *
 * @return array<string, array{label: string, color: string}>
 */
function vault_classification_levels(): array
{
  return [
    'unclassified' => [
      'label' => 'Uklassificeret',
      'color' => '#6c757d'
    ],
    'internal' => [
      'label' => 'Intern',
      'color' => '#17a2b8'
    ],
    'confidential' => [
      'label' => 'Fortrolig',
      'color' => '#ffc107'
    ],
    'secret' => [
      'label' => 'Hemmelig',
      'color' => '#fd7e14'
    ],
    'top_secret' => [
      'label' => 'Strengt fortrolig',
      'color' => '#dc3545'
    ]
  ];
}
