<?php
/**
 * MFA Verification Modal Component
 * 
 * Two-step verification modal for CCS and other secure consoles.
 * Supports TOTP (Authenticator app), SMS, and recovery codes.
 * 
 * Usage: include this file and call with JavaScript:
 * window.bbxMfa.show({ onSuccess: callback, onCancel: callback })
 * 
 * Sprint 2 will connect to real MFA backend.
 */
?>

<!-- MFA Modal Overlay -->
<div id="bbx-mfa-modal" class="bbx-mfa" role="dialog" aria-modal="true" aria-labelledby="bbx-mfa-title" aria-hidden="true">
    <div class="bbx-mfa__backdrop"></div>
    
    <div class="bbx-mfa__container">
        <!-- Progress Stepper -->
        <div class="bbx-mfa__stepper" role="group" aria-label="Authentication progress">
            <div class="bbx-mfa__step bbx-mfa__step--complete" data-step="1">
                <span class="bbx-mfa__step-number">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </span>
                <span class="bbx-mfa__step-label">Credentials</span>
            </div>
            <div class="bbx-mfa__step-line bbx-mfa__step-line--complete" aria-hidden="true"></div>
            <div class="bbx-mfa__step bbx-mfa__step--active" data-step="2">
                <span class="bbx-mfa__step-number">2</span>
                <span class="bbx-mfa__step-label">Verification</span>
            </div>
            <div class="bbx-mfa__step-line" aria-hidden="true"></div>
            <div class="bbx-mfa__step" data-step="3">
                <span class="bbx-mfa__step-number">3</span>
                <span class="bbx-mfa__step-label">Access</span>
            </div>
        </div>

        <!-- Modal Card -->
        <div class="bbx-mfa__card">
            <button type="button" class="bbx-mfa__close" aria-label="Cancel verification and close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>

            <div class="bbx-mfa__header">
                <div class="bbx-mfa__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="M9 12l2 2 4-4"/>
                    </svg>
                </div>
                <h2 id="bbx-mfa-title" class="bbx-mfa__title">Two-Step Verification</h2>
                <p class="bbx-mfa__subtitle">Enter the 6-digit code from your authenticator app</p>
            </div>

            <!-- TOTP Code Input -->
            <form class="bbx-mfa__form" data-mfa-form>
                <div class="bbx-mfa__code-inputs" role="group" aria-label="Verification code">
                    <input type="text" 
                           class="bbx-mfa__code-digit" 
                           maxlength="1" 
                           pattern="[0-9]" 
                           inputmode="numeric" 
                           autocomplete="one-time-code"
                           aria-label="Digit 1"
                           data-digit="1"
                           required>
                    <input type="text" 
                           class="bbx-mfa__code-digit" 
                           maxlength="1" 
                           pattern="[0-9]" 
                           inputmode="numeric"
                           aria-label="Digit 2"
                           data-digit="2"
                           required>
                    <input type="text" 
                           class="bbx-mfa__code-digit" 
                           maxlength="1" 
                           pattern="[0-9]" 
                           inputmode="numeric"
                           aria-label="Digit 3"
                           data-digit="3"
                           required>
                    <span class="bbx-mfa__code-separator" aria-hidden="true">–</span>
                    <input type="text" 
                           class="bbx-mfa__code-digit" 
                           maxlength="1" 
                           pattern="[0-9]" 
                           inputmode="numeric"
                           aria-label="Digit 4"
                           data-digit="4"
                           required>
                    <input type="text" 
                           class="bbx-mfa__code-digit" 
                           maxlength="1" 
                           pattern="[0-9]" 
                           inputmode="numeric"
                           aria-label="Digit 5"
                           data-digit="5"
                           required>
                    <input type="text" 
                           class="bbx-mfa__code-digit" 
                           maxlength="1" 
                           pattern="[0-9]" 
                           inputmode="numeric"
                           aria-label="Digit 6"
                           data-digit="6"
                           required>
                </div>

                <!-- Error message -->
                <div class="bbx-mfa__error" role="alert" aria-live="polite" hidden>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span class="bbx-mfa__error-text">Invalid code. Please try again.</span>
                </div>

                <button type="submit" class="bbx-mfa__submit">
                    <span class="bbx-mfa__submit-text">Verify</span>
                    <span class="bbx-mfa__submit-loading" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20"/>
                        </svg>
                    </span>
                </button>
            </form>

            <!-- Alternative methods -->
            <div class="bbx-mfa__alternatives">
                <p class="bbx-mfa__alternatives-label">Can't access your app?</p>
                <div class="bbx-mfa__alternatives-btns">
                    <button type="button" class="bbx-mfa__alt-btn" data-method="sms" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                        <span>SMS code</span>
                    </button>
                    <button type="button" class="bbx-mfa__alt-btn" data-method="recovery" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <span>Recovery code</span>
                    </button>
                </div>
                <p class="bbx-mfa__alternatives-note">Alternative methods available in Sprint 2.</p>
            </div>
        </div>
    </div>
</div>

<style>
/* ================================================
   MFA MODAL STYLES
   ================================================ */

.bbx-mfa {
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.25s ease, visibility 0.25s ease;
}

.bbx-mfa[aria-hidden="false"] {
    opacity: 1;
    visibility: visible;
}

.bbx-mfa__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(10, 14, 20, 0.9);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.bbx-mfa__container {
    position: relative;
    width: 100%;
    max-width: 400px;
    transform: translateY(20px) scale(0.98);
    transition: transform 0.3s ease;
}

.bbx-mfa[aria-hidden="false"] .bbx-mfa__container {
    transform: translateY(0) scale(1);
}

/* Stepper */
.bbx-mfa__stepper {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0;
    margin-bottom: 1.5rem;
}

.bbx-mfa__step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.375rem;
}

.bbx-mfa__step-number {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
    border: 2px solid rgba(255,255,255,0.15);
    font-size: 0.75rem;
    font-weight: 600;
    color: rgba(255,255,255,0.5);
    transition: all 0.3s ease;
}

.bbx-mfa__step-number svg {
    width: 14px;
    height: 14px;
}

.bbx-mfa__step--complete .bbx-mfa__step-number {
    background: rgba(46, 204, 113, 0.2);
    border-color: #2ecc71;
    color: #2ecc71;
}

.bbx-mfa__step--active .bbx-mfa__step-number {
    background: rgba(46, 204, 113, 0.15);
    border-color: #2ecc71;
    color: #fff;
    box-shadow: 0 0 12px rgba(46, 204, 113, 0.3);
}

.bbx-mfa__step-label {
    font-size: 0.65rem;
    color: rgba(255,255,255,0.4);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.bbx-mfa__step--active .bbx-mfa__step-label,
.bbx-mfa__step--complete .bbx-mfa__step-label {
    color: rgba(255,255,255,0.7);
}

.bbx-mfa__step-line {
    width: 2.5rem;
    height: 2px;
    background: rgba(255,255,255,0.1);
    margin: 0 0.25rem;
    margin-bottom: 1.25rem;
}

.bbx-mfa__step-line--complete {
    background: linear-gradient(90deg, #2ecc71, rgba(46, 204, 113, 0.5));
}

/* Modal Card */
.bbx-mfa__card {
    position: relative;
    background: rgba(15, 20, 28, 0.95);
    border: 1px solid rgba(46, 204, 113, 0.15);
    border-radius: 1rem;
    padding: 2rem 1.5rem;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.bbx-mfa__close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 0.5rem;
    color: rgba(255,255,255,0.5);
    cursor: pointer;
    transition: all 0.2s ease;
}

.bbx-mfa__close:hover {
    background: rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.9);
}

.bbx-mfa__close svg {
    width: 16px;
    height: 16px;
}

/* Header */
.bbx-mfa__header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.bbx-mfa__icon {
    width: 3.5rem;
    height: 3.5rem;
    margin: 0 auto 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(46, 204, 113, 0.15), rgba(46, 204, 113, 0.05));
    border: 1px solid rgba(46, 204, 113, 0.3);
    border-radius: 1rem;
    color: #2ecc71;
}

.bbx-mfa__icon svg {
    width: 1.75rem;
    height: 1.75rem;
}

.bbx-mfa__title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #fff;
    margin: 0 0 0.5rem;
}

.bbx-mfa__subtitle {
    font-size: 0.875rem;
    color: rgba(255,255,255,0.6);
    margin: 0;
}

/* Code Inputs */
.bbx-mfa__code-inputs {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.bbx-mfa__code-digit {
    width: 2.75rem;
    height: 3.5rem;
    background: rgba(255,255,255,0.04);
    border: 2px solid rgba(255,255,255,0.15);
    border-radius: 0.625rem;
    color: #fff;
    font-size: 1.5rem;
    font-weight: 600;
    font-family: 'Inter', monospace;
    text-align: center;
    transition: all 0.2s ease;
}

.bbx-mfa__code-digit:hover {
    border-color: rgba(46, 204, 113, 0.3);
}

.bbx-mfa__code-digit:focus {
    outline: none;
    border-color: #2ecc71;
    background: rgba(46, 204, 113, 0.08);
    box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.15);
}

.bbx-mfa__code-digit.is-filled {
    border-color: rgba(46, 204, 113, 0.5);
    background: rgba(46, 204, 113, 0.05);
}

.bbx-mfa__code-separator {
    color: rgba(255,255,255,0.3);
    font-size: 1.25rem;
    margin: 0 0.125rem;
}

/* Error */
.bbx-mfa__error {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 0.5rem;
    color: #fca5a5;
    font-size: 0.8rem;
    margin-bottom: 1rem;
}

.bbx-mfa__error[hidden] {
    display: none;
}

.bbx-mfa__error svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

/* Submit */
.bbx-mfa__submit {
    width: 100%;
    min-height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    border: none;
    border-radius: 0.625rem;
    color: #0a0e14;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.2s ease;
}

.bbx-mfa__submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(46, 204, 113, 0.3);
}

.bbx-mfa__submit:active {
    transform: translateY(0);
}

.bbx-mfa__submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.bbx-mfa__submit-loading {
    display: none;
}

.bbx-mfa__submit.is-loading .bbx-mfa__submit-text {
    display: none;
}

.bbx-mfa__submit.is-loading .bbx-mfa__submit-loading {
    display: block;
}

.bbx-mfa__submit-loading svg {
    width: 20px;
    height: 20px;
    animation: mfa-spin 1s linear infinite;
}

@keyframes mfa-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Alternatives */
.bbx-mfa__alternatives {
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid rgba(255,255,255,0.08);
    text-align: center;
}

.bbx-mfa__alternatives-label {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.5);
    margin: 0 0 0.75rem;
}

.bbx-mfa__alternatives-btns {
    display: flex;
    gap: 0.625rem;
    margin-bottom: 0.75rem;
}

.bbx-mfa__alt-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.375rem;
    min-height: 40px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 0.5rem;
    color: rgba(255,255,255,0.5);
    font-size: 0.75rem;
    cursor: not-allowed;
    opacity: 0.5;
    transition: all 0.2s ease;
}

.bbx-mfa__alt-btn svg {
    width: 14px;
    height: 14px;
}

.bbx-mfa__alternatives-note {
    font-size: 0.65rem;
    color: rgba(255,255,255,0.35);
    margin: 0;
}

/* Mobile adjustments */
@media (max-width: 480px) {
    .bbx-mfa__code-digit {
        width: 2.25rem;
        height: 3rem;
        font-size: 1.25rem;
    }
    
    .bbx-mfa__step-label {
        display: none;
    }
}
</style>

<script>
(function() {
    'use strict';
    
    const modal = document.getElementById('bbx-mfa-modal');
    if (!modal) return;
    
    const form = modal.querySelector('[data-mfa-form]');
    const digits = modal.querySelectorAll('.bbx-mfa__code-digit');
    const submitBtn = modal.querySelector('.bbx-mfa__submit');
    const errorEl = modal.querySelector('.bbx-mfa__error');
    const closeBtn = modal.querySelector('.bbx-mfa__close');
    const backdrop = modal.querySelector('.bbx-mfa__backdrop');
    
    let onSuccessCallback = null;
    let onCancelCallback = null;
    
    // Auto-advance on digit input
    digits.forEach((input, index) => {
        input.addEventListener('input', function(e) {
            const value = e.target.value.replace(/\D/g, '');
            e.target.value = value.slice(0, 1);
            
            if (value && index < digits.length - 1) {
                digits[index + 1].focus();
            }
            
            e.target.classList.toggle('is-filled', !!value);
        });
        
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                digits[index - 1].focus();
            }
        });
        
        // Paste support
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
            pastedData.split('').forEach((char, i) => {
                if (digits[i]) {
                    digits[i].value = char;
                    digits[i].classList.add('is-filled');
                }
            });
            if (digits[pastedData.length - 1]) {
                digits[pastedData.length - 1].focus();
            }
        });
    });
    
    // Form submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const code = Array.from(digits).map(d => d.value).join('');
        if (code.length !== 6) {
            showError('Please enter all 6 digits.');
            return;
        }
        
        submitBtn.classList.add('is-loading');
        submitBtn.disabled = true;
        errorEl.hidden = true;
        
        // Simulate verification (Sprint 2 will use real API)
        setTimeout(() => {
            // Demo: code "123456" succeeds, others fail
            if (code === '123456') {
                if (onSuccessCallback) onSuccessCallback(code);
                hide();
            } else {
                showError('Invalid code. Please try again.');
                submitBtn.classList.remove('is-loading');
                submitBtn.disabled = false;
                digits[0].focus();
                digits.forEach(d => d.value = '');
            }
        }, 1500);
    });
    
    function showError(message) {
        errorEl.querySelector('.bbx-mfa__error-text').textContent = message;
        errorEl.hidden = false;
    }
    
    function show(options = {}) {
        onSuccessCallback = options.onSuccess || null;
        onCancelCallback = options.onCancel || null;
        
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        
        // Focus first digit
        setTimeout(() => digits[0].focus(), 100);
    }
    
    function hide() {
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        submitBtn.classList.remove('is-loading');
        submitBtn.disabled = false;
        errorEl.hidden = true;
        digits.forEach(d => {
            d.value = '';
            d.classList.remove('is-filled');
        });
    }
    
    // Close handlers
    closeBtn.addEventListener('click', () => {
        if (onCancelCallback) onCancelCallback();
        hide();
    });
    
    backdrop.addEventListener('click', () => {
        if (onCancelCallback) onCancelCallback();
        hide();
    });
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') {
            if (onCancelCallback) onCancelCallback();
            hide();
        }
    });
    
    // Expose API
    window.bbxMfa = { show, hide };
})();
</script>
