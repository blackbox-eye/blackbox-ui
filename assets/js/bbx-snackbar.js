/**
 * Blackbox EYE Snackbar/Toast System
 * 
 * Non-blocking notification system for user feedback.
 * 
 * Usage:
 * bbxSnackbar.success('Operation completed!');
 * bbxSnackbar.error('Something went wrong');
 * bbxSnackbar.warning('Please review your input');
 * bbxSnackbar.info('New updates available');
 * bbxSnackbar.show('Custom message', { type: 'success', duration: 5000 });
 */

(function() {
    'use strict';

    // Icon SVGs
    const icons = {
        success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="9 12 12 15 16 10"/></svg>',
        error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
        close: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
    };

    let container = null;

    /**
     * Get or create the snackbar container
     */
    function getContainer() {
        if (!container) {
            container = document.createElement('div');
            container.className = 'bbx-snackbar-container';
            container.setAttribute('role', 'status');
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-atomic', 'false');
            document.body.appendChild(container);
        }
        return container;
    }

    /**
     * Show a snackbar notification
     * @param {string} message - The message to display
     * @param {Object} options - Configuration options
     * @param {string} options.type - 'success' | 'error' | 'warning' | 'info'
     * @param {number} options.duration - Auto-dismiss time in ms (0 = no auto-dismiss)
     * @param {boolean} options.dismissible - Show dismiss button
     * @param {boolean} options.showProgress - Show countdown progress bar
     */
    function show(message, options = {}) {
        const {
            type = 'info',
            duration = 4000,
            dismissible = true,
            showProgress = true
        } = options;

        const container = getContainer();

        // Create snackbar element
        const snackbar = document.createElement('div');
        snackbar.className = `bbx-snackbar bbx-snackbar--${type}`;
        snackbar.setAttribute('role', 'alert');

        // Build HTML
        let html = `
            <div class="bbx-snackbar__icon">${icons[type] || icons.info}</div>
            <div class="bbx-snackbar__content">
                <p class="bbx-snackbar__message">${escapeHtml(message)}</p>
            </div>
        `;

        if (dismissible) {
            html += `
                <button type="button" class="bbx-snackbar__dismiss" aria-label="Dismiss notification">
                    ${icons.close}
                </button>
            `;
        }

        if (showProgress && duration > 0) {
            html += `
                <div class="bbx-snackbar__progress">
                    <div class="bbx-snackbar__progress-bar"></div>
                </div>
            `;
        }

        snackbar.innerHTML = html;

        // Add to container
        container.appendChild(snackbar);

        // Trigger animation
        requestAnimationFrame(() => {
            snackbar.classList.add('is-visible');
        });

        // Start progress bar animation
        if (showProgress && duration > 0) {
            const progressBar = snackbar.querySelector('.bbx-snackbar__progress-bar');
            if (progressBar) {
                progressBar.style.transition = `transform ${duration}ms linear`;
                requestAnimationFrame(() => {
                    progressBar.style.transform = 'scaleX(0)';
                });
            }
        }

        // Dismiss handler
        const dismiss = () => {
            snackbar.classList.remove('is-visible');
            snackbar.classList.add('is-exiting');
            
            setTimeout(() => {
                if (snackbar.parentNode) {
                    snackbar.parentNode.removeChild(snackbar);
                }
            }, 250);
        };

        // Auto-dismiss
        let dismissTimeout = null;
        if (duration > 0) {
            dismissTimeout = setTimeout(dismiss, duration);
        }

        // Dismiss button
        const dismissBtn = snackbar.querySelector('.bbx-snackbar__dismiss');
        if (dismissBtn) {
            dismissBtn.addEventListener('click', () => {
                if (dismissTimeout) clearTimeout(dismissTimeout);
                dismiss();
            });
        }

        // Pause on hover
        snackbar.addEventListener('mouseenter', () => {
            if (dismissTimeout) clearTimeout(dismissTimeout);
            const progressBar = snackbar.querySelector('.bbx-snackbar__progress-bar');
            if (progressBar) progressBar.style.animationPlayState = 'paused';
        });

        snackbar.addEventListener('mouseleave', () => {
            if (duration > 0) {
                dismissTimeout = setTimeout(dismiss, 1500);
            }
        });

        return { dismiss };
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Convenience methods
    const success = (message, options = {}) => show(message, { ...options, type: 'success' });
    const error = (message, options = {}) => show(message, { ...options, type: 'error' });
    const warning = (message, options = {}) => show(message, { ...options, type: 'warning' });
    const info = (message, options = {}) => show(message, { ...options, type: 'info' });

    // Expose API
    window.bbxSnackbar = {
        show,
        success,
        error,
        warning,
        info
    };
})();
