<?php
/**
 * Modular Login Card Component
 * 
 * Reusable login card component with color-coding per console.
 * Supports CCS, GDI, Intel24 and custom themes.
 * 
 * Usage:
 * <?php
 * $login_config = [
 *     'console' => 'ccs',           // ccs | gdi | intel24
 *     'title' => 'Sign in to CCS',
 *     'form_action' => 'ccs-login.php',
 *     'show_mfa' => true,
 *     'show_sso' => true,
 *     'sso_enabled' => false,
 *     'show_password_strength' => true,
 * ];
 * include __DIR__ . '/includes/login-card-modular.php';
 * ?>
 */

// Default configuration
$login_config = $login_config ?? [];
$console = $login_config['console'] ?? 'default';
$title = $login_config['title'] ?? 'Sign in';
$form_action = $login_config['form_action'] ?? '';
$show_mfa = $login_config['show_mfa'] ?? true;
$show_sso = $login_config['show_sso'] ?? true;
$sso_enabled = $login_config['sso_enabled'] ?? false;
$show_password_strength = $login_config['show_password_strength'] ?? true;
$show_remember_me = $login_config['show_remember_me'] ?? false;
$error_message = $login_config['error'] ?? null;
$success_message = $login_config['success'] ?? null;

// Console color themes
$themes = [
    'ccs' => [
        'primary' => '#2ecc71',
        'primary_rgb' => '46, 204, 113',
        'accent' => '#7cf0ba',
        'badge_text' => 'CCS',
        'badge_label' => 'Settlement',
    ],
    'gdi' => [
        'primary' => '#289bde',
        'primary_rgb' => '40, 155, 222',
        'accent' => '#59c0ff',
        'badge_text' => 'GDI',
        'badge_label' => 'Intelligence',
    ],
    'intel24' => [
        'primary' => '#c9a227',
        'primary_rgb' => '201, 162, 39',
        'accent' => '#f5d86a',
        'badge_text' => 'I24',
        'badge_label' => 'Intel',
    ],
    'default' => [
        'primary' => '#6b7280',
        'primary_rgb' => '107, 114, 128',
        'accent' => '#9ca3af',
        'badge_text' => 'BBX',
        'badge_label' => 'Access',
    ],
];

$theme = $themes[$console] ?? $themes['default'];
$card_id = 'login-card-' . $console;
?>

<section class="bbx-login-card bbx-login-card--<?= htmlspecialchars($console) ?>" 
         id="<?= $card_id ?>"
         style="--login-primary: <?= $theme['primary'] ?>; --login-primary-rgb: <?= $theme['primary_rgb'] ?>; --login-accent: <?= $theme['accent'] ?>;">
    
    <div class="bbx-login-card__inner">
        <!-- Header with badge -->
        <div class="bbx-login-card__header">
            <div class="bbx-login-card__badge">
                <span class="bbx-login-card__badge-text"><?= htmlspecialchars($theme['badge_text']) ?></span>
            </div>
            <h2 class="bbx-login-card__title"><?= htmlspecialchars($title) ?></h2>
        </div>
        
        <!-- Status Messages -->
        <?php if ($error_message): ?>
        <div class="bbx-login-card__alert bbx-login-card__alert--error" role="alert">
            <svg class="bbx-login-card__alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span><?= htmlspecialchars($error_message) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
        <div class="bbx-login-card__alert bbx-login-card__alert--success" role="status">
            <svg class="bbx-login-card__alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="9 12 12 15 16 10"/>
            </svg>
            <span><?= htmlspecialchars($success_message) ?></span>
        </div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form action="<?= htmlspecialchars($form_action) ?>" method="post" class="bbx-login-card__form" autocomplete="off">
            <div class="bbx-login-card__field">
                <label for="<?= $card_id ?>-email" class="bbx-login-card__label">Email or Username</label>
                <input type="text" 
                       id="<?= $card_id ?>-email" 
                       name="email" 
                       class="bbx-login-card__input"
                       placeholder="operator@institution.com"
                       required
                       autocomplete="username">
            </div>

            <div class="bbx-login-card__field">
                <label for="<?= $card_id ?>-password" class="bbx-login-card__label">Password</label>
                <div class="bbx-login-card__password-wrapper">
                    <input type="password" 
                           id="<?= $card_id ?>-password" 
                           name="password" 
                           class="bbx-login-card__input bbx-login-card__input--password"
                           placeholder="••••••••••••"
                           required
                           autocomplete="current-password">
                    <button type="button" 
                            class="bbx-login-card__password-toggle" 
                            aria-label="Toggle password visibility"
                            data-toggle-password="<?= $card_id ?>-password">
                        <svg class="bbx-login-card__eye-icon bbx-login-card__eye-icon--show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg class="bbx-login-card__eye-icon bbx-login-card__eye-icon--hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
                
                <?php if ($show_password_strength): ?>
                <div class="bbx-login-card__strength" aria-live="polite">
                    <div class="bbx-login-card__strength-bar">
                        <div class="bbx-login-card__strength-fill" data-strength="0"></div>
                    </div>
                    <span class="bbx-login-card__strength-text">Password strength</span>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($show_remember_me): ?>
            <div class="bbx-login-card__checkbox-field">
                <input type="checkbox" id="<?= $card_id ?>-remember" name="remember" class="bbx-login-card__checkbox">
                <label for="<?= $card_id ?>-remember" class="bbx-login-card__checkbox-label">Remember me</label>
            </div>
            <?php endif; ?>

            <button type="submit" class="bbx-login-card__submit">
                Log in
            </button>
        </form>

        <!-- Secondary Links -->
        <div class="bbx-login-card__links">
            <a href="contact.php?subject=<?= $console ?>-password" class="bbx-login-card__link">Forgot password?</a>
            <span class="bbx-login-card__link-divider" aria-hidden="true">•</span>
            <a href="contact.php?subject=<?= $console ?>-support" class="bbx-login-card__link">Need help?</a>
        </div>

        <?php if ($show_mfa): ?>
        <!-- MFA Step Indicator (Sprint 2 enhanced) -->
        <div class="bbx-login-card__mfa-step" role="status" aria-live="polite" data-mfa-step>
            <div class="bbx-login-card__mfa-header">
                <span class="bbx-login-card__mfa-badge">Step 2 of 2</span>
                <span class="bbx-login-card__mfa-title">Multi-factor authentication</span>
            </div>
            <div class="bbx-login-card__mfa-icon">
                <svg class="bbx-icon bbx-icon--lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            <p class="bbx-login-card__mfa-privacy">
                Your second factor is verified in real time and never stored.
            </p>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($show_sso): ?>
    <!-- SSO Section -->
    <div class="bbx-login-card__sso">
        <div class="bbx-login-card__sso-divider">
            <span>Or continue with</span>
        </div>
        
        <div class="bbx-login-card__sso-buttons">
            <button type="button" 
                    class="bbx-login-card__sso-btn bbx-login-card__sso-btn--azure <?= !$sso_enabled ? 'is-disabled' : '' ?>" 
                    <?= !$sso_enabled ? '' : '' ?>
                    data-tooltip="<?= $sso_enabled ? 'Sign in with Azure AD' : 'Available for approved enterprise tenants. Request access.' ?>"
                    data-tooltip-pos="top"
                    data-sso-provider="azure"
                    aria-describedby="sso-status-hint">
                <?php if (!$sso_enabled): ?>
                <svg class="bbx-login-card__sso-lock bbx-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                </svg>
                <?php endif; ?>
                <svg class="bbx-login-card__sso-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M11.4 24H0l9.1-15.8L5.7 0h5.7L24 24h-5.7l-2.5-4.3L11.4 24z"/>
                </svg>
                <span>Azure AD</span>
            </button>
            <button type="button" 
                    class="bbx-login-card__sso-btn bbx-login-card__sso-btn--google <?= !$sso_enabled ? 'is-disabled' : '' ?>" 
                    <?= !$sso_enabled ? '' : '' ?>
                    data-tooltip="<?= $sso_enabled ? 'Sign in with Google Workspace' : 'Available for approved enterprise tenants. Request access.' ?>"
                    data-tooltip-pos="top"
                    data-sso-provider="google"
                    aria-describedby="sso-status-hint">
                <?php if (!$sso_enabled): ?>
                <svg class="bbx-login-card__sso-lock bbx-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                </svg>
                <?php endif; ?>
                <svg class="bbx-login-card__sso-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                <span>Google Workspace</span>
            </button>
        </div>
        
        <p id="sso-status-hint" class="bbx-login-card__sso-note">
            <?php if ($sso_enabled): ?>
            SSO is enabled for your account.
            <?php else: ?>
            <a href="contact.php?subject=sso-request&console=<?= htmlspecialchars($console) ?>" class="bbx-login-card__sso-request-link" data-sso-request>Request SSO access</a> for your enterprise.
            <?php endif; ?>
        </p>
    </div>
    <?php endif; ?>
</section>

<style>
/* ================================================
   MODULAR LOGIN CARD STYLES
   ================================================ */

.bbx-login-card {
    --login-primary: #6b7280;
    --login-primary-rgb: 107, 114, 128;
    --login-accent: #9ca3af;
    
    background: rgba(15, 20, 28, 0.9);
    border: 1px solid rgba(var(--login-primary-rgb), 0.15);
    border-radius: 1.25rem;
    overflow: hidden;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    box-shadow: 
        0 25px 50px -12px rgba(0, 0, 0, 0.5),
        0 0 0 1px rgba(var(--login-primary-rgb), 0.05) inset;
}

.bbx-login-card__inner {
    padding: 1.75rem 1.5rem;
}

@media (min-width: 640px) {
    .bbx-login-card__inner {
        padding: 2rem;
    }
}

/* Header */
.bbx-login-card__header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.bbx-login-card__badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.375rem 0.875rem;
    background: rgba(var(--login-primary-rgb), 0.12);
    border: 1px solid rgba(var(--login-primary-rgb), 0.3);
    border-radius: 0.5rem;
    margin-bottom: 0.75rem;
}

.bbx-login-card__badge-text {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: var(--login-accent);
}

.bbx-login-card__title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #fff;
    margin: 0;
}

/* Alerts */
.bbx-login-card__alert {
    display: flex;
    align-items: flex-start;
    gap: 0.625rem;
    padding: 0.875rem 1rem;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    margin-bottom: 1.25rem;
}

.bbx-login-card__alert--error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #fca5a5;
}

.bbx-login-card__alert--success {
    background: rgba(46, 204, 113, 0.1);
    border: 1px solid rgba(46, 204, 113, 0.3);
    color: #7cf0ba;
}

.bbx-login-card__alert-icon {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
    margin-top: 1px;
}

/* Form */
.bbx-login-card__form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.bbx-login-card__field {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.bbx-login-card__label {
    font-size: 0.8rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.8);
}

.bbx-login-card__input {
    width: 100%;
    min-height: 48px;
    padding: 0.875rem 1rem;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 0.625rem;
    color: #fff;
    font-size: 0.95rem;
    font-family: inherit;
    transition: border-color 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
    box-sizing: border-box;
}

.bbx-login-card__input::placeholder {
    color: rgba(255, 255, 255, 0.35);
}

.bbx-login-card__input:hover {
    border-color: rgba(var(--login-primary-rgb), 0.3);
}

.bbx-login-card__input:focus {
    outline: none;
    border-color: rgba(var(--login-primary-rgb), 0.6);
    background: rgba(var(--login-primary-rgb), 0.05);
    box-shadow: 0 0 0 3px rgba(var(--login-primary-rgb), 0.1);
}

/* Password field */
.bbx-login-card__password-wrapper {
    position: relative;
}

.bbx-login-card__input--password {
    padding-right: 3rem;
}

.bbx-login-card__password-toggle {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    padding: 0.375rem;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.5);
    transition: color 0.2s ease;
    min-width: 44px;
    min-height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bbx-login-card__password-toggle:hover {
    color: rgba(255, 255, 255, 0.8);
}

.bbx-login-card__eye-icon {
    width: 20px;
    height: 20px;
}

/* Password strength */
.bbx-login-card__strength {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-top: 0.375rem;
}

.bbx-login-card__strength-bar {
    flex: 1;
    height: 4px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
    overflow: hidden;
}

.bbx-login-card__strength-fill {
    height: 100%;
    width: 0;
    border-radius: 2px;
    transition: width 0.3s ease, background-color 0.3s ease;
}

.bbx-login-card__strength-fill[data-strength="1"] { width: 33%; background: #ef4444; }
.bbx-login-card__strength-fill[data-strength="2"] { width: 66%; background: var(--color-primary, #c9a227); }
.bbx-login-card__strength-fill[data-strength="3"] { width: 100%; background: var(--login-primary); }

.bbx-login-card__strength-text {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.5);
    white-space: nowrap;
}

/* Submit button */
.bbx-login-card__submit {
    width: 100%;
    min-height: 48px;
    padding: 0.875rem 1.5rem;
    background: linear-gradient(135deg, var(--login-primary), color-mix(in srgb, var(--login-primary), black 15%));
    border: none;
    border-radius: 0.625rem;
    color: #0a0e14;
    font-size: 0.95rem;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.2s ease, filter 0.2s ease;
    margin-top: 0.5rem;
}

.bbx-login-card__submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(var(--login-primary-rgb), 0.3);
    filter: brightness(1.05);
}

.bbx-login-card__submit:active {
    transform: translateY(0);
}

/* Links */
.bbx-login-card__links {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.75rem;
    margin-top: 1.25rem;
    font-size: 0.8rem;
}

.bbx-login-card__link {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: color 0.2s ease;
}

.bbx-login-card__link:hover {
    color: var(--login-accent);
}

.bbx-login-card__link-divider {
    color: rgba(255, 255, 255, 0.2);
}

/* MFA Step Indicator (Sprint 2 enhanced) */
.bbx-login-card__mfa-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    margin-top: 1.5rem;
    padding: 1.25rem;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 0 0 1rem 1rem;
    background: rgba(var(--login-primary-rgb), 0.04);
}

.bbx-login-card__mfa-header {
    display: flex;
    align-items: center;
    gap: 0.625rem;
}

.bbx-login-card__mfa-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    background: rgba(var(--login-primary-rgb), 0.15);
    border: 1px solid rgba(var(--login-primary-rgb), 0.3);
    border-radius: 0.375rem;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    color: var(--login-accent);
    text-transform: uppercase;
}

.bbx-login-card__mfa-title {
    font-size: 0.8rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.85);
}

.bbx-login-card__mfa-step .bbx-login-card__mfa-icon {
    width: 32px;
    height: 32px;
    color: var(--login-accent);
    opacity: 0.7;
}

.bbx-login-card__mfa-privacy {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.5);
    text-align: center;
    margin: 0;
    max-width: 240px;
    line-height: 1.4;
}

/* SSO Section */
.bbx-login-card__sso {
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.02);
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.bbx-login-card__sso-divider {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.bbx-login-card__sso-divider::before,
.bbx-login-card__sso-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(255, 255, 255, 0.1);
}

.bbx-login-card__sso-divider span {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.4);
    white-space: nowrap;
}

.bbx-login-card__sso-buttons {
    display: flex;
    gap: 0.75rem;
}

.bbx-login-card__sso-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    min-height: 44px;
    padding: 0.75rem 1rem;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 0.5rem;
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.8rem;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.2s ease;
}

.bbx-login-card__sso-btn.is-disabled,
.bbx-login-card__sso-btn:disabled,
.bbx-login-card__sso-btn[aria-disabled="true"] {
    opacity: 0.8;
    cursor: pointer;
    border-style: dashed;
    background: rgba(255, 255, 255, 0.03);
    color: rgba(255, 255, 255, 0.55);
}

.bbx-login-card__sso-btn.is-disabled:hover {
    background: rgba(255, 255, 255, 0.06);
    border-color: rgba(255, 255, 255, 0.15);
}

.bbx-login-card__sso-btn:not(.is-disabled):hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(var(--login-primary-rgb), 0.3);
}

.bbx-login-card__sso-lock {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 14px;
    height: 14px;
    color: rgba(255, 255, 255, 0.5);
    flex-shrink: 0;
}

.bbx-login-card__sso-icon {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.bbx-login-card__sso-note {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.45);
    text-align: center;
    margin: 0.875rem 0 0 0;
}

.bbx-login-card__sso-request-link {
    color: var(--login-accent);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease, text-decoration 0.2s ease;
}

.bbx-login-card__sso-request-link:hover {
    text-decoration: underline;
}

.bbx-login-card__sso-request-link:focus-visible {
    outline: 2px solid var(--login-accent);
    outline-offset: 2px;
    border-radius: 2px;
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .bbx-login-card,
    .bbx-login-card__submit,
    .bbx-login-card__sso-btn,
    .bbx-login-card__input,
    .bbx-login-card__link,
    .bbx-login-card__strength-fill {
        transition: none;
    }
    
    .bbx-login-card__submit:hover {
        transform: none;
    }
}
</style>

<script>
(function() {
    'use strict';
    
    // Password toggle
    document.querySelectorAll('[data-toggle-password]').forEach(btn => {
        btn.addEventListener('click', function() {
            const inputId = this.getAttribute('data-toggle-password');
            const input = document.getElementById(inputId);
            if (!input) return;
            
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            
            const showIcon = this.querySelector('.bbx-login-card__eye-icon--show');
            const hideIcon = this.querySelector('.bbx-login-card__eye-icon--hide');
            if (showIcon) showIcon.style.display = isPassword ? 'none' : 'block';
            if (hideIcon) hideIcon.style.display = isPassword ? 'block' : 'none';
            
            this.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        });
    });
    
    // Password strength
    document.querySelectorAll('.bbx-login-card__input--password').forEach(input => {
        const card = input.closest('.bbx-login-card');
        const strengthFill = card?.querySelector('.bbx-login-card__strength-fill');
        const strengthText = card?.querySelector('.bbx-login-card__strength-text');
        
        if (!strengthFill || !strengthText) return;
        
        input.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            let label = 'Password strength';
            
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            if (strength <= 1) {
                strength = password.length > 0 ? 1 : 0;
                label = password.length > 0 ? 'Weak' : 'Password strength';
            } else if (strength <= 3) {
                strength = 2;
                label = 'Moderate';
            } else {
                strength = 3;
                label = 'Strong';
            }
            
            strengthFill.setAttribute('data-strength', strength);
            strengthText.textContent = label;
        });
    });

    // SSO click handling with snackbar feedback and modal trigger
    document.querySelectorAll('.bbx-login-card__sso-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const isDisabled = this.classList.contains('is-disabled');
            const provider = this.getAttribute('data-sso-provider') || 'SSO';

            if (window.bbxSnackbar) {
                if (isDisabled) {
                    window.bbxSnackbar.info('SSO requires enterprise approval. Use the link below to request access.');
                } else {
                    window.bbxSnackbar.success('Redirecting to ' + provider.toUpperCase() + ' sign-in...');
                }
            }
        });
    });

    // SSO request link handler
    document.querySelectorAll('[data-sso-request]').forEach(link => {
        link.addEventListener('click', function(e) {
            if (window.bbxSnackbar) {
                window.bbxSnackbar.info('Opening SSO access request form...');
            }
        });
    });
})();
</script>
