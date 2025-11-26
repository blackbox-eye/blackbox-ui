<?php
// --- PHP LOGIN LOGIK (FINAL VERSION – PLAIN PASSWORD VERSION) ---

session_start();

// Inkluderer databaseforbindelsen og logging-hjælper
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/logging.php';

$page_title = 'Agent Login';
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

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
            $passwordMatch  = false;
            $rehashNeeded   = false;

            if ($storedPassword !== '') {
                if (password_verify($password, $storedPassword)) {
                    $passwordMatch = true;
                    $rehashNeeded = password_needs_rehash($storedPassword, PASSWORD_DEFAULT);
                } elseif (hash_equals($storedPassword, $password)) {
                    $passwordMatch = true;
                    $rehashNeeded = true;
                }
            }

            $storedPinRaw   = (string) ($agent['pin'] ?? '');
            $providedPinRaw = $pin;

            if ($storedPinRaw !== '' && $providedPinRaw !== '') {
                $pinLength = max(strlen($storedPinRaw), strlen($providedPinRaw));
                $storedPinCanonical = str_pad($storedPinRaw, $pinLength, '0', STR_PAD_LEFT);
                $providedPinCanonical = str_pad($providedPinRaw, $pinLength, '0', STR_PAD_LEFT);
                $pinMatch = hash_equals($storedPinCanonical, $providedPinCanonical);
            } else {
                $pinMatch = false;
            }
            $isActive = strtolower((string) $agent['status']) === 'active';

            if ($passwordMatch && $pinMatch && $isActive) {
                if ($rehashNeeded) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    try {
                        $upgradeStmt = $pdo->prepare("UPDATE agents SET password = ? WHERE id = ?");
                        $upgradeStmt->execute([$newHash, $agent['id']]);
                    } catch (Throwable $upgradeError) {
                        error_log('[LOGIN] Password rehash failed for agent ' . $agent['agent_id'] . ': ' . $upgradeError->getMessage());
                    }
                }

                session_regenerate_id(true);
                $_SESSION['agent_id'] = $agent['agent_id'];
                $_SESSION['is_admin'] = (bool)$agent['is_admin'];
                log_agent_event($agent['agent_id'], 'LOGIN_SUCCESS');
                header("Location: dashboard.php");
                exit;
            }

            $failure = [
                'status'   => $agent['status'] ?? 'unknown',
                'reason'   => !$isActive ? 'inactive_agent' : (!$passwordMatch ? 'password_mismatch' : 'pin_mismatch')
            ];
            log_agent_event($attemptId, 'LOGIN_FAILED', $failure);
        } else {
            log_agent_event($attemptId, 'LOGIN_FAILED', ['reason' => 'agent_not_found']);
        }

        $error = "Ugyldigt Agent ID, Password eller PIN.";
    } else {
        if ($agent_id !== '') {
            log_agent_event($agent_id, 'LOGIN_FAILED', ['reason' => 'db_unavailable']);
        }
        $error = "Databaseforbindelse kunne ikke etableres.";
    }
}
?>
<!DOCTYPE html>
<html lang="da">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Blackbox EYE</title>
    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-dark: #0a0a0a;
            --card-bg: rgba(20, 20, 22, 0.95);
            --card-border: rgba(255, 255, 255, 0.06);
            --text-primary: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.6);
            --text-muted: rgba(255, 255, 255, 0.4);
            --input-bg: rgba(255, 255, 255, 0.03);
            --input-border: rgba(255, 255, 255, 0.08);
            --input-focus: rgba(34, 197, 94, 0.5);
            --btn-success: #22c55e;
            --btn-success-hover: #16a34a;
            --accent-glow: rgba(34, 197, 94, 0.15);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Subtle particle/star background */
        .bg-particles {
            position: fixed;
            inset: 0;
            z-index: 0;
            background: 
                radial-gradient(1px 1px at 20% 30%, rgba(255,255,255,0.15) 1px, transparent 1px),
                radial-gradient(1px 1px at 40% 70%, rgba(255,255,255,0.1) 1px, transparent 1px),
                radial-gradient(1px 1px at 50% 50%, rgba(255,255,255,0.12) 1px, transparent 1px),
                radial-gradient(1.5px 1.5px at 60% 20%, rgba(255,255,255,0.18) 1px, transparent 1px),
                radial-gradient(1px 1px at 70% 60%, rgba(255,255,255,0.08) 1px, transparent 1px),
                radial-gradient(1px 1px at 80% 40%, rgba(255,255,255,0.14) 1px, transparent 1px),
                radial-gradient(1.5px 1.5px at 90% 80%, rgba(255,255,255,0.1) 1px, transparent 1px),
                radial-gradient(1px 1px at 10% 90%, rgba(255,255,255,0.12) 1px, transparent 1px),
                radial-gradient(1px 1px at 30% 10%, rgba(255,255,255,0.16) 1px, transparent 1px),
                radial-gradient(1px 1px at 85% 15%, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 
                550px 550px,
                350px 350px,
                250px 250px,
                450px 450px,
                300px 300px,
                400px 400px,
                500px 500px,
                600px 600px,
                380px 380px,
                420px 420px;
            animation: drift 120s linear infinite;
        }

        @keyframes drift {
            0% { transform: translate(0, 0); }
            50% { transform: translate(-30px, -20px); }
            100% { transform: translate(0, 0); }
        }

        /* Subtle gradient overlay */
        .bg-gradient-overlay {
            position: fixed;
            inset: 0;
            z-index: 1;
            background: radial-gradient(ellipse at 50% 0%, rgba(34, 197, 94, 0.03) 0%, transparent 60%),
                        radial-gradient(ellipse at 50% 100%, rgba(20, 20, 22, 0.8) 0%, transparent 50%);
            pointer-events: none;
        }

        /* Login container */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 1.5rem;
        }

        .login-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5),
                        0 0 0 1px rgba(255, 255, 255, 0.03) inset;
            animation: cardFadeIn 0.6s ease-out;
        }

        @keyframes cardFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Logo section */
        .logo-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(34, 197, 94, 0.05) 100%);
            border: 1px solid rgba(34, 197, 94, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-icon svg {
            width: 28px;
            height: 28px;
            color: var(--btn-success);
        }

        .login-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .login-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 400;
        }

        /* Error message */
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            padding: 0.875rem 1rem;
            border-radius: 10px;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        /* Form styling */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .input-group {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 0.9375rem;
            font-family: inherit;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .form-input:hover {
            border-color: rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.04);
        }

        .form-input:focus {
            border-color: var(--btn-success);
            background: rgba(255, 255, 255, 0.05);
            box-shadow: 0 0 0 3px var(--input-focus);
        }

        /* Submit button */
        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: var(--btn-success);
            color: #000;
            border: none;
            border-radius: 10px;
            font-size: 0.9375rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 0.5rem;
        }

        .submit-btn:hover {
            background: var(--btn-success-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px var(--input-focus);
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--card-border);
        }

        .footer-text {
            font-size: 0.75rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .footer-link {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-link:hover {
            color: var(--btn-success);
        }

        /* Status indicator */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.2);
            border-radius: 100px;
            font-size: 0.75rem;
            color: var(--btn-success);
            margin-bottom: 1.5rem;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            background: var(--btn-success);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.5rem;
            }
            
            .login-title {
                font-size: 1.25rem;
            }
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
    </style>
</head>

<body>
    <!-- Background effects -->
    <div class="bg-particles"></div>
    <div class="bg-gradient-overlay"></div>

    <!-- Login form -->
    <main class="login-wrapper">
        <div class="login-card">
            <!-- Logo & Header -->
            <div class="logo-section">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="M9 12l2 2 4-4"/>
                    </svg>
                </div>
                <div class="status-badge">
                    <span class="status-dot"></span>
                    <span>Sikker forbindelse</span>
                </div>
                <h1 class="login-title">Sikker adgang</h1>
                <p class="login-subtitle">Brug dine TS-Intel legitimationsoplysninger</p>
            </div>

            <!-- Error message -->
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Login form -->
            <form action="agent-login.php" method="post" class="login-form" autocomplete="off">
                <div class="input-group">
                    <label for="agent_id" class="sr-only">Agent ID</label>
                    <input 
                        type="text" 
                        name="agent_id" 
                        id="agent_id" 
                        placeholder="Indtast brugernavn" 
                        required 
                        class="form-input"
                        autocomplete="off"
                        spellcheck="false"
                    >
                </div>
                
                <div class="input-group">
                    <label for="password" class="sr-only">Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        placeholder="Indtast adgangskode" 
                        required 
                        class="form-input"
                        autocomplete="new-password"
                    >
                </div>

                <div class="input-group">
                    <label for="pin" class="sr-only">PIN</label>
                    <input 
                        type="password" 
                        name="pin" 
                        id="pin" 
                        placeholder="Indtast PIN-kode" 
                        required 
                        class="form-input"
                        autocomplete="off"
                        inputmode="numeric"
                    >
                </div>

                <div class="input-group">
                    <label for="token" class="sr-only">Token</label>
                    <input 
                        type="text" 
                        name="token" 
                        id="token" 
                        placeholder="Hardware token (valgfri)" 
                        class="form-input"
                        autocomplete="off"
                    >
                </div>

                <button type="submit" class="submit-btn">
                    Log ind
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <p class="footer-text">
                    Adgang kræver autoriseret hardware-nøgle.<br>
                    Alle login forsøg logges og overvåges.
                </p>
            </div>
        </div>
    </main>
</body>

</html>
