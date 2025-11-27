<?php
/**
 * Login Card Component
 * 
 * Genbrugelig login-formular komponent til agent-login
 * Responsiv: 340px på desktop, 92vw på mobil med 20px margen
 * 
 * Variabler der kan sættes før include:
 * - $error: Fejlbesked der skal vises
 * - $login_action: Form action URL (standard: agent-login.php)
 */

$error = $error ?? null;
$login_action = $login_action ?? 'agent-login.php';
?>
<div class="login-card">
    <!-- Powered by badge -->
    <div class="login-card__meta">
        <span class="login-card__powered-label">… Powered by</span>
        <img src="/assets/Logo-blackbox-hvid.png" 
             alt="Powered by BLACKBOX EYE™" 
             class="login-card__powered-logo" 
             loading="lazy">
    </div>

    <!-- Logo Section -->
    <div class="login-card__logo-section">
        <img src="/assets/greyeeye_logo_transparent.png" 
             alt="GreyEYE Data Intelligence" 
             class="login-card__logo" 
             loading="lazy">
        <h1 class="login-card__title">Sikker adgang</h1>
        <p class="login-card__subtitle">GreyEYE Data Intelligence (GDI) operatør-portal</p>
    </div>

    <!-- Error Message -->
    <?php if ($error): ?>
    <div class="login-card__error" role="alert">
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form action="<?= htmlspecialchars($login_action) ?>" method="post" autocomplete="off" class="login-card__form">
        <div class="login-card__field">
            <label for="agent_id" class="sr-only">Brugernavn</label>
            <input type="text" 
                   name="agent_id" 
                   id="agent_id" 
                   placeholder="Brugernavn" 
                   required 
                   class="login-card__input" 
                   autocomplete="off">
        </div>
        
        <div class="login-card__field">
            <label for="password" class="sr-only">Adgangskode</label>
            <input type="password" 
                   name="password" 
                   id="password" 
                   placeholder="Adgangskode" 
                   required 
                   class="login-card__input" 
                   autocomplete="new-password">
        </div>
        
        <div class="login-card__field">
            <label for="pin" class="sr-only">PIN-kode</label>
            <input type="password" 
                   name="pin" 
                   id="pin" 
                   placeholder="PIN-kode" 
                   required 
                   class="login-card__input" 
                   inputmode="numeric">
        </div>
        
        <div class="login-card__field">
            <label for="token" class="sr-only">Token</label>
            <input type="text" 
                   name="token" 
                   id="token" 
                   placeholder="Token (valgfri)" 
                   class="login-card__input">
        </div>
        
        <button type="submit" class="login-card__submit">Log ind</button>
    </form>

    <!-- Footer -->
    <div class="login-card__footer">
        <p>Adgang kræver autoriseret hardware-nøgle.<br>Alle forsøg logges.</p>
    </div>
</div>
