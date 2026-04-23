<?php
require_once __DIR__ . '/includes/i18n.php';
// AI Assistant enabled on landing for cross-browser parity
$disable_alphabot = false;
$show_alphabot = true;
$current_page = 'home';
$page_title = t('home.meta.title');
$meta_description = t('home.meta.description');
include 'includes/site-header.php';
?>

<main id="main-content" class="pt-0">
    <!-- Hero Section: Graphene 3D Network - BlackboxEYE × Blackbox EYE Fusion -->
    <section id="home" class="graphene-hero-3d relative w-full flex items-center">
        <!-- Fallback background for devices without WebGL -->
        <div class="graphene-hero-3d__fallback" aria-hidden="true"></div>

        <!-- 3D Hexagon Network Canvas -->
        <canvas id="graphene-canvas" class="graphene-hero-3d__canvas" aria-hidden="true"></canvas>

        <!-- Gradient Overlays for depth -->
        <div class="graphene-hero-3d__overlay" aria-hidden="true"></div>
        <div class="graphene-hero-3d__vignette" aria-hidden="true"></div>

        <!-- Main Content -->
        <div class="graphene-hero-3d__content relative z-20 px-6 sm:px-8 lg:px-16 py-32 sm:py-40 max-w-7xl mx-auto w-full">
            <div class="graphene-hero-3d__content-inner">
                <!-- Animated Brand Badge -->
                <div class="graphene-floating-badge" role="banner">
                    <div class="graphene-floating-badge__ring" aria-hidden="true"></div>
                    <div class="graphene-floating-badge__inner">
                        <span class="graphene-floating-badge__icon" aria-hidden="true">◈</span>
                        <span class="graphene-floating-badge__text"><?= t('home.hero.badge') ?></span>
                    </div>
                </div>

                <!-- Headline with Typing Effect -->
                <h1 class="graphene-hero-title">
                    <span class="graphene-hero-title__line graphene-hero-title__line--1"><?= t('home.hero.headline_part1', 'Næste generations') ?></span>
                    <span class="graphene-hero-title__line graphene-hero-title__line--2 graphene-text-shimmer"><?= t('home.hero.headline_part2', 'sikkerhedsinfrastruktur') ?></span>
                </h1>

                <!-- Subheadline with fade-in -->
                <p class="graphene-hero-subtitle">
                    <?= t('home.hero.subheadline') ?>
                </p>

                <div class="graphene-hero-tagline">
                    <p class="graphene-hero-tagline__title">
                        <?= t('home.hero.tagline_title') ?>
                        <span class="graphene-hero-tagline__highlight"><?= t('home.hero.tagline_highlight') ?></span>
                    </p>
                    <p class="graphene-hero-tagline__copy">
                        <?= t('home.hero.tagline_copy') ?>
                    </p>
                </div>

                <a href="products.php" class="graphene-btn-spotlight" aria-label="<?= t('home.hero.spotlight_cta_aria') ?>">
                    <span class="graphene-btn-spotlight__glow" aria-hidden="true"></span>
                    <svg class="graphene-btn__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span class="graphene-btn__text"><?= t('home.hero.spotlight_cta') ?></span>
                </a>

                <!-- Premium CTA Buttons with Glow Effects -->
                <div class="graphene-cta-group">
                    <a href="demo.php" class="graphene-btn-primary" aria-label="<?= t('home.hero.primary_cta_aria') ?>">
                        <span class="graphene-btn-primary__bg"></span>
                        <span class="graphene-btn-primary__glow"></span>
                        <svg class="graphene-btn__icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                        </svg>
                        <span class="graphene-btn__text"><?= t('home.hero.primary_cta') ?></span>
                    </a>
                    <a href="free-scan.php" class="graphene-btn-secondary" aria-label="<?= t('home.hero.secondary_cta_aria') ?>">
                        <span class="graphene-btn-secondary__border"></span>
                        <svg class="graphene-btn__icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 16v-4M12 8h.01"></path>
                        </svg>
                        <span class="graphene-btn__text"><?= t('home.hero.secondary_cta') ?></span>
                    </a>
                </div>

                <!-- NOTE: .graphene-cta-bar removed - #sticky-cta is the canonical CTA on landing -->

                <?php
                $hero_usps = [
                    ['key' => 'ai_response', 'icon' => 'shield-bolt'],
                    ['key' => 'intel', 'icon' => 'radar'],
                    ['key' => 'compliance', 'icon' => 'layers']
                ];
                ?>
                <div class="graphene-hero-usps" role="list" aria-label="<?= t('home.hero.badge') ?>">
                    <?php foreach ($hero_usps as $usp): ?>
                        <div class="graphene-hero-usps__item" role="listitem">
                            <div class="graphene-hero-usps__icon" aria-hidden="true">
                                <?php if ($usp['icon'] === 'shield-bolt'): ?>
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                        <path d="M12 7v6l3-1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                <?php elseif ($usp['icon'] === 'radar'): ?>
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 3v3m0 12v3m9-9h-3M6 12H3" stroke-linecap="round" />
                                        <path d="M12 12l4 4" stroke-linecap="round" />
                                    </svg>
                                <?php else: ?>
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                        <rect x="3" y="3" width="13" height="13" rx="2" />
                                        <path d="M8 8h13v13H8z" />
                                    </svg>
                                <?php endif; ?>
                            </div>
                            <div class="graphene-hero-usps__text">
                                <p class="graphene-hero-usps__title"><?= t('home.hero.usps.' . $usp['key'] . '.title') ?></p>
                                <p class="graphene-hero-usps__body"><?= t('home.hero.usps.' . $usp['key'] . '.body') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Stats with Glassmorphism -->
                <div class="graphene-stats-glass" role="list" aria-label="<?= t('home.hero.stats_aria') ?>">
                    <div class="graphene-stat-card" role="listitem">
                        <div class="graphene-stat-card__icon graphene-stat-card__icon--shield">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            </svg>
                        </div>
                        <div class="graphene-stat-card__value" data-count="847000">847K+</div>
                        <div class="graphene-stat-card__label"><?= t('home.hero.stats.threats') ?></div>
                    </div>
                    <div class="graphene-stat-card" role="listitem">
                        <div class="graphene-stat-card__icon graphene-stat-card__icon--uptime">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                        </div>
                        <div class="graphene-stat-card__value">99.9%</div>
                        <div class="graphene-stat-card__label"><?= t('home.hero.stats.uptime') ?></div>
                    </div>
                    <div class="graphene-stat-card" role="listitem">
                        <div class="graphene-stat-card__icon graphene-stat-card__icon--speed">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                            </svg>
                        </div>
                        <div class="graphene-stat-card__value">&lt;50ms</div>
                        <div class="graphene-stat-card__label"><?= t('home.hero.stats.response') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="graphene-scroll-indicator" aria-hidden="true">
            <div class="graphene-scroll-indicator__mouse">
                <div class="graphene-scroll-indicator__wheel"></div>
            </div>
            <span class="graphene-scroll-indicator__text">Scroll</span>
        </div>

        <!-- Bottom Gold Accent -->
        <div class="graphene-hero-3d__accent" aria-hidden="true"></div>
    </section>

    <section class="py-16 sm:py-20 section-fade-in page-section">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <p class="text-[var(--bbx-gold)] uppercase tracking-widest text-sm font-semibold mb-3"><?= t('home.tactical.tagline') ?></p>
                <h2 class="text-3xl sm:text-4xl font-bold mb-4"><?= t('home.tactical.title') ?></h2>
                <p class="text-gray-300 text-base sm:text-lg">
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
                        <span class="text-xs uppercase tracking-widest text-gray-300 mb-3"><?= htmlspecialchars($card['number']) ?></span>
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
                                <span class="text-[var(--bbx-gold)] mr-3 mt-0.5 flex-shrink-0">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </span>
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
