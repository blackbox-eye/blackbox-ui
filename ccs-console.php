<?php
require_once __DIR__ . '/includes/i18n.php';
$current_page = 'ccs-console';
$page_title = t('agent_access.cards.ccs.title');
$meta_description = t('agent_access.cards.ccs.body_primary');
$meta_og_title = $page_title;
$meta_og_description = $meta_description;

include 'includes/site-header.php';
?>

<main id="main-content" class="graphene-page">
  <section class="graphene-section py-16 md:py-24">
    <div class="container mx-auto px-4">
      <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
        <div class="space-y-6">
          <p class="text-xs uppercase tracking-[0.25em] text-[var(--bbx-gold-light)] font-semibold">CCS Platform</p>
          <h1 class="text-3xl md:text-4xl font-semibold text-white leading-tight">
            <?= t('agent_access.cards.ccs.title') ?>
          </h1>
          <p class="text-lg text-gray-200 max-w-2xl">
            <?= t('agent_access.cards.ccs.body_primary') ?>
          </p>
          <p class="text-base text-gray-300 max-w-2xl">
            <?= t('agent_access.cards.ccs.body_secondary') ?>
          </p>
          <div class="flex flex-wrap gap-3 pt-2">
            <span class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-emerald-400/30 bg-emerald-400/10 text-emerald-100 text-sm font-semibold">Verified supply chain</span>
            <span class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-amber-300/30 bg-amber-300/10 text-amber-50 text-sm font-semibold">Human-in-the-loop</span>
            <span class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-sky-300/30 bg-sky-300/10 text-sky-50 text-sm font-semibold">Real-time intelligence</span>
          </div>
          <div class="flex flex-wrap gap-3">
            <a href="contact.php" class="bbx-btn-pill">
              <?= t('header.cta.book_demo', 'Book demo') ?>
            </a>
            <a href="agent-access.php" class="bbx-btn-pill bbx-btn-pill--ghost">
              <?= t('agent_access.cards.ccs.cta') ?>
            </a>
          </div>
        </div>
        <div class="relative">
          <div class="rounded-3xl border border-emerald-400/20 bg-black/50 backdrop-blur-xl shadow-2xl p-6 lg:p-8">
            <div class="flex items-center justify-between mb-4">
              <div>
                <p class="text-sm text-emerald-100 font-semibold mb-1">Secure overview</p>
                <p class="text-xs text-emerald-200/70">Consular supply corridors</p>
              </div>
              <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-400/15 text-emerald-100 text-xs font-semibold">Live</span>
            </div>
            <div class="space-y-3 text-sm text-gray-200">
              <div class="flex items-center justify-between p-3 rounded-xl border border-white/5 bg-white/5">
                <div>
                  <p class="font-semibold">Critical assets</p>
                  <p class="text-xs text-gray-400">Tracked and verified</p>
                </div>
                <span class="text-xl font-bold text-emerald-200">128</span>
              </div>
              <div class="flex items-center justify-between p-3 rounded-xl border border-white/5 bg-white/5">
                <div>
                  <p class="font-semibold">Active corridors</p>
                  <p class="text-xs text-gray-400">Low-latency telemetry</p>
                </div>
                <span class="text-xl font-bold text-emerald-200">42</span>
              </div>
              <div class="flex items-center justify-between p-3 rounded-xl border border-white/5 bg-white/5">
                <div>
                  <p class="font-semibold">Response time</p>
                  <p class="text-xs text-gray-400">Human validation SLA</p>
                </div>
                <span class="text-xl font-bold text-emerald-200">&lt; 4m</span>
              </div>
            </div>
            <div class="mt-5 p-3 rounded-xl border border-emerald-300/30 bg-emerald-300/10 text-emerald-50 text-sm">
              <?= t('agent_access.cards.ccs.requirements') ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include 'includes/site-footer.php'; ?>
