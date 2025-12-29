<?php
// Determine if this is the landing page (page-home)
$is_landing_page = (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'index.php');

// P0 Kill-switch flags (set by includes/debug-killswitch.php)
global $_BBX_DISABLE_CTA, $_BBX_DISABLE_CHAT;

// ═══════════════════════════════════════════════════════════════════════════
// P0 iOS SCROLL FIX: Sticky CTA bar PERMANENTLY DISABLED
// These fixed-positioned bottom elements capture first touch/wheel events
// on iOS WebKit and prevent scroll passthrough. CSS failsafe also in critical.css.
// Original code preserved below as comments for reference.
// ═══════════════════════════════════════════════════════════════════════════
?>
    <?php /* P0 DISABLED: Sticky CTA Bar for Mobile/Tablet
    <?php if (!$is_landing_page && empty($_BBX_DISABLE_CTA)): ?>
    <div id="sticky-cta-bar" class="sticky-cta-bar" role="navigation" aria-label="<?= t('header.mobile.quick_actions', 'Quick actions') ?>">
        <a href="demo.php" class="sticky-cta-bar__btn sticky-cta-bar__btn--primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <polygon points="5 3 19 12 5 21 5 3"></polygon>
            </svg>
            <?= t('home.hero.primary_cta', 'Book Demo') ?>
        </a>
        <a href="free-scan.php" class="sticky-cta-bar__btn sticky-cta-bar__btn--secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 16v-4M12 8h.01"></path>
            </svg>
            <?= t('home.hero.secondary_cta', 'Free Scan') ?>
        </a>
    </div>
    <?php endif; ?>
    END P0 DISABLED */ ?>

    <footer class="bg-gradient-to-b from-gray-900/50 to-black border-t border-gray-800/50 mt-24 sm:mt-28 lg:mt-32 section-fade-in">
        <div class="container mx-auto px-4 py-16 sm:py-20 lg:py-24">
            <!-- Main Footer Content -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-12 mb-12">
                <!-- Brand Section -->
                <div class="sm:col-span-2 lg:col-span-1 text-center sm:text-left">
                    <h4 class="text-xl font-bold text-white mb-4"><?= t('site.name') ?></h4>
                    <p class="text-gray-400 text-sm leading-relaxed mb-6"><?= t('footer.tagline') ?></p>
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg " style="background: rgba(212, 175, 55, 0.1); border " style="border-color: rgba(212, 175, 55, 0.2);">
                        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                        <span class="text-xs " style="color: var(--primary-accent); font-semibold"><?= t('footer.operational') ?></span>
                    </div>
                </div>

                <!-- Global Offices -->
                <div class="text-center sm:text-left">
                    <h4 class="text-sm font-bold uppercase tracking-wider " style="color: var(--primary-accent); mb-4"><?= t('footer.offices.title') ?></h4>
                    <div class="space-y-6 text-gray-300">
                        <!-- Geneva Office -->
                        <div class="pb-4 border-b border-gray-700/50">
                            <p class="font-semibold text-white mb-1"><?= t('footer.offices.switzerland') ?></p>
                            <p class="text-xs leading-relaxed text-gray-400"><?= t('footer.offices.switzerland_address') ?></p>
                        </div>
                        <!-- Dubai HQ -->
                        <div>
                            <p class="font-semibold text-white mb-1"><?= t('footer.offices.uae') ?></p>
                            <p class="text-xs mb-1 font-medium" style="color: rgba(212, 175, 55, 0.8);" dir="rtl"><?= t('footer.offices.uae_company_ar') ?></p>
                            <p class="text-xs text-gray-500 mb-2">(<?= t('footer.offices.uae_company_en') ?>)</p>
                            <p class="text-xs leading-relaxed text-gray-400 mb-2"><?= t('footer.offices.uae_address') ?></p>
                            <p class="text-xs text-gray-400">
                                <svg class="w-3 h-3 inline mr-1 " style="color: var(--primary-accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <a href="tel:+971547335770" class="hover:" style="color: var(--primary-accent); transition-colors"><?= t('footer.offices.uae_phone') ?></a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="text-center sm:text-left">
                    <h4 class="text-sm font-bold uppercase tracking-wider " style="color: var(--primary-accent); mb-4"><?= t('footer.contact.title') ?></h4>
                    <ul class="space-y-3 text-gray-300">
                        <li class="flex items-center justify-center sm:justify-start gap-2">
                            <svg class="w-4 h-4 " style="color: var(--primary-accent); flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <a href="mailto:ops@blackbox.codes" class="text-sm hover:" style="color: var(--primary-accent); transition-colors">ops@blackbox.codes</a>
                        </li>
                        <li class="flex items-center justify-center sm:justify-start gap-2">
                            <svg class="w-4 h-4 " style="color: var(--primary-accent); flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span class="text-xs text-gray-500 mr-1"><?= t('footer.contact.dk_label') ?></span>
                            <a href="tel:+4531330033" class="text-sm hover:" style="color: var(--primary-accent); transition-colors">+45 31 33 00 33</a>
                        </li>
                    </ul>
                    <!-- PGP Key Link -->
                    <div class="mt-6 pt-4 border-t border-gray-700/50">
                        <a href="contact.php#secure-comm" class="inline-flex items-center gap-2 text-xs text-gray-500 hover:" style="color: var(--primary-accent); transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <?= t('footer.pgp_link') ?>
                        </a>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="text-center sm:text-left">
                    <h4 class="text-sm font-bold uppercase tracking-wider " style="color: var(--primary-accent); mb-4"><?= t('footer.social.title') ?></h4>
                    <div class="flex justify-center sm:justify-start gap-3">
                        <a href="#" class="social-icon-link group" aria-label="LinkedIn">
                            <svg class="social-icon-svg" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                            </svg>
                        </a>
                        <a href="#" class="social-icon-link group" aria-label="Twitter">
                            <svg class="social-icon-svg" fill="currentColor" viewBox="0 0 24 24">
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
                    <div class="flex items-center gap-4">
                        <a href="privacy.php" class="hover:" style="color: var(--primary-accent); transition-colors"><?= t('footer.privacy', 'Privatlivspolitik') ?></a>
                        <span class="text-gray-700">|</span>
                        <a href="terms.php" class="hover:" style="color: var(--primary-accent); transition-colors"><?= t('footer.terms', 'Vilkår') ?></a>
                        <span class="text-gray-700">|</span>
                        <span class="text-gray-600"><?= t('footer.recaptcha_notice', 'This site is protected by reCAPTCHA') ?></span>
                        <?php if (defined('BBX_QA_MODE') && BBX_QA_MODE): ?>
                            <span class="qa-version-chip inline-flex items-center gap-2 px-3 py-1 rounded-full border border-amber-400/60 text-amber-200 text-[0.65rem] uppercase tracking-[0.08em]">
                                Blackbox UI v1.0.0-QA
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <?php if (defined('BBX_QA_MODE') && BBX_QA_MODE) {
        include __DIR__ . '/components/qa-debug-panel.php';
    } ?>

    <?php if (!empty($show_alphabot) && empty($_BBX_DISABLE_CHAT)): ?>
        <div id="alphabot-overlay" class="alphabot-overlay" aria-hidden="true"></div>
    <?php endif; ?>

    <?php /* ═══════════════════════════════════════════════════════════════
         P0 iOS SCROLL FIX: STICKY CTA PERMANENTLY DISABLED
         These fixed-positioned bottom elements capture first touch/wheel events
         on iOS WebKit and prevent scroll passthrough.
         CSS failsafe in critical.css hides via display:none !important.
         Original code commented out below for reference.
         ═══════════════════════════════════════════════════════════════
    <?php if (empty($_BBX_DISABLE_CTA)): ?>
    <aside id="sticky-cta"
        class="sticky-cta-bar"
        data-component="sticky-cta"
        hidden
        data-hidden="true"
        role="region"
        aria-live="polite"
        aria-label="<?= htmlspecialchars(t('cta_bar.region_label')) ?>">
        
        <div class="sticky-cta-bar__row sticky-cta-bar__row--header">
            <div class="sticky-cta-bar__label">
                <span class="sticky-cta-bar__eyebrow"><?= t('cta_bar.eyebrow') ?></span>
                <span class="sticky-cta-bar__title"><?= t('cta_bar.title') ?></span>
            </div>
            <button type="button"
                class="sticky-cta-bar__dismiss"
                data-sticky-cta-close
                aria-label="<?= htmlspecialchars(t('cta_bar.dismiss')) ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="sticky-cta-bar__row sticky-cta-bar__row--actions">
            <a href="demo.php" class="sticky-cta-bar__cta sticky-cta-bar__cta--primary">
                <?= t('cta_bar.primary') ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6"/>
                </svg>
            </a>
            <a href="tel:+4531330033" class="sticky-cta-bar__cta sticky-cta-bar__cta--secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                </svg>
                <?= t('cta_bar.secondary') ?>
            </a>
        </div>
    </aside>
    <?php endif; ?>
    END P0 DISABLED */ ?>

    <?php if (empty($disable_alphabot) && empty($_BBX_DISABLE_CHAT)): ?>
        <div class="bbx-command-rail<?= empty($show_alphabot) ? ' bbx-command-rail--cta-only' : '' ?>">
            <?php if (!empty($show_alphabot)): ?>
                <div id="alphabot-container" class="alphabot-widget" data-component="alphabot" aria-live="polite">
                    <button type="button"
                        id="alphabot-toggle-btn"
                        class="alphabot-toggle"
                        aria-expanded="false"
                        aria-controls="alphabot-panel"
                        aria-label="Åbn Blackbox EYE Assistant sikkerhedsassistent">
                        <span class="alphabot-status-dot" aria-hidden="true"></span>
                        <span class="alphabot-label"><?= t('alphabot.title') ?></span>
                    </button>
                    <section id="alphabot-panel"
                        class="alphabot-panel"
                        role="dialog"
                        aria-modal="false"
                        aria-hidden="true"
                        inert
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
        </div>
    <?php endif; ?>
    <?php $asset_version = $GLOBALS['css_version'] ?? $css_version ?? '1.6.22'; ?>
    <script src="assets/js/router-guard.js" defer></script>
    <script src="assets/js/qa-mode.js" defer></script>
    <script src="assets/js/site.min.js?v=<?= htmlspecialchars($asset_version) ?>" defer></script>
    <script src="script.js" defer></script>
    <?php if (isset($current_page) && $current_page === 'home'): ?>
        <!-- Sprint 9: Defer heavy 3D hero to idle time for better INP -->
        <script>
        (function() {
            function loadGrapheneHero() {
                var script = document.createElement('script');
                script.type = 'module';
                script.src = 'assets/js/graphene-hero.js';
                document.body.appendChild(script);
            }
            // Use requestIdleCallback if available, otherwise setTimeout
            if ('requestIdleCallback' in window) {
                requestIdleCallback(loadGrapheneHero, { timeout: 2000 });
            } else {
                setTimeout(loadGrapheneHero, 100);
            }
        })();
        </script>
    <?php endif; ?>
    
    <!-- Login Dropdown - CRITICAL FIX -->
    <script>
    (function() {
        function initLoginDropdown() {
            var dropdown = document.querySelector('.console-access-dropdown');
            var trigger = document.querySelector('.console-access-trigger');
            
            if (!trigger || !dropdown) {
                console.error('Login dropdown elements not found!');
                return;
            }
            
            console.log('Login dropdown initialized');
            
            // Remove any existing listeners by cloning
            var newTrigger = trigger.cloneNode(true);
            trigger.parentNode.replaceChild(newTrigger, trigger);
            trigger = newTrigger;
            
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var isOpen = dropdown.getAttribute('aria-expanded') === 'true';
                var newState = isOpen ? 'false' : 'true';
                
                dropdown.setAttribute('aria-expanded', newState);
                trigger.setAttribute('aria-expanded', newState);
                
                console.log('Login dropdown clicked, new state:', newState);
            });
            
            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target)) {
                    dropdown.setAttribute('aria-expanded', 'false');
                    trigger.setAttribute('aria-expanded', 'false');
                }
            });
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    dropdown.setAttribute('aria-expanded', 'false');
                    trigger.setAttribute('aria-expanded', 'false');
                }
            });
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initLoginDropdown);
        } else {
            initLoginDropdown();
        }
    })();
    </script>
    
    <?php 
    // Sprint 6: Include SSO modal globally for mobile drawer SSO buttons
    include __DIR__ . '/sso-request-modal.php'; 
    ?>
    
    <script>
    // Sprint 6: Initialize SSO button handlers for mobile drawer
    (function() {
        function initMobileSsoButtons() {
            document.querySelectorAll('[data-sso-request]').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var consoleName = this.getAttribute('data-sso-request') || 'ccs';
                    if (window.bbxSsoRequest) {
                        window.bbxSsoRequest.show({
                            console: consoleName,
                            provider: ''
                        });
                        // Close mobile drawer when opening SSO modal
                        var drawer = document.querySelector('.mobile-nav-drawer');
                        if (drawer) {
                            drawer.classList.remove('is-open');
                            drawer.setAttribute('aria-hidden', 'true');
                        }
                    }
                });
            });
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMobileSsoButtons);
        } else {
            initMobileSsoButtons();
        }
    })();
    </script>
    
<!-- BBX_MARKER_1766950928 (ACTIVE_FOOTER_PROBE) -->
</body>

    </html>




