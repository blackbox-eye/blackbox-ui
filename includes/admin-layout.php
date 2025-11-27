<?php

/**
 * Admin Layout Template
 *
 * Fælles layout for alle admin-sider (dashboard, admin, settings osv.)
 * Inkluderer: Header, command deck menu, main content area, footer
 *
 * Variabler der kan sættes før include:
 * - $page_title: Sidetitel
 * - $current_admin_page: Slug for aktiv side (dashboard, admin, settings osv.)
 * - $admin_content: Sti til content partial ELLER brug output buffering
 * - $hide_command_deck: Sæt til true for at skjule menuen (f.eks. på login)
 */

// Start session hvis ikke allerede startet
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Hent environment og i18n
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/i18n.php';

// Definer standard variabler
$page_title = $page_title ?? 'Admin Panel';
$current_admin_page = $current_admin_page ?? '';
$hide_command_deck = $hide_command_deck ?? false;
$meta_robots = 'noindex, nofollow';

// Admin navigation items
$admin_nav_items = [
  [
    'slug' => 'dashboard',
    'label' => 'Dashboard',
    'href' => 'dashboard.php',
    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12l9-9 9 9"/><path d="M9 21V9h6v12"/></svg>'
  ],
  [
    'slug' => 'access-requests',
    'label' => 'Adgangsanmodninger',
    'href' => 'access-requests.php',
    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>',
    'admin_only' => true
  ],
  [
    'slug' => 'intel-vault',
    'label' => 'Intel Vault',
    'href' => 'intel-vault.php',
    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><circle cx="12" cy="16" r="1"/></svg>'
  ],
  [
    'slug' => 'api-keys',
    'label' => 'API & Keys',
    'href' => 'api-keys.php',
    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>'
  ],
  [
    'slug' => 'admin',
    'label' => 'Brugerstyring',
    'href' => 'admin.php',
    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'admin_only' => true
  ],
  [
    'slug' => 'download-logs',
    'label' => 'Systemlogs',
    'href' => 'download-logs.php',
    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>'
  ],
  [
    'slug' => 'settings',
    'label' => 'Indstillinger',
    'href' => 'settings.php',
    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>'
  ]
];

// Funktion til at tjekke aktiv side
function admin_is_active(string $slug, string $current): bool
{
  return $slug === $current;
}
?>
<!DOCTYPE html>
<html lang="da" data-theme="dark">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="<?= htmlspecialchars($meta_robots) ?>">
  <title><?= htmlspecialchars($page_title) ?> - GreyEYE Admin</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin="anonymous">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@400;500;700&display=swap" rel="stylesheet" crossorigin="anonymous">

  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_white_32x32.png">
  <link rel="icon" type="image/png" sizes="192x192" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_white_256x256.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_white_256x256.png">
  <link rel="shortcut icon" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_white.ico">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="/assets/css/tailwind.full.css">
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>

<body class="admin-body">
  <!-- Skip Navigation Link -->
  <a href="#main-content" class="skip-link">Spring til indhold</a>

  <?php if (!$hide_command_deck): ?>
    <!-- Command Deck Launcher Button -->
    <button type="button"
      class="command-deck-launcher"
      id="commandDeckLauncher"
      aria-label="Åbn kommandopanel"
      aria-expanded="false"
      aria-controls="commandDeckMenu">
      <span class="command-deck-launcher__icon" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="3" y1="12" x2="21" y2="12" />
          <line x1="3" y1="6" x2="21" y2="6" />
          <line x1="3" y1="18" x2="21" y2="18" />
        </svg>
      </span>
      <span class="command-deck-launcher__label">Menu</span>
    </button>

    <!-- Command Deck Overlay -->
    <div class="command-deck-overlay" id="commandDeckOverlay" aria-hidden="true"></div>

    <!-- Command Deck Slide-in Menu -->
    <nav class="command-deck"
      id="commandDeckMenu"
      aria-label="GreyEYE Admin Navigation"
      aria-hidden="true">

      <!-- Close Button -->
      <button type="button"
        class="command-deck__close"
        id="commandDeckClose"
        aria-label="Luk menu">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18" />
          <line x1="6" y1="6" x2="18" y2="18" />
        </svg>
      </button>

      <!-- Brand Section -->
      <div class="command-deck__brand">
        <img src="/assets/greyeeye_logo_transparent.png"
          alt="GreyEYE Logo"
          class="command-deck__logo"
          loading="lazy">
        <div class="command-deck__brand-text">
          <span class="command-deck__brand-label">GreyEYE</span>
          <strong class="command-deck__brand-title">Command Deck</strong>
        </div>
      </div>

      <!-- Agent Info -->
      <?php if (isset($_SESSION['agent_id'])): ?>
        <div class="command-deck__agent">
          <span class="command-deck__agent-label">Agent:</span>
          <span class="command-deck__agent-id"><?= htmlspecialchars($_SESSION['agent_id']) ?></span>
          <?php if (!empty($_SESSION['is_admin'])): ?>
            <span class="command-deck__agent-badge">Admin</span>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- Navigation Heading -->
      <p class="command-deck__heading">Navigator</p>

      <!-- Navigation List -->
      <div class="command-deck__nav">
        <?php foreach ($admin_nav_items as $item): ?>
          <?php
          // Skip admin-only items for non-admins
          if (!empty($item['admin_only']) && empty($_SESSION['is_admin'])) continue;

          $is_active = admin_is_active($item['slug'], $current_admin_page);
          ?>
          <a href="<?= htmlspecialchars($item['href']) ?>"
            class="command-deck__item <?= $is_active ? 'is-active' : '' ?>"
            <?= $is_active ? 'aria-current="page"' : '' ?>>
            <span class="command-deck__item-icon" aria-hidden="true">
              <?= $item['icon'] ?>
            </span>
            <span class="command-deck__item-label"><?= htmlspecialchars($item['label']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Divider -->
      <div class="command-deck__divider"></div>

      <!-- Quick Actions -->
      <div class="command-deck__actions">
        <a href="/" class="command-deck__action" title="Gå til hjemmesiden">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
            <polyline points="9 22 9 12 15 12 15 22" />
          </svg>
          <span>Hjemmeside</span>
        </a>
        <a href="logout.php" class="command-deck__action command-deck__action--danger" title="Log ud">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" y1="12" x2="9" y2="12" />
          </svg>
          <span>Log ud</span>
        </a>
      </div>

      <!-- Footer -->
      <div class="command-deck__footer">
        <span class="command-deck__status-dot"></span>
        <span class="command-deck__status-text">System online</span>
      </div>
    </nav>
  <?php endif; ?>

  <!-- Main Content Area -->
  <main id="main-content" class="admin-main <?= $hide_command_deck ? 'admin-main--full' : '' ?>">
