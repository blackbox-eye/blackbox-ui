<?php

/**
 * Agent Login Page
 *
 * Secure login portal for Blackbox EYE Data Intelligence operators.
 * Uses the new admin layout system with componentized login card.
 */

session_start();
require_once __DIR__ . '/includes/env.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/logging.php';
require_once __DIR__ . '/includes/jwt_helper.php';
require_once __DIR__ . '/includes/sso_audit.php';

$page_title = 'Agent Login';
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

// Handle redirect parameter (e.g., redirect=intel24 for SSO handoff)
$redirect_target = $_GET['redirect'] ?? $_POST['redirect'] ?? null;
if ($redirect_target === 'intel24') {
    $_SESSION['login_redirect'] = 'intel24';
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agent_id = trim($_POST['agent_id'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $pin      = trim($_POST['pin'] ?? '');
    $token    = trim($_POST['token'] ?? '');
    $normalizedAgentId = mb_strtolower($agent_id, 'UTF-8');
    $attemptId = $agent_id !== '' ? $agent_id : 'unknown';

    $dbReady = isset($pdo) && defined('BBX_DB_CONNECTED') && BBX_DB_CONNECTED === true;

    if (!$dbReady) {
        bbx_log_error('LOGIN_DB_UNAVAILABLE', ['agent_id' => $attemptId]);
        log_agent_event($attemptId, 'LOGIN_FAILED', ['reason' => 'db_unavailable']);
        $error = "Login er midlertidigt utilgængeligt. Kontakt operations, hvis problemet fortsætter.";
    } else {
        /** @var PDO $pdo */
        try {
            $stmt = $pdo->prepare("SELECT * FROM agents WHERE LOWER(agent_id) = ? LIMIT 1");
            $stmt->execute([$normalizedAgentId]);
            $agent = $stmt->fetch();

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
                    // Capture basic client metadata for personalised dashboard feeds
                    $clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                    // If X-Forwarded-For contains multiple IPs, take the first
                    if (strpos($clientIp, ',') !== false) {
                        $clientIp = trim(explode(',', $clientIp)[0]);
                    }
                    $_SESSION['agent_ip'] = $clientIp;
                    $_SESSION['agent_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

                    if (bbx_jwt_secret_available() && bbx_jwt_library_available()) {
                        try {
                            $tokenBundle = bbx_issue_agent_sso_token($agent);
                            bbx_store_agent_sso_token($tokenBundle);
                            bbx_set_agent_jwt_cookie($tokenBundle['token'], $tokenBundle['expires_at']);
                            $fingerprint = substr(sha1($tokenBundle['token']), 0, 16);
                            bbx_log_sso_event('SSO_TOKEN_ISSUED', [
                                'agent_id' => $agent['agent_id'] ?? null,
                                'uid' => $tokenBundle['payload']['uid'] ?? null,
                                'sub' => $tokenBundle['payload']['sub'] ?? null,
                                'role' => $tokenBundle['payload']['role'] ?? null,
                                'scope' => $tokenBundle['payload']['scope'] ?? null,
                                'expires_at' => $tokenBundle['expires_at'],
                                'token_fingerprint' => $fingerprint,
                            ]);
                            log_agent_event($agent['agent_id'], 'SSO_TOKEN_ISSUED', [
                                'expires_at' => $tokenBundle['expires_at'],
                            ]);
                        } catch (Throwable $jwtError) {
                            bbx_log_error('JWT_ISSUE_FAILED', [
                                'agent_id' => $agent['agent_id'],
                                'message' => $jwtError->getMessage(),
                            ]);
                            bbx_log_sso_event('SSO_TOKEN_MINT_FAILED', [
                                'agent_id' => $agent['agent_id'] ?? $attemptId,
                                'reason' => $jwtError->getMessage(),
                            ]);
                        }
                    }

                    log_agent_event($agent['agent_id'], 'LOGIN_SUCCESS');

                    // Check for pending redirect (e.g., Intel24 SSO)
                    $pending_redirect = $_SESSION['login_redirect'] ?? null;
                    unset($_SESSION['login_redirect']);

                    if ($pending_redirect === 'intel24') {
                        // Redirect to agent-access which will now have a valid JWT
                        header("Location: agent-access.php?launch=intel24");
                        exit;
                    }

                    header("Location: dashboard.php");
                    exit;
                }
                log_agent_event($attemptId, 'LOGIN_FAILED', ['reason' => !$isActive ? 'inactive' : (!$passwordMatch ? 'password' : 'pin')]);
            } else {
                log_agent_event($attemptId, 'LOGIN_FAILED', ['reason' => 'not_found']);
            }
            $error = "Ugyldigt Agent ID, Password eller PIN.";
        } catch (Throwable $exception) {
            bbx_log_error('LOGIN_PROCESSING_ERROR', [
                'agent_id' => $attemptId,
                'message' => $exception->getMessage(),
            ]);
            $error = "Der opstod en systemfejl under login. Prøv igen om lidt.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="da" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($page_title) ?> - Blackbox EYE</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" crossorigin="anonymous">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_black_32x32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_black_256x256.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_black_256x256.png">
    <link rel="shortcut icon" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_black.ico">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/admin.css">
    <?php include __DIR__ . '/includes/qa-bootstrap.php'; ?>
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
        SECURITY: Control Panel er FJERNET fra login-siden.
        Navigation til dashboard, brugerstyring og indstillinger
        er kun tilgængelig efter succesfuld autentificering.
        Se includes/admin-layout.php for Control Panel implementering.
    -->

    <!-- Main Content -->
    <main id="main-content">
        <?php include __DIR__ . '/includes/components/login-card.php'; ?>
    </main>

    <!-- Request Access Section -->
    <?php include __DIR__ . '/includes/components/request-access.php'; ?>

    <?php if (defined('BBX_QA_MODE') && BBX_QA_MODE) {
        include __DIR__ . '/includes/components/qa-debug-panel.php';
    } ?>

    <!-- Scripts -->
    <script src="/assets/js/router-guard.js" defer></script>
    <script src="/assets/js/qa-mode.js" defer></script>
    <script src="/assets/js/interface-menu.js"></script>
    <script src="/assets/js/password-toggle.js"></script>
</body>

</html>
