<?php
if (defined('BBX_QA_BOOTSTRAP_EMITTED')) {
  return;
}

define('BBX_QA_BOOTSTRAP_EMITTED', true);

require_once __DIR__ . '/env.php';

$qaFlag = defined('BBX_QA_MODE') ? BBX_QA_MODE : (strtolower(bbx_env('QA_MODE', '0')) === '1');
if ($qaFlag && session_status() === PHP_SESSION_NONE) {
  session_start();
}

$activeToken = null;
$activeTokenExp = null;
$lastRedirect = null;
if ($qaFlag && session_status() === PHP_SESSION_ACTIVE) {
  $activeToken = $_SESSION['gdi_sso_token'] ?? null;
  $expiresAt = isset($_SESSION['gdi_sso_token_exp']) ? (int) $_SESSION['gdi_sso_token_exp'] : null;
  $activeTokenExp = $expiresAt ? $expiresAt * 1000 : null;
  $lastRedirect = $_SESSION['ts24_last_redirect'] ?? null;
}

$cookieFingerprint = null;
if ($qaFlag && isset($_COOKIE['gdi_sso_token']) && $_COOKIE['gdi_sso_token'] !== '') {
  $cookieFingerprint = substr(sha1($_COOKIE['gdi_sso_token']), 0, 16);
}

$protectedRoutes = [
  '/dashboard.php',
  '/admin.php',
  '/settings.php',
  '/api-keys.php',
  '/intel-vault.php',
  '/download-logs.php',
  '/access-requests.php'
];

$routerConfig = [
  'loginRoute' => '/agent-login.php',
  'fallbackRoute' => '/agent-login.php',
  'protectedRoutes' => $protectedRoutes,
  'ts24Entry' => defined('BBX_TS24_CONSOLE_URL') ? BBX_TS24_CONSOLE_URL : 'https://intel24.blackbox.codes/sso-login',
  'expectedIssuer' => BBX_SITE_BASE_URL,
  'expectedAudience' => defined('BBX_TS24_CONSOLE_URL') ? BBX_TS24_CONSOLE_URL : BBX_SITE_BASE_URL,
  'qaMode' => $qaFlag,
];

$qaState = [
  'token' => $qaFlag ? $activeToken : null,
  'tokenExpiresAt' => $qaFlag ? $activeTokenExp : null,
  'lastRedirect' => $qaFlag ? $lastRedirect : null,
  'cookieFingerprint' => $qaFlag ? $cookieFingerprint : null,
  'healthEndpoint' => '/tools/sso_health.php',
];
?>
<?php if ($qaFlag): ?>
  <link rel="stylesheet" href="/assets/css/qa-mode.css">
<?php endif; ?>
<script>
  window.BBX_QA_MODE = <?= $qaFlag ? 'true' : 'false' ?>;
  window.BBX_ROUTER_CONFIG = <?= json_encode($routerConfig, JSON_UNESCAPED_SLASHES) ?>;
  window.BBX_QA_STATE = <?= json_encode($qaState, JSON_UNESCAPED_SLASHES) ?>;
  if (window.BBX_QA_MODE) {
    console.info('[Blackbox-SSO] QA MODE ACTIVE');
  }
</script>
<?php
