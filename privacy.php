<?php
require_once __DIR__ . '/includes/env.php';
require_once __DIR__ . '/includes/i18n.php';

$current_page = 'privacy';
$page_title = t('privacy.title', 'Privatlivspolitik') . ' | ' . t('site.name');
$meta_description = t('privacy.meta_description', 'Læs om hvordan Blackbox EYE™ behandler dine persondata og beskytter dit privatliv.');
$meta_robots = 'noindex,follow'; // Privacy pages typically shouldn't be indexed

include 'includes/site-header.php';

$current_lang = bbx_get_language();
?>

<main id="main-content" class="pt-16">
  <section class="py-16 sm:py-20 page-section">
    <div class="container mx-auto px-4">
      <div class="max-w-3xl mx-auto">
        <div class="mb-12">
          <p class="" style="color: var(--primary-accent); uppercase tracking-widest text-sm font-semibold mb-4">Legal</p>
          <h1 class="text-3xl sm:text-4xl font-bold mb-6">
            <?= $current_lang === 'da' ? 'Privatlivspolitik' : 'Privacy Policy' ?>
          </h1>
          <p class="text-gray-400 text-sm">
            <?= $current_lang === 'da' ? 'Senest opdateret: 25. november 2025' : 'Last updated: November 25, 2025' ?>
          </p>
        </div>

        <div class="prose prose-invert prose-amber max-w-none space-y-8">

          <?php if ($current_lang === 'da'): ?>

            <!-- Danish content -->
            <section>
              <h2 class="text-xl font-semibold text-white mb-4">1. Dataansvarlig</h2>
              <p class="text-gray-300 leading-relaxed">
                Blackbox EYE™ (herefter "vi", "os" eller "vores") er dataansvarlig for behandlingen af personoplysninger via dette website.
              </p>
              <p class="text-gray-300 leading-relaxed mt-3">
                <strong class="text-white">Kontakt:</strong><br>
                E-mail: <a href="mailto:privacy@blackbox.codes" class="" style="color: var(--primary-accent); ">privacy@blackbox.codes</a><br>
                Telefon: +45 31 33 00 33
              </p>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-white mb-4">2. Hvilke data vi indsamler</h2>

              <h3 class="text-lg font-medium text-white mb-2">2.1 Kontaktformular</h3>
              <p class="text-gray-300 leading-relaxed">
                Når du udfylder vores kontaktformular, indsamler vi:
              </p>
              <ul class="text-gray-300 list-disc list-inside mt-2 space-y-1">
                <li>Navn</li>
                <li>E-mailadresse</li>
                <li>Telefonnummer (valgfrit)</li>
                <li>Din besked</li>
              </ul>
              <p class="text-gray-300 leading-relaxed mt-3">
                <strong class="text-white">Formål:</strong> At besvare din henvendelse og levere de efterspurgte tjenester.<br>
                <strong class="text-white">Retsgrundlag:</strong> Samtykke (GDPR art. 6(1)(a)) og legitim interesse (GDPR art. 6(1)(f)).<br>
                <strong class="text-white">Opbevaring:</strong> Op til 24 måneder efter sidste kontakt.
              </p>

              <h3 class="text-lg font-medium text-white mb-2 mt-6">2.2 Cookies og tekniske data</h3>
              <p class="text-gray-300 leading-relaxed">
                Vi bruger følgende typer cookies:
              </p>
              <ul class="text-gray-300 list-disc list-inside mt-2 space-y-1">
                <li><strong class="text-white">Nødvendige cookies:</strong> Til grundlæggende sitefunktionalitet (session, tema-præference)</li>
                <li><strong class="text-white">Analysecookies:</strong> Til at forstå hvordan besøgende bruger sitet (kun med samtykke)</li>
              </ul>
              <p class="text-gray-300 leading-relaxed mt-3">
                Du kan til enhver tid ændre dine cookie-præferencer via banneret nederst på siden.
              </p>

              <h3 class="text-lg font-medium text-white mb-2 mt-6">2.3 Sikkerhedsdata</h3>
              <p class="text-gray-300 leading-relaxed">
                Af sikkerhedsmæssige årsager logger vi:
              </p>
              <ul class="text-gray-300 list-disc list-inside mt-2 space-y-1">
                <li>IP-adresse (anonymiseret efter 30 dage)</li>
                <li>Browser-type og version</li>
                <li>Tidspunkt for besøg</li>
                <li>reCAPTCHA-score (til spambeskyttelse)</li>
              </ul>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-white mb-4">3. Deling af data</h2>
              <p class="text-gray-300 leading-relaxed">
                Vi deler ikke dine personoplysninger med tredjeparter, undtagen:
              </p>
              <ul class="text-gray-300 list-disc list-inside mt-2 space-y-1">
                <li>Google reCAPTCHA til spambeskyttelse</li>
                <li>E-mail-udbydere til levering af beskeder</li>
                <li>Myndigheder, hvis lovpligtigt</li>
              </ul>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-white mb-4">4. Dine rettigheder</h2>
              <p class="text-gray-300 leading-relaxed">
                Under GDPR har du ret til:
              </p>
              <ul class="text-gray-300 list-disc list-inside mt-2 space-y-1">
                <li>Indsigt i dine personoplysninger</li>
                <li>Berigtigelse af ukorrekte data</li>
                <li>Sletning ("retten til at blive glemt")</li>
                <li>Begrænsning af behandling</li>
                <li>Dataportabilitet</li>
                <li>Indsigelse mod behandling</li>
              </ul>
              <p class="text-gray-300 leading-relaxed mt-3">
                Kontakt os på <a href="mailto:privacy@blackbox.codes" class="" style="color: var(--primary-accent); ">privacy@blackbox.codes</a> for at udøve dine rettigheder.
              </p>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-white mb-4">5. Sikkerhed</h2>
              <p class="text-gray-300 leading-relaxed">
                Vi anvender branchestandard sikkerhedsforanstaltninger, herunder:
              </p>
              <ul class="text-gray-300 list-disc list-inside mt-2 space-y-1">
                <li>TLS/SSL-kryptering af al kommunikation</li>
                <li>Adgangskontrol og logning</li>
                <li>Regelmæssige sikkerhedsgennemgange</li>
              </ul>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-white mb-4">6. Kontakt og klager</h2>
              <p class="text-gray-300 leading-relaxed">
                Har du spørgsmål eller klager vedrørende vores behandling af persondata, kontakt os på
                <a href="mailto:privacy@blackbox.codes" class="" style="color: var(--primary-accent); ">privacy@blackbox.codes</a>.
              </p>
              <p class="text-gray-300 leading-relaxed mt-3">
                Du har også ret til at klage til Datatilsynet:<br>
                <a href="https://www.datatilsynet.dk" target="_blank" rel="noopener" class="" style="color: var(--primary-accent); ">www.datatilsynet.dk</a>
              </p>
            </section>

          <?php else: ?>

            <!-- English content -->
            <section>
              <h2 class="text-xl font-semibold text-white mb-4">1. Data Controller</h2>
              <p class="text-gray-300 leading-relaxed">
                Blackbox EYE™ (hereinafter "we", "us" or "our") is the data controller for the processing of personal data via this website.
              </p>
              <p class="text-gray-300 leading-relaxed mt-3">
                <strong class="text-white">Contact:</strong><br>
                Email: <a href="mailto:privacy@blackbox.codes" class="" style="color: var(--primary-accent); ">privacy@blackbox.codes</a><br>
                Phone: +45 31 33 00 33
              </p>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-white mb-4">2. Data We Collect</h2>

              <h3 class="text-lg font-medium text-white mb-2">2.1 Contact Form</h3>
              <p class="text-gray-300 leading-relaxed">
                When you fill out our contact form, we collect:
              </p>
              <ul class="text-gray-300 list-disc list-inside mt-2 space-y-1">
                <li>Name</li>
                <li>Email address</li>
                <li>Phone number (optional)</li>
                <li>Your message</li>
              </ul>
              <p class="text-gray-300 leading-relaxed mt-3">
                <strong class="text-white">Purpose:</strong> To respond to your inquiry and deliver requested services.<br>
                <strong class="text-white">Legal basis:</strong> Consent (GDPR Art. 6(1)(a)) and legitimate interest (GDPR Art. 6(1)(f)).<br>
                <strong class="text-white">Retention:</strong> Up to 24 months after last contact.
              </p>

              <h3 class="text-lg font-medium text-white mb-2 mt-6">2.2 Cookies and Technical Data</h3>
              <p class="text-gray-300 leading-relaxed">
                We use the following types of cookies:
              </p>
              <ul class="text-gray-300 list-disc list-inside mt-2 space-y-1">
                <li><strong class="text-white">Essential cookies:</strong> For basic site functionality (session, theme preference)</li>
                <li><strong class="text-white">Analytics cookies:</strong> To understand how visitors use the site (consent required)</li>
              </ul>
              <p class="text-gray-300 leading-relaxed mt-3">
                You can change your cookie preferences at any time via the banner at the bottom of the page.
              </p>

              <h3 class="text-lg font-medium text-white mb-2 mt-6">2.3 Security Data</h3>
              <p class="text-gray-300 leading-relaxed">
                For security purposes, we log:
              </p>
              <ul class="text-gray-300 list-disc list-inside mt-2 space-y-1">
                <li>IP address (anonymized after 30 days)</li>
                <li>Browser type and version</li>
                <li>Time of visit</li>
                <li>reCAPTCHA score (for spam protection)</li>
              </ul>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-white mb-4">3. Data Sharing</h2>
              <p class="text-gray-300 leading-relaxed">
                We do not share your personal data with third parties, except:
              </p>
              <ul class="text-gray-300 list-disc list-inside mt-2 space-y-1">
                <li>Google reCAPTCHA for spam protection</li>
                <li>Email providers for message delivery</li>
                <li>Authorities, if legally required</li>
              </ul>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-white mb-4">4. Your Rights</h2>
              <p class="text-gray-300 leading-relaxed">
                Under GDPR, you have the right to:
              </p>
              <ul class="text-gray-300 list-disc list-inside mt-2 space-y-1">
                <li>Access your personal data</li>
                <li>Rectification of incorrect data</li>
                <li>Erasure ("right to be forgotten")</li>
                <li>Restriction of processing</li>
                <li>Data portability</li>
                <li>Object to processing</li>
              </ul>
              <p class="text-gray-300 leading-relaxed mt-3">
                Contact us at <a href="mailto:privacy@blackbox.codes" class="" style="color: var(--primary-accent); ">privacy@blackbox.codes</a> to exercise your rights.
              </p>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-white mb-4">5. Security</h2>
              <p class="text-gray-300 leading-relaxed">
                We employ industry-standard security measures, including:
              </p>
              <ul class="text-gray-300 list-disc list-inside mt-2 space-y-1">
                <li>TLS/SSL encryption of all communications</li>
                <li>Access control and logging</li>
                <li>Regular security reviews</li>
              </ul>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-white mb-4">6. Contact and Complaints</h2>
              <p class="text-gray-300 leading-relaxed">
                If you have questions or complaints regarding our processing of personal data, contact us at
                <a href="mailto:privacy@blackbox.codes" class="" style="color: var(--primary-accent); ">privacy@blackbox.codes</a>.
              </p>
              <p class="text-gray-300 leading-relaxed mt-3">
                You also have the right to lodge a complaint with the Danish Data Protection Agency:<br>
                <a href="https://www.datatilsynet.dk" target="_blank" rel="noopener" class="" style="color: var(--primary-accent); ">www.datatilsynet.dk</a>
              </p>
            </section>

          <?php endif; ?>

        </div>

        <!-- Back to home -->
        <div class="mt-12 pt-8 border-t border-gray-800">
          <a href="/" class="inline-flex items-center " style="color: var(--primary-accent);  font-medium">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            <?= $current_lang === 'da' ? 'Tilbage til forsiden' : 'Back to home' ?>
          </a>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include 'includes/site-footer.php'; ?>
