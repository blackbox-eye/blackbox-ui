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
    <div class="access-console__grid access-console__grid--equal">
      <!-- CCS Card -->
      <article id="ccs" class="access-card access-card--ccs" data-console="ccs">
        <div class="access-card__header">
          <div class="access-card__badge access-card__badge--ccs" aria-hidden="true">
            <span><?= t('agent_access.cards.ccs.badge') ?></span>
          </div>
          <button type="button" class="access-card__info-btn" aria-label="More info about CCS" data-tooltip-target="ccs-tooltip">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
          </button>
        </div>
        <div class="access-card__body">
          <h2 class="access-card__title"><?= t('agent_access.cards.ccs.title') ?></h2>
          <p class="access-card__tagline"><?= t('agent_access.cards.ccs.subtitle') ?></p>
          <div class="access-card__chips">
            <span class="access-card__chip">Multi-Asset</span>
            <span class="access-card__chip">Settlement</span>
            <span class="access-card__chip">MFA Required</span>
          </div>
        </div>
        <div class="access-card__actions">
          <a href="<?= htmlspecialchars($ccs_console_url) ?>"
            class="access-card__cta bbx-btn-pill access-card__cta--ccs"
            data-console-launch="ccs">
            <?= t('agent_access.cards.ccs.cta') ?>
          </a>
        </div>
        <!-- Expandable details -->
        <div id="ccs-tooltip" class="access-card__tooltip" role="tooltip" hidden>
          <p><?= t('agent_access.cards.ccs.body_primary') ?></p>
          <p><?= t('agent_access.cards.ccs.body_secondary') ?></p>
          <p class="access-card__tooltip-note"><?= t('agent_access.cards.ccs.requirements') ?></p>
        </div>
      </article>

      <!-- GDI Card -->
      <article id="gdi" class="access-card access-card--gdi" data-console="gdi">
        <div class="access-card__header">
          <div class="access-card__badge access-card__badge--gdi" aria-hidden="true">
            <span>GDI</span>
          </div>
          <button type="button" class="access-card__info-btn" aria-label="More info about GDI" data-tooltip-target="gdi-tooltip">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
          </button>
        </div>
        <div class="access-card__body">
          <h2 class="access-card__title"><?= t('agent_access.cards.gdi.title') ?></h2>
          <p class="access-card__tagline">Data Intelligence Platform</p>
          <div class="access-card__chips">
            <span class="access-card__chip">Investigations</span>
            <span class="access-card__chip">Alert Triage</span>
            <span class="access-card__chip">VPN + Badge</span>
          </div>
        </div>
        <div class="access-card__actions">
          <a href="<?= htmlspecialchars($gdi_console_url) ?>"
            class="access-card__cta bbx-btn-pill access-card__cta--gdi"
            data-console-launch="gdi">
            <?= t('agent_access.cards.gdi.cta') ?>
          </a>
        </div>
        <div id="gdi-tooltip" class="access-card__tooltip" role="tooltip" hidden>
          <p><?= t('agent_access.cards.gdi.description') ?></p>
          <p class="access-card__tooltip-note"><?= t('agent_access.cards.gdi.meta') ?></p>
        </div>
      </article>

      <!-- Intel24 Card -->
      <article id="intel24" class="access-card access-card--intel24" data-console="intel24">
        <div class="access-card__header">
          <div class="access-card__badge access-card__badge--intel24" aria-hidden="true">
            <span>I24</span>
          </div>
          <?php if ($intel24_has_sso): ?>
            <span class="access-card__sso-badge">SSO Ready</span>
          <?php endif; ?>
          <button type="button" class="access-card__info-btn" aria-label="More info about Intel24" data-tooltip-target="intel24-tooltip">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
          </button>
        </div>
        <div class="access-card__body">
          <h2 class="access-card__title"><?= t('agent_access.cards.ts24.title') ?></h2>
          <p class="access-card__tagline">Rapid Response Intelligence</p>
          <div class="access-card__chips">
            <span class="access-card__chip">Telemetry</span>
            <span class="access-card__chip">Transport Alerts</span>
            <span class="access-card__chip">Tactical Briefs</span>
          </div>
        </div>
        <div class="access-card__actions">
          <a href="<?= htmlspecialchars($intel24_console_url) ?>"
            class="access-card__cta bbx-btn-pill access-card__cta--intel24"
            data-console-launch="intel24"
            target="_blank"
            rel="noopener"
            <?= $intel24_has_sso ? 'data-sso-active="true"' : '' ?>>
            <?= t('agent_access.cards.ts24.cta') ?>
          </a>
        </div>
        <div id="intel24-tooltip" class="access-card__tooltip" role="tooltip" hidden>
          <p><?= t('agent_access.cards.ts24.description') ?></p>
          <p><?= t('agent_access.cards.ts24.sso_notice') ?></p>
          <p class="access-card__tooltip-note"><?= t('agent_access.cards.ts24.meta') ?></p>
        </div>
      </article>
    </div>
  </section>

  <script>
  // Smooth scroll animation for anchor navigation
  document.addEventListener('DOMContentLoaded', function() {
    // Handle hash on page load
    if (window.location.hash) {
      const targetId = window.location.hash.substring(1);
      const targetCard = document.getElementById(targetId);
      if (targetCard) {
        setTimeout(function() {
          targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
          targetCard.classList.add('access-card--highlight');
          setTimeout(function() {
            targetCard.classList.remove('access-card--highlight');
          }, 2000);
        }, 100);
      }
    }

    // Info button tooltip toggle
    document.querySelectorAll('.access-card__info-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('data-tooltip-target');
        const tooltip = document.getElementById(targetId);
        if (tooltip) {
          const isHidden = tooltip.hasAttribute('hidden');
          // Close all tooltips first
          document.querySelectorAll('.access-card__tooltip').forEach(function(t) {
            t.setAttribute('hidden', '');
          });
          if (isHidden) {
            tooltip.removeAttribute('hidden');
          }
        }
      });
    });

    // Close tooltips when clicking outside
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.access-card__info-btn') && !e.target.closest('.access-card__tooltip')) {
        document.querySelectorAll('.access-card__tooltip').forEach(function(t) {
          t.setAttribute('hidden', '');
        });
      }
    });
  });
  </script>
</main>

<?php include 'includes/site-footer.php'; ?>
