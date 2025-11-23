<?php
require_once __DIR__ . '/includes/i18n.php';
$current_page = 'free-scan';
$page_title = t('free_scan.meta.title', 'Gratis sårbarhedstjek | BLACKBOX EYE™');
$meta_description = t('free_scan.meta.description', 'Få en mock-rapport på sekunder og se hvordan vores PVE-modul prioriterer fundne svagheder.');
$structured_data = [
  '@context' => 'https://schema.org',
  '@type' => 'WebPage',
  'name' => strip_tags($page_title),
  'description' => $meta_description,
  'audience' => [
    '@type' => 'BusinessAudience',
    'name' => 'Security decision makers'
  ]
];

include 'includes/site-header.php';
?>

<main id="main-content" class="pt-16">
  <section class="py-16 sm:py-20 section-fade-in">
    <div class="container mx-auto px-4">
      <div class="max-w-3xl mx-auto text-center">
        <p class="text-amber-400 uppercase tracking-widest text-sm font-semibold mb-4">
          <?= t('free_scan.hero.tagline', 'Lead flow') ?>
        </p>
        <h1 class="text-4xl sm:text-5xl md:text-6xl font-black tracking-tight hero-gradient-text mb-6">
          <?= t('free_scan.hero.title', 'Gratis sårbarhedstjek på få sekunder') ?>
        </h1>
        <p class="text-lg sm:text-xl text-gray-300 leading-relaxed">
          <?= t('free_scan.hero.description', 'Indtast jeres domæne og få et overflade-scan med de vigtigste observationer. Book en demo for en fuld PVE-rapport.') ?>
        </p>
      </div>
    </div>
  </section>

  <section class="py-12 sm:py-16 bg-gray-900/40 section-fade-in">
    <div class="container mx-auto px-4">
      <div class="max-w-4xl mx-auto glass-effect rounded-2xl p-6 sm:p-10">
        <div class="mb-8 text-center">
          <h2 class="text-2xl sm:text-3xl font-bold mb-3 text-white">
            <?= t('free_scan.form.title', 'Få din rapport') ?>
          </h2>
          <p class="text-gray-300 max-w-2xl mx-auto text-base sm:text-lg">
            <?= t('free_scan.form.description', 'Vi viser et eksempel på, hvordan vores PVE prioriterer findings baseret på offentligt tilgængelige data.') ?>
          </p>
        </div>

        <form id="vulnerability-scan-form" class="space-y-6" data-endpoint="scan-submit.php" novalidate>
          <div>
            <label for="scan-domain" class="block text-sm font-semibold text-gray-200 mb-2">
              <?= t('free_scan.form.domain_label', 'Domæne') ?>
            </label>
            <input type="text"
              id="scan-domain"
              name="domain"
              inputmode="url"
              autocomplete="off"
              required
              class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-sm sm:text-base text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400"
              placeholder="<?= htmlspecialchars(t('free_scan.form.domain_placeholder', 'example.com')) ?>"
              data-error-message="<?= htmlspecialchars(t('free_scan.validation.domain_required', 'Indtast et domæne.')) ?>"
              data-invalid-message="<?= htmlspecialchars(t('free_scan.validation.domain_invalid', 'Angiv et gyldigt domæne (fx example.com).')) ?>">
            <p class="text-xs text-gray-500 mt-2">
              <?= t('free_scan.form.rate_limit_note', 'Maks. 3 gratis scans pr. organisation pr. døgn.') ?>
            </p>
            <p class="text-sm text-rose-400 mt-2 hidden" data-error-for="scan-domain"></p>
          </div>
          <div>
            <label for="scan-email" class="block text-sm font-semibold text-gray-200 mb-2">
              <?= t('free_scan.form.email_label', 'Arbejdsmail (valgfri)') ?>
            </label>
            <input type="email"
              id="scan-email"
              name="email"
              autocomplete="email"
              class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-sm sm:text-base text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400"
              placeholder="<?= htmlspecialchars(t('free_scan.form.email_placeholder', 'navn@virksomhed.dk')) ?>"
              data-invalid-message="<?= htmlspecialchars(t('free_scan.validation.email_invalid', 'Angiv en gyldig e-mailadresse.')) ?>">
            <p class="text-sm text-rose-400 mt-2 hidden" data-error-for="scan-email"></p>
          </div>

          <div class="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
            <button type="submit"
              class="inline-flex items-center justify-center bg-amber-400 text-black font-semibold py-3 px-6 rounded-lg hover:bg-amber-500 transition-colors"
              data-loading-text="<?= htmlspecialchars(t('free_scan.form.loading', 'Analyserer angrebsfladen...')) ?>">
              <?= t('free_scan.form.submit', 'Generér rapport') ?>
            </button>
            <div class="text-sm text-gray-400">
              <?= t('free_scan.form.privacy_note', 'Vi gemmer ikke det indtastede domæne. Demonstrationen viser kun formatet.') ?>
            </div>
          </div>

          <div id="vulnerability-scan-status" class="hidden mt-4 text-sm" role="alert"></div>
        </form>

        <div id="vulnerability-scan-success" class="hidden mt-10 border border-emerald-600/50 bg-emerald-900/30 rounded-xl p-6 text-emerald-100">
          <h3 class="text-xl font-semibold mb-2">
            <?= t('free_scan.form.success_title', 'Mock-rapport klar') ?>
          </h3>
          <p class="text-sm sm:text-base">
            <?= t('free_scan.form.success_message', 'Nedenfor finder du et overblik. Book en demo for en komplet penetrationstest.') ?>
          </p>
        </div>

        <div id="vulnerability-scan-result"
          class="hidden mt-6"
          aria-live="polite"
          data-label-score="<?= htmlspecialchars(t('free_scan.report.score_label', 'Sikkerhedsscore')) ?>"
          data-label-issues="<?= htmlspecialchars(t('free_scan.report.issues_heading', 'Fremhævede fund')) ?>"
          data-label-no-issues="<?= htmlspecialchars(t('free_scan.report.no_issues', 'Ingen kritiske fund i denne mock.')) ?>"
          data-label-severity-high="<?= htmlspecialchars(t('free_scan.report.severity.high', 'Høj risiko')) ?>"
          data-label-severity-medium="<?= htmlspecialchars(t('free_scan.report.severity.medium', 'Middel risiko')) ?>"
          data-label-severity-low="<?= htmlspecialchars(t('free_scan.report.severity.low', 'Lav risiko')) ?>"
          data-label-next="<?= htmlspecialchars(t('free_scan.report.cta_text', 'Klar til en fuld, automatiseret PVE-rapport?')) ?>"
          data-label-cta="<?= htmlspecialchars(t('free_scan.report.cta_link', 'Book en demo')) ?>"
          data-plan-mvp-basis="<?= htmlspecialchars(t('pricing.mvp.basis.title')) ?>"
          data-plan-mvp-pro="<?= htmlspecialchars(t('pricing.mvp.pro.title')) ?>"
          data-plan-mvp-premium="<?= htmlspecialchars(t('pricing.mvp.premium.title')) ?>"
          data-plan-standard="<?= htmlspecialchars(t('pricing.enterprise.standard.title')) ?>"
          data-plan-premium="<?= htmlspecialchars(t('pricing.enterprise.premium.title')) ?>"
          data-plan-enterprise="<?= htmlspecialchars(t('pricing.enterprise.enterprise.title')) ?>"
          data-label-recommended="<?= htmlspecialchars(t('pricing.calculator.result.recommended_plan', 'Anbefalet pakke')) ?>"
          data-label-disclaimer="<?= htmlspecialchars(t('pricing.calculator.result.estimation_disclaimer', 'Prisen er vejledende.')) ?>"
          data-label-compliance="<?= htmlspecialchars(t('pricing.calculator.result.compliance_note', 'Alle estimater inkluderer standard compliance-rapportering.')) ?>"
          data-demo-url="demo.php"></div>
      </div>
    </div>
  </section>

  <section class="py-16 sm:py-20 section-fade-in">
    <div class="container mx-auto px-4">
      <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6">
          <?= t('free_scan.report.cta_text', 'Klar til en fuld, automatiseret PVE-rapport?') ?>
        </h2>
        <p class="text-gray-300 max-w-2xl mx-auto mb-8">
          <?= t('free_scan.report.cta_description', 'Se hvordan vores specialister aktiverer hele PVE-modulet og leverer en komplet rapport med anbefalinger.') ?>
        </p>
        <a href="demo.php" class="inline-flex items-center justify-center bg-amber-400 text-black font-semibold py-3 px-8 rounded-lg hover:bg-amber-500 transition-colors">
          <?= t('free_scan.report.cta_link', 'Book en demo') ?>
        </a>
      </div>
    </div>
  </section>
</main>

<?php include 'includes/site-footer.php'; ?>
