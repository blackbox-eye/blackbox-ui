<?php

/**
 * Access Requests Management Page
 *
 * Admin-only page to view, approve, or deny access requests.
 * Shows all requests with status filtering and inline actions.
 */

session_start();

// Check authentication
if (!isset($_SESSION['agent_id'])) {
  header('Location: agent-login.php');
  exit;
}

// Check admin privileges
if (empty($_SESSION['is_admin'])) {
  header('Location: dashboard.php');
  exit;
}

require_once __DIR__ . '/includes/env.php';
require_once __DIR__ . '/db.php';

// Page configuration
$page_title = 'Adgangsanmodninger';
$current_admin_page = 'access-requests';

// Handle status update via POST
$updateMessage = null;
$updateError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['request_id'])) {
  $requestId = (int) $_POST['request_id'];
  $action = $_POST['action'];
  $notes = trim($_POST['notes'] ?? '');

  $allowedActions = ['approve', 'deny', 'pending'];
  $statusMap = [
    'approve' => 'approved',
    'deny'    => 'denied',
    'pending' => 'pending',
  ];

  if (in_array($action, $allowedActions, true) && defined('BBX_DB_CONNECTED') && BBX_DB_CONNECTED) {
    try {
      $stmt = $pdo->prepare("
                UPDATE access_requests
                SET status = :status,
                    reviewed_by = :reviewed_by,
                    reviewed_at = NOW(),
                    review_notes = :notes
                WHERE id = :id
            ");

      $stmt->execute([
        ':status'      => $statusMap[$action],
        ':reviewed_by' => $_SESSION['agent_id'] ?? null,
        ':notes'       => $notes !== '' ? $notes : null,
        ':id'          => $requestId,
      ]);

      $updateMessage = 'Anmodning #' . $requestId . ' er blevet ' .
        ($action === 'approve' ? 'godkendt' : ($action === 'deny' ? 'afvist' : 'sat til afventende')) . '.';
    } catch (PDOException $e) {
      error_log('ACCESS REQUESTS: Update failed - ' . $e->getMessage());
      $updateError = 'Kunne ikke opdatere anmodningen. Prøv igen.';
    }
  }
}

// Fetch requests from database
$requests = [];
$statusFilter = $_GET['status'] ?? '';

if (defined('BBX_DB_CONNECTED') && BBX_DB_CONNECTED) {
  try {
    $sql = "SELECT * FROM access_requests";
    $params = [];

    if ($statusFilter !== '' && in_array($statusFilter, ['pending', 'approved', 'denied', 'expired'], true)) {
      $sql .= " WHERE status = :status";
      $params[':status'] = $statusFilter;
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll();
  } catch (PDOException $e) {
    error_log('ACCESS REQUESTS: Fetch failed - ' . $e->getMessage());
  }
}

// Count by status
$statusCounts = [
  'pending'  => 0,
  'approved' => 0,
  'denied'   => 0,
  'expired'  => 0,
];

if (defined('BBX_DB_CONNECTED') && BBX_DB_CONNECTED) {
  try {
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM access_requests GROUP BY status");
    while ($row = $stmt->fetch()) {
      if (isset($statusCounts[$row['status']])) {
        $statusCounts[$row['status']] = (int) $row['count'];
      }
    }
  } catch (PDOException $e) {
    // Ignore count errors
  }
}

$totalRequests = array_sum($statusCounts);

// Include admin layout
require_once __DIR__ . '/includes/admin-layout.php';
?>

<!-- Access Requests Content -->
<div class="admin-page access-requests">
  <!-- Page Header -->
  <header class="admin-page__header">
    <div class="admin-page__header-content">
      <h1 class="admin-page__title">
        <span class="admin-page__title-icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
            <circle cx="8.5" cy="7" r="4" />
            <line x1="20" y1="8" x2="20" y2="14" />
            <line x1="23" y1="11" x2="17" y2="11" />
          </svg>
        </span>
        Adgangsanmodninger
      </h1>
      <p class="admin-page__subtitle">
        Administrer anmodninger om portal-adgang
      </p>
    </div>
  </header>

  <?php if ($updateMessage): ?>
    <div class="access-requests__alert access-requests__alert--success" role="alert">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
        <polyline points="22 4 12 14.01 9 11.01" />
      </svg>
      <?= htmlspecialchars($updateMessage) ?>
    </div>
  <?php endif; ?>

  <?php if ($updateError): ?>
    <div class="access-requests__alert access-requests__alert--error" role="alert">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
        <circle cx="12" cy="12" r="10" />
        <line x1="15" y1="9" x2="9" y2="15" />
        <line x1="9" y1="9" x2="15" y2="15" />
      </svg>
      <?= htmlspecialchars($updateError) ?>
    </div>
  <?php endif; ?>

  <?php if (!defined('BBX_DB_CONNECTED') || !BBX_DB_CONNECTED): ?>
    <div class="access-requests__alert access-requests__alert--warning" role="alert">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
        <line x1="12" y1="9" x2="12" y2="13" />
        <line x1="12" y1="17" x2="12.01" y2="17" />
      </svg>
      Database-forbindelse ikke tilgængelig. Kontakt systemadministrator.
    </div>
  <?php endif; ?>

  <!-- Statistics -->
  <section class="access-requests__stats" aria-labelledby="stats-heading">
    <h2 id="stats-heading" class="sr-only">Statistik over anmodninger</h2>

    <a href="?status=" class="access-requests__stat-card <?= $statusFilter === '' ? 'is-active' : '' ?>">
      <span class="access-requests__stat-value"><?= $totalRequests ?></span>
      <span class="access-requests__stat-label">I alt</span>
    </a>

    <a href="?status=pending" class="access-requests__stat-card access-requests__stat-card--pending <?= $statusFilter === 'pending' ? 'is-active' : '' ?>">
      <span class="access-requests__stat-value"><?= $statusCounts['pending'] ?></span>
      <span class="access-requests__stat-label">Afventende</span>
    </a>

    <a href="?status=approved" class="access-requests__stat-card access-requests__stat-card--approved <?= $statusFilter === 'approved' ? 'is-active' : '' ?>">
      <span class="access-requests__stat-value"><?= $statusCounts['approved'] ?></span>
      <span class="access-requests__stat-label">Godkendte</span>
    </a>

    <a href="?status=denied" class="access-requests__stat-card access-requests__stat-card--denied <?= $statusFilter === 'denied' ? 'is-active' : '' ?>">
      <span class="access-requests__stat-value"><?= $statusCounts['denied'] ?></span>
      <span class="access-requests__stat-label">Afviste</span>
    </a>
  </section>

  <!-- Requests Table -->
  <section class="access-requests__list" aria-labelledby="requests-heading">
    <h2 id="requests-heading" class="sr-only">Liste over adgangsanmodninger</h2>

    <?php if (empty($requests)): ?>
      <div class="access-requests__empty">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" width="64" height="64">
          <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
          <circle cx="8.5" cy="7" r="4" />
          <line x1="18" y1="8" x2="23" y2="13" />
          <line x1="23" y1="8" x2="18" y2="13" />
        </svg>
        <p>Ingen anmodninger fundet<?= $statusFilter !== '' ? ' med denne status' : '' ?>.</p>
      </div>
    <?php else: ?>
      <div class="access-requests__table-wrapper">
        <table class="access-requests__table">
          <thead>
            <tr>
              <th scope="col">ID</th>
              <th scope="col">Ansøger</th>
              <th scope="col">Organisation</th>
              <th scope="col">Rolle</th>
              <th scope="col">Status</th>
              <th scope="col">Indsendt</th>
              <th scope="col">Handlinger</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($requests as $request): ?>
              <tr class="access-requests__row access-requests__row--<?= htmlspecialchars($request['status']) ?>">
                <td class="access-requests__cell-id">
                  #<?= htmlspecialchars($request['id']) ?>
                </td>
                <td class="access-requests__cell-applicant">
                  <strong><?= htmlspecialchars($request['name']) ?></strong>
                  <span class="access-requests__email"><?= htmlspecialchars($request['email']) ?></span>
                </td>
                <td><?= htmlspecialchars($request['organization']) ?></td>
                <td>
                  <?php
                  $roleLabels = [
                    'observer'    => 'Observer',
                    'operator'    => 'Operator',
                    'analyst'     => 'Analytiker',
                    'admin'       => 'Admin',
                    'unspecified' => '—',
                  ];
                  echo htmlspecialchars($roleLabels[$request['role']] ?? $request['role']);
                  ?>
                </td>
                <td>
                  <span class="access-requests__status access-requests__status--<?= htmlspecialchars($request['status']) ?>">
                    <?php
                    $statusLabels = [
                      'pending'  => 'Afventende',
                      'approved' => 'Godkendt',
                      'denied'   => 'Afvist',
                      'expired'  => 'Udløbet',
                    ];
                    echo htmlspecialchars($statusLabels[$request['status']] ?? $request['status']);
                    ?>
                  </span>
                </td>
                <td class="access-requests__cell-date">
                  <?= date('d/m/Y H:i', strtotime($request['created_at'])) ?>
                </td>
                <td class="access-requests__cell-actions">
                  <button type="button"
                    class="access-requests__action-btn"
                    onclick="openRequestModal(<?= htmlspecialchars(json_encode($request)) ?>)"
                    title="Se detaljer">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="16" height="16">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                      <circle cx="12" cy="12" r="3" />
                    </svg>
                  </button>
                  <?php if ($request['status'] === 'pending'): ?>
                    <form method="POST" class="access-requests__inline-form">
                      <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']) ?>">
                      <input type="hidden" name="notes" value="">
                      <button type="submit" name="action" value="approve"
                        class="access-requests__action-btn access-requests__action-btn--approve"
                        title="Godkend">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                          <polyline points="20 6 9 17 4 12" />
                        </svg>
                      </button>
                      <button type="submit" name="action" value="deny"
                        class="access-requests__action-btn access-requests__action-btn--deny"
                        title="Afvis">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                          <line x1="18" y1="6" x2="6" y2="18" />
                          <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                      </button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</div>

<!-- Request Details Modal -->
<div class="access-requests__modal-overlay" id="requestModal" role="presentation">
  <div class="access-requests__modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <button type="button" class="access-requests__modal-close" onclick="closeRequestModal()" aria-label="Luk">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
        <line x1="18" y1="6" x2="6" y2="18" />
        <line x1="6" y1="6" x2="18" y2="18" />
      </svg>
    </button>

    <h2 id="modalTitle" class="access-requests__modal-title">Anmodning #<span id="modalRequestId"></span></h2>

    <div class="access-requests__modal-content">
      <dl class="access-requests__details">
        <div class="access-requests__detail-row">
          <dt>Navn</dt>
          <dd id="modalName"></dd>
        </div>
        <div class="access-requests__detail-row">
          <dt>E-mail</dt>
          <dd id="modalEmail"></dd>
        </div>
        <div class="access-requests__detail-row">
          <dt>Organisation</dt>
          <dd id="modalOrganization"></dd>
        </div>
        <div class="access-requests__detail-row">
          <dt>Ønsket rolle</dt>
          <dd id="modalRole"></dd>
        </div>
        <div class="access-requests__detail-row">
          <dt>Status</dt>
          <dd id="modalStatus"></dd>
        </div>
        <div class="access-requests__detail-row">
          <dt>Indsendt</dt>
          <dd id="modalCreatedAt"></dd>
        </div>
        <div class="access-requests__detail-row">
          <dt>IP-adresse</dt>
          <dd id="modalIp"></dd>
        </div>
        <div class="access-requests__detail-row">
          <dt>reCAPTCHA score</dt>
          <dd id="modalRecaptcha"></dd>
        </div>
      </dl>

      <div class="access-requests__reason-section">
        <h3>Begrundelse</h3>
        <p id="modalReason"></p>
      </div>

      <div class="access-requests__notes-section" id="modalNotesSection" style="display: none;">
        <h3>Review-noter</h3>
        <p id="modalNotes"></p>
      </div>
    </div>

    <form method="POST" class="access-requests__modal-form" id="modalForm">
      <input type="hidden" name="request_id" id="modalFormRequestId">

      <label class="access-requests__modal-label">
        Noter (valgfrit)
        <textarea name="notes" id="modalFormNotes" rows="2" placeholder="Tilføj noter til denne anmodning..."></textarea>
      </label>

      <div class="access-requests__modal-actions" id="modalActions">
        <button type="submit" name="action" value="approve" class="admin-btn admin-btn--success">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <polyline points="20 6 9 17 4 12" />
          </svg>
          Godkend
        </button>
        <button type="submit" name="action" value="deny" class="admin-btn admin-btn--danger">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
          Afvis
        </button>
        <button type="button" class="admin-btn admin-btn--secondary" onclick="closeRequestModal()">
          Luk
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  const roleLabels = {
    'observer': 'Observer',
    'operator': 'Operator',
    'analyst': 'Analytiker',
    'admin': 'Administrator',
    'unspecified': 'Ikke specificeret'
  };

  const statusLabels = {
    'pending': 'Afventende',
    'approved': 'Godkendt',
    'denied': 'Afvist',
    'expired': 'Udløbet'
  };

  function openRequestModal(request) {
    document.getElementById('modalRequestId').textContent = request.id;
    document.getElementById('modalName').textContent = request.name;
    document.getElementById('modalEmail').textContent = request.email;
    document.getElementById('modalOrganization').textContent = request.organization;
    document.getElementById('modalRole').textContent = roleLabels[request.role] || request.role;
    document.getElementById('modalStatus').innerHTML = '<span class="access-requests__status access-requests__status--' + request.status + '">' + (statusLabels[request.status] || request.status) + '</span>';
    document.getElementById('modalCreatedAt').textContent = new Date(request.created_at).toLocaleString('da-DK');
    document.getElementById('modalIp').textContent = request.ip_address || '—';
    document.getElementById('modalRecaptcha').textContent = request.recaptcha_score ? parseFloat(request.recaptcha_score).toFixed(2) : '—';
    document.getElementById('modalReason').textContent = request.reason;

    document.getElementById('modalFormRequestId').value = request.id;

    // Show/hide notes section
    const notesSection = document.getElementById('modalNotesSection');
    if (request.review_notes) {
      document.getElementById('modalNotes').textContent = request.review_notes;
      notesSection.style.display = 'block';
    } else {
      notesSection.style.display = 'none';
    }

    // Show/hide action buttons based on status
    const actions = document.getElementById('modalActions');
    const form = document.getElementById('modalForm');
    if (request.status === 'pending') {
      form.style.display = 'block';
    } else {
      form.style.display = 'none';
    }

    document.getElementById('requestModal').classList.add('is-open');
    document.body.style.overflow = 'hidden';
  }

  function closeRequestModal() {
    document.getElementById('requestModal').classList.remove('is-open');
    document.body.style.overflow = '';
    document.getElementById('modalFormNotes').value = '';
  }

  document.getElementById('requestModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeRequestModal();
    }
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('requestModal').classList.contains('is-open')) {
      closeRequestModal();
    }
  });
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
