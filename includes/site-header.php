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
            --text-medium-emphasis: #9CA3AF;
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
                <button id="mobile-menu-button" class="md:hidden text-white p-2 -mr-2" aria-controls="mobile-menu" aria-expanded="false">
                    <span class="sr-only">Åbn menu</span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <div id="mobile-menu" class="hidden md:hidden fixed top-20 left-0 right-0 bottom-0 glass-effect z-40 p-8">
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
