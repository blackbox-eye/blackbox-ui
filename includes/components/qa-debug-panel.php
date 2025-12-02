<?php
require_once __DIR__ . '/../env.php';

$qaModeActive = defined('BBX_QA_MODE') ? constant('BBX_QA_MODE') : false;
if (!$qaModeActive) {
  return;
}

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$token = $_SESSION['gdi_sso_token'] ?? null;
$tokenExp = isset($_SESSION['gdi_sso_token_exp']) ? (int) $_SESSION['gdi_sso_token_exp'] : null;
$lastRedirect = $_SESSION['ts24_last_redirect'] ?? null;
$healthEndpoint = '/tools/sso_health.php';
?>
<section id="qa-debug-panel"
  class="qa-debug-panel"
  data-component="qa-panel"
  data-qa-token="<?= htmlspecialchars($token ?? '', ENT_QUOTES) ?>"
  data-qa-exp="<?= $tokenExp ? $tokenExp * 1000 : '' ?>"
  data-qa-redirect="<?= htmlspecialchars($lastRedirect ?? '', ENT_QUOTES) ?>"
  data-qa-health="<?= htmlspecialchars($healthEndpoint, ENT_QUOTES) ?>">
  <header class="qa-debug-panel__header">
    <div>
      <p class="qa-debug-panel__eyebrow">QA MODE ACTIVE</p>
      <h3 class="qa-debug-panel__title">QADebugPanel</h3>
    </div>
    <span class="qa-debug-panel__badge">ALPHA-GUI v1.0.0-QA</span>
  </header>
  <dl class="qa-debug-panel__grid">
    <dt>Last token</dt>
    <dd data-qa-field="token"><?= htmlspecialchars($token ?? 'N/A') ?></dd>
    <dt>Expires at</dt>
    <dd data-qa-field="expires"><?= $tokenExp ? date('c', $tokenExp) : 'N/A' ?></dd>
    <dt>Expires in</dt>
    <dd data-qa-field="expiresCountdown">Calculating…</dd>
    <dt>Last redirect</dt>
    <dd data-qa-field="redirect"><?= htmlspecialchars($lastRedirect ?? 'N/A') ?></dd>
    <dt>Health endpoint</dt>
    <dd data-qa-field="health">Pending…</dd>
  </dl>
  <div class="qa-debug-panel__actions">
    <button type="button" data-qa-action="force-refresh">Force refresh</button>
    <button type="button" data-qa-action="invalidate-cookie">Invalidate cookie</button>
  </div>
</section>
