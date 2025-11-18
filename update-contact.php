<?php
// public_html/update-contact.php
session_start();
require __DIR__ . '/db.php';

// 1) Tjek login
if (!isset($_SESSION['agent_id'])) {
  header('Location: agent-login.php');
    exit;
}

$agentId = $_SESSION['agent_id'];
$error   = '';
$success = '';

// 2) Håndter formular submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newEmail = trim($_POST['email'] ?? '');
    if ($newEmail === '' || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Indtast en gyldig e-mailadresse.';
    } else {
        $stmt = $pdo->prepare("UPDATE agents SET email = ? WHERE agent_id = ?");
        $stmt->execute([$newEmail, $agentId]);
        $success = 'Din e-mail er blevet opdateret.';
    }
}

// 3) Hent current e-mail til formular
$stmt = $pdo->prepare("SELECT email FROM agents WHERE agent_id = ?");
$stmt->execute([$agentId]);
$current = $stmt->fetchColumn();

// 4) Render
$page_title = 'Opdater Kontaktinfo';
include __DIR__ . '/includes/header.php';
?>

<div class="admin-panel">
  <h1>Kontaktinformation</h1>
  <p>Her kan du opdatere din e-mailadresse.</p>
  <p><a href="settings.php" class="btn">← Tilbage til Indstillinger</a></p>

  <?php if ($error): ?>
     <div class="alert alert-error"><?=htmlspecialchars($error)?></div>
  <?php elseif ($success): ?>
     <div class="alert alert-success"><?=htmlspecialchars($success)?></div>
  <?php endif; ?>

  <form method="post">
    <input name="email" type="email" placeholder="E-mail" required
           value="<?=htmlspecialchars($current)?>">
    <button type="submit" class="btn">Opdater kontakt</button>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
