<?php
require_once __DIR__ . '/includes/i18n.php';
$current_page = 'cases';
$page_title = t('cases.hero_section.title') . ' | ' . t('site.name');
$meta_description = t('cases.hero_section.description');
include 'includes/site-header.php';
?>

<main class="pt-16">
    <section class="py-20 sm:py-24 section-fade-in">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <p class="text-amber-400 uppercase tracking-widest text-sm font-semibold mb-4"><?= t('cases.hero_section.tagline') ?></p>
                <h1 class="text-3xl sm:text-5xl font-bold mb-6"><?= t('cases.hero_section.title') ?></h1>
                <p class="text-gray-300 text-base sm:text-lg">
                    <?= t('cases.hero_section.description') ?>
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <?php foreach (['municipality', 'realestate', 'security'] as $case_key): ?>
                    <article class="glass-effect rounded-2xl p-8 flex flex-col">
                        <h2 class="text-xl font-bold mb-2"><?= t('cases.cards.' . $case_key . '.title') ?></h2>
                        <p class="text-gray-400 mb-4"><strong><?= t('cases.labels.challenge') ?>:</strong> <?= t('cases.cards.' . $case_key . '.challenge') ?></p>
                        <p class="text-gray-300 mb-6"><strong><?= t('cases.labels.solution') ?>:</strong> <?= t('cases.cards.' . $case_key . '.solution') ?></p>
                        <div class="mt-auto pt-4 border-t border-gray-700">
                            <p class="text-amber-400 font-bold text-lg"><?= t('cases.labels.result') ?>: <?= t('cases.cards.' . $case_key . '.result') ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-24 bg-gray-900/30 section-fade-in">
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

    <section class="py-16 sm:py-20 section-fade-in">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4"><?= t('cases.cta.title') ?></h2>
            <p class="text-gray-400 max-w-2xl mx-auto mb-8">
                <?= t('cases.cta.description') ?>
            </p>
            <a href="contact.php" class="inline-flex items-center justify-center bg-amber-400 text-black font-semibold py-3 px-8 rounded-lg hover:bg-amber-500 transition-colors">
                <?= t('cases.cta.button') ?>
            </a>
        </div>
    </section>
</main>

<?php include 'includes/site-footer.php'; ?>
