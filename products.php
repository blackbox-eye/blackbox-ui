<?php
require_once __DIR__ . '/includes/i18n.php';
$current_page     = 'products';
$page_title       = t('products.hero_section.title') . ' | ' . t('site.name');
$meta_description = t('products.hero_section.description');
include 'includes/site-header.php';

if (!function_exists('bbx_products_module_icon')) {
    function bbx_products_module_icon(string $slug): string
    {
        switch ($slug) {
            case 'greyeye':
                return '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M4 16s4.5-7.5 12-7.5S28 16 28 16s-4.5 7.5-12 7.5S4 16 4 16Z" /><circle cx="16" cy="16" r="4.5" /><path d="M16 11.5V9" /></svg>';
            case 'idmatrix':
                return '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="5" y="5" width="22" height="22" rx="4" /><path d="M10 14h12M10 18h7" /><circle cx="19.5" cy="18" r="1.2" /></svg>';
            case 'aut':
                return '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M6 10h20" /><path d="M8 16h16" /><path d="M10 22h12" /><circle cx="12" cy="10" r="2" /><circle cx="20" cy="16" r="2" /><circle cx="16" cy="22" r="2" /></svg>';
            case 'pve':
            default:
                return '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M16 4 27 8v8.5c0 6.3-5.7 11.5-11 13-5.3-1.5-11-6.7-11-13V8l11-4Z" /><path d="m12.5 16 2.5 2.5 4-4" /></svg>';
        }
    }
}

$products_modules = [
    [
        'slug'        => 'pve',
        'title'       => t('products.pve.title'),
        'subtitle'    => t('products.pve.subtitle'),
        'description' => t('products.pve.description'),
        'features'    => [
            t('products.pve.features.scanning'),
            t('products.pve.features.pentest'),
            t('products.pve.features.reporting'),
            t('products.pve.features.simulation')
        ],
        'data_module' => 'PVE',
    ],
    [
        'slug'        => 'greyeye',
        'title'       => t('products.greyeye.title'),
        'subtitle'    => t('products.greyeye.subtitle'),
        'description' => t('products.greyeye.description'),
        'features'    => [
            t('products.greyeye.features.monitoring'),
            t('products.greyeye.features.analysis'),
            t('products.greyeye.features.response'),
            t('products.greyeye.features.intelligence')
        ],
        'data_module' => 'GreyEYE',
    ],
    [
        'slug'        => 'idmatrix',
        'title'       => t('products.idmatrix.title'),
        'subtitle'    => t('products.idmatrix.subtitle'),
        'description' => t('products.idmatrix.description'),
        'features'    => [
            t('products.idmatrix.features.identity'),
            t('products.idmatrix.features.mfa'),
            t('products.idmatrix.features.zerotrust')
        ],
        'data_module' => 'IDMatrix',
    ],
    [
        'slug'        => 'aut',
        'title'       => t('products.aut.title'),
        'subtitle'    => t('products.aut.subtitle'),
        'description' => t('products.aut.description'),
        'features'    => [
            t('products.aut.features.training'),
            t('products.aut.features.simulation'),
            t('products.aut.features.tracking')
        ],
        'data_module' => 'AUT',
    ],
];

$extra_modules = ['idmatrix', 'aut', 'bridge', 'command'];
?>

<main id="main-content" class="pt-24 lg:pt-28">
    <section class="products-hero section-fade-in">
        <div class="container mx-auto px-4">
            <div class="products-hero__shell">
                <div class="products-hero__content">
                    <p class="products-hero__eyebrow"><?= t('products.hero_section.tagline') ?></p>
                    <h1 class="products-hero__title"><?= t('products.hero_section.title') ?></h1>
                    <p class="products-hero__lede">
                        <?= t('products.hero_section.description') ?>
                    </p>
                    <p class="products-hero__meta">
                        <?= t('products.hero_section.intro') ?>
                    </p>
                </div>
                <div class="products-hero__panel" aria-label="<?= htmlspecialchars(t('products.subtitle')) ?>">
                    <div class="products-hero__panel-card">
                        <span class="products-hero__panel-label"><?= t('products.subtitle') ?></span>
                        <ul class="products-hero__modules" role="list">
                            <?php foreach ($products_modules as $module): ?>
                                <li>
                                    <strong><?= htmlspecialchars($module['title']) ?></strong>
                                    <span><?= htmlspecialchars($module['subtitle']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="products-grid section-fade-in">
        <div class="container mx-auto px-4">
            <div class="module-grid">
                <?php foreach ($products_modules as $module): ?>
                    <?php $labelId = $module['slug'] . '-title'; ?>
                    <article class="module-card" data-module="<?= htmlspecialchars($module['slug']) ?>" aria-labelledby="<?= htmlspecialchars($labelId) ?>">
                        <div class="module-card__badge">
                            <span class="module-card__icon" aria-hidden="true"><?= bbx_products_module_icon($module['slug']) ?></span>
                            <span class="module-card__badge-text"><?= htmlspecialchars($module['subtitle']) ?></span>
                        </div>
                        <h3 id="<?= htmlspecialchars($labelId) ?>" class="module-card__title"><?= htmlspecialchars($module['title']) ?></h3>
                        <p class="module-card__description">
                            <?= htmlspecialchars($module['description']) ?>
                        </p>
                        <ul class="module-card__features" role="list">
                            <?php foreach ($module['features'] as $feature): ?>
                                <li><?= htmlspecialchars($feature) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="module-card__actions">
                            <button type="button"
                                class="gemini-trigger-btn module-card__action module-card__action--primary"
                                data-module="<?= htmlspecialchars($module['data_module']) ?>"
                                data-loading-text="<?= htmlspecialchars(t('common.ai_loading')) ?>">
                                <?= t('products.scenario_button') ?>
                            </button>
                            <a href="contact.php?module=<?= htmlspecialchars($module['slug']) ?>"
                                class="module-card__action module-card__action--ghost">
                                <span><?= t('products.read_more', t('products.cta.button', 'Læs mere')) ?></span>
                                <svg class="module-card__action-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12h14" />
                                    <path d="M13 6l6 6-6 6" />
                                </svg>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="products-extras section-fade-in">
        <div class="container mx-auto px-4">
            <div class="products-extras__shell glass-effect">
                <div class="products-extras__header">
                    <p class="products-extras__eyebrow"><?= t('products.subtitle') ?></p>
                    <h2 class="products-extras__title"><?= t('products.extras.title') ?></h2>
                </div>
                <div class="products-extras-grid">
                    <?php foreach ($extra_modules as $extra): ?>
                        <article class="products-extra-card">
                            <h3><?= t('products.extras.items.' . $extra . '.title') ?></h3>
                            <p><?= t('products.extras.items.' . $extra . '.body') ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="products-cta section-fade-in">
        <div class="container mx-auto px-4">
            <div class="products-cta__shell glass-effect">
                <h2 class="products-cta__title"><?= t('products.cta.title') ?></h2>
                <p class="products-cta__body">
                    <?= t('products.cta.description') ?>
                </p>
                <a href="contact.php" class="products-cta__btn">
                    <?= t('products.cta.button') ?>
                </a>
            </div>
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
