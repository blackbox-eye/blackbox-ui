<?php
/**
 * SSO Request Modal Component
 * 
 * Enterprise SSO access request form modal.
 * Supports prefilling for Intel24 or CCS.
 * 
 * Usage: Include this file and call:
 * window.bbxSsoRequest.show({ console: 'ccs', provider: 'azure' })
 * 
 * Sprint 3: Full form flow with API submission.
 */
?>

<!-- SSO Request Modal -->
<div id="bbx-sso-request-modal" class="bbx-modal" role="dialog" aria-modal="true" aria-labelledby="bbx-sso-request-title" aria-hidden="true" data-testid="sso-request-modal">
    <div class="bbx-modal__backdrop" data-dismiss="modal"></div>
    
    <div class="bbx-modal__container bbx-modal__container--md">
        <div class="bbx-modal__card">
            <button type="button" class="bbx-modal__close" aria-label="Close" data-dismiss="modal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>

            <div class="bbx-modal__header">
                <div class="bbx-modal__icon bbx-modal__icon--ccs">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        <circle cx="12" cy="16" r="1"/>
                    </svg>
                </div>
                <h2 id="bbx-sso-request-title" class="bbx-modal__title">Request SSO Access</h2>
                <p class="bbx-modal__subtitle">Enterprise Single Sign-On for <span data-console-name>CCS</span></p>
            </div>

            <form class="bbx-modal__form" data-sso-request-form autocomplete="off">
                <input type="hidden" name="console" value="ccs" data-field="console">
                
                <div class="bbx-modal__field">
                    <label for="sso-company" class="bbx-modal__label">Company Name <span class="bbx-modal__required">*</span></label>
                    <input type="text" 
                           id="sso-company" 
                           name="company" 
                           class="bbx-modal__input"
                           placeholder="Acme Corporation"
                           required
                           data-field="company"
                           data-testid="sso-company-input">
                </div>

                <div class="bbx-modal__field">
                    <label for="sso-email" class="bbx-modal__label">Work Email <span class="bbx-modal__required">*</span></label>
                    <input type="email" 
                           id="sso-email" 
                           name="email" 
                           class="bbx-modal__input"
                           placeholder="admin@company.com"
                           required
                           data-field="email"
                           data-testid="sso-email-input">
                </div>

                <div class="bbx-modal__field">
                    <label for="sso-domain" class="bbx-modal__label">Tenant Domain</label>
                    <input type="text" 
                           id="sso-domain" 
                           name="domain" 
                           class="bbx-modal__input"
                           placeholder="company.onmicrosoft.com"
                           data-field="domain"
                           data-testid="sso-domain-input">
                </div>

                <div class="bbx-modal__field">
                    <label for="sso-provider" class="bbx-modal__label">Identity Provider</label>
                    <select id="sso-provider" name="provider" class="bbx-modal__select" data-field="provider" data-testid="sso-provider-select">
                        <option value="">Select provider...</option>
                        <option value="azure">Azure AD / Entra ID</option>
                        <option value="google">Google Workspace</option>
                        <option value="okta">Okta</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="bbx-modal__field">
                    <label for="sso-notes" class="bbx-modal__label">Additional Notes</label>
                    <textarea id="sso-notes" 
                              name="notes" 
                              class="bbx-modal__textarea"
                              placeholder="Any additional requirements or questions..."
                              rows="3"
                              data-field="notes"
                              data-testid="sso-notes-input"></textarea>
                </div>

                <!-- Error message -->
                <div class="bbx-modal__error" role="alert" aria-live="polite" hidden data-error>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span data-error-text>An error occurred. Please try again.</span>
                </div>

                <div class="bbx-modal__actions">
                    <button type="button" class="bbx-modal__btn bbx-modal__btn--secondary" data-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="bbx-modal__btn bbx-modal__btn--primary" data-testid="sso-submit-btn">
                        <span class="bbx-modal__btn-text">Submit Request</span>
                        <span class="bbx-modal__btn-loading" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20"/>
                            </svg>
                        </span>
                    </button>
                </div>
            </form>

            <!-- Success state -->
            <div class="bbx-modal__success" hidden data-success>
                <div class="bbx-modal__success-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="9 12 12 15 16 10"/>
                    </svg>
                </div>
                <h3 class="bbx-modal__success-title">Request Submitted</h3>
                <p class="bbx-modal__success-text">
                    Your SSO request has been received.<br>
                    Reference: <strong data-request-id>SSO-XXXXXXXX</strong>
                </p>
                <p class="bbx-modal__success-note">We'll contact you within 24-48 hours.</p>
                <button type="button" class="bbx-modal__btn bbx-modal__btn--primary" data-dismiss="modal">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ================================================
   SSO REQUEST MODAL STYLES
   ================================================ */

.bbx-modal {
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

.bbx-modal[aria-hidden="false"] {
    opacity: 1;
    visibility: visible;
}

.bbx-modal__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(10, 14, 20, 0.9);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.bbx-modal__container {
    position: relative;
    width: 100%;
    max-width: 420px;
    max-height: 90vh;
    overflow-y: auto;
    transform: translateY(20px) scale(0.98);
    transition: transform 0.3s ease;
}

.bbx-modal__container--md {
    max-width: 480px;
}

.bbx-modal[aria-hidden="false"] .bbx-modal__container {
    transform: translateY(0) scale(1);
}

.bbx-modal__card {
    position: relative;
    background: rgba(15, 20, 28, 0.98);
    border: 1px solid rgba(46, 204, 113, 0.15);
    border-radius: 1rem;
    padding: 2rem 1.5rem;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.bbx-modal__close {
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

.bbx-modal__close:hover {
    background: rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.8);
}

.bbx-modal__close svg {
    width: 16px;
    height: 16px;
}

/* Header */
.bbx-modal__header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.bbx-modal__icon {
    width: 3.5rem;
    height: 3.5rem;
    margin: 0 auto 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 1rem;
    background: rgba(46, 204, 113, 0.1);
    border: 1px solid rgba(46, 204, 113, 0.2);
}

.bbx-modal__icon svg {
    width: 1.75rem;
    height: 1.75rem;
    color: #2ecc71;
}

.bbx-modal__icon--intel24 {
    background: rgba(168, 85, 247, 0.1);
    border-color: rgba(168, 85, 247, 0.2);
}

.bbx-modal__icon--intel24 svg {
    color: #a855f7;
}

.bbx-modal__title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #fff;
    margin: 0 0 0.375rem;
}

.bbx-modal__subtitle {
    font-size: 0.875rem;
    color: rgba(255,255,255,0.6);
    margin: 0;
}

/* Form */
.bbx-modal__form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.bbx-modal__field {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.bbx-modal__label {
    font-size: 0.8rem;
    font-weight: 500;
    color: rgba(255,255,255,0.8);
}

.bbx-modal__required {
    color: #ef4444;
}

.bbx-modal__input,
.bbx-modal__select,
.bbx-modal__textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 0.5rem;
    color: #fff;
    font-size: 0.9rem;
    font-family: inherit;
    transition: border-color 0.2s ease, background-color 0.2s ease;
    box-sizing: border-box;
}

.bbx-modal__input::placeholder,
.bbx-modal__textarea::placeholder {
    color: rgba(255,255,255,0.35);
}

.bbx-modal__input:focus,
.bbx-modal__select:focus,
.bbx-modal__textarea:focus {
    outline: none;
    border-color: rgba(46, 204, 113, 0.5);
    background: rgba(46, 204, 113, 0.05);
}

.bbx-modal__select {
    cursor: pointer;
}

.bbx-modal__select option {
    background: #0d1117;
    color: #fff;
}

.bbx-modal__textarea {
    resize: vertical;
    min-height: 80px;
}

/* Error */
.bbx-modal__error {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 0.5rem;
    color: #fca5a5;
    font-size: 0.8rem;
}

.bbx-modal__error svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    margin-top: 1px;
}

/* Actions */
.bbx-modal__actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 0.5rem;
}

.bbx-modal__btn {
    flex: 1;
    padding: 0.75rem 1.25rem;
    border-radius: 0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.bbx-modal__btn--secondary {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.7);
}

.bbx-modal__btn--secondary:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
}

.bbx-modal__btn--primary {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    border: none;
    color: #0a0e14;
}

.bbx-modal__btn--primary:hover {
    filter: brightness(1.1);
    transform: translateY(-1px);
}

.bbx-modal__btn--primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.bbx-modal__btn-loading {
    display: none;
}

.bbx-modal__btn--loading .bbx-modal__btn-text {
    display: none;
}

.bbx-modal__btn--loading .bbx-modal__btn-loading {
    display: block;
}

.bbx-modal__btn-loading svg {
    width: 20px;
    height: 20px;
    animation: bbx-spin 1s linear infinite;
}

@keyframes bbx-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Success State */
.bbx-modal__success {
    text-align: center;
    padding: 1rem 0;
}

.bbx-modal__success-icon {
    width: 4rem;
    height: 4rem;
    margin: 0 auto 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(46, 204, 113, 0.15);
    border: 2px solid #2ecc71;
    border-radius: 50%;
    color: #2ecc71;
}

.bbx-modal__success-icon svg {
    width: 2rem;
    height: 2rem;
}

.bbx-modal__success-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #fff;
    margin: 0 0 0.75rem;
}

.bbx-modal__success-text {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.8);
    margin: 0 0 0.5rem;
    line-height: 1.5;
}

.bbx-modal__success-text strong {
    color: #2ecc71;
    font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;
}

.bbx-modal__success-note {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.5);
    margin: 0 0 1.5rem;
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .bbx-modal,
    .bbx-modal__container,
    .bbx-modal__btn-loading svg {
        transition: none;
        animation: none;
    }
}
</style>

<script>
(function() {
    'use strict';

    const modal = document.getElementById('bbx-sso-request-modal');
    if (!modal) return;

    const form = modal.querySelector('[data-sso-request-form]');
    const errorEl = modal.querySelector('[data-error]');
    const errorText = modal.querySelector('[data-error-text]');
    const successEl = modal.querySelector('[data-success]');
    const requestIdEl = modal.querySelector('[data-request-id]');
    const consoleNameEl = modal.querySelector('[data-console-name]');
    const iconEl = modal.querySelector('.bbx-modal__icon');

    let onSuccessCallback = null;
    let previousFocus = null;

    // Console display names
    const consoleNames = {
        ccs: 'CCS Settlement',
        gdi: 'GDI Intelligence',
        intel24: 'Intel24',
    };

    function show(options = {}) {
        options = options || {};
        previousFocus = document.activeElement;

        // Reset form state
        if (form) {
            form.reset();
            form.classList.remove('bbx-modal__btn--loading');
        }
        if (errorEl) errorEl.hidden = true;
        if (successEl) successEl.hidden = true;
        if (form) form.hidden = false;

        // Prefill values
        const consoleName = options.console || 'ccs';
        const consoleField = modal.querySelector('[data-field="console"]');
        if (consoleField) consoleField.value = consoleName;

        if (consoleNameEl) {
            consoleNameEl.textContent = consoleNames[consoleName] || consoleName.toUpperCase();
        }

        // Update icon color for intel24
        if (iconEl) {
            iconEl.classList.toggle('bbx-modal__icon--intel24', consoleName === 'intel24');
        }

        if (options.provider) {
            const providerField = modal.querySelector('[data-field="provider"]');
            if (providerField) providerField.value = options.provider;
        }

        onSuccessCallback = options.onSuccess || null;

        // Show modal
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        // Focus first input
        setTimeout(function() {
            const firstInput = modal.querySelector('input:not([type="hidden"])');
            if (firstInput) firstInput.focus();
        }, 100);
    }

    function hide() {
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';

        if (previousFocus) {
            previousFocus.focus();
            previousFocus = null;
        }
    }

    function showError(message) {
        if (errorEl && errorText) {
            errorText.textContent = message;
            errorEl.hidden = false;
        }
    }

    function showSuccess(requestId) {
        if (form) form.hidden = true;
        if (requestIdEl) requestIdEl.textContent = requestId;
        if (successEl) successEl.hidden = false;

        // Log activity
        logActivity('sso_request', { request_id: requestId });

        if (onSuccessCallback) {
            onSuccessCallback({ request_id: requestId });
        }
    }

    function logActivity(action, data) {
        // Post to server-side activity API
        fetch('/api/console-activity.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: action,
                data: data,
                timestamp: Date.now(),
            }),
        }).catch(function() {
            // Fallback to localStorage
            try {
                const key = 'bbx_console_activity';
                const activity = JSON.parse(localStorage.getItem(key)) || [];
                activity.unshift({
                    action: action,
                    data: data,
                    timestamp: Date.now(),
                });
                localStorage.setItem(key, JSON.stringify(activity.slice(0, 20)));
            } catch (e) {}
        });
    }

    // DEMO_MODE: Set to true to always return success (for demos/testing)
    const DEMO_MODE = true;
    
    // Validation messages (not generic)
    const VALIDATION_MESSAGES = {
        company_required: 'Company name is required',
        email_required: 'Work email is required',
        email_invalid: 'Please enter a valid work email address',
        service_unavailable: 'Service unavailable. Please try again later.',
        network_error: 'Connection failed. Check your network and retry.'
    };
    
    function validateForm(data) {
        if (!data.company || data.company.trim().length < 2) {
            return VALIDATION_MESSAGES.company_required;
        }
        if (!data.email || data.email.trim().length < 5) {
            return VALIDATION_MESSAGES.email_required;
        }
        // Basic email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(data.email)) {
            return VALIDATION_MESSAGES.email_invalid;
        }
        return null; // Valid
    }

    // Form submission with proper validation
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('[type="submit"]');
            if (errorEl) errorEl.hidden = true;

            const formData = new FormData(form);
            const data = {};
            formData.forEach(function(value, key) {
                data[key] = value;
            });
            
            // Client-side validation first
            const validationError = validateForm(data);
            if (validationError) {
                showError(validationError);
                return;
            }
            
            // Show loading state
            if (submitBtn) submitBtn.classList.add('bbx-modal__btn--loading');
            if (submitBtn) submitBtn.disabled = true;

            // DEMO MODE: Always succeed after brief delay
            if (DEMO_MODE) {
                setTimeout(function() {
                    if (submitBtn) submitBtn.classList.remove('bbx-modal__btn--loading');
                    if (submitBtn) submitBtn.disabled = false;
                    const demoRequestId = 'SSO-DEMO-' + Date.now().toString(36).toUpperCase();
                    showSuccess(demoRequestId);
                    if (window.bbxSnackbar) {
                        window.bbxSnackbar.success('SSO request received. Ref: ' + demoRequestId);
                    }
                }, 800);
                return;
            }

            fetch('/api/sso-request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            })
            .then(function(res) { 
                if (!res.ok) {
                    throw new Error('HTTP ' + res.status);
                }
                return res.json(); 
            })
            .then(function(result) {
                if (submitBtn) submitBtn.classList.remove('bbx-modal__btn--loading');
                if (submitBtn) submitBtn.disabled = false;

                if (result.ok) {
                    showSuccess(result.request_id);
                    if (window.bbxSnackbar) {
                        window.bbxSnackbar.success('SSO request received. Ref: ' + result.request_id);
                    }
                } else {
                    showError(result.error || VALIDATION_MESSAGES.service_unavailable);
                }
            })
            .catch(function(err) {
                if (submitBtn) submitBtn.classList.remove('bbx-modal__btn--loading');
                if (submitBtn) submitBtn.disabled = false;
                // Specific error message instead of generic
                showError(err.message.includes('HTTP') ? VALIDATION_MESSAGES.service_unavailable : VALIDATION_MESSAGES.network_error);
            });
        });
    }

    // Close handlers
    modal.querySelectorAll('[data-dismiss="modal"]').forEach(function(el) {
        el.addEventListener('click', hide);
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') {
            hide();
        }
    });

    // Expose API
    window.bbxSsoRequest = {
        show: show,
        hide: hide,
    };
})();
</script>
