<?php
/**
 * Demo Booking Page - Blackbox EYE
 * Sprint 5: UX & Lead-Flow Improvements
 * 
 * This page provides a demo booking interface using Calendly integration
 * 
 * @version 1.0
 * @date 2025-11-23
 */

require_once __DIR__ . '/includes/i18n.php';
$current_page = 'demo';
$page_title = 'Book en Demo | ' . t('site.name');
$meta_description = 'Book en gratis demo af Blackbox EYE™ cybersikkerhedsplatform. Se hvordan vores AI-drevne løsninger kan beskytte din virksomhed.';
$meta_keywords = 'demo, book møde, cybersikkerhed, Blackbox EYE, sikkerhedsdemo';

$structured_data = [
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => 'Book en Demo',
    'description' => $meta_description,
];

include 'includes/site-header.php';
?>

<main id="main-content" class="pt-16">
    <!-- Hero Section -->
    <section class="py-16 sm:py-20 section-fade-in">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center mb-12">
                <p class="text-amber-400 uppercase tracking-widest text-sm font-semibold mb-4">
                    <?= t('demo.hero.tagline', 'GRATIS DEMONSTRATION') ?>
                </p>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold mb-6 hero-gradient-text">
                    <?= t('demo.hero.title', 'Se Blackbox EYE™ i aktion') ?>
                </h1>
                <p class="text-lg sm:text-xl text-gray-300 leading-relaxed max-w-3xl mx-auto">
                    <?= t('demo.hero.description', 'Book en personlig demo og oplev hvordan vores AI-drevne sikkerhedsplatform kan transformere din cybersikkerhed. Få svar på dine spørgsmål fra vores eksperter.') ?>
                </p>
            </div>

            <!-- Benefits Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
                <div class="glass-effect rounded-xl p-6 text-center">
                    <div class="w-12 h-12 bg-amber-400/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2 text-white">
                        <?= t('demo.benefits.duration.title', '30-45 minutter') ?>
                    </h3>
                    <p class="text-gray-400 text-sm">
                        <?= t('demo.benefits.duration.description', 'Perfekt til at få et overblik over platformen') ?>
                    </p>
                </div>

                <div class="glass-effect rounded-xl p-6 text-center">
                    <div class="w-12 h-12 bg-amber-400/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2 text-white">
                        <?= t('demo.benefits.customized.title', 'Skræddersyet til dig') ?>
                    </h3>
                    <p class="text-gray-400 text-sm">
                        <?= t('demo.benefits.customized.description', 'Fokuseret på din branche og dine behov') ?>
                    </p>
                </div>

                <div class="glass-effect rounded-xl p-6 text-center">
                    <div class="w-12 h-12 bg-amber-400/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2 text-white">
                        <?= t('demo.benefits.noobligation.title', 'Ingen forpligtelser') ?>
                    </h3>
                    <p class="text-gray-400 text-sm">
                        <?= t('demo.benefits.noobligation.description', '100% gratis og uforpligtende') ?>
                    </p>
                </div>
            </div>

            <!-- Calendly Embed Section -->
            <div class="max-w-5xl mx-auto">
                <div class="glass-effect rounded-2xl p-6 sm:p-8">
                    <h2 class="text-2xl font-bold mb-6 text-center">
                        <?= t('demo.booking.title', 'Vælg et tidspunkt der passer dig') ?>
                    </h2>
                    
                    <!-- Calendly inline widget begin -->
                    <div class="calendly-inline-widget" 
                         data-url="<?= htmlspecialchars(BBX_CALENDLY_URL) ?>" 
                         style="min-width:320px;height:700px;">
                    </div>
                    <!-- Calendly inline widget end -->
                    
                    <p class="text-center text-gray-400 text-sm mt-6">
                        <?= t('demo.booking.note', 'Kan du ikke finde et passende tidspunkt? Kontakt os direkte på') ?> 
                        <a href="mailto:ops@blackbox.codes" class="text-amber-400 hover:text-amber-300">ops@blackbox.codes</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- What to Expect Section -->
    <section class="py-16 sm:py-20 bg-gray-900/40 section-fade-in">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl sm:text-4xl font-bold mb-8 text-center">
                    <?= t('demo.expect.title', 'Hvad kan du forvente?') ?>
                </h2>
                
                <div class="space-y-6">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-400 text-black flex items-center justify-center font-bold">
                            1
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold mb-2 text-white">
                                <?= t('demo.expect.step1.title', 'Introduktion til platformen') ?>
                            </h3>
                            <p class="text-gray-300">
                                <?= t('demo.expect.step1.description', 'Vi viser dig hvordan GreyEYE AI-assistenten, PVE-modulet og ID-Matrix arbejder sammen for at beskytte din organisation 24/7.') ?>
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-400 text-black flex items-center justify-center font-bold">
                            2
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold mb-2 text-white">
                                <?= t('demo.expect.step2.title', 'Live demonstration') ?>
                            </h3>
                            <p class="text-gray-300">
                                <?= t('demo.expect.step2.description', 'Se et live eksempel på hvordan platformen identificerer trusler, udfører automatiske pentests og genererer compliance-rapporter.') ?>
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-400 text-black flex items-center justify-center font-bold">
                            3
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold mb-2 text-white">
                                <?= t('demo.expect.step3.title', 'Q&A og næste skridt') ?>
                            </h3>
                            <p class="text-gray-300">
                                <?= t('demo.expect.step3.description', 'Få svar på alle dine spørgsmål og diskutér den rette pakke og implementation for din organisation.') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 sm:py-20 section-fade-in">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4">
                <?= t('demo.cta.title', 'Har du spørgsmål før demoen?') ?>
            </h2>
            <p class="text-gray-400 max-w-2xl mx-auto mb-8">
                <?= t('demo.cta.description', 'Vores team er klar til at hjælpe dig med at forstå hvordan Blackbox EYE kan styrke din sikkerhed.') ?>
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="contact.php" class="inline-flex items-center justify-center bg-amber-400 text-black font-semibold py-3 px-8 rounded-lg hover:bg-amber-500 transition-colors">
                    <?= t('demo.cta.contact', 'Kontakt os') ?>
                </a>
                <a href="pricing.php" class="inline-flex items-center justify-center border border-amber-400 text-amber-400 font-semibold py-3 px-8 rounded-lg hover:bg-amber-400 hover:text-black transition-colors">
                    <?= t('demo.cta.pricing', 'Se priser') ?>
                </a>
            </div>
        </div>
    </section>
</main>

<!-- Calendly badge widget begin -->
<link href="https://assets.calendly.com/assets/external/widget.css" rel="stylesheet">
<script src="https://assets.calendly.com/assets/external/widget.js" type="text/javascript" async></script>
<!-- Calendly badge widget end -->

<?php include 'includes/site-footer.php'; ?>
