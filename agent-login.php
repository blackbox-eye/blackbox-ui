<?php
// Starter sessionen for at kunne gemme login-status
session_start();

// Inkluderer databaseforbindelsen
require_once './db.php'; 

// Definerer sidens titel og inkluderer header-filen, som indeholder starten af HTML'en
$page_title = 'Agent Login';

// Tjekker om der er en fejlmeddelelse fra en tidligere handling
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']); // Fjerner fejlmeddelelsen efter den er vist

// Håndterer login-forsøg, når formularen submittes via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agent_id = trim($_POST['agent_id'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $pin = trim($_POST['pin'] ?? '');
    $token = trim($_POST['token'] ?? ''); // Yubikey eller lignende

    // Forbereder og udfører databasekald for at finde agenten
    $stmt = $pdo->prepare("SELECT * FROM agents WHERE agent_id = ?");
    $stmt->execute([$agent_id]);
    $agent = $stmt->fetch();

    // Validerer login-oplysningerne
    if ($agent && password_verify($password, $agent['password']) && $pin === $agent['pin']) {
        // Login er succesfuldt
        $_SESSION['agent_id'] = $agent['agent_id'];
        $_SESSION['is_admin'] = (bool)$agent['is_admin'];
        
        // Viderestiller til dashboardet
        header("Location: dashboard.php");
        exit;
    } else {
        // Login fejlede, sætter en fejlmeddelelse
        $error = "Ugyldigt Agent ID, Password eller PIN.";
    }
}
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Blackbox EYE</title>
    <!-- Linker til dit stylesheet. Sørg for at stien er korrekt. -->
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <div class="login-panel">
        <!-- Logo -->
        <img src="assets/logo.png" alt="Blackbox Codes Logo" class="logo">
        
        <!-- Overskrift -->
        <h1>blackbox.codes</h1>
        
        <!-- Viser fejlmeddelelse hvis der er en -->
        <?php if (!empty($error)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Login Formular -->
        <form action="agent-login.php" method="post" class="login-form">
            <input type="text" name="agent_id" placeholder="Agent ID" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="pin" placeholder="PIN" required>
            <input type="text" name="token" placeholder="Token (valgfri)">
            <button type="submit">LOGIN</button>
        </form>

        <!-- Ekstra info i bunden -->
        <div class="p-div">
            <p class="b-ini">Only access with physical key (Yubikey or similar)</p>
        </div>
    </div>
</body>
</html>
