<?php
$page_title = 'Priser | Blackbox EYE™';
$current_page = 'pricing';
include 'includes/site-header.php';
?>

<main class="pt-16">
    <section class="py-16 sm:py-20 section-fade-in">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <p class="text-amber-400 uppercase tracking-widest text-sm font-semibold mb-4"><?= t('pricing.subtitle') ?></p>
                <h1 class="text-3xl sm:text-5xl font-bold mb-6"><?= t('pricing.intro') ?></h1>
                <p class="text-gray-300 text-base sm:text-lg">
                    <?= t('pricing.description') ?>
                </p>
            </div>

            <div class="max-w-4xl mx-auto glass-effect rounded-3xl p-6 sm:p-8 lg:p-10 mb-16">
                <h2 class="text-xl sm:text-2xl font-bold text-center mb-6"><?= t('pricing.ai_advisor.title') ?></h2>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-6">
                    <div>
                        <label for="industry-select" class="block text-sm font-medium text-gray-300 mb-2"><?= t('pricing.ai_advisor.industry_label') ?></label>
                        <select id="industry-select" class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-3 py-2.5 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400">
                            <option>Produktionsvirksomhed</option>
                            <option>Offentlig institution</option>
                            <option>Advokatfirma</option>
                            <option>Ejendomsselskab</option>
                            <option>Detailhandel</option>
                            <option>IT &amp; Teknologi</option>
                        </select>
                    </div>
                    <div>
                        <label for="employee-count" class="block text-sm font-medium text-gray-300 mb-2"><?= t('pricing.ai_advisor.employees_label') ?></label>
                        <input type="number" id="employee-count" value="50" min="1" class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-3 py-2.5 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div class="lg:self-end">
                        <button id="get-recommendation-btn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-lg transition-colors">
                            <?= t('pricing.ai_advisor.button') ?>
                        </button>
                    </div>
                </div>
                <div id="recommendation-result-container" class="mt-6 hidden" style="min-height:150px;">
                    <div class="border-t border-gray-700 pt-6">
                        <div id="recommendation-loader" class="flex flex-col items-center justify-center text-center">
                            <div class="spinner"></div>
                            <p class="mt-4 text-gray-300"><?= t('pricing.ai_advisor.analyzing') ?></p>
                        </div>
                        <div id="recommendation-result" class="hidden prose prose-invert max-w-none text-gray-200 text-sm sm:text-base" aria-live="polite"></div>
                    </div>
                </div>
            </div>

            <div class="pricing-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 items-stretch mb-12">
                <!-- MVP-Basis -->
                <article class="glass-effect rounded-2xl p-6 lg:p-8 text-center h-full flex flex-col">
                    <div class="mb-3">
                        <span class="text-xs uppercase tracking-wider text-amber-400 font-semibold">MVP Starter</span>
                    </div>
                    <h2 class="text-xl lg:text-2xl font-bold mb-2">MVP-Basis</h2>
                    <div class="text-3xl font-bold text-amber-400 mb-2">1.799 DKK</div>
                    <p class="text-gray-400 text-sm mb-6">pr. måned + moms</p>
                    <p class="text-gray-400 mb-6 text-sm lg:text-base">Grundlæggende beskyttelse til små teams og startups.</p>
                    <ul class="text-left space-y-2 lg:space-y-3 text-gray-300 my-6 lg:my-8 flex-grow text-sm lg:text-base">
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>GreyEYE AI-assistent (begrænsede features)</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Basis ID-Matrix adgang</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Email support (hverdage)</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Op til 10 brugere</span>
                        </li>
                    </ul>
                    <div class="text-xs text-gray-500 mb-4">Månedligt abonnement • Ingen binding</div>
                    <a href="contact.php" class="mt-auto w-full inline-block border border-amber-400 text-amber-400 py-3 rounded-lg hover:bg-amber-400 hover:text-black transition-all font-semibold text-sm lg:text-base">Kom i gang</a>
                </article>

                <!-- MVP-Pro -->
                <article class="glass-effect rounded-2xl p-6 lg:p-8 text-center h-full flex flex-col">
                    <div class="mb-3">
                        <span class="text-xs uppercase tracking-wider text-blue-400 font-semibold">MVP Vækst</span>
                    </div>
                    <h2 class="text-xl lg:text-2xl font-bold mb-2">MVP-Pro</h2>
                    <div class="text-3xl font-bold text-amber-400 mb-2">3.499 DKK</div>
                    <p class="text-gray-400 text-sm mb-6">pr. måned + moms</p>
                    <p class="text-gray-400 mb-6 text-sm lg:text-base">Udvidet MVP-løsning til voksende virksomheder.</p>
                    <ul class="text-left space-y-2 lg:space-y-3 text-gray-300 my-6 lg:my-8 flex-grow text-sm lg:text-base">
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Alt i MVP-Basis +</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>GreyEYE AI-assistent (fulde features)</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Standard ID-Matrix adgangskontrol</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Op til 25 brugere</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Chat support (hverdage 9-17)</span>
                        </li>
                    </ul>
                    <div class="text-xs text-gray-500 mb-4">Månedligt abonnement • 3 måneders opsigelse</div>
                    <a href="contact.php" class="mt-auto w-full inline-block border border-amber-400 text-amber-400 py-3 rounded-lg hover:bg-amber-400 hover:text-black transition-all font-semibold text-sm lg:text-base">Kom i gang</a>
                </article>

                <!-- MVP-Premium -->
                <article class="glass-effect rounded-2xl p-6 lg:p-8 text-center h-full flex flex-col">
                    <div class="mb-3">
                        <span class="text-xs uppercase tracking-wider text-purple-400 font-semibold">MVP Elite</span>
                    </div>
                    <h2 class="text-xl lg:text-2xl font-bold mb-2">MVP-Premium</h2>
                    <div class="text-3xl font-bold text-amber-400 mb-2">5.999 DKK</div>
                    <p class="text-gray-400 text-sm mb-6">pr. måned + moms</p>
                    <p class="text-gray-400 mb-6 text-sm lg:text-base">Komplet MVP-pakke med prioriteret support.</p>
                    <ul class="text-left space-y-2 lg:space-y-3 text-gray-300 my-6 lg:my-8 flex-grow text-sm lg:text-base">
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Alt i MVP-Pro +</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Basis PVE modul</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Onboarding workshop (1 session)</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Op til 50 brugere</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Prioriteret support</span>
                        </li>
                    </ul>
                    <div class="text-xs text-gray-500 mb-4">Månedligt abonnement • 6 måneders opsigelse</div>
                    <a href="contact.php" class="mt-auto w-full inline-block border border-amber-400 text-amber-400 py-3 rounded-lg hover:bg-amber-400 hover:text-black transition-all font-semibold text-sm lg:text-base">Kom i gang</a>
                </article>
            </div>

            <div class="text-center mb-12">
                <h3 class="text-2xl font-bold mb-4"><?= t('pricing.enterprise.section_title') ?></h3>
                <p class="text-gray-400 max-w-2xl mx-auto"><?= t('pricing.enterprise.section_description') ?></p>
            </div>

            <div class="pricing-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 items-stretch">
                <article class="glass-effect rounded-2xl p-6 lg:p-8 text-center h-full flex flex-col order-2 lg:order-1">
                    <h2 class="text-xl lg:text-2xl font-bold mb-2">Standard</h2>
                    <div class="text-3xl font-bold text-amber-400 mb-2">9.900 DKK</div>
                    <p class="text-gray-400 text-sm mb-6">pr. måned + moms</p>
                    <p class="text-gray-400 mb-6 text-sm lg:text-base">Effektiv sikkerhedsbeskyttelse og digitale løsninger til daglig brug.</p>
                    <ul class="text-left space-y-2 lg:space-y-3 text-gray-300 my-6 lg:my-8 flex-grow text-sm lg:text-base">
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>GreyEYE AI-assistent (fuld adgang)</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>ID-Matrix adgangskontrol</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Basis rapportering og analytics</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Op til 100 brugere</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Email & chat support</span>
                        </li>
                    </ul>
                    <div class="text-xs text-gray-500 mb-4">Årligt abonnement • Kvartalsvis fakturering</div>
                    <a href="contact.php" class="mt-auto w-full inline-block border border-amber-400 text-amber-400 py-3 rounded-lg hover:bg-amber-400 hover:text-black transition-all font-semibold text-sm lg:text-base">Kontakt os</a>
                </article>
                <article class="relative glass-effect rounded-2xl p-6 lg:p-8 text-center border-2 border-amber-400 h-full flex flex-col order-1 lg:order-2 transform lg:scale-105 z-10">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-400 text-black px-4 py-1 rounded-full text-xs lg:text-sm font-bold whitespace-nowrap">Mest populære</div>
                    <h2 class="text-xl lg:text-2xl font-bold mb-2 mt-2">Premium</h2>
                    <div class="text-3xl font-bold text-amber-400 mb-2">18.900 DKK</div>
                    <p class="text-gray-400 text-sm mb-6">pr. måned + moms</p>
                    <p class="text-gray-400 mb-6 text-sm lg:text-base">Udvidet adgang til avancerede analyseværktøjer og specialmoduler.</p>
                    <ul class="text-left space-y-2 lg:space-y-3 text-gray-300 my-6 lg:my-8 flex-grow text-sm lg:text-base">
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Alt i Standard +</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>PVE modul (fuld version)</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>AUT træningsmodul</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Avanceret rapportering</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Op til 250 brugere</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Prioriteret support 24/5</span>
                        </li>
                    </ul>
                    <div class="text-xs text-gray-500 mb-4">Årligt abonnement • Månedlig fakturering</div>
                    <a href="contact.php" class="mt-auto w-full inline-block bg-amber-400 text-black py-3 rounded-lg hover:bg-amber-500 transition-all font-semibold text-sm lg:text-base">Kontakt os</a>
                </article>
                <article class="glass-effect rounded-2xl p-6 lg:p-8 text-center h-full flex flex-col order-3">
                    <h2 class="text-xl lg:text-2xl font-bold mb-2">Enterprise</h2>
                    <div class="text-3xl font-bold text-amber-400 mb-2">39.000 DKK</div>
                    <p class="text-gray-400 text-sm mb-6">pr. måned + moms*</p>
                    <p class="text-gray-400 mb-6 text-sm lg:text-base">Helhedsorienteret, skræddersyet beskyttelse for større organisationer.</p>
                    <ul class="text-left space-y-2 lg:space-y-3 text-gray-300 my-6 lg:my-8 flex-grow text-sm lg:text-base">
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Alt i Premium +</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Adgang til specialteams</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>24/7 operationel beredskab</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Dedikeret account manager</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>Ubegrænset brugere</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span>VIP support & SLA garanti</span>
                        </li>
                    </ul>
                    <div class="text-xs text-gray-500 mb-4">*Eller efter aftale • Årligt abonnement • Fleksibel fakturering</div>
                    <a href="contact.php" class="mt-auto w-full inline-block border border-amber-400 text-amber-400 py-3 rounded-lg hover:bg-amber-400 hover:text-black transition-all font-semibold text-sm lg:text-base">Kontakt os</a>
                </article>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 bg-gray-900/40 section-fade-in">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4"><?= t('pricing.custom.title') ?></h2>
            <p class="text-gray-400 max-w-2xl mx-auto mb-8"><?= t('pricing.custom.description') ?></p>
            <a href="contact.php" class="inline-flex items-center justify-center bg-amber-400 text-black font-semibold py-3 px-8 rounded-lg hover:bg-amber-500 transition-colors">
                <?= t('pricing.custom.button') ?>
            </a>
        </div>
    </section>
</main>

<?php include 'includes/site-footer.php'; ?>
