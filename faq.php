<?php
/**
 * FAQ Page with AI-Powered Search - Blackbox EYE
 * Sprint 4: FAQ Section + AI Search
 * 
 * @version 1.0
 * @date 2025-11-23
 */

require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/db.php';

$current_page = 'faq';
$page_title = t('faq.meta.title');
$meta_description = t('faq.meta.description');

// Get language
$lang = bbx_get_language();

// Get selected category from query string
$selected_category = isset($_GET['category']) ? $_GET['category'] : null;

// Fetch FAQ categories
$categories_sql = "SELECT DISTINCT category FROM faq_items ORDER BY category";
$categories = $pdo->query($categories_sql)->fetchAll(PDO::FETCH_COLUMN);

// Fetch FAQ items
if ($selected_category) {
    $faq_sql = "
        SELECT 
            id,
            category,
            " . ($lang === 'da' ? 'question_da AS question' : 'question_en AS question') . ",
            " . ($lang === 'da' ? 'answer_da AS answer' : 'answer_en AS answer') . ",
            keywords,
            helpful_count,
            not_helpful_count,
            order_index
        FROM faq_items 
        WHERE category = :category
        ORDER BY order_index ASC, id ASC
    ";
    $stmt = $pdo->prepare($faq_sql);
    $stmt->execute(['category' => $selected_category]);
} else {
    $faq_sql = "
        SELECT 
            id,
            category,
            " . ($lang === 'da' ? 'question_da AS question' : 'question_en AS question') . ",
            " . ($lang === 'da' ? 'answer_da AS answer' : 'answer_en AS answer') . ",
            keywords,
            helpful_count,
            not_helpful_count,
            order_index
        FROM faq_items 
        ORDER BY category, order_index ASC, id ASC
    ";
    $stmt = $pdo->query($faq_sql);
}

$faqs = $stmt->fetchAll();

// Group FAQs by category for display
$faqs_by_category = [];
foreach ($faqs as $faq) {
    $faqs_by_category[$faq['category']][] = $faq;
}

// Structured data for FAQPage
$faq_schema_items = [];
foreach ($faqs as $faq) {
    $faq_schema_items[] = [
        '@type' => 'Question',
        'name' => $faq['question'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => strip_tags($faq['answer'])
        ]
    ];
}

$structured_data = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => $faq_schema_items
];

include 'includes/site-header.php';
?>

<main id="main-content" class="pt-24 pb-16">
    <!-- Hero Section -->
    <section class="py-16 bg-gradient-to-b from-gray-900/50 to-transparent">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-4xl sm:text-5xl font-bold mb-6 hero-gradient-text">
                    <?= t('faq.hero.title') ?>
                </h1>
                <p class="text-lg text-gray-300 mb-8">
                    <?= t('faq.hero.description') ?>
                </p>
                
                <!-- AI-Powered Search Bar -->
                <div class="relative">
                    <input type="text" 
                           id="faq-search" 
                           placeholder="<?= htmlspecialchars(t('faq.search.placeholder')) ?>"
                           class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-6 py-4 pr-12 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent">
                    <button id="faq-search-btn" 
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-amber-400 hover:text-amber-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Search Results Container -->
                <div id="faq-search-results" class="hidden mt-6 text-left"></div>
            </div>
        </div>
    </section>

    <!-- Category Filter -->
    <section class="py-8 border-b border-gray-800">
        <div class="container mx-auto px-4">
            <div class="flex flex-wrap justify-center gap-3">
                <a href="faq.php" 
                   class="px-4 py-2 rounded-lg transition-colors <?= $selected_category === null ? 'bg-amber-400 text-black font-semibold' : 'bg-gray-800 hover:bg-gray-700 text-gray-300' ?>">
                    <?= t('faq.filter.all') ?>
                </a>
                <?php foreach ($categories as $cat): ?>
                <a href="faq.php?category=<?= urlencode($cat) ?>" 
                   class="px-4 py-2 rounded-lg transition-colors <?= $selected_category === $cat ? 'bg-amber-400 text-black font-semibold' : 'bg-gray-800 hover:bg-gray-700 text-gray-300' ?>">
                    <?= htmlspecialchars($cat) ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- FAQ Accordion -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <?php if (empty($faqs)): ?>
                    <div class="text-center py-16">
                        <p class="text-xl text-gray-400"><?= t('faq.empty.message') ?></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($faqs_by_category as $category => $category_faqs): ?>
                        <!-- Category Header -->
                        <div class="mb-8">
                            <h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
                                <span class="text-amber-400"><?= htmlspecialchars($category) ?></span>
                                <span class="text-sm text-gray-400 font-normal">(<?= count($category_faqs) ?> <?= t('faq.questions') ?>)</span>
                            </h2>
                            
                            <!-- FAQ Items -->
                            <div class="space-y-4">
                                <?php foreach ($category_faqs as $faq): ?>
                                <div class="faq-item glass-effect rounded-xl overflow-hidden" data-faq-id="<?= $faq['id'] ?>">
                                    <!-- Question (clickable) -->
                                    <button class="faq-question w-full text-left px-6 py-5 flex items-center justify-between hover:bg-gray-800/50 transition-colors"
                                            aria-expanded="false"
                                            aria-controls="faq-answer-<?= $faq['id'] ?>">
                                        <span class="text-lg font-semibold text-white pr-4"><?= htmlspecialchars($faq['question']) ?></span>
                                        <svg class="faq-icon w-6 h-6 text-amber-400 flex-shrink-0 transition-transform duration-300" 
                                             fill="none" 
                                             stroke="currentColor" 
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    
                                    <!-- Answer (collapsible) -->
                                    <div id="faq-answer-<?= $faq['id'] ?>" 
                                         class="faq-answer hidden px-6 pb-5"
                                         role="region">
                                        <div class="pt-4 border-t border-gray-700">
                                            <p class="text-gray-300 leading-relaxed mb-4">
                                                <?= nl2br(htmlspecialchars($faq['answer'])) ?>
                                            </p>
                                            
                                            <!-- Helpfulness Feedback -->
                                            <div class="flex items-center gap-4 pt-4 border-t border-gray-800">
                                                <span class="text-sm text-gray-400"><?= t('faq.helpful.question') ?></span>
                                                <div class="flex items-center gap-2">
                                                    <button class="faq-helpful-btn px-3 py-1 bg-gray-800 hover:bg-green-600 rounded-lg transition-colors text-sm flex items-center gap-1"
                                                            data-faq-id="<?= $faq['id'] ?>"
                                                            data-vote="helpful">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                                                        </svg>
                                                        <span class="helpful-count"><?= $faq['helpful_count'] ?></span>
                                                    </button>
                                                    <button class="faq-helpful-btn px-3 py-1 bg-gray-800 hover:bg-red-600 rounded-lg transition-colors text-sm flex items-center gap-1"
                                                            data-faq-id="<?= $faq['id'] ?>"
                                                            data-vote="not-helpful">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"></path>
                                                        </svg>
                                                        <span class="not-helpful-count"><?= $faq['not_helpful_count'] ?></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-r from-amber-400/10 to-amber-600/10 border-y border-amber-400/20">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center">
                <h3 class="text-2xl sm:text-3xl font-bold mb-4"><?= t('faq.cta.title') ?></h3>
                <p class="text-gray-300 mb-8 text-lg"><?= t('faq.cta.description') ?></p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="contact.php" 
                       class="px-8 py-4 bg-amber-400 text-black font-semibold rounded-lg hover:bg-amber-500 transition-colors">
                        <?= t('faq.cta.contact') ?>
                    </a>
                    <a href="pricing.php" 
                       class="px-8 py-4 border border-amber-400 text-amber-400 font-semibold rounded-lg hover:bg-amber-400 hover:text-black transition-colors">
                        <?= t('faq.cta.pricing') ?>
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
// FAQ Accordion functionality
document.addEventListener('DOMContentLoaded', () => {
    // Accordion toggle
    document.querySelectorAll('.faq-question').forEach(button => {
        button.addEventListener('click', () => {
            const faqItem = button.closest('.faq-item');
            const answer = faqItem.querySelector('.faq-answer');
            const icon = button.querySelector('.faq-icon');
            const isExpanded = button.getAttribute('aria-expanded') === 'true';
            
            // Toggle this item
            button.setAttribute('aria-expanded', !isExpanded);
            answer.classList.toggle('hidden');
            icon.style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(180deg)';
        });
    });

    // Helpfulness voting
    document.querySelectorAll('.faq-helpful-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const faqId = btn.dataset.faqId;
            const vote = btn.dataset.vote;
            
            try {
                const response = await fetch('api/faq-feedback.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ faq_id: faqId, vote })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update counts
                    const faqItem = btn.closest('.faq-item');
                    faqItem.querySelector('.helpful-count').textContent = data.helpful_count;
                    faqItem.querySelector('.not-helpful-count').textContent = data.not_helpful_count;
                    
                    // Visual feedback
                    btn.classList.add('ring-2', 'ring-amber-400');
                    setTimeout(() => btn.classList.remove('ring-2', 'ring-amber-400'), 1000);
                }
            } catch (error) {
                console.error('Failed to submit feedback:', error);
            }
        });
    });

    // AI-Powered Search (will be implemented in separate API endpoint)
    const searchInput = document.getElementById('faq-search');
    const searchBtn = document.getElementById('faq-search-btn');
    const searchResults = document.getElementById('faq-search-results');
    
    let searchTimeout;
    
    const performSearch = async () => {
        const query = searchInput.value.trim();
        
        if (query.length < 3) {
            searchResults.classList.add('hidden');
            return;
        }
        
        searchResults.innerHTML = '<div class="glass-effect rounded-xl p-6 text-center"><span class="text-amber-400"><?= t('common.ai_loading') ?></span></div>';
        searchResults.classList.remove('hidden');
        
        try {
            const response = await fetch('api/faq-search.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ query, language: '<?= $lang ?>' })
            });
            
            const data = await response.json();
            
            if (data.results && data.results.length > 0) {
                let html = '<div class="glass-effect rounded-xl p-6"><h3 class="text-lg font-bold mb-4 text-amber-400"><?= t('faq.search.results') ?> (' + data.results.length + ')</h3><div class="space-y-4">';
                
                data.results.forEach(faq => {
                    html += `
                        <div class="border-b border-gray-700 last:border-0 pb-4 last:pb-0">
                            <a href="#faq-answer-${faq.id}" class="block hover:text-amber-400 transition-colors">
                                <h4 class="font-semibold mb-2">${faq.question}</h4>
                                <p class="text-sm text-gray-400">${faq.answer.substring(0, 150)}...</p>
                            </a>
                        </div>
                    `;
                });
                
                html += '</div></div>';
                searchResults.innerHTML = html;
            } else {
                searchResults.innerHTML = '<div class="glass-effect rounded-xl p-6 text-center text-gray-400"><?= t('faq.search.no_results') ?></div>';
            }
        } catch (error) {
            console.error('Search failed:', error);
            searchResults.innerHTML = '<div class="glass-effect rounded-xl p-6 text-center text-red-400"><?= t('common.form_error_default') ?></div>';
        }
    };
    
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 500);
    });
    
    searchBtn.addEventListener('click', performSearch);
    
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
    });
});
</script>

<?php include 'includes/site-footer.php'; ?>
