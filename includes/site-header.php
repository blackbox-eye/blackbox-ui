<?php
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/graphene-config.php';
require_once __DIR__ . '/debug-killswitch.php';

$current_language = bbx_get_language();

// Handle language switching via query parameter (priority: query > cookie/localStorage > default EN)
if (isset($_GET['lang']) && in_array($_GET['lang'], BBX_ALLOWED_LANGS, true)) {
    $requested_lang = $_GET['lang'];
    bbx_set_language($requested_lang);
    $current_language = $requested_lang;

    // Redirect to remove query parameter from URL without breaking deep links
    $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
    header('Location: ' . $redirect_url);
    exit;
} else {
    $current_language = bbx_get_language();
}

// Load Graphene theme settings
$graphene_mode = bbx_graphene_get_mode();
$graphene_body_class = bbx_graphene_body_class();

$page_title = $page_title ?? 'Blackbox EYE™ - Intelligent Sikkerhed';
$current_page = $current_page ?? basename($_SERVER['PHP_SELF'], '.php');
$meta_description = $meta_description ?? 'Blackbox EYE™ leverer avancerede sikkerhedsløsninger, efterretning og AI-drevet overvågning til virksomheder med højrisiko-profil.';
$meta_keywords = $meta_keywords ?? 'Blackbox EYE, sikkerhed, AI, efterretning, cyber defense, overvågning';
$meta_author = $meta_author ?? BBX_SITE_NAME;
$meta_og_title = $meta_og_title ?? $page_title;
$meta_og_description = $meta_og_description ?? $meta_description;
// Centralized brand asset paths (approved 2025 set)
$bbx_brand_base = '/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full';
$bbx_brand_full_url = BBX_SITE_BASE_URL . $bbx_brand_base;
$bbx_logo_black = '/assets/new_logo_black_BBX.svg';
$bbx_logo_white = '/assets/new_logo_white_BBX.svg';
$meta_og_image = $meta_og_image ?? ($bbx_brand_base . '/BlackboxEYE_black_512x512.png');
$meta_og_type = $meta_og_type ?? 'website';
$canonical_url = $canonical_url ?? BBX_SITE_BASE_URL . ($current_page === 'index' || $current_page === 'home' ? '/' : '/' . $current_page . '.php');
$meta_robots = $meta_robots ?? 'index,follow';
// Twitter card type - use summary_large_image for better sharing appearance
$meta_twitter_card = $meta_twitter_card ?? 'summary_large_image';

$default_structured_data = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => BBX_SITE_NAME,
    'url' => BBX_SITE_BASE_URL,
    'logo' => $bbx_brand_full_url . '/BlackboxEYE_black_512x512.png',
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
            'gdi-login' => ['label' => t('header.cta.agent_login'), 'href' => 'gdi-login.php'],
            'agent-login' => ['label' => t('header.cta.agent_login'), 'href' => 'gdi-login.php'],
            'agent-access' => ['label' => t('agent_access.meta.breadcrumb', 'Agent Access'), 'href' => 'agent-access.php'],
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
<html lang="<?= htmlspecialchars($current_language) ?>" data-lang="<?= htmlspecialchars($current_language) ?>" class="scroll-smooth" data-theme="dark">
<head>
    <script>
        (function() {
            var storageKey = 'bbx-theme';
            var preferred = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
            var storedTheme = null;
            try {
                storedTheme = window.localStorage.getItem(storageKey);
            } catch (err) {
                // ignore
            }
            var theme = storedTheme === 'light' || storedTheme === 'dark' ? storedTheme : preferred;
            document.documentElement.dataset.theme = theme;
            document.documentElement.style.colorScheme = theme;
            if (document.body) {
                document.body.setAttribute('data-theme', theme);
            } else {
                document.addEventListener('DOMContentLoaded', function() {
                    if (document.body) {
                        document.body.setAttribute('data-theme', theme);
                    }
                });
            }
            window.__BBX_INITIAL_THEME__ = theme;

            // Language bootstrap for client-side resolver
            window.__BBX_INITIAL_LANG__ = '<?= htmlspecialchars($current_language) ?>';
            window.__BBX_ALLOWED_LANGS__ = <?= json_encode(BBX_ALLOWED_LANGS, JSON_UNESCAPED_SLASHES) ?>;
            
            // P1-E: FOUC Prevention - Add landing-gate class before paint
            // This prevents any flash of unstyled content on landing page
            // CRITICAL FIX #2: Gate only controls OPACITY, not visibility/scroll
            <?php if ($current_page === 'home' || $current_page === 'index'): ?>
            document.documentElement.classList.add('landing-gate');
            document.documentElement.classList.remove('landing-ready');

            var releaseLandingGate = function releaseLandingGate() {
                document.documentElement.classList.remove('landing-gate');
                document.documentElement.classList.add('landing-ready');
                window.dispatchEvent(new Event('bbx:landing-ready'));
            };

            // CRITICAL FIX: Release gate on DOMContentLoaded, don't wait for fonts
            // This ensures scroll works immediately while still preventing FOUC
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    requestAnimationFrame(releaseLandingGate);
                }, { once: true });
            } else {
                // Already loaded, release immediately
                requestAnimationFrame(releaseLandingGate);
            }
            <?php endif; ?>
        })();
    </script>
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
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
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">
    <meta property="og:site_name" content="Blackbox EYE™">
    <meta property="og:locale" content="<?= $current_language === 'da' ? 'da_DK' : 'en_US' ?>">

    <meta name="twitter:card" content="<?= htmlspecialchars($meta_twitter_card) ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($meta_og_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($meta_og_description) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($meta_og_image) ?>">
    <meta name="twitter:site" content="@blackboxeye">

    <?php
    /**
     * Sprint 9 Batch 4: Cache-proof asset versioning
     * Uses filemtime() for automatic cache invalidation when files change.
     * No manual version bumps required - deploys immediately bust cache.
     */
    
    /**
     * Get cache-busting version for an asset file.
     * Returns filemtime hash for automatic invalidation on file changes.
     * Falls back to static version if file doesn't exist.
     */
    function bbx_asset_version(string $path): string {
        static $asset_base = null;
        if ($asset_base === null) {
            $asset_base = __DIR__ . '/../assets';
        }
        $full_path = $asset_base . '/' . ltrim($path, '/');
        if (file_exists($full_path)) {
            return substr(md5((string)filemtime($full_path)), 0, 8);
        }
        return '1.6.20'; // Fallback version
    }
    
    // Legacy compat: keep $css_version for any remaining static refs
    $css_version = '1.6.20';
    ?>

    <link rel="icon" type="image/svg+xml" href="/assets/icon_box.svg?v=<?= $css_version ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png?v=<?= $css_version ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="/assets/favicon-192x192.png?v=<?= $css_version ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png?v=<?= $css_version ?>">
    <link rel="shortcut icon" href="/assets/favicon.ico?v=<?= $css_version ?>">

    <!-- Sprint 9: Critical CSS inlined for FCP/LCP (above-the-fold styles) -->
    <style id="critical-css"><?php include __DIR__ . '/../assets/css/critical.css'; ?></style>

    <!-- Sprint 9: Preload key fonts for LCP improvement -->
    <link rel="preload" href="https://fonts.gstatic.com/s/inter/v18/UcCO3FwrK3iLTeHuS_nVMrMxCp50SjIw2boKoduKmMEVuLyfAZ9hjp-Ek-_EeA.woff2" as="font" type="font/woff2" crossorigin>

    <!-- Non-critical CSS: async load via preload + onload swap (filemtime-based versioning) -->
    <link rel="preload" href="/assets/css/tailwind.full.css?v=<?= bbx_asset_version('css/tailwind.full.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/assets/css/tailwind.full.css?v=<?= bbx_asset_version('css/tailwind.full.css') ?>"></noscript>

    <link rel="preload" href="/assets/css/tokens.css?v=<?= bbx_asset_version('css/tokens.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/assets/css/tokens.css?v=<?= bbx_asset_version('css/tokens.css') ?>"></noscript>

    <link rel="preload" href="/assets/css/custom-ui.css?v=<?= bbx_asset_version('css/custom-ui.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/assets/css/custom-ui.css?v=<?= bbx_asset_version('css/custom-ui.css') ?>"></noscript>

    <link rel="preload" href="/assets/css/theme-overrides.css?v=<?= bbx_asset_version('css/theme-overrides.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/assets/css/theme-overrides.css?v=<?= bbx_asset_version('css/theme-overrides.css') ?>"></noscript>

    <!-- Deterministic CSS order: base -> marketing -> components -> glass (last) -->
    <?php
    // Admin pages need admin.css, marketing pages need marketing.css
    $admin_pages = ['gdi-login.php', 'agent-login.php', 'dashboard.php', 'admin.php', 'settings.php'];
    $current_script = basename($_SERVER['SCRIPT_NAME']);
    $is_admin_page = in_array($current_script, $admin_pages);
    // Use minified CSS in production (when DEBUG is not set or false)
    $use_minified = !defined('BBX_DEBUG_RECAPTCHA') || !BBX_DEBUG_RECAPTCHA;
    $css_suffix = $use_minified ? '.min.css' : '.css';
    $admin_css_path = 'css/admin' . $css_suffix;
    $marketing_css_path = 'css/marketing' . $css_suffix;

    if ($is_admin_page): ?>
        <link rel="preload" href="/assets/css/admin<?= $css_suffix ?>?v=<?= bbx_asset_version($admin_css_path) ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript><link rel="stylesheet" href="/assets/css/admin<?= $css_suffix ?>?v=<?= bbx_asset_version($admin_css_path) ?>"></noscript>
    <?php else: ?>
        <link rel="preload" href="/assets/css/marketing<?= $css_suffix ?>?v=<?= bbx_asset_version($marketing_css_path) ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript><link rel="stylesheet" href="/assets/css/marketing<?= $css_suffix ?>?v=<?= bbx_asset_version($marketing_css_path) ?>"></noscript>
    <?php endif; ?>

    <!-- Inline landing gate (pre-render) to prevent FOUC before async CSS swaps -->
    <!-- CRITICAL FIX #2: Do NOT block scroll - only control opacity for visual smoothness -->
    <style id="landing-gate-guard">
        body.landing-gate { opacity: 0; /* NO visibility:hidden - was blocking scroll! */ }
        body.landing-ready { opacity: 1; transition: opacity 0.15s ease; }
    </style>
    <script>
        (function() {
            function releaseLandingGate() {
                var body = document.body;
                if (!body || !body.classList.contains('landing-gate')) return;
                body.classList.add('landing-ready');
                body.classList.remove('landing-gate');
            }
            // CRITICAL FIX: Release gate IMMEDIATELY on DOMContentLoaded, don't wait for fonts
            // Fonts can load in background without blocking scroll
            var fireRelease = function() {
                requestAnimationFrame(function() { requestAnimationFrame(releaseLandingGate); });
            };
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', fireRelease, { once: true });
            } else {
                fireRelease();
            }
        })();
    </script>

    <!-- Sprint 6: Motion safety (global) + unified hero mobile -->
    <link rel="preload" href="/assets/css/components/motion-safe.css?v=<?= bbx_asset_version('css/components/motion-safe.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/assets/css/components/motion-safe.css?v=<?= bbx_asset_version('css/components/motion-safe.css') ?>"></noscript>

    <!-- Sprint 9: Async load remaining component CSS -->
    <link rel="preload" href="/assets/css/components/hero-mobile.css?v=<?= bbx_asset_version('css/components/hero-mobile.css') ?>" as="style" media="(max-width: 768px)" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/assets/css/components/hero-mobile.css?v=<?= bbx_asset_version('css/components/hero-mobile.css') ?>" media="(max-width: 768px)"></noscript>

    <!-- Sprint 9 P0: Mobile baseline - aggressive UX overrides -->
    <link rel="preload" href="/assets/css/components/mobile-baseline.css?v=<?= bbx_asset_version('css/components/mobile-baseline.css') ?>" as="style" media="(max-width: 768px)" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/assets/css/components/mobile-baseline.css?v=<?= bbx_asset_version('css/components/mobile-baseline.css') ?>" media="(max-width: 768px)"></noscript>

    <!-- Sprint 8: Mobile nav scaling + touch targets -->
    <link rel="preload" href="/assets/css/components/mobile-nav-scale.css?v=<?= bbx_asset_version('css/components/mobile-nav-scale.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/assets/css/components/mobile-nav-scale.css?v=<?= bbx_asset_version('css/components/mobile-nav-scale.css') ?>"></noscript>

    <!-- Sprint 10: Final mobile UI polish (sticky bar, drawer, console, login) -->
    <link rel="preload" href="/assets/css/components/mobile-final-polish.css?v=<?= bbx_asset_version('css/components/mobile-final-polish.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/assets/css/components/mobile-final-polish.css?v=<?= bbx_asset_version('css/components/mobile-final-polish.css') ?>"></noscript>

    <!-- P0 Landing Page Stability Fixes (sticky CTA, drawer, assistant, FOUC) -->
    <link rel="preload" href="/assets/css/components/landing-p0-fix.css?v=<?= bbx_asset_version('css/components/landing-p0-fix.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/assets/css/components/landing-p0-fix.css?v=<?= bbx_asset_version('css/components/landing-p0-fix.css') ?>"></noscript>

    <!-- Alphabot iOS Cross-Browser Fix (2025-12-25) - Decouple from cookie/CTA state -->
    <link rel="preload" href="/assets/css/components/alphabot-ios-fix.css?v=<?= bbx_asset_version('css/components/alphabot-ios-fix.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/assets/css/components/alphabot-ios-fix.css?v=<?= bbx_asset_version('css/components/alphabot-ios-fix.css') ?>"></noscript>

    <!-- P1/P2 Landing Page Polish (Priority Access, footer, FOUC gate) -->
    <link rel="preload" href="/assets/css/components/landing-p1-polish.css?v=<?= bbx_asset_version('css/components/landing-p1-polish.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/assets/css/components/landing-p1-polish.css?v=<?= bbx_asset_version('css/components/landing-p1-polish.css') ?>"></noscript>
    
    <!-- Sticky CTA Component (canonical styles - overrides marketing.css) -->
    <link rel="preload" href="/assets/css/components/sticky-cta.css?v=<?= bbx_asset_version('css/components/sticky-cta.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/assets/css/components/sticky-cta.css?v=<?= bbx_asset_version('css/components/sticky-cta.css') ?>"></noscript>

    <!-- Touch Targets Optimization - Ensures 44x44px minimum for all interactive elements -->
    <link rel="preload" href="/assets/css/components/touch-targets.css?v=<?= bbx_asset_version('css/components/touch-targets.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/assets/css/components/touch-targets.css?v=<?= bbx_asset_version('css/components/touch-targets.css') ?>"></noscript>

    <!-- Liquid Glass System - Cross-browser glass/blur effects (must load last to override) -->
    <link rel="preload" href="/assets/css/components/liquid-glass.css?v=<?= bbx_asset_version('css/components/liquid-glass.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/assets/css/components/liquid-glass.css?v=<?= bbx_asset_version('css/components/liquid-glass.css') ?>"></noscript>

    <script src="config.js?v=<?= bbx_asset_version('../config.js') ?>" defer></script>
    <?php if (BBX_RECAPTCHA_SITE_KEY !== ''): ?>
        <script src="https://www.google.com/recaptcha/api.js?render=<?= htmlspecialchars(BBX_RECAPTCHA_SITE_KEY) ?>" async defer></script>
    <?php endif; ?>
    <script>
        window.RECAPTCHA_SITE_KEY = "<?= htmlspecialchars(BBX_RECAPTCHA_SITE_KEY) ?>";
        window.BBX_SITE_BASE_URL = "<?= htmlspecialchars(BBX_SITE_BASE_URL) ?>";
        window.RECAPTCHA_DEBUG = <?= BBX_DEBUG_RECAPTCHA ? 'true' : 'false' ?>;
    </script>
    <!-- Fonts: preconnect already in early head, use media=print trick for async -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Chakra+Petch:wght@700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'" crossorigin="anonymous">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Chakra+Petch:wght@700&display=swap" rel="stylesheet" crossorigin="anonymous"></noscript>

    <script type="application/ld+json">
        <?= json_encode($default_structured_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>

    <!-- Inline styles removed; migrated to /assets/css/custom-ui.css -->
    <?php include __DIR__ . '/qa-bootstrap.php'; ?>

    <!-- P0 Debug: Kill-switch CSS for iOS scroll isolation -->
    <?= bbx_killswitch_debug_comment() ?>
    <?= bbx_killswitch_inline_css() ?>
</head>

<?php
// Determine if this page uses Graphene theme
$graphene_pages = ['index', 'home', 'about', 'products', 'cases', 'pricing', 'contact', 'demo', 'free-scan', 'faq', 'blog'];
$is_graphene_page = in_array($current_page, $graphene_pages, true);
$body_classes = ['antialiased'];
if ($is_graphene_page) {
    $body_classes[] = 'graphene-page';
    // Add graphene mode class (standard or strong)
    $body_classes[] = $graphene_body_class;
}

// Page slug class for scoped overrides
if (!empty($current_page)) {
    $body_classes[] = 'page-' . $current_page;
}

// Landing isolation gate (prevents FOUC/ghost UI on first paint)
if ($current_page === 'home' || $current_page === 'index') {
    $body_classes[] = 'landing-gate';
}
?>

<body class="<?= implode(' ', $body_classes) ?>" data-graphene-mode="<?= htmlspecialchars($graphene_mode) ?>" data-theme="dark" data-lang="<?= htmlspecialchars($current_language) ?>">

    <!-- Skip navigation for keyboard users (WCAG 2.1) - Hidden by default, visible on focus -->
    <a href="#main-content" class="skip-link"><?= t('common.skip_link') ?></a>

    <header id="main-header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
        <div class="container mx-auto px-4">
            <div class="header-shell">
                <div class="header-grid bbx-header">
                    <div class="header-brand bbx-header-left">
                        <a href="/" class="header-logo-link" aria-label="<?= htmlspecialchars(t('header.menu.home')) ?>">
                            <!-- White logo - visible on dark backgrounds (dark theme) -->
                            <img src="<?= htmlspecialchars($bbx_logo_white) ?>"
                                alt="BLACKBOX EYE™"
                                class="header-logo header-logo--white"
                                width="140"
                                height="32"
                                loading="eager">
                            <!-- Black logo - visible on light backgrounds (light theme) -->
                            <img src="<?= htmlspecialchars($bbx_logo_black) ?>"
                                alt="BLACKBOX EYE™"
                                class="header-logo header-logo--black"
                                width="140"
                                height="32"
                                loading="eager">
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
                                <div class="more-dropdown more-dropdown-menu" role="menu">
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
                    <div class="header-actions bbx-header-right">
                        <!-- CTAs removed from header - clean navigation only -->
                        <!-- Demo/Scan links available in MERE dropdown and mobile menu -->
                        <!-- Graphene mode toggle hidden - moved to settings/footer per P0.3 -->
                        <button
                            id="graphene-mode-toggle"
                            type="button"
                            class="graphene-toggle-btn inline-flex hidden"
                            data-current-mode="<?= htmlspecialchars($graphene_mode) ?>"
                            aria-pressed="<?= $graphene_mode === 'strong' ? 'true' : 'false' ?>"
                            aria-label="<?= htmlspecialchars(t('header.graphene.toggle_label', 'Skift Graphene-mode')) ?>">
                            <span class="graphene-toggle__dot" aria-hidden="true"></span>
                            <span class="graphene-toggle__text">
                                <?= $graphene_mode === 'strong'
                                    ? htmlspecialchars(t('header.graphene.strong', 'Strong'))
                                    : htmlspecialchars(t('header.graphene.standard', 'Standard')) ?>
                            </span>
                        </button>
                        <div class="language-switcher-wrapper flex items-center gap-0.5">
                            <a href="?lang=da" data-lang-target="da" class="language-switch <?= $current_language === 'da' ? 'is-active' : '' ?>" aria-label="<?= htmlspecialchars(t('header.language.switch_da')) ?>" <?= $current_language === 'da' ? 'aria-current="true"' : '' ?>>
                                <?= t('header.language.da') ?>
                            </a>
                            <a href="?lang=en" data-lang-target="en" class="language-switch <?= $current_language === 'en' ? 'is-active' : '' ?>" aria-label="<?= htmlspecialchars(t('header.language.switch_en')) ?>" <?= $current_language === 'en' ? 'aria-current="true"' : '' ?>>
                                <?= t('header.language.en') ?>
                            </a>
                        </div>
                        <!-- Graphene Theme Mode Toggle -->
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
                        </button>
                        <!-- Console Access Dropdown - Sprint 1.6 QA: New fold-out menu -->
                        <div class="console-access-dropdown" id="login-dropdown-container" data-dropdown>
                            <button type="button" 
                                id="login-dropdown-btn"
                                class="console-access-trigger header-cta header-cta--pill items-center gap-1.5"
                                aria-haspopup="menu"
                                aria-expanded="false"
                                aria-controls="login-dropdown-menu"
                                onclick="(function(e){e.preventDefault();e.stopPropagation();var btn=this;var isOpen=btn.getAttribute('aria-expanded')==='true';btn.setAttribute('aria-expanded',isOpen?'false':'true');btn.parentElement.classList.toggle('is-open',!isOpen)}).call(this,event)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span class="header-cta__label"><?= t('header.cta.login', 'Login') ?></span>
                                <svg class="console-chevron w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div class="console-access-menu" id="login-dropdown-menu" role="menu" aria-labelledby="login-dropdown-btn">
                                <div class="console-menu-header">
                                    <span><?= t('header.console.title', 'Vælg konsol') ?></span>
                                </div>
                                <a href="/agent-access.php#ccs" class="console-menu-item" role="menuitem" data-console-login="ccs">
                                    <div class="console-menu-item__icon console-menu-item__icon--ccs">CCS</div>
                                    <div class="console-menu-item__content">
                                        <span class="console-menu-item__title"><?= t('header.console.ccs', 'CCS Console') ?></span>
                                        <span class="console-menu-item__desc"><?= t('header.console.ccs_desc', 'Settlement Systems') ?></span>
                                    </div>
                                </a>
                                <a href="/agent-access.php#gdi" class="console-menu-item" role="menuitem" data-console-login="gdi">
                                    <div class="console-menu-item__icon console-menu-item__icon--gdi">GDI</div>
                                    <div class="console-menu-item__content">
                                        <span class="console-menu-item__title"><?= t('header.console.gdi', 'GDI Console') ?></span>
                                        <span class="console-menu-item__desc"><?= t('header.console.gdi_desc', 'Data Intelligence') ?></span>
                                    </div>
                                </a>
                                <a href="/agent-access.php#intel24" class="console-menu-item" role="menuitem" data-console-login="intel24">
                                    <div class="console-menu-item__icon console-menu-item__icon--intel24">I24</div>
                                    <div class="console-menu-item__content">
                                        <span class="console-menu-item__title"><?= t('header.console.intel24', 'Intel24 Console') ?></span>
                                        <span class="console-menu-item__desc"><?= t('header.console.intel24_desc', 'Intel24 Intelligence') ?></span>
                                    </div>
                                </a>
                            </div>
                        </div>
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
    <div id="mobile-menu-overlay" class="bbx-drawer-overlay lg:hidden transition-opacity duration-200" data-menu-overlay></div>

    <!-- Mobile menu drawer (compact design) -->
    <div id="mobile-menu" class="bbx-drawer-panel lg:hidden mobile-nav-drawer fixed top-0 right-0 bottom-0 w-64 max-w-[70vw] translate-x-full transition-transform duration-200" role="dialog" aria-modal="true" aria-labelledby="mobile-menu-heading" aria-hidden="true">
        <!-- Compact header -->
        <div class="mobile-drawer-header flex justify-between items-center px-4 py-3 border-b border-gray-800/50">
            <span id="mobile-menu-heading" class="text-sm font-semibold text-gray-400 uppercase tracking-wider"><?= t('header.mobile.navigation') ?></span>
            <button id="mobile-menu-close" class="text-gray-400 hover:text-white p-1.5 -mr-1 rounded-lg hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 transition-colors" style="--tw-ring-color: var(--primary-accent);" aria-label="<?= htmlspecialchars(t('header.mobile.close_menu')) ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <!-- Navigation links (left-aligned, compact) -->
        <nav class="px-3 py-3" aria-label="<?= htmlspecialchars(t('header.mobile.primary_navigation')) ?>">
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
        <!-- Footer actions (compact, sticky bottom via flexbox) -->
        <div class="px-3 py-3 border-t border-gray-800/50 bg-gray-900/95">
            <!-- Theme + Language row -->
            <div class="flex items-center justify-between gap-2 mb-2">
                <div class="flex items-center gap-1">
                    <a href="?lang=da"
                        data-lang-target="da"
                        class="language-switch language-switch--drawer <?= $current_language === 'da' ? 'is-active' : '' ?>"
                        aria-label="<?= htmlspecialchars(t('header.language.switch_da')) ?>"
                        <?= $current_language === 'da' ? 'aria-current="true"' : '' ?>>
                        <?= t('header.language.da') ?>
                    </a>
                    <a href="?lang=en"
                        data-lang-target="en"
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
            <!-- Console Access - Mobile version with direct login links (Sprint 8 fix) -->
            <div class="mobile-console-access mb-3">
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-2"><?= t('header.console.title', 'Vælg konsol') ?></p>
                <div class="flex gap-2">
                    <a href="/agent-access.php#ccs" class="mobile-console-btn flex-1" data-testid="mobile-login-ccs">
                        <span class="mobile-console-icon mobile-console-icon--ccs">CCS</span>
                        <span class="mobile-console-label"><?= t('header.console.ccs', 'CCS Console') ?></span>
                    </a>
                    <a href="/agent-access.php#gdi" class="mobile-console-btn flex-1" data-testid="mobile-login-gdi">
                        <span class="mobile-console-icon mobile-console-icon--gdi">GDI</span>
                        <span class="mobile-console-label"><?= t('header.console.gdi', 'GDI Console') ?></span>
                    </a>
                    <a href="/agent-access.php#intel24" class="mobile-console-btn flex-1" data-testid="mobile-login-intel24">
                        <span class="mobile-console-icon mobile-console-icon--intel24">I24</span>
                        <span class="mobile-console-label"><?= t('header.console.intel24', 'Intel24') ?></span>
                    </a>
                </div>
            </div>
            <div class="mobile-primary-ctas">
                <a href="demo.php"
                    class="header-cta header-cta--pill header-cta--primary header-cta--wide"
                    aria-label="<?= htmlspecialchars(t('header.cta.book_demo_aria', t('header.cta.book_demo', t('header.menu.demo')))) ?>">
                    <span class="header-cta__label"><?= t('header.cta.book_demo', t('header.menu.demo')) ?></span>
                </a>
                <a href="free-scan.php"
                    class="header-cta header-cta--pill header-cta--secondary header-cta--wide"
                    aria-label="<?= htmlspecialchars(t('header.cta.free_scan_aria', t('header.cta.free_scan', t('header.menu.free_scan')))) ?>">
                    <span class="header-cta__label"><?= t('header.cta.free_scan', t('header.menu.free_scan')) ?></span>
                </a>
            </div>
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

