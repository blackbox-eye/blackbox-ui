<?php
$page_title = 'Kundecases | Blackbox EYE™';
$current_page = 'cases';
include 'includes/site-header.php';
?>

<main class="pt-16">
    <section class="py-20 sm:py-24 section-fade-in">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <p class="text-amber-400 uppercase tracking-widest text-sm font-semibold mb-4">Dokumenteret effekt</p>
                <h1 class="text-3xl sm:text-5xl font-bold mb-6">Bevist værdi i den virkelige verden</h1>
                <p class="text-gray-300 text-base sm:text-lg">
                    Se hvordan vi har neutraliseret kritiske trusler for kunder med høje krav – og lad vores AI analysere din egen situation.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <article class="glass-effect rounded-2xl p-8 flex flex-col">
                    <h2 class="text-xl font-bold mb-2">Kommune med kritisk infrastruktur</h2>
                    <p class="text-gray-400 mb-4"><strong>Udfordring:</strong> Uautoriseret adgang til tekniske installationer.</p>
                    <p class="text-gray-300 mb-6"><strong>Løsning:</strong> ID-Matrix og GreyEYE implementeret til at logge al adgang og levere ugentlige rapporter.</p>
                    <div class="mt-auto pt-4 border-t border-gray-700">
                        <p class="text-amber-400 font-bold text-lg">Resultat: 80% reduktion i adgangsbrud.</p>
                    </div>
                </article>
                <article class="glass-effect rounded-2xl p-8 flex flex-col">
                    <h2 class="text-xl font-bold mb-2">Ejendomsselskab</h2>
                    <p class="text-gray-400 mb-4"><strong>Udfordring:</strong> Manglende overblik over 12 bygninger og tyveri.</p>
                    <p class="text-gray-300 mb-6"><strong>Løsning:</strong> Platformen integreret med eksisterende kameraer, med mobil adgang for viceværter.</p>
                    <div class="mt-auto pt-4 border-t border-gray-700">
                        <p class="text-amber-400 font-bold text-lg">Resultat: 65% færre tyverier på 3 måneder.</p>
                    </div>
                </article>
                <article class="glass-effect rounded-2xl p-8 flex flex-col">
                    <h2 class="text-xl font-bold mb-2">Vagtselskab</h2>
                    <p class="text-gray-400 mb-4"><strong>Udfordring:</strong> Manuelle logbøger og langsom reaktionstid.</p>
                    <p class="text-gray-300 mb-6"><strong>Løsning:</strong> GreyEYE assisterer nu vagter med automatisk dokumentation og live-opdateringer.</p>
                    <div class="mt-auto pt-4 border-t border-gray-700">
                        <p class="text-amber-400 font-bold text-lg">Resultat: Ny differentieringsfaktor.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-24 bg-gray-900/30 section-fade-in">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto glass-effect rounded-3xl p-6 sm:p-8 lg:p-10">
                <h2 class="text-2xl sm:text-3xl font-bold text-center mb-6">✨ Analysér din egen situation</h2>
                <p class="text-center text-gray-300 mb-8">
                    Beskriv kort en sikkerhedsudfordring i din organisation, så genererer vores AI-konsulent en målrettet anbefaling baseret på tidligere cases.
                </p>
                <div class="space-y-4">
                    <label for="case-input" class="block text-sm font-medium text-gray-300 mb-2">Din udfordring</label>
                    <textarea id="case-input" rows="4" class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400" placeholder="F.eks. 'Vi er bekymrede for phishing-mails til vores bogholderi...'"></textarea>
                    <button id="analyze-case-btn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                        Få AI-analyse
                    </button>
                </div>
                <div id="case-analysis-container" class="mt-6 hidden" style="min-height:150px;">
                    <div class="border-t border-gray-700 pt-6">
                        <div id="case-analysis-loader" class="flex flex-col items-center justify-center text-center">
                            <div class="spinner"></div>
                            <p class="mt-4 text-gray-300">Analyserer din situation...</p>
                        </div>
                        <div id="case-analysis-result" class="hidden prose prose-invert max-w-none text-gray-200 text-sm sm:text-base" aria-live="polite"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 section-fade-in">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4">Skal vi skabe din næste case?</h2>
            <p class="text-gray-400 max-w-2xl mx-auto mb-8">
                Kontakt os for en fortrolig dialog og få en operativ plan, der matcher jeres risikoprofil.
            </p>
            <a href="contact.php" class="inline-flex items-center justify-center bg-amber-400 text-black font-semibold py-3 px-8 rounded-lg hover:bg-amber-500 transition-colors">
                Book et fortroligt brief
            </a>
        </div>
    </section>
</main>

<?php include 'includes/site-footer.php'; ?>
