<?php

/**
 * Graphene Stats Counter Component
 *
 * Reusable stats counter with icons for the Graphene design system.
 *
 * Usage:
 *   $stats_items = [
 *       ['icon' => 'shield', 'value' => '2.4M+', 'label' => 'Threats Neutralized'],
 *       ['icon' => 'clock', 'value' => '99.97%', 'label' => 'Uptime'],
 *       ['icon' => 'bolt', 'value' => '<50ms', 'label' => 'Response Time'],
 *   ];
 *   include 'includes/components/stats-counter.php';
 *
 * @var array $stats_items Optional array of stats with icon, value, label keys
 * @var string $stats_aria_label Optional ARIA label for the stats container
 */

// Default stats items if not provided
if (!isset($stats_items) || empty($stats_items)) {
  $stats_items = [
    [
      'icon' => 'shield',
      'value' => t('home.hero.stats.threats_value') ?: '2.4M+',
      'label' => t('home.hero.stats.threats'),
    ],
    [
      'icon' => 'clock',
      'value' => t('home.hero.stats.uptime_value') ?: '99.97%',
      'label' => t('home.hero.stats.uptime'),
    ],
    [
      'icon' => 'bolt',
      'value' => t('home.hero.stats.response_value') ?: '<50ms',
      'label' => t('home.hero.stats.response'),
    ],
  ];
}

$stats_aria_label = $stats_aria_label ?? t('home.hero.stats_aria');

// Icon SVG paths
$icons = [
  'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />',
  'clock' => '<circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" />',
  'bolt' => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />',
  'check' => '<polyline points="20 6 9 17 4 12" />',
  'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />',
  'globe' => '<circle cx="12" cy="12" r="10" /><line x1="2" y1="12" x2="22" y2="12" /><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />',
  'target' => '<circle cx="12" cy="12" r="10" /><circle cx="12" cy="12" r="6" /><circle cx="12" cy="12" r="2" />',
];
?>

<div class="graphene-stats" role="list" aria-label="<?= htmlspecialchars($stats_aria_label) ?>">
  <?php foreach ($stats_items as $item): ?>
    <div class="graphene-stats__item" role="listitem">
      <div class="graphene-stats__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <?= $icons[$item['icon']] ?? $icons['shield'] ?>
        </svg>
      </div>
      <div class="graphene-stats__content">
        <div class="graphene-stats__value"><?= htmlspecialchars($item['value']) ?></div>
        <div class="graphene-stats__label"><?= htmlspecialchars($item['label']) ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
