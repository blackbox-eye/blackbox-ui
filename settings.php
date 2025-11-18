<?php
session_start();
require __DIR__ . '/db.php';

// Hvis ikke logget ind, send til login
if (!isset($_SESSION['agent_id'])) {
  header('Location: agent-login.php');
    exit;
}

$page_title = 'Indstillinger';
include __DIR__ . '/includes/header.php';

$error   = '';
$success = '';

// Håndter formular-submits
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) Skift password & PIN
    if (isset($_POST['action']) && $_POST['action']==='change_credentials') {
        $currPass = $_POST['current_password'] ?? '';
        $newPass  = $_POST['new_password']     ?? '';
        $newPin   = trim($_POST['new_pin']     ?? '');

        if ($currPass && $newPass && $newPin) {
            $stmt = $pdo->prepare("SELECT password FROM agents WHERE agent_id = ?");
            $stmt->execute([$_SESSION['agent_id']]);
            $row = $stmt->fetch();

            if ($row && password_verify($currPass, $row['password'])) {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $upd  = $pdo->prepare("
                    UPDATE agents
                       SET password = ?, pin = ?
                     WHERE agent_id = ?
                ");
                $upd->execute([$hash, $newPin, $_SESSION['agent_id']]);
                $success = 'Password og PIN er opdateret.';
            } else {
                $error = 'Nuværende password er forkert.';
            }
        } else {
            $error = 'Udfyld alle felter.';
        }
    }

    // 2) Ændr token
    if (isset($_POST['action']) && $_POST['action']==='regen_token') {
        $newToken = bin2hex(random_bytes(8));
        $upd = $pdo->prepare("
            UPDATE agents
               SET token = ?
             WHERE agent_id = ?
        ");
        $upd->execute([$newToken, $_SESSION['agent_id']]);
        $success = 'Nyt token genereret: ' . htmlspecialchars($newToken);
    }

    // 3) Deaktiver egen konto (soft delete)
    if (isset($_POST['action']) && $_POST['action']==='deactivate_self') {
        $upd = $pdo->prepare("
            UPDATE agents
               SET status = 'deactivated'
             WHERE agent_id = ?
        ");
        $upd->execute([$_SESSION['agent_id']]);
        session_destroy();
        header('Location: agent-login.php?msg=deactivated');
        exit;
    }

    // Øvrige funktioner (placeholder)
    // – download logs, 2FA, email, ghost-mode osv. kan håndteres her senere…
}

?>
<div class="admin-panel">
  <h1>Indstillinger</h1>
  <p>Her kan du ændre dine personlige indstillinger.</p>
  <p><a href="dashboard.php" class="btn">← Tilbage til Dashboard</a></p>

  <?php if ($error): ?>
    <div class="alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert" style="background:var(--green); color:var(--bg-dark)">
      <?= htmlspecialchars($success) ?>
    </div>
  <?php endif; ?>

  <!-- 1) Skift Password & PIN -->
  <section>
    <h2>Skift password & PIN</h2>
    <form method="post">
      <input type="hidden" name="action" value="change_credentials">
      <input name="current_password" type="password" placeholder="Nuværende password" required>
      <input name="new_password"     type="password" placeholder="Nyt password"      required>
      <input name="new_pin"          placeholder="Ny PIN"               required>
      <button type="submit" class="btn">Opdatér</button>
    </form>
  </section>

  <!-- 2) Ændr Token -->
  <section>
    <h2>Token</h2>
    <p>Aktuelt token: <code><?= htmlspecialchars($agentToken ?? '—') ?></code></p>
    <form method="post">
      <input type="hidden" name="action" value="regen_token">
      <button type="submit" class="btn">Generér nyt token</button>
    </form>
  </section>

  <!-- 3) Download login-logs -->
  <section>
    <h2>Login-logs</h2>
    <p><a href="download-logs.php" class="btn">Download dine login-logs</a></p>
  </section>

  <!-- 4) Kontaktinformation -->
  <section>
    <h2>Kontaktinformation</h2>
    <form method="post" action="update-contact.php">
      <input name="email" type="email" placeholder="E-mail" value="<?= htmlspecialchars($userEmail ?? '') ?>" required>
      <button type="submit" class="btn">Opdatér kontakt</button>
    </form>
  </section>

  <!-- 5) Skift Agent-ID -->
  <section>
    <h2>Skift Agent-ID</h2>
    <form method="post" action="change-agentid.php">
      <input name="new_agent_id" placeholder="Nyt Agent ID" required>
      <button type="submit" class="btn">Opdatér Agent ID</button>
    </form>
  </section>

  <!-- 6) Ghost-mode -->
  <section>
    <h2>Ghost-mode</h2>
    <form method="post" action="toggle-ghost.php">
      <input type="hidden" name="action" value="toggle_ghost">
      <button type="submit" class="btn">
        <?= $isGhost ? 'Deaktiver Ghost-mode' : 'Aktivér Ghost-mode' ?>
      </button>
    </form>
  </section>

  <!-- 7) Deaktiver egen konto -->
  <section>
    <h2>Slet/Deaktiver Konto</h2>
    <form method="post" onsubmit="return confirm('Er du sikker? Dette logger dig ud.')">
      <input type="hidden" name="action" value="deactivate_self">
      <button type="submit" class="btn-danger">Deaktiver konto</button>
    </form>
  </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
