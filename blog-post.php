<?php

/**
 * Individual Blog Post Page - Blackbox EYE
 * Sprint 4: Blog CMS System
 *
 * @version 1.0
 * @date 2025-11-23
 */

require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/blog-functions.php';
require_once __DIR__ . '/db.php';

$current_page = 'blog';

// Get post by slug
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$post = $slug ? bbx_get_blog_post($slug) : null;

// 404 if post not found
if (!$post) {
  header('HTTP/1.0 404 Not Found');
  $page_title = t('blog.post.not_found');
  $meta_description = t('blog.meta.description');
  include 'includes/site-header.php';
?>
  <main id="main-content" class="pt-24 pb-16">
    <div class="container mx-auto px-4">
      <div class="max-w-2xl mx-auto text-center py-16">
        <h1 class="text-4xl font-bold mb-6"><?= t('blog.post.not_found') ?></h1>
        <p class="text-xl text-gray-400 mb-8"><?= t('blog.post.not_found_desc') ?></p>
        <a href="blog.php" class="inline-block px-6 py-3 border-2" style="background: rgba(212, 175, 55, 0.1); border-color: var(--primary-accent); color: var(--primary-accent); text-black font-semibold rounded-lg hover:bg-amber-500 transition-colors">
          <?= t('blog.post.back_to_blog') ?>
        </a>
      </div>
    </div>
  </main>
<?php
  include 'includes/site-footer.php';
  exit;
}

// Get related posts
$related_posts = bbx_get_related_posts($post['id'], 3);

// Set page meta
$page_title = htmlspecialchars($post['title']) . ' - Blackbox EYE Blog';
$meta_description = $post['meta_description'] ?: $post['excerpt'];
$meta_og_title = $post['title'];
$meta_og_description = $post['meta_description'] ?: $post['excerpt'];
$meta_og_image = $post['featured_image'] ?: BBX_SITE_BASE_URL . '/assets/logo.png';
$meta_og_type = 'article';

// Structured data for BlogPosting
$structured_data = [
  '@context' => 'https://schema.org',
  '@type' => 'BlogPosting',
  'headline' => $post['title'],
  'image' => $post['featured_image'] ?: BBX_SITE_BASE_URL . '/assets/logo.png',
  'author' => [
    '@type' => 'Organization',
    'name' => $post['author']
  ],
  'publisher' => [
    '@type' => 'Organization',
    'name' => 'Blackbox EYE',
    'logo' => [
      '@type' => 'ImageObject',
      'url' => BBX_SITE_BASE_URL . '/assets/logo.png'
    ]
  ],
  'datePublished' => date('c', strtotime($post['publish_date'])),
  'dateModified' => date('c', strtotime($post['publish_date'])),
  'description' => $post['meta_description'] ?: $post['excerpt'],
];

include 'includes/site-header.php';
?>

<main id="main-content" class="pt-24 pb-16">
  <!-- Article Header -->
  <article class="py-8">
    <div class="container mx-auto px-4">
      <div class="max-w-4xl mx-auto">
        <!-- Breadcrumbs -->
        <nav class="mb-6 text-sm" aria-label="Breadcrumb">
          <ol class="flex items-center gap-2 text-gray-400">
            <li><a href="index.php" class="hover:" style="color: var(--primary-accent);"><?= t('common.home') ?></a></li>
            <li>/</li>
            <li><a href="blog.php" class="hover:" style="color: var(--primary-accent);"><?= t('blog.title') ?></a></li>
            <?php if ($post['category']): ?>
              <li>/</li>
              <li><a href="blog.php?category=<?= urlencode($post['category']) ?>" class="hover:" style="color: var(--primary-accent);"><?= htmlspecialchars($post['category']) ?></a></li>
            <?php endif; ?>
            <li>/</li>
            <li class="text-white truncate max-w-xs"><?= htmlspecialchars($post['title']) ?></li>
          </ol>
        </nav>

        <!-- Category Badge -->
        <?php if ($post['category']): ?>
          <span class="inline-block px-4 py-1 text-sm font-semibold rounded-full mb-4" style="background: rgba(212, 175, 55, 0.15); color: var(--primary-accent);">
            <?= htmlspecialchars($post['category']) ?>
          </span>
        <?php endif; ?>

        <!-- Title -->
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-6 leading-tight">
          <?= htmlspecialchars($post['title']) ?>
        </h1>

        <!-- Meta Info -->
        <div class="flex flex-wrap items-center gap-6 text-gray-400 mb-8 pb-8 border-b border-gray-800">
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span><?= htmlspecialchars($post['author']) ?></span>
          </div>
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <time datetime="<?= date('Y-m-d', strtotime($post['publish_date'])) ?>">
              <?= bbx_format_blog_date($post['publish_date']) ?>
            </time>
          </div>
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            <span><?= number_format($post['views']) ?> <?= t('blog.views') ?></span>
          </div>
        </div>

        <!-- Featured Image -->
        <?php if ($post['featured_image']): ?>
          <div class="aspect-video bg-gray-800 rounded-2xl overflow-hidden mb-12">
            <img src="<?= htmlspecialchars($post['featured_image']) ?>"
              alt="<?= htmlspecialchars($post['title']) ?>"
              class="w-full h-full object-cover"
              loading="lazy"
              decoding="async">
          </div>
        <?php endif; ?>

        <!-- Article Content -->
        <div class="prose prose-invert prose-lg max-w-none mb-12">
          <?= $post['content'] ?>
        </div>

        <!-- Tags -->
        <?php if (!empty($post['tags'])): ?>
          <div class="flex flex-wrap gap-2 mb-12 pb-12 border-b border-gray-800">
            <span class="text-gray-400 font-semibold mr-2"><?= t('blog.post.tags') ?>:</span>
            <?php foreach ($post['tags'] as $tag): ?>
              <span class="px-3 py-1 text-sm bg-gray-800 text-gray-300 rounded-full">
                <?= htmlspecialchars($tag) ?>
              </span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Social Share -->
        <div class="mb-12 pb-12 border-b border-gray-800">
          <p class="text-gray-400 font-semibold mb-4"><?= t('blog.post.share') ?>:</p>
          <div class="flex gap-3">
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(bbx_get_blog_post_url($post['slug'])) ?>"
              target="_blank"
              rel="noopener noreferrer"
              class="flex items-center gap-2 px-4 py-2 bg-[#0077B5] hover:bg-[#006399] text-white rounded-lg transition-colors">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
              </svg>
              LinkedIn
            </a>
            <a href="https://twitter.com/intent/tweet?url=<?= urlencode(bbx_get_blog_post_url($post['slug'])) ?>&text=<?= urlencode($post['title']) ?>"
              target="_blank"
              rel="noopener noreferrer"
              class="flex items-center gap-2 px-4 py-2 bg-[#1DA1F2] hover:bg-[#1a8cd8] text-white rounded-lg transition-colors">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
              </svg>
              Twitter
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Related Posts -->
    <?php if (!empty($related_posts)): ?>
      <div class="bg-gray-900/30 py-16 mt-12">
        <div class="container mx-auto px-4">
          <div class="max-w-4xl mx-auto">
            <h2 class="text-2xl font-bold mb-8"><?= t('blog.post.related') ?></h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <?php foreach ($related_posts as $related): ?>
                <article class="glass-effect rounded-xl overflow-hidden hover:scale-105 transition-transform">
                  <?php if ($related['featured_image']): ?>
                    <div class="aspect-video bg-gray-800 overflow-hidden">
                      <img src="<?= htmlspecialchars($related['featured_image']) ?>"
                        alt="<?= htmlspecialchars($related['title']) ?>"
                        loading="lazy"
                        decoding="async"
                        class="w-full h-full object-cover">
                    </div>
                  <?php endif; ?>
                  <div class="p-4">
                    <h3 class="font-bold mb-2 text-white hover:" style="color: var(--primary-accent); transition-colors">
                      <a href="<?= bbx_get_blog_post_url($related['slug']) ?>">
                        <?= htmlspecialchars($related['title']) ?>
                      </a>
                    </h3>
                    <p class="text-sm text-gray-400">
                      <?= bbx_format_blog_date($related['publish_date']) ?>
                    </p>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </article>
</main>

<style>
  /* Prose styling for blog content */
  .prose-invert {
    color: #e5e7eb;
  }

  .prose-invert h2 {
    color: #fbbf24;
    font-size: 1.875rem;
    font-weight: 700;
    margin-top: 2rem;
    margin-bottom: 1rem;
  }

  .prose-invert h3 {
    color: #fbbf24;
    font-size: 1.5rem;
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
  }

  .prose-invert p {
    margin-bottom: 1.5rem;
    line-height: 1.75;
  }

  .prose-invert ul,
  .prose-invert ol {
    margin-left: 1.5rem;
    margin-bottom: 1.5rem;
  }

  .prose-invert li {
    margin-bottom: 0.5rem;
  }

  .prose-invert strong {
    color: white;
    font-weight: 600;
  }

  .prose-invert a {
    color: #fbbf24;
    text-decoration: underline;
  }

  .prose-invert a:hover {
    color: #f59e0b;
  }
</style>

<?php include 'includes/site-footer.php'; ?>
