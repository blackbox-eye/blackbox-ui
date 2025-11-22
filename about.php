<?php
require_once __DIR__ . '/includes/i18n.php';
$current_page = 'about';
$page_title = t('about.hero_section.title');
$meta_description = t('about.hero_section.description');
include 'includes/site-header.php';
?>

<main class="pt-16">
    <section class="py-20 sm:py-24">
        <div class="container mx-auto px-4">
            <div class="glass-effect rounded-3xl p-8 sm:p-12 mb-16 text-center section-fade-in">
                <p class="text-amber-400 uppercase tracking-widest text-sm font-semibold mb-4"><?= t('about.hero_section.tagline') ?></p>
                <h1 class="text-3xl sm:text-5xl font-bold mb-6"><?= t('about.hero_section.title') ?></h1>
                <p class="text-gray-300 text-base sm:text-lg max-w-3xl mx-auto">
                    <?= t('about.hero_section.description') ?>
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 mb-16 section-fade-in">
                <div class="glass-effect rounded-2xl p-8">
                    <h2 class="text-2xl font-bold text-amber-400 mb-4"><?= t('about.pillars.vision_title') ?></h2>
                    <p class="text-gray-300"><?= t('about.pillars.vision_body') ?></p>
                </div>
                <div class="glass-effect rounded-2xl p-8">
                    <h2 class="text-2xl font-bold text-amber-400 mb-4"><?= t('about.pillars.mission_title') ?></h2>
                    <p class="text-gray-300"><?= t('about.pillars.mission_body') ?></p>
                </div>
            </div>

            <section class="text-center mb-20 section-fade-in">
                <h2 class="text-3xl sm:text-4xl font-bold mb-10"><?= t('about.values_grid.title') ?></h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <?php foreach (['discretion', 'innovation', 'integrity', 'perfection'] as $value): ?>
                        <div class="glass-effect rounded-2xl p-6">
                            <h3 class="text-xl font-bold text-amber-400 mb-2"><?= t('about.values_grid.items.' . $value . '.title') ?></h3>
                            <p class="text-gray-400"><?= t('about.values_grid.items.' . $value . '.body') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="glass-effect rounded-3xl p-8 sm:p-12 section-fade-in">
                <h2 class="text-3xl sm:text-4xl font-bold text-center mb-6"><?= t('about.teams.title') ?></h2>
                <p class="text-center text-gray-400 max-w-3xl mx-auto mb-10">
                    <?= t('about.teams.description') ?>
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-left">
                    <?php
                    $team_blocks = ['recon', 'red', 'stealth', 'blue'];
                    foreach ($team_blocks as $unit): ?>
                        <div class="glass-effect rounded-2xl p-6">
                            <h3 class="text-xl font-semibold text-amber-400 mb-2"><?= t('about.teams.units.' . $unit . '.title') ?></h3>
                            <p class="text-gray-300"><?= t('about.teams.units.' . $unit . '.body') ?></p>
                        </div>
                    <?php endforeach; ?>
                    <div class="md:col-span-2 glass-effect rounded-2xl p-6">
                        <h3 class="text-xl font-semibold text-amber-400 mb-2"><?= t('about.teams.units.custom.title') ?></h3>
                        <p class="text-gray-300"><?= t('about.teams.units.custom.body') ?></p>
                    </div>
                </div>
            </section>
        </div>
    </section>

    <section class="py-16 sm:py-20 bg-gray-900/40 section-fade-in">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4"><?= t('about.cta.title') ?></h2>
            <p class="text-gray-400 max-w-2xl mx-auto mb-8">
                <?= t('about.cta.description') ?>
            </p>
            <a href="contact.php" class="inline-flex items-center justify-center bg-amber-400 text-black font-semibold py-3 px-8 rounded-lg hover:bg-amber-500 transition-colors">
                <?= t('about.cta.button') ?>
            </a>
        </div>
    </section>
</main>

<?php include 'includes/site-footer.php'; ?>
