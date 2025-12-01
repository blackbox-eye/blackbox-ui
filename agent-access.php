<?php
require_once __DIR__ . '/includes/env.php';
require_once __DIR__ . '/includes/i18n.php';

$current_page = 'agent-access';
$page_title = t('agent_access.meta.title');
$meta_description = t('agent_access.meta.description');
$meta_og_title = $page_title;
$meta_og_description = $meta_description;

$gdi_console_url = 'agent-login.php';
$ts24_console_url = bbx_env('TS24_CONSOLE_URL', 'https://intel24.tstransport.app/sso-login');

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

      <article class="access-card" data-console="ts24">
        <div class="access-card__badge access-card__badge--ts24" aria-hidden="true">
          <span>TS24</span>
        </div>
        <div class="access-card__body">
          <h2 class="access-card__title"><?= t('agent_access.cards.ts24.title') ?></h2>
          <p class="access-card__description">
            <?= t('agent_access.cards.ts24.description') ?>
          </p>
          <ul class="access-card__meta" aria-label="<?= htmlspecialchars(t('agent_access.cards.ts24.title')) ?>">
            <li><?= t('agent_access.cards.ts24.meta') ?></li>
            <li><?= t('agent_access.hero.audit_notice') ?></li>
          </ul>
        </div>
        <div class="access-card__actions">
          <a href="<?= htmlspecialchars($ts24_console_url) ?>"
            class="access-card__cta bbx-btn-pill"
            data-console-launch="ts24"
            target="_blank"
            rel="noopener">
            <?= t('agent_access.cards.ts24.cta') ?>
          </a>
        </div>
      </article>
    </div>
  </section>
</main>

<?php include 'includes/site-footer.php'; ?>
