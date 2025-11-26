<?php
require_once __DIR__ . '/includes/i18n.php';
$current_page = 'about';
$page_title = t('about.hero_section.title');
$meta_description = t('about.hero_section.description');

// SEO meta tags
$og_image = 'https://blackbox.codes/assets/images/og-about.jpg';
$og_url = 'https://blackbox.codes/about.php';

include 'includes/site-header.php';
?>

<!-- Structured Data for Organization -->
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Blackbox EYE™",
        "description": "<?= htmlspecialchars($meta_description) ?>",
        "url": "https://blackbox.codes",
        "logo": "https://blackbox.codes/assets/images/logo.png",
        "foundingDate": "2020",
        "numberOfEmployees": {
            "@type": "QuantitativeValue",
            "value": "15-50"
        },
        "areaServed": {
            "@type": "Place",
            "name": "Global"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+45-XXXXXXXX",
            "contactType": "Sales",
            "availableLanguage": ["Danish", "English"]
        }
    }
</script>

<main class="pt-16">
    <!-- Hero Section - Immersive with animated background -->
    <section class="relative min-h-[70vh] flex items-center justify-center overflow-hidden" role="banner">
        <!-- Animated gradient background -->
        <div class="absolute inset-0 bg-gradient-to-br from-gray-900 via-black to-gray-900" aria-hidden="true">
            <div class="absolute inset-0 opacity-30">
                <div class="absolute top-0 left-1/4 w-96 h-96 bg-amber-500/20 rounded-full blur-3xl animate-pulse motion-reduce:animate-none"></div>
                <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl animate-pulse motion-reduce:animate-none" style="animation-delay: 1s;"></div>
            </div>
        </div>

        <div class="container mx-auto px-4 relative z-10">
            <!-- Breadcrumb -->
            <nav aria-label="Breadcrumb" class="mb-8 max-w-4xl mx-auto">
                <ol class="flex items-center gap-2 text-sm text-gray-400">
                    <li><a href="index.php" class="hover:text-amber-400 transition-colors">Hjem</a></li>
                    <li aria-hidden="true">/</li>
                    <li aria-current="page" class="text-amber-400">Om os</li>
                </ol>
            </nav>

            <div class="max-w-4xl mx-auto text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-400/10 border border-amber-400/30 mb-6 backdrop-blur-sm">
                    <div class="w-2 h-2 rounded-full bg-amber-400 animate-pulse motion-reduce:animate-none" aria-hidden="true"></div>
                    <span class="text-xs uppercase tracking-widest text-amber-400 font-bold"><?= t('about.hero_section.tagline') ?></span>
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black mb-6 leading-tight">
                    <span class="bg-gradient-to-r from-white via-amber-200 to-amber-400 bg-clip-text text-transparent">
                        <?= t('about.hero_section.title') ?>
                    </span>
                </h1>
                <p class="text-lg sm:text-xl text-gray-300 mb-12 leading-relaxed max-w-3xl mx-auto">
                    <?= t('about.hero_section.description') ?>
                </p>

                <!-- Quick stats with hover effects -->
                <div class="grid grid-cols-3 gap-4 sm:gap-6 max-w-2xl mx-auto">
                    <div class="text-center group cursor-default">
                        <div class="text-3xl sm:text-4xl font-black text-amber-400 mb-2 transition-transform group-hover:scale-110">24/7</div>
                        <div class="text-xs sm:text-sm text-gray-400 uppercase tracking-wide">Operations</div>
                    </div>
                    <div class="text-center group cursor-default">
                        <div class="text-3xl sm:text-4xl font-black text-amber-400 mb-2 transition-transform group-hover:scale-110">3</div>
                        <div class="text-xs sm:text-sm text-gray-400 uppercase tracking-wide">Kontinenter</div>
                    </div>
                    <div class="text-center group cursor-default">
                        <div class="text-3xl sm:text-4xl font-black text-amber-400 mb-2 transition-transform group-hover:scale-110">∞</div>
                        <div class="text-xs sm:text-sm text-gray-400 uppercase tracking-wide">Dedikation</div>
                    </div>
                </div>

                <!-- Trust indicators -->
                <div class="mt-12 pt-8 border-t border-gray-800 flex flex-wrap items-center justify-center gap-6 text-sm text-gray-400">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span>ISO 27001 Ready</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>GDPR Compliant</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>5+ Years Experience</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll indicator -->
        <a href="#vision-mission" class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce motion-reduce:animate-none focus:outline-none focus:ring-2 focus:ring-amber-400 rounded-full p-2" aria-label="Scroll to content">
            <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </a>
    </section>

    <!-- Vision & Mission - Side-by-side cards with icons -->
    <section id="vision-mission" class="py-20 sm:py-28 bg-gradient-to-b from-black to-gray-900">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-6xl mx-auto">
                <article class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-500/10 to-transparent border border-amber-500/20 p-10 hover:border-amber-500/40 transition-all duration-500 focus-within:ring-2 focus-within:ring-amber-400">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/5 rounded-full blur-3xl group-hover:bg-amber-500/10 transition-all duration-500 motion-reduce:transition-none" aria-hidden="true"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 rounded-2xl bg-amber-500/20 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-bold text-amber-400 mb-4"><?= t('about.pillars.vision_title') ?></h2>
                        <p class="text-gray-300 leading-relaxed"><?= t('about.pillars.vision_body') ?></p>
                    </div>
                </div>

                <article class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-500/10 to-transparent border border-blue-500/20 p-10 hover:border-blue-500/40 transition-all duration-500 focus-within:ring-2 focus-within:ring-blue-400">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full blur-3xl group-hover:bg-blue-500/10 transition-all duration-500 motion-reduce:transition-none" aria-hidden="true"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 rounded-2xl bg-blue-500/20 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-bold text-amber-400 mb-4"><?= t('about.pillars.mission_title') ?></h2>
                        <p class="text-gray-300 leading-relaxed"><?= t('about.pillars.mission_body') ?></p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- Core Values - Interactive grid -->
    <section class="py-20 sm:py-28 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-gray-900 via-black to-gray-900"></div>
        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-5xl font-black mb-4"><?= t('about.values_grid.title') ?></h2>
                <div class="w-24 h-1 bg-gradient-to-r from-transparent via-amber-400 to-transparent mx-auto"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto">
                <?php
                $values_data = [
                    ['key' => 'discretion', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>'],
                    ['key' => 'innovation', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>'],
                    ['key' => 'integrity', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>'],
                    ['key' => 'perfection', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>']
                ];

                foreach ($values_data as $value): ?>
                    <div class="group relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-amber-500/20 to-transparent rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-300 opacity-0 group-hover:opacity-100"></div>
                        <div class="relative bg-gray-900/60 backdrop-blur-sm border border-gray-800 rounded-2xl p-8 hover:border-amber-500/50 transition-all duration-300">
                            <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center mb-4 group-hover:bg-amber-500/20 transition-colors duration-300">
                                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <?= $value['icon'] ?>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-3"><?= t('about.values_grid.items.' . $value['key'] . '.title') ?></h3>
                            <p class="text-gray-400 text-sm leading-relaxed"><?= t('about.values_grid.items.' . $value['key'] . '.body') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Specialized Teams - Timeline style -->
    <section class="py-20 sm:py-28 bg-gradient-to-b from-gray-900 to-black relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, rgba(255,199,0,0.15) 1px, transparent 1px); background-size: 40px 40px;"></div>
        </div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-16 max-w-3xl mx-auto">
                <h2 class="text-3xl sm:text-5xl font-black mb-6"><?= t('about.teams.title') ?></h2>
                <p class="text-lg text-gray-300 leading-relaxed">
                    <?= t('about.teams.description') ?>
                </p>
            </div>

            <!-- Timeline connector -->
            <div class="max-w-5xl mx-auto relative">
                <div class="absolute left-8 top-12 bottom-12 w-0.5 bg-gradient-to-b from-amber-400/20 via-amber-400/40 to-amber-400/20 hidden lg:block" aria-hidden="true"></div>
                
                <div class="space-y-6">
                <?php
                $team_blocks = [
                    ['key' => 'recon', 'colorClass' => 'blue', 'bgClass' => 'bg-blue-500/20', 'hoverBgClass' => 'group-hover:bg-blue-500/30', 'textClass' => 'text-blue-400', 'borderClass' => 'hover:border-blue-500/40', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>'],
                    ['key' => 'red', 'colorClass' => 'red', 'bgClass' => 'bg-red-500/20', 'hoverBgClass' => 'group-hover:bg-red-500/30', 'textClass' => 'text-red-400', 'borderClass' => 'hover:border-red-500/40', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path>'],
                    ['key' => 'stealth', 'colorClass' => 'purple', 'bgClass' => 'bg-purple-500/20', 'hoverBgClass' => 'group-hover:bg-purple-500/30', 'textClass' => 'text-purple-400', 'borderClass' => 'hover:border-purple-500/40', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>'],
                    ['key' => 'blue', 'colorClass' => 'cyan', 'bgClass' => 'bg-cyan-500/20', 'hoverBgClass' => 'group-hover:bg-cyan-500/30', 'textClass' => 'text-cyan-400', 'borderClass' => 'hover:border-cyan-500/40', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>']
                ];

                foreach ($team_blocks as $index => $unit):
                    $isEven = $index % 2 === 0;
                ?>
                    <article class="group relative flex flex-col sm:flex-row items-start sm:items-center gap-6 <?= $isEven ? 'sm:flex-row' : 'sm:flex-row-reverse' ?>">
                        <div class="w-16 h-16 rounded-2xl <?= $unit['bgClass'] ?> flex items-center justify-center flex-shrink-0 group-hover:scale-110 <?= $unit['hoverBgClass'] ?> transition-all duration-300 motion-reduce:transform-none relative z-10">
                            <svg class="w-8 h-8 <?= $unit['textClass'] ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <?= $unit['icon'] ?>
                            </svg>
                        </div>
                        <div class="flex-1 bg-gray-900/80 backdrop-blur-sm border border-gray-800 rounded-2xl p-6 <?= $unit['borderClass'] ?> transition-all duration-300 motion-reduce:transition-none text-left">
                            <h3 class="text-xl font-bold <?= $unit['textClass'] ?> mb-2"><?= t('about.teams.units.' . $unit['key'] . '.title') ?></h3>
                            <p class="text-gray-300 leading-relaxed"><?= t('about.teams.units.' . $unit['key'] . '.body') ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
                </div>
            </div>

            <!-- Custom Solutions - Full width card -->
            <div class="max-w-5xl mx-auto mt-12">
                <article class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-amber-500/20 via-purple-500/20 to-blue-500/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-50 motion-reduce:transition-none" aria-hidden="true"></div>
                    <div class="relative bg-gray-900/90 backdrop-blur-sm border border-amber-500/30 rounded-3xl p-8 sm:p-10 text-center focus-within:ring-2 focus-within:ring-amber-400">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500/20 to-purple-500/20 flex items-center justify-center mx-auto mb-6" aria-hidden="true">
                            <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-amber-400 mb-4"><?= t('about.teams.units.custom.title') ?></h3>
                        <p class="text-gray-300 leading-relaxed max-w-3xl mx-auto"><?= t('about.teams.units.custom.body') ?></p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- CTA Section - Bold and action-oriented -->
    <section class="py-20 sm:py-28 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 via-transparent to-blue-500/10" aria-hidden="true"></div>
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-400/10 border border-amber-400/30 mb-8 backdrop-blur-sm">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <span class="text-xs uppercase tracking-widest text-amber-400 font-bold">Klar til handling</span>
                </div>
                <h2 class="text-4xl sm:text-5xl font-black mb-6 leading-tight"><?= t('about.cta.title') ?></h2>
                <p class="text-lg text-gray-300 mb-6 max-w-2xl mx-auto leading-relaxed">
                    <?= t('about.cta.description') ?>
                </p>
                
                <!-- Social proof -->
                <div class="flex items-center justify-center gap-2 mb-10 text-sm text-gray-400">
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                    </svg>
                    <span>Tillidsvurderet af 500+ virksomheder globalt</span>
                </div>
                
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="contact.php" class="group inline-flex items-center gap-3 bg-gradient-to-r from-amber-400 to-amber-500 text-black font-bold py-4 px-10 rounded-xl hover:from-amber-500 hover:to-amber-600 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-amber-500/50 focus:outline-none focus:ring-4 focus:ring-amber-400/50 motion-reduce:hover:scale-100" aria-label="Kontakt os for at komme i gang">
                        <?= t('about.cta.button') ?>
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform motion-reduce:transform-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </a>
                    <a href="demo.php" class="inline-flex items-center gap-3 border-2 border-gray-700 text-white font-semibold py-4 px-10 rounded-xl hover:border-amber-400 hover:text-amber-400 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-gray-600" aria-label="Book en demo af vores produkter">
                        Book en demo
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/site-footer.php'; ?>
