<?php
session_start();
require __DIR__ . '/db.php';

// Hvis brugeren ikke er logget ind, send dem til den korrekte login-side.
if (!isset($_SESSION['agent_id'])) {
    header('Location: agent-login.php'); // RETTET FRA index.php
    exit;
}

// Hent admin-status fra sessionen
$is_admin = $_SESSION['is_admin'] ?? false;

// Sæt sidens titel og inkluder header
$page_title = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<div class="panel">
    <h1>Velkommen, <?php echo htmlspecialchars($_SESSION['agent_id']); ?>!</h1>
    <p>Her kan du tilgå dine agent-funktioner.</p>

    <div class="actions">
        <?php if ($is_admin): ?>
            <a href="admin.php" class="btn">Adminpanel</a>
        <?php endif; ?>
        <a href="settings.php" class="btn">Indstillinger</a>
        <a href="logout.php" class="btn">Log ud</a>
    </div>
</div>

<?php
include __DIR__ . '/includes/footer.php';
?>
