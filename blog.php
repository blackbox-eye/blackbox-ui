<?php

/**
 * Blog Listing Page - Blackbox EYE
 * Sprint 5: Complete Blog Redesign - Modern Cyber News Hub
 *
 * @version 2.0
 * @date 2025-11-26
 */

require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/blog-functions.php';

// Gracefully handle missing db.php (fail-open pattern)
if (file_exists(__DIR__ . '/db.php')) {
  require_once __DIR__ . '/db.php';
}

$current_page = 'blog';
$page_title = t('blog.meta.title');
$meta_description = t('blog.meta.description');

// Pagination
$posts_per_page = 9;
$current_page_num = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$category_filter = isset($_GET['category']) ? $_GET['category'] : null;
$region_filter = isset($_GET['region']) ? $_GET['region'] : null;
$tag_filter = isset($_GET['tag']) ? $_GET['tag'] : null;

// Get posts
$blog_data_error = false;
$blog_error_message = '';
$posts = [];
$categories = [];
$total_posts = 0;
$total_pages = 0;
$data_source = 'none'; // Track data source: 'database', 'json', or 'none'

// Check if database is available before attempting queries
$db_available = defined('BBX_DB_CONNECTED') && BBX_DB_CONNECTED === true;

try {
  if ($db_available) {
    // Try database first (legacy support)
    $posts = bbx_get_blog_posts($current_page_num, $posts_per_page, $category_filter);
    $total_posts = bbx_get_blog_posts_count($category_filter);
    $total_pages = $total_posts > 0 ? (int) ceil($total_posts / $posts_per_page) : 0;
    $categories = bbx_get_blog_categories();
    $data_source = 'database';
    
    // Ensure we have a good set of categories - add fallbacks if database has few
    $default_categories = [
      'Cybersecurity',
      'Ransomware',
      'AI & Machine Learning',
      'Threat Intelligence',
      'Compliance & GDPR',
      'Cloud Security'
    ];

    // Merge existing categories with defaults, keeping unique values
    if (count($categories) < 3) {
      $categories = array_unique(array_merge($categories, $default_categories));
    }
  } else {
    // Fall back to JSON data (new intel engine)
    error_log('[Blog] Database not available - using JSON data source');
    
    // Use tag filter instead of category for JSON posts
    $filter_param = $category_filter ?? $tag_filter;
    
    $posts = bbx_get_blog_posts_from_json($current_page_num, $posts_per_page, $region_filter, $filter_param);
    $total_posts = bbx_get_blog_posts_json_count($region_filter, $filter_param);
    $total_pages = $total_posts > 0 ? (int) ceil($total_posts / $posts_per_page) : 0;
    $categories = bbx_get_blog_tags_from_json(); // Use tags as categories
    $data_source = 'json';
    
    // Transform JSON posts to match expected structure
    $posts = array_map(function($post) {
      return [
        'id' => $post['id'] ?? uniqid(),
        'slug' => $post['id'] ?? uniqid(),
        'title' => $post['title'] ?? 'Untitled',
        'excerpt' => $post['excerpt'] ?? '',
        'featured_image' => null, // JSON posts don't have images yet
        'category' => !empty($post['tags']) ? $post['tags'][0] : 'Intel',
        'tags' => $post['tags'] ?? [],
        'author' => $post['source'] ?? 'Intel Engine',
        'publish_date' => $post['published_at'] ?? date('Y-m-d H:i:s'),
        'views' => 0,
        'url' => $post['url'] ?? '#',
        'source' => $post['source'] ?? '',
        'severity' => $post['risk_level'] ?? 'medium',
        'region' => $post['region'] ?? 'global',
        'country' => $post['country'] ?? ''
      ];
    }, $posts);
  }
} catch (Throwable $e) {
  $blog_data_error = true;
  $blog_error_message = t('common.form_error_default', 'Vi kunne ikke hente blogindholdet lige nu. Prøv igen senere eller kontakt support.');
  error_log('[Blog] Failed to render blog.php: ' . $e->getMessage());
}

// Structured data for BlogPosting list
$structured_data = [
  '@context' => 'https://schema.org',
  '@type' => 'Blog',
  'name' => 'Blackbox EYE Blog',
  'description' => t('blog.meta.description'),
];

// Curated News Data - organized by region with full details
$news_items = [
  'denmark' => [
    'short' => 'DK',
    'title' => t('blog.news.denmark', 'Danmark'),
    'items' => [
      [
        'date' => '26. nov 2025',
        'source' => 'Ekstra Bladet',
        'title' => 'Stort flyselskab hacket: Kundernes data i fare',
        'excerpt' => 'Et stort flyselskab er blevet ramt af et cyberangreb, som potentielt har eksponeret kundedata.',
        'url' => 'https://ekstrabladet.dk/forbrug/Teknologi/stort-flyselskab-hacket-kundernes-data-i-fare/11019607',
        'severity' => 'high'
      ],
      [
        'date' => '26. nov 2025',
        'source' => 'Ekstra Bladet',
        'title' => 'Cybertrussel mod Danmark stadig meget høj',
        'excerpt' => 'Myndighederne fastslår, at cybertruslen forbliver på et meget højt niveau. Statslige aktører udgør fortsat betydelig risiko.',
        'url' => 'https://ekstrabladet.dk/nyheder/politik/danskpolitik/ny-vurdering-cybertrussel-mod-danmark-er-stadig-meget-hoej/11019651',
        'severity' => 'critical'
      ]
    ]
  ],
  'europe' => [
    'short' => 'EU',
    'title' => t('blog.news.europe', 'Europa'),
    'items' => [
      [
        'date' => '5. nov 2025',
        'source' => 'Industrial Cyber',
        'title' => 'CrowdStrike: Europas trusselsbillede 2025',
        'excerpt' => 'Big Game Hunting-ransomware rettet mod store europæiske økonomier voksede 13%. Ca. 2.100 europæiske ofre på ransomware-leak-sites.',
        'url' => 'https://industrialcyber.co/reports/crowdstrike-2025-european-threat-landscape',
        'severity' => 'high'
      ],
      [
        'date' => '5. nov 2025',
        'source' => 'Cybernews',
        'title' => 'Schweizisk bank angrebet af Qilin-gruppen',
        'excerpt' => 'Qilin hævder at have stjålet 2,5 TB data inkl. pasnumre, kontosaldi og bankens kildekode.',
        'url' => 'https://cybernews.com/security/habib-bank-zurich-qilin-ransomware',
        'severity' => 'critical'
      ],
      [
        'date' => '25. sep 2025',
        'source' => 'World Economic Forum',
        'title' => 'Ransomware rammer europæiske lufthavne',
        'excerpt' => 'Collins Aerospace-angreb forårsagede driftsforstyrrelser i Heathrow, Berlin og Bruxelles.',
        'url' => 'https://www.weforum.org/agenda/2025/09/european-airports-cyberattack',
        'severity' => 'high'
      ],
      [
        'date' => '14. okt 2025',
        'source' => 'The Guardian',
        'title' => 'UK: 50% stigning i betydningsfulde cyberangreb',
        'excerpt' => 'NCSC håndterede 429 hændelser. Kina, Rusland, Iran og Nordkorea identificeres som største trusler.',
        'url' => 'https://www.theguardian.com/technology/2025/oct/14/uk-cyberattacks-rise-ncsc',
        'severity' => 'critical'
      ]
    ]
  ],
  'middle_east' => [
    'short' => 'ME',
    'title' => t('blog.news.middle_east', 'Mellemøsten'),
    'items' => [
      [
        'date' => '25. mar 2025',
        'source' => 'The National',
        'title' => 'Massivt hack mod UAE: 634 organisationer',
        'excerpt' => 'Hackeren "rose87168" kompromitterede Oracle Cloud med 6 mio. kundeposter. 30 statslige enheder berørt.',
        'url' => 'https://www.thenationalnews.com/uae/2025/03/25/uae-oracle-cloud-hack',
        'severity' => 'critical'
      ],
      [
        'date' => '19. aug 2025',
        'source' => 'The Peninsula Qatar',
        'title' => 'Katar sanktionerer virksomhed for databrud',
        'excerpt' => 'Qatars Nationale Cyber Sikkerhed Agentur idømte sanktion for utilstrækkelige sikkerhedsforanstaltninger.',
        'url' => 'https://thepeninsulaqatar.com/article/19/08/2025/qatar-data-breach-sanction',
        'severity' => 'medium'
      ],
      [
        'date' => '23. nov 2025',
        'source' => 'Al Kabban & Associates',
        'title' => 'Dhs185 millioner cyberbedrageri i Dubai',
        'excerpt' => 'Sofistikeret svindel mod advokatfirma. UAE Cybersecurity Council advarer om 12.000+ databrud i 2025.',
        'url' => 'https://www.alkabban.ae/news/dubai-cyber-fraud-185-million',
        'severity' => 'high'
      ]
    ]
  ],
  'americas' => [
    'short' => 'AM',
    'title' => t('blog.news.americas', 'Amerika'),
    'items' => [
      [
        'date' => '11. sep 2025',
        'source' => 'BleepingComputer',
        'title' => 'Panama: Finansministeriet ramt af ransomware',
        'excerpt' => 'INC Ransom stjal over 1,5 TB data, herunder e-mails og finansielle dokumenter.',
        'url' => 'https://www.bleepingcomputer.com/news/security/panama-ministry-inc-ransomware',
        'severity' => 'high'
      ],
      [
        'date' => '3. nov 2025',
        'source' => 'Industrial Cyber',
        'title' => 'USA: 70% af angreb rammer kritisk infrastruktur',
        'excerpt' => 'Homeland Security advarer om øgede trusler fra Kina, Iran, Rusland og Nordkorea.',
        'url' => 'https://industrialcyber.co/threats-attacks/us-critical-infrastructure-70-percent',
        'severity' => 'critical'
      ],
      [
        'date' => '24. nov 2025',
        'source' => 'Cybersecurity Dive',
        'title' => 'SitusAMC-hack påvirker JPMorgan, Citi, Morgan Stanley',
        'excerpt' => 'Hackere stjal regnskabsoptegnelser, juridiske aftaler og kundedata. FBI bistår i undersøgelsen.',
        'url' => 'https://www.cybersecuritydive.com/news/situsamc-hack-banks',
        'severity' => 'critical'
      ]
    ]
  ],
  'asia' => [
    'short' => 'AS',
    'title' => t('blog.news.asia', 'Asien'),
    'items' => [
      [
        'date' => '7. okt 2025',
        'source' => 'Reuters',
        'title' => 'Qilin stopper Asahi Groups ølproduktion',
        'excerpt' => 'Ransomware-angreb den 29. september stoppede produktionen. 9.300 filer (≈27 GB) stjålet.',
        'url' => 'https://www.reuters.com/technology/asahi-group-qilin-ransomware-2025-10-07',
        'severity' => 'high'
      ],
      [
        'date' => '10. nov 2025',
        'source' => 'Cyberpress',
        'title' => 'Kinesisk sikkerhedsfirma lækker statsstøttede værktøjer',
        'excerpt' => 'Knownsec databrud: 12.000+ klassificerede dokumenter med avanceret malware til Linux, Windows, macOS.',
        'url' => 'https://cyberpress.org/knownsec-leak-chinese-malware',
        'severity' => 'critical'
      ],
      [
        'date' => '25. nov 2025',
        'source' => 'The Indian Express',
        'title' => '1,86 mio. brugere udsat i Adda-databrud',
        'excerpt' => 'Hacker lagde 145 MB data på forum med navne, telefonnumre og hashede passwords.',
        'url' => 'https://indianexpress.com/article/technology/adda-data-breach-1-86-million',
        'severity' => 'high'
      ]
    ]
  ]
];

include 'includes/site-header.php';
?>

<main id="main-content" class="pt-20 pb-16">
  <!-- Compact Hero + Status Bar -->
  <section class="relative py-8 sm:py-10 overflow-hidden border-b border-gray-800/50">
    <div class="absolute inset-0 cyber-grid opacity-10"></div>
    <div class="container mx-auto px-4 relative z-10">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <!-- Left: Title + live badge -->
        <div class="flex items-center gap-4">
          <h1 class="text-2xl sm:text-3xl font-bold hero-gradient-text">
            <?= t('blog.hero.title') ?>
          </h1>
          <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-500/10 border border-red-500/30">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
            </span>
            <span class="text-red-400 text-xs font-medium"><?= t('blog.hero.live', 'LIVE') ?></span>
          </div>
        </div>
        <!-- Right: Quick stats (compact pills) -->
        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
          <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-gray-800/50">
            <svg class="w-3.5 h-3.5 " style="color: var(--text-gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            26. nov 2025
          </span>
          <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-gray-800/50">
            <svg class="w-3.5 h-3.5 " style="color: var(--text-gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"></path>
            </svg>
            5 regioner
          </span>
          <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-gray-800/50">
            <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            15+ trusler
          </span>
        </div>
      </div>
    </div>
  </section>

  <?php if ($blog_data_error || !$db_available): ?>
    <section class="py-16">
      <div class="container mx-auto px-4">
        <div class="glass-effect rounded-2xl p-8 text-center border border-red-500/30">
          <h2 class="text-2xl font-bold mb-4 text-white">
            <?php if (!$db_available): ?>
              Blog er i gang med at blive opdateret
            <?php else: ?>
              Bloggen er midlertidigt utilgængelig
            <?php endif; ?>
          </h2>
          <p class="text-gray-200 mb-6">
            <?php if (!$db_available): ?>
              Vores blog-motor skifter fra database til et nyt, hurtigere system. Den vil snart være tilbage med friske cybersecurity-nyheder!
            <?php else: ?>
              <?= htmlspecialchars($blog_error_message) ?>
            <?php endif; ?>
          </p>
          <a href="contact.php" class="inline-flex items-center gap-2 px-6 py-3 border-2 rounded-lg font-semibold transition-colors" style="background: rgba(10, 12, 16, 0.96); border-color: rgba(255, 255, 255, 0.22); color: #f3f4f6;">
            Kontakt support
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </a>
        </div>
      </div>
    </section>
  <?php else: ?>

    <!-- Unified Sticky Navigation: Improved Filter + Region Tabs -->
    <nav class="sticky top-16 z-30 bg-[var(--page-background)]/95 backdrop-blur-lg border-b border-gray-800/50" aria-label="Blog navigation">
      <div class="container mx-auto px-4">
        <!-- Two-row layout: categories top, regions bottom -->
        <div class="flex flex-col gap-3 py-3">
          <!-- Top row: Category filter -->
          <?php if (!empty($categories)): ?>
            <div class="flex items-start gap-2">
              <span class="text-xs text-gray-500 flex-shrink-0 hidden sm:inline pt-2.5"><?= t('blog.filter.label', 'Filter:') ?></span>
              <div class="flex items-center gap-2 flex-wrap flex-1 min-w-0">
                <a href="blog.php"
                  class="blog-filter-pill <?= $category_filter === null ? 'is-active' : '' ?>">
                  <?= t('blog.filter.all') ?>
                </a>
                <?php foreach ($categories as $cat): ?>
                  <a href="blog.php?category=<?= urlencode($cat) ?>"
                    class="blog-filter-pill <?= $category_filter === $cat ? 'is-active' : '' ?>">
                    <?= htmlspecialchars($cat) ?>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Bottom row: Region tabs with label -->
          <div class="flex items-start gap-2">
            <span class="text-xs text-gray-500 flex-shrink-0 hidden sm:inline pt-2.5"><?= t('blog.news.title', 'Nyheder') ?>:</span>
            <div class="flex items-center gap-1.5 flex-wrap flex-1 min-w-0">
              <?php $first = true;
              foreach ($news_items as $region_key => $region): ?>
                <button
                  class="news-region-tab <?= $first ? 'is-active' : '' ?>"
                  data-region="<?= $region_key ?>"
                  aria-selected="<?= $first ? 'true' : 'false' ?>"
                  title="<?= $region['title'] ?>"
                  aria-label="<?= $region['title'] ?>">
                  <span class="region-badge region-badge--tab" aria-hidden="true"><?= htmlspecialchars($region['short']) ?></span>
                  <span class="hidden lg:inline text-xs"><?= $region['title'] ?></span>
                </button>
              <?php $first = false;
              endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Blog Posts Section -->
    <section class="py-12">
      <div class="container mx-auto px-4">
        <?php if (empty($posts)): ?>
          <!-- No Posts State -->
          <div class="max-w-md mx-auto text-center py-16">
            <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gray-800/50 flex items-center justify-center">
              <svg class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
              </svg>
            </div>
            <p class="text-xl text-gray-400 mb-6"><?= t('blog.empty.message') ?></p>
            <a href="blog.php" class="inline-block px-6 py-3 border-2" style="background: rgba(232, 197, 71, 0.14); border-color: var(--text-gold); color: var(--text-gold); text-black font-semibold rounded-lg hover:bg-opacity-90 transition-colors">
              <?= t('blog.empty.back') ?>
            </a>
          </div>
        <?php else: ?>

          <!-- Section Header -->
          <div class="flex items-center justify-between mb-8">
            <div>
              <h2 class="text-2xl font-bold text-white"><?= t('blog.section.our_articles', 'Vores Artikler') ?></h2>
              <p class="text-gray-400 mt-1"><?= t('blog.section.our_articles_desc', 'Dybdegående analyser fra vores eksperter') ?></p>
            </div>
            <div class="hidden sm:flex items-center gap-2 text-sm text-gray-500">
              <span><?= $total_posts ?> <?= t('blog.articles', 'artikler') ?></span>
            </div>
          </div>

          <!-- Posts Grid - Modern Card Design -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($posts as $index => $post): ?>
              <article class="blog-card group <?= $index === 0 ? 'blog-card--featured md:col-span-2 lg:col-span-1' : '' ?>">
                <?php if ($post['featured_image']): ?>
                  <div class="blog-card__image">
                    <img src="<?= htmlspecialchars($post['featured_image']) ?>"
                      alt="<?= htmlspecialchars($post['title']) ?>"
                      loading="lazy"
                      decoding="async"
                      class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="blog-card__image-overlay"></div>
                  </div>
                <?php endif; ?>

                <div class="blog-card__content">
                  <!-- Category Badge -->
                  <?php if ($post['category']): ?>
                    <span class="blog-card__category">
                      <?= htmlspecialchars($post['category']) ?>
                    </span>
                  <?php endif; ?>

                  <!-- Title -->
                  <h3 class="blog-card__title">
                    <?php if ($data_source === 'json' && !empty($post['url'])): ?>
                      <!-- External link for JSON posts -->
                      <a href="<?= htmlspecialchars($post['url']) ?>" 
                         target="_blank" 
                         rel="noopener noreferrer" 
                         class="hover:" 
                         style="color: var(--text-gold); transition-colors">
                        <?= htmlspecialchars($post['title']) ?>
                        <svg class="w-3.5 h-3.5 inline-block ml-1 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                      </a>
                    <?php else: ?>
                      <!-- Internal link for database posts -->
                      <a href="<?= bbx_get_blog_post_url($post['slug']) ?>" class="hover:" style="color: var(--text-gold); transition-colors">
                        <?= htmlspecialchars($post['title']) ?>
                      </a>
                    <?php endif; ?>
                  </h3>

                  <!-- Excerpt -->
                  <?php if ($post['excerpt']): ?>
                    <p class="blog-card__excerpt">
                      <?= htmlspecialchars($post['excerpt']) ?>
                    </p>
                  <?php endif; ?>

                  <!-- Meta Footer -->
                  <div class="blog-card__meta">
                    <div class="flex items-center gap-4 text-sm text-gray-500">
                      <?php if ($data_source === 'json'): ?>
                        <!-- JSON posts: show source and date -->
                        <span class="font-semibold" style="color: var(--text-gold);"><?= htmlspecialchars($post['source']) ?></span>
                        <span><?= bbx_format_blog_date($post['publish_date']) ?></span>
                        <?php if (!empty($post['severity'])): ?>
                          <span class="text-xs px-2 py-0.5 rounded-full <?= 
                            $post['severity'] === 'critical' ? 'bg-red-500/20 text-red-400' : 
                            ($post['severity'] === 'high' ? 'bg-amber-500/20 text-amber-400' : 
                            ($post['severity'] === 'medium' ? 'bg-blue-500/20 text-blue-400' : 'bg-gray-500/20 text-gray-400'))
                          ?>">
                            <?= strtoupper($post['severity']) ?>
                          </span>
                        <?php endif; ?>
                      <?php else: ?>
                        <!-- Database posts: show date and views -->
                        <span><?= bbx_format_blog_date($post['publish_date']) ?></span>
                        <span class="flex items-center gap-1">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                          </svg>
                          <?= number_format($post['views']) ?>
                        </span>
                      <?php endif; ?>
                    </div>
                    <?php if ($data_source === 'json' && !empty($post['url'])): ?>
                      <a href="<?= htmlspecialchars($post['url']) ?>" 
                         target="_blank" 
                         rel="noopener noreferrer" 
                         class="blog-card__read-more">
                        <?= t('blog.read_source', 'Læs kilde') ?>
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                      </a>
                    <?php else: ?>
                      <a href="<?= bbx_get_blog_post_url($post['slug']) ?>" class="blog-card__read-more">
                        <?= t('blog.read_more') ?>
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>

          <!-- Pagination -->
          <?php
          $base_url = 'blog.php' . ($category_filter ? '?category=' . urlencode($category_filter) : '');
          echo bbx_blog_pagination($current_page_num, $total_pages, $base_url);
          ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- Global Threat Intelligence Section -->
    <section class="py-10 relative overflow-hidden">
      <div class="container mx-auto px-4 relative z-10">
        <!-- Section Header (compact) -->
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center">
              <svg class="w-4 h-4 " style="color: var(--text-gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
            </div>
            <div>
              <h2 class="text-lg font-bold text-white"><?= t('blog.news.title', 'Global Cyber Security News') ?></h2>
              <p class="text-xs text-gray-500"><?= t('blog.news.subtitle', 'Kurateret af vores eksperter') ?></p>
            </div>
          </div>
        </div>

        <!-- News Panels (no tabs here - controlled by sticky nav) -->
        <div class="news-panels">
          <?php $first = true;
          foreach ($news_items as $region_key => $region): ?>
            <div class="news-panel <?= $first ? 'is-visible' : '' ?>" data-panel="<?= $region_key ?>">
              <!-- Region Header -->
              <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-800/50">
                <span class="region-badge region-badge--panel" aria-hidden="true"><?= htmlspecialchars($region['short']) ?></span>
                <h3 class="text-base font-semibold text-white"><?= $region['title'] ?></h3>
                <span class="ml-auto text-xs text-gray-500 bg-gray-800/50 px-2 py-0.5 rounded"><?= count($region['items']) ?> <?= t('blog.news.alerts', 'alerts') ?></span>
              </div>

              <!-- News Grid (compact cards) -->
              <div class="grid gap-3">
                <?php foreach ($region['items'] as $item): ?>
                  <article class="news-card news-card--<?= $item['severity'] ?>">
                    <!-- Severity indicator -->
                    <div class="news-card__severity-badge" data-severity="<?= $item['severity'] ?>">
                      <?= strtoupper($item['severity']) ?>
                    </div>

                    <div class="news-card__body">
                      <!-- Meta row -->
                      <div class="news-card__meta">
                        <span class="news-card__source"><?= $item['source'] ?></span>
                        <span class="news-card__date"><?= $item['date'] ?></span>
                      </div>

                      <!-- Title -->
                      <h4 class="news-card__title">
                        <?= $item['title'] ?>
                      </h4>

                      <!-- Excerpt -->
                      <p class="news-card__excerpt"><?= $item['excerpt'] ?></p>

                      <!-- Source link CTA (unified) -->
                      <?php if ($item['url']): ?>
                        <a href="<?= $item['url'] ?>" target="_blank" rel="noopener noreferrer" class="news-card__source-link">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                          </svg>
                          <?= t('blog.news.read_source', 'Læs kilde') ?>
                        </a>
                      <?php endif; ?>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            </div>
          <?php $first = false;
          endforeach; ?>
        </div>

        <!-- Attribution -->
        <p class="text-xs text-gray-500 mt-6 text-center">
          <svg class="w-3 h-3 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
          </svg>
          <?= t('blog.news.attribution', 'Kurateret fra verificerede kilder. Alle nyheder er til informationsformål.') ?>
        </p>
      </div>
    </section>

    <!-- Newsletter CTA - Redesigned -->
    <section class="py-16">
      <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
          <div class="newsletter-card relative overflow-hidden rounded-3xl p-8 sm:p-12">
            <!-- Background pattern -->
            <div class="absolute inset-0 opacity-30">
              <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23fbbf24\' fill-opacity=\'0.1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>

            <div class="relative z-10 text-center">
              <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl " style="background: rgba(212, 175, 55, 0.15); mb-6">
                <svg class="w-8 h-8 " style="color: var(--text-gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
              </div>

              <h3 class="text-2xl sm:text-3xl font-bold mb-4"><?= t('blog.newsletter.title') ?></h3>
              <p class="text-gray-200 mb-8 max-w-xl mx-auto"><?= t('blog.newsletter.description') ?></p>

              <form id="blog-newsletter-form" class="flex flex-col sm:flex-row gap-3 max-w-lg mx-auto">
                <input type="email"
                  name="email"
                  placeholder="<?= htmlspecialchars(t('blog.newsletter.placeholder')) ?>"
                  required
                  class="flex-1 bg-gray-800/80 border border-gray-700 rounded-xl px-5 py-4 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all">
                <button type="submit"
                  class="px-8 py-4 border-2" style="background: rgba(232, 197, 71, 0.14); border-color: var(--text-gold); color: var(--text-gold); text-black font-bold rounded-xl hover:bg-opacity-90 transition-all hover:scale-105 whitespace-nowrap">
                  <?= t('blog.newsletter.button') ?>
                </button>
              </form>

              <p class="text-xs text-gray-500 mt-4"><?= t('blog.newsletter.privacy', 'Vi respekterer dit privatliv. Afmeld når som helst.') ?></p>
            </div>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>
</main>

<style>
  /* Blog Page Specific Styles */

  /* Cyber grid background */
  .cyber-grid {
    background-image:
      linear-gradient(rgba(255, 199, 0, 0.03) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255, 199, 0, 0.03) 1px, transparent 1px);
    background-size: 50px 50px;
    animation: grid-move 20s linear infinite;
  }

  @keyframes grid-move {
    0% {
      transform: translate(0, 0);
    }

    100% {
      transform: translate(50px, 50px);
    }
  }

  /* Filter pills */
  .blog-filter-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--text-medium-emphasis);
    background: var(--surface-200);
    border: 1px solid var(--surface-border);
    white-space: nowrap;
    flex-shrink: 0;
    transition: all 0.2s ease;
  }

  .blog-filter-pill:hover {
    border-color: var(--text-gold);
    color: var(--text-high-emphasis);
    background: rgba(255, 199, 0, 0.08);
  }

  .blog-filter-pill.is-active {
    background: var(--primary-accent);
    color: #000;
    border-color: transparent;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(255, 199, 0, 0.25);
  }

  /* Blog cards */
  .blog-card {
    background: var(--surface-card-bg);
    border: 1px solid var(--surface-border);
    border-radius: 1.25rem;
    overflow: hidden;
    transition: all 0.3s ease;
  }

  .blog-card:hover {
    border-color: var(--text-gold);
    transform: translateY(-4px);
    box-shadow: 0 20px 40px -20px rgba(255, 199, 0, 0.2);
  }

  .blog-card__image {
    position: relative;
    aspect-ratio: 16/9;
    overflow: hidden;
    background: var(--surface-200);
  }

  .blog-card__image-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.6), transparent);
  }

  .blog-card__content {
    padding: 1.5rem;
  }

  .blog-card__category {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    background: var(--primary-accent-soft);
    color: var(--text-gold);
    margin-bottom: 0.75rem;
  }

  .blog-card__title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--text-high-emphasis);
    margin-bottom: 0.75rem;
    line-height: 1.4;
  }

  .blog-card__excerpt {
    font-size: 0.9rem;
    color: var(--text-medium-emphasis);
    line-height: 1.6;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 1rem;
  }

  .blog-card__meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid var(--surface-border);
  }

  .blog-card__read-more {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-gold);
  }

  .region-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.4rem;
    padding: 0.2rem 0.55rem;
    border-radius: 0.65rem;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    background: rgba(255, 199, 0, 0.12);
    color: var(--text-gold);
    border: 1px solid rgba(255, 199, 0, 0.32);
    transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
  }

  .region-badge--tab {
    min-width: 2.1rem;
    font-size: 0.625rem;
    padding: 0.18rem 0.5rem;
  }

  .region-badge--panel {
    font-size: 0.7rem;
    padding: 0.25rem 0.65rem;
    border-radius: 0.75rem;
    background: rgba(255, 199, 0, 0.16);
  }

  /* News region tabs - compact for sticky nav */
  .news-region-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.75rem;
    border-radius: 0.6rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--text-medium-emphasis);
    background: transparent;
    border: 1px solid var(--surface-border-soft);
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .news-region-tab:hover {
    color: var(--text-high-emphasis);
    background: rgba(255, 255, 255, 0.06);
    border-color: var(--surface-border);
  }

  .news-region-tab:hover .region-badge {
    border-color: rgba(255, 199, 0, 0.48);
  }

  .news-region-tab.is-active {
    background: rgba(255, 199, 0, 0.12);
    border-color: rgba(255, 199, 0, 0.45);
    color: var(--text-gold);
    font-weight: 600;
  }

  .news-region-tab.is-active .region-badge {
    background: var(--primary-accent);
    color: var(--cta-contrast);
    border-color: transparent;
  }

  /* News panels */
  .news-panel {
    display: none;
  }

  .news-panel.is-visible {
    display: block;
    animation: fadeIn 0.2s ease;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(6px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* News cards - compact redesign */
  .news-card {
    display: flex;
    align-items: flex-start;
    gap: 0.875rem;
    padding: 1rem;
    background: var(--surface-card-bg);
    border: 1px solid var(--surface-border);
    border-radius: 0.75rem;
    transition: border-color 0.15s ease, transform 0.15s ease;
  }

  .news-card:hover {
    border-color: var(--surface-border-strong);
    transform: translateX(2px);
  }

  /* Severity badge (compact pill) */
  .news-card__severity-badge {
    flex-shrink: 0;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.625rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }

  .news-card__severity-badge[data-severity="critical"] {
    background: rgba(239, 68, 68, 0.15);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.3);
  }

  .news-card__severity-badge[data-severity="high"] {
    background: var(--color-primary-soft, rgba(201, 162, 39, 0.15));
    color: var(--text-gold, var(--color-primary));
    border: 1px solid var(--color-primary-soft, rgba(201, 162, 39, 0.3));
  }

  .news-card__severity-badge[data-severity="medium"] {
    background: rgba(59, 130, 246, 0.15);
    color: #60a5fa;
    border: 1px solid rgba(59, 130, 246, 0.3);
  }

  .news-card__body {
    flex: 1;
    min-width: 0;
  }

  .news-card__meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.375rem;
  }

  .news-card__source {
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--text-gold);
  }

  .news-card__date {
    font-size: 0.7rem;
    color: var(--muted);
  }

  .news-card__date::before {
    content: "•";
    margin-right: 0.5rem;
    color: var(--muted);
  }

  .news-card__title {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-high-emphasis);
    line-height: 1.4;
    margin-bottom: 0.375rem;
  }

  .news-card__excerpt {
    font-size: 0.8rem;
    color: var(--text-medium-emphasis);
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  /* Unified source link CTA */
  .news-card__source-link {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    margin-top: 0.625rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--text-gold);
    background: var(--primary-accent-soft);
    transition: background-color 0.15s ease, gap 0.15s ease;
  }

  .news-card__source-link:hover {
    background: rgba(255, 199, 0, 0.25);
    gap: 0.5rem;
  }

  /* Legacy styles for backwards compat */
  .news-card__severity {
    flex-shrink: 0;
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.75rem;
  }

  .news-card--critical .news-card__severity {
    background: rgba(239, 68, 68, 0.15);
    color: #f87171;
  }

  .news-card--high .news-card__severity {
    background: rgba(251, 191, 36, 0.15);
color: var(--text-gold, var(--color-primary));
  }

  .news-card--medium .news-card__severity {
    background: rgba(59, 130, 246, 0.15);
    color: #60a5fa;
  }

  .news-card__content {
    flex: 1;
    min-width: 0;
  }

  .news-card__header {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
  }

  .news-card__link {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    margin-top: 0.75rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-gold);
    transition: gap 0.2s ease;
  }

  .news-card__link:hover {
    gap: 0.625rem;
  }

  /* Newsletter card */
  .newsletter-card {
    background: linear-gradient(135deg, rgba(255, 199, 0, 0.1) 0%, rgba(17, 24, 39, 0.95) 50%, rgba(255, 199, 0, 0.05) 100%);
    border: 1px solid rgba(255, 199, 0, 0.2);
  }

  /* Hide scrollbar for filter pills */
  .scrollbar-hide::-webkit-scrollbar {
    display: none;
  }

  .scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
  }

  /* Light mode adjustments */
  :root[data-theme="light"] .region-badge {
    background: rgba(255, 180, 0, 0.18);
    border-color: rgba(255, 180, 0, 0.28);
  }

  :root[data-theme="light"] .region-badge--panel {
    background: rgba(255, 180, 0, 0.24);
  }

  :root[data-theme="light"] .news-region-tab:hover {
    background: rgba(17, 24, 39, 0.06);
  }

  :root[data-theme="light"] .news-region-tab.is-active .region-badge {
    color: #111827;
  }

  :root[data-theme="light"] .blog-card {
    background: rgba(255, 255, 255, 0.9);
  }

  :root[data-theme="light"] .news-card {
    background: rgba(255, 255, 255, 0.9);
  }

  :root[data-theme="light"] .blog-filter-pill.is-active {
    color: #1f2937;
  }

  :root[data-theme="light"] .news-region-tab.is-active {
    color: #1f2937;
  }

  :root[data-theme="light"] .newsletter-card {
    background: linear-gradient(135deg, rgba(255, 199, 0, 0.15) 0%, rgba(255, 255, 255, 0.95) 50%, rgba(255, 199, 0, 0.08) 100%);
  }
</style>

<script>
  // News region tabs functionality
  document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.news-region-tab');
    const panels = document.querySelectorAll('.news-panel');

    tabs.forEach(tab => {
      tab.addEventListener('click', function() {
        const region = this.dataset.region;

        // Update tabs
        tabs.forEach(t => {
          t.classList.remove('is-active');
          t.setAttribute('aria-selected', 'false');
        });
        this.classList.add('is-active');
        this.setAttribute('aria-selected', 'true');

        // Update panels
        panels.forEach(p => {
          p.classList.remove('is-visible');
          if (p.dataset.panel === region) {
            p.classList.add('is-visible');
          }
        });
      });
    });
  });
</script>

<?php include 'includes/site-footer.php'; ?>
