<?php

/**
 * Blog Listing Page - Blackbox EYE
 * Sprint 4: Blog CMS System
 *
 * @version 1.0
 * @date 2025-11-23
 */

require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/blog-functions.php';
require_once __DIR__ . '/db.php';

$current_page = 'blog';
$page_title = t('blog.meta.title');
$meta_description = t('blog.meta.description');

// Pagination
$posts_per_page = 9;
$current_page_num = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$category_filter = isset($_GET['category']) ? $_GET['category'] : null;

// Get posts
$blog_data_error = false;
$blog_error_message = '';
$posts = [];
$categories = [];
$total_posts = 0;
$total_pages = 0;

try {
  $posts = bbx_get_blog_posts($current_page_num, $posts_per_page, $category_filter);
  $total_posts = bbx_get_blog_posts_count($category_filter);
  $total_pages = $total_posts > 0 ? (int) ceil($total_posts / $posts_per_page) : 0;
  $categories = bbx_get_blog_categories();
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

include 'includes/site-header.php';
?>

<main id="main-content" class="pt-24 pb-16">
  <!-- Hero Section -->
  <section class="py-16 bg-gradient-to-b from-gray-900/50 to-transparent">
    <div class="container mx-auto px-4">
      <div class="max-w-3xl mx-auto text-center">
        <h1 class="text-4xl sm:text-5xl font-bold mb-6 hero-gradient-text">
          <?= t('blog.hero.title') ?>
        </h1>
        <p class="text-lg text-gray-300">
          <?= t('blog.hero.description') ?>
        </p>
      </div>
    </div>
  </section>

  <?php if ($blog_data_error): ?>
    <section class="py-16">
      <div class="container mx-auto px-4">
        <div class="glass-effect rounded-2xl p-8 text-center border border-red-500/30">
          <h2 class="text-2xl font-bold mb-4 text-white">
            Bloggen er midlertidigt utilgængelig
          </h2>
          <p class="text-gray-300 mb-6">
            <?= htmlspecialchars($blog_error_message) ?>
          </p>
          <a href="contact.php" class="inline-flex items-center gap-2 px-6 py-3 bg-amber-400 text-black font-semibold rounded-lg hover:bg-amber-500 transition-colors">
            Kontakt support
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </a>
        </div>
      </div>
    </section>
  <?php else: ?>
    <!-- Category Filter -->
    <?php if (!empty($categories)): ?>
      <section class="py-8 border-b border-gray-800">
        <div class="container mx-auto px-4">
          <div class="flex flex-wrap justify-center gap-3">
            <a href="blog.php"
              class="px-4 py-2 rounded-lg transition-colors <?= $category_filter === null ? 'bg-amber-400 text-black font-semibold' : 'bg-gray-800 hover:bg-gray-700 text-gray-300' ?>">
              <?= t('blog.filter.all') ?>
            </a>
            <?php foreach ($categories as $cat): ?>
              <a href="blog.php?category=<?= urlencode($cat) ?>"
                class="px-4 py-2 rounded-lg transition-colors <?= $category_filter === $cat ? 'bg-amber-400 text-black font-semibold' : 'bg-gray-800 hover:bg-gray-700 text-gray-300' ?>">
                <?= htmlspecialchars($cat) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
    <?php endif; ?>

    <!-- Blog Posts Grid -->
    <section class="py-16">
      <div class="container mx-auto px-4">
        <?php if (empty($posts)): ?>
          <!-- No Posts -->
          <div class="max-w-2xl mx-auto text-center py-16">
            <p class="text-xl text-gray-400 mb-6"><?= t('blog.empty.message') ?></p>
            <a href="blog.php" class="inline-block px-6 py-3 bg-amber-400 text-black font-semibold rounded-lg hover:bg-amber-500 transition-colors">
              <?= t('blog.empty.back') ?>
            </a>
          </div>
        <?php else: ?>
          <!-- Posts Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($posts as $post): ?>
              <article class="glass-effect rounded-2xl overflow-hidden hover:scale-105 transition-transform duration-300">
                <?php if ($post['featured_image']): ?>
                  <div class="aspect-video bg-gray-800 overflow-hidden">
                    <img src="<?= htmlspecialchars($post['featured_image']) ?>"
                      alt="<?= htmlspecialchars($post['title']) ?>"
                      loading="lazy"
                      decoding="async"
                      class="w-full h-full object-cover">
                  </div>
                <?php endif; ?>

                <div class="p-6">
                  <!-- Category Badge -->
                  <?php if ($post['category']): ?>
                    <span class="inline-block px-3 py-1 text-xs font-semibold bg-amber-400/20 text-amber-400 rounded-full mb-3">
                      <?= htmlspecialchars($post['category']) ?>
                    </span>
                  <?php endif; ?>

                  <!-- Title -->
                  <h2 class="text-xl font-bold mb-3 text-white">
                    <a href="<?= bbx_get_blog_post_url($post['slug']) ?>" class="hover:text-amber-400 transition-colors">
                      <?= htmlspecialchars($post['title']) ?>
                    </a>
                  </h2>

                  <!-- Excerpt -->
                  <?php if ($post['excerpt']): ?>
                    <p class="text-gray-300 mb-4 line-clamp-3">
                      <?= htmlspecialchars($post['excerpt']) ?>
                    </p>
                  <?php endif; ?>

                  <!-- Meta Info -->
                  <div class="flex items-center justify-between text-sm text-gray-400">
                    <div class="flex items-center gap-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                      </svg>
                      <span><?= bbx_format_blog_date($post['publish_date']) ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                      <span><?= number_format($post['views']) ?> <?= t('blog.views') ?></span>
                    </div>
                  </div>

                  <!-- Read More Button -->
                  <a href="<?= bbx_get_blog_post_url($post['slug']) ?>"
                    class="inline-flex items-center mt-4 text-amber-400 font-semibold hover:text-amber-300 transition-colors">
                    <?= t('blog.read_more') ?>
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                  </a>
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

    <!-- Newsletter CTA -->
    <section class="py-16 bg-gradient-to-r from-amber-400/10 to-amber-600/10 border-y border-amber-400/20">
      <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto text-center">
          <h3 class="text-2xl font-bold mb-4"><?= t('blog.newsletter.title') ?></h3>
          <p class="text-gray-300 mb-6"><?= t('blog.newsletter.description') ?></p>
          <form id="blog-newsletter-form" class="flex flex-col sm:flex-row gap-3">
            <input type="email"
              name="email"
              placeholder="<?= htmlspecialchars(t('blog.newsletter.placeholder')) ?>"
              required
              class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
            <button type="submit"
              class="px-8 py-3 bg-amber-400 text-black font-semibold rounded-lg hover:bg-amber-500 transition-colors">
              <?= t('blog.newsletter.button') ?>
            </button>
          </form>
        </div>
      </div>
    </section>
  <?php endif; ?>
</main>

<style>
  .line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
</style>

<?php include 'includes/site-footer.php'; ?>
