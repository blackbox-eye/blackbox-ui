<?php
$page_title = 'Produkter | Blackbox EYE™';
$current_page = 'products';
include 'includes/site-header.php';
?>

<main class="pt-24">
    <section class="py-20 sm:py-24 section-fade-in">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <p class="text-amber-400 uppercase tracking-widest text-sm font-semibold mb-4">Platformen</p>
                <h1 class="text-3xl sm:text-5xl font-bold mb-6">En holistisk sikkerhedsstrategi på ét sted</h1>
                <p class="text-gray-300 text-base sm:text-lg">
                    Hvert modul i Blackbox EYE™ er en specialiseret forsvarslinje. Brug vores AI-assistent til at generere konkrete trussels-scenarier og se, hvordan vi neutraliserer dem.
                </p>
            </div>

            <article class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center section-fade-in mb-16">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-amber-400 mb-4">🛡️ Penetration &amp; Vulnerability Engine (PVE)</h2>
                    <p class="text-gray-300 mb-6">PVE er jeres proaktive, offensive skjold. Ved konstant at simulere avancerede cyberangreb identificerer vores AI sårbarheder i jeres systemer, netværk og applikationer – før ondsindede aktører gør det.</p>
                    <button class="gemini-trigger-btn bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors" data-module="PVE">
                        ✨ Generer trusselsscenarie
                    </button>
                </div>
                <div class="glass-effect rounded-2xl p-6 sm:p-8 h-full">
                    <h3 class="font-semibold text-lg mb-4">Nøglefunktioner</h3>
                    <ul class="list-disc list-inside text-gray-300 space-y-2">
                        <li>Automatiseret, kontinuerlig penetrationstest</li>
                        <li>AI-baseret risikovurdering og prioritering</li>
                        <li>Detaljerede, handlingsanvisende rapporter</li>
                        <li>Adaptiv trusselssimulering mod nyeste angreb</li>
                    </ul>
                </div>
            </article>

            <article class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center section-fade-in">
                <div class="lg:order-last">
                    <h2 class="text-2xl sm:text-3xl font-bold text-amber-400 mb-4">👁️ GreyEYE</h2>
                    <p class="text-gray-300 mb-6">GreyEYE fungerer som jeres digitale vagtpost 24/7. Modulet overvåger al aktivitet i jeres infrastruktur og opdager straks, når noget afviger fra normalen.</p>
                    <button class="gemini-trigger-btn bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors" data-module="GreyEYE">
                        ✨ Generer trusselsscenarie
                    </button>
                </div>
                <div class="glass-effect rounded-2xl p-6 sm:p-8 h-full lg:order-first">
                    <h3 class="font-semibold text-lg mb-4">Nøglefunktioner</h3>
                    <ul class="list-disc list-inside text-gray-300 space-y-2">
                        <li>24/7 realtidsovervågning af logs og events</li>
                        <li>AI-baseret anomali- og trusselsdetektion</li>
                        <li>Autonom respons på detekterede trusler</li>
                        <li>Global trusselsintelligens og korrelation</li>
                    </ul>
                </div>
            </article>
        </div>
    </section>

    <section class="py-16 sm:py-20 bg-gray-900/30 section-fade-in">
        <div class="container mx-auto px-4">
            <div class="glass-effect rounded-3xl p-8 sm:p-12">
                <h2 class="text-3xl sm:text-4xl font-bold mb-6 text-center">Ekstra moduler &amp; integrationer</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-xl font-semibold text-amber-400 mb-3">ID-Matrix</h3>
                        <p class="text-gray-300">Granulær adgangskontrol, live session-monitorering og biometriske sikkerhedslag.</p>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-amber-400 mb-3">AUT – Awareness &amp; Training</h3>
                        <p class="text-gray-300">Scenariebaseret awareness-træning drevet af AI og adaptiv læring.</p>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-amber-400 mb-3">Operational Bridge</h3>
                        <p class="text-gray-300">Integrer vores platform med jeres eksisterende SOC, SIEM og workflows.</p>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-amber-400 mb-3">Live Command Center</h3>
                        <p class="text-gray-300">Real-time situationsrum, mission control dashboards og automatiserede playbooks.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-24 section-fade-in">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4">Klar til at se modulerne i aktion?</h2>
            <p class="text-gray-400 max-w-2xl mx-auto mb-8">
                Book en fortrolig demonstration, så viser vi hvordan Blackbox EYE™ indsættes i jeres miljø uden friktion.
            </p>
            <a href="contact.php" class="inline-flex items-center justify-center bg-amber-400 text-black font-semibold py-3 px-8 rounded-lg hover:bg-amber-500 transition-colors">
                Kontakt vores produktteam
            </a>
        </div>
    </section>
</main>

<div id="gemini-modal" role="dialog" aria-modal="true" aria-labelledby="gemini-modal-title" class="hidden fixed inset-0 bg-black bg-opacity-70 z-50 flex items-center justify-center p-4">
    <div class="glass-effect rounded-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
        <div class="flex justify-between items-center p-4 border-b border-gray-700">
            <h3 id="gemini-modal-title" class="text-lg sm:text-xl font-bold text-amber-400">AI-genereret trusselsscenarie</h3>
            <button id="close-modal-btn" class="text-gray-400 hover:text-white text-3xl leading-none" aria-label="Luk">&times;</button>
        </div>
        <div id="modal-content" class="p-6 overflow-y-auto flex-1">
            <div id="modal-loader" class="flex flex-col items-center justify-center text-center">
                <div class="spinner"></div>
                <p class="mt-4 text-gray-300">Kontakter Gemini... Genererer relevant scenarie...</p>
            </div>
            <div id="modal-result" class="hidden prose prose-invert max-w-none text-gray-300 text-sm sm:text-base" aria-live="polite"></div>
        </div>
    </div>
</div>

<?php include 'includes/site-footer.php'; ?>
