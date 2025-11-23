<?php

/**
 * Dynamic XML Sitemap for Blackbox EYE
 * Generates sitemap.xml with all public pages
 *
 * @version 1.0
 * @date 2025-11-23
 */

require_once __DIR__ . '/includes/env.php';
require_once __DIR__ . '/db.php';

// Set XML content type
header('Content-Type: application/xml; charset=utf-8');

// Output XML declaration
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
  xmlns:xhtml="http://www.w3.org/1999/xhtml">

  <!-- Homepage -->
  <url>
    <loc>https://blackbox.codes/</loc>
    <lastmod><?= date('Y-m-d') ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
    <xhtml:link rel="alternate" hreflang="da-DK" href="https://blackbox.codes/?lang=da" />
    <xhtml:link rel="alternate" hreflang="en" href="https://blackbox.codes/?lang=en" />
  </url>

  <!-- Main Pages -->
  <?php
  $pages = [
    ['slug' => 'about', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['slug' => 'products', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['slug' => 'cases', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['slug' => 'pricing', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['slug' => 'contact', 'priority' => '0.8', 'changefreq' => 'monthly'],
  ];

  foreach ($pages as $page): ?>
    <url>
      <loc>https://blackbox.codes/<?= $page['slug'] ?>.php</loc>
      <lastmod><?= date('Y-m-d') ?></lastmod>
      <changefreq><?= $page['changefreq'] ?></changefreq>
      <priority><?= $page['priority'] ?></priority>
      <xhtml:link rel="alternate" hreflang="da-DK" href="https://blackbox.codes/<?= $page['slug'] ?>.php?lang=da" />
      <xhtml:link rel="alternate" hreflang="en" href="https://blackbox.codes/<?= $page['slug'] ?>.php?lang=en" />
    </url>
  <?php endforeach; ?>

  <?php
  // Blog posts (dynamically generated)
  try {
    $blog_stmt = $pdo->query("
      SELECT slug, updated_at
      FROM blog_posts
      WHERE status = 'published'
      AND publish_date <= NOW()
      ORDER BY publish_date DESC
    ");

    while ($post = $blog_stmt->fetch()): ?>
      <url>
        <loc>https://blackbox.codes/blog-post.php?slug=<?= htmlspecialchars($post['slug']) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($post['updated_at'])) ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
        <xhtml:link rel="alternate" hreflang="da-DK" href="https://blackbox.codes/blog-post.php?slug=<?= htmlspecialchars($post['slug']) ?>&lang=da" />
        <xhtml:link rel="alternate" hreflang="en" href="https://blackbox.codes/blog-post.php?slug=<?= htmlspecialchars($post['slug']) ?>&lang=en" />
      </url>
  <?php endwhile;
  } catch (PDOException $e) {
    // Silently fail if table doesn't exist yet
  }
  ?>

  <!-- Blog listing page -->
  <url>
    <loc>https://blackbox.codes/blog.php</loc>
    <lastmod><?= date('Y-m-d') ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
    <xhtml:link rel="alternate" hreflang="da-DK" href="https://blackbox.codes/blog.php?lang=da" />
    <xhtml:link rel="alternate" hreflang="en" href="https://blackbox.codes/blog.php?lang=en" />
  </url>

  <!-- FAQ page -->
  <url>
    <loc>https://blackbox.codes/faq.php</loc>
    <lastmod><?= date('Y-m-d') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
    <xhtml:link rel="alternate" hreflang="da-DK" href="https://blackbox.codes/faq.php?lang=da" />
    <xhtml:link rel="alternate" hreflang="en" href="https://blackbox.codes/faq.php?lang=en" />
  </url>

</urlset>
