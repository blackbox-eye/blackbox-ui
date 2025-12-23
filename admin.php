<?php

/**
 * Admin Panel - User Management
 *
 * Administrative interface for managing agents/users.
 * Uses admin-layout.php with modern panel-based design.
 */

session_start();
require __DIR__ . '/db.php';

// Redirect if not logged in
if (!isset($_SESSION['agent_id'])) {
  header('Location: gdi-login.php');
  exit;
}

// Redirect if not admin
if (empty($_SESSION['is_admin'])) {
  header('Location: dashboard.php');
  exit;
}

/**
 * Get last login timestamp for an agent from their log file.
 */
function getLastLoginForAgent(string $agentId): string
{
  $logDir = __DIR__ . '/logs/';
  $safeAgent = preg_replace('/[^a-zA-Z0-9_-]/', '_', $agentId);
  if ($safeAgent === '' || !is_dir($logDir)) {
    return '—';
  }

  $logPath = $logDir . $safeAgent . '.log';
  if (!is_readable($logPath)) {
    return '—';
  }

  $lines = @file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  if (!$lines) {
    return '—';
  }

  for ($i = count($lines) - 1; $i >= 0; $i--) {
    $line = $lines[$i];
    if (strpos($line, 'event=LOGIN_SUCCESS') === false) {
      continue;
    }
    $segments = explode(' | ', $line);
    $timestamp = trim($segments[0] ?? '');
    if ($timestamp === '') {
      continue;
    }
    try {
      $dt = new DateTimeImmutable($timestamp);
      return $dt->format('Y-m-d H:i');
    } catch (Throwable $e) {
      return '—';
    }
  }

  return '—';
}

// Handle toggle and delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
  $id     = (int) $_POST['id'];
  $action = $_POST['action'];

  if ($action === 'toggle') {
    $stmt = $pdo->prepare("
            UPDATE agents
               SET status = IF(status='active','deactivated','active')
             WHERE id = ?
        ");
    $stmt->execute([$id]);
  } elseif ($action === 'delete') {
    $stmt = $pdo->prepare("DELETE FROM agents WHERE id = ?");
    $stmt->execute([$id]);
  }

  header('Location: admin.php');
  exit;
}

// Handle new agent creation
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_agent'])) {
  $agent_id = trim($_POST['agent_id'] ?? '');
  $password = $_POST['password'] ?? '';
  $pin      = trim($_POST['pin'] ?? '');
  $token    = trim($_POST['token'] ?? '');
  $isAdmin  = isset($_POST['is_admin']) ? 1 : 0;

  if ($agent_id && $password && $pin) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
            INSERT INTO agents (agent_id, password, pin, token, is_admin)
            VALUES (?, ?, ?, ?, ?)
        ");
    $stmt->execute([$agent_id, $hash, $pin, $token, $isAdmin]);
    $success = 'Agent "' . $agent_id . '" er oprettet.';
  } else {
    $error = 'Udfyld alle påkrævede felter.';
  }
}

// Fetch all agents
$agents = $pdo
  ->query("SELECT id, agent_id, pin, token, status, is_admin, ghost FROM agents ORDER BY id")
  ->fetchAll();

// Calculate stats
$totalAgents = count($agents);
$activeAgents = count(array_filter($agents, fn($a) => $a['status'] === 'active'));
$adminCount = count(array_filter($agents, fn($a) => $a['is_admin']));
$ghostCount = count(array_filter($agents, fn($a) => !empty($a['ghost'])));

// Set page variables for admin layout
$page_title = 'Brugerstyring';
$current_admin_page = 'admin';

// Include admin layout header
include __DIR__ . '/includes/admin-layout.php';
?>

<!-- Admin Panel Styles -->
<style>
  .user-mgmt {
    max-width: 1200px;
    margin: 0 auto;
  }

  .user-mgmt__stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--admin-spacing-md);
    margin-bottom: var(--admin-spacing-xl);
  }

  @media (min-width: 640px) {
    .user-mgmt__stats {
      grid-template-columns: repeat(4, 1fr);
    }
  }

  .user-mgmt__stat {
    background: var(--admin-bg-secondary);
    border: 1px solid var(--admin-border-subtle);
    border-radius: var(--admin-border-radius);
    padding: var(--admin-spacing-md);
    text-align: center;
    transition: border-color 0.2s;
  }

  .user-mgmt__stat:hover {
    border-color: var(--admin-border-gold);
  }

  .user-mgmt__stat-value {
    display: block;
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--admin-text-gold);
    line-height: 1;
  }

  .user-mgmt__stat-label {
    font-size: 0.68rem;
    color: var(--admin-text-muted);
    margin-top: 0.35rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .user-mgmt__panel {
    background: var(--admin-bg-secondary);
    border: 1px solid var(--admin-border-subtle);
    border-radius: var(--admin-border-radius);
    padding: var(--admin-spacing-lg);
    margin-bottom: var(--admin-spacing-lg);
  }

  .user-mgmt__panel:hover {
    border-color: var(--admin-border-gold);
  }

  .user-mgmt__panel-header {
    display: flex;
    align-items: center;
    gap: var(--admin-spacing-sm);
    margin-bottom: var(--admin-spacing-md);
    padding-bottom: var(--admin-spacing-sm);
    border-bottom: 1px solid var(--admin-border-subtle);
  }

  .user-mgmt__panel-icon {
    width: 24px;
    height: 24px;
    color: var(--admin-gold);
  }

  .user-mgmt__panel-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--admin-text-gold);
    margin: 0;
  }

  .user-mgmt__form {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--admin-spacing-sm);
  }

  @media (min-width: 640px) {
    .user-mgmt__form {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (min-width: 900px) {
    .user-mgmt__form {
      grid-template-columns: repeat(4, 1fr) auto;
      align-items: end;
    }
  }

  .user-mgmt__input {
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

  .user-mgmt__input::placeholder {
    color: var(--admin-text-muted);
  }

  .user-mgmt__input:focus {
    outline: none;
    border-color: var(--admin-gold);
    box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
  }

  .user-mgmt__checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: var(--admin-text-secondary);
    cursor: pointer;
  }

  .user-mgmt__checkbox-label input {
    accent-color: var(--admin-gold);
  }

  .user-mgmt__btn {
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

  .user-mgmt__btn--primary {
    background: linear-gradient(135deg, var(--admin-gold) 0%, var(--admin-gold-dark) 100%);
    color: #000;
  }

  .user-mgmt__btn--primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(212, 175, 55, 0.35);
  }

  .user-mgmt__btn--small {
    padding: 0.35rem 0.65rem;
    font-size: 0.65rem;
    min-height: auto;
  }

  .user-mgmt__btn--secondary {
    background: rgba(255, 255, 255, 0.05);
    border-color: var(--admin-border-subtle);
    color: var(--admin-text-secondary);
  }

  .user-mgmt__btn--secondary:hover {
    border-color: var(--admin-gold);
    color: var(--admin-text-gold);
  }

  .user-mgmt__btn--danger {
    background: rgba(248, 113, 113, 0.15);
    border-color: rgba(248, 113, 113, 0.4);
    color: #f87171;
  }

  .user-mgmt__btn--danger:hover {
    background: rgba(248, 113, 113, 0.25);
    border-color: #f87171;
  }

  .user-mgmt__table-wrapper {
    overflow-x: auto;
    border-radius: var(--admin-border-radius);
    -webkit-overflow-scrolling: touch;
  }

  .user-mgmt__table {
    width: 100%;
    min-width: 500px;
    border-collapse: collapse;
    font-size: 0.75rem;
  }

  .user-mgmt__table th {
    text-align: left;
    padding: var(--admin-spacing-sm) var(--admin-spacing-md);
    background: rgba(0, 0, 0, 0.3);
    color: var(--admin-text-secondary);
    font-weight: 500;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid var(--admin-border-subtle);
    white-space: nowrap;
  }

  /* Hide less important columns on smaller screens */
  @media (max-width: 900px) {

    .user-mgmt__table .col-pin,
    .user-mgmt__table .col-token,
    .user-mgmt__table .col-ghost {
      display: none;
    }
  }

  @media (max-width: 640px) {

    .user-mgmt__table .col-id,
    .user-mgmt__table .col-last-login {
      display: none;
    }

    .user-mgmt__table {
      min-width: 350px;
    }
  }

  .user-mgmt__table td {
    padding: var(--admin-spacing-sm) var(--admin-spacing-md);
    border-bottom: 1px solid var(--admin-border-subtle);
    color: var(--admin-text-primary);
    vertical-align: middle;
  }

  .user-mgmt__table tbody tr:last-child td {
    border-bottom: none;
  }

  .user-mgmt__table tbody tr:hover {
    background: rgba(255, 255, 255, 0.02);
  }

  .user-mgmt__status {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.5rem;
    border-radius: 999px;
    font-size: 0.6rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .user-mgmt__status--active {
    background: rgba(74, 222, 128, 0.15);
    color: #4ade80;
  }

  .user-mgmt__status--active::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #4ade80;
    animation: pulse-dot 2s ease-in-out infinite;
  }

  @keyframes pulse-dot {

    0%,
    100% {
      opacity: 1;
    }

    50% {
      opacity: 0.5;
    }
  }

  .user-mgmt__status--inactive {
    background: rgba(248, 113, 113, 0.15);
    color: #f87171;
  }

  .user-mgmt__badge {
    display: inline-block;
    font-size: 0.9rem;
  }

  .user-mgmt__token {
    font-family: 'Fira Code', monospace;
    font-size: 0.68rem;
    color: var(--admin-text-muted);
    background: rgba(0, 0, 0, 0.2);
    padding: 0.15rem 0.35rem;
    border-radius: 4px;
    max-width: 80px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: inline-block;
  }

  .user-mgmt__actions {
    display: flex;
    gap: 0.35rem;
  }

  .user-mgmt__alert {
    display: flex;
    align-items: center;
    gap: var(--admin-spacing-sm);
    padding: var(--admin-spacing-sm) var(--admin-spacing-md);
    border-radius: var(--admin-border-radius-sm);
    font-size: 0.75rem;
    margin-bottom: var(--admin-spacing-lg);
  }

  .user-mgmt__alert--error {
    background: rgba(248, 113, 113, 0.1);
    border: 1px solid rgba(248, 113, 113, 0.3);
    color: #fca5a5;
  }

  .user-mgmt__alert--success {
    background: rgba(74, 222, 128, 0.1);
    border: 1px solid rgba(74, 222, 128, 0.3);
    color: #86efac;
  }
</style>

<!-- Admin Content -->
<div class="user-mgmt admin-page">
  <!-- Page Header -->
  <header class="admin-page__header">
    <div>
      <h1 class="admin-page__title">
        <span class="admin-page__title-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
          </svg>
        </span>
        Brugerstyring
      </h1>
      <p class="admin-page__subtitle">Administrer agenter og brugeradgang</p>
    </div>
  </header>

  <!-- Alerts -->
  <?php if ($error): ?>
    <div class="user-mgmt__alert user-mgmt__alert--error">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10" />
        <line x1="12" y1="8" x2="12" y2="12" />
        <line x1="12" y1="16" x2="12.01" y2="16" />
      </svg>
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="user-mgmt__alert user-mgmt__alert--success">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
        <polyline points="22 4 12 14.01 9 11.01" />
      </svg>
      <?= htmlspecialchars($success) ?>
    </div>
  <?php endif; ?>

  <!-- Stats Row -->
  <div class="user-mgmt__stats">
    <div class="user-mgmt__stat">
      <span class="user-mgmt__stat-value"><?= $totalAgents ?></span>
      <span class="user-mgmt__stat-label">Total Agenter</span>
    </div>
    <div class="user-mgmt__stat">
      <span class="user-mgmt__stat-value"><?= $activeAgents ?></span>
      <span class="user-mgmt__stat-label">Aktive</span>
    </div>
    <div class="user-mgmt__stat">
      <span class="user-mgmt__stat-value"><?= $adminCount ?></span>
      <span class="user-mgmt__stat-label">Admins</span>
    </div>
    <div class="user-mgmt__stat">
      <span class="user-mgmt__stat-value"><?= $ghostCount ?></span>
      <span class="user-mgmt__stat-label">Ghost Mode</span>
    </div>
  </div>

  <!-- Create New Agent Panel -->
  <div class="user-mgmt__panel">
    <header class="user-mgmt__panel-header">
      <svg class="user-mgmt__panel-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
        <circle cx="8.5" cy="7" r="4" />
        <line x1="20" y1="8" x2="20" y2="14" />
        <line x1="23" y1="11" x2="17" y2="11" />
      </svg>
      <h2 class="user-mgmt__panel-title">Opret ny agent</h2>
    </header>
    <form method="post" class="user-mgmt__form">
      <input type="hidden" name="create_agent" value="1">
      <input class="user-mgmt__input" name="agent_id" placeholder="Agent ID *" required>
      <input class="user-mgmt__input" name="password" type="password" placeholder="Password *" required autocomplete="new-password">
      <input class="user-mgmt__input" name="pin" placeholder="PIN *" required>
      <input class="user-mgmt__input" name="token" placeholder="Token (valgfri)">
      <div style="display: flex; align-items: center; gap: var(--admin-spacing-md);">
        <label class="user-mgmt__checkbox-label">
          <input type="checkbox" name="is_admin" value="1">
          Admin-adgang
        </label>
        <button type="submit" class="user-mgmt__btn user-mgmt__btn--primary">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
          </svg>
          Opret
        </button>
      </div>
    </form>
  </div>

  <!-- Existing Agents Panel -->
  <div class="user-mgmt__panel">
    <header class="user-mgmt__panel-header">
      <svg class="user-mgmt__panel-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
        <circle cx="9" cy="7" r="4" />
        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
      </svg>
      <h2 class="user-mgmt__panel-title">Eksisterende agenter</h2>
    </header>
    <div class="user-mgmt__table-wrapper">
      <table class="user-mgmt__table">
        <thead>
          <tr>
            <th class="col-id">ID</th>
            <th class="col-agent-id">Agent ID</th>
            <th class="col-pin">PIN</th>
            <th class="col-status">Status</th>
            <th class="col-role">Rolle</th>
            <th class="col-ghost">Ghost</th>
            <th class="col-token">Token</th>
            <th class="col-last-login">Sidste login</th>
            <th class="col-actions">Handlinger</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($agents as $a): ?>
            <tr>
              <td class="col-id"><?= htmlspecialchars($a['id']) ?></td>
              <td class="col-agent-id"><strong><?= htmlspecialchars($a['agent_id']) ?></strong></td>
              <td class="col-pin"><?= htmlspecialchars($a['pin']) ?></td>
              <td class="col-status">
                <span class="user-mgmt__status <?= $a['status'] === 'active' ? 'user-mgmt__status--active' : 'user-mgmt__status--inactive' ?>">
                  <?= $a['status'] === 'active' ? 'Aktiv' : 'Deaktiveret' ?>
                </span>
              </td>
              <td class="col-role">
                <span class="user-mgmt__badge"><?= $a['is_admin'] ? '👑' : '👤' ?></span>
              </td>
              <td class="col-ghost">
                <span class="user-mgmt__badge"><?= !empty($a['ghost']) ? '👻' : '—' ?></span>
              </td>
              <td class="col-token">
                <?php if ($a['token']): ?>
                  <span class="user-mgmt__token" title="<?= htmlspecialchars($a['token']) ?>">
                    <?= htmlspecialchars($a['token']) ?>
                  </span>
                <?php else: ?>
                  <span style="color: var(--admin-text-muted);">—</span>
                <?php endif; ?>
              </td>
              <td class="col-last-login"><?= htmlspecialchars(getLastLoginForAgent($a['agent_id'])) ?></td>
              <td class="col-actions">
                <div class="user-mgmt__actions">
                  <form method="post" style="display: contents;">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($a['id']) ?>">
                    <input type="hidden" name="action" value="toggle">
                    <button type="submit" class="user-mgmt__btn user-mgmt__btn--small user-mgmt__btn--secondary">
                      <?= $a['status'] === 'active' ? 'Deaktiver' : 'Aktivér' ?>
                    </button>
                  </form>
                  <form method="post" style="display: contents;" onsubmit="return confirm('Er du sikker på at du vil slette denne agent?')">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($a['id']) ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="user-mgmt__btn user-mgmt__btn--small user-mgmt__btn--danger">
                      Slet
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>
