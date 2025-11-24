<?php
require_once 'includes/env.php';
require_once __DIR__ . '/includes/i18n.php';
$current_page = 'contact';
$page_title = t('contact.title') . ' | ' . t('site.name');
$meta_description = t('contact.hero_section.description');
$meta_keywords = 'Blackbox EYE contact, security consultant, cyber defense support, intelligence';
$meta_og_image = BBX_SITE_BASE_URL . '/assets/logo.png';
$structured_data = [
    '@type' => 'ProfessionalService',
    'serviceType' => 'Cybersecurity Operations & Intelligence',
    'areaServed' => 'GLOBAL',
    'availableLanguage' => ['da', 'en']
];
include 'includes/site-header.php';
?>

<main id="main-content" class="pt-16">
    <section class="py-20 sm:py-24 section-fade-in">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <p class="text-amber-400 uppercase tracking-widest text-sm font-semibold mb-4"><?= t('contact.hero_section.tagline') ?></p>
                <h1 class="text-3xl sm:text-5xl font-bold mb-6"><?= t('contact.hero_section.title') ?></h1>
                <p class="text-gray-300 text-base sm:text-lg">
                    <?= t('contact.hero_section.description') ?>
                </p>
            </div>

            <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-10">
                <div class="glass-effect rounded-2xl p-8 lg:col-span-2">
                    <form id="contact-form" class="space-y-5" data-endpoint="contact-submit.php" novalidate aria-label="<?= htmlspecialchars(t('contact.form.submit')) ?>">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-300 mb-2"><?= t('contact.form.name') ?></label>
                            <input type="text" id="name" name="name" required 
                                   autocomplete="name"
                                   aria-required="true"
                                   class="block w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-300 mb-2"><?= t('contact.form.email') ?></label>
                            <input type="email" id="email" name="email" required 
                                   autocomplete="email"
                                   aria-required="true"
                                   class="block w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-300 mb-2"><?= t('contact.form.phone_optional') ?></label>
                            <input type="tel" id="phone" name="phone" 
                                   autocomplete="tel"
                                   aria-required="false"
                                   class="block w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-300 mb-2"><?= t('contact.form.message') ?></label>
                            <textarea id="message" name="message" rows="5" required 
                                      aria-required="true"
                                      class="block w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400"></textarea>
                        </div>
                        <input type="hidden" name="recaptcha_token" id="recaptcha_token" value="">
                        <button type="submit" 
                                aria-label="<?= htmlspecialchars(t('contact.form.submit')) ?>"
                                class="w-full bg-amber-400 text-black font-semibold py-3 rounded-lg hover:bg-amber-500 transition-colors" 
                                data-sending-text="<?= htmlspecialchars(t('contact.form.sending')) ?>">
                            <?= t('contact.form.submit') ?>
                        </button>
                    </form>
                    <div id="contact-form-error"
                        class="hidden mt-4 text-center text-red-400 border border-red-500/60 rounded-md p-4 text-sm"
                        role="alert"
                        aria-live="polite"
                        aria-atomic="true">
                    </div>
                    <div id="contact-form-success"
                        class="hidden mt-6 text-center text-green-400 border border-green-400 rounded-md p-4 text-sm"
                        role="status"
                        aria-live="polite"
                        aria-atomic="true">
                        <?= t('contact.form.success') ?>
                    </div>
                </div>
                <aside class="glass-effect rounded-2xl p-8 space-y-8">
                    <div>
                        <h2 class="text-xl font-semibold text-amber-400 mb-2"><?= t('contact.details.direct.title') ?></h2>
                        <p class="text-gray-300 text-sm"><?= t('contact.details.direct.email') ?></p>
                        <p class="text-gray-300 text-sm"><?= t('contact.details.direct.phone') ?></p>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-amber-400 mb-2"><?= t('contact.details.offices.title') ?></h2>
                        <p class="text-gray-400 text-sm mb-3">
                            <strong class="text-gray-200"><?= t('contact.details.offices.switzerland') ?></strong><br>
                            <?= t('contact.details.offices.switzerland_address') ?>
                        </p>
                        <p class="text-gray-400 text-sm">
                            <strong class="text-gray-200"><?= t('contact.details.offices.uae') ?></strong><br>
                            <?= t('contact.details.offices.uae_address') ?>
                        </p>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-amber-400 mb-2"><?= t('contact.details.secure.title') ?></h2>
                        <p class="text-gray-300 text-sm"><?= t('contact.details.secure.body') ?></p>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 bg-gray-900/40 section-fade-in">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4"><?= t('contact.cta.title') ?></h2>
            <p class="text-gray-400 max-w-2xl mx-auto mb-8"><?= t('contact.cta.description') ?></p>
            <a href="mailto:ops@blackbox.codes" class="inline-flex items-center justify-center border border-amber-400 text-amber-400 font-semibold py-3 px-8 rounded-lg hover:bg-amber-400 hover:text-black transition-colors">
                <?= t('contact.cta.button') ?>
            </a>
        </div>
    </section>
</main>

<?php include 'includes/site-footer.php'; ?>
