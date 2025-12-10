<?php

/**
 * Terms of Service Page
 * Blackbox EYE™ - Enterprise Terms & Conditions
 */
require_once __DIR__ . '/includes/env.php';
require_once __DIR__ . '/includes/i18n.php';

$current_page = 'terms';
$page_title = t('terms.title', 'Vilkår og betingelser');
$meta_description = t('terms.meta_description', 'Vilkår og betingelser for brug af Blackbox EYE cybersikkerhedsplatformen.');
include __DIR__ . '/includes/site-header.php';
?>

<main class="min-h-screen pt-24 sm:pt-28 lg:pt-32">
  <div class="container mx-auto px-4 py-12 sm:py-16 lg:py-20">

    <!-- Page Header -->
    <div class="max-w-3xl mx-auto text-center mb-12">
      <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4">
        <?= t('terms.title', 'Vilkår og Betingelser') ?>
      </h1>
      <p class="text-gray-400">
        <?= t('terms.last_updated', 'Senest opdateret') ?>: <?= date('d. F Y', strtotime('2025-01-15')) ?>
      </p>
    </div>

    <!-- Terms Content -->
    <article class="max-w-4xl mx-auto prose prose-invert prose-amber">

      <!-- Introduction -->
      <section class="mb-12 p-6 rounded-xl bg-gray-900/50 border border-gray-800">
        <h2 class="text-xl font-bold " style="color: var(--primary-accent); mb-4">1. Introduktion</h2>
        <p class="text-gray-300 leading-relaxed">
          Ved at bruge Blackbox EYE™ platformen, tjenesterne og hjemmesiden accepterer du disse vilkår og betingelser.
          Blackbox EYE™ drives af Blackbox Codes ApS ("vi", "os", "vores"). Hvis du ikke accepterer disse vilkår,
          bedes du undlade at bruge vores tjenester.
        </p>
      </section>

      <!-- Service Description -->
      <section class="mb-12 p-6 rounded-xl bg-gray-900/50 border border-gray-800">
        <h2 class="text-xl font-bold " style="color: var(--primary-accent); mb-4">2. Tjenestebeskrivelse</h2>
        <p class="text-gray-300 leading-relaxed mb-4">
          Blackbox EYE™ er en enterprise cybersikkerhedsplatform, der tilbyder:
        </p>
        <ul class="list-disc list-inside text-gray-300 space-y-2 ml-4">
          <li>Cybertrusselsintelligens og overvågning</li>
          <li>Sikkerhedsrevision og penetrationstest</li>
          <li>Compliance management og GDPR-værktøjer</li>
          <li>Incident response og sikkerhedsrådgivning</li>
          <li>Security awareness træning</li>
        </ul>
      </section>

      <!-- Usage Rights -->
      <section class="mb-12 p-6 rounded-xl bg-gray-900/50 border border-gray-800">
        <h2 class="text-xl font-bold " style="color: var(--primary-accent); mb-4">3. Brugsrettigheder og Begrænsninger</h2>
        <div class="space-y-4 text-gray-300">
          <h3 class="text-lg font-semibold text-white">3.1 Tilladte anvendelser</h3>
          <p>Du må bruge vores tjenester til lovlige forretningsformål relateret til din organisations cybersikkerhed.</p>

          <h3 class="text-lg font-semibold text-white">3.2 Forbudte handlinger</h3>
          <p>Du må ikke:</p>
          <ul class="list-disc list-inside space-y-1 ml-4">
            <li>Bruge vores platform til uautoriseret adgang til tredjepartssystemer</li>
            <li>Dele dine loginoplysninger med uautoriserede parter</li>
            <li>Reverse-engineere eller dekompilere vores software</li>
            <li>Bruge vores data til konkurrencedygtige formål</li>
            <li>Overbelaste eller sabotere vores infrastruktur</li>
          </ul>
        </div>
      </section>

      <!-- Intellectual Property -->
      <section class="mb-12 p-6 rounded-xl bg-gray-900/50 border border-gray-800">
        <h2 class="text-xl font-bold " style="color: var(--primary-accent); mb-4">4. Immaterielle Rettigheder</h2>
        <p class="text-gray-300 leading-relaxed">
          Alle rettigheder til Blackbox EYE™ platformen, inklusiv software, varemærker, algoritmer,
          rapporter og dokumentation tilhører Blackbox Codes ApS. Du erhverver en begrænset,
          ikke-eksklusiv licens til at bruge platformen i henhold til din abonnementsaftale.
        </p>
      </section>

      <!-- Confidentiality -->
      <section class="mb-12 p-6 rounded-xl bg-gray-900/50 border border-gray-800">
        <h2 class="text-xl font-bold " style="color: var(--primary-accent); mb-4">5. Fortrolighed</h2>
        <div class="space-y-4 text-gray-300">
          <h3 class="text-lg font-semibold text-white">5.1 Vores forpligtelser</h3>
          <p>
            Vi behandler alle oplysninger om din organisation og sikkerhedsrapporter som strengt fortrolige.
            Vi deler ikke disse oplysninger med tredjeparter uden din samtykke, medmindre vi er juridisk forpligtet.
          </p>

          <h3 class="text-lg font-semibold text-white">5.2 Dine forpligtelser</h3>
          <p>
            Du accepterer at behandle rapporter, trusselsintelligens og andre materialer fra Blackbox EYE™ som fortrolige
            og ikke dele dem med uautoriserede parter.
          </p>
        </div>
      </section>

      <!-- Liability -->
      <section class="mb-12 p-6 rounded-xl bg-gray-900/50 border border-gray-800">
        <h2 class="text-xl font-bold " style="color: var(--primary-accent); mb-4">6. Ansvar og Garantier</h2>
        <div class="space-y-4 text-gray-300">
          <h3 class="text-lg font-semibold text-white">6.1 Ingen garantier</h3>
          <p>
            Selvom vi bestræber os på at levere præcise og opdaterede sikkerhedsanalyser,
            kan vi ikke garantere, at vores tjenester vil identificere alle sikkerhedstrusler
            eller forhindre alle cyberangreb.
          </p>

          <h3 class="text-lg font-semibold text-white">6.2 Ansvarsbegrænsning</h3>
          <p>
            Vores samlede ansvar er begrænset til det beløb, du har betalt for tjenesten
            i de 12 måneder forud for kravet. Vi er ikke ansvarlige for indirekte tab,
            herunder tabt fortjeneste eller datatab.
          </p>
        </div>
      </section>

      <!-- Payment Terms -->
      <section class="mb-12 p-6 rounded-xl bg-gray-900/50 border border-gray-800">
        <h2 class="text-xl font-bold " style="color: var(--primary-accent); mb-4">7. Betalingsvilkår</h2>
        <p class="text-gray-300 leading-relaxed">
          Abonnementsafgifter faktureres forud. Betalingsbetingelser er 14 dage netto.
          Ved forsinket betaling tilskrives renter i henhold til renteloven.
          Vi forbeholder os retten til at suspendere adgang ved manglende betaling.
        </p>
      </section>

      <!-- Termination -->
      <section class="mb-12 p-6 rounded-xl bg-gray-900/50 border border-gray-800">
        <h2 class="text-xl font-bold " style="color: var(--primary-accent); mb-4">8. Opsigelse</h2>
        <div class="space-y-4 text-gray-300">
          <p>Abonnementer kan opsiges med 30 dages varsel til udgangen af en måned.</p>
          <p>Vi kan opsige din adgang med øjeblikkelig virkning ved:</p>
          <ul class="list-disc list-inside space-y-1 ml-4">
            <li>Væsentlig misligholdelse af disse vilkår</li>
            <li>Manglende betaling trods påmindelse</li>
            <li>Ulovlig anvendelse af platformen</li>
          </ul>
        </div>
      </section>

      <!-- Governing Law -->
      <section class="mb-12 p-6 rounded-xl bg-gray-900/50 border border-gray-800">
        <h2 class="text-xl font-bold " style="color: var(--primary-accent); mb-4">9. Lovvalg og Tvister</h2>
        <p class="text-gray-300 leading-relaxed">
          Disse vilkår er underlagt dansk ret. Enhver tvist, der udspringer af disse vilkår eller
          brugen af vores tjenester, skal afgøres ved Københavns Byret som første instans,
          eller ved voldgift i henhold til Voldgiftsinstituttets regler.
        </p>
      </section>

      <!-- Changes to Terms -->
      <section class="mb-12 p-6 rounded-xl bg-gray-900/50 border border-gray-800">
        <h2 class="text-xl font-bold " style="color: var(--primary-accent); mb-4">10. Ændringer af Vilkår</h2>
        <p class="text-gray-300 leading-relaxed">
          Vi kan opdatere disse vilkår med 30 dages varsel. Fortsat brug af tjenesterne efter
          ændringerne træder i kraft betragtes som accept af de nye vilkår.
          Væsentlige ændringer kommunikeres via e-mail til registrerede brugere.
        </p>
      </section>

      <!-- Contact -->
      <section class="p-6 rounded-xl bg-amber-400/10 border " style="border-color: var(--primary-accent);/30">
        <h2 class="text-xl font-bold " style="color: var(--primary-accent); mb-4">Kontakt</h2>
        <p class="text-gray-300 leading-relaxed mb-4">
          For spørgsmål om disse vilkår, kontakt venligst:
        </p>
        <ul class="text-gray-300 space-y-2">
          <li><strong class="text-white">E-mail:</strong> <a href="mailto:legal@blackbox.codes" class="" style="color: var(--primary-accent); hover:underline">legal@blackbox.codes</a></li>
          <li><strong class="text-white">CVR:</strong> [Indsæt CVR-nummer]</li>
          <li><strong class="text-white">Adresse:</strong> Schweiz / UAE</li>
        </ul>
      </section>

    </article>
  </div>
</main>

<?php include __DIR__ . '/includes/site-footer.php'; ?>
