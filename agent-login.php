<?php
session_start();
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
      $passwordMatch = false;
      $rehashNeeded = false;

      if ($storedPassword !== '') {
        if (password_verify($password, $storedPassword)) {
          $passwordMatch = true;
          $rehashNeeded = password_needs_rehash($storedPassword, PASSWORD_DEFAULT);
        } elseif (hash_equals($storedPassword, $password)) {
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
<html lang="da">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?> - GreyEYE</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #050505 url('assets/agent_login_baggrund.png') center/cover no-repeat fixed;
      color: #fff;
      position: relative;
      z-index: 0;
      transition: background 0.3s ease;
    }

    body::after {
      content: '';
      position: fixed;
      inset: 0;
      pointer-events: none;
      background: rgba(5, 5, 15, 0);
      backdrop-filter: blur(0px);
      transition: background 0.4s ease, backdrop-filter 0.4s ease;
      z-index: 0;
    }

    body.interaction-active::after {
      background: rgba(5, 5, 15, 0.55);
      backdrop-filter: blur(12px);
    }

    .login-card {
      width: 320px;
      background: rgba(12, 12, 14, 0.92);
      border: 1px solid rgba(212, 175, 55, 0.12);
      border-radius: 12px;
      padding: 1.75rem 1.5rem 1.5rem;
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      box-shadow: 0 16px 48px rgba(0, 0, 0, 0.6);
      position: relative;
      z-index: 1;
      transition: background 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
    }

    body.interaction-active .login-card {
      background: rgba(12, 12, 14, 0.82);
      box-shadow: 0 22px 54px rgba(0, 0, 0, 0.7);
      border-color: rgba(212, 175, 55, 0.2);
    }

    .card-meta {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      gap: 0.45rem;
      margin-bottom: 0.75rem;
    }

    .powered-label {
      font-size: 0.58rem;
      font-style: italic;
      letter-spacing: 0.05em;
      color: rgba(255, 255, 255, 0.6);
      text-transform: uppercase;
    }

    .powered-logo {
      width: 104px;
      height: auto;
      filter: drop-shadow(0 1px 6px rgba(212, 175, 55, 0.3));
    }

    .logo-section {
      text-align: center;
      margin-bottom: 1.25rem;
    }

    .logo-img {
      display: block;
      height: auto;
    }

    .logo-img--primary {
      width: 220px;
      max-width: 90%;
      margin: 0 auto 0.85rem;
      filter: drop-shadow(0 4px 16px rgba(18, 20, 32, 0.45));
    }

    .title {
      font-size: 1.05rem;
      font-weight: 600;
      color: #fff;
      margin-bottom: 0.4rem;
    }

    .subtitle {
      font-size: 0.68rem;
      color: rgba(255, 255, 255, 0.58);
      letter-spacing: 0.01em;
    }

    .error-box {
      background: rgba(220, 38, 38, 0.15);
      border: 1px solid rgba(220, 38, 38, 0.3);
      color: #fca5a5;
      padding: 0.5rem;
      border-radius: 6px;
      font-size: 0.65rem;
      text-align: center;
      margin-bottom: 0.75rem;
    }

    .form-group {
      margin-bottom: 0.6rem;
    }

    .form-input {
      width: 100%;
      padding: 0.55rem 0.7rem;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 6px;
      color: #fff;
      font-size: 0.75rem;
      font-family: inherit;
      transition: all 0.2s;
    }

    .form-input::placeholder {
      color: rgba(255, 255, 255, 0.35);
    }

    .form-input:focus {
      outline: none;
      border-color: #d4af37;
      box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.25);
    }

    .submit-btn {
      width: 100%;
      padding: 0.6rem;
      background: linear-gradient(135deg, #d4af37 0%, #b8960f 100%);
      border: none;
      border-radius: 6px;
      color: #000;
      font-size: 0.7rem;
      font-weight: 600;
      font-family: inherit;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      cursor: pointer;
      transition: all 0.2s;
      margin-top: 0.25rem;
    }

    .submit-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(212, 175, 55, 0.4);
    }

    .footer {
      text-align: center;
      margin-top: 0.75rem;
      padding-top: 0.75rem;
      border-top: 1px solid rgba(255, 255, 255, 0.06);
    }

    .footer p {
      font-size: 0.55rem;
      color: rgba(255, 255, 255, 0.35);
      line-height: 1.4;
    }

    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      border: 0;
    }

    /* Back button */
    .back-link {
      position: fixed;
      top: 20px;
      left: 20px;
      display: flex;
      align-items: center;
      gap: 6px;
      color: rgba(255, 255, 255, 0.5);
      text-decoration: none;
      font-size: 0.8rem;
      font-weight: 500;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      padding: 8px 12px;
      border-radius: 4px;
      background: rgba(0, 0, 0, 0.3);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      z-index: 1;
    }

    .back-link:hover {
      color: #d4af37;
      background: rgba(0, 0, 0, 0.5);
      border-color: rgba(212, 175, 55, 0.3);
      transform: translateX(-3px);
    }

    .back-link svg {
      width: 14px;
      height: 14px;
      transition: transform 0.3s ease;
    }

    .back-link:hover svg {
      transform: translateX(-2px);
    }
  </style>
</head>

<body>
  <a href="index.php" class="back-link">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
    </svg>
    <span>Tilbage</span>
  </a>

  <main class="login-card">
    <div class="card-meta">
      <span class="powered-label">… Powered by</span>
      <img src="assets/blackbox_logo_black.png" alt="Powered by BLACKBOX EYE™" class="powered-logo" loading="lazy">
    </div>
    <div class="logo-section">
      <img src="assets/greyeeye_logo_transparent.png" alt="GreyEYE Data Intelligence" class="logo-img logo-img--primary" loading="lazy">
      <h1 class="title">Sikker adgang</h1>
      <p class="subtitle">GreyEYE Data Intelligence (GDI) operatør-portal</p>
    </div> <?php if ($error): ?>
      <div class="error-box"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="agent-login.php" method="post" autocomplete="off">
      <div class="form-group">
        <label for="agent_id" class="sr-only">Brugernavn</label>
        <input type="text" name="agent_id" id="agent_id" placeholder="Brugernavn" required class="form-input" autocomplete="off">
      </div>
      <div class="form-group">
        <label for="password" class="sr-only">Adgangskode</label>
        <input type="password" name="password" id="password" placeholder="Adgangskode" required class="form-input" autocomplete="new-password">
      </div>
      <div class="form-group">
        <label for="pin" class="sr-only">PIN-kode</label>
        <input type="password" name="pin" id="pin" placeholder="PIN-kode" required class="form-input" inputmode="numeric">
      </div>
      <div class="form-group">
        <label for="token" class="sr-only">Token</label>
        <input type="text" name="token" id="token" placeholder="Token (valgfri)" class="form-input">
      </div>
      <button type="submit" class="submit-btn">Log ind</button>
    </form>

    <div class="footer">
      <p>Adgang kræver autoriseret hardware-nøgle.<br>Alle forsøg logges.</p>
    </div>
  </main>
  <script>
    (function() {
      const body = document.body;
      const card = document.querySelector('.login-card');
      if (!body || !card) {
        return;
      }

      const interactiveElements = card.querySelectorAll('input, button');

      const activate = () => body.classList.add('interaction-active');

      const maybeDeactivate = () => {
        setTimeout(() => {
          const activeElement = document.activeElement;
          if (activeElement && card.contains(activeElement)) {
            return;
          }
          body.classList.remove('interaction-active');
        }, 60);
      };

      interactiveElements.forEach((el) => {
        el.addEventListener('focus', activate);
        el.addEventListener('blur', maybeDeactivate);
        el.addEventListener('click', activate);
      });

      card.addEventListener('mouseenter', activate);
      card.addEventListener('mouseleave', maybeDeactivate);

      document.addEventListener('click', (event) => {
        if (!card.contains(event.target)) {
          body.classList.remove('interaction-active');
        }
      });
    })();
  </script>
</body>

</html>
