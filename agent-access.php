<?php
session_start();
require_once __DIR__ . '/includes/env.php';
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/jwt_helper.php';

$current_page = 'agent-access';
$page_title = t('agent_access.meta.title');
$meta_description = t('agent_access.meta.description');
$meta_og_title = $page_title;
$meta_og_description = $meta_description;

$gdi_console_url = 'agent-login.php';
$ccs_console_url = 'ccs-login.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['agent_id']) && !empty($_SESSION['agent_id']);

// Intel24 URLs - SSO entry and login fallback (legacy TS24 env var names kept for compatibility)
$intel24_sso_url = defined('BBX_TS24_CONSOLE_URL') ? BBX_TS24_CONSOLE_URL : bbx_env('TS24_CONSOLE_URL', 'https://intel24.blackbox.codes/sso-login');
$intel24_sso_url = rtrim($intel24_sso_url, '/');
$intel24_login_url = bbx_env('TS24_LOGIN_URL', 'https://intel24.blackbox.codes/login');
$intel24_active_jwt = bbx_current_agent_jwt();
$intel24_has_sso = $intel24_active_jwt !== null;

// Determine Intel24 URL based on login state and JWT availability
if ($intel24_has_sso) {
  $separator = strpos($intel24_sso_url, '?') === false ? '?' : '&';
  $intel24_console_url = $intel24_sso_url . $separator . 'sso=' . urlencode($intel24_active_jwt);
  $intel24_requires_login = false;

  $_SESSION['intel24_last_redirect'] = $intel24_console_url;
  $_SESSION['intel24_last_redirect_at'] = time();
} else {
  $intel24_console_url = $intel24_login_url;
  $intel24_requires_login = false; // Intel24 handles its own login
}

// Auto-launch Intel24 if redirected from GDI login with launch=intel24 parameter
if (isset($_GET['launch']) && $_GET['launch'] === 'intel24' && $intel24_has_sso) {
  header('Location: ' . $intel24_console_url);
  exit;
}

// Default to #ccs if no hash provided (handled in JS for SPA-like behavior)
$default_console = 'ccs';

include 'includes/site-header.php';
?>

<main id="main-content" class="agent-access-page" tabindex="-1">
  <!-- Snackbar CSS/JS for feedback -->
  <link rel="stylesheet" href="/assets/css/components/bbx-snackbar.css">
  <link rel="stylesheet" href="/assets/css/components/bbx-icons.css">
  <!-- Sprint 8: Mobile console card layout fix -->
  <link rel="stylesheet" href="/assets/css/components/console-selector-mobile.css" media="(max-width: 768px)">
  <script src="/assets/js/bbx-snackbar.js" defer></script>
  
  <section class="access-hero">
    <div class="access-hero__inner">
      <p class="access-hero__eyebrow"><?= t('agent_access.hero.eyebrow') ?></p>
      <h1 class="access-hero__title"><?= t('agent_access.hero.title') ?></h1>
      <p class="access-hero__lead">
        <?= t('agent_access.hero.lead') ?>
      </p>
      <p class="access-hero__audit">
        <?= t('agent_access.hero.audit_notice') ?>
      </p>
    </div>
  </section>

  <section class="access-console" aria-label="<?= htmlspecialchars(t('agent_access.hero.title')) ?>">
    <?php 
    // Include the reusable console selector component
    $console_context = 'page';
    include __DIR__ . '/includes/console-selector.php'; 
    ?>
  </section>
</main>

<script>
// Default redirect to #ccs if no hash present
(function() {
  if (window.location.pathname.includes('agent-access.php') && !window.location.hash) {
    // Set default hash without triggering navigation
    if (history.replaceState) {
      history.replaceState(null, null, window.location.pathname + '#<?= $default_console ?>');
    }
    // Highlight the default card
    setTimeout(function() {
      if (window.bbxConsoleSelector) {
        window.bbxConsoleSelector.highlightCard('<?= $default_console ?>');
      }
    }, 200);
  }
})();
</script>

<?php include 'includes/site-footer.php'; ?>
