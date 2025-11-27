<?php
require_once __DIR__ . '/includes/i18n.php';
$current_page = 'home';
$page_title = t('home.meta.title');
$meta_description = t('home.meta.description');
include 'includes/site-header.php';
?>

<main id="main-content" class="pt-0">
    <!-- Hero Section: Graphene Premium Design - Gold/Grey Fusion -->
    <section id="home" class="graphene-hero-premium relative min-h-screen w-full flex items-center overflow-hidden page-section">
        <!-- Hexagon Pattern Background -->
        <div class="graphene-hero-premium__bg" aria-hidden="true">
            <div class="graphene-hexagon-pattern"></div>
            <div class="graphene-gradient-overlay"></div>
        </div>

        <div class="graphene-hero-premium__content relative px-6 sm:px-8 lg:px-12 py-32 sm:py-40 max-w-7xl mx-auto w-full">
            <!-- Hero Text Content - Centered on mobile, left on desktop -->
            <div class="max-w-4xl">
                <!-- Graphene Brand Badge -->
                <div class="graphene-premium-badge" role="banner">
                    <span class="graphene-premium-badge__glow" aria-hidden="true"></span>
                    <span class="graphene-premium-badge__text"><?= t('home.hero.badge') ?></span>
                </div>

                <h1 class="graphene-headline text-4xl sm:text-5xl md:text-6xl lg:text-7xl xl:text-8xl font-black leading-tight tracking-tight mb-8">
                    <?= t('home.hero.headline') ?>
                </h1>

                <p class="graphene-subheadline text-lg sm:text-xl md:text-2xl mb-12 leading-relaxed max-w-3xl">
                    <?= t('home.hero.subheadline') ?>
                </p>

                <!-- Premium CTA Buttons - Large and prominent -->
                <div class="flex flex-col sm:flex-row items-start gap-5 mb-16">
                    <a href="demo.php"
                        class="graphene-cta-primary"
                        aria-label="<?= t('home.hero.primary_cta_aria') ?>">
                        <span class="graphene-cta-primary__glow" aria-hidden="true"></span>
                        <svg class="graphene-cta-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span><?= t('home.hero.primary_cta') ?></span>
                    </a>
                    <a href="products.php"
                        class="graphene-cta-secondary"
                        aria-label="<?= t('home.hero.secondary_cta_aria') ?>">
                        <svg class="graphene-cta-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        <span><?= t('home.hero.secondary_cta') ?></span>
                    </a>
                </div>

                <!-- Graphene Stats - Premium styling -->
                <div class="graphene-premium-stats" role="list" aria-label="<?= t('home.hero.stats_aria') ?>">
                    <div class="graphene-premium-stats__item" role="listitem">
                        <div class="graphene-premium-stats__icon" aria-hidden="true">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            </svg>
                        </div>
                        <div class="graphene-premium-stats__content">
                            <div class="graphene-premium-stats__value">847K+</div>
                            <div class="graphene-premium-stats__label"><?= t('home.hero.stats.threats') ?></div>
                        </div>
                    </div>
                    <div class="graphene-premium-stats__divider" aria-hidden="true"></div>
                    <div class="graphene-premium-stats__item" role="listitem">
                        <div class="graphene-premium-stats__icon" aria-hidden="true">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                        </div>
                        <div class="graphene-premium-stats__content">
                            <div class="graphene-premium-stats__value">99.9%</div>
                            <div class="graphene-premium-stats__label"><?= t('home.hero.stats.uptime') ?></div>
                        </div>
                    </div>
                    <div class="graphene-premium-stats__divider" aria-hidden="true"></div>
                    <div class="graphene-premium-stats__item" role="listitem">
                        <div class="graphene-premium-stats__icon" aria-hidden="true">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                            </svg>
                        </div>
                        <div class="graphene-premium-stats__content">
                            <div class="graphene-premium-stats__value">&lt;50ms</div>
                            <div class="graphene-premium-stats__label"><?= t('home.hero.stats.response') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Decorative gold accent line -->
        <div class="graphene-hero-premium__accent" aria-hidden="true"></div>
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
