/**
 * Password Visibility Toggle Component
 *
 * Automatically adds show/hide eye icons to all password and PIN input fields.
 * Supports dark and light themes.
 *
 * Usage: Include this script after DOM is loaded. It will auto-initialize
 * on all inputs with type="password" or data-password-toggle attribute.
 */

(function () {
  'use strict';

  // SVG icons for show/hide states
  const ICONS = {
    show: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
      <circle cx="12" cy="12" r="3"></circle>
    </svg>`,
    hide: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
      <line x1="1" y1="1" x2="23" y2="23"></line>
    </svg>`
  };

  /**
   * Initialize password toggle for a given input element
   * @param {HTMLInputElement} input - The password input element
   */
  function initPasswordToggle(input) {
    // Skip if already initialized
    if (input.dataset.passwordToggleInit === 'true') return;

    // Mark as initialized
    input.dataset.passwordToggleInit = 'true';

    // Get or create wrapper
    let wrapper = input.parentElement;
    if (!wrapper.classList.contains('password-field')) {
      // Create wrapper
      wrapper = document.createElement('div');
      wrapper.className = 'password-field';
      input.parentNode.insertBefore(wrapper, input);
      wrapper.appendChild(input);
    }

    // Create toggle button
    const toggleBtn = document.createElement('button');
    toggleBtn.type = 'button';
    toggleBtn.className = 'password-toggle';
    toggleBtn.setAttribute('aria-label', 'Vis adgangskode');
    toggleBtn.setAttribute('tabindex', '0');
    toggleBtn.innerHTML = ICONS.show;

    // Insert button after input
    wrapper.appendChild(toggleBtn);

    // Track visibility state
    let isVisible = false;

    // Toggle handler
    toggleBtn.addEventListener('click', function (e) {
      e.preventDefault();
      isVisible = !isVisible;

      if (isVisible) {
        input.type = 'text';
        toggleBtn.innerHTML = ICONS.hide;
        toggleBtn.setAttribute('aria-label', 'Skjul adgangskode');
        toggleBtn.classList.add('password-toggle--visible');
      } else {
        input.type = 'password';
        toggleBtn.innerHTML = ICONS.show;
        toggleBtn.setAttribute('aria-label', 'Vis adgangskode');
        toggleBtn.classList.remove('password-toggle--visible');
      }

      // Keep focus on input
      input.focus();
    });

    // Keyboard accessibility
    toggleBtn.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggleBtn.click();
      }
    });
  }

  /**
   * Initialize all password fields on the page
   */
  function initAllPasswordToggles() {
    // Select all password inputs and PIN fields
    const passwordInputs = document.querySelectorAll(
      'input[type="password"], input[data-password-toggle]'
    );

    passwordInputs.forEach(initPasswordToggle);
  }

  /**
   * Observe DOM for dynamically added password fields
   */
  function observeDOMChanges() {
    const observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (node) {
          if (node.nodeType === Node.ELEMENT_NODE) {
            // Check if the added node is a password input
            if (node.matches && node.matches('input[type="password"]')) {
              initPasswordToggle(node);
            }
            // Check for password inputs inside the added node
            const inputs = node.querySelectorAll && node.querySelectorAll('input[type="password"]');
            if (inputs) {
              inputs.forEach(initPasswordToggle);
            }
          }
        });
      });
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
  }

  // Initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      initAllPasswordToggles();
      observeDOMChanges();
    });
  } else {
    initAllPasswordToggles();
    observeDOMChanges();
  }

  // Expose API for manual initialization
  window.PasswordToggle = {
    init: initPasswordToggle,
    initAll: initAllPasswordToggles
  };
})();
