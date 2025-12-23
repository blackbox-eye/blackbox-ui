<?php

/**
 * API Keys Management Page
 *
 * Manage API keys for external integrations and services
 * Database-driven with full CRUD operations
 */

session_start();

// Check authentication
if (!isset($_SESSION['agent_id'])) {
  header('Location: gdi-login.php');
  exit;
}

// Load dependencies
require_once __DIR__ . '/includes/env.php';
require_once __DIR__ . '/includes/apikey-helper.php';
require_once __DIR__ . '/db.php';

// Page configuration
$page_title = 'API & Keys';
$current_admin_page = 'api-keys';

$agentId = (int) $_SESSION['agent_id'];
$isAdmin = !empty($_SESSION['is_admin']);

// Fetch API keys from database
$apiKeys = [];
$scopes = [];
$stats = ['active' => 0, 'expired' => 0, 'revoked' => 0, 'requests_today' => 0];
$dbError = null;

if (defined('BBX_DB_CONNECTED') && BBX_DB_CONNECTED && isset($pdo)) {
  try {
    // Fetch keys
    if ($isAdmin) {
      $stmt = $pdo->prepare("
        SELECT
          k.*,
          a.name AS agent_name
        FROM api_keys k
        JOIN agents a ON k.agent_id = a.id
        ORDER BY k.created_at DESC
      ");
      $stmt->execute();
    } else {
      $stmt = $pdo->prepare("
        SELECT *
        FROM api_keys
        WHERE agent_id = :agent_id
        ORDER BY created_at DESC
      ");
      $stmt->execute([':agent_id' => $agentId]);
    }

    $apiKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate stats
    foreach ($apiKeys as $key) {
      if ($key['revoked_at']) {
        $stats['revoked']++;
      } elseif ($key['expires_at'] && strtotime($key['expires_at']) < time()) {
        $stats['expired']++;
      } elseif ($key['is_active']) {
        $stats['active']++;
      }
    }

    // Get today's request count
    $todayStmt = $pdo->prepare(
      "
      SELECT SUM(request_count) as total
      FROM api_keys
      WHERE " . ($isAdmin ? "1=1" : "agent_id = :agent_id")
    );
    if (!$isAdmin) {
      $todayStmt->execute([':agent_id' => $agentId]);
    } else {
      $todayStmt->execute();
    }
    $stats['requests_today'] = (int) ($todayStmt->fetchColumn() ?? 0);

    // Fetch available scopes
    $scopes = apikey_get_scopes($pdo, $isAdmin);
  } catch (PDOException $e) {
    error_log('API Keys fetch error: ' . $e->getMessage());
    $dbError = 'Kunne ikke hente API-nøgler fra databasen';
  }
}

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
            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4" />
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
          <line x1="12" y1="5" x2="12" y2="19" />
          <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
        Opret ny nøgle
      </button>
    </div>
  </header>

  <?php if ($dbError): ?>
    <div class="api-keys__error" role="alert">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
        <circle cx="12" cy="12" r="10" />
        <line x1="12" y1="8" x2="12" y2="12" />
        <line x1="12" y1="16" x2="12.01" y2="16" />
      </svg>
      <?php echo htmlspecialchars($dbError); ?>
    </div>
  <?php endif; ?>

  <!-- Statistics Overview -->
  <section class="api-keys__stats" aria-labelledby="stats-heading">
    <h2 id="stats-heading" class="sr-only">API-nøgle statistik</h2>

    <div class="api-keys__stat-card">
      <span class="api-keys__stat-value"><?php echo $stats['active']; ?></span>
      <span class="api-keys__stat-label">Aktive nøgler</span>
    </div>

    <div class="api-keys__stat-card">
      <span class="api-keys__stat-value"><?php echo $stats['expired']; ?></span>
      <span class="api-keys__stat-label">Udløbne</span>
    </div>

    <div class="api-keys__stat-card">
      <span class="api-keys__stat-value"><?php echo $stats['revoked']; ?></span>
      <span class="api-keys__stat-label">Tilbagekaldt</span>
    </div>

    <div class="api-keys__stat-card">
      <span class="api-keys__stat-value"><?php echo number_format($stats['requests_today']); ?></span>
      <span class="api-keys__stat-label">Total API kald</span>
    </div>
  </section>

  <!-- API Keys Table -->
  <section class="api-keys__list" aria-labelledby="keys-heading">
    <header class="api-keys__list-header">
      <h2 id="keys-heading" class="api-keys__section-title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="20" height="20">
          <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777z" />
        </svg>
        Alle API-nøgler
      </h2>
      <div class="api-keys__filters">
        <select class="api-keys__filter-select" id="statusFilter" aria-label="Filtrer efter status">
          <option value="">Alle statusser</option>
          <option value="active">Aktive</option>
          <option value="expired">Udløbne</option>
          <option value="revoked">Tilbagekaldt</option>
        </select>
      </div>
    </header>

    <?php if (empty($apiKeys)): ?>
      <div class="api-keys__empty">
        <div class="api-keys__empty-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" width="48" height="48">
            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777z" />
          </svg>
        </div>
        <p>Ingen API-nøgler endnu. Opret din første nøgle for at komme i gang.</p>
      </div>
    <?php else: ?>
      <div class="api-keys__table-wrapper">
        <table class="api-keys__table" role="grid" id="keysTable">
          <thead>
            <tr>
              <th scope="col">Navn</th>
              <th scope="col">Nøgle</th>
              <th scope="col">Status</th>
              <th scope="col">Scopes</th>
              <th scope="col">Rate limit</th>
              <th scope="col">Oprettet</th>
              <th scope="col">Sidst brugt</th>
              <th scope="col" class="api-keys__actions-header">Handlinger</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($apiKeys as $key):
              $keyScopes = !empty($key['scopes']) ? json_decode($key['scopes'], true) : [];
              $isRevoked = !empty($key['revoked_at']);
              $isExpired = !$isRevoked && $key['expires_at'] && strtotime($key['expires_at']) < time();
              $isActive = !$isRevoked && !$isExpired && $key['is_active'];

              $status = 'active';
              if ($isRevoked) $status = 'revoked';
              elseif ($isExpired) $status = 'expired';
              elseif (!$key['is_active']) $status = 'inactive';

              $maskedKey = apikey_mask($key['key_id'], $key['key_hint']);
            ?>
              <tr class="api-keys__row api-keys__row--<?php echo $status; ?>" data-status="<?php echo $status; ?>" data-id="<?php echo $key['id']; ?>">
                <td class="api-keys__cell-name">
                  <strong><?php echo htmlspecialchars($key['name']); ?></strong>
                  <?php if ($isAdmin && !empty($key['agent_name'])): ?>
                    <span class="api-keys__key-owner"><?php echo htmlspecialchars($key['agent_name']); ?></span>
                  <?php endif; ?>
                </td>
                <td class="api-keys__cell-key">
                  <code class="api-keys__masked-key"><?php echo htmlspecialchars($maskedKey); ?></code>
                </td>
                <td>
                  <span class="api-keys__status api-keys__status--<?php echo $status; ?>">
                    <?php
                    $statusLabels = [
                      'active' => 'Aktiv',
                      'expired' => 'Udløbet',
                      'revoked' => 'Tilbagekaldt',
                      'inactive' => 'Inaktiv'
                    ];
                    echo $statusLabels[$status] ?? $status;
                    ?>
                  </span>
                </td>
                <td class="api-keys__cell-scopes">
                  <?php if (empty($keyScopes)): ?>
                    <span class="api-keys__no-scopes">Ingen</span>
                  <?php else: ?>
                    <?php foreach (array_slice($keyScopes, 0, 3) as $scope): ?>
                      <span class="api-keys__scope"><?php echo htmlspecialchars($scope); ?></span>
                    <?php endforeach; ?>
                    <?php if (count($keyScopes) > 3): ?>
                      <span class="api-keys__scope-more">+<?php echo count($keyScopes) - 3; ?></span>
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
                <td class="api-keys__cell-rate">
                  <?php echo $key['rate_limit'] ? number_format($key['rate_limit']) . '/time' : 'Ubegrænset'; ?>
                </td>
                <td><?php echo date('d. M Y', strtotime($key['created_at'])); ?></td>
                <td>
                  <?php echo $key['last_used_at'] ? date('d. M Y H:i', strtotime($key['last_used_at'])) : 'Aldrig'; ?>
                </td>
                <td class="api-keys__cell-actions">
                  <?php if ($isActive): ?>
                    <button type="button"
                      class="api-keys__action-btn api-keys__action-btn--danger"
                      title="Tilbagekald nøgle"
                      data-revoke="<?php echo $key['id']; ?>">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="16" height="16">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="15" y1="9" x2="9" y2="15" />
                        <line x1="9" y1="9" x2="15" y2="15" />
                      </svg>
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>

  <!-- Usage Documentation -->
  <section class="api-keys__docs" aria-labelledby="docs-heading">
    <h2 id="docs-heading" class="api-keys__section-title">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="20" height="20">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
        <polyline points="14 2 14 8 20 8" />
        <line x1="16" y1="13" x2="8" y2="13" />
        <line x1="16" y1="17" x2="8" y2="17" />
      </svg>
      Hurtig start
    </h2>

    <div class="api-keys__code-block">
      <header class="api-keys__code-header">
        <span class="api-keys__code-lang">cURL</span>
        <button type="button" class="api-keys__code-copy" title="Kopiér kode" id="copyCodeBtn">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="14" height="14">
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
          </svg>
          Kopiér
        </button>
      </header>
      <pre class="api-keys__code"><code id="codeExample">curl -X GET https://api.blackboxeye.dk/v1/intel \
  -H "X-API-Key: bbx_YOUR_API_KEY" \
  -H "Content-Type: application/json"</code></pre>
    </div>
  </section>
</div>

<!-- Create Key Modal -->
<div class="api-keys__modal-overlay" id="createKeyModal" aria-hidden="true">
  <div class="api-keys__modal" role="dialog" aria-modal="true" aria-labelledby="createKeyTitle">
    <button type="button" class="api-keys__modal-close" data-close-modal aria-label="Luk">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
        <line x1="18" y1="6" x2="6" y2="18" />
        <line x1="6" y1="6" x2="18" y2="18" />
      </svg>
    </button>

    <h2 id="createKeyTitle" class="api-keys__modal-title">Opret ny API-nøgle</h2>
    <p class="api-keys__modal-description">
      API-nøglen vises kun én gang efter oprettelse. Gem den sikkert.
    </p>

    <form id="createKeyForm">
      <div class="api-keys__form-group">
        <label for="keyName" class="api-keys__label">Navn <span class="required">*</span></label>
        <input type="text" id="keyName" name="name" class="api-keys__input" required maxlength="100" placeholder="F.eks. Production API">
      </div>

      <div class="api-keys__form-group">
        <label for="keyDescription" class="api-keys__label">Beskrivelse</label>
        <textarea id="keyDescription" name="description" class="api-keys__textarea" rows="2" placeholder="Valgfri beskrivelse..."></textarea>
      </div>

      <div class="api-keys__form-group">
        <label class="api-keys__label">Scopes / Tilladelser</label>
        <div class="api-keys__scopes-list">
          <?php foreach ($scopes as $scope): ?>
            <label class="api-keys__scope-checkbox">
              <input type="checkbox" name="scopes[]" value="<?php echo htmlspecialchars($scope['scope']); ?>">
              <span class="api-keys__scope-name"><?php echo htmlspecialchars($scope['name']); ?></span>
              <?php if (!empty($scope['description'])): ?>
                <span class="api-keys__scope-desc"><?php echo htmlspecialchars($scope['description']); ?></span>
              <?php endif; ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="api-keys__form-group">
        <label for="rateLimit" class="api-keys__label">Rate limit (requests/time)</label>
        <input type="number" id="rateLimit" name="rate_limit" class="api-keys__input" value="1000" min="0" max="100000">
        <span class="api-keys__hint">0 = ubegrænset</span>
      </div>

      <div class="api-keys__modal-actions">
        <button type="button" class="admin-btn admin-btn--secondary" data-close-modal>Annuller</button>
        <button type="submit" class="admin-btn admin-btn--primary" id="submitKeyBtn">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777z" />
          </svg>
          Opret nøgle
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Key Created Modal -->
<div class="api-keys__modal-overlay" id="keyCreatedModal" aria-hidden="true">
  <div class="api-keys__modal api-keys__modal--success" role="dialog" aria-modal="true" aria-labelledby="keyCreatedTitle">
    <h2 id="keyCreatedTitle" class="api-keys__modal-title">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
        <polyline points="22 4 12 14.01 9 11.01" />
      </svg>
      API-nøgle oprettet!
    </h2>

    <div class="api-keys__modal-notice api-keys__modal-notice--warning">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="24" height="24">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
        <line x1="12" y1="9" x2="12" y2="13" />
        <line x1="12" y1="17" x2="12.01" y2="17" />
      </svg>
      <span>Denne nøgle vises kun én gang. Kopiér den nu og gem den sikkert!</span>
    </div>

    <div class="api-keys__new-key-display">
      <label class="api-keys__label">Din nye API-nøgle:</label>
      <div class="api-keys__key-box">
        <code id="newKeyValue"></code>
        <button type="button" class="api-keys__copy-key-btn" id="copyNewKey" title="Kopiér nøgle">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="18" height="18">
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
          </svg>
        </button>
      </div>
    </div>

    <div class="api-keys__modal-actions">
      <button type="button" class="admin-btn admin-btn--primary" id="closeKeyCreatedModal">
        Jeg har gemt nøglen
      </button>
    </div>
  </div>
</div>

<script>
  (function() {
    'use strict';

    // DOM elements
    const createKeyBtn = document.getElementById('createKeyBtn');
    const createKeyModal = document.getElementById('createKeyModal');
    const createKeyForm = document.getElementById('createKeyForm');
    const keyCreatedModal = document.getElementById('keyCreatedModal');
    const newKeyValue = document.getElementById('newKeyValue');
    const copyNewKey = document.getElementById('copyNewKey');
    const closeKeyCreatedModal = document.getElementById('closeKeyCreatedModal');
    const statusFilter = document.getElementById('statusFilter');
    const keysTable = document.getElementById('keysTable');

    // Open create modal
    createKeyBtn?.addEventListener('click', () => {
      createKeyModal.setAttribute('aria-hidden', 'false');
      createKeyModal.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      document.getElementById('keyName').focus();
    });

    // Close modals
    document.querySelectorAll('[data-close-modal]').forEach(btn => {
      btn.addEventListener('click', () => {
        createKeyModal.setAttribute('aria-hidden', 'true');
        createKeyModal.classList.remove('is-open');
        document.body.style.overflow = '';
        createKeyForm.reset();
      });
    });

    // Close on backdrop click
    createKeyModal?.addEventListener('click', (e) => {
      if (e.target === createKeyModal) {
        createKeyModal.setAttribute('aria-hidden', 'true');
        createKeyModal.classList.remove('is-open');
        document.body.style.overflow = '';
      }
    });

    // Close on Escape
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        if (createKeyModal?.classList.contains('is-open')) {
          createKeyModal.setAttribute('aria-hidden', 'true');
          createKeyModal.classList.remove('is-open');
          document.body.style.overflow = '';
        }
      }
    });

    // Create key form submit
    createKeyForm?.addEventListener('submit', async (e) => {
      e.preventDefault();

      const submitBtn = document.getElementById('submitKeyBtn');
      submitBtn.disabled = true;

      const formData = new FormData(createKeyForm);
      const data = {
        name: formData.get('name'),
        description: formData.get('description'),
        scopes: formData.getAll('scopes[]'),
        rate_limit: parseInt(formData.get('rate_limit')) || 0
      };

      try {
        const response = await fetch('api/api-keys.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
          // Close create modal
          createKeyModal.setAttribute('aria-hidden', 'true');
          createKeyModal.classList.remove('is-open');
          createKeyForm.reset();

          // Show key created modal
          newKeyValue.textContent = result.key.api_key;
          keyCreatedModal.setAttribute('aria-hidden', 'false');
          keyCreatedModal.classList.add('is-open');
        } else {
          alert('Fejl: ' + (result.error || 'Kunne ikke oprette API-nøgle'));
        }
      } catch (error) {
        alert('Fejl: ' + error.message);
      } finally {
        submitBtn.disabled = false;
      }
    });

    // Copy new key
    copyNewKey?.addEventListener('click', async () => {
      const key = newKeyValue.textContent;
      try {
        await navigator.clipboard.writeText(key);
        copyNewKey.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
          <polyline points="20 6 9 17 4 12" />
        </svg>
      `;
        setTimeout(() => {
          copyNewKey.innerHTML = `
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="18" height="18">
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
          </svg>
        `;
        }, 2000);
      } catch (err) {
        alert('Kunne ikke kopiere til udklipsholder');
      }
    });

    // Close key created modal
    closeKeyCreatedModal?.addEventListener('click', () => {
      keyCreatedModal.setAttribute('aria-hidden', 'true');
      keyCreatedModal.classList.remove('is-open');
      document.body.style.overflow = '';
      window.location.reload();
    });

    // Filter by status
    statusFilter?.addEventListener('change', () => {
      const status = statusFilter.value;
      const rows = keysTable?.querySelectorAll('tbody tr');

      rows?.forEach(row => {
        if (!status || row.dataset.status === status) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });

    // Revoke key
    document.querySelectorAll('[data-revoke]').forEach(btn => {
      btn.addEventListener('click', async () => {
        const keyId = btn.dataset.revoke;

        if (!confirm('Er du sikker på, at du vil tilbagekalde denne API-nøgle? Denne handling kan ikke fortrydes.')) {
          return;
        }

        try {
          const response = await fetch('api/api-keys.php', {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              id: keyId
            })
          });

          const result = await response.json();

          if (result.success) {
            window.location.reload();
          } else {
            alert('Fejl: ' + (result.error || 'Kunne ikke tilbagekalde API-nøgle'));
          }
        } catch (error) {
          alert('Fejl: ' + error.message);
        }
      });
    });

    // Copy code example
    document.getElementById('copyCodeBtn')?.addEventListener('click', async () => {
      const code = document.getElementById('codeExample')?.textContent;
      try {
        await navigator.clipboard.writeText(code);
      } catch (err) {
        // Silent fail
      }
    });
  })();
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
