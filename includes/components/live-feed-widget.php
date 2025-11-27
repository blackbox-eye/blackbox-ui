<?php

/**
 * Live Feed Widget 2.0 Component
 *
 * Reusable live threat feed widget with severity tags.
 * Part of the Graphene design system.
 *
 * Usage:
 *   $feed_items = [
 *       ['severity' => 'critical', 'event' => 'Ransomware blocked', 'meta' => 'Pattern #4821', 'time' => '2m'],
 *       ['severity' => 'warning', 'event' => 'Lateral movement detected', 'meta' => '203.0.113.42', 'time' => '5m'],
 *       ['severity' => 'info', 'event' => 'Shadow IT discovery', 'meta' => 'Ports 22, 443', 'time' => '12m'],
 *   ];
 *   include 'includes/components/live-feed-widget.php';
 *
 * @var array $feed_items Optional array of feed items with severity, event, meta, time keys
 * @var string $feed_title Optional custom title (defaults to i18n key)
 */

// Default feed items if not provided
if (!isset($feed_items) || empty($feed_items)) {
  $feed_items = [
    [
      'severity' => 'critical',
      'event' => t('home.hero.feed.malware'),
      'meta' => 'Quarantined • Pattern #4821',
      'time' => '2m'
    ],
    [
      'severity' => 'warning',
      'event' => t('home.hero.feed.login'),
      'meta' => '203.0.113.42 • Copenhagen, DK',
      'time' => '5m'
    ],
    [
      'severity' => 'info',
      'event' => t('home.hero.feed.scan'),
      'meta' => 'Ports 22, 443, 3306 • Berlin, DE',
      'time' => '12m'
    ],
  ];
}

$feed_title = $feed_title ?? t('home.hero.live_feed_title');

// Severity labels for ARIA
$severity_labels = [
  'critical' => 'Critical severity',
  'warning' => 'Warning severity',
  'info' => 'Info severity',
];
?>

<div class="live-feed-2" id="heroLiveFeed" aria-live="polite" aria-atomic="false">
  <div class="live-feed-2__badge">
    <span class="live-feed-2__pulse" aria-hidden="true"></span>
    <span>LIVE</span>
  </div>
  <div class="live-feed-2__header">
    <span class="live-feed-2__title"><?= htmlspecialchars($feed_title) ?></span>
  </div>
  <div class="live-feed-2__items" id="liveFeedItems">
    <?php foreach ($feed_items as $item): ?>
      <div class="live-feed-2__item live-feed-2__item--<?= htmlspecialchars($item['severity']) ?>">
        <span class="severity-tag severity-tag--<?= htmlspecialchars($item['severity']) ?>"
          role="status"
          aria-label="<?= htmlspecialchars($severity_labels[$item['severity']] ?? $item['severity']) ?>">
          <?= strtoupper(htmlspecialchars($item['severity'])) ?>
        </span>
        <div class="live-feed-2__content">
          <div class="live-feed-2__event"><?= htmlspecialchars($item['event']) ?></div>
          <div class="live-feed-2__meta"><?= htmlspecialchars($item['meta']) ?></div>
        </div>
        <span class="live-feed-2__time"><?= htmlspecialchars($item['time']) ?></span>
      </div>
    <?php endforeach; ?>
  </div>
</div>
