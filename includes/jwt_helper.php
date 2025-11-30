<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/logging.php';

$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendorAutoload)) {
  require_once $vendorAutoload;
} else {
  error_log('BBX JWT WARNING: vendor/autoload.php missing – install firebase/php-jwt via Composer.');
}

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Determine if JWT issuance is configured.
 */
function bbx_jwt_secret_available(): bool
{
  return defined('BBX_JWT_SECRET') && BBX_JWT_SECRET !== '';
}

function bbx_jwt_library_available(): bool
{
  return class_exists('Firebase\\JWT\\JWT');
}

/**
 * Generate a signed JWT payload for an agent session.
 *
 * @param array $claims Additional claims merged into the token payload.
 *
 * @return array{token:string,payload:array,expires_at:int}
 * @throws RuntimeException When the JWT secret is missing.
 */
function bbx_generate_agent_jwt(array $claims): array
{
  if (!bbx_jwt_secret_available()) {
    throw new RuntimeException('JWT secret is not configured');
  }

  if (!bbx_jwt_library_available()) {
    throw new RuntimeException('firebase/php-jwt library is missing');
  }

  $issuedAt = time();
  $ttl = defined('BBX_JWT_TTL') ? (int) BBX_JWT_TTL : 600;
  if ($ttl < 60) {
    $ttl = 60;
  }
  $expiresAt = $issuedAt + $ttl;

  $defaultClaims = [
    'iss' => BBX_SITE_BASE_URL,
    'aud' => defined('BBX_TS24_CONSOLE_URL') ? BBX_TS24_CONSOLE_URL : BBX_SITE_BASE_URL,
    'iat' => $issuedAt,
    'nbf' => $issuedAt,
    'exp' => $expiresAt,
  ];

  $payload = array_merge($defaultClaims, $claims);
  $token = JWT::encode($payload, BBX_JWT_SECRET, 'HS256');

  return [
    'token' => $token,
    'payload' => $payload,
    'expires_at' => $expiresAt,
  ];
}

/**
 * Issue a JWT based on the authenticated agent row.
 *
 * @param array $agentRow Database row for the agent.
 */
function bbx_issue_agent_sso_token(array $agentRow): array
{
  $role = !empty($agentRow['is_admin']) ? 'admin' : 'operator';
  $scope = $role === 'admin' ? ['dashboard', 'intel', 'admin'] : ['dashboard', 'intel'];
  $nameFallbacks = [
    $agentRow['name'] ?? null,
    $agentRow['full_name'] ?? null,
    $agentRow['display_name'] ?? null,
    $agentRow['agent_id'] ?? null,
  ];
  $displayName = 'GreyEYE Operator';
  foreach ($nameFallbacks as $candidate) {
    if (is_string($candidate) && trim($candidate) !== '') {
      $displayName = trim($candidate);
      break;
    }
  }

  $claims = [
    'sub' => (string) ($agentRow['agent_id'] ?? ''),
    'uid' => (string) ($agentRow['id'] ?? ''),
    'name' => $displayName,
    'role' => $role,
    'scope' => $scope,
  ];

  return bbx_generate_agent_jwt($claims);
}

function bbx_store_agent_sso_token(array $tokenBundle): void
{
  $_SESSION['gdi_sso_token'] = $tokenBundle['token'];
  $_SESSION['gdi_sso_token_exp'] = $tokenBundle['expires_at'];
}

function bbx_store_agent_jwt(array $tokenBundle): void
{
  bbx_store_agent_sso_token($tokenBundle);
}

function bbx_current_agent_jwt(): ?string
{
  $sessionToken = $_SESSION['gdi_sso_token'] ?? $_SESSION['gdi_jwt'] ?? null;
  $sessionExpiry = $_SESSION['gdi_sso_token_exp'] ?? $_SESSION['gdi_jwt_exp'] ?? null;

  if ($sessionToken !== null && $sessionExpiry !== null) {
    if ((int) $sessionExpiry > time()) {
      return (string) $sessionToken;
    }
    bbx_clear_agent_jwt();
  }

  $cookieToken = $_COOKIE['gdi_sso_token'] ?? null;
  if (!is_string($cookieToken) || $cookieToken === '' || !bbx_jwt_secret_available() || !bbx_jwt_library_available()) {
    return null;
  }

  try {
    $decoded = JWT::decode($cookieToken, new Key(BBX_JWT_SECRET, 'HS256'));
    $exp = isset($decoded->exp) ? (int) $decoded->exp : 0;
    if ($exp > time()) {
      $_SESSION['gdi_sso_token'] = $cookieToken;
      $_SESSION['gdi_sso_token_exp'] = $exp;
      return $cookieToken;
    }
  } catch (Throwable $exception) {
    bbx_log_error('JWT_COOKIE_INVALID', ['message' => $exception->getMessage()]);
  }

  bbx_clear_agent_jwt_cookie();
  return null;
}

function bbx_set_agent_jwt_cookie(string $token, int $expiresAt): void
{
  if (headers_sent()) {
    return;
  }

  $options = [
    'expires' => $expiresAt,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax',
  ];

  $cookieDomain = parse_url(BBX_SITE_BASE_URL, PHP_URL_HOST);
  if (is_string($cookieDomain) && $cookieDomain !== '') {
    $options['domain'] = $cookieDomain;
  }

  setcookie('gdi_sso_token', $token, $options);
}

function bbx_clear_agent_jwt(): void
{
  unset($_SESSION['gdi_sso_token'], $_SESSION['gdi_sso_token_exp'], $_SESSION['gdi_jwt'], $_SESSION['gdi_jwt_exp']);
}

function bbx_clear_agent_jwt_cookie(): void
{
  if (headers_sent()) {
    return;
  }

  $options = [
    'expires' => time() - 3600,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax',
  ];

  $cookieDomain = parse_url(BBX_SITE_BASE_URL, PHP_URL_HOST);
  if (is_string($cookieDomain) && $cookieDomain !== '') {
    $options['domain'] = $cookieDomain;
  }

  setcookie('gdi_sso_token', '', $options);
}

function generate_agent_sso_token(array $agentRow): array
{
  return bbx_issue_agent_sso_token($agentRow);
}
