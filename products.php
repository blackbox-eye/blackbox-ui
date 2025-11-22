<?php
require_once __DIR__ . '/includes/i18n.php';
$current_page = 'products';
$page_title = t('products.hero_section.title') . ' | ' . t('site.name');
$meta_description = t('products.hero_section.description');
include 'includes/site-header.php';
?>

<main class="pt-16">
    <section class="py-20 sm:py-24 section-fade-in">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <p class="text-amber-400 uppercase tracking-widest text-sm font-semibold mb-4"><?= t('products.hero_section.tagline') ?></p>
                <h1 class="text-3xl sm:text-5xl font-bold mb-6"><?= t('products.hero_section.title') ?></h1>
                <p class="text-gray-300 text-base sm:text-lg">
                    <?= t('products.hero_section.description') ?>
                </p>
            </div>

            <article class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center section-fade-in mb-16">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-amber-400 mb-4">🛡️ <?= t('products.pve.subtitle') ?> (<?= t('products.pve.title') ?>)</h2>
                    <p class="text-gray-300 mb-6"><?= t('products.pve.description') ?></p>
                    <button class="gemini-trigger-btn bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors"
                        data-module="PVE"
                        data-loading-text="<?= htmlspecialchars(t('common.ai_loading')) ?>">
                        <?= t('products.scenario_button') ?>
                    </button>
                </div>
                <div class="glass-effect rounded-2xl p-6 sm:p-8 h-full">
                    <h3 class="font-semibold text-lg mb-4"><?= t('products.key_features_heading') ?></h3>
                    <ul class="list-disc list-inside text-gray-300 space-y-2">
                        <li><?= t('products.pve.features.scanning') ?></li>
                        <li><?= t('products.pve.features.pentest') ?></li>
                        <li><?= t('products.pve.features.reporting') ?></li>
                        <li><?= t('products.pve.features.simulation') ?></li>
                    </ul>
                </div>
            </article>

            <article class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center section-fade-in">
                <div class="lg:order-last">
                    <h2 class="text-2xl sm:text-3xl font-bold text-amber-400 mb-4">👁️ <?= t('products.greyeye.title') ?></h2>
                    <p class="text-gray-300 mb-6"><?= t('products.greyeye.description') ?></p>
                    <button class="gemini-trigger-btn bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors"
                        data-module="GreyEYE"
                        data-loading-text="<?= htmlspecialchars(t('common.ai_loading')) ?>">
                        <?= t('products.scenario_button') ?>
                    </button>
                </div>
                <div class="glass-effect rounded-2xl p-6 sm:p-8 h-full lg:order-first">
                    <h3 class="font-semibold text-lg mb-4"><?= t('products.key_features_heading') ?></h3>
                    <ul class="list-disc list-inside text-gray-300 space-y-2">
                        <li><?= t('products.greyeye.features.monitoring') ?></li>
                        <li><?= t('products.greyeye.features.analysis') ?></li>
                        <li><?= t('products.greyeye.features.response') ?></li>
                        <li><?= t('products.greyeye.features.intelligence') ?></li>
                    </ul>
                </div>
            </article>
        </div>
    </section>

    <section class="py-16 sm:py-20 bg-gray-900/30 section-fade-in">
        <div class="container mx-auto px-4">
            <div class="glass-effect rounded-3xl p-8 sm:p-12">
                <h2 class="text-3xl sm:text-4xl font-bold mb-6 text-center"><?= t('products.extras.title') ?></h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <?php foreach (['idmatrix', 'aut', 'bridge', 'command'] as $extra): ?>
                        <div>
                            <h3 class="text-xl font-semibold text-amber-400 mb-3"><?= t('products.extras.items.' . $extra . '.title') ?></h3>
                            <p class="text-gray-300"><?= t('products.extras.items.' . $extra . '.body') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-24 section-fade-in">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4"><?= t('products.cta.title') ?></h2>
            <p class="text-gray-400 max-w-2xl mx-auto mb-8">
                <?= t('products.cta.description') ?>
            </p>
            <a href="contact.php" class="inline-flex items-center justify-center bg-amber-400 text-black font-semibold py-3 px-8 rounded-lg hover:bg-amber-500 transition-colors">
                <?= t('products.cta.button') ?>
            </a>
        </div>
    </section>
</main>

<div id="gemini-modal" role="dialog" aria-modal="true" aria-labelledby="gemini-modal-title" class="hidden fixed inset-0 bg-black bg-opacity-70 z-50 flex items-center justify-center p-4">
    <div class="glass-effect rounded-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
        <div class="flex justify-between items-center p-4 border-b border-gray-700">
            <h3 id="gemini-modal-title" class="text-lg sm:text-xl font-bold text-amber-400">AI-genereret trusselsscenarie</h3>
            <button id="close-modal-btn" class="text-gray-400 hover:text-white text-3xl leading-none" aria-label="Luk">&times;</button>
        </div>
        <div id="modal-content" class="p-6 overflow-y-auto flex-1">
            <div id="modal-loader" class="flex flex-col items-center justify-center text-center">
                <div class="spinner"></div>
                <p class="mt-4 text-gray-300">Kontakter Gemini... Genererer relevant scenarie...</p>
            </div>
            <div id="modal-result" class="hidden prose prose-invert max-w-none text-gray-300 text-sm sm:text-base" aria-live="polite"></div>
        </div>
    </div>
</div>

<?php include 'includes/site-footer.php'; ?>
