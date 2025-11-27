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
      flex-direction: row;
      align-items: center;
      justify-content: center;
      flex-wrap: wrap;
      gap: clamp(2rem, 6vw, 5rem);
      padding: clamp(2rem, 5vw, 4rem) clamp(1.5rem, 5vw, 4.5rem);
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
      background: rgba(5, 5, 15, 0.32);
      backdrop-filter: blur(8px);
    }

    .login-card {
      width: 340px;
      max-width: 92vw;
      margin: 0 auto;
      background: rgba(12, 12, 14, 0.92);
      border: 1px solid rgba(212, 175, 55, 0.12);
      border-radius: 12px;
      padding: 1.65rem 1.45rem 1.4rem;
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
      position: absolute;
      top: 0.85rem;
      right: 0.95rem;
      display: flex;
      align-items: center;
      gap: 0.25rem;
      padding: 0;
      background: transparent;
      border: none;
      box-shadow: none;
    }

    .powered-label {
      font-size: 0.39rem;
      font-style: italic;
      letter-spacing: 0.16em;
      color: rgba(255, 255, 255, 0.65);
      text-transform: uppercase;
      white-space: nowrap;
    }

    .powered-logo {
      width: 84px;
      height: auto;
      filter: drop-shadow(0 1px 7px rgba(212, 175, 55, 0.22));
    }

    .logo-section {
      text-align: center;
      margin: 2.35rem auto 1.15rem;
      max-width: 240px;
    }

    .logo-img {
      display: block;
      height: auto;
    }

    .logo-img--primary {
      width: 240px;
      max-width: 100%;
      margin: 0 auto 0.6rem;
      filter: drop-shadow(0 6px 22px rgba(18, 20, 32, 0.5));
    }

    .title {
      font-size: 1rem;
      font-weight: 600;
      color: #fff;
      margin-bottom: 0.3rem;
    }

    .subtitle {
      font-size: 0.66rem;
      color: rgba(255, 255, 255, 0.62);
      letter-spacing: 0.025em;
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

    .request-access {
      margin-top: 1.5rem;
      text-align: left;
      max-width: 360px;
      color: rgba(255, 255, 255, 0.72);
      font-size: 0.68rem;
      line-height: 1.45;
    }

    .request-access strong {
      color: #fdd15c;
    }

    .request-access-actions {
      margin-top: 0.85rem;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      align-items: flex-start;
    }

    .request-access button,
    .request-access a {
      font-family: inherit;
      font-size: 0.7rem;
      border-radius: 999px;
      padding: 0.55rem 1.4rem;
      border: 1px solid rgba(255, 209, 92, 0.45);
      background: rgba(15, 15, 16, 0.75);
      color: #ffd15c;
      cursor: pointer;
      transition: all 0.25s ease;
      text-decoration: none;
    }

    .request-access button:hover,
    .request-access a:hover {
      border-color: rgba(255, 225, 120, 0.75);
      background: rgba(25, 25, 30, 0.85);
      color: #fff4cc;
      transform: translateY(-1px);
      box-shadow: 0 12px 28px -18px rgba(255, 209, 92, 0.6);
    }

    .request-access-note {
      font-size: 0.6rem;
      color: rgba(255, 255, 255, 0.5);
      text-align: left;
    }

    .request-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(4, 6, 12, 0.75);
      backdrop-filter: blur(10px);
      display: none;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      z-index: 5;
    }

    .request-modal-overlay.is-visible {
      display: flex;
    }

    .request-modal {
      width: min(420px, 100%);
      background: rgba(12, 12, 16, 0.92);
      border-radius: 16px;
      border: 1px solid rgba(212, 175, 55, 0.18);
      box-shadow: 0 24px 56px rgba(0, 0, 0, 0.65);
      padding: 1.5rem;
      color: #fff;
      position: relative;
    }

    .request-modal h2 {
      font-size: 1rem;
      margin-bottom: 0.75rem;
      letter-spacing: 0.04em;
    }

    .request-modal p {
      font-size: 0.7rem;
      color: rgba(255, 255, 255, 0.7);
      margin-bottom: 1rem;
      line-height: 1.5;
    }

    .request-modal form {
      display: grid;
      gap: 0.65rem;
    }

    .request-modal label {
      font-size: 0.62rem;
      color: rgba(255, 255, 255, 0.75);
      display: flex;
      flex-direction: column;
      gap: 0.35rem;
    }

    .request-modal input,
    .request-modal textarea {
      width: 100%;
      border-radius: 8px;
      border: 1px solid rgba(255, 255, 255, 0.12);
      background: rgba(20, 20, 24, 0.85);
      color: #fff;
      padding: 0.55rem 0.65rem;
      font-size: 0.7rem;
      font-family: inherit;
      resize: vertical;
      min-height: 38px;
    }

    .request-modal textarea {
      min-height: 96px;
    }

    .request-modal-actions {
      display: flex;
      gap: 0.5rem;
      justify-content: flex-end;
      margin-top: 0.4rem;
    }

    .request-modal-actions button {
      border-radius: 999px;
      padding: 0.5rem 1.4rem;
      font-size: 0.7rem;
      border: 1px solid rgba(255, 209, 92, 0.4);
      background: linear-gradient(135deg, rgba(255, 209, 92, 0.2), rgba(212, 175, 55, 0.35));
      color: #ffd15c;
      cursor: pointer;
      transition: all 0.25s ease;
    }

    .request-modal-actions button:hover {
      background: linear-gradient(135deg, rgba(255, 209, 92, 0.35), rgba(212, 175, 55, 0.5));
      color: #fff5d1;
    }

    .request-modal-close {
      position: absolute;
      top: 0.75rem;
      right: 0.75rem;
      background: none;
      border: none;
      color: rgba(255, 255, 255, 0.6);
      font-size: 1rem;
      cursor: pointer;
    }

    .request-modal-close:hover {
      color: #ffd15c;
    }

    .request-modal .status-message {
      margin-top: 0.5rem;
      font-size: 0.65rem;
      color: rgba(255, 255, 255, 0.65);
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

    /* Menu launcher trigger */
    .menu-launcher {
      position: fixed;
      top: 50%;
      right: 0;
      transform: translateY(-50%);
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.5rem;
      padding: 0.75rem 0.55rem;
      background: rgba(10, 10, 16, 0.85);
      border: 1px solid rgba(212, 175, 55, 0.2);
      border-right: none;
      border-radius: 12px 0 0 12px;
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      cursor: pointer;
      z-index: 3;
      transition: all 0.3s ease;
    }

    .menu-launcher:hover {
      background: rgba(15, 15, 22, 0.92);
      border-color: rgba(255, 209, 92, 0.4);
      padding-right: 0.75rem;
    }

    .menu-launcher__icon {
      width: 28px;
      height: 28px;
      display: grid;
      place-items: center;
      border-radius: 50%;
      background: rgba(255, 209, 92, 0.18);
      border: 1px solid rgba(255, 209, 92, 0.35);
      color: #ffe8a3;
      filter: drop-shadow(0 4px 12px rgba(255, 209, 92, 0.3));
      transition: all 0.25s ease;
    }

    .menu-launcher:hover .menu-launcher__icon {
      background: rgba(255, 209, 92, 0.28);
      transform: scale(1.08);
    }

    .menu-launcher__icon svg {
      width: 14px;
      height: 14px;
    }

    .menu-launcher__label {
      writing-mode: vertical-rl;
      text-orientation: mixed;
      font-size: 0.55rem;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: rgba(255, 255, 255, 0.6);
      transition: color 0.25s ease;
    }

    .menu-launcher:hover .menu-launcher__label {
      color: #ffe8a3;
    }

    /* Slide-in menu panel */
    .interface-menu {
      position: fixed;
      top: 0;
      right: 0;
      height: 100vh;
      width: 260px;
      max-width: 85vw;
      display: flex;
      flex-direction: column;
      gap: 1rem;
      padding: 1.5rem 1.25rem;
      background: rgba(8, 8, 14, 0.96);
      border-left: 1px solid rgba(212, 175, 55, 0.2);
      box-shadow: -12px 0 48px rgba(0, 0, 0, 0.65);
      backdrop-filter: blur(22px);
      -webkit-backdrop-filter: blur(22px);
      z-index: 4;
      transform: translateX(100%);
      transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
      overflow-y: auto;
    }

    .interface-menu.is-open {
      transform: translateX(0);
    }

    .interface-menu__close {
      position: absolute;
      top: 1rem;
      right: 1rem;
      width: 28px;
      height: 28px;
      display: grid;
      place-items: center;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: rgba(255, 255, 255, 0.6);
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .interface-menu__close:hover {
      background: rgba(255, 209, 92, 0.2);
      border-color: rgba(255, 209, 92, 0.4);
      color: #ffe8a3;
    }

    .interface-menu__close svg {
      width: 12px;
      height: 12px;
    }

    .interface-menu__brand {
      display: flex;
      gap: 0.6rem;
      align-items: center;
      padding-bottom: 0.65rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .interface-menu__brand img {
      width: 32px;
      height: 32px;
      object-fit: contain;
      filter: drop-shadow(0 6px 16px rgba(255, 209, 92, 0.3));
    }

    .interface-menu__brand-label {
      display: flex;
      flex-direction: column;
      line-height: 1.1;
    }

    .interface-menu__brand-label span {
      font-size: 0.58rem;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: rgba(255, 255, 255, 0.55);
    }

    .interface-menu__brand-label strong {
      font-size: 0.92rem;
      color: #ffe8a3;
      letter-spacing: 0.04em;
      text-shadow: 0 2px 8px rgba(255, 209, 92, 0.35);
    }

    .interface-menu__heading {
      font-size: 0.7rem;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: rgba(255, 255, 255, 0.58);
    }

    .interface-menu__list {
      display: flex;
      flex-direction: column;
      gap: 0.55rem;
    }

    .interface-menu__item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      width: 100%;
      padding: 0.6rem 0.85rem;
      border-radius: 12px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: linear-gradient(135deg, rgba(18, 18, 24, 0.75), rgba(12, 12, 18, 0.6));
      color: #ffe8a3;
      font-size: 0.72rem;
      letter-spacing: 0.02em;
      text-decoration: none;
      text-shadow: 0 1px 6px rgba(255, 209, 92, 0.25);
      transition: all 0.25s ease;
      cursor: pointer;
    }

    .interface-menu__item[aria-current="page"],
    .interface-menu__item.is-active {
      border-color: rgba(255, 209, 92, 0.5);
      background: linear-gradient(135deg, rgba(255, 209, 92, 0.15), rgba(212, 175, 55, 0.1));
      box-shadow: inset 0 0 0 1px rgba(255, 209, 92, 0.15);
    }

    .interface-menu__item:hover,
    .interface-menu__item:focus {
      border-color: rgba(255, 209, 92, 0.5);
      color: #fff;
      text-shadow: 0 2px 10px rgba(255, 232, 163, 0.4);
      transform: translateX(-3px);
      box-shadow: 0 14px 32px rgba(212, 175, 55, 0.3);
      outline: none;
    }

    .interface-menu__icon {
      flex: none;
      width: 1.5rem;
      height: 1.5rem;
      display: grid;
      place-items: center;
      border-radius: 50%;
      background: rgba(255, 209, 92, 0.2);
      border: 1px solid rgba(255, 209, 92, 0.35);
      color: #ffe8a3;
      filter: drop-shadow(0 2px 6px rgba(255, 209, 92, 0.3));
    }

    .interface-menu__icon svg {
      width: 0.8rem;
      height: 0.8rem;
    }

    .interface-menu__footer {
      margin-top: auto;
      padding: 1rem 0.5rem 0.5rem;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .interface-menu__status {
      font-size: 0.6rem;
      color: rgba(255, 232, 163, 0.7);
      letter-spacing: 0.04em;
      text-transform: uppercase;
    }

    .interface-menu__status-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #4ade80;
      box-shadow: 0 0 8px rgba(74, 222, 128, 0.6);
      animation: pulse-dot 2s ease-in-out infinite;
    }

    @keyframes pulse-dot {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }

    @media (max-width: 768px) {
      body {
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        gap: 2rem;
        padding: 3.5rem 1.25rem 2.5rem;
      }

      .menu-launcher {
        top: 1rem;
        right: 0;
        transform: none;
        padding: 0.5rem 0.4rem;
        border-radius: 8px 0 0 8px;
      }

      .menu-launcher__label {
        display: none;
      }

      .login-card {
        width: 92vw;
        max-width: 340px;
        padding: 1.5rem 1.35rem 1.35rem;
      }

      .logo-section {
        margin: 1.75rem auto 1rem;
        max-width: 200px;
      }

      .logo-img--primary {
        width: 170px;
      }

      .title {
        font-size: 0.88rem;
      }

      .subtitle {
        font-size: 0.58rem;
      }

      .form-input {
        font-size: 0.78rem;
        padding: 0.65rem 0.75rem;
        min-height: 44px;
      }

      .submit-btn {
        font-size: 0.72rem;
        padding: 0.7rem;
        min-height: 44px;
      }

      .request-access {
        max-width: 92vw;
        margin-left: 0;
        text-align: center;
      }

      .request-access-actions {
        align-items: center;
      }

      .request-access-note {
        text-align: center;
      }
    }

    @media (max-width: 420px) {
      body {
        padding: 3rem 1rem 2rem;
      }

      .login-card {
        width: 100%;
        padding: 1.35rem 1.1rem 1.25rem;
      }

      .logo-img--primary {
        width: 150px;
      }

      .title {
        font-size: 0.82rem;
      }

      .subtitle {
        font-size: 0.54rem;
      }

      .request-access {
        font-size: 0.62rem;
      }

      .request-access button,
      .request-access a {
        font-size: 0.64rem;
        padding: 0.55rem 1.25rem;
        min-height: 40px;
      }
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

  <!-- Menu Launcher Trigger -->
  <button type="button" class="menu-launcher" id="menuLauncher" aria-label="Åbn kommandopanel" aria-expanded="false" aria-controls="commandDeckMenu">
    <span class="menu-launcher__icon" aria-hidden="true">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="6" x2="21" y2="6"/>
        <line x1="3" y1="18" x2="21" y2="18"/>
      </svg>
    </span>
    <span class="menu-launcher__label">Menu</span>
  </button>

  <!-- Slide-in Command Deck Panel -->
  <nav class="interface-menu" id="commandDeckMenu" aria-label="GreyEYE kontrolpanel genveje" aria-hidden="true">
    <!-- Close button -->
    <button type="button" class="interface-menu__close" id="menuClose" aria-label="Luk menu">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"/>
        <line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
    </button>

    <div class="interface-menu__brand">
      <img src="assets/greyeeye_logo_transparent.png" alt="GreyEYE logo">
      <div class="interface-menu__brand-label">
        <span>GreyEYE</span>
        <strong>Command Deck</strong>
      </div>
    </div>
    <p class="interface-menu__heading">Navigator</p>
    <div class="interface-menu__list">
      <button type="button" class="interface-menu__item is-active" data-destination="overview" aria-current="page">
        <span class="interface-menu__icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 12l9-9 9 9" />
            <path d="M9 21V9h6v12" />
          </svg>
        </span>
        Oversigt
      </button>
      <button type="button" class="interface-menu__item" data-destination="operations">
        <span class="interface-menu__icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3" />
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V22a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H2a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H8a1.65 1.65 0 0 0 1-1.51V2a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V8a1.65 1.65 0 0 0 1.51 1H22a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
          </svg>
        </span>
        Operationer
      </button>
      <button type="button" class="interface-menu__item" data-destination="ide">
        <span class="interface-menu__icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 7h-9" />
            <path d="M14 17H5" />
            <circle cx="7" cy="7" r="2" />
            <circle cx="17" cy="17" r="2" />
          </svg>
        </span>
        IDE Workspace
      </button>
      <button type="button" class="interface-menu__item" data-destination="api">
        <span class="interface-menu__icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 9h16" />
            <path d="M4 15h16" />
            <path d="M10 3v18" />
          </svg>
        </span>
        API & Keys
      </button>
      <button type="button" class="interface-menu__item" data-destination="intel">
        <span class="interface-menu__icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
          </svg>
        </span>
        Intel Vault
      </button>
      <button type="button" class="interface-menu__item" data-destination="support">
        <span class="interface-menu__icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2h-3l-4 4v-4H7a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2z" />
            <path d="M12 11v2" />
            <path d="M12 7h.01" />
          </svg>
        </span>
        Support Desk
      </button>
    </div>
    <div class="interface-menu__footer">
      <span class="interface-menu__status-dot"></span>
      <span class="interface-menu__status">System online</span>
    </div>
  </nav>

  <main class="login-card">
    <div class="card-meta">
      <span class="powered-label">… Powered by</span>
      <img src="assets/Logo-blackbox-hvid.png" alt="Powered by BLACKBOX EYE™" class="powered-logo" loading="lazy">
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
  <section class="request-access" aria-labelledby="request-access-heading">
    <h2 id="request-access-heading" class="sr-only">Anmodning om adgang</h2>
    <p>Har du brug for operatør-login? <strong>Anmod om adgang</strong> og modtag en tidsbegrænset sikker invitation.</p>
    <div class="request-access-actions">
      <button type="button" id="request-access-init" aria-haspopup="dialog" aria-controls="request-access-dialog">Anmod om adgang</button>
      <a href="mailto:ops@blackbox.codes?subject=GreyEYE%20Access%20Request" class="request-access-note">Eller kontakt GreyEYE sikkerhedsdesk direkte</a>
    </div>
    <p class="request-access-note">Alle forespørgsler verificeres manuelt. Autoriserede brugere modtager et krypteret link og multi-faktor onboarding.</p>
  </section>

  <div class="request-modal-overlay" id="request-access-overlay" role="presentation">
    <div class="request-modal" role="dialog" id="request-access-dialog" aria-modal="true" aria-labelledby="request-modal-title">
      <button type="button" class="request-modal-close" id="request-access-close" aria-label="Luk dialog">&times;</button>
      <h2 id="request-modal-title">Anmod om sikker adgang</h2>
      <p>Indsend kontaktoplysninger og operationelt scope. Vores sikkerhedsteam udsteder et unikt onboarding-link via krypteret e-mail (PGP/GPG) inden for 24 timer.</p>
      <form id="request-access-form">
        <label>
          Sikker e-mail (PGP eller virksomhed)
          <input type="email" name="secureEmail" id="request-email" placeholder="navn@domæne.com" required autocomplete="email">
        </label>
        <label>
          Organisation / Titel
          <input type="text" name="org" id="request-org" placeholder="Virksomhed, rolle" required>
        </label>
        <label>
          Operationelt scope & begrundelse
          <textarea name="scope" id="request-scope" placeholder="Kort beskrivelse af hvorfor adgang er nødvendig" required></textarea>
        </label>
        <div class="request-modal-actions">
          <button type="button" id="request-access-cancel">Annuller</button>
          <button type="submit" id="request-access-submit">Send anmodning</button>
        </div>
        <p class="status-message" id="request-access-status" role="status" aria-live="polite"></p>
      </form>
    </div>
  </div>
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

      const requestInit = document.getElementById('request-access-init');
      const overlay = document.getElementById('request-access-overlay');
      const dialog = document.getElementById('request-access-dialog');
      const closeBtns = [
        document.getElementById('request-access-close'),
        document.getElementById('request-access-cancel')
      ].filter(Boolean);
      const form = document.getElementById('request-access-form');
      const status = document.getElementById('request-access-status');
      const emailField = document.getElementById('request-email');

      const openModal = () => {
        if (!overlay) {
          return;
        }
        overlay.classList.add('is-visible');
        body.classList.add('interaction-active');
        requestAnimationFrame(() => {
          emailField?.focus();
        });
      };

      const closeModal = () => {
        if (!overlay) {
          return;
        }
        overlay.classList.remove('is-visible');
        status.textContent = '';
        requestInit?.focus();
        body.classList.remove('interaction-active');
      };

      requestInit?.addEventListener('click', openModal);

      closeBtns.forEach((btn) => {
        btn.addEventListener('click', closeModal);
      });

      overlay?.addEventListener('click', (event) => {
        if (event.target === overlay) {
          closeModal();
        }
      });

      form?.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!form.reportValidity()) {
          return;
        }
        status.textContent = 'Sender krypteret anmodning...';

        // Fremtidig integration: POST til sikker API, generér one-time invite token og send PGP-sikret mail.
        // fetch('/api/access-invite.php', { method: 'POST', body: new FormData(form) }) ...

        setTimeout(() => {
          status.textContent = 'Tak. GreyEYE-teamet verificerer og udsender en sikker onboarding-mail (typisk < 24 timer).';
          form.reset();
        }, 900);
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && overlay?.classList.contains('is-visible')) {
          closeModal();
        }
      });

      // =========================================
      // Command Deck Slide-In Menu
      // =========================================
      const menuLauncher = document.getElementById('menuLauncher');
      const menuPanel = document.getElementById('commandDeckMenu');
      const menuClose = document.getElementById('menuClose');

      const openMenu = () => {
        if (!menuPanel) return;
        menuPanel.classList.add('is-open');
        menuPanel.setAttribute('aria-hidden', 'false');
        menuLauncher?.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => {
          menuClose?.focus();
        });
      };

      const closeMenu = () => {
        if (!menuPanel) return;
        menuPanel.classList.remove('is-open');
        menuPanel.setAttribute('aria-hidden', 'true');
        menuLauncher?.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        menuLauncher?.focus();
      };

      menuLauncher?.addEventListener('click', openMenu);
      menuClose?.addEventListener('click', closeMenu);

      // Click outside menu to close
      document.addEventListener('click', (event) => {
        if (menuPanel?.classList.contains('is-open') &&
            !menuPanel.contains(event.target) &&
            !menuLauncher?.contains(event.target)) {
          closeMenu();
        }
      });

      // ESC key to close menu
      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && menuPanel?.classList.contains('is-open')) {
          closeMenu();
        }
      });

      // Trap focus inside menu when open
      menuPanel?.addEventListener('keydown', (event) => {
        if (event.key === 'Tab') {
          const focusableElements = menuPanel.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
          const firstElement = focusableElements[0];
          const lastElement = focusableElements[focusableElements.length - 1];

          if (event.shiftKey && document.activeElement === firstElement) {
            event.preventDefault();
            lastElement?.focus();
          } else if (!event.shiftKey && document.activeElement === lastElement) {
            event.preventDefault();
            firstElement?.focus();
          }
        }
      });
    })();
  </script>
</body>

</html>
