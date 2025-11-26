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

$current_lang = bbx_get_language();
?>

<main id="main-content" class="pt-16">
    <section class="py-20 sm:py-24 section-fade-in page-section">
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
                    <form id="contact-form" class="space-y-5" data-endpoint="contact-submit.php" novalidate aria-label="<?= htmlspecialchars(t('contact.form.aria_label', 'Kontaktformular')) ?>">

                        <!-- Honeypot field - hidden from real users, bots will fill it -->
                        <div class="bbx-hp" aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;">
                            <label for="website">Website</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                                <?= t('contact.form.name') ?> <span class="text-red-400" aria-hidden="true">*</span>
                            </label>
                            <input type="text" id="name" name="name" required
                                autocomplete="name"
                                maxlength="100"
                                aria-required="true"
                                aria-describedby="name-error"
                                class="block w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400">
                            <p id="name-error" class="mt-1 text-sm text-red-400 hidden" role="alert"></p>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                                <?= t('contact.form.email') ?> <span class="text-red-400" aria-hidden="true">*</span>
                            </label>
                            <input type="email" id="email" name="email" required
                                autocomplete="email"
                                maxlength="254"
                                aria-required="true"
                                aria-describedby="email-error"
                                class="block w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400">
                            <p id="email-error" class="mt-1 text-sm text-red-400 hidden" role="alert"></p>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-300 mb-2">
                                <?= t('contact.form.phone_optional') ?>
                            </label>
                            <input type="tel" id="phone" name="phone"
                                autocomplete="tel"
                                maxlength="30"
                                aria-required="false"
                                class="block w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-300 mb-2">
                                <?= t('contact.form.message') ?> <span class="text-red-400" aria-hidden="true">*</span>
                            </label>
                            <textarea id="message" name="message" rows="5" required
                                maxlength="5000"
                                aria-required="true"
                                aria-describedby="message-error message-hint"
                                class="block w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400"></textarea>
                            <p id="message-hint" class="mt-1 text-xs text-gray-500">
                                <?= $current_lang === 'da' ? 'Maks. 5000 tegn' : 'Max. 5000 characters' ?>
                            </p>
                            <p id="message-error" class="mt-1 text-sm text-red-400 hidden" role="alert"></p>
                        </div>

                        <!-- Privacy consent -->
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="privacy-consent" name="privacy_consent" required
                                aria-required="true"
                                aria-describedby="privacy-error"
                                class="mt-1 accent-amber-400 w-4 h-4">
                            <label for="privacy-consent" class="text-sm text-gray-300">
                                <?= $current_lang === 'da'
                                    ? 'Jeg accepterer <a href="privacy.php" class="text-amber-400 hover:text-amber-300 underline" target="_blank">privatlivspolitikken</a> og at mine oplysninger behandles.'
                                    : 'I accept the <a href="privacy.php" class="text-amber-400 hover:text-amber-300 underline" target="_blank">privacy policy</a> and consent to my data being processed.' ?>
                                <span class="text-red-400" aria-hidden="true">*</span>
                            </label>
                        </div>
                        <p id="privacy-error" class="text-sm text-red-400 hidden" role="alert"></p>

                        <input type="hidden" name="recaptcha_token" id="recaptcha_token" value="">
                        <input type="hidden" name="form_timestamp" value="<?= time() ?>">

                        <button type="submit"
                            aria-label="<?= htmlspecialchars(t('contact.form.submit')) ?>"
                            class="w-full bg-amber-400 text-black font-semibold py-3 rounded-lg hover:bg-amber-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
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
                        <p class="text-gray-400 text-sm mb-4">
                            <strong class="text-gray-200"><?= t('contact.details.offices.switzerland') ?></strong><br>
                            <?= t('contact.details.offices.switzerland_address') ?>
                        </p>
                        <div class="border-t border-gray-700/50 pt-4">
                            <p class="text-gray-400 text-sm">
                                <strong class="text-gray-200"><?= t('contact.details.offices.uae') ?></strong><br>
                                <span class="text-amber-400/80 text-xs" dir="rtl"><?= t('footer.offices.uae_company_ar') ?></span><br>
                                <span class="text-gray-500 text-xs">(<?= t('footer.offices.uae_company_en') ?>)</span><br>
                                <?= t('contact.details.offices.uae_address') ?><br>
                                <span class="text-gray-300"><?= t('footer.offices.uae_phone') ?></span>
                            </p>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-amber-400 mb-2"><?= t('contact.details.secure.title') ?></h2>
                        <p class="text-gray-300 text-sm mb-3"><?= t('contact.details.secure.body') ?></p>

                        <!-- PGP Key Info -->
                        <div class="mt-4 p-4 bg-gray-800/40 rounded-lg border border-gray-700">
                            <p class="text-xs text-gray-400 mb-2">
                                <strong class="text-gray-300">PGP <?= $current_lang === 'da' ? 'Fingeraftryk' : 'Fingerprint' ?>:</strong>
                            </p>
                            <code class="text-xs text-amber-400 break-all font-mono">
                                B4C2 3A91 5E7D 8F12 A6D0 9C4B E7F3 2D86 1A5C 0E9B
                            </code>
                            <div class="mt-3">
                                <a href="/pgp-key.asc"
                                    class="inline-flex items-center text-xs text-amber-400 hover:text-amber-300"
                                    download="blackbox-eye-pgp.asc">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    <?= $current_lang === 'da' ? 'Download PGP-nøgle' : 'Download PGP key' ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 bg-gray-900/40 section-fade-in page-section page-section--soft">
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
