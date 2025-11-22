    <footer class="border-t border-gray-800 mt-12 sm:mt-16 lg:mt-20 section-fade-in">
        <div class="container mx-auto px-4 py-8 sm:py-10 lg:py-12">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 text-sm">
                <div class="sm:col-span-2 lg:col-span-1 text-center sm:text-left">
                    <h4 class="font-semibold text-gray-200 mb-3 sm:mb-4">Blackbox EYE&trade;</h4>
                    <p class="text-gray-300">Intelligent Sikkerhed. Klar til Handling.</p>
                </div>
                <div class="text-center sm:text-left">
                    <h4 class="font-semibold text-gray-200 mb-3 sm:mb-4">Globale Kontorer</h4>
                    <div class="space-y-3 sm:space-y-4 text-gray-300">
                        <div>
                            <p class="font-bold text-gray-200">Schweiz – Genève Branch</p>
                            <p class="text-xs sm:text-sm">Rue du Rhône 80<br>1204 Genève, Schweiz</p>
                        </div>
                        <div>
                            <p class="font-bold text-gray-200">UAE – Dubai HQ</p>
                            <p class="text-xs sm:text-sm">Emirates Financial Towers, South Tower<br>Level 27, DIFC, Dubai, UAE</p>
                        </div>
                    </div>
                </div>
                <div class="text-center sm:text-left">
                    <h4 class="font-semibold text-gray-200 mb-3 sm:mb-4">Kontakt</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li class="text-xs sm:text-sm">Email: ops@blackbox.codes</li>
                        <li class="text-xs sm:text-sm">Telefon: +45 31 33 00 33</li>
                    </ul>
                </div>
                <div class="text-center sm:text-left">
                    <h4 class="font-semibold text-gray-200 mb-3 sm:mb-4">Følg Os</h4>
                    <div class="flex justify-center sm:justify-start space-x-4">
                        <a href="#" class="text-gray-300 hover:text-white text-xs sm:text-sm" aria-label="LinkedIn">LinkedIn</a>
                        <a href="#" class="text-gray-300 hover:text-white text-xs sm:text-sm" aria-label="Twitter">Twitter</a>
                    </div>
                </div>
            </div>
            <div class="mt-12 pt-8 border-t border-gray-800 text-center text-gray-500 text-xs">
                <p>&copy; 2025 Blackbox EYE&trade;. Alle rettigheder forbeholdes.</p>
            </div>
        </div>
    </footer>

    <?php if (!empty($show_alphabot)): ?>
        <div id="alphabot-container" class="alphabot-widget" data-component="alphabot" aria-live="polite">
            <button type="button"
                id="alphabot-toggle-btn"
                class="alphabot-toggle"
                aria-expanded="false"
                aria-controls="alphabot-panel">
                <span class="alphabot-status-dot" aria-hidden="true"></span>
                <span>Tal med AlphaBot</span>
            </button>
            <section id="alphabot-panel"
                class="alphabot-panel"
                role="dialog"
                aria-modal="false"
                aria-label="AlphaBot sikkerhedsassistent">
                <div class="alphabot-panel-header">
                    <div>
                        <p class="alphabot-panel-title">GreyEYE AlphaBot</p>
                        <p class="alphabot-panel-subtitle">AI-sikkerhedsrådgiver 24/7</p>
                    </div>
                    <button type="button" id="alphabot-close-btn" class="alphabot-close-btn" aria-label="Luk AlphaBot">&times;</button>
                </div>
                <div id="alphabot-messages" class="alphabot-messages" role="log" aria-live="polite" aria-label="AlphaBot samtale"></div>
                <div class="alphabot-input-group">
                    <label for="alphabot-input" class="sr-only">Skriv dit spørgsmål til AlphaBot</label>
                    <textarea id="alphabot-input"
                        rows="2"
                        placeholder="Stil et spørgsmål om sikkerhed..."
                        aria-describedby="alphabot-hint"></textarea>
                    <button type="button" id="alphabot-send-btn" class="alphabot-send-btn" disabled>
                        <span id="send-text">Send</span>
                        <span id="send-loader" class="hidden ai-spinner" aria-hidden="true"></span>
                    </button>
                </div>
                <p id="alphabot-hint" class="text-xs text-gray-400 mt-2">Enter sender beskeden. Shift + Enter giver linjeskift.</p>
            </section>
        </div>
    <?php endif; ?>

    <!-- Sticky CTA Button -->
    <a href="contact.php"
        id="sticky-cta"
        class="sticky-cta"
        aria-label="Book sikkerhedsmøde">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <span class="hidden sm:inline">Book Møde</span>
        <span class="sm:hidden">Kontakt</span>
    </a>

    <script src="assets/js/site.js"></script>
    </body>

    </html>
