<?php
session_start();
require __DIR__ . '/db.php';

$page_title = 'Agent Login';
include __DIR__ . '/includes/header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agent = trim($_POST['agent_id'] ?? '');
    $pass  = $_POST['password']    ?? '';
    $pin   = trim($_POST['pin']     ?? '');
    $token = trim($_POST['token']   ?? '');

    // Hent agent fra databasen
    $stmt = $pdo->prepare("SELECT * FROM agents WHERE agent_id = ?");
    $stmt->execute([$agent]);
    $row = $stmt->fetch();

    if (
        $row
        && password_verify($pass, $row['password'])
        && $pin === $row['pin']
    ) {
        // Gem login og admin-flag i session
        $_SESSION['agent_id'] = $agent;
        $_SESSION['is_admin'] = (bool)$row['is_admin'];

        header("Location: dashboard.php");
        exit;
    }

    $error = 'Ugyldigt Agent ID, Password eller PIN.';
}
?>

<div class="login-panel">
  <img src="assets/logo.png" alt="blackbox.codes logo" class="logo">

  <!-- Stealth-overskrift -->
  <h1>blackbox.codes</h1>

  <!-- Fejlbesked -->
  <?php if ($error): ?>
    <div class="alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- Login-formular -->
  <form method="post">
    <input name="agent_id" placeholder="Agent ID" required>
    <input name="password" type="password" placeholder="Password" required>
    <input name="pin" placeholder="PIN" required>
    <input name="token" placeholder="Token (valgfri)">
    <button type="submit">LOGIN</button>
  </form>

  <p class="hint">
    only access with physical key (YubiKey or similar)
  </p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
