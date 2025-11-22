<?php
require_once 'includes/env.php';
$page_title = 'Kontakt | Blackbox EYE™';
$current_page = 'contact';
$meta_description = 'Kontakt Blackbox EYE™ for fortrolig dialog om avancerede sikkerheds- og efterretningsløsninger. Vores specialister står klar til at hjælpe dig 24/7.';
$meta_keywords = 'Blackbox EYE kontakt, sikkerhedskonsulent, cyber defense support, efterretning';
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
                <p class="text-amber-400 uppercase tracking-widest text-sm font-semibold mb-4">Fortrolig dialog</p>
                <h1 class="text-3xl sm:text-5xl font-bold mb-6">Kontakt os – når kun det ypperste er acceptabelt</h1>
                <p class="text-gray-300 text-base sm:text-lg">
                    Hos Blackbox EYE™ er kompromisløs fortrolighed og uigennemtrængelig beskyttelse fundamentet. Intet overlades til tilfældighederne.
                </p>
            </div>

            <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-10">
                <div class="glass-effect rounded-2xl p-8 lg:col-span-2">
                    <form id="contact-form" class="space-y-5" data-endpoint="contact-submit.php" novalidate>
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Navn</label>
                            <input type="text" id="name" name="name" required class="block w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                            <input type="email" id="email" name="email" required class="block w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-300 mb-2">Telefon (valgfri)</label>
                            <input type="tel" id="phone" name="phone" class="block w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400" autocomplete="tel">
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-300 mb-2">Besked</label>
                            <textarea id="message" name="message" rows="5" required class="block w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400"></textarea>
                        </div>
                        <input type="hidden" name="recaptcha_token" id="recaptcha_token" value="">
                        <button type="submit" class="w-full bg-amber-400 text-black font-semibold py-3 rounded-lg hover:bg-amber-500 transition-colors">
                            Send forespørgsel
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
                        Tak for din henvendelse! Vi vender tilbage hurtigst muligt.
                    </div>
                </div>
                <aside class="glass-effect rounded-2xl p-8 space-y-8">
                    <div>
                        <h2 class="text-xl font-semibold text-amber-400 mb-2">Direkte kontakt</h2>
                        <p class="text-gray-300 text-sm">ops@blackbox.codes</p>
                        <p class="text-gray-300 text-sm">+45 31 33 00 33</p>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-amber-400 mb-2">Globale kontorer</h2>
                        <p class="text-gray-400 text-sm mb-3">
                            <strong class="text-gray-200">Schweiz – Genève Branch</strong><br>
                            Rue du Rhône 80<br>
                            1204 Genève, Schweiz
                        </p>
                        <p class="text-gray-400 text-sm">
                            <strong class="text-gray-200">UAE – Dubai HQ</strong><br>
                            Emirates Financial Towers, South Tower<br>
                            Level 27, DIFC, Dubai, UAE
                        </p>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-amber-400 mb-2">Sikker kommunikation</h2>
                        <p class="text-gray-300 text-sm">PGP-nøgler og sikre kanaler udleveres efter NDA.</p>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 bg-gray-900/40 section-fade-in">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4">Foretrækker du et direkte strategimøde?</h2>
            <p class="text-gray-400 max-w-2xl mx-auto mb-8">Book et fortroligt strategi- eller krisemøde, fysisk eller virtuelt. Vi arbejder fleksibelt efter jeres sikkerhedsprotokoller.</p>
            <a href="mailto:ops@blackbox.codes" class="inline-flex items-center justify-center border border-amber-400 text-amber-400 font-semibold py-3 px-8 rounded-lg hover:bg-amber-400 hover:text-black transition-colors">
                Anmod om NDA &amp; secure line
            </a>
        </div>
    </section>
</main>

<?php include 'includes/site-footer.php'; ?>
