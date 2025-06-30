<?php
session_start();
require __DIR__ . '/db.php';

// 1) Redirect hvis ikke logget ind eller ikke admin
if (!isset($_SESSION['agent_id'], $_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: dashboard.php');
    exit;
}

$page_title = 'Adminpanel';
include __DIR__ . '/includes/header.php';

// 2) Håndter toggle og slet
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

// 3) Opret ny agent
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

// 4) Hent alle agenter
$agents = $pdo
    ->query("SELECT id, agent_id, pin, token, status, is_admin FROM agents ORDER BY id")
    ->fetchAll();
?>

<div class="admin-panel">
  <h1>Adminpanel</h1>
  <p>Her kan du oprette, deaktivere eller slette agenter.</p>
  <p><a href="dashboard.php" class="btn">← Tilbage til Dashboard</a></p>

  <?php if ($error): ?>
    <div class="alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- 3.1) Opret ny agent -->
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

  <!-- 4.1) Eksisterende agenter -->
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
          <th>Token</th>
          <th>Handling</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($agents as $a): ?>
        <tr>
          <td><?= htmlentities($a['id']) ?></td>
          <td><?= htmlentities($a['agent_id']) ?></td>
          <td><?= htmlentities($a['pin']) ?></td>
          <td>
            <span class="status <?= $a['status']==='active' ? 'pulse-active' : 'pulse-deactivated' ?>">
              <?= ucfirst($a['status']) ?>
            </span>
          </td>
          <td>
            <?= $a['is_admin'] ? '✅' : '—' ?>
          </td>
          <td><?= htmlentities($a['token']) ?></td>
          <td>
            <form method="post" style="display:inline">
              <input type="hidden" name="id"     value="<?= $a['id'] ?>">
              <input type="hidden" name="action" value="toggle">
              <button type="submit" class="btn-sm">
                <?= $a['status']==='active' ? 'Deaktiver' : 'Genaktiver' ?>
              </button>
            </form>
            <form method="post" style="display:inline; margin-left:4px">
              <input type="hidden" name="id"     value="<?= $a['id'] ?>">
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

<?php include __DIR__ . '/includes/footer.php'; ?>
