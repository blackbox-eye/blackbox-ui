<?php

/**
 * API Keys Management Page
 *
 * Manage API keys for external integrations and services
 * Displays list of API keys with creation, rotation, and revocation capabilities
 */

session_start();

// Check authentication
if (!isset($_SESSION['agent_id'])) {
  header('Location: agent-login.php');
  exit;
}

// Page configuration
$page_title = 'API & Keys';
$current_admin_page = 'api-keys';

// Dummy API keys data for placeholder
$api_keys = [
  [
    'id' => 'key_001',
    'name' => 'Production API Key',
    'prefix' => 'greyeye_live_',
    'masked_key' => '•••••••••••••••••abc123',
    'created' => '2024-11-15',
    'last_used' => '2025-01-08',
    'status' => 'active',
    'permissions' => ['read', 'write'],
    'rate_limit' => '10,000/hour'
  ],
  [
    'id' => 'key_002',
    'name' => 'Staging Environment',
    'prefix' => 'greyeye_test_',
    'masked_key' => '•••••••••••••••••def456',
    'created' => '2024-12-01',
    'last_used' => '2025-01-06',
    'status' => 'active',
    'permissions' => ['read', 'write', 'admin'],
    'rate_limit' => '50,000/hour'
  ],
  [
    'id' => 'key_003',
    'name' => 'Legacy Integration',
    'prefix' => 'greyeye_live_',
    'masked_key' => '•••••••••••••••••ghi789',
    'created' => '2024-06-20',
    'last_used' => '2024-09-15',
    'status' => 'expired',
    'permissions' => ['read'],
    'rate_limit' => '1,000/hour'
  ],
  [
    'id' => 'key_004',
    'name' => 'Mobile App',
    'prefix' => 'greyeye_mobile_',
    'masked_key' => '•••••••••••••••••jkl012',
    'created' => '2024-10-10',
    'last_used' => 'Aldrig',
    'status' => 'revoked',
    'permissions' => ['read'],
    'rate_limit' => '5,000/hour'
  ]
];

// Include admin layout
require_once __DIR__ . '/includes/admin-layout.php';
?>

<!-- API Keys Content -->
<div class="admin-page api-keys">
  <!-- Page Header -->
  <header class="admin-page__header">
    <div class="admin-page__header-content">
      <h1 class="admin-page__title">
        <span class="admin-page__title-icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
          </svg>
        </span>
        API & Keys
      </h1>
      <p class="admin-page__subtitle">
        Administrer API-nøgler til eksterne integrationer og tjenester
      </p>
    </div>
    <div class="admin-page__header-actions">
      <button type="button" class="admin-btn admin-btn--primary" id="createKeyBtn" title="Opret ny API-nøgle">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="16" height="16">
          <line x1="12" y1="5" x2="12" y2="19"/>
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Opret ny nøgle
      </button>
    </div>
  </header>

  <!-- Statistics Overview -->
  <section class="api-keys__stats" aria-labelledby="stats-heading">
    <h2 id="stats-heading" class="sr-only">API-nøgle statistik</h2>

    <div class="api-keys__stat-card">
      <span class="api-keys__stat-value">2</span>
      <span class="api-keys__stat-label">Aktive nøgler</span>
    </div>

    <div class="api-keys__stat-card">
      <span class="api-keys__stat-value">1</span>
      <span class="api-keys__stat-label">Udløbne</span>
    </div>

    <div class="api-keys__stat-card">
      <span class="api-keys__stat-value">1</span>
      <span class="api-keys__stat-label">Tilbagekaldt</span>
    </div>

    <div class="api-keys__stat-card">
      <span class="api-keys__stat-value">12,847</span>
      <span class="api-keys__stat-label">API kald i dag</span>
    </div>
  </section>

  <!-- API Keys Table -->
  <section class="api-keys__list" aria-labelledby="keys-heading">
    <header class="api-keys__list-header">
      <h2 id="keys-heading" class="api-keys__section-title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="20" height="20">
          <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777z"/>
        </svg>
        Alle API-nøgler
      </h2>
      <div class="api-keys__filters">
        <select class="api-keys__filter-select" aria-label="Filtrer efter status">
          <option value="">Alle statusser</option>
          <option value="active">Aktive</option>
          <option value="expired">Udløbne</option>
          <option value="revoked">Tilbagekaldt</option>
        </select>
      </div>
    </header>

    <div class="api-keys__table-wrapper">
      <table class="api-keys__table" role="grid">
        <thead>
          <tr>
            <th scope="col">Navn</th>
            <th scope="col">Nøgle</th>
            <th scope="col">Status</th>
            <th scope="col">Tilladelser</th>
            <th scope="col">Rate limit</th>
            <th scope="col">Oprettet</th>
            <th scope="col">Sidst brugt</th>
            <th scope="col" class="api-keys__actions-header">Handlinger</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($api_keys as $key): ?>
            <tr class="api-keys__row api-keys__row--<?= htmlspecialchars($key['status']) ?>">
              <td class="api-keys__cell-name">
                <strong><?= htmlspecialchars($key['name']) ?></strong>
                <span class="api-keys__key-id"><?= htmlspecialchars($key['id']) ?></span>
              </td>
              <td class="api-keys__cell-key">
                <code class="api-keys__masked-key">
                  <span class="api-keys__key-prefix"><?= htmlspecialchars($key['prefix']) ?></span><?= htmlspecialchars($key['masked_key']) ?>
                </code>
                <button type="button" class="api-keys__copy-btn" title="Kopiér nøgle" aria-label="Kopiér API-nøgle">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="14" height="14">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                  </svg>
                </button>
              </td>
              <td>
                <span class="api-keys__status api-keys__status--<?= htmlspecialchars($key['status']) ?>">
                  <?php
                  $status_labels = [
                    'active' => 'Aktiv',
                    'expired' => 'Udløbet',
                    'revoked' => 'Tilbagekaldt'
                  ];
                  echo htmlspecialchars($status_labels[$key['status']] ?? $key['status']);
                  ?>
                </span>
              </td>
              <td class="api-keys__cell-permissions">
                <?php foreach ($key['permissions'] as $perm): ?>
                  <span class="api-keys__permission api-keys__permission--<?= htmlspecialchars($perm) ?>">
                    <?= htmlspecialchars($perm) ?>
                  </span>
                <?php endforeach; ?>
              </td>
              <td class="api-keys__cell-rate"><?= htmlspecialchars($key['rate_limit']) ?></td>
              <td><?= htmlspecialchars($key['created']) ?></td>
              <td><?= htmlspecialchars($key['last_used']) ?></td>
              <td class="api-keys__cell-actions">
                <?php if ($key['status'] === 'active'): ?>
                  <button type="button" class="api-keys__action-btn" title="Rotér nøgle" aria-label="Rotér API-nøgle">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="16" height="16">
                      <polyline points="23 4 23 10 17 10"/>
                      <polyline points="1 20 1 14 7 14"/>
                      <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                    </svg>
                  </button>
                  <button type="button" class="api-keys__action-btn api-keys__action-btn--danger" title="Tilbagekald nøgle" aria-label="Tilbagekald API-nøgle">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="16" height="16">
                      <circle cx="12" cy="12" r="10"/>
                      <line x1="15" y1="9" x2="9" y2="15"/>
                      <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                  </button>
                <?php else: ?>
                  <button type="button" class="api-keys__action-btn" title="Slet nøgle" aria-label="Slet API-nøgle">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="16" height="16">
                      <polyline points="3 6 5 6 21 6"/>
                      <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                  </button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Usage Documentation -->
  <section class="api-keys__docs" aria-labelledby="docs-heading">
    <h2 id="docs-heading" class="api-keys__section-title">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="20" height="20">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
        <line x1="16" y1="13" x2="8" y2="13"/>
        <line x1="16" y1="17" x2="8" y2="17"/>
      </svg>
      Hurtig start
    </h2>

    <div class="api-keys__code-block">
      <header class="api-keys__code-header">
        <span class="api-keys__code-lang">cURL</span>
        <button type="button" class="api-keys__code-copy" title="Kopiér kode">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="14" height="14">
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
          </svg>
          Kopiér
        </button>
      </header>
      <pre class="api-keys__code"><code>curl -X GET https://api.greyeye.io/v1/intel \
  -H "Authorization: Bearer greyeye_live_YOUR_API_KEY" \
  -H "Content-Type: application/json"</code></pre>
    </div>

    <div class="api-keys__docs-links">
      <a href="#" class="api-keys__docs-link">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="16" height="16">
          <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
          <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
        </svg>
        Fuld API dokumentation
      </a>
      <a href="#" class="api-keys__docs-link">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="16" height="16">
          <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/>
        </svg>
        SDK'er og biblioteker
      </a>
      <a href="#" class="api-keys__docs-link">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="16" height="16">
          <circle cx="12" cy="12" r="10"/>
          <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
          <line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        FAQ og fejlfinding
      </a>
    </div>
  </section>
</div>

<!-- Create Key Modal (placeholder) -->
<div class="api-keys__modal-overlay" id="createKeyModal" role="presentation">
  <div class="api-keys__modal" role="dialog" aria-modal="true" aria-labelledby="createKeyTitle">
    <button type="button" class="api-keys__modal-close" aria-label="Luk">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
        <line x1="18" y1="6" x2="6" y2="18"/>
        <line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
    </button>

    <h2 id="createKeyTitle" class="api-keys__modal-title">Opret ny API-nøgle</h2>
    <p class="api-keys__modal-description">
      Denne funktion er under udvikling. API-nøgler kan oprettes via kommandolinje eller ved at kontakte sikkerhedsteamet.
    </p>

    <div class="api-keys__modal-notice">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="24" height="24">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <span>API-nøgle administration kommer snart med fuld funktionalitet.</span>
    </div>

    <div class="api-keys__modal-actions">
      <button type="button" class="admin-btn admin-btn--secondary" id="closeCreateKeyModal">
        Luk
      </button>
    </div>
  </div>
</div>

<script>
(function() {
  'use strict';

  const createBtn = document.getElementById('createKeyBtn');
  const modal = document.getElementById('createKeyModal');
  const closeBtn = document.getElementById('closeCreateKeyModal');
  const modalClose = modal?.querySelector('.api-keys__modal-close');

  function openModal() {
    modal?.classList.add('is-open');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    modal?.classList.remove('is-open');
    document.body.style.overflow = '';
  }

  createBtn?.addEventListener('click', openModal);
  closeBtn?.addEventListener('click', closeModal);
  modalClose?.addEventListener('click', closeModal);

  modal?.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal?.classList.contains('is-open')) {
      closeModal();
    }
  });

  // Copy button functionality (placeholder)
  document.querySelectorAll('.api-keys__copy-btn, .api-keys__code-copy').forEach(btn => {
    btn.addEventListener('click', () => {
      // In a real implementation, this would copy to clipboard
      console.log('Copy functionality placeholder');
    });
  });
})();
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
