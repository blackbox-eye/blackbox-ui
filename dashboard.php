<?php
session_start();
require __DIR__ . '/db.php';

// Hvis ikke logget ind, send tilbage til login
if (!isset($_SESSION['agent_id'])) {
    header('Location: index.php');
    exit;
}

// Sørg for at is_admin-feltet også ligger i session
// (hvis du glemte at gemme det ved login, skal du trække det fra databasen her)
if (!isset($_SESSION['is_admin'])) {
    $stmt = $pdo->prepare("SELECT is_admin FROM agents WHERE agent_id = ?");
    $stmt->execute([$_SESSION['agent_id']]);
    $_SESSION['is_admin'] = (bool) $stmt->fetchColumn();
}

$page_title = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<div class="panel">
  <h1>Velkommen, <?= htmlspecialchars($_SESSION['agent_id']) ?></h1>
  <p>Her kan du tilgå dine agent-funktioner.</p>

  <div class="actions">
    <?php if ($_SESSION['is_admin']): ?>
      <a href="admin.php"    class="btn">Adminpanel</a>
    <?php endif; ?>
    <a href="settings.php" class="btn">Indstillinger</a>
    <a href="logout.php"   class="btn">Log ud</a>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
