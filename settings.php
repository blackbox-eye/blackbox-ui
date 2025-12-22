<?php

/**
 * Settings Page - Agent Personal Settings
 *
 * Modern panel-based layout for managing personal settings including
 * password, PIN, token, contact info, and account status.
 * Uses admin-layout.php with Control Panel navigation.
 */

session_start();
require __DIR__ . '/db.php';

// Redirect if not logged in
if (!isset($_SESSION['agent_id'])) {
  header('Location: gdi-login.php');
  exit;
}

$agentId = $_SESSION['agent_id'];

$fetchStmt = $pdo->prepare("SELECT agent_id, email, token, ghost FROM agents WHERE agent_id = ?");
$fetchStmt->execute([$agentId]);
$agentData = $fetchStmt->fetch();

$agentEmail = $agentData['email'] ?? '';
$agentToken = $agentData['token'] ?? '';
$isGhost    = !empty($agentData['ghost']);

$error   = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // 1) Change password & PIN
  if (isset($_POST['action']) && $_POST['action'] === 'change_credentials') {
    $currPass = $_POST['current_password'] ?? '';
    $newPass  = $_POST['new_password']     ?? '';
    $newPin   = trim($_POST['new_pin']     ?? '');

    if ($currPass && $newPass && $newPin) {
      $stmt = $pdo->prepare("SELECT password FROM agents WHERE agent_id = ?");
      $stmt->execute([$agentId]);
      $row = $stmt->fetch();

      if ($row && password_verify($currPass, $row['password'])) {
        $hash = password_hash($newPass, PASSWORD_DEFAULT);
        $upd  = $pdo->prepare("
                    UPDATE agents
                       SET password = ?, pin = ?
                     WHERE agent_id = ?
                ");
        $upd->execute([$hash, $newPin, $agentId]);
        $success = 'Password og PIN er opdateret.';
      } else {
        $error = 'Nuværende password er forkert.';
      }
    } else {
      $error = 'Udfyld alle felter.';
    }
  }

  // 2) Regenerate token
  if (isset($_POST['action']) && $_POST['action'] === 'regen_token') {
    $newToken = bin2hex(random_bytes(8));
    $upd = $pdo->prepare("
            UPDATE agents
               SET token = ?
             WHERE agent_id = ?
        ");
    $upd->execute([$newToken, $agentId]);
    $agentToken = $newToken;
    $success = 'Nyt token genereret: ' . $newToken;
  }

  // 3) Deactivate own account (soft delete)
  if (isset($_POST['action']) && $_POST['action'] === 'deactivate_self') {
    $upd = $pdo->prepare("
            UPDATE agents
               SET status = 'deactivated'
             WHERE agent_id = ?
        ");
    $upd->execute([$agentId]);
    session_destroy();
    header('Location: gdi-login.php?msg=deactivated');
    exit;
  }
}

// Reload current data after any changes
$fetchStmt->execute([$agentId]);
$agentData = $fetchStmt->fetch();
$agentEmail = $agentData['email'] ?? $agentEmail;
$agentToken = $agentData['token'] ?? $agentToken;
$isGhost    = !empty($agentData['ghost']);

if ($success === '' && isset($_SESSION['settings_success'])) {
  $success = $_SESSION['settings_success'];
  unset($_SESSION['settings_success']);
}

if ($error === '' && isset($_SESSION['settings_error'])) {
  $error = $_SESSION['settings_error'];
  unset($_SESSION['settings_error']);
}

// Set page variables for admin layout
$page_title = 'Indstillinger';
$current_admin_page = 'settings';

// Include admin layout header
include __DIR__ . '/includes/admin-layout.php';
?>

<!-- Settings Page Styles -->
<style>
  .settings {
    max-width: 900px;
    margin: 0 auto;
  }

  .settings__grid {
    display: grid;
    gap: var(--admin-spacing-lg);
    grid-template-columns: 1fr;
  }

  @media (min-width: 768px) {
    .settings__grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  .settings__panel {
    background: var(--admin-bg-secondary);
    border: 1px solid var(--admin-border-subtle);
    border-radius: var(--admin-border-radius);
    padding: var(--admin-spacing-lg);
    transition: border-color 0.25s ease;
  }

  .settings__panel:hover {
    border-color: var(--admin-border-gold);
  }

  .settings__panel--full {
    grid-column: 1 / -1;
  }

  .settings__panel--danger {
    border-color: rgba(248, 113, 113, 0.3);
  }

  .settings__panel--danger:hover {
    border-color: rgba(248, 113, 113, 0.5);
  }

  .settings__panel-header {
    display: flex;
    align-items: center;
    gap: var(--admin-spacing-sm);
    margin-bottom: var(--admin-spacing-md);
    padding-bottom: var(--admin-spacing-sm);
    border-bottom: 1px solid var(--admin-border-subtle);
  }

  .settings__panel-icon {
    width: 24px;
    height: 24px;
    color: var(--admin-gold);
  }

  .settings__panel-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--admin-text-gold);
    margin: 0;
  }

  .settings__panel-description {
    font-size: 0.72rem;
    color: var(--admin-text-muted);
    margin: 0 0 var(--admin-spacing-md);
  }

  .settings__form {
    display: flex;
    flex-direction: column;
    gap: var(--admin-spacing-sm);
  }

  .settings__input {
    width: 100%;
    padding: 0.6rem 0.8rem;
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid var(--admin-border-subtle);
    border-radius: var(--admin-border-radius-sm);
    color: var(--admin-text-primary);
    font-size: 0.78rem;
    font-family: inherit;
    min-height: 40px;
    transition: border-color 0.2s, box-shadow 0.2s;
  }

  .settings__input::placeholder {
    color: var(--admin-text-muted);
  }

  .settings__input:focus {
    outline: none;
    border-color: var(--admin-gold);
    box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
  }

  .settings__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.6rem 1.25rem;
    min-height: 40px;
    border-radius: var(--admin-border-radius-sm);
    font-size: 0.72rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.25s ease;
    border: 1px solid transparent;
    font-family: inherit;
  }

  .settings__btn--primary {
    background: linear-gradient(135deg, var(--admin-gold) 0%, var(--admin-gold-dark) 100%);
    color: #000;
  }

  .settings__btn--primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(212, 175, 55, 0.35);
  }

  .settings__btn--secondary {
    background: rgba(255, 255, 255, 0.05);
    border-color: var(--admin-border-subtle);
    color: var(--admin-text-secondary);
  }

  .settings__btn--secondary:hover {
    border-color: var(--admin-gold);
    color: var(--admin-text-gold);
  }

  .settings__btn--danger {
    background: rgba(248, 113, 113, 0.15);
    border-color: rgba(248, 113, 113, 0.4);
    color: #f87171;
  }

  .settings__btn--danger:hover {
    background: rgba(248, 113, 113, 0.25);
    border-color: #f87171;
  }

  .settings__token-display {
    display: flex;
    align-items: center;
    gap: var(--admin-spacing-sm);
    padding: var(--admin-spacing-sm) var(--admin-spacing-md);
    background: rgba(0, 0, 0, 0.3);
    border-radius: var(--admin-border-radius-sm);
    margin-bottom: var(--admin-spacing-md);
  }

  .settings__token-value {
    flex: 1;
    font-family: 'Fira Code', monospace;
    font-size: 0.8rem;
    color: var(--admin-text-gold);
    word-break: break-all;
  }

  .settings__status-card {
    background: rgba(0, 0, 0, 0.2);
    border-radius: var(--admin-border-radius-sm);
    padding: var(--admin-spacing-md);
    margin-bottom: var(--admin-spacing-md);
  }

  .settings__status-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--admin-spacing-xs) 0;
    font-size: 0.75rem;
  }

  .settings__status-label {
    color: var(--admin-text-muted);
  }

  .settings__status-value {
    color: var(--admin-text-primary);
    font-weight: 500;
  }

  .settings__status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.5rem;
    border-radius: 999px;
    font-size: 0.6rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .settings__status-badge--active {
    background: rgba(74, 222, 128, 0.15);
    color: #4ade80;
  }

  .settings__status-badge--inactive {
    background: rgba(248, 113, 113, 0.15);
    color: #f87171;
  }

  .settings__alert {
    display: flex;
    align-items: center;
    gap: var(--admin-spacing-sm);
    padding: var(--admin-spacing-sm) var(--admin-spacing-md);
    border-radius: var(--admin-border-radius-sm);
    font-size: 0.75rem;
    margin-bottom: var(--admin-spacing-lg);
  }

  .settings__alert--error {
    background: rgba(248, 113, 113, 0.1);
    border: 1px solid rgba(248, 113, 113, 0.3);
    color: #fca5a5;
  }

  .settings__alert--success {
    background: rgba(74, 222, 128, 0.1);
    border: 1px solid rgba(74, 222, 128, 0.3);
    color: #86efac;
  }

  .settings__alert-icon {
    flex-shrink: 0;
  }
</style>

<!-- Settings Content -->
<div class="settings admin-page">
  <!-- Page Header -->
  <header class="admin-page__header">
    <div>
      <h1 class="admin-page__title">
        <span class="admin-page__title-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="3" />
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
          </svg>
        </span>
        Indstillinger
      </h1>
      <p class="admin-page__subtitle">Administrer dine personlige indstillinger og kontooplysninger</p>
    </div>
  </header>

  <!-- Alerts -->
  <?php if ($error): ?>
    <div class="settings__alert settings__alert--error">
      <svg class="settings__alert-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10" />
        <line x1="12" y1="8" x2="12" y2="12" />
        <line x1="12" y1="16" x2="12.01" y2="16" />
      </svg>
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="settings__alert settings__alert--success">
      <svg class="settings__alert-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
        <polyline points="22 4 12 14.01 9 11.01" />
      </svg>
      <?= htmlspecialchars($success) ?>
    </div>
  <?php endif; ?>

  <!-- Status Overview Panel -->
  <div class="settings__panel settings__panel--full" style="margin-bottom: var(--admin-spacing-lg);">
    <header class="settings__panel-header">
      <svg class="settings__panel-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
        <circle cx="12" cy="7" r="4" />
      </svg>
      <h2 class="settings__panel-title">Kontooversigt</h2>
    </header>
    <div class="settings__status-card">
      <div class="settings__status-item">
        <span class="settings__status-label">Agent-ID</span>
        <span class="settings__status-value"><?= htmlspecialchars($agentData['agent_id'] ?? $agentId) ?></span>
      </div>
      <div class="settings__status-item">
        <span class="settings__status-label">E-mail</span>
        <span class="settings__status-value"><?= htmlspecialchars($agentEmail ?: '—') ?></span>
      </div>
      <div class="settings__status-item">
        <span class="settings__status-label">Ghost-mode</span>
        <span class="settings__status-badge <?= $isGhost ? 'settings__status-badge--active' : 'settings__status-badge--inactive' ?>">
          <?= $isGhost ? '● Aktiv' : '○ Deaktiveret' ?>
        </span>
      </div>
    </div>
  </div>

  <!-- Settings Grid -->
  <div class="settings__grid">

    <!-- Password & PIN Panel -->
    <div class="settings__panel">
      <header class="settings__panel-header">
        <svg class="settings__panel-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
          <path d="M7 11V7a5 5 0 0 1 10 0v4" />
        </svg>
        <h2 class="settings__panel-title">Password & PIN</h2>
      </header>
      <p class="settings__panel-description">Opdater dine login-oplysninger for øget sikkerhed</p>
      <form method="post" class="settings__form">
        <input type="hidden" name="action" value="change_credentials">
        <input class="settings__input" name="current_password" type="password" placeholder="Nuværende password" required autocomplete="current-password">
        <input class="settings__input" name="new_password" type="password" placeholder="Nyt password" required autocomplete="new-password">
        <input class="settings__input" name="new_pin" type="text" placeholder="Ny PIN-kode" required pattern="[0-9]*" inputmode="numeric">
        <button type="submit" class="settings__btn settings__btn--primary">Opdatér</button>
      </form>
    </div>

    <!-- Token Panel -->
    <div class="settings__panel">
      <header class="settings__panel-header">
        <svg class="settings__panel-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4" />
        </svg>
        <h2 class="settings__panel-title">API Token</h2>
      </header>
      <p class="settings__panel-description">Dit unikke token til API-adgang</p>
      <div class="settings__token-display">
        <code class="settings__token-value"><?= htmlspecialchars($agentToken ?: '—') ?></code>
      </div>
      <form method="post">
        <input type="hidden" name="action" value="regen_token">
        <button type="submit" class="settings__btn settings__btn--secondary">Generér nyt token</button>
      </form>
    </div>

    <!-- Contact Information Panel -->
    <div class="settings__panel">
      <header class="settings__panel-header">
        <svg class="settings__panel-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
          <polyline points="22,6 12,13 2,6" />
        </svg>
        <h2 class="settings__panel-title">Kontaktinformation</h2>
      </header>
      <p class="settings__panel-description">Opdater din e-mail-adresse</p>
      <form method="post" action="update-contact.php" class="settings__form">
        <input class="settings__input" name="email" type="email" placeholder="E-mail" value="<?= htmlspecialchars($agentEmail) ?>" required autocomplete="email">
        <button type="submit" class="settings__btn settings__btn--primary">Opdatér e-mail</button>
      </form>
    </div>

    <!-- Change Agent ID Panel -->
    <div class="settings__panel">
      <header class="settings__panel-header">
        <svg class="settings__panel-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
          <circle cx="12" cy="7" r="4" />
        </svg>
        <h2 class="settings__panel-title">Agent-ID</h2>
      </header>
      <p class="settings__panel-description">Skift dit unikke agent-ID</p>
      <form method="post" action="change-agentid.php" class="settings__form">
        <input class="settings__input" name="new_agent_id" type="text" placeholder="Nyt Agent-ID" required>
        <button type="submit" class="settings__btn settings__btn--primary">Opdatér Agent-ID</button>
      </form>
    </div>

    <!-- Login Logs Panel -->
    <div class="settings__panel">
      <header class="settings__panel-header">
        <svg class="settings__panel-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
          <polyline points="14 2 14 8 20 8" />
          <line x1="16" y1="13" x2="8" y2="13" />
          <line x1="16" y1="17" x2="8" y2="17" />
        </svg>
        <h2 class="settings__panel-title">Login-logs</h2>
      </header>
      <p class="settings__panel-description">Download oversigt over dine login-aktiviteter</p>
      <a href="download-logs.php" class="settings__btn settings__btn--secondary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
          <polyline points="7 10 12 15 17 10" />
          <line x1="12" y1="15" x2="12" y2="3" />
        </svg>
        Download logs
      </a>
    </div>

    <?php if (!empty($_SESSION['is_admin'])): ?>
      <!-- Ghost Mode Panel (Admin Only) -->
      <div class="settings__panel">
        <header class="settings__panel-header">
          <svg class="settings__panel-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
            <line x1="1" y1="1" x2="23" y2="23" />
          </svg>
          <h2 class="settings__panel-title">Ghost-mode</h2>
        </header>
        <p class="settings__panel-description">Skjul din aktivitet fra andre agenter</p>
        <div style="display: flex; align-items: center; gap: var(--admin-spacing-md); margin-bottom: var(--admin-spacing-md);">
          <span class="settings__status-badge <?= $isGhost ? 'settings__status-badge--active' : 'settings__status-badge--inactive' ?>">
            <?= $isGhost ? '● Aktiv' : '○ Deaktiveret' ?>
          </span>
        </div>
        <form method="post" action="toggle-ghost.php">
          <input type="hidden" name="action" value="toggle_ghost">
          <button type="submit" class="settings__btn settings__btn--secondary">
            <?= $isGhost ? 'Deaktiver Ghost-mode' : 'Aktivér Ghost-mode' ?>
          </button>
        </form>
      </div>
    <?php endif; ?>

    <!-- Danger Zone Panel -->
    <div class="settings__panel settings__panel--full settings__panel--danger">
      <header class="settings__panel-header">
        <svg class="settings__panel-icon" style="color: #f87171;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
          <line x1="12" y1="9" x2="12" y2="13" />
          <line x1="12" y1="17" x2="12.01" y2="17" />
        </svg>
        <h2 class="settings__panel-title" style="color: #f87171;">Farezone</h2>
      </header>
      <p class="settings__panel-description">Deaktiver eller slet din konto permanent. Denne handling kan ikke fortrydes.</p>
      <form method="post" onsubmit="return confirm('Er du sikker? Denne handling kan ikke fortrydes og vil logge dig ud.')">
        <input type="hidden" name="action" value="deactivate_self">
        <button type="submit" class="settings__btn settings__btn--danger">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6" />
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
          </svg>
          Deaktiver konto
        </button>
      </form>
    </div>

  </div>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>
