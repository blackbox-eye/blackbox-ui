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
$ccs_console_url = 'ccs-console.php';

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

  $_SESSION['ts24_last_redirect'] = $intel24_console_url;
  $_SESSION['ts24_last_redirect_at'] = time();
} else {
  $intel24_console_url = $intel24_login_url;
  $intel24_requires_login = false; // Intel24 handles its own login
}

// Auto-launch Intel24 if redirected from GDI login with launch=intel24 parameter
if (isset($_GET['launch']) && $_GET['launch'] === 'intel24' && $intel24_has_sso) {
  header('Location: ' . $intel24_console_url);
  exit;
}

include 'includes/site-header.php';
?>

<main id="main-content" class="agent-access-page" tabindex="-1">
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
    <div class="access-console__grid">
      <article class="access-card access-card--wide access-card--ccs" data-console="ccs">
        <div class="access-card__badge access-card__badge--ccs" aria-hidden="true">
          <span><?= t('agent_access.cards.ccs.badge') ?></span>
        </div>
        <div class="access-card__body">
          <h2 class="access-card__title"><?= t('agent_access.cards.ccs.title') ?></h2>
          <p class="access-card__subtitle"><?= t('agent_access.cards.ccs.subtitle') ?></p>
          <p class="access-card__description">
            <?= t('agent_access.cards.ccs.body_primary') ?>
          </p>
          <p class="access-card__description">
            <?= t('agent_access.cards.ccs.body_secondary') ?>
          </p>
          <p class="access-card__note"><?= t('agent_access.cards.ccs.requirements') ?></p>
          <ul class="access-card__meta" aria-label="<?= htmlspecialchars(t('agent_access.cards.ccs.title')) ?>">
            <li><?= t('agent_access.cards.ccs.subtitle') ?></li>
            <li><?= t('agent_access.hero.audit_notice') ?></li>
          </ul>
        </div>
        <div class="access-card__actions">
          <a href="<?= htmlspecialchars($ccs_console_url) ?>"
            class="access-card__cta bbx-btn-pill"
            data-console-launch="ccs">
            <?= t('agent_access.cards.ccs.cta') ?>
          </a>
        </div>
      </article>

      <article class="access-card" data-console="gdi">
        <div class="access-card__badge access-card__badge--gdi" aria-hidden="true">
          <span>GDI</span>
        </div>
        <div class="access-card__body">
          <h2 class="access-card__title"><?= t('agent_access.cards.gdi.title') ?></h2>
          <p class="access-card__description">
            <?= t('agent_access.cards.gdi.description') ?>
          </p>
          <ul class="access-card__meta" aria-label="<?= htmlspecialchars(t('agent_access.cards.gdi.title')) ?>">
            <li><?= t('agent_access.cards.gdi.meta') ?></li>
            <li><?= t('agent_access.hero.audit_notice') ?></li>
          </ul>
        </div>
        <div class="access-card__actions">
          <a href="<?= htmlspecialchars($gdi_console_url) ?>"
            class="access-card__cta bbx-btn-pill"
            data-console-launch="gdi">
            <?= t('agent_access.cards.gdi.cta') ?>
          </a>
        </div>
      </article>

      <article class="access-card" data-console="intel24">
        <div class="access-card__badge access-card__badge--intel24" aria-hidden="true">
          <span>Intel24</span>
        </div>
        <div class="access-card__body">
          <h2 class="access-card__title"><?= t('agent_access.cards.ts24.title') ?></h2>
          <p class="access-card__description">
            <?= t('agent_access.cards.ts24.description') ?>
          </p>
          <p class="access-card__note">
            <?= t('agent_access.cards.ts24.sso_notice') ?>
            <?php if ($ts24_has_sso): ?>
              <span class="access-card__note-badge"><?= t('agent_access.cards.ts24.sso_ready') ?></span>
            <?php endif; ?>
          </p>
          <ul class="access-card__meta" aria-label="<?= htmlspecialchars(t('agent_access.cards.ts24.title')) ?>">
            <li><?= t('agent_access.cards.ts24.meta') ?></li>
            <li><?= t('agent_access.hero.audit_notice') ?></li>
          </ul>
        </div>
        <div class="access-card__actions">
          <a href="<?= htmlspecialchars($intel24_console_url) ?>"
            class="access-card__cta bbx-btn-pill"
            data-console-launch="intel24"
            target="_blank"
            rel="noopener"
            <?= $intel24_has_sso ? 'data-sso-active="true"' : '' ?>>
            <?= t('agent_access.cards.ts24.cta') ?>
          </a>
        </div>
      </article>
    </div>
  </section>
</main>

<?php include 'includes/site-footer.php'; ?>
