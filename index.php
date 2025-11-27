<?php
require_once __DIR__ . '/includes/i18n.php';
$current_page = 'home';
$page_title = t('home.meta.title');
$meta_description = t('home.meta.description');
include 'includes/site-header.php';
?>

<main id="main-content" class="pt-0">
    <!-- Hero Section: Blackbox EYE branded with Matrix rain -->
    <section id="home" class="blackbox-section relative min-h-screen w-full flex items-center justify-center text-center overflow-hidden page-section">
        <canvas id="hero-canvas" class="absolute inset-0 w-full h-full pointer-events-none" style="z-index: -10;"></canvas>

        <!-- Content overlay with subtle gradient - adapts to theme -->
        <div class="hero-overlay absolute inset-0 pointer-events-none" style="z-index: 1;"></div>

        <div class="relative px-4 py-28 sm:py-36 max-w-5xl mx-auto" style="z-index: 10;">
            <!-- Blackbox EYE Brand Badge -->
            <div class="flex justify-center lg:justify-start mb-6">
                <span class="blackbox-badge">
                    <svg class="blackbox-badge__icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                    </svg>
                    Blackbox EYE™ Security Platform
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-12 items-center">
                <!-- Hero Text Content -->
                <div class="lg:col-span-3 text-center lg:text-left">
                    <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-black hero-gradient-text leading-tight tracking-tight mb-6">
                        <?= t('home.hero.headline') ?>
                    </h1>
                    <p class="text-lg sm:text-xl md:text-2xl text-gray-300 mb-10 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        <?= t('home.hero.subheadline') ?>
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        <a href="demo.php" class="btn-primary btn-primary--lg">
                            <?= t('home.hero.primary_cta') ?>
                        </a>
                        <a href="products.php" class="btn-secondary btn-secondary--lg">
                            <?= t('home.hero.secondary_cta') ?>
                        </a>
                    </div>

                    <!-- Stats Counter -->
                    <div class="stats-counter mt-10">
                        <div class="stats-counter__item">
                            <div class="stats-counter__value" id="threatsBlocked">847K</div>
                            <div class="stats-counter__label"><?= t('home.hero.stats.threats') ?></div>
                        </div>
                        <div class="stats-counter__item">
                            <div class="stats-counter__value">99.9%</div>
                            <div class="stats-counter__label"><?= t('home.hero.stats.uptime') ?></div>
                        </div>
                        <div class="stats-counter__item">
                            <div class="stats-counter__value">&lt;50ms</div>
                            <div class="stats-counter__label"><?= t('home.hero.stats.response') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Live Feed Widget -->
                <div class="lg:col-span-2 mt-8 lg:mt-0">
                    <div class="live-feed-widget" id="heroLiveFeed">
                        <div class="live-feed-widget__header">
                            <span class="live-feed-widget__indicator" aria-hidden="true"></span>
                            <span class="live-feed-widget__title"><?= t('home.hero.live_feed_title') ?></span>
                        </div>
                        <div class="live-feed-widget__items" id="liveFeedItems">
                            <!-- Feed items populated by JS -->
                            <div class="live-feed-item live-feed-item--warning">
                                <span class="live-feed-item__icon" aria-hidden="true">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                        <line x1="12" y1="9" x2="12" y2="13" />
                                        <line x1="12" y1="17" x2="12.01" y2="17" />
                                    </svg>
                                </span>
                                <div class="live-feed-item__content">
                                    <div class="live-feed-item__title">Suspicious login attempt blocked</div>
                                    <div class="live-feed-item__meta">Source: 203.0.113.42 • Copenhagen, DK</div>
                                </div>
                                <span class="live-feed-item__time">2m ago</span>
                            </div>
                            <div class="live-feed-item live-feed-item--critical">
                                <span class="live-feed-item__icon" aria-hidden="true">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <line x1="12" y1="8" x2="12" y2="12" />
                                        <line x1="12" y1="16" x2="12.01" y2="16" />
                                    </svg>
                                </span>
                                <div class="live-feed-item__content">
                                    <div class="live-feed-item__title">Malware signature matched</div>
                                    <div class="live-feed-item__meta">Quarantined • Pattern #4821</div>
                                </div>
                                <span class="live-feed-item__time">5m ago</span>
                            </div>
                            <div class="live-feed-item live-feed-item--info">
                                <span class="live-feed-item__icon" aria-hidden="true">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <line x1="12" y1="16" x2="12" y2="12" />
                                        <line x1="12" y1="8" x2="12.01" y2="8" />
                                    </svg>
                                </span>
                                <div class="live-feed-item__content">
                                    <div class="live-feed-item__title">Port scan activity detected</div>
                                    <div class="live-feed-item__meta">Ports 22, 443, 3306 • Berlin, DE</div>
                                </div>
                                <span class="live-feed-item__time">12m ago</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 section-fade-in page-section">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <p class="text-[var(--bbx-gold)] uppercase tracking-widest text-sm font-semibold mb-3"><?= t('home.tactical.tagline') ?></p>
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
                        <a href="<?= htmlspecialchars($card['href']) ?>" class="mt-6 inline-flex items-center text-[var(--bbx-gold)] font-semibold hover:text-[var(--bbx-gold-light)] transition-colors">
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
                    <p class="text-[var(--bbx-gold)] uppercase tracking-widest text-sm font-semibold mb-4"><?= t('home.operational.tagline') ?></p>
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
                            <textarea id="quick-assessment" rows="4" class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-[var(--bbx-gold)]" placeholder="<?= htmlspecialchars(t('home.operational.panel.placeholder')) ?>"></textarea>
                        </div>
                        <button type="button" id="quick-assessment-btn" class="btn-primary w-full"
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
                <a href="cases.php" class="btn-secondary">
                    <?= t('home.cta.actions.cases') ?>
                </a>
                <a href="pricing.php" class="btn-secondary">
                    <?= t('home.cta.actions.pricing') ?>
                </a>
                <a href="contact.php" class="btn-primary">
                    <?= t('home.cta.actions.contact') ?>
                </a>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/site-footer.php'; ?>
