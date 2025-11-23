<?php
/**
 * Blog Functions - Helper functions for blog CMS
 * Blackbox EYE - Sprint 4
 * 
 * @version 1.0
 * @date 2025-11-23
 */

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/i18n.php';

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
    global $pdo;
    
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
    global $pdo;
    
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
    global $pdo;
    
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
    global $pdo;
    
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
    global $pdo;
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
            'January' => 'januar', 'February' => 'februar', 'March' => 'marts',
            'April' => 'april', 'May' => 'maj', 'June' => 'juni',
            'July' => 'juli', 'August' => 'august', 'September' => 'september',
            'October' => 'oktober', 'November' => 'november', 'December' => 'december'
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
