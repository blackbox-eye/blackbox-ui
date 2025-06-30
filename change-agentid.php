<?php
// public_html/change-agentid.php
session_start();
require __DIR__ . '/db.php';

// 1) Tjek login
if (!isset($_SESSION['agent_id'])) {
    header('Location: index.php');
    exit;
}

$oldId   = $_SESSION['agent_id'];
$error   = '';
$success = '';

// 2) Formularbehandling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newId = trim($_POST['new_agent_id'] ?? '');
    if ($newId === '') {
        $error = 'Agent ID må ikke være tomt.';
    } else {
        // Tjek unikhed
        $exists = $pdo
          ->prepare("SELECT COUNT(*) FROM agents WHERE agent_id = ?")
          ->execute([$newId]);
        if ($pdo->prepare("SELECT COUNT(*) FROM agents WHERE agent_id = ?")
                 ->execute([$newId]) && $pdo->prepare("SELECT COUNT(*) FROM agents WHERE agent_id = ?")->fetchColumn() > 0) {
            $error = 'Dette Agent ID er allerede i brug.';
        } else {
            // Opdater
            $stmt = $pdo->prepare("UPDATE agents SET agent_id = ? WHERE agent_id = ?");
            $stmt->execute([$newId, $oldId]);
            $_SESSION['agent_id'] = $newId;
            $success = 'Dit Agent ID er blevet ændret til ' . htmlspecialchars($newId) . '.';
            $oldId = $newId;
        }
    }
}

// 3) Render
$page_title = 'Skift Agent ID';
include __DIR__ . '/includes/header.php';
?>

<div class="admin-panel">
  <h1>Skift Agent-ID</h1>
  <p>Her kan du ændre dit Agent-ID.</p>
  <p><a href="settings.php" class="btn">← Tilbage til Indstillinger</a></p>

  <?php if ($error): ?>
    <div class="alert"><?=htmlspecialchars($error)?></div>
  <?php elseif ($success): ?>
    <div class="alert" style="background:var(--green);color:#01140f;"><?=htmlspecialchars($success)?></div>
  <?php endif; ?>

  <form method="post">
    <input name="new_agent_id" placeholder="Nyt Agent ID" required
           value="<?=htmlspecialchars($oldId)?>">
    <button type="submit" class="btn">Opdater Agent ID</button>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
