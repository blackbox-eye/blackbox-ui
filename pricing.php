<?php
require_once __DIR__ . '/includes/i18n.php';
$current_page = 'pricing';
$page_title = t('pricing.title') . ' | ' . t('site.name');
include 'includes/site-header.php';
?>

<main class="pt-16">
    <section class="py-16 sm:py-20 section-fade-in page-section">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <p class=" uppercase tracking-widest text-sm font-semibold mb-4" style="color: var(--primary-accent);"><?= t('pricing.subtitle') ?></p>
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
                        <select id="industry-select" class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-3 py-2.5 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-2" style="--tw-ring-color: var(--primary-accent);">
                            <option><?= t('pricing.ai_advisor.industries.manufacturing') ?></option>
                            <option><?= t('pricing.ai_advisor.industries.public') ?></option>
                            <option><?= t('pricing.ai_advisor.industries.law') ?></option>
                            <option><?= t('pricing.ai_advisor.industries.real_estate') ?></option>
                            <option><?= t('pricing.ai_advisor.industries.retail') ?></option>
                            <option><?= t('pricing.ai_advisor.industries.it') ?></option>
                        </select>
                    </div>
                    <div>
                        <label for="employee-count" class="block text-sm font-medium text-gray-300 mb-2"><?= t('pricing.ai_advisor.employees_label') ?></label>
                        <input type="number" id="employee-count" value="50" min="1" class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-3 py-2.5 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-2" style="--tw-ring-color: var(--primary-accent);">
                    </div>
                    <div class="lg:self-end">
                        <button id="get-recommendation-btn" class="w-full border-2" style="background: rgba(212, 175, 55, 0.1); border-color: var(--primary-accent); color: var(--primary-accent); font-semibold py-2.5 px-4 rounded-lg transition-colors">
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

            <div id="pricing-calculator"
                class="max-w-4xl mx-auto glass-effect rounded-3xl p-6 sm:p-8 lg:p-10 mb-16"
                data-result-heading="<?= htmlspecialchars(t('pricing.calculator.result.heading')) ?>"
                data-result-monthly="<?= htmlspecialchars(t('pricing.calculator.result.monthly_label')) ?>"
                data-result-per-user="<?= htmlspecialchars(t('pricing.calculator.result.per_user_label')) ?>"
                data-result-recommended="<?= htmlspecialchars(t('pricing.calculator.result.recommended_plan')) ?>"
                data-result-addons="<?= htmlspecialchars(t('pricing.calculator.result.addons_label')) ?>"
                data-result-next="<?= htmlspecialchars(t('pricing.calculator.result.next_steps')) ?>"
                data-result-compliance="<?= htmlspecialchars(t('pricing.calculator.result.compliance_note')) ?>"
                data-result-disclaimer="<?= htmlspecialchars(t('pricing.calculator.result.estimation_disclaimer')) ?>"
                data-result-popular="<?= htmlspecialchars(t('pricing.calculator.result.badge_popular')) ?>"
                data-demo-label="<?= htmlspecialchars(t('header.menu.demo')) ?>"
                data-plan-mvp-basis="<?= htmlspecialchars(t('pricing.mvp.basis.title')) ?>"
                data-plan-mvp-pro="<?= htmlspecialchars(t('pricing.mvp.pro.title')) ?>"
                data-plan-mvp-premium="<?= htmlspecialchars(t('pricing.mvp.premium.title')) ?>"
                data-plan-standard="<?= htmlspecialchars(t('pricing.enterprise.standard.title')) ?>"
                data-plan-premium="<?= htmlspecialchars(t('pricing.enterprise.premium.title')) ?>"
                data-plan-enterprise="<?= htmlspecialchars(t('pricing.enterprise.enterprise.title')) ?>">
                <div class="max-w-3xl mx-auto text-center mb-10">
                    <h2 class="text-2xl sm:text-3xl font-bold mb-4"><?= t('pricing.calculator.title') ?></h2>
                    <p class="text-gray-300 text-base sm:text-lg">
                        <?= t('pricing.calculator.description') ?>
                    </p>
                </div>
                <form id="pricing-calculator-form" class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 items-start" novalidate>
                    <div class="space-y-6">
                        <div>
                            <label for="calc-users" class="block text-sm font-medium text-gray-300 mb-2"><?= t('pricing.calculator.users_label') ?></label>
                            <input
                                type="number"
                                id="calc-users"
                                name="users"
                                class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-3 py-2.5 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-2" style="--tw-ring-color: var(--primary-accent);"
                                min="1"
                                step="1"
                                inputmode="numeric"
                                required
                                data-required-message="<?= htmlspecialchars(t('pricing.calculator.validation.users_required')) ?>"
                                data-min-message="<?= htmlspecialchars(t('pricing.calculator.validation.users_min')) ?>"
                                aria-describedby="calc-users-help calc-users-error">
                            <p id="calc-users-help" class="text-xs text-gray-400 mt-2"><?= t('pricing.calculator.users_help') ?></p>
                            <p id="calc-users-error" class="text-sm text-rose-400 mt-2 hidden" data-error-for="calc-users"></p>
                        </div>
                        <div>
                            <label for="calc-endpoints" class="block text-sm font-medium text-gray-300 mb-2"><?= t('pricing.calculator.endpoints_label') ?></label>
                            <input
                                type="number"
                                id="calc-endpoints"
                                name="endpoints"
                                class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-3 py-2.5 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-2" style="--tw-ring-color: var(--primary-accent);"
                                min="0"
                                step="10"
                                inputmode="numeric"
                                required
                                data-required-message="<?= htmlspecialchars(t('pricing.calculator.validation.endpoints_required')) ?>"
                                aria-describedby="calc-endpoints-help calc-endpoints-error">
                            <p id="calc-endpoints-help" class="text-xs text-gray-400 mt-2"><?= t('pricing.calculator.endpoints_help') ?></p>
                            <p id="calc-endpoints-error" class="text-sm text-rose-400 mt-2 hidden" data-error-for="calc-endpoints"></p>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <fieldset>
                            <legend class="block text-sm font-medium text-gray-300 mb-2"><?= t('pricing.calculator.addons_label') ?></legend>
                            <p class="text-xs text-gray-400 mb-4" id="calc-addons-help"><?= t('pricing.calculator.addons_help') ?></p>
                            <div class="space-y-3" role="group" aria-describedby="calc-addons-help">
                                <label class="flex items-start gap-3 bg-gray-800/50 border border-gray-700 rounded-xl px-4 py-3  transition-colors">
                                    <input type="checkbox" class="mt-1 accent-color" style="accent-color: var(--primary-accent);" name="addons" value="pve">
                                    <span class="text-sm text-gray-200">
                                        <span class="block font-semibold text-white"><?= t('pricing.calculator.addons.pve.title') ?></span>
                                        <span class="block text-xs text-gray-400 mt-1"><?= t('pricing.calculator.addons.pve.description') ?></span>
                                    </span>
                                </label>
                                <label class="flex items-start gap-3 bg-gray-800/50 border border-gray-700 rounded-xl px-4 py-3  transition-colors">
                                    <input type="checkbox" class="mt-1 accent-color" style="accent-color: var(--primary-accent);" name="addons" value="aut">
                                    <span class="text-sm text-gray-200">
                                        <span class="block font-semibold text-white"><?= t('pricing.calculator.addons.aut.title') ?></span>
                                        <span class="block text-xs text-gray-400 mt-1"><?= t('pricing.calculator.addons.aut.description') ?></span>
                                    </span>
                                </label>
                                <label class="flex items-start gap-3 bg-gray-800/50 border border-gray-700 rounded-xl px-4 py-3  transition-colors">
                                    <input type="checkbox" class="mt-1 accent-color" style="accent-color: var(--primary-accent);" name="addons" value="bridge">
                                    <span class="text-sm text-gray-200">
                                        <span class="block font-semibold text-white"><?= t('pricing.calculator.addons.bridge.title') ?></span>
                                        <span class="block text-xs text-gray-400 mt-1"><?= t('pricing.calculator.addons.bridge.description') ?></span>
                                    </span>
                                </label>
                                <label class="flex items-start gap-3 bg-gray-800/50 border border-gray-700 rounded-xl px-4 py-3  transition-colors">
                                    <input type="checkbox" class="mt-1 accent-color" style="accent-color: var(--primary-accent);" name="addons" value="support">
                                    <span class="text-sm text-gray-200">
                                        <span class="block font-semibold text-white"><?= t('pricing.calculator.addons.support.title') ?></span>
                                        <span class="block text-xs text-gray-400 mt-1"><?= t('pricing.calculator.addons.support.description') ?></span>
                                    </span>
                                </label>
                            </div>
                        </fieldset>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="submit" class="inline-flex items-center justify-center border-2" style="background: rgba(212, 175, 55, 0.1); border-color: var(--primary-accent); color: var(--primary-accent); backdrop-filter: blur(8px); font-semibold py-3 px-6 rounded-lg  transition-colors" data-loading-text="<?= htmlspecialchars(t('pricing.calculator.loading', 'Beregner...')) ?>">
                                <?= t('pricing.calculator.submit') ?>
                            </button>
                            <button type="reset" class="inline-flex items-center justify-center border border-gray-600 text-gray-300 font-semibold py-3 px-6 rounded-lg  hover:text-white transition-colors">
                                <?= t('pricing.calculator.reset') ?>
                            </button>
                        </div>
                        <p id="pricing-calculator-status" class="mt-2 text-sm text-gray-400" aria-live="polite" role="status" hidden></p>
                    </div>
                </form>
                <div id="pricing-calculator-result" class="hidden mt-10" aria-live="polite" role="region" aria-label="<?= htmlspecialchars(t('pricing.calculator.result.heading')) ?>"></div>
            </div>

            <div class="pricing-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 items-stretch mb-16">
                <!-- MVP-Basis -->
                <article class="glass-effect rounded-2xl p-6 lg:p-8 text-center flex flex-col">
                    <div class="mb-3">
                        <span class="text-xs uppercase tracking-wider  font-semibold" style="color: var(--primary-accent);"><?= t('pricing.mvp.basis.badge') ?></span>
                    </div>
                    <h2 class="text-xl lg:text-2xl font-bold mb-2"><?= t('pricing.mvp.basis.title') ?></h2>
                    <div class="text-3xl font-bold  mb-2" style="color: var(--primary-accent);"><?= t('pricing.mvp.basis.price') ?></div>
                    <p class="text-gray-400 text-sm mb-6"><?= t('pricing.mvp.basis.period') ?></p>
                    <p class="text-gray-400 mb-6 text-sm lg:text-base"><?= t('pricing.mvp.basis.description') ?></p>
                    <ul class="text-left space-y-2 lg:space-y-3 text-gray-300 my-6 lg:my-8 flex-grow text-sm lg:text-base">
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.mvp.basis.features.greyeye') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.mvp.basis.features.idmatrix') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.mvp.basis.features.support') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.mvp.basis.features.users') ?></span>
                        </li>
                    </ul>
                    <div class="text-xs text-gray-500 mb-4"><?= t('pricing.mvp.basis.subscription') ?></div>
                    <a href="contact.php" class="mt-auto w-full inline-block border-2" style="border-color: var(--primary-accent);  py-3 rounded-lg  transition-all font-semibold text-sm lg:text-base" style="color: var(--primary-accent);"><?= t('pricing.cta.button') ?></a>
                </article>

                <!-- MVP-Pro -->
                <article class="glass-effect rounded-2xl p-6 lg:p-8 text-center flex flex-col min-h-[500px]">
                    <div class="mb-3">
                        <span class="text-xs uppercase tracking-wider text-blue-400 font-semibold"><?= t('pricing.mvp.pro.badge') ?></span>
                    </div>
                    <h2 class="text-xl lg:text-2xl font-bold mb-2"><?= t('pricing.mvp.pro.title') ?></h2>
                    <div class="text-3xl font-bold  mb-2" style="color: var(--primary-accent);"><?= t('pricing.mvp.pro.price') ?></div>
                    <p class="text-gray-400 text-sm mb-6"><?= t('pricing.mvp.pro.period') ?></p>
                    <p class="text-gray-400 mb-6 text-sm lg:text-base"><?= t('pricing.mvp.pro.description') ?></p>
                    <ul class="text-left space-y-2 lg:space-y-3 text-gray-300 my-6 lg:my-8 flex-grow text-sm lg:text-base">
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.mvp.pro.features.includes') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.mvp.pro.features.greyeye') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.mvp.pro.features.idmatrix') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.mvp.pro.features.users') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.mvp.pro.features.support') ?></span>
                        </li>
                    </ul>
                    <div class="text-xs text-gray-500 mb-4"><?= t('pricing.mvp.pro.subscription') ?></div>
                    <a href="contact.php" class="mt-auto w-full inline-block border-2" style="border-color: var(--primary-accent);  py-3 rounded-lg  transition-all font-semibold text-sm lg:text-base" style="color: var(--primary-accent);"><?= t('pricing.cta.button') ?></a>
                </article>

                <!-- MVP-Premium -->
                <article class="glass-effect rounded-2xl p-6 lg:p-8 text-center flex flex-col min-h-[500px]">
                    <div class="mb-3">
                        <span class="text-xs uppercase tracking-wider  font-semibold" style="color: var(--primary-accent);"><?= t('pricing.mvp.premium.badge') ?></span>
                    </div>
                    <h2 class="text-xl lg:text-2xl font-bold mb-2"><?= t('pricing.mvp.premium.title') ?></h2>
                    <div class="text-3xl font-bold  mb-2" style="color: var(--primary-accent);"><?= t('pricing.mvp.premium.price') ?></div>
                    <p class="text-gray-400 text-sm mb-6"><?= t('pricing.mvp.premium.period') ?></p>
                    <p class="text-gray-400 mb-6 text-sm lg:text-base"><?= t('pricing.mvp.premium.description') ?></p>
                    <ul class="text-left space-y-2 lg:space-y-3 text-gray-300 my-6 lg:my-8 flex-grow text-sm lg:text-base">
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.mvp.premium.features.includes') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.mvp.premium.features.pve') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.mvp.premium.features.workshop') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.mvp.premium.features.users') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.mvp.premium.features.support') ?></span>
                        </li>
                    </ul>
                    <div class="text-xs text-gray-500 mb-4"><?= t('pricing.mvp.premium.subscription') ?></div>
                    <a href="contact.php" class="mt-auto w-full inline-block border-2" style="border-color: var(--primary-accent);  py-3 rounded-lg  transition-all font-semibold text-sm lg:text-base" style="color: var(--primary-accent);"><?= t('pricing.cta.button') ?></a>
                </article>
            </div>

            <div class="text-center mb-12">
                <h3 class="text-2xl font-bold mb-4"><?= t('pricing.enterprise.section_title') ?></h3>
                <p class="text-gray-400 max-w-2xl mx-auto"><?= t('pricing.enterprise.section_description') ?></p>
            </div>

            <div class="pricing-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 items-stretch mb-16">
                <article class="glass-effect rounded-2xl p-6 lg:p-8 text-center flex flex-col order-2 lg:order-1">
                    <h2 class="text-xl lg:text-2xl font-bold mb-2"><?= t('pricing.enterprise.standard.title') ?></h2>
                    <div class="text-3xl font-bold  mb-2" style="color: var(--primary-accent);"><?= t('pricing.enterprise.standard.price') ?></div>
                    <p class="text-gray-400 text-sm mb-6"><?= t('pricing.enterprise.standard.period') ?></p>
                    <p class="text-gray-400 mb-6 text-sm lg:text-base"><?= t('pricing.enterprise.standard.description') ?></p>
                    <ul class="text-left space-y-2 lg:space-y-3 text-gray-300 my-6 lg:my-8 flex-grow text-sm lg:text-base">
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.enterprise.standard.features.greyeye') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.enterprise.standard.features.idmatrix') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.enterprise.standard.features.reporting') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.enterprise.standard.features.users') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.enterprise.standard.features.support') ?></span>
                        </li>
                    </ul>
                    <div class="text-xs text-gray-500 mb-4"><?= t('pricing.enterprise.standard.subscription') ?></div>
                    <a href="contact.php" class="mt-auto w-full inline-block border-2" style="border-color: var(--primary-accent);  py-3 rounded-lg  transition-all font-semibold text-sm lg:text-base" style="color: var(--primary-accent);"><?= t('pricing.cta.contact') ?></a>
                </article>
                <article class="relative glass-effect rounded-2xl p-6 lg:p-8 text-center border-2" style="border-color: var(--primary-accent); flex flex-col min-h-[500px] order-1 lg:order-2 transform lg:scale-105 z-10">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 border-2" style="background: rgba(212, 175, 55, 0.1); border-color: var(--primary-accent); color: var(--primary-accent); backdrop-filter: blur(8px); px-4 py-1 rounded-full text-xs lg:text-sm font-bold whitespace-nowrap"><?= t('pricing.enterprise.premium.badge') ?></div>
                    <h2 class="text-xl lg:text-2xl font-bold mb-2 mt-2"><?= t('pricing.enterprise.premium.title') ?></h2>
                    <div class="text-3xl font-bold  mb-2" style="color: var(--primary-accent);"><?= t('pricing.enterprise.premium.price') ?></div>
                    <p class="text-gray-400 text-sm mb-6"><?= t('pricing.enterprise.premium.period') ?></p>
                    <p class="text-gray-400 mb-6 text-sm lg:text-base"><?= t('pricing.enterprise.premium.description') ?></p>
                    <ul class="text-left space-y-2 lg:space-y-3 text-gray-300 my-6 lg:my-8 flex-grow text-sm lg:text-base">
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.enterprise.premium.features.includes') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.enterprise.premium.features.pve') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.enterprise.premium.features.aut') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.enterprise.premium.features.reporting') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.enterprise.premium.features.users') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.enterprise.premium.features.support') ?></span>
                        </li>
                    </ul>
                    <div class="text-xs text-gray-500 mb-4"><?= t('pricing.enterprise.premium.subscription') ?></div>
                    <a href="contact.php" class="mt-auto w-full inline-block border-2" style="background: rgba(212, 175, 55, 0.1); border-color: var(--primary-accent); color: var(--primary-accent); backdrop-filter: blur(8px); py-3 rounded-lg  transition-all font-semibold text-sm lg:text-base"><?= t('pricing.cta.contact') ?></a>
                </article>
                <article class="glass-effect rounded-2xl p-6 lg:p-8 text-center flex flex-col min-h-[500px] order-3">
                    <h2 class="text-xl lg:text-2xl font-bold mb-2"><?= t('pricing.enterprise.enterprise.title') ?></h2>
                    <div class="text-3xl font-bold  mb-2" style="color: var(--primary-accent);"><?= t('pricing.enterprise.enterprise.price') ?></div>
                    <p class="text-gray-400 text-sm mb-6"><?= t('pricing.enterprise.enterprise.period') ?></p>
                    <p class="text-gray-400 mb-6 text-sm lg:text-base"><?= t('pricing.enterprise.enterprise.description') ?></p>
                    <ul class="text-left space-y-2 lg:space-y-3 text-gray-300 my-6 lg:my-8 flex-grow text-sm lg:text-base">
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.enterprise.enterprise.features.includes') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.enterprise.enterprise.features.teams') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.enterprise.enterprise.features.operations') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.enterprise.enterprise.features.manager') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.enterprise.enterprise.features.users') ?></span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2 mt-0.5">✓</span>
                            <span><?= t('pricing.enterprise.enterprise.features.support') ?></span>
                        </li>
                    </ul>
                    <div class="text-xs text-gray-500 mb-4"><?= t('pricing.enterprise.enterprise.subscription') ?></div>
                    <a href="contact.php" class="mt-auto w-full inline-block border-2" style="border-color: var(--primary-accent);  py-3 rounded-lg  transition-all font-semibold text-sm lg:text-base" style="color: var(--primary-accent);"><?= t('pricing.cta.contact') ?></a>
                </article>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 bg-gray-900/40 section-fade-in page-section page-section--soft">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4"><?= t('pricing.custom.title') ?></h2>
            <p class="text-gray-400 max-w-2xl mx-auto mb-8"><?= t('pricing.custom.description') ?></p>
            <a href="contact.php" class="inline-flex items-center justify-center border-2" style="background: rgba(212, 175, 55, 0.1); border-color: var(--primary-accent); color: var(--primary-accent); backdrop-filter: blur(8px); font-semibold py-3 px-8 rounded-lg  transition-colors">
                <?= t('pricing.custom.button') ?>
            </a>
        </div>
    </section>
</main>

<?php include 'includes/site-footer.php'; ?>
