<?php
require_once __DIR__ . '/includes/i18n.php';
$current_page = 'cases';
$page_title = t('cases.hero_section.title') . ' | ' . t('site.name');
$meta_description = t('cases.hero_section.description');
include 'includes/site-header.php';

$case_metrics = [
    [
        'value' => '68%',
        'label' => t('cases.metrics.response', 'Reduceret responstid'),
        'caption' => t('cases.metrics.response_caption', 'Gennemsnitlig reduktion efter 6 måneder med Blackbox EYE™'),
    ],
    [
        'value' => '42',
        'label' => t('cases.metrics.threats', 'Automatisk standsede forsøg'),
        'caption' => t('cases.metrics.threats_caption', 'Pr. kvartal med PVE™ og AUT i produktion'),
    ],
    [
        'value' => '0',
        'label' => t('cases.metrics.incidents', 'Kritiske hændelser'),
        'caption' => t('cases.metrics.incidents_caption', 'Efter onboarding af Blackbox EYE™ hos Enterprise-kunder'),
    ],
];

$case_navigator = [
    'municipality' => [
        'label' => t('cases.navigator.municipality.label', 'Nordisk kommune'),
        'industry' => t('cases.navigator.municipality.industry', 'Offentlig forvaltning'),
        'impact' => t('cases.navigator.municipality.impact', '72% hurtigere hændelsesrespons og fuld audit-trail til GDPR på 48 timer.'),
        'metrics' => [
            t('cases.navigator.municipality.metric_one', '24/7 overvågning af 9.100 endpoints'),
            t('cases.navigator.municipality.metric_two', 'Automatiseret kommunikation til ledelsesrapportering'),
            t('cases.navigator.municipality.metric_three', 'Opgraderet awareness-program for 4.500 medarbejdere'),
        ],
        'modules' => ['Blackbox EYE™ SOC Co-pilot', 'ID-Matrix™ Identity Shield'],
    ],
    'realestate' => [
        'label' => t('cases.navigator.realestate.label', 'Internationalt ejendomsselskab'),
        'industry' => t('cases.navigator.realestate.industry', 'PropTech & Facility'),
        'impact' => t('cases.navigator.realestate.impact', 'Automatiseret scanning af nye lokationer og 35% lavere compliance-omkostning.'),
        'metrics' => [
            t('cases.navigator.realestate.metric_one', 'Integration til 14 adgangskontrolsystemer'),
            t('cases.navigator.realestate.metric_two', 'Digital tvilling af sikkerhedsmodenhed pr. lokation'),
            t('cases.navigator.realestate.metric_three', 'Automatiseret ISO 27001-dokumentation'),
        ],
        'modules' => ['PVE™ Penetration Engine', 'Bridge™ API Orchestrator'],
    ],
    'security' => [
        'label' => t('cases.navigator.security.label', 'Globalt vagtselskab'),
        'industry' => t('cases.navigator.security.industry', 'Critical infrastructure'),
        'impact' => t('cases.navigator.security.impact', '0 kritiske driftsstop i 18 måneder og 91% hurtigere efterretningsflow.'),
        'metrics' => [
            t('cases.navigator.security.metric_one', 'Fusionscenter med live-threat feeds til 38 lokationer'),
            t('cases.navigator.security.metric_two', 'Integreret insider threat-detection med Blackbox EYE™'),
            t('cases.navigator.security.metric_three', 'Predictive staffing med Blackbox EYE Assistant™ alerts'),
        ],
        'modules' => ['AUT™ Autonomous Testing', 'Blackbox EYE Assistant™ Command'],
    ],
];
?>

<main class="pt-16">
    <section class="py-20 sm:py-24 section-fade-in page-section">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <p class="text-amber-400 uppercase tracking-widest text-sm font-semibold mb-4"><?= t('cases.hero_section.tagline') ?></p>
                <h1 class="text-3xl sm:text-5xl font-bold mb-6"><?= t('cases.hero_section.title') ?></h1>
                <p class="text-gray-300 text-base sm:text-lg">
                    <?= t('cases.hero_section.description') ?>
                </p>
                <ul class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4 text-left">
                    <?php foreach (['evidence', 'frameworks', 'results'] as $bullet_key): ?>
                        <li class="surface-card p-5 flex items-start gap-3">
                            <span class="text-amber-400 mt-1" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </span>
                            <span class="text-sm text-gray-300">
                                <strong class="text-white block text-base mb-1"><?= t('cases.hero_section.bullets.' . $bullet_key . '.title') ?></strong>
                                <?= t('cases.hero_section.bullets.' . $bullet_key . '.description') ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-16">
                <?php foreach ($case_metrics as $metric): ?>
                    <article class="glass-effect case-metric-card">
                        <p class="case-metric-value"><?= htmlspecialchars($metric['value']) ?></p>
                        <p class="case-metric-label"><?= htmlspecialchars($metric['label']) ?></p>
                        <p class="case-metric-caption"><?= htmlspecialchars($metric['caption']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="glass-effect case-navigator">
                <div class="case-navigator-header">
                    <div>
                        <p class="case-navigator-eyebrow"><?= t('cases.navigator.eyebrow', 'Udvalgte transformationer') ?></p>
                        <h2 class="case-navigator-title"><?= t('cases.navigator.title', 'Vælg en branche og se effekten') ?></h2>
                    </div>
                    <p class="case-navigator-subtitle"><?= t('cases.navigator.subtitle', 'Overblikket opdateres løbende med nye nøgletal fra vores implementeringer.') ?></p>
                </div>
                <div class="case-navigator-tabs" role="tablist">
                    <?php $first_tab = true; ?>
                    <?php foreach ($case_navigator as $key => $case): ?>
                        <button type="button"
                            class="case-navigator-tab<?= $first_tab ? ' is-active' : '' ?>"
                            role="tab"
                            data-case-tab="<?= htmlspecialchars($key) ?>"
                            id="case-tab-<?= htmlspecialchars($key) ?>"
                            aria-selected="<?= $first_tab ? 'true' : 'false' ?>"
                            tabindex="<?= $first_tab ? '0' : '-1' ?>"
                            aria-controls="case-panel-<?= htmlspecialchars($key) ?>">
                            <span class="case-navigator-tab__label"><?= htmlspecialchars($case['label']) ?></span>
                            <span class="case-navigator-tab__industry"><?= htmlspecialchars($case['industry']) ?></span>
                        </button>
                        <?php $first_tab = false; ?>
                    <?php endforeach; ?>
                </div>
                <div class="case-navigator-panels">
                    <?php $first_panel = true; ?>
                    <?php foreach ($case_navigator as $key => $case): ?>
                        <article class="case-navigator-panel<?= $first_panel ? ' is-visible' : '' ?>"
                            role="tabpanel"
                            id="case-panel-<?= htmlspecialchars($key) ?>"
                            aria-labelledby="case-tab-<?= htmlspecialchars($key) ?>"
                            data-case-panel="<?= htmlspecialchars($key) ?>"
                            <?= $first_panel ? '' : 'hidden' ?>>
                            <div class="case-navigator-impact">
                                <p class="case-navigator-impact__label"><?= t('cases.navigator.impact_label', 'Impact på <span>12 mdr.</span>') ?></p>
                                <p class="case-navigator-impact__value"><?= htmlspecialchars($case['impact']) ?></p>
                            </div>
                            <div class="case-navigator-details">
                                <div class="case-navigator-metrics">
                                    <?php foreach ($case['metrics'] as $item): ?>
                                        <div class="case-navigator-metric">
                                            <span class="case-navigator-metric__dot" aria-hidden="true"></span>
                                            <p><?= htmlspecialchars($item) ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="case-navigator-modules">
                                    <p class="case-navigator-modules__label"><?= t('cases.navigator.modules_label', 'Kerne-moduler') ?></p>
                                    <ul>
                                        <?php foreach ($case['modules'] as $module): ?>
                                            <li><?= htmlspecialchars($module) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <a href="contact.php" class="header-cta header-cta--pill header-cta--secondary">
                                        <span class="header-cta__label"><?= t('cases.navigator.cta', 'Planlæg et strategisk matchmøde') ?></span>
                                    </a>
                                </div>
                            </div>
                        </article>
                        <?php $first_panel = false; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <?php foreach (['municipality', 'realestate', 'security'] as $case_key): ?>
                    <article class="case-card-graphene flex flex-col" style="background: #0D0F11; border: 1px solid rgba(214,178,94,0.12); border-radius: 16px; padding: 1.75rem;">
                        <h2 class="text-xl font-bold mb-3" style="color: #D6B25E;"><?= t('cases.cards.' . $case_key . '.title') ?></h2>
                        <p class="mb-4" style="color: rgba(244,244,244,0.7);"><strong style="color: rgba(244,244,244,0.9);"><?= t('cases.labels.challenge') ?>:</strong> <?= t('cases.cards.' . $case_key . '.challenge') ?></p>
                        <p class="mb-6" style="color: rgba(244,244,244,0.8);"><strong style="color: rgba(244,244,244,0.9);"><?= t('cases.labels.solution') ?>:</strong> <?= t('cases.cards.' . $case_key . '.solution') ?></p>
                        <div class="mt-auto pt-4" style="border-top: 1px solid rgba(214,178,94,0.15);">
                            <p class="font-bold text-lg" style="color: #D6B25E;"><?= t('cases.labels.result') ?>: <?= t('cases.cards.' . $case_key . '.result') ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-24 bg-gray-900/30 section-fade-in page-section page-section--soft">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto glass-effect rounded-3xl p-6 sm:p-8 lg:p-10">
                <h2 class="text-2xl sm:text-3xl font-bold text-center mb-6"><?= t('cases.analysis.title') ?></h2>
                <p class="text-center text-gray-300 mb-8">
                    <?= t('cases.analysis.description') ?>
                </p>
                <div class="space-y-4">
                    <label for="case-input" class="block text-sm font-medium text-gray-300 mb-2"><?= t('cases.analysis.label') ?></label>
                    <textarea id="case-input" rows="4" class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400" placeholder="<?= htmlspecialchars(t('cases.analysis.placeholder')) ?>"></textarea>
                    <button id="analyze-case-btn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors" data-loading-text="<?= htmlspecialchars(t('cases.analysis.loading')) ?>">
                        <?= t('cases.analysis.button') ?>
                    </button>
                </div>
                <div id="case-analysis-container" class="mt-6 hidden" style="min-height:150px;">
                    <div class="border-t border-gray-700 pt-6">
                        <div id="case-analysis-loader" class="flex flex-col items-center justify-center text-center">
                            <div class="spinner"></div>
                            <p class="mt-4 text-gray-300" data-loading-text="<?= htmlspecialchars(t('cases.analysis.loading')) ?>"><?= t('cases.analysis.loading') ?></p>
                        </div>
                        <div id="case-analysis-result" class="hidden prose prose-invert max-w-none text-gray-200 text-sm sm:text-base" aria-live="polite"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 section-fade-in page-section">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4"><?= t('cases.cta.title') ?></h2>
            <p class="text-gray-400 max-w-2xl mx-auto mb-8">
                <?= t('cases.cta.description') ?>
            </p>
            <a href="contact.php" class="header-cta header-cta--pill header-cta--primary">
                <span class="header-cta__label"><?= t('cases.cta.button') ?></span>
            </a>
        </div>
    </section>
</main>

<?php include 'includes/site-footer.php'; ?>
