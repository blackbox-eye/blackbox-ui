<?php
require_once __DIR__ . '/includes/i18n.php';
$current_page = 'home';
$page_title = t('home.meta.title');
$meta_description = t('home.meta.description');
include 'includes/site-header.php';
?>

<main id="main-content" class="pt-0">
    <!-- Hero Section: Transparent background to show Matrix rain -->
    <section id="home" class="relative min-h-screen w-full flex items-center justify-center text-center overflow-hidden page-section">
        <canvas id="hero-canvas" class="absolute inset-0 w-full h-full pointer-events-none" style="z-index: -10;"></canvas>

        <!-- Content overlay with subtle gradient -->
        <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-black/60 pointer-events-none" style="z-index: 1;"></div>

        <div class="relative px-4 py-28 sm:py-36 max-w-4xl mx-auto" style="z-index: 10;">
            <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl xl:text-8xl font-black hero-gradient-text leading-tight sm:leading-tight md:leading-[1.05] tracking-tight mb-6 mx-auto max-w-3xl">
                <?= t('home.hero.headline') ?>
            </h1>
            <p class="text-lg sm:text-xl md:text-2xl max-w-2xl mx-auto text-gray-300 mb-10 leading-relaxed">
                <?= t('home.hero.subheadline') ?>
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="demo.php" class="bg-amber-400 text-black font-bold py-3 px-8 sm:py-4 sm:px-10 rounded-lg text-base sm:text-lg hover:scale-105 transition-transform inline-block">
                    <?= t('home.hero.primary_cta') ?>
                </a>
                <a href="products.php" class="border border-amber-400 text-amber-400 font-semibold py-3 px-8 sm:py-4 sm:px-10 rounded-lg text-base sm:text-lg hover:bg-amber-400 hover:text-black transition-transform inline-block">
                    <?= t('home.hero.secondary_cta') ?>
                </a>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 section-fade-in page-section">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <p class="text-amber-400 uppercase tracking-widest text-sm font-semibold mb-3"><?= t('home.tactical.tagline') ?></p>
                <h2 class="text-3xl sm:text-4xl font-bold mb-4"><?= t('home.tactical.title') ?></h2>
                <p class="text-gray-400 text-base sm:text-lg">
                    <?= t('home.tactical.description') ?>
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6">
                <?php
                $home_cards = [
                    ['number' => '01', 'key' => 'about', 'href' => 'about.php'],
                    ['number' => '02', 'key' => 'products', 'href' => 'products.php'],
                    ['number' => '03', 'key' => 'cases', 'href' => 'cases.php'],
                    ['number' => '04', 'key' => 'pricing', 'href' => 'pricing.php'],
                    ['number' => '05', 'key' => 'contact', 'href' => 'contact.php'],
                ];
                foreach ($home_cards as $card):
                    $card_key = 'home.tactical.cards.' . $card['key'];
                ?>
                    <article class="glass-effect rounded-2xl p-6 flex flex-col">
                        <span class="text-xs uppercase tracking-widest text-gray-400 mb-3"><?= htmlspecialchars($card['number']) ?></span>
                        <h3 class="text-2xl font-semibold mb-3 text-white"><?= t($card_key . '.title') ?></h3>
                        <p class="text-gray-300 flex-grow"><?= t($card_key . '.body') ?></p>
                        <a href="<?= htmlspecialchars($card['href']) ?>" class="mt-6 inline-flex items-center text-amber-400 font-semibold">
                            <?= t($card_key . '.cta') ?>
                            <span class="ml-2">→</span>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-24 bg-gray-900/30 section-fade-in page-section page-section--soft">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">
                <div>
                    <p class="text-amber-400 uppercase tracking-widest text-sm font-semibold mb-4"><?= t('home.operational.tagline') ?></p>
                    <h2 class="text-3xl sm:text-4xl font-bold mb-6"><?= t('home.operational.title') ?></h2>
                    <p class="text-gray-300 text-base sm:text-lg mb-6">
                        <?= t('home.operational.description') ?>
                    </p>
                    <ul class="space-y-4 text-gray-300">
                        <?php foreach (['penetration', 'monitoring', 'expertise'] as $bullet): ?>
                            <li class="flex items-start">
                                <span class="text-green-400 mr-3 mt-1">✓</span>
                                <span><strong class="text-white"><?= t('home.operational.bullets.' . $bullet . '.title') ?></strong> <?= t('home.operational.bullets.' . $bullet . '.body') ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="glass-effect rounded-2xl p-8 sm:p-10">
                    <h3 class="text-2xl font-semibold mb-4 text-white"><?= t('home.operational.panel.title') ?></h3>
                    <p class="text-gray-300 mb-6">
                        <?= t('home.operational.panel.description') ?>
                    </p>
                    <form id="quick-assessment-form" class="space-y-4">
                        <div>
                            <label for="quick-assessment" class="block text-sm font-medium text-gray-300 mb-2"><?= t('home.operational.panel.label') ?></label>
                            <textarea id="quick-assessment" rows="4" class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400" placeholder="<?= htmlspecialchars(t('home.operational.panel.placeholder')) ?>"></textarea>
                        </div>
                        <button type="button" id="quick-assessment-btn" class="w-full bg-amber-400 text-black font-semibold py-3 rounded-lg hover:bg-amber-500 transition-colors"
                            data-loading-text="<?= htmlspecialchars(t('home.operational.panel.loading')) ?>">
                            <?= t('home.operational.panel.button') ?>
                        </button>
                    </form>
                    <div id="quick-assessment-output" class="hidden mt-6 border-t border-gray-700 pt-6 text-sm text-gray-200"></div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-24 section-fade-in page-section">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4"><?= t('home.cta.title') ?></h2>
            <p class="text-gray-400 text-base sm:text-lg mb-10">
                <?= t('home.cta.description') ?>
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4 sm:gap-6">
                <a href="cases.php" class="border border-amber-400 text-amber-400 font-semibold py-3 px-8 rounded-lg hover:bg-amber-400 hover:text-black transition-colors">
                    <?= t('home.cta.actions.cases') ?>
                </a>
                <a href="pricing.php" class="border border-amber-400 text-amber-400 font-semibold py-3 px-8 rounded-lg hover:bg-amber-400 hover:text-black transition-colors">
                    <?= t('home.cta.actions.pricing') ?>
                </a>
                <a href="contact.php" class="bg-amber-400 text-black font-semibold py-3 px-8 rounded-lg hover:bg-amber-500 transition-colors">
                    <?= t('home.cta.actions.contact') ?>
                </a>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/site-footer.php'; ?>
