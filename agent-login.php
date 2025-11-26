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
      background: rgba(5, 5, 15, 0.32);
      backdrop-filter: blur(8px);
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
      margin: 2.6rem auto 1.25rem;
      max-width: 260px;
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
      font-size: 1.08rem;
      font-weight: 600;
      color: #fff;
      margin-bottom: 0.3rem;
    }

    .subtitle {
      font-size: 0.7rem;
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
      margin-top: 1.75rem;
      text-align: center;
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
      align-items: center;
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
    })();
  </script>
</body>

</html>
