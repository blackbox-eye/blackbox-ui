    <footer class="bg-gradient-to-b from-gray-900/50 to-black border-t border-gray-800/50 mt-24 sm:mt-28 lg:mt-32 section-fade-in">
        <div class="container mx-auto px-4 py-16 sm:py-20 lg:py-24">
            <!-- Main Footer Content -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-12 mb-12">
                <!-- Brand Section -->
                <div class="sm:col-span-2 lg:col-span-1 text-center sm:text-left">
                    <h4 class="text-xl font-bold text-white mb-4"><?= t('site.name') ?></h4>
                    <p class="text-gray-400 text-sm leading-relaxed mb-6"><?= t('footer.tagline') ?></p>
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-400/10 border border-amber-400/20">
                        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                        <span class="text-xs text-amber-400 font-semibold"><?= t('footer.operational') ?></span>
                    </div>
                </div>

                <!-- Global Offices -->
                <div class="text-center sm:text-left">
                    <h4 class="text-sm font-bold uppercase tracking-wider text-amber-400 mb-4"><?= t('footer.offices.title') ?></h4>
                    <div class="space-y-5 text-gray-300">
                        <div>
                            <p class="font-semibold text-white mb-1"><?= t('footer.offices.switzerland') ?></p>
                            <p class="text-xs leading-relaxed text-gray-400"><?= t('footer.offices.switzerland_address') ?></p>
                        </div>
                        <div>
                            <p class="font-semibold text-white mb-1"><?= t('footer.offices.uae') ?></p>
                            <p class="text-xs leading-relaxed text-gray-400"><?= t('footer.offices.uae_address') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="text-center sm:text-left">
                    <h4 class="text-sm font-bold uppercase tracking-wider text-amber-400 mb-4"><?= t('footer.contact.title') ?></h4>
                    <ul class="space-y-3 text-gray-300">
                        <li class="flex items-center justify-center sm:justify-start gap-2">
                            <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <a href="mailto:ops@blackbox.codes" class="text-sm hover:text-amber-400 transition-colors">ops@blackbox.codes</a>
                        </li>
                        <li class="flex items-center justify-center sm:justify-start gap-2">
                            <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <a href="tel:+4531330033" class="text-sm hover:text-amber-400 transition-colors">+45 31 33 00 33</a>
                        </li>
                    </ul>
                </div>

                <!-- Social Links -->
                <div class="text-center sm:text-left">
                    <h4 class="text-sm font-bold uppercase tracking-wider text-amber-400 mb-4"><?= t('footer.social.title') ?></h4>
                    <div class="flex justify-center sm:justify-start gap-4">
                        <a href="#" class="group flex items-center justify-center w-12 h-12 rounded-lg bg-gray-800 hover:bg-amber-400 hover:scale-110 transition-all duration-300" aria-label="LinkedIn">
                            <svg class="w-6 h-6 text-gray-400 group-hover:text-black transition-colors" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                            </svg>
                        </a>
                        <a href="#" class="group flex items-center justify-center w-12 h-12 rounded-lg bg-gray-800 hover:bg-amber-400 hover:scale-110 transition-all duration-300" aria-label="Twitter">
                            <svg class="w-6 h-6 text-gray-400 group-hover:text-black transition-colors" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="pt-8 border-t border-gray-800/50">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500">
                    <p><?= t('footer.copyright') ?></p>
                    <p class="text-gray-600">This site is protected by reCAPTCHA (invisible)</p>
                </div>
            </div>
        </div>
    </footer>

    <?php if (!empty($show_alphabot)): ?>
        <div id="alphabot-overlay" class="alphabot-overlay" aria-hidden="true"></div>
    <?php endif; ?>

    <div class="bbx-command-rail<?= empty($show_alphabot) ? ' bbx-command-rail--cta-only' : '' ?>">
        <?php if (!empty($show_alphabot)): ?>
            <div id="alphabot-container" class="alphabot-widget" data-component="alphabot" aria-live="polite">
                <button type="button"
                    id="alphabot-toggle-btn"
                    class="alphabot-toggle"
                    aria-expanded="false"
                    aria-controls="alphabot-panel"
                    aria-label="Åbn AlphaBot sikkerhedsassistent">
                    <span class="alphabot-status-dot" aria-hidden="true"></span>
                    <span class="alphabot-label"><?= t('alphabot.title') ?></span>
                </button>
                <section id="alphabot-panel"
                    class="alphabot-panel"
                    role="dialog"
                    aria-modal="false"
                    aria-label="<?= htmlspecialchars(t('alphabot.subtitle')) ?>">
                    <div class="alphabot-panel-header">
                        <div>
                            <p class="alphabot-panel-title"><?= t('alphabot.title') ?></p>
                            <p class="alphabot-panel-subtitle"><?= t('alphabot.panel_tagline') ?></p>
                        </div>
                        <button type="button" id="alphabot-close-btn" class="alphabot-close-btn" aria-label="<?= htmlspecialchars(t('common.close')) ?>">&times;</button>
                    </div>
                    <div id="alphabot-messages" class="alphabot-messages" role="log" aria-live="polite" aria-label="<?= htmlspecialchars(t('alphabot.subtitle')) ?>"></div>
                    <div class="alphabot-input-group">
                        <label for="alphabot-input" class="sr-only"><?= t('alphabot.subtitle') ?></label>
                        <textarea id="alphabot-input"
                            rows="2"
                            placeholder="<?= htmlspecialchars(t('alphabot.placeholder')) ?>"
                            aria-describedby="alphabot-hint"></textarea>
                        <button type="button" id="alphabot-send-btn" class="alphabot-send-btn" disabled>
                            <span id="send-text"><?= t('alphabot.send') ?></span>
                            <span id="send-loader" class="hidden ai-spinner" aria-hidden="true"></span>
                        </button>
                    </div>
                    <p id="alphabot-hint" class="text-xs text-gray-400 mt-2"><?= t('alphabot.hint') ?></p>
                </section>
            </div>
        <?php endif; ?>

        <!-- Sticky CTA Button -->
        <a href="contact.php"
            id="sticky-cta"
            class="sticky-cta"
            aria-label="<?= htmlspecialchars(t('footer.cta.aria_label')) ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span class="hidden sm:inline"><?= t('footer.cta.book_meeting') ?></span>
            <span class="sm:hidden"><?= t('footer.cta.contact') ?></span>
        </a>
    </div>

    <script src="assets/js/site.min.js" defer></script>
    </body>

    </html>
