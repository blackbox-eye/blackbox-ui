<?php
require_once __DIR__ . '/includes/i18n.php';
$current_page = 'home';
$page_title = t('home.meta.title');
$meta_description = t('home.meta.description');
include 'includes/site-header.php';
?>

<main id="main-content" class="pt-0">
    <!-- Hero Section: Graphene Theme with honeycomb pattern -->
    <section id="home" class="graphene-hero relative min-h-screen w-full flex items-center overflow-hidden page-section">
        <div class="graphene-hero__content relative px-4 sm:px-8 py-28 sm:py-36 max-w-7xl mx-auto w-full">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-16 items-center">
                <!-- Hero Text Content - Left Aligned -->
                <div class="lg:col-span-3 text-left">
                    <!-- Graphene Brand Badge - Simplified without icon -->
                    <div class="graphene-badge" role="banner">
                        <span class="graphene-badge__pulse" aria-hidden="true"></span>
                        <span class="graphene-badge__text"><?= t('home.hero.badge') ?></span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-black graphene-gradient-text leading-tight tracking-tight mb-6">
                        <?= t('home.hero.headline') ?>
                    </h1>
                    <p class="text-lg sm:text-xl md:text-2xl hero-subheadline mb-10 leading-relaxed max-w-2xl">
                        <?= t('home.hero.subheadline') ?>
                    </p>

                    <!-- CTA Buttons with improved Graphene styling -->
                    <div class="flex flex-col sm:flex-row items-start gap-4 mb-10">
                        <a href="demo.php"
                            class="btn-graphene-primary btn-graphene-lg"
                            aria-label="<?= t('home.hero.primary_cta_aria') ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <?= t('home.hero.primary_cta') ?>
                        </a>
                        <a href="products.php"
                            class="btn-graphene-secondary btn-graphene-lg"
                            aria-label="<?= t('home.hero.secondary_cta_aria') ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            <?= t('home.hero.secondary_cta') ?>
                        </a>
                    </div>

                    <!-- Graphene Stats Counter - Left aligned -->
                    <div class="graphene-stats" role="list" aria-label="<?= t('home.hero.stats_aria') ?>">
                        <div class="graphene-stats__item" role="listitem">
                            <div class="graphene-stats__icon graphene-stats__icon--shield" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                </svg>
                            </div>
                            <div class="graphene-stats__content">
                                <div class="graphene-stats__value" id="threatsBlocked">847K+</div>
                                <div class="graphene-stats__label"><?= t('home.hero.stats.threats') ?></div>
                            </div>
                        </div>
                        <div class="graphene-stats__divider" aria-hidden="true"></div>
                        <div class="graphene-stats__item" role="listitem">
                            <div class="graphene-stats__icon graphene-stats__icon--uptime" aria-hidden="true">
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
                        <div class="graphene-stats__divider" aria-hidden="true"></div>
                        <div class="graphene-stats__item" role="listitem">
                            <div class="graphene-stats__icon graphene-stats__icon--response" aria-hidden="true">
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

                <!-- Live Feed Widget 2.0 - Enhanced -->
                <div class="lg:col-span-2 mt-8 lg:mt-0">
                    <div class="live-feed-2" id="heroLiveFeed" aria-live="polite" aria-atomic="false">
                        <div class="live-feed-2__badge">
                            <span class="live-feed-2__pulse" aria-hidden="true"></span>
                            <span><?= t('home.hero.live_badge', 'LIVE') ?></span>
                        </div>
                        <div class="live-feed-2__header">
                            <span class="live-feed-2__title"><?= t('home.hero.live_feed_title') ?></span>
                            <span class="live-feed-2__subtitle"><?= t('home.hero.live_feed_subtitle', 'Real-time security events') ?></span>
                        </div>
                        <div class="live-feed-2__items" id="liveFeedItems">
                            <div class="live-feed-2__item live-feed-2__item--critical">
                                <div class="live-feed-2__severity">
                                    <span class="severity-tag severity-tag--critical" role="status" aria-label="Critical severity">
                                        <svg class="severity-tag__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M12 2L2 22h20L12 2zm0 3.83L19.13 20H4.87L12 5.83zM11 10v4h2v-4h-2zm0 6v2h2v-2h-2z" />
                                        </svg>
                                        CRITICAL
                                    </span>
                                </div>
                                <div class="live-feed-2__content">
                                    <div class="live-feed-2__event"><?= t('home.hero.feed.malware') ?></div>
                                    <div class="live-feed-2__meta">
                                        <span class="live-feed-2__status live-feed-2__status--resolved"><?= t('home.hero.feed.status_quarantined', 'Quarantined') ?></span>
                                        <span class="live-feed-2__pattern">Pattern #4821</span>
                                    </div>
                                </div>
                                <span class="live-feed-2__time" aria-label="2 minutes ago">2m</span>
                            </div>
                            <div class="live-feed-2__item live-feed-2__item--warning">
                                <div class="live-feed-2__severity">
                                    <span class="severity-tag severity-tag--warning" role="status" aria-label="Warning severity">
                                        <svg class="severity-tag__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                                        </svg>
                                        WARNING
                                    </span>
                                </div>
                                <div class="live-feed-2__content">
                                    <div class="live-feed-2__event"><?= t('home.hero.feed.login') ?></div>
                                    <div class="live-feed-2__meta">
                                        <span class="live-feed-2__location">
                                            <svg class="w-3 h-3 inline" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                                            </svg>
                                            Copenhagen, DK
                                        </span>
                                        <span class="live-feed-2__ip">203.0.113.42</span>
                                    </div>
                                </div>
                                <span class="live-feed-2__time" aria-label="5 minutes ago">5m</span>
                            </div>
                            <div class="live-feed-2__item live-feed-2__item--info">
                                <div class="live-feed-2__severity">
                                    <span class="severity-tag severity-tag--info" role="status" aria-label="Info severity">
                                        <svg class="severity-tag__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
                                        </svg>
                                        INFO
                                    </span>
                                </div>
                                <div class="live-feed-2__content">
                                    <div class="live-feed-2__event"><?= t('home.hero.feed.scan') ?></div>
                                    <div class="live-feed-2__meta">
                                        <span class="live-feed-2__location">
                                            <svg class="w-3 h-3 inline" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                                            </svg>
                                            Berlin, DE
                                        </span>
                                        <span class="live-feed-2__ports">Ports 22, 443, 3306</span>
                                    </div>
                                </div>
                                <span class="live-feed-2__time" aria-label="12 minutes ago">12m</span>
                            </div>
                        </div>
                        <div class="live-feed-2__footer">
                            <a href="products.php" class="live-feed-2__link">
                                <?= t('home.hero.feed.view_all', 'View all events') ?>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
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

    <section class="py-20 sm:py-24 section-fade-in page-section cta-section-gradient">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4 text-white"><?= t('home.cta.title') ?></h2>
            <p class="text-gray-300 text-base sm:text-lg mb-10 max-w-2xl mx-auto">
                <?= t('home.cta.description') ?>
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4 sm:gap-6">
                <a href="cases.php" class="btn-graphene-secondary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <?= t('home.cta.actions.cases') ?>
                </a>
                <a href="pricing.php" class="btn-graphene-secondary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <?= t('home.cta.actions.pricing') ?>
                </a>
                <a href="contact.php" class="btn-graphene-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <?= t('home.cta.actions.contact') ?>
                </a>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/site-footer.php'; ?>
