<?php

declare(strict_types=1);

/**
 * API Keys Helper
 *
 * Functions for generating, validating and managing API keys.
 * Part of ALPHA Interface GUI.
 */

require_once __DIR__ . '/env.php';

/**
 * API Key configuration
 */
define('API_KEY_PREFIX', 'bbx_');
define('API_KEY_LENGTH', 32);
define('API_KEY_ID_LENGTH', 16);

/**
 * Generate a new API key.
 *
 * The key format is: bbx_[key_id]_[random_secret]
 * Example: bbx_abc123def456ghi7_8j9k0l1m2n3o4p5q6r7s8t9u0v1w2x3y4z5a6b7c
 *
 * @return array{key: string, key_id: string, key_hash: string, key_hint: string}
 */
function apikey_generate(): array
{
  // Generate key ID (16 chars, alphanumeric)
  $keyId = bin2hex(random_bytes(8)); // 16 hex chars

  // Generate secret portion (32 chars)
  $secret = bin2hex(random_bytes(16)); // 32 hex chars

  // Full key format
  $fullKey = API_KEY_PREFIX . $keyId . '_' . $secret;

  // Hash the full key for storage
  $keyHash = hash('sha256', $fullKey);

  // Last 8 chars for identification
  $keyHint = substr($secret, -8);

  return [
    'key' => $fullKey,
    'key_id' => $keyId,
    'key_hash' => $keyHash,
    'key_hint' => $keyHint
  ];
}

/**
 * Validate an API key format.
 *
 * @param string $key The API key to validate
 * @return bool True if format is valid
 */
function apikey_validate_format(string $key): bool
{
  // Expected format: bbx_[16 hex chars]_[32 hex chars]
  $pattern = '/^' . preg_quote(API_KEY_PREFIX, '/') . '[a-f0-9]{16}_[a-f0-9]{32}$/';
  return (bool) preg_match($pattern, $key);
}

/**
 * Extract the key ID from a full API key.
 *
 * @param string $key The full API key
 * @return string|null The key ID or null if invalid
 */
function apikey_extract_id(string $key): ?string
{
  if (!apikey_validate_format($key)) {
    return null;
  }

  // Remove prefix and extract ID
  $withoutPrefix = substr($key, strlen(API_KEY_PREFIX));
  $parts = explode('_', $withoutPrefix);

  return $parts[0] ?? null;
}

/**
 * Hash an API key for comparison.
 *
 * @param string $key The API key to hash
 * @return string SHA-256 hash
 */
function apikey_hash(string $key): string
{
  return hash('sha256', $key);
}

/**
 * Verify an API key against its stored hash.
 *
 * @param string $key The API key to verify
 * @param string $storedHash The stored hash
 * @return bool True if key matches hash
 */
function apikey_verify(string $key, string $storedHash): bool
{
  return hash_equals($storedHash, apikey_hash($key));
}

/**
 * Mask an API key for display.
 * Shows only the prefix and hint.
 *
 * @param string $keyId The key ID
 * @param string $keyHint The key hint (last 8 chars)
 * @return string Masked key like "bbx_abc123...xy7z8"
 */
function apikey_mask(string $keyId, string $keyHint): string
{
  return API_KEY_PREFIX . substr($keyId, 0, 6) . '...' . $keyHint;
}

/**
 * Get available API scopes.
 *
 * @param PDO $pdo Database connection
 * @param bool $includeAdminOnly Whether to include admin-only scopes
 * @return array Array of scopes
 */
function apikey_get_scopes(PDO $pdo, bool $includeAdminOnly = false): array
{
  try {
    if ($includeAdminOnly) {
      $stmt = $pdo->query("SELECT * FROM api_scopes ORDER BY scope");
    } else {
      $stmt = $pdo->query("SELECT * FROM api_scopes WHERE is_admin_only = FALSE ORDER BY scope");
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log('Failed to fetch API scopes: ' . $e->getMessage());
    return [];
  }
}

/**
 * Check if an API key has a specific scope.
 *
 * @param array $keyScopes The key's scopes
 * @param string $requiredScope The scope to check
 * @return bool True if key has the scope
 */
function apikey_has_scope(array $keyScopes, string $requiredScope): bool
{
  // Check for wildcard scope
  if (in_array('*', $keyScopes, true)) {
    return true;
  }

  // Check for specific scope
  if (in_array($requiredScope, $keyScopes, true)) {
    return true;
  }

  // Check for category wildcard (e.g., "read:*" matches "read:vault")
  $parts = explode(':', $requiredScope);
  if (count($parts) === 2) {
    $categoryWildcard = $parts[0] . ':*';
    if (in_array($categoryWildcard, $keyScopes, true)) {
      return true;
    }
  }

  return false;
}

/**
 * Validate API key from request and return key data.
 *
 * @param PDO $pdo Database connection
 * @param string|null $apiKey The API key (or null to extract from headers)
 * @return array|null Key data if valid, null otherwise
 */
function apikey_authenticate(PDO $pdo, ?string $apiKey = null): ?array
{
  // Get API key from header if not provided
  if ($apiKey === null) {
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;

    // Handle Bearer token format
    if ($apiKey && strpos($apiKey, 'Bearer ') === 0) {
      $apiKey = substr($apiKey, 7);
    }
  }

  if (!$apiKey || !apikey_validate_format($apiKey)) {
    return null;
  }

  $keyId = apikey_extract_id($apiKey);
  if (!$keyId) {
    return null;
  }

  try {
    $stmt = $pdo->prepare("
            SELECT
                k.*,
                a.name AS agent_name,
                a.email AS agent_email
            FROM api_keys k
            JOIN agents a ON k.agent_id = a.id
            WHERE k.key_id = :key_id
            AND k.is_active = TRUE
            AND k.revoked_at IS NULL
            AND (k.expires_at IS NULL OR k.expires_at > NOW())
            LIMIT 1
        ");

    $stmt->execute([':key_id' => $keyId]);
    $keyData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$keyData) {
      return null;
    }

    // Verify the key hash
    if (!apikey_verify($apiKey, $keyData['key_hash'])) {
      return null;
    }

    // Check IP whitelist
    if (!empty($keyData['allowed_ips'])) {
      $allowedIps = json_decode($keyData['allowed_ips'], true);
      $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';

      if (!empty($allowedIps) && !in_array($clientIp, $allowedIps, true)) {
        return null;
      }
    }

    // Update last used timestamp
    $updateStmt = $pdo->prepare("
            UPDATE api_keys
            SET last_used_at = NOW(),
                last_used_ip = :ip,
                request_count = request_count + 1
            WHERE id = :id
        ");

    $updateStmt->execute([
      ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
      ':id' => $keyData['id']
    ]);

    // Parse scopes
    $keyData['scopes_array'] = !empty($keyData['scopes'])
      ? json_decode($keyData['scopes'], true)
      : [];

    return $keyData;
  } catch (PDOException $e) {
    error_log('API key authentication error: ' . $e->getMessage());
    return null;
  }
}

/**
 * Log API key usage.
 *
 * @param PDO $pdo Database connection
 * @param int $keyId The API key ID
 * @param string $endpoint The endpoint accessed
 * @param string $method HTTP method
 * @param int $statusCode Response status code
 * @param int $responseTime Response time in ms
 */
function apikey_log_usage(
  PDO $pdo,
  int $keyId,
  string $endpoint,
  string $method,
  int $statusCode,
  int $responseTime = 0
): void {
  try {
    $stmt = $pdo->prepare("
            INSERT INTO api_key_usage_log (
                key_id,
                endpoint,
                method,
                status_code,
                response_time,
                ip_address,
                user_agent
            ) VALUES (
                :key_id,
                :endpoint,
                :method,
                :status_code,
                :response_time,
                :ip_address,
                :user_agent
            )
        ");

    $stmt->execute([
      ':key_id' => $keyId,
      ':endpoint' => substr($endpoint, 0, 255),
      ':method' => strtoupper(substr($method, 0, 10)),
      ':status_code' => $statusCode,
      ':response_time' => $responseTime,
      ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
      ':user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)
    ]);
  } catch (PDOException $e) {
    error_log('Failed to log API key usage: ' . $e->getMessage());
  }
}
