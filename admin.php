<?php

/**
 * Admin Panel - User Management
 *
 * Administrative interface for managing agents/users.
 * Uses the new admin layout system with Command Deck menu.
 */

session_start();
require __DIR__ . '/db.php';

// Redirect if not logged in
if (!isset($_SESSION['agent_id'])) {
  header('Location: agent-login.php');
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
    header('Location: admin.php');
    exit;
  } else {
    $error = 'Udfyld alle påkrævede felter.';
  }
}

// Fetch all agents
$agents = $pdo
  ->query("SELECT id, agent_id, pin, token, status, is_admin, ghost FROM agents ORDER BY id")
  ->fetchAll();

// Set page variables for admin layout
$page_title = 'Adminpanel';
$current_admin_page = 'admin';

// Include admin layout header
include __DIR__ . '/includes/admin-layout.php';
?>

<div class="admin-panel">
  <h1>Adminpanel</h1>
  <p>Her kan du oprette, deaktivere eller slette agenter.</p>
  <p>
    <a href="dashboard.php" class="btn">← Tilbage til Dashboard</a>
    <a href="logout.php" class="btn btn-danger" style="margin-left:8px;">Log ud</a>
  </p>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- Create New Agent Section -->
  <section>
    <h2>Opret ny agent</h2>
    <form method="post">
      <input type="hidden" name="create_agent" value="1">
      <input name="agent_id" placeholder="Agent ID" required>
      <input name="password" type="password" placeholder="Password" required>
      <input name="pin" placeholder="PIN" required>
      <input name="token" placeholder="Token (valgfri)">
      <label style="display:block; margin:8px 0;">
        <input type="checkbox" name="is_admin" value="1">
        Giv admin-adgang
      </label>
      <button type="submit" class="btn">Opret agent</button>
    </form>
  </section>

  <!-- Existing Agents Section -->
  <section>
    <h2>Eksisterende agenter</h2>
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Agent ID</th>
          <th>PIN</th>
          <th>Status</th>
          <th>Admin</th>
          <th>Ghost</th>
          <th>Token</th>
          <th>Sidste login</th>
          <th>Handling</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($agents as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a['id']) ?></td>
            <td><?= htmlspecialchars($a['agent_id']) ?></td>
            <td><?= htmlspecialchars($a['pin']) ?></td>
            <td>
              <span class="status <?= $a['status'] === 'active' ? 'pulse-active' : 'pulse-deactivated' ?>">
                <?= htmlspecialchars(ucfirst($a['status'])) ?>
              </span>
            </td>
            <td><?= $a['is_admin'] ? '✅' : '—' ?></td>
            <td><?= !empty($a['ghost']) ? '👻' : '—' ?></td>
            <td><?= htmlspecialchars($a['token'] ?? '') ?></td>
            <td><?= htmlspecialchars(getLastLoginForAgent($a['agent_id'])) ?></td>
            <td>
              <form method="post" style="display:inline">
                <input type="hidden" name="id" value="<?= htmlspecialchars($a['id']) ?>">
                <input type="hidden" name="action" value="toggle">
                <button type="submit" class="btn-sm">
                  <?= $a['status'] === 'active' ? 'Deaktiver' : 'Genaktiver' ?>
                </button>
              </form>
              <form method="post" style="display:inline; margin-left:4px">
                <input type="hidden" name="id" value="<?= htmlspecialchars($a['id']) ?>">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn-sm btn-danger">Slet</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>
