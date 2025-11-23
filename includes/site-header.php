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

$nav_links = [
    ['slug' => 'about', 'label' => t('header.menu.about'), 'href' => 'about.php'],
    ['slug' => 'products', 'label' => t('header.menu.products'), 'href' => 'products.php'],
    ['slug' => 'cases', 'label' => t('header.menu.cases'), 'href' => 'cases.php'],
    ['slug' => 'blog', 'label' => t('blog.title'), 'href' => 'blog.php'],
    ['slug' => 'faq', 'label' => t('faq.hero.title'), 'href' => 'faq.php'],
    ['slug' => 'pricing', 'label' => t('header.menu.pricing'), 'href' => 'pricing.php'],
    ['slug' => 'contact', 'label' => t('header.menu.contact'), 'href' => 'contact.php'],
];

$alphabot_enabled_pages = ['home', 'index', 'about', 'products', 'cases', 'pricing', 'contact'];
$show_alphabot = $show_alphabot ?? in_array($current_page, $alphabot_enabled_pages, true);
if (!empty($disable_alphabot)) {
    $show_alphabot = false;
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_language) ?>" class="scroll-smooth" style="color-scheme: light;">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($meta_keywords) ?>">
    <meta name="author" content="<?= htmlspecialchars($meta_author) ?>">
    <meta name="robots" content="<?= htmlspecialchars($meta_robots) ?>">

    <!-- Performance Optimization: Resource Hints -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
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

    <!-- Tailwind CSS Production CDN (must load via script) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <noscript>
        <style>
            body {
                background-color: #101419;
                color: #e5e7eb;
            }
            .noscript-warning {
                margin: 2rem;
                padding: 1.5rem;
                border: 1px solid #fbbf24;
                background: rgba(17, 24, 39, 0.9);
                border-radius: 0.75rem;
                font-size: 1rem;
                line-height: 1.5;
            }
        </style>
        <div class="noscript-warning">
            Tailwind CSS kræver JavaScript for at indlæse designet. Aktivér venligst JavaScript eller kontakt supporten.
        </div>
    </noscript>

    <!-- Custom styles - must load AFTER Tailwind to override -->
    <link rel="stylesheet" href="/style.css">

    <script src="config.js"></script>
    <?php if (BBX_RECAPTCHA_SITE_KEY !== ''): ?>
        <script src="https://www.google.com/recaptcha/api.js?render=<?= htmlspecialchars(BBX_RECAPTCHA_SITE_KEY) ?>" async defer></script>
    <?php endif; ?>
    <script>
        window.RECAPTCHA_SITE_KEY = "<?= htmlspecialchars(BBX_RECAPTCHA_SITE_KEY) ?>";
        window.BBX_SITE_BASE_URL = "<?= htmlspecialchars(BBX_SITE_BASE_URL) ?>";
        window.RECAPTCHA_DEBUG = <?= BBX_DEBUG_RECAPTCHA ? 'true' : 'false' ?>;
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=Chakra+Petch:wght@700&display=swap" rel="stylesheet">

    <script type="application/ld+json">
        <?= json_encode($default_structured_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>

    <style>
        /* Navigation Scroll Effect */
        #main-header {
            background-color: transparent;
            backdrop-filter: none;
        }

        #main-header.scrolled {
            background-color: rgba(16, 20, 25, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }

        :root {
            --bg-color: #101419;
            --primary-accent: #FFC700;
            --glitch-dark-green: #003d00;
            --glitch-fire: #ffac00;
            --text-high-emphasis: #EAEAEA;
            --text-medium-emphasis: #B0B8C6;
            /* Improved contrast 4.52:1 */
            --glass-border: rgba(255, 255, 255, 0.1);
            --glass-bg: rgba(22, 28, 39, 0.6);
            --digital-rain-color: #008000;
        }

        /* Hide reCAPTCHA badge completely */
        .grecaptcha-badge {
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-high-emphasis);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        body::-webkit-scrollbar {
            display: none;
        }

        html {
            scrollbar-width: none;
        }

        body {
            -ms-overflow-style: none;
        }

        *::-webkit-scrollbar {
            display: none;
        }

        * {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        @media (max-width: 768px) {
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }

        .glass-effect {
            background-color: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
        }

        .hero-gradient-text {
            background: linear-gradient(90deg, var(--primary-accent), #FFFFFF);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section-fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .section-fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.2);
            border-left-color: var(--primary-accent);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        :focus-visible {
            outline: 2px solid var(--primary-accent);
            outline-offset: 2px;
            border-radius: 2px;
        }

        /* Skip to main content link for keyboard navigation */
        .skip-link {
            position: absolute;
            top: -100px;
            left: 0;
            background: var(--primary-accent);
            color: #000;
            padding: 8px 16px;
            text-decoration: none;
            font-weight: bold;
            z-index: 100;
            transition: top 0.2s ease;
        }

        .skip-link:focus {
            top: 0;
        }

        /* Breadcrumb navigation */
        .breadcrumb {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            font-size: 0.875rem;
            padding: 0.5rem 0;
        }

        .breadcrumb-item {
            display: flex;
            align-items: center;
            color: var(--text-medium-emphasis);
        }

        .breadcrumb-item a {
            color: var(--text-medium-emphasis);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .breadcrumb-item a:hover {
            color: var(--text-high-emphasis);
        }

        .breadcrumb-item a:focus-visible {
            outline: 2px solid var(--primary-accent);
            outline-offset: 2px;
            border-radius: 2px;
        }

        .breadcrumb-separator {
            color: #6B7280;
            margin: 0 0.25rem;
            user-select: none;
        }

        .breadcrumb-item[aria-current="page"] {
            color: var(--text-high-emphasis);
            font-weight: 500;
        }

        @media (max-width: 640px) {
            .breadcrumb {
                font-size: 0.75rem;
                padding: 0.375rem 0;
            }
        }

        /* Mobile menu improvements */
        #mobile-menu {
            transform: translateX(100%);
            transition: transform 0.3s ease-out;
            will-change: transform;
        }

        #mobile-menu.active {
            transform: translateX(0);
        }

        #mobile-menu-overlay {
            opacity: 0;
            transition: opacity 0.3s ease-out;
            pointer-events: none;
        }

        #mobile-menu-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        @media (prefers-reduced-motion: reduce) {

            #mobile-menu,
            #mobile-menu-overlay {
                transition: none !important;
            }
        }

        /* AI Loading States & Skeleton Screens */
        .ai-loading-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            min-height: 200px;
        }

        .ai-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid rgba(255, 199, 0, 0.2);
            border-top-color: var(--primary-accent);
            border-radius: 50%;
            animation: ai-spin 0.8s linear infinite;
        }

        @keyframes ai-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .ai-loading-text {
            margin-top: 1rem;
            color: var(--text-medium-emphasis);
            font-size: 0.875rem;
            text-align: center;
        }

        .skeleton {
            background: linear-gradient(90deg,
                    rgba(255, 255, 255, 0.05) 25%,
                    rgba(255, 255, 255, 0.1) 50%,
                    rgba(255, 255, 255, 0.05) 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s ease-in-out infinite;
            border-radius: 4px;
        }

        @keyframes skeleton-loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .skeleton-line {
            height: 1rem;
            margin-bottom: 0.75rem;
        }

        .skeleton-line.short {
            width: 60%;
        }

        .skeleton-heading {
            height: 1.5rem;
            width: 80%;
            margin-bottom: 1rem;
        }

        @media (prefers-reduced-motion: reduce) {

            .ai-spinner,
            .skeleton {
                animation: none !important;
            }

            .ai-spinner {
                opacity: 0.5;
            }
        }

        /* Command Rail (AlphaBot + CTA) */
        .bbx-command-rail {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 45;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: flex-end;
        }

        .bbx-command-rail--cta-only {
            gap: 0;
        }

        /* Sticky CTA Button */
        .sticky-cta {
            position: relative;
            background: var(--primary-accent);
            color: #000;
            padding: 1rem 1.5rem;
            border-radius: 3rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3), 0 0 20px rgba(255, 199, 0, 0.4);
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.3s ease;
            opacity: 0;
            transform: translateY(100px);
            pointer-events: none;
        }

        .sticky-cta.visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .sticky-cta:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4), 0 0 30px rgba(255, 199, 0, 0.6);
        }

        .sticky-cta:active {
            transform: translateY(0) scale(0.98);
        }

        @media (max-width: 1024px) {
            .bbx-command-rail {
                right: 1rem;
                bottom: 1rem;
            }
        }

        @media (max-width: 768px) {
            .bbx-command-rail {
                position: static;
                width: 100%;
                bottom: auto;
                right: auto;
                gap: 0;
            }

            .sticky-cta {
                position: fixed;
                left: 1rem;
                right: 1rem;
                bottom: 1rem;
                width: auto;
                justify-content: center;
                font-size: 0.95rem;
                border-radius: 1rem;
            }
        }

        /* AlphaBot Widget */
        .alphabot-widget {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            width: auto;
            min-width: 15rem;
            z-index: 46;
        }

        .alphabot-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(14, 20, 29, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--text-high-emphasis);
            padding: 0.65rem 1.25rem;
            border-radius: 999px;
            font-weight: 600;
            letter-spacing: 0.02em;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .alphabot-toggle:focus-visible {
            outline: 2px solid var(--primary-accent);
            outline-offset: 3px;
        }

        .alphabot-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 35px rgba(0, 0, 0, 0.45);
        }

        .alphabot-widget.open .alphabot-toggle {
            box-shadow: 0 0 25px rgba(255, 199, 0, 0.35);
        }

        .alphabot-status-dot {
            width: 0.65rem;
            height: 0.65rem;
            border-radius: 999px;
            background: #22c55e;
            box-shadow: 0 0 10px rgba(34, 197, 94, 0.6);
        }

        .alphabot-panel {
            position: absolute;
            bottom: 100%;
            right: 0;
            margin-bottom: 1rem;
            width: min(22rem, calc(100vw - 4rem));
            max-height: 60vh;
            background: rgba(8, 13, 20, 0.98);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.25rem;
            padding: 1.25rem;
            box-shadow: 0 25px 55px rgba(0, 0, 0, 0.6);
            opacity: 0;
            transform: translate3d(0, 10px, 0);
            pointer-events: none;
            transition: opacity 0.25s ease, transform 0.25s ease;
            display: flex;
            flex-direction: column;
        }

        .alphabot-widget.open .alphabot-panel {
            opacity: 1;
            transform: translate3d(0, 0, 0);
            pointer-events: auto;
        }

        .alphabot-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .alphabot-panel-title {
            font-weight: 700;
            font-size: 1rem;
        }

        .alphabot-panel-subtitle {
            font-size: 0.85rem;
            color: var(--text-medium-emphasis);
        }

        .alphabot-close-btn {
            border: none;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-high-emphasis);
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }

        .alphabot-close-btn:hover,
        .alphabot-close-btn:focus-visible {
            background: rgba(255, 255, 255, 0.12);
        }

        .alphabot-messages {
            flex: 1;
            min-height: 0;
            max-height: 18rem;
            overflow-y: auto;
            padding-right: 0.35rem;
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .alphabot-messages::-webkit-scrollbar {
            width: 4px;
        }

        .alphabot-messages::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 999px;
        }

        .chat-message {
            display: flex;
            width: 100%;
        }

        .chat-message .message-text {
            padding: 0.75rem 1rem;
            border-radius: 0.85rem;
            font-size: 0.9rem;
            line-height: 1.5;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.04);
            width: 100%;
        }

        .chat-message.bot .message-text {
            background: rgba(255, 255, 255, 0.04);
        }

        .chat-message.user {
            justify-content: flex-end;
        }

        .chat-message.user .message-text {
            background: rgba(255, 199, 0, 0.15);
            color: var(--text-high-emphasis);
        }

        .alphabot-input-group {
            display: flex;
            gap: 0.5rem;
            align-items: flex-end;
        }

        #alphabot-input {
            flex: 1;
            background: rgba(13, 18, 27, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            padding: 0.75rem 1rem;
            resize: none;
            min-height: 3rem;
            color: var(--text-high-emphasis);
            font-size: 0.9rem;
        }

        #alphabot-input:focus-visible {
            outline: 2px solid var(--primary-accent);
            outline-offset: 2px;
        }

        .alphabot-send-btn {
            background: var(--primary-accent);
            color: #000;
            border: none;
            border-radius: 999px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-width: 5.5rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .alphabot-send-btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            box-shadow: none;
        }

        .alphabot-send-btn:not(:disabled):hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.35);
        }

        .alphabot-send-btn .ai-spinner {
            width: 20px;
            height: 20px;
            border-width: 3px;
        }

        .alphabot-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
            z-index: 44;
            display: none;
        }

        .alphabot-overlay.visible {
            opacity: 1;
            pointer-events: auto;
        }

        @media (max-width: 1024px) {
            .alphabot-panel {
                width: min(20rem, calc(100vw - 4.5rem));
            }
        }

        @media (max-width: 768px) {
            body.alphabot-locked {
                overflow: hidden;
            }

            .bbx-command-rail {
                padding-bottom: calc(env(safe-area-inset-bottom, 1rem) + 4.5rem);
            }

            .alphabot-overlay {
                display: block;
            }

            .alphabot-widget {
                position: fixed;
                bottom: calc(5.25rem + env(safe-area-inset-bottom, 1rem));
                right: 1rem;
                width: auto;
                min-width: 0;
            }

            .alphabot-toggle {
                padding: 0.85rem;
                border-radius: 999px;
                box-shadow: 0 12px 30px rgba(0, 0, 0, 0.45);
            }

            .alphabot-toggle .alphabot-label {
                display: none;
            }

            .alphabot-panel {
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                width: 100%;
                border-radius: 1.5rem 1.5rem 0 0;
                max-height: 65vh;
                transform: translateY(110%);
                padding: 1.25rem clamp(1rem, 4vw, 1.5rem) calc(1.25rem + env(safe-area-inset-bottom, 0));
                box-shadow: 0 -20px 55px rgba(0, 0, 0, 0.65);
                border-width: 1px 0 0 0;
                border-top: 2px solid rgba(255, 199, 0, 0.25);
            }

            .alphabot-widget.open .alphabot-panel {
                transform: translateY(0);
            }

            .alphabot-messages {
                max-height: calc(45vh - 6rem);
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .alphabot-toggle,
            .alphabot-panel,
            .alphabot-send-btn {
                transition: none !important;
            }

            .alphabot-widget.open .alphabot-panel,
            .alphabot-panel {
                transform: none !important;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .sticky-cta {
                transition: opacity 0.1s ease !important;
                transform: none !important;
            }

            .sticky-cta.visible {
                transform: none !important;
            }

            .sticky-cta:hover {
                transform: none !important;
            }
        }

        /* Respect user's motion preferences */
        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }

            .section-fade-in {
                opacity: 1 !important;
                transform: none !important;
            }

            .glitch-logo {
                animation: none !important;
            }

            .glitch-logo span {
                animation: none !important;
            }

            #hero-canvas {
                display: none;
            }
        }

        .glitch-logo {
            font-family: 'Chakra Petch', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            position: relative;
            font-size: 1.65rem;
            line-height: 1.65rem;
            color: var(--text-high-emphasis);
            text-shadow: 0.05em 0 0 var(--glitch-dark-green), -0.025em -0.05em 0 var(--glitch-fire), 0.025em 0.05em 0 var(--glitch-dark-green);
            animation: glitch 500ms infinite;
        }

        .glitch-logo span {
            position: absolute;
            top: 0;
            left: 0;
        }

        .glitch-logo span:first-child {
            animation: glitch 650ms infinite;
            clip-path: polygon(0 0, 100% 0, 100% 45%, 0 45%);
            transform: translate(-0.025em, -0.0125em);
            opacity: 0.8;
        }

        .glitch-logo span:last-child {
            animation: glitch 375ms infinite;
            clip-path: polygon(0 80%, 100% 20%, 100% 100%, 0 100%);
            transform: translate(0.0125em, 0.025em);
            opacity: 0.8;
        }

        @keyframes glitch {

            0%,
            14% {
                text-shadow: 0.05em 0 0 var(--glitch-dark-green), -0.025em -0.05em 0 var(--glitch-fire), 0.025em 0.05em 0 var(--glitch-dark-green);
            }

            15%,
            49% {
                text-shadow: -0.05em -0.025em 0 var(--glitch-dark-green), 0.025em 0.025em 0 var(--glitch-fire), -0.05em -0.05em 0 var(--glitch-dark-green);
            }

            50%,
            99% {
                text-shadow: 0.025em 0.05em 0 var(--glitch-dark-green), 0.05em 0 0 var(--glitch-fire), 0 -0.05em 0 var(--glitch-dark-green);
            }

            100% {
                text-shadow: -0.025em 0 0 var(--glitch-dark-green), -0.025em -0.025em 0 var(--glitch-fire), -0.025em -0.05em 0 var(--glitch-dark-green);
            }
        }
    </style>
</head>

<body class="antialiased">

    <!-- Skip navigation for keyboard users (WCAG 2.1) -->
    <a href="#main-content" class="skip-link"><?= t('common.skip_link') ?></a>

    <header id="main-header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-20 gap-6 md:gap-8 lg:gap-12 xl:gap-16">
                <div class="glitch-logo flex-shrink-0" aria-label="Blackbox EYE">
                    Blackbox EYE&trade;
                    <span aria-hidden="true">Blackbox EYE&trade;</span>
                    <span aria-hidden="true">Blackbox EYE&trade;</span>
                </div>
                <div class="hidden md:flex items-center gap-3 lg:gap-8 xl:gap-10 flex-nowrap">
                    <nav class="flex items-center gap-3 lg:gap-8 xl:gap-10 flex-nowrap">
                        <?php foreach ($nav_links as $link): ?>
                            <a href="<?= $link['href'] ?>"
                                class="nav-link <?= aig_nav_class($link['slug'], $current_page) ?> whitespace-nowrap"
                                <?= aig_nav_aria($link['slug'], $current_page) ?>>
                                <?= htmlspecialchars($link['label']) ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                    <a href="agent-login.php" class="md:inline-block border border-amber-400 text-amber-400 py-2 px-4 lg:px-6 rounded-lg hover:bg-amber-400 hover:text-black transition-all font-semibold whitespace-nowrap flex-shrink-0 text-sm lg:text-base">
                        <?= t('header.cta.agent_login') ?>
                    </a>
                    <!-- Language Switcher -->
                    <div class="flex items-center gap-1 border border-gray-600 rounded-lg p-1 flex-shrink-0">
                        <a href="?lang=da"
                            class="language-switch px-3 py-1.5 rounded text-xs lg:text-sm font-medium transition-all <?= $current_language === 'da' ? 'bg-amber-400 text-black' : 'text-gray-400 hover:text-white' ?>"
                            aria-label="<?= htmlspecialchars(t('header.language.switch_da')) ?>"
                            <?= $current_language === 'da' ? 'aria-current="true"' : '' ?>>
                            <?= t('header.language.da') ?>
                        </a>
                        <a href="?lang=en"
                            class="language-switch px-3 py-1.5 rounded text-xs lg:text-sm font-medium transition-all <?= $current_language === 'en' ? 'bg-amber-400 text-black' : 'text-gray-400 hover:text-white' ?>"
                            aria-label="<?= htmlspecialchars(t('header.language.switch_en')) ?>"
                            <?= $current_language === 'en' ? 'aria-current="true"' : '' ?>>
                            <?= t('header.language.en') ?>
                        </a>
                    </div>
                </div>
                <button id="mobile-menu-button"
                    class="md:hidden text-white p-2 -mr-2 bg-transparent border-none focus:outline-none focus:ring-2 focus:ring-amber-400"
                    aria-controls="mobile-menu"
                    aria-expanded="false"
                    aria-label="<?= htmlspecialchars(t('header.mobile.open_menu')) ?>">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile menu overlay -->
    <div id="mobile-menu-overlay" class="md:hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-39" aria-hidden="true"></div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="md:hidden fixed top-0 right-0 bottom-0 w-4/5 max-w-sm bg-gray-900/95 backdrop-blur-md z-40 p-8 shadow-2xl border-l border-gray-800">
        <div class="flex justify-between items-center mb-8">
            <span class="text-lg font-semibold text-white"><?= t('header.mobile.navigation') ?></span>
            <button id="mobile-menu-close" class="text-white p-2 -mr-2" aria-label="<?= htmlspecialchars(t('header.mobile.close_menu')) ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <nav class="flex flex-col space-y-8 text-center mt-8">
            <?php foreach ($nav_links as $link): ?>
                <a href="<?= $link['href'] ?>"
                    class="nav-link-mobile <?= $link['slug'] === $current_page ? 'text-white font-semibold' : '' ?>"
                    <?= aig_nav_aria($link['slug'], $current_page) ?>>
                    <?= htmlspecialchars($link['label']) ?>
                </a>
            <?php endforeach; ?>
            <a href="agent-login.php" class="mt-8 inline-block border border-amber-400 text-amber-400 py-3 px-8 rounded-lg text-xl font-semibold">
                <?= t('header.cta.agent_login') ?>
            </a>
            <div class="flex items-center justify-center gap-3 pt-6">
                <a href="?lang=da"
                    class="language-switch px-4 py-2 rounded-lg text-base font-semibold transition-all <?= $current_language === 'da' ? 'bg-amber-400 text-black' : 'text-gray-300 border border-gray-600' ?>"
                    aria-label="<?= htmlspecialchars(t('header.language.switch_da')) ?>"
                    <?= $current_language === 'da' ? 'aria-current="true"' : '' ?>>
                    <?= t('header.language.da') ?>
                </a>
                <a href="?lang=en"
                    class="language-switch px-4 py-2 rounded-lg text-base font-semibold transition-all <?= $current_language === 'en' ? 'bg-amber-400 text-black' : 'text-gray-300 border border-gray-600' ?>"
                    aria-label="<?= htmlspecialchars(t('header.language.switch_en')) ?>"
                    <?= $current_language === 'en' ? 'aria-current="true"' : '' ?>>
                    <?= t('header.language.en') ?>
                </a>
            </div>
        </nav>
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
