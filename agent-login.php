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

    <!-- Command Deck Launcher -->
    <button type="button" 
            class="command-deck-launcher" 
            id="commandDeckLauncher" 
            aria-label="Åbn kommandopanel" 
            aria-expanded="false" 
            aria-controls="commandDeckMenu">
        <span class="command-deck-launcher__icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </span>
        <span class="command-deck-launcher__label">Menu</span>
    </button>

    <!-- Command Deck Overlay -->
    <div class="command-deck-overlay" id="commandDeckOverlay" aria-hidden="true"></div>

    <!-- Command Deck Menu -->
    <nav class="command-deck" 
         id="commandDeckMenu" 
         aria-label="GreyEYE kontrolpanel genveje" 
         aria-hidden="true">
        
        <!-- Close Button -->
        <button type="button" 
                class="command-deck__close" 
                id="commandDeckClose" 
                aria-label="Luk menu">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>

        <!-- Brand -->
        <div class="command-deck__brand">
            <img src="/assets/greyeeye_logo_transparent.png" alt="GreyEYE Logo" class="command-deck__logo" loading="lazy">
            <div class="command-deck__brand-text">
                <span class="command-deck__brand-label">GreyEYE</span>
                <strong class="command-deck__brand-title">Command Deck</strong>
            </div>
        </div>

        <!-- Navigation Heading -->
        <p class="command-deck__heading">Navigator</p>

        <!-- Navigation Items -->
        <div class="command-deck__nav">
            <a href="dashboard.php" class="command-deck__item">
                <span class="command-deck__item-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M3 12l9-9 9 9"/><path d="M9 21V9h6v12"/>
                    </svg>
                </span>
                <span class="command-deck__item-label">Dashboard</span>
            </a>
            <a href="admin.php" class="command-deck__item">
                <span class="command-deck__item-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    </svg>
                </span>
                <span class="command-deck__item-label">Brugerstyring</span>
            </a>
            <a href="settings.php" class="command-deck__item">
                <span class="command-deck__item-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                </span>
                <span class="command-deck__item-label">Indstillinger</span>
            </a>
        </div>

        <!-- Divider -->
        <div class="command-deck__divider"></div>

        <!-- Quick Actions -->
        <div class="command-deck__actions">
            <a href="/" class="command-deck__action" title="Gå til hjemmesiden">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <span>Hjemmeside</span>
            </a>
        </div>

        <!-- Footer -->
        <div class="command-deck__footer">
            <span class="command-deck__status-dot"></span>
            <span class="command-deck__status-text">System online</span>
        </div>
    </nav>

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
