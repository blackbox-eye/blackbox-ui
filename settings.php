<?php

/**
 * Settings Page - Agent Personal Settings
 *
 * Allows agents to manage their personal settings including
 * password, PIN, token, contact info, and account status.
 * Uses the new admin layout system with Command Deck menu.
 */

session_start();
require __DIR__ . '/db.php';

// Redirect if not logged in
if (!isset($_SESSION['agent_id'])) {
  header('Location: agent-login.php');
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
    header('Location: agent-login.php?msg=deactivated');
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

<div class="admin-panel">
  <h1>Indstillinger</h1>
  <p>Her kan du ændre dine personlige indstillinger.</p>
  <p><a href="dashboard.php" class="btn">← Tilbage til Dashboard</a></p>

  <!-- Status Overview Card -->
  <div class="settings-status">
    <p><strong>Aktuelt Agent-ID:</strong> <?= htmlspecialchars($agentData['agent_id'] ?? $agentId) ?></p>
    <p><strong>Ghost-mode:</strong> <?= $isGhost ? 'Aktiv' : 'Deaktiveret' ?></p>
    <p><strong>Aktuelt token:</strong> <code><?= htmlspecialchars($agentToken ?: '—') ?></code></p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <!-- Change Password & PIN Section -->
  <section>
    <h2>Skift password & PIN</h2>
    <form method="post">
      <input type="hidden" name="action" value="change_credentials">
      <input name="current_password" type="password" placeholder="Nuværende password" required>
      <input name="new_password" type="password" placeholder="Nyt password" required>
      <input name="new_pin" placeholder="Ny PIN" required>
      <button type="submit" class="btn">Opdatér</button>
    </form>
  </section>

  <!-- Token Section -->
  <section>
    <h2>Token</h2>
    <p>Aktuelt token: <code><?= htmlspecialchars($agentToken ?: '—') ?></code></p>
    <form method="post">
      <input type="hidden" name="action" value="regen_token">
      <button type="submit" class="btn">Generér nyt token</button>
    </form>
  </section>

  <!-- Login Logs Section -->
  <section>
    <h2>Login-logs</h2>
    <p><a href="download-logs.php" class="btn">Download dine login-logs</a></p>
  </section>

  <!-- Contact Information Section -->
  <section>
    <h2>Kontaktinformation</h2>
    <form method="post" action="update-contact.php">
      <input name="email" type="email" placeholder="E-mail" value="<?= htmlspecialchars($agentEmail) ?>" required>
      <button type="submit" class="btn">Opdatér kontakt</button>
    </form>
  </section>

  <!-- Change Agent ID Section -->
  <section>
    <h2>Skift Agent-ID</h2>
    <form method="post" action="change-agentid.php">
      <input name="new_agent_id" placeholder="Nyt Agent ID" required>
      <button type="submit" class="btn">Opdatér Agent ID</button>
    </form>
  </section>

  <!-- Ghost Mode Section -->
  <section>
    <h2>Ghost-mode</h2>
    <form method="post" action="toggle-ghost.php">
      <input type="hidden" name="action" value="toggle_ghost">
      <button type="submit" class="btn">
        <?= $isGhost ? 'Deaktiver Ghost-mode' : 'Aktivér Ghost-mode' ?>
      </button>
    </form>
  </section>

  <!-- Deactivate Account Section -->
  <section>
    <h2>Slet/Deaktiver Konto</h2>
    <form method="post" onsubmit="return confirm('Er du sikker? Dette logger dig ud.')">
      <input type="hidden" name="action" value="deactivate_self">
      <button type="submit" class="btn btn-danger">Deaktiver konto</button>
    </form>
  </section>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>
