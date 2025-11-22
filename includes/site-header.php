<?php
require_once __DIR__ . '/env.php';

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
            ? 'text-white font-semibold border-b border-amber-400'
            : 'text-gray-400 hover:text-white';
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
            'index' => ['label' => 'Hjem', 'href' => '/'],
            'home' => ['label' => 'Hjem', 'href' => '/'],
            'about' => ['label' => 'Om Os', 'href' => 'about.php'],
            'products' => ['label' => 'Produkter', 'href' => 'products.php'],
            'cases' => ['label' => 'Kundecases', 'href' => 'cases.php'],
            'pricing' => ['label' => 'Priser', 'href' => 'pricing.php'],
            'contact' => ['label' => 'Kontakt', 'href' => 'contact.php'],
            'agent-login' => ['label' => 'Agent Login', 'href' => 'agent-login.php'],
            'dashboard' => ['label' => 'Dashboard', 'href' => 'dashboard.php'],
            'admin' => ['label' => 'Admin', 'href' => 'admin.php'],
            'settings' => ['label' => 'Indstillinger', 'href' => 'settings.php'],
        ];

        $breadcrumbs = [
            ['label' => 'Hjem', 'href' => '/', 'current' => false]
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
    ['slug' => 'about', 'label' => 'Om Os', 'href' => 'about.php'],
    ['slug' => 'products', 'label' => 'Produkter', 'href' => 'products.php'],
    ['slug' => 'cases', 'label' => 'Kundecases', 'href' => 'cases.php'],
    ['slug' => 'pricing', 'label' => 'Priser', 'href' => 'pricing.php'],
    ['slug' => 'contact', 'label' => 'Kontakt', 'href' => 'contact.php'],
];
?>
<!DOCTYPE html>
<html lang="da" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($meta_keywords) ?>">
    <meta name="author" content="<?= htmlspecialchars($meta_author) ?>">
    <meta name="robots" content="<?= htmlspecialchars($meta_robots) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical_url) ?>">

    <meta property="og:title" content="<?= htmlspecialchars($meta_og_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($meta_og_description) ?>">
    <meta property="og:type" content="<?= htmlspecialchars($meta_og_type) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical_url) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($meta_og_image) ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($meta_og_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($meta_og_description) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($meta_og_image) ?>">

    <script src="https://cdn.tailwindcss.com"></script>
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
            padding: 0.75rem 0;
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
                padding: 0.5rem 0;
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

        /* Sticky CTA Button */
        .sticky-cta {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 45;
            background: var(--primary-accent);
            color: #000;
            padding: 1rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
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

        @media (max-width: 768px) {
            .sticky-cta {
                bottom: 1rem;
                right: 1rem;
                padding: 0.875rem 1.25rem;
                font-size: 0.875rem;
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
    <a href="#main-content" class="skip-link">Spring til hovedindhold</a>

    <header id="main-header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-20">
                <div class="glitch-logo flex-shrink-0" aria-label="Blackbox EYE">
                    Blackbox EYE&trade;
                    <span aria-hidden="true">Blackbox EYE&trade;</span>
                    <span aria-hidden="true">Blackbox EYE&trade;</span>
                </div>
                <div class="hidden md:flex items-center space-x-4 lg:space-x-8">
                    <nav class="flex items-center space-x-4 lg:space-x-8">
                        <?php foreach ($nav_links as $link): ?>
                            <a href="<?= $link['href'] ?>"
                                class="nav-link <?= aig_nav_class($link['slug'], $current_page) ?> transition-colors"
                                <?= aig_nav_aria($link['slug'], $current_page) ?>>
                                <?= htmlspecialchars($link['label']) ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                    <a href="agent-login.php" class="hidden lg:inline-block border border-amber-400 text-amber-400 py-2 px-6 rounded-lg hover:bg-amber-400 hover:text-black transition-all font-semibold">
                        Agent Login
                    </a>
                </div>
                <button id="mobile-menu-button" class="md:hidden text-white p-2 -mr-2" aria-controls="mobile-menu" aria-expanded="false" aria-label="Åbn navigation menu">
                    <span class="sr-only">Åbn menu</span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile menu overlay -->
    <div id="mobile-menu-overlay" class="md:hidden fixed inset-0 bg-black/70 z-39" aria-hidden="true"></div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="md:hidden fixed top-0 right-0 bottom-0 w-4/5 max-w-sm glass-effect z-40 p-8 shadow-2xl">
        <div class="flex justify-between items-center mb-8">
            <span class="text-lg font-semibold text-white">Navigation</span>
            <button id="mobile-menu-close" class="text-white p-2 -mr-2" aria-label="Luk navigation menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <nav class="flex flex-col space-y-8 text-center mt-8">
            <?php foreach ($nav_links as $link): ?>
                <a href="<?= $link['href'] ?>"
                    class="nav-link-mobile text-2xl <?= $link['slug'] === $current_page ? 'text-white font-semibold' : 'text-gray-300' ?>"
                    <?= aig_nav_aria($link['slug'], $current_page) ?>>
                    <?= htmlspecialchars($link['label']) ?>
                </a>
            <?php endforeach; ?>
            <a href="agent-login.php" class="mt-8 inline-block border border-amber-400 text-amber-400 py-3 px-8 rounded-lg text-xl font-semibold">
                Agent Login
            </a>
        </nav>
    </div>

    <?php
    // Generate breadcrumbs (skip for home page)
    $breadcrumbs = aig_get_breadcrumbs($current_page, $page_title ?? '');
    if (!empty($breadcrumbs)):
        $breadcrumb_schema = aig_breadcrumb_structured_data($breadcrumbs);
    ?>
        <!-- Breadcrumb Navigation -->
        <nav aria-label="Breadcrumb" class="pt-20">
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
