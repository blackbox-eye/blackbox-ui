<?php
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/i18n.php';

$current_language = bbx_get_language();

// Handle language switching via query parameter
if (isset($_GET['lang']) && in_array($_GET['lang'], ['da', 'en'])) {
    bbx_set_language($_GET['lang']);
    // Redirect to remove query parameter from URL
    $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
    header('Location: ' . $redirect_url);
    exit;
}

$page_title = $page_title ?? 'Blackbox EYE™ - Intelligent Sikkerhed';
$current_page = $current_page ?? basename($_SERVER['PHP_SELF'], '.php');
$meta_description = $meta_description ?? 'Blackbox EYE™ leverer avancerede sikkerhedsløsninger, efterretning og AI-drevet overvågning til virksomheder med højrisiko-profil.';
$meta_keywords = $meta_keywords ?? 'Blackbox EYE, sikkerhed, AI, efterretning, cyber defense, overvågning';
$meta_author = $meta_author ?? BBX_SITE_NAME;
$meta_og_title = $meta_og_title ?? $page_title;
$meta_og_description = $meta_og_description ?? $meta_description;
$meta_og_image = $meta_og_image ?? BBX_SITE_BASE_URL . '/assets/logo.png';
$meta_og_type = $meta_og_type ?? 'website';
$canonical_url = $canonical_url ?? BBX_SITE_BASE_URL . ($current_page === 'index' || $current_page === 'home' ? '/' : '/' . $current_page . '.php');
$meta_robots = $meta_robots ?? 'index,follow';

$default_structured_data = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => BBX_SITE_NAME,
    'url' => BBX_SITE_BASE_URL,
    'logo' => BBX_SITE_BASE_URL . '/assets/logo.png',
    'contactPoint' => [
        [
            '@type' => 'ContactPoint',
            'telephone' => '+45 31 33 00 33',
            'contactType' => 'customer support',
            'email' => 'ops@blackbox.codes',
            'areaServed' => 'GLOBAL'
        ]
    ]
];

if (!empty($structured_data) && is_array($structured_data)) {
    $default_structured_data = array_merge($default_structured_data, $structured_data);
}

if (!function_exists('aig_nav_class')) {
    function aig_nav_class(string $slug, string $current): string
    {
        return $slug === $current
            ? 'nav-link-active'
            : '';
    }

    function aig_nav_aria(string $slug, string $current): string
    {
        return $slug === $current ? 'aria-current="page"' : '';
    }

    /**
     * Generate breadcrumb trail based on current page
     * @param string $current_page Current page slug
     * @param string $page_title Optional custom page title
     * @return array Breadcrumb items
     */
    function aig_get_breadcrumbs(string $current_page, string $page_title = ''): array
    {
        $breadcrumb_map = [
            'index' => ['label' => t('header.menu.home'), 'href' => '/'],
            'home' => ['label' => t('header.menu.home'), 'href' => '/'],
            'about' => ['label' => t('header.menu.about'), 'href' => 'about.php'],
            'products' => ['label' => t('header.menu.products'), 'href' => 'products.php'],
            'cases' => ['label' => t('header.menu.cases'), 'href' => 'cases.php'],
            'pricing' => ['label' => t('header.menu.pricing'), 'href' => 'pricing.php'],
            'contact' => ['label' => t('header.menu.contact'), 'href' => 'contact.php'],
            'demo' => ['label' => t('header.menu.demo'), 'href' => 'demo.php'],
            'free-scan' => ['label' => t('header.menu.free_scan'), 'href' => 'free-scan.php'],
            'faq' => ['label' => 'FAQ', 'href' => 'faq.php'],
            'agent-login' => ['label' => t('header.cta.agent_login'), 'href' => 'agent-login.php'],
            'dashboard' => ['label' => 'Dashboard', 'href' => 'dashboard.php'],
            'admin' => ['label' => 'Admin', 'href' => 'admin.php'],
            'settings' => ['label' => 'Indstillinger', 'href' => 'settings.php'],
        ];

        $breadcrumbs = [
            ['label' => t('header.menu.home'), 'href' => '/', 'current' => false]
        ];

        // Skip breadcrumbs for home page
        if ($current_page === 'index' || $current_page === 'home') {
            return [];
        }

        // Add current page
        if (isset($breadcrumb_map[$current_page])) {
            $breadcrumbs[] = [
                'label' => $page_title ?: $breadcrumb_map[$current_page]['label'],
                'href' => $breadcrumb_map[$current_page]['href'],
                'current' => true
            ];
        } else {
            // Fallback for unknown pages
            $label = $page_title ?: ucfirst(str_replace('-', ' ', $current_page));
            $breadcrumbs[] = [
                'label' => $label,
                'href' => $current_page . '.php',
                'current' => true
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Generate structured data for breadcrumbs (schema.org)
     * @param array $breadcrumbs Breadcrumb items
     * @return array Structured data
     */
    function aig_breadcrumb_structured_data(array $breadcrumbs): array
    {
        $items = [];
        foreach ($breadcrumbs as $index => $crumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['label'],
                'item' => BBX_SITE_BASE_URL . '/' . ltrim($crumb['href'], '/')
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items
        ];
    }
}

// Navigation links - Optimized for Sprint 5 UX improvements
// Reduced to 5 essential items for professional desktop layout
$nav_links = [
    [
        'slug' => 'about',
        'label' => t('header.menu.about'),
        'href' => 'about.php',
    ],
    [
        'slug' => 'products',
        'label' => t('header.menu.solutions'),
        'href' => 'products.php',
    ],
    [
        'slug' => 'cases',
        'label' => t('header.menu.cases'),
        'href' => 'cases.php',
    ],
    [
        'slug' => 'pricing',
        'label' => t('header.menu.pricing'),
        'href' => 'pricing.php',
    ],
    [
        'slug' => 'contact',
        'label' => t('header.menu.contact'),
        'href' => 'contact.php',
    ],
];

// Secondary navigation items for mobile drawer (additional pages)
$secondary_nav_links = [
    ['slug' => 'faq', 'label' => t('header.menu.faq'), 'href' => 'faq.php'],
    ['slug' => 'blog', 'label' => t('header.menu.blog'), 'href' => 'blog.php'],
    ['slug' => 'demo', 'label' => t('header.menu.demo'), 'href' => 'demo.php'],
    ['slug' => 'free-scan', 'label' => t('header.menu.free_scan'), 'href' => 'free-scan.php'],
];

$alphabot_enabled_pages = ['home', 'index', 'about', 'products', 'cases', 'pricing', 'contact', 'demo', 'free-scan', 'faq', 'blog', 'privacy', 'terms'];
$show_alphabot = $show_alphabot ?? in_array($current_page, $alphabot_enabled_pages, true);
if (!empty($disable_alphabot)) {
    $show_alphabot = false;
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_language) ?>" class="scroll-smooth" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark light">
    <script>
        (function() {
            var storageKey = 'bbx-theme';
            var preferred = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
            var storedTheme = null;
            try {
                storedTheme = window.localStorage.getItem(storageKey);
            } catch (err) {
                storedTheme = null;
            }
            var theme = storedTheme === 'light' || storedTheme === 'dark' ? storedTheme : preferred;
            document.documentElement.dataset.theme = theme;
            document.documentElement.style.colorScheme = theme;
            window.__BBX_INITIAL_THEME__ = theme;
        })();
    </script>
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($meta_keywords) ?>">
    <meta name="author" content="<?= htmlspecialchars($meta_author) ?>">
    <meta name="robots" content="<?= htmlspecialchars($meta_robots) ?>">

    <!-- Performance Optimization: Resource Hints -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
    <link rel="dns-prefetch" href="https://www.google.com">
    <link rel="dns-prefetch" href="https://generativelanguage.googleapis.com">

    <link rel="canonical" href="<?= htmlspecialchars($canonical_url) ?>">
    <?php
    $hreflang_locales = [
        'da' => 'da-DK',
        'en' => 'en'
    ];
    $canonical_base = strtok($canonical_url, '?') ?: $canonical_url;
    $alternate_links = [];
    foreach ($hreflang_locales as $lang_code => $locale_code) {
        $alternate_links[$lang_code] = $canonical_base . '?lang=' . $lang_code;
    }
    foreach ($alternate_links as $lang_code => $href): ?>
        <link rel="alternate" hreflang="<?= htmlspecialchars($hreflang_locales[$lang_code]) ?>" href="<?= htmlspecialchars($href) ?>">
    <?php endforeach; ?>
    <link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars($alternate_links['en'] ?? $canonical_base) ?>">

    <meta property="og:title" content="<?= htmlspecialchars($meta_og_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($meta_og_description) ?>">
    <meta property="og:type" content="<?= htmlspecialchars($meta_og_type) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical_url) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($meta_og_image) ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($meta_og_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($meta_og_description) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($meta_og_image) ?>">

    <!-- Local compiled Tailwind CSS (v3 build) -->
    <link rel="stylesheet" href="/assets/css/tailwind.full.css">
    <!-- Custom UI components extracted from previous inline styles -->
    <link rel="stylesheet" href="/assets/css/custom-ui.css">
    <!-- Removed redundant inline Tailwind utility overrides -->

    <!-- Conditional CSS loading -->
    <?php
    // Admin pages need admin.css, marketing pages need marketing.css
    $admin_pages = ['agent-login.php', 'dashboard.php', 'admin.php', 'settings.php'];
    $current_script = basename($_SERVER['SCRIPT_NAME']);
    $is_admin_page = in_array($current_script, $admin_pages);
    // Use minified CSS in production (when DEBUG is not set or false)
    $use_minified = !defined('BBX_DEBUG_RECAPTCHA') || !BBX_DEBUG_RECAPTCHA;
    $css_suffix = $use_minified ? '.min.css' : '.css';

    if ($is_admin_page): ?>
        <link rel="stylesheet" href="/assets/css/admin<?= $css_suffix ?>">
    <?php else: ?>
        <link rel="stylesheet" href="/assets/css/marketing<?= $css_suffix ?>">
    <?php endif; ?>

    <script src="config.js"></script>
    <?php if (BBX_RECAPTCHA_SITE_KEY !== ''): ?>
        <script src="https://www.google.com/recaptcha/api.js?render=<?= htmlspecialchars(BBX_RECAPTCHA_SITE_KEY) ?>" async defer></script>
    <?php endif; ?>
    <script>
        window.RECAPTCHA_SITE_KEY = "<?= htmlspecialchars(BBX_RECAPTCHA_SITE_KEY) ?>";
        window.BBX_SITE_BASE_URL = "<?= htmlspecialchars(BBX_SITE_BASE_URL) ?>";
        window.RECAPTCHA_DEBUG = <?= BBX_DEBUG_RECAPTCHA ? 'true' : 'false' ?>;
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=Chakra+Petch:wght@700&display=swap" rel="stylesheet" crossorigin="anonymous">

    <script type="application/ld+json">
        <?= json_encode($default_structured_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>

    <!-- Inline styles removed; migrated to /assets/css/custom-ui.css -->
</head>

<body class="antialiased">

    <!-- Skip navigation for keyboard users (WCAG 2.1) -->
    <a href="#main-content" class="skip-link"><?= t('common.skip_link') ?></a>

    <header id="main-header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
        <div class="container mx-auto px-4">
            <div class="header-shell">
                <div class="header-grid">
                    <div class="header-brand">
                        <a href="/" class="header-logo-link" aria-label="<?= htmlspecialchars(t('header.menu.home')) ?>">
                            <!-- White logo - visible on dark backgrounds (dark theme) -->
                            <img src="/assets/logo-white.png"
                                alt="BLACKBOX EYE™"
                                class="header-logo header-logo--white"
                                loading="lazy"
                                width="180"
                                height="40">
                            <!-- Black logo - visible on light backgrounds (light theme) -->
                            <img src="/assets/logo-black.png"
                                alt="BLACKBOX EYE™"
                                class="header-logo header-logo--black"
                                loading="lazy"
                                width="180"
                                height="40">
                        </a>
                    </div>
                    <nav class="main-nav header-nav hidden lg:block" aria-label="<?= htmlspecialchars(t('header.desktop.primary_navigation', 'Primær navigation')) ?>">
                        <ul class="main-nav-list" role="list">
                            <?php foreach ($nav_links as $link): ?>
                                <li>
                                    <a href="<?= $link['href'] ?>"
                                        class="nav-chip <?= aig_nav_class($link['slug'], $current_page) ?>"
                                        <?= aig_nav_aria($link['slug'], $current_page) ?>>
                                        <span><?= htmlspecialchars($link['label']) ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            <li class="main-nav-more more-dropdown-wrapper" data-dropdown>
                                <button type="button"
                                    class="nav-chip nav-chip--more more-dropdown-trigger"
                                    aria-haspopup="true"
                                    aria-expanded="false">
                                    <span><?= t('header.menu.more', 'Mere') ?></span>
                                    <svg class="more-dropdown-chevron" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div class="more-dropdown-menu" role="menu">
                                    <a href="faq.php" class="more-dropdown-item <?= $current_page === 'faq' ? 'is-active' : '' ?>" role="menuitem">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <?= t('header.menu.faq') ?>
                                    </a>
                                    <a href="blog.php" class="more-dropdown-item <?= $current_page === 'blog' ? 'is-active' : '' ?>" role="menuitem">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                        </svg>
                                        <?= t('header.menu.blog') ?>
                                    </a>
                                    <a href="demo.php" class="more-dropdown-item <?= $current_page === 'demo' ? 'is-active' : '' ?>" role="menuitem">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <?= t('header.menu.demo') ?>
                                    </a>
                                    <a href="free-scan.php" class="more-dropdown-item <?= $current_page === 'free-scan' ? 'is-active' : '' ?>" role="menuitem">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        <?= t('header.menu.free_scan') ?>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </nav>
                    <div class="header-actions">
                        <a href="agent-login.php" class="header-cta agent-login-cta">
                            <?= t('header.cta.agent_login') ?>
                        </a>
                        <div class="language-switcher-wrapper hidden lg:flex items-center gap-0.5">
                            <a href="?lang=da" class="language-switch <?= $current_language === 'da' ? 'is-active' : '' ?>" aria-label="<?= htmlspecialchars(t('header.language.switch_da')) ?>" <?= $current_language === 'da' ? 'aria-current="true"' : '' ?>>
                                <?= t('header.language.da') ?>
                            </a>
                            <a href="?lang=en" class="language-switch <?= $current_language === 'en' ? 'is-active' : '' ?>" aria-label="<?= htmlspecialchars(t('header.language.switch_en')) ?>" <?= $current_language === 'en' ? 'aria-current="true"' : '' ?>>
                                <?= t('header.language.en') ?>
                            </a>
                        </div>
                        <button type="button"
                            class="theme-toggle hidden lg:inline-flex"
                            data-theme-toggle
                            data-theme-label-dark="<?= htmlspecialchars(t('header.theme.label_dark', 'Skift til mørkt tema')) ?>"
                            data-theme-label-light="<?= htmlspecialchars(t('header.theme.label_light', 'Skift til lyst tema')) ?>"
                            data-theme-text-dark="<?= htmlspecialchars(t('header.theme.text_dark', 'Mørkt')) ?>"
                            data-theme-text-light="<?= htmlspecialchars(t('header.theme.text_light', 'Lyst')) ?>"
                            aria-pressed="false"
                            aria-label="<?= htmlspecialchars(t('header.theme.toggle_label', 'Skift farvetema')) ?>">
                            <span class="theme-toggle__icon" aria-hidden="true"></span>
                            <span class="theme-toggle__text hidden xl:inline"><?= t('header.theme.toggle_text', 'Tema') ?></span>
                        </button>
                        <button id="mobile-menu-button" class="header-burger lg:hidden" aria-controls="mobile-menu" aria-expanded="false" aria-label="<?= htmlspecialchars(t('header.mobile.open_menu')) ?>">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile menu overlay (hidden by default, JS toggles via class) -->
    <div id="mobile-menu-overlay" class="lg:hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[39] opacity-0 pointer-events-none transition-opacity duration-200" data-menu-overlay></div>

    <!-- Mobile menu drawer (compact design) -->
    <div id="mobile-menu" class="lg:hidden fixed top-0 right-0 bottom-0 w-64 max-w-[70vw] bg-gray-900/98 backdrop-blur-md z-40 shadow-2xl border-l border-gray-800/50 translate-x-full transition-transform duration-200" role="dialog" aria-modal="true" aria-labelledby="mobile-menu-heading" aria-hidden="true">
        <!-- Compact header -->
        <div class="flex justify-between items-center px-4 py-3 border-b border-gray-800/50">
            <span id="mobile-menu-heading" class="text-sm font-semibold text-gray-400 uppercase tracking-wider"><?= t('header.mobile.navigation') ?></span>
            <button id="mobile-menu-close" class="text-gray-400 hover:text-white p-1.5 -mr-1 rounded-lg hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-amber-400 transition-colors" aria-label="<?= htmlspecialchars(t('header.mobile.close_menu')) ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <!-- Navigation links (left-aligned, compact) -->
        <nav class="px-3 py-3 overflow-y-auto" style="max-height: calc(100vh - 180px);" aria-label="<?= htmlspecialchars(t('header.mobile.primary_navigation')) ?>">
            <ul class="flex flex-col space-y-0.5" role="list">
                <?php foreach ($nav_links as $index => $link): ?>
                    <li>
                        <a href="<?= $link['href'] ?>"
                            class="nav-link-mobile <?= $link['slug'] === $current_page ? 'is-active' : '' ?>"
                            data-mobile-nav-index="<?= $index ?>"
                            <?= aig_nav_aria($link['slug'], $current_page) ?>>
                            <?= htmlspecialchars($link['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Secondary links (smaller, muted) -->
            <div class="border-t border-gray-800/50 pt-2 mt-2" aria-label="<?= htmlspecialchars(t('header.mobile.secondary_navigation')) ?>">
                <ul class="flex flex-col space-y-0.5" role="list">
                    <?php foreach ($secondary_nav_links as $offset => $link): ?>
                        <li>
                            <a href="<?= $link['href'] ?>"
                                class="nav-link-mobile nav-link-mobile--secondary <?= $link['slug'] === $current_page ? 'is-active' : '' ?>"
                                data-mobile-secondary-index="<?= $offset ?>"
                                <?= aig_nav_aria($link['slug'], $current_page) ?>>
                                <?= htmlspecialchars($link['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
        <!-- Footer actions (compact) -->
        <div class="absolute bottom-0 left-0 right-0 px-3 py-3 border-t border-gray-800/50 bg-gray-900/95">
            <!-- Theme + Language row -->
            <div class="flex items-center justify-between gap-2 mb-2">
                <div class="flex items-center gap-1">
                    <a href="?lang=da"
                        class="language-switch language-switch--drawer <?= $current_language === 'da' ? 'is-active' : '' ?>"
                        aria-label="<?= htmlspecialchars(t('header.language.switch_da')) ?>"
                        <?= $current_language === 'da' ? 'aria-current="true"' : '' ?>>
                        <?= t('header.language.da') ?>
                    </a>
                    <a href="?lang=en"
                        class="language-switch language-switch--drawer <?= $current_language === 'en' ? 'is-active' : '' ?>"
                        aria-label="<?= htmlspecialchars(t('header.language.switch_en')) ?>"
                        <?= $current_language === 'en' ? 'aria-current="true"' : '' ?>>
                        <?= t('header.language.en') ?>
                    </a>
                </div>
                <button type="button"
                    class="theme-toggle theme-toggle--mobile"
                    data-theme-toggle
                    data-theme-label-dark="<?= htmlspecialchars(t('header.theme.label_dark', 'Skift til mørkt tema')) ?>"
                    data-theme-label-light="<?= htmlspecialchars(t('header.theme.label_light', 'Skift til lyst tema')) ?>"
                    aria-pressed="false"
                    aria-label="<?= htmlspecialchars(t('header.theme.toggle_label', 'Skift farvetema')) ?>">
                    <span class="theme-toggle__icon" aria-hidden="true"></span>
                </button>
            </div>
            <!-- Agent login CTA -->
            <a href="agent-login.php" class="header-cta header-cta--wide text-xs py-2">
                <?= t('header.cta.agent_login') ?>
            </a>
        </div>
    </div>

    <?php
    // Generate breadcrumbs (skip for home page)
    $breadcrumbs = aig_get_breadcrumbs($current_page, $page_title ?? '');
    if (!empty($breadcrumbs)):
        $breadcrumb_schema = aig_breadcrumb_structured_data($breadcrumbs);
    ?>
        <!-- Breadcrumb Navigation -->
        <nav aria-label="Breadcrumb" class="pt-16 pb-2">
            <div class="container mx-auto px-4">
                <ol class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
                    <?php foreach ($breadcrumbs as $index => $crumb): ?>
                        <li class="breadcrumb-item"
                            itemprop="itemListElement"
                            itemscope
                            itemtype="https://schema.org/ListItem"
                            <?= $crumb['current'] ? 'aria-current="page"' : '' ?>>
                            <?php if (!$crumb['current']): ?>
                                <a href="<?= htmlspecialchars($crumb['href']) ?>"
                                    itemprop="item">
                                    <span itemprop="name"><?= htmlspecialchars($crumb['label']) ?></span>
                                </a>
                                <meta itemprop="position" content="<?= $index + 1 ?>">
                                <span class="breadcrumb-separator" aria-hidden="true">/</span>
                            <?php else: ?>
                                <span itemprop="name"><?= htmlspecialchars($crumb['label']) ?></span>
                                <meta itemprop="position" content="<?= $index + 1 ?>">
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </nav>

        <!-- Breadcrumb Structured Data -->
        <script type="application/ld+json">
            <?= json_encode($breadcrumb_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
        </script>
    <?php endif; ?>
