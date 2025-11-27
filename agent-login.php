<?php

/**
 * Agent Login Page
 *
 * Secure login portal for GreyEYE Data Intelligence operators.
 * Uses the new admin layout system with componentized login card.
 */

session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/logging.php';

$page_title = 'Agent Login';
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agent_id = trim($_POST['agent_id'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $pin      = trim($_POST['pin'] ?? '');
    $token    = trim($_POST['token'] ?? '');
    $normalizedAgentId = mb_strtolower($agent_id, 'UTF-8');

    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT * FROM agents WHERE LOWER(agent_id) = ? LIMIT 1");
        $stmt->execute([$normalizedAgentId]);
        $agent = $stmt->fetch();
        $attemptId = $agent_id !== '' ? $agent_id : 'unknown';

        if ($agent) {
            $storedPassword = (string) ($agent['password'] ?? '');
            $passwordMatch = false;
            $rehashNeeded = false;

            if ($storedPassword !== '') {
                if (password_verify($password, $storedPassword)) {
                    $passwordMatch = true;
                    $rehashNeeded = password_needs_rehash($storedPassword, PASSWORD_DEFAULT);
                } elseif (hash_equals($storedPassword, $password)) {
                    // Plaintext fallback (development only)
                    $passwordMatch = true;
                    $rehashNeeded = true;
                }
            }

            $storedPinRaw = (string) ($agent['pin'] ?? '');
            $providedPinRaw = $pin;
            $pinMatch = false;

            if ($storedPinRaw !== '' && $providedPinRaw !== '') {
                $pinLength = max(strlen($storedPinRaw), strlen($providedPinRaw));
                $storedPinCanonical = str_pad($storedPinRaw, $pinLength, '0', STR_PAD_LEFT);
                $providedPinCanonical = str_pad($providedPinRaw, $pinLength, '0', STR_PAD_LEFT);
                $pinMatch = hash_equals($storedPinCanonical, $providedPinCanonical);
            }

            $isActive = strtolower((string) $agent['status']) === 'active';

            if ($passwordMatch && $pinMatch && $isActive) {
                if ($rehashNeeded) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    try {
                        $upgradeStmt = $pdo->prepare("UPDATE agents SET password = ? WHERE id = ?");
                        $upgradeStmt->execute([$newHash, $agent['id']]);
                    } catch (Throwable $e) {
                        // Silent fail - password will be rehashed on next login
                    }
                }
                session_regenerate_id(true);
                $_SESSION['agent_id'] = $agent['agent_id'];
                $_SESSION['is_admin'] = (bool)$agent['is_admin'];
                log_agent_event($agent['agent_id'], 'LOGIN_SUCCESS');
                header("Location: dashboard.php");
                exit;
            }
            log_agent_event($attemptId, 'LOGIN_FAILED', ['reason' => !$isActive ? 'inactive' : (!$passwordMatch ? 'password' : 'pin')]);
        } else {
            log_agent_event($attemptId, 'LOGIN_FAILED', ['reason' => 'not_found']);
        }
        $error = "Ugyldigt Agent ID, Password eller PIN.";
    } else {
        $error = "Databaseforbindelse fejlede.";
    }
}
?>
<!DOCTYPE html>
<html lang="da" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($page_title) ?> - GreyEYE</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" crossorigin="anonymous">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_white_32x32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_white_256x256.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_white_256x256.png">
    <link rel="shortcut icon" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_white.ico">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>

<body class="admin-body admin-body--login">
    <!-- Skip Link -->
    <a href="#main-content" class="skip-link">Spring til indhold</a>

    <!-- Back Link -->
    <a href="index.php" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span>Tilbage</span>
    </a>

    <!--
        SECURITY: Command Deck er FJERNET fra login-siden.
        Navigation til dashboard, brugerstyring og indstillinger
        er kun tilgængelig efter succesfuld autentificering.
        Se includes/admin-layout.php for Command Deck implementering.
    -->

    <!-- Main Content -->
    <main id="main-content">
        <?php include __DIR__ . '/includes/components/login-card.php'; ?>
    </main>

    <!-- Request Access Section -->
    <?php include __DIR__ . '/includes/components/request-access.php'; ?>

    <!-- Scripts -->
    <script src="/assets/js/interface-menu.js"></script>
</body>

</html>
