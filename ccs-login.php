<?php
/**
 * CCS Login Portal
 * 
 * Enterprise-grade login portal for Cross-Currency Settlement platform.
 * Communicates trust, security, and institutional seriousness.
 * 
 * Sprint 1: UX, structure, and security posture (no backend auth yet)
 */

session_start();
require_once __DIR__ . '/includes/env.php';
require_once __DIR__ . '/includes/i18n.php';

$current_page = 'ccs-login';
$page_title = 'CCS Login — Cross-Currency Settlement';
$meta_description = 'Secure access to Cross-Currency Settlement infrastructure. Institutional-grade multi-asset settlement for fiat and crypto flows.';

// Mock error handling (no real auth in Sprint 1)
$error = $_SESSION['ccs_login_error'] ?? null;
unset($_SESSION['ccs_login_error']);

// Mock form submission (placeholder for Sprint 2)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sprint 2 will implement real auth + MFA
    $_SESSION['ccs_login_error'] = 'Authentication system coming in Sprint 2. This is a preview.';
    header('Location: ccs-login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
    <title><?= htmlspecialchars($page_title) ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" crossorigin="anonymous">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_black_32x32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_black_256x256.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_black_256x256.png">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/marketing.min.css">
    <link rel="stylesheet" href="/assets/css/ccs-login.css">
    <?php include __DIR__ . '/includes/qa-bootstrap.php'; ?>
</head>

<body class="ccs-login-body">
    <!-- Skip Link -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <!-- Hexagon Pattern Background -->
    <div class="ccs-login__pattern" aria-hidden="true">
        <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="ccs-hexagon-pattern" x="0" y="0" width="56" height="100" patternUnits="userSpaceOnUse" patternTransform="scale(1.5)">
                    <path d="M28 66L0 50L0 16L28 0L56 16L56 50L28 66L28 100" fill="none" stroke="rgba(46,204,113,0.06)" stroke-width="1"/>
                    <path d="M28 0L56 16L56 50L28 66L0 50L0 16L28 0" fill="none" stroke="rgba(46,204,113,0.03)" stroke-width="0.5"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#ccs-hexagon-pattern)"/>
        </svg>
    </div>

    <!-- Header -->
    <header class="ccs-login__header">
        <a href="/" class="ccs-login__logo-link" aria-label="Blackbox EYE Home">
            <img src="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_white_128x128.png" 
                 alt="Blackbox EYE" 
                 class="ccs-login__logo"
                 width="40" height="40">
            <span class="ccs-login__logo-text">BLACKBOX EYE™</span>
        </a>
        <div class="ccs-login__header-badge">
            <span class="ccs-login__badge ccs-login__badge--ccs">CCS</span>
            <span class="ccs-login__badge-label">Settlement Platform</span>
        </div>
    </header>

    <!-- Main Content -->
    <main id="main-content" class="ccs-login__main">
        <div class="ccs-login__container">
            
            <!-- Hero / Value Proposition -->
            <section class="ccs-login__hero" aria-labelledby="ccs-hero-title">
                <h1 id="ccs-hero-title" class="ccs-login__headline">
                    Welcome to Cross-Currency Settlement — Fiat &amp; Crypto Unified
                </h1>
                <p class="ccs-login__subtext">
                    Institutional-grade settlement infrastructure for multi-asset flows.
                </p>
                
                <!-- Value Props -->
                <ul class="ccs-login__features" role="list">
                    <li class="ccs-login__feature">
                        <svg class="ccs-login__feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M3 9h18M9 21V9"/>
                        </svg>
                        <span>Multi-asset ledger</span>
                    </li>
                    <li class="ccs-login__feature">
                        <svg class="ccs-login__feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <span>Near-instant settlement</span>
                    </li>
                    <li class="ccs-login__feature">
                        <svg class="ccs-login__feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <path d="M9 12l2 2 4-4"/>
                        </svg>
                        <span>Audit-grade security</span>
                    </li>
                </ul>
            </section>

            <!-- Login Card -->
            <section class="ccs-login__card" aria-labelledby="ccs-login-title">
                <div class="ccs-login__card-inner">
                    <h2 id="ccs-login-title" class="ccs-login__card-title">Sign in to CCS</h2>
                    
                    <!-- Error Message -->
                    <?php if ($error): ?>
                    <div class="ccs-login__error" role="alert">
                        <svg class="ccs-login__error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form action="ccs-login.php" method="post" class="ccs-login__form" autocomplete="off" data-testid="ccs-login-form">
                        <div class="ccs-login__field">
                            <label for="ccs-email" class="ccs-login__label">Email or Username</label>
                            <input type="text" 
                                   id="ccs-email" 
                                   name="email" 
                                   class="ccs-login__input"
                                   placeholder="operator@institution.com"
                                   required
                                   autocomplete="username"
                                   data-testid="ccs-email-input">
                        </div>

                        <div class="ccs-login__field">
                            <label for="ccs-password" class="ccs-login__label">Password</label>
                            <div class="ccs-login__password-wrapper">
                                <input type="password" 
                                       id="ccs-password" 
                                       name="password" 
                                       class="ccs-login__input ccs-login__input--password"
                                       placeholder="••••••••••••"
                                       required
                                       autocomplete="current-password"
                                       data-testid="ccs-password-input">
                                <button type="button" 
                                        class="ccs-login__password-toggle" 
                                        aria-label="Toggle password visibility"
                                        data-testid="password-toggle">
                                    <svg class="ccs-login__eye-icon ccs-login__eye-icon--show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <svg class="ccs-login__eye-icon ccs-login__eye-icon--hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                        <line x1="1" y1="1" x2="23" y2="23"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- Password Strength Indicator -->
                            <div class="ccs-login__strength" aria-live="polite" data-testid="password-strength">
                                <div class="ccs-login__strength-bar">
                                    <div class="ccs-login__strength-fill" data-strength="0"></div>
                                </div>
                                <span class="ccs-login__strength-text">Password strength</span>
                            </div>
                        </div>

                        <button type="submit" class="ccs-login__submit" data-testid="ccs-submit-btn">
                            Log in
                        </button>
                    </form>

                    <!-- Secondary Links -->
                    <div class="ccs-login__links">
                        <a href="contact.php?subject=ccs-password" class="ccs-login__link">Forgot password?</a>
                        <span class="ccs-login__link-divider" aria-hidden="true">•</span>
                        <a href="contact.php?subject=ccs-support" class="ccs-login__link">Need help?</a>
                    </div>

                    <!-- MFA Placeholder (Sprint 2) -->
                    <div class="ccs-login__mfa-placeholder" aria-hidden="true" data-testid="mfa-placeholder">
                        <div class="ccs-login__mfa-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <span class="ccs-login__mfa-text">Multi-factor authentication required</span>
                    </div>
                </div>

                <!-- SSO Section -->
                <div class="ccs-login__sso" data-testid="sso-section">
                    <div class="ccs-login__sso-divider">
                        <span>Or continue with</span>
                    </div>
                    
                    <div class="ccs-login__sso-buttons">
                        <button type="button" class="ccs-login__sso-btn" disabled aria-disabled="true" data-testid="sso-azure">
                            <svg class="ccs-login__sso-icon" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M11.4 24H0l9.1-15.8L5.7 0h5.7L24 24h-5.7l-2.5-4.3L11.4 24z"/>
                            </svg>
                            <span>Azure AD</span>
                        </button>
                        <button type="button" class="ccs-login__sso-btn" disabled aria-disabled="true" data-testid="sso-google">
                            <svg class="ccs-login__sso-icon" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            <span>Google Workspace</span>
                        </button>
                    </div>
                    
                    <p class="ccs-login__sso-note">SSO available for approved enterprise accounts.</p>
                </div>
            </section>

            <!-- Request Access CTA -->
            <section class="ccs-login__request" aria-labelledby="ccs-request-title" data-testid="request-access-section">
                <h3 id="ccs-request-title" class="ccs-login__request-title">New to CCS?</h3>
                <p class="ccs-login__request-text">
                    CCS access is available to qualified financial institutions and approved operators.
                </p>
                <a href="contact.php?subject=ccs-access-request" class="ccs-login__request-btn" data-testid="request-access-btn">
                    Request CCS Access
                </a>
            </section>
        </div>
    </main>

    <!-- Trust & Compliance Footer -->
    <footer class="ccs-login__footer" data-testid="trust-footer">
        <div class="ccs-login__footer-inner">
            
            <!-- Certifications -->
            <div class="ccs-login__certifications" data-testid="certifications">
                <div class="ccs-login__cert">
                    <svg class="ccs-login__cert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="M9 12l2 2 4-4"/>
                    </svg>
                    <span class="ccs-login__cert-label">PCI DSS</span>
                </div>
                <div class="ccs-login__cert">
                    <svg class="ccs-login__cert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 6v6l4 2"/>
                    </svg>
                    <span class="ccs-login__cert-label">ISO 27001</span>
                </div>
                <div class="ccs-login__cert">
                    <svg class="ccs-login__cert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <path d="M3 9h18M9 21V9"/>
                    </svg>
                    <span class="ccs-login__cert-label">SOC 2</span>
                </div>
            </div>

            <!-- Ledger Statement -->
            <p class="ccs-login__ledger-statement">
                All transactions are logged immutably in the Lerian Midaz ledger.
            </p>

            <!-- Legal Links -->
            <div class="ccs-login__legal">
                <a href="privacy.php" class="ccs-login__legal-link">Privacy Policy</a>
                <span class="ccs-login__legal-divider" aria-hidden="true">|</span>
                <a href="terms.php" class="ccs-login__legal-link">Terms of Service</a>
            </div>

            <!-- Copyright -->
            <p class="ccs-login__copyright">
                © <?= date('Y') ?> Blackbox EYE™. All rights reserved.
            </p>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
    (function() {
        'use strict';
        
        // Password visibility toggle
        const passwordToggle = document.querySelector('.ccs-login__password-toggle');
        const passwordInput = document.getElementById('ccs-password');
        const showIcon = document.querySelector('.ccs-login__eye-icon--show');
        const hideIcon = document.querySelector('.ccs-login__eye-icon--hide');
        
        if (passwordToggle && passwordInput) {
            passwordToggle.addEventListener('click', function() {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                showIcon.style.display = isPassword ? 'none' : 'block';
                hideIcon.style.display = isPassword ? 'block' : 'none';
                this.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            });
        }
        
        // Password strength indicator
        const strengthFill = document.querySelector('.ccs-login__strength-fill');
        const strengthText = document.querySelector('.ccs-login__strength-text');
        
        if (passwordInput && strengthFill && strengthText) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                let label = 'Password strength';
                
                if (password.length >= 8) strength++;
                if (password.length >= 12) strength++;
                if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
                if (/\d/.test(password)) strength++;
                if (/[^a-zA-Z0-9]/.test(password)) strength++;
                
                // Normalize to 0-3 scale
                if (strength <= 1) {
                    strength = 0;
                    label = password.length > 0 ? 'Weak' : 'Password strength';
                } else if (strength <= 3) {
                    strength = 1;
                    label = 'Moderate';
                } else {
                    strength = 2;
                    label = 'Strong';
                }
                
                strengthFill.setAttribute('data-strength', strength);
                strengthText.textContent = label;
            });
        }
    })();
    </script>
</body>
</html>
