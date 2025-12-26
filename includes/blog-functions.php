<?php

/**
 * Blog Functions - Helper functions for blog CMS
 * Blackbox EYE - Sprint 5 + Intel Engine
 *
 * @version 2.0
 * @date 2025-12-26
 * 
 * Supports both database-driven (legacy) and JSON-driven (new) blog posts.
 */

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/i18n.php';

/**
 * Load blog posts from JSON file (new data-driven approach)
 *
 * @return array Parsed posts data or empty structure
 */
function bbx_load_blog_posts_json(): array
{
  $json_path = __DIR__ . '/../data/blog/posts.json';
  
  if (!file_exists($json_path)) {
    error_log('[Blog] posts.json not found at: ' . $json_path);
    return [
      'version' => '1.0.0',
      'generated_at' => null,
      'pipeline_version' => null,
      'metadata' => ['total_posts' => 0, 'regions' => [], 'date_range' => ['earliest' => null, 'latest' => null]],
      'posts' => []
    ];
  }
  
  $json_content = file_get_contents($json_path);
  $data = json_decode($json_content, true);
  
  if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('[Blog] Failed to parse posts.json: ' . json_last_error_msg());
    return [
      'version' => '1.0.0',
      'generated_at' => null,
      'pipeline_version' => null,
      'metadata' => ['total_posts' => 0, 'regions' => [], 'date_range' => ['earliest' => null, 'latest' => null]],
      'posts' => []
    ];
  }
  
  return $data;
}

/**
 * Get blog posts from JSON with pagination and filtering
 *
 * @param int $page Current page number
 * @param int $per_page Posts per page
 * @param string|null $region Filter by region
 * @param string|null $tag Filter by tag
 * @return array Array of blog posts
 */
function bbx_get_blog_posts_from_json(int $page = 1, int $per_page = 10, ?string $region = null, ?string $tag = null): array
{
  $data = bbx_load_blog_posts_json();
  $posts = $data['posts'] ?? [];
  
  // Apply filters
  if ($region !== null) {
    $posts = array_filter($posts, function($post) use ($region) {
      return isset($post['region']) && $post['region'] === $region;
    });
  }
  
  if ($tag !== null) {
    $posts = array_filter($posts, function($post) use ($tag) {
      return isset($post['tags']) && in_array($tag, $post['tags']);
    });
  }
  
  // Sort by published_at descending
  usort($posts, function($a, $b) {
    $time_a = isset($a['published_at']) ? strtotime($a['published_at']) : 0;
    $time_b = isset($b['published_at']) ? strtotime($b['published_at']) : 0;
    return $time_b - $time_a;
  });
  
  // Paginate
  $offset = ($page - 1) * $per_page;
  return array_slice($posts, $offset, $per_page);
}

/**
 * Get total count of posts from JSON
 *
 * @param string|null $region Filter by region
 * @param string|null $tag Filter by tag
 * @return int Total count
 */
function bbx_get_blog_posts_json_count(?string $region = null, ?string $tag = null): int
{
  $data = bbx_load_blog_posts_json();
  $posts = $data['posts'] ?? [];
  
  // Apply filters
  if ($region !== null) {
    $posts = array_filter($posts, function($post) use ($region) {
      return isset($post['region']) && $post['region'] === $region;
    });
  }
  
  if ($tag !== null) {
    $posts = array_filter($posts, function($post) use ($tag) {
      return isset($post['tags']) && in_array($tag, $post['tags']);
    });
  }
  
  return count($posts);
}

/**
 * Get all unique tags from JSON posts
 *
 * @return array Array of tags
 */
function bbx_get_blog_tags_from_json(): array
{
  $data = bbx_load_blog_posts_json();
  $posts = $data['posts'] ?? [];
  $tags = [];
  
  foreach ($posts as $post) {
    if (isset($post['tags']) && is_array($post['tags'])) {
      $tags = array_merge($tags, $post['tags']);
    }
  }
  
  $tags = array_unique($tags);
  sort($tags);
  
  return $tags;
}

/**
 * Get all unique regions from JSON posts
 *
 * @return array Array of regions
 */
function bbx_get_blog_regions_from_json(): array
{
  $data = bbx_load_blog_posts_json();
  $posts = $data['posts'] ?? [];
  $regions = [];
  
  foreach ($posts as $post) {
    if (isset($post['region'])) {
      $regions[] = $post['region'];
    }
  }
  
  $regions = array_unique($regions);
  sort($regions);
  
  return $regions;
}

/**
 * Ensure that a PDO instance is available before running queries.
 *
 * @param string $context Helpful identifier for error logs
 * @return PDO
 * @throws RuntimeException when the PDO instance is missing
 */
function bbx_require_pdo(string $context): PDO
{
  global $pdo;

  if ($pdo instanceof PDO) {
    return $pdo;
  }

  $message = "[DB] PDO instance is not available ({$context}).";
  error_log($message);
  throw new RuntimeException($message);
}

/**
 * Get all published blog posts with pagination
 *
 * @param int $page Current page number
 * @param int $per_page Posts per page
 * @param string|null $category Filter by category
 * @return array Array of blog posts
 */
function bbx_get_blog_posts(int $page = 1, int $per_page = 10, ?string $category = null): array
{
  $pdo = bbx_require_pdo(__FUNCTION__);

  $offset = ($page - 1) * $per_page;
  $lang = bbx_get_language();

  $sql = "
        SELECT
            id,
            slug,
            " . ($lang === 'da' ? 'title_da AS title' : 'title_en AS title') . ",
            " . ($lang === 'da' ? 'excerpt_da AS excerpt' : 'excerpt_en AS excerpt') . ",
            featured_image,
            category,
            tags,
            author,
            publish_date,
            views
        FROM blog_posts
        WHERE status = 'published'
        AND publish_date <= NOW()
    ";

  if ($category) {
    $sql .= " AND category = :category";
  }

  $sql .= " ORDER BY publish_date DESC LIMIT :limit OFFSET :offset";

  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

  if ($category) {
    $stmt->bindValue(':category', $category, PDO::PARAM_STR);
  }

  $stmt->execute();
  $posts = $stmt->fetchAll();

  // Decode JSON tags
  foreach ($posts as &$post) {
    $post['tags'] = json_decode($post['tags'], true) ?? [];
  }

  return $posts;
}

/**
 * Get single blog post by slug
 *
 * @param string $slug Post slug
 * @return array|null Blog post data or null
 */
function bbx_get_blog_post(string $slug): ?array
{
  $pdo = bbx_require_pdo(__FUNCTION__);

  $lang = bbx_get_language();

  $sql = "
        SELECT
            id,
            slug,
            " . ($lang === 'da' ? 'title_da AS title' : 'title_en AS title') . ",
            " . ($lang === 'da' ? 'content_da AS content' : 'content_en AS content') . ",
            " . ($lang === 'da' ? 'excerpt_da AS excerpt' : 'excerpt_en AS excerpt') . ",
            " . ($lang === 'da' ? 'meta_description_da AS meta_description' : 'meta_description_en AS meta_description') . ",
            featured_image,
            category,
            tags,
            author,
            publish_date,
            views
        FROM blog_posts
        WHERE slug = :slug
        AND status = 'published'
        AND publish_date <= NOW()
        LIMIT 1
    ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute(['slug' => $slug]);
  $post = $stmt->fetch();

  if ($post) {
    $post['tags'] = json_decode($post['tags'], true) ?? [];

    // Increment view count
    $update = $pdo->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?");
    $update->execute([$post['id']]);
  }

  return $post ?: null;
}

/**
 * Get total count of published posts
 *
 * @param string|null $category Filter by category
 * @return int Total count
 */
function bbx_get_blog_posts_count(?string $category = null): int
{
  $pdo = bbx_require_pdo(__FUNCTION__);

  $sql = "SELECT COUNT(*) FROM blog_posts WHERE status = 'published' AND publish_date <= NOW()";

  if ($category) {
    $sql .= " AND category = :category";
  }

  $stmt = $pdo->prepare($sql);

  if ($category) {
    $stmt->execute(['category' => $category]);
  } else {
    $stmt->execute();
  }

  return (int) $stmt->fetchColumn();
}

/**
 * Get all unique categories
 *
 * @return array Array of categories
 */
function bbx_get_blog_categories(): array
{
  $pdo = bbx_require_pdo(__FUNCTION__);

  $sql = "
        SELECT DISTINCT category
        FROM blog_posts
        WHERE status = 'published'
        AND category IS NOT NULL
        ORDER BY category
    ";

  $stmt = $pdo->query($sql);
  return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Get related blog posts
 *
 * @param int $post_id Current post ID
 * @param int $limit Number of related posts
 * @return array Array of related posts
 */
function bbx_get_related_posts(int $post_id, int $limit = 3): array
{
  $pdo = bbx_require_pdo(__FUNCTION__);
  $lang = bbx_get_language();

  // Get current post's category
  $current = $pdo->prepare("SELECT category, tags FROM blog_posts WHERE id = ?");
  $current->execute([$post_id]);
  $current_post = $current->fetch();

  if (!$current_post) {
    return [];
  }

  $sql = "
        SELECT
            id,
            slug,
            " . ($lang === 'da' ? 'title_da AS title' : 'title_en AS title') . ",
            " . ($lang === 'da' ? 'excerpt_da AS excerpt' : 'excerpt_en AS excerpt') . ",
            featured_image,
            category,
            publish_date
        FROM blog_posts
        WHERE status = 'published'
        AND id != :post_id
        AND category = :category
        ORDER BY publish_date DESC
        LIMIT :limit
    ";

  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
  $stmt->bindValue(':category', $current_post['category'], PDO::PARAM_STR);
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->execute();

  return $stmt->fetchAll();
}

/**
 * Format blog date for display
 *
 * @param string $date Date string
 * @return string Formatted date
 */
function bbx_format_blog_date(string $date): string
{
  $lang = bbx_get_language();
  $timestamp = strtotime($date);

  if ($lang === 'da') {
    $months = [
      'January' => 'januar',
      'February' => 'februar',
      'March' => 'marts',
      'April' => 'april',
      'May' => 'maj',
      'June' => 'juni',
      'July' => 'juli',
      'August' => 'august',
      'September' => 'september',
      'October' => 'oktober',
      'November' => 'november',
      'December' => 'december'
    ];

    $formatted = date('j. F Y', $timestamp);
    return str_replace(array_keys($months), array_values($months), $formatted);
  }

  return date('F j, Y', $timestamp);
}

/**
 * Generate blog post URL
 *
 * @param string $slug Post slug
 * @return string Full URL
 */
function bbx_get_blog_post_url(string $slug): string
{
  return BBX_SITE_BASE_URL . '/blog-post.php?slug=' . urlencode($slug);
}

/**
 * Generate pagination HTML
 *
 * @param int $current_page Current page
 * @param int $total_pages Total pages
 * @param string $base_url Base URL for pagination links
 * @return string Pagination HTML
 */
function bbx_blog_pagination(int $current_page, int $total_pages, string $base_url): string
{
  if ($total_pages <= 1) {
    return '';
  }

  $html = '<nav class="flex justify-center items-center gap-2 mt-12" aria-label="Blog pagination">';

  // Previous button
  if ($current_page > 1) {
    $prev_url = $base_url . '?page=' . ($current_page - 1);
    $html .= '<a href="' . htmlspecialchars($prev_url) . '" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">' . t('blog.pagination.previous') . '</a>';
  }

  // Page numbers
  $range = 2; // Show 2 pages on each side of current
  for ($i = max(1, $current_page - $range); $i <= min($total_pages, $current_page + $range); $i++) {
    if ($i === $current_page) {
      $html .= '<span class="px-4 py-2 bg-amber-400 text-black rounded-lg font-semibold">' . $i . '</span>';
    } else {
      $page_url = $base_url . '?page=' . $i;
      $html .= '<a href="' . htmlspecialchars($page_url) . '" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">' . $i . '</a>';
    }
  }

  // Next button
  if ($current_page < $total_pages) {
    $next_url = $base_url . '?page=' . ($current_page + 1);
    $html .= '<a href="' . htmlspecialchars($next_url) . '" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">' . t('blog.pagination.next') . '</a>';
  }

  $html .= '</nav>';

  return $html;
}
