<?php
require_once __DIR__ . '/includes/i18n.php';
$current_page = 'home';
$page_title = t('home.meta.title');
$meta_description = t('home.meta.description');
include 'includes/site-header.php';
?>

<main id="main-content" class="pt-0">
    <!-- Hero Section: Graphene Theme with honeycomb pattern -->
    <section id="home" class="graphene-hero relative min-h-screen w-full flex items-center justify-center overflow-hidden page-section">
        <div class="graphene-hero__content relative px-4 py-28 sm:py-36 max-w-6xl mx-auto w-full">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-12 items-center">
                <!-- Hero Text Content -->
                <div class="lg:col-span-3 text-center lg:text-left">
                    <!-- Graphene Brand Badge -->
                    <div class="graphene-badge" role="banner">
                        <svg class="graphene-badge__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M21 16.5c0 .38-.21.71-.53.88l-7.9 4.44c-.16.12-.36.18-.57.18-.21 0-.41-.06-.57-.18l-7.9-4.44A.991.991 0 0 1 3 16.5v-9c0-.38.21-.71.53-.88l7.9-4.44c.16-.12.36-.18.57-.18.21 0 .41.06.57.18l7.9 4.44c.32.17.53.5.53.88v9z" />
                        </svg>
                        <span class="graphene-badge__text"><?= t('home.hero.badge') ?></span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-black graphene-gradient-text leading-tight tracking-tight mb-6">
                        <?= t('home.hero.headline') ?>
                    </h1>
                    <p class="text-lg sm:text-xl md:text-2xl text-[var(--graphene-text)] mb-8 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        <?= t('home.hero.subheadline') ?>
                    </p>

                    <!-- CTA Buttons with Graphene styling -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 mb-8">
                        <a href="demo.php"
                            class="btn-graphene-primary"
                            aria-label="<?= t('home.hero.primary_cta_aria') ?>"
                            title="<?= t('home.hero.primary_cta') ?>">
                            <?= t('home.hero.primary_cta') ?>
                        </a>
                        <a href="products.php"
                            class="btn-graphene-secondary"
                            aria-label="<?= t('home.hero.secondary_cta_aria') ?>"
                            title="<?= t('home.hero.secondary_cta') ?>">
                            <?= t('home.hero.secondary_cta') ?>
                        </a>
                    </div>

                    <!-- Graphene Stats Counter -->
                    <div class="graphene-stats" role="list" aria-label="<?= t('home.hero.stats_aria') ?>">
                        <div class="graphene-stats__item" role="listitem">
                            <div class="graphene-stats__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                </svg>
                            </div>
                            <div class="graphene-stats__content">
                                <div class="graphene-stats__value" id="threatsBlocked">847K</div>
                                <div class="graphene-stats__label"><?= t('home.hero.stats.threats') ?></div>
                            </div>
                        </div>
                        <div class="graphene-stats__item" role="listitem">
                            <div class="graphene-stats__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                            </div>
                            <div class="graphene-stats__content">
                                <div class="graphene-stats__value">99.9%</div>
                                <div class="graphene-stats__label"><?= t('home.hero.stats.uptime') ?></div>
                            </div>
                        </div>
                        <div class="graphene-stats__item" role="listitem">
                            <div class="graphene-stats__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                                </svg>
                            </div>
                            <div class="graphene-stats__content">
                                <div class="graphene-stats__value">&lt;50ms</div>
                                <div class="graphene-stats__label"><?= t('home.hero.stats.response') ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Feed Widget 2.0 -->
                <div class="lg:col-span-2 mt-8 lg:mt-0">
                    <div class="live-feed-2" id="heroLiveFeed" aria-live="polite" aria-atomic="false">
                        <div class="live-feed-2__badge">
                            <span class="live-feed-2__pulse" aria-hidden="true"></span>
                            <span>LIVE</span>
                        </div>
                        <div class="live-feed-2__header">
                            <span class="live-feed-2__title"><?= t('home.hero.live_feed_title') ?></span>
                        </div>
                        <div class="live-feed-2__items" id="liveFeedItems">
                            <div class="live-feed-2__item live-feed-2__item--critical">
                                <span class="severity-tag severity-tag--critical" role="status" aria-label="Critical severity">CRITICAL</span>
                                <div class="live-feed-2__content">
                                    <div class="live-feed-2__event"><?= t('home.hero.feed.malware') ?></div>
                                    <div class="live-feed-2__meta">Quarantined • Pattern #4821</div>
                                </div>
                                <span class="live-feed-2__time">2m</span>
                            </div>
                            <div class="live-feed-2__item live-feed-2__item--warning">
                                <span class="severity-tag severity-tag--warning" role="status" aria-label="Warning severity">WARNING</span>
                                <div class="live-feed-2__content">
                                    <div class="live-feed-2__event"><?= t('home.hero.feed.login') ?></div>
                                    <div class="live-feed-2__meta">203.0.113.42 • Copenhagen, DK</div>
                                </div>
                                <span class="live-feed-2__time">5m</span>
                            </div>
                            <div class="live-feed-2__item live-feed-2__item--info">
                                <span class="severity-tag severity-tag--info" role="status" aria-label="Info severity">INFO</span>
                                <div class="live-feed-2__content">
                                    <div class="live-feed-2__event"><?= t('home.hero.feed.scan') ?></div>
                                    <div class="live-feed-2__meta">Ports 22, 443, 3306 • Berlin, DE</div>
                                </div>
                                <span class="live-feed-2__time">12m</span>
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
