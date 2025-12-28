/**
 * interface-menu.js — Control Panel Menu Controller
 *
 * Handles the collapsible right-side Control Panel menu for admin pages.
 * Features:
 * - Slide-in/slide-out animation
 * - Keyboard navigation (ESC to close, Tab trapping)
 * - Overlay click to close
 * - ARIA attributes for accessibility
 * - Focus management
 */

(function () {
  "use strict";

  // DOM Elements
  const launcher = document.getElementById("commandDeckLauncher");
  const menu = document.getElementById("commandDeckMenu");
  const overlay = document.getElementById("commandDeckOverlay");
  const closeBtn = document.getElementById("commandDeckClose");

  // Exit if elements don't exist (e.g., on login page with hidden menu)
  if (!launcher || !menu) {
    return;
  }

  /**
   * Opens the Control Panel menu
   */
  function openMenu() {
    menu.classList.add("is-open");
    menu.setAttribute("aria-hidden", "false");
    launcher.setAttribute("aria-expanded", "true");

    if (overlay) {
      overlay.classList.add("is-open");
    }

    // P0 FIX: NO scroll-lock - user must be able to scroll freely
    // document.body.style.overflow = 'hidden'; // REMOVED - violates P0 scroll policy

    // Focus the close button for accessibility
    requestAnimationFrame(() => {
      if (closeBtn) {
        closeBtn.focus();
      }
    });
  }

  /**
   * Closes the Control Panel menu
   */
  function closeMenu() {
    menu.classList.remove("is-open");
    menu.setAttribute("aria-hidden", "true");
    launcher.setAttribute("aria-expanded", "false");

    if (overlay) {
      overlay.classList.remove("is-open");
    }

    // Restore body scroll
    document.body.style.overflow = "";

    // Return focus to the launcher button
    launcher.focus();
  }

  /**
   * Toggles the Control Panel menu
   */
  function toggleMenu() {
    const isOpen = menu.classList.contains("is-open");
    if (isOpen) {
      closeMenu();
    } else {
      openMenu();
    }
  }

  /**
   * Get all focusable elements inside the menu
   */
  function getFocusableElements() {
    return menu.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
  }

  /**
   * Trap focus inside the menu when it's open
   */
  function handleTabKey(event) {
    if (!menu.classList.contains("is-open")) {
      return;
    }

    const focusableElements = getFocusableElements();
    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    if (event.shiftKey && document.activeElement === firstElement) {
      // Shift+Tab on first element -> go to last
      event.preventDefault();
      lastElement.focus();
    } else if (!event.shiftKey && document.activeElement === lastElement) {
      // Tab on last element -> go to first
      event.preventDefault();
      firstElement.focus();
    }
  }

  // Event Listeners

  // Launcher button click
  launcher.addEventListener("click", toggleMenu);

  // Close button click
  if (closeBtn) {
    closeBtn.addEventListener("click", closeMenu);
  }

  // Overlay click to close
  if (overlay) {
    overlay.addEventListener("click", closeMenu);
  }

  // Click outside menu to close
  document.addEventListener("click", (event) => {
    if (!menu.classList.contains("is-open")) {
      return;
    }

    // Check if click is outside menu and launcher
    if (!menu.contains(event.target) && !launcher.contains(event.target)) {
      closeMenu();
    }
  });

  // Keyboard navigation
  document.addEventListener("keydown", (event) => {
    // ESC key to close menu
    if (event.key === "Escape" && menu.classList.contains("is-open")) {
      event.preventDefault();
      closeMenu();
      return;
    }

    // Tab key for focus trapping
    if (event.key === "Tab" && menu.classList.contains("is-open")) {
      handleTabKey(event);
    }
  });

  // Initialize: ensure correct initial state
  menu.setAttribute("aria-hidden", "true");
  launcher.setAttribute("aria-expanded", "false");
})();

/**
 * Login Page Interactions
 *
 * Handles the interaction active state and request access modal
 * for the login page.
 */
(function () {
  "use strict";

  const body = document.body;

  // Only run on login pages
  if (!body.classList.contains("admin-body--login")) {
    return;
  }

  const loginCard = document.querySelector(".login-card");

  if (loginCard) {
    const interactiveElements = loginCard.querySelectorAll("input, button");

    const activate = () => body.classList.add("interaction-active");

    const maybeDeactivate = () => {
      setTimeout(() => {
        const activeElement = document.activeElement;
        if (activeElement && loginCard.contains(activeElement)) {
          return;
        }
        body.classList.remove("interaction-active");
      }, 60);
    };

    interactiveElements.forEach((el) => {
      el.addEventListener("focus", activate);
      el.addEventListener("blur", maybeDeactivate);
      el.addEventListener("click", activate);
    });

    loginCard.addEventListener("mouseenter", activate);
    loginCard.addEventListener("mouseleave", maybeDeactivate);

    document.addEventListener("click", (event) => {
      if (!loginCard.contains(event.target)) {
        body.classList.remove("interaction-active");
      }
    });
  }

  // Request Access Modal
  const requestInit = document.getElementById("requestAccessInit");
  const requestOverlay = document.getElementById("requestAccessOverlay");
  const requestDialog = document.getElementById("requestAccessDialog");
  const requestClose = document.getElementById("requestAccessClose");
  const requestCancel = document.getElementById("requestAccessCancel");
  const requestForm = document.getElementById("requestAccessForm");
  const requestStatus = document.getElementById("requestAccessStatus");
  const requestEmail = document.getElementById("requestEmail");

  if (!requestOverlay || !requestDialog) {
    return;
  }

  const openModal = () => {
    requestOverlay.classList.add("is-visible");
    body.classList.add("interaction-active");
    requestAnimationFrame(() => {
      if (requestEmail) {
        requestEmail.focus();
      }
    });
  };

  const closeModal = () => {
    requestOverlay.classList.remove("is-visible");
    if (requestStatus) {
      requestStatus.textContent = "";
    }
    if (requestInit) {
      requestInit.focus();
    }
    body.classList.remove("interaction-active");
  };

  // Open modal
  if (requestInit) {
    requestInit.addEventListener("click", openModal);
  }

  // Close buttons
  if (requestClose) {
    requestClose.addEventListener("click", closeModal);
  }
  if (requestCancel) {
    requestCancel.addEventListener("click", closeModal);
  }

  // Click outside dialog to close
  requestOverlay.addEventListener("click", (event) => {
    if (event.target === requestOverlay) {
      closeModal();
    }
  });

  // Form submission
  if (requestForm) {
    requestForm.addEventListener("submit", (event) => {
      event.preventDefault();

      if (!requestForm.reportValidity()) {
        return;
      }

      if (requestStatus) {
        requestStatus.textContent = "Sender krypteret anmodning...";
      }

      // Future integration: POST to secure API
      // fetch('/api/access-invite.php', { method: 'POST', body: new FormData(requestForm) })...

      setTimeout(() => {
        if (requestStatus) {
          requestStatus.textContent =
            "Tak. Blackbox EYE-teamet verificerer og udsender en sikker onboarding-mail (typisk < 24 timer).";
        }
        requestForm.reset();
      }, 900);
    });
  }

  // ESC key to close modal
  document.addEventListener("keydown", (event) => {
    if (
      event.key === "Escape" &&
      requestOverlay.classList.contains("is-visible")
    ) {
      closeModal();
    }
  });
})();

/**
 * Theme Toggle Controller
 *
 * Handles dark/light theme switching with localStorage persistence.
 * Features:
 * - Toggle between dark and light themes
 * - Persists user preference in localStorage
 * - Respects system preference as fallback
 * - Updates ARIA attributes and visual indicators
 */
(function () {
  "use strict";

  const STORAGE_KEY = "blackbox-eye-theme";
  const themeToggle = document.getElementById("themeToggle");
  const root = document.documentElement;
  const body = document.body;

  // Exit if no toggle button
  if (!themeToggle) {
    return;
  }

  /**
   * Get the current theme from localStorage or system preference
   */
  function getStoredTheme() {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored) {
      return stored;
    }

    // Check system preference
    if (
      window.matchMedia &&
      window.matchMedia("(prefers-color-scheme: light)").matches
    ) {
      return "light";
    }

    return "dark";
  }

  /**
   * Apply the theme to the document
   */
  function applyTheme(theme) {
    root.setAttribute("data-theme", theme);
    if (body) {
      body.setAttribute("data-theme", theme);
    }

    // Update toggle button label
    const label = themeToggle.querySelector(".theme-toggle-label");
    if (label) {
      label.textContent = theme === "dark" ? "Lyst tema" : "Mørkt tema";
    }

    // Update ARIA
    themeToggle.setAttribute("aria-pressed", theme === "light");
  }

  /**
   * Toggle between dark and light themes
   */
  function toggleTheme() {
    const currentTheme =
      (body && body.getAttribute("data-theme")) ||
      root.getAttribute("data-theme") ||
      "dark";
    const newTheme = currentTheme === "dark" ? "light" : "dark";

    applyTheme(newTheme);
    localStorage.setItem(STORAGE_KEY, newTheme);

    // Dispatch custom event for other components that might need to know
    document.dispatchEvent(
      new CustomEvent("themechange", { detail: { theme: newTheme } })
    );
  }

  // Initialize theme on page load
  function initTheme() {
    const theme = getStoredTheme();
    applyTheme(theme);
  }

  // Event listener for toggle click
  themeToggle.addEventListener("click", toggleTheme);

  // Listen for system preference changes
  if (window.matchMedia) {
    window
      .matchMedia("(prefers-color-scheme: light)")
      .addEventListener("change", (e) => {
        // Only auto-switch if user hasn't explicitly set a preference
        if (!localStorage.getItem(STORAGE_KEY)) {
          applyTheme(e.matches ? "light" : "dark");
        }
      });
  }

  // Initialize on DOM ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initTheme);
  } else {
    initTheme();
  }
})();

/**
 * Console Selector Modal Controller
 *
 * Handles the console selector modal for switching between CCS, GDI, and Intel24.
 * Features:
 * - Modal open/close with animations
 * - Keyboard accessibility (ESC to close, Tab trapping)
 * - Focus management
 * - ARIA attributes
 */
(function () {
  "use strict";

  const modalBtn = document.getElementById("consoleSelectorBtn");
  const modal = document.getElementById("consoleSelectorModal");

  // Exit if elements don't exist
  if (!modalBtn || !modal) {
    return;
  }

  const closeElements = modal.querySelectorAll("[data-close-modal]");
  let previousFocus = null;

  /**
   * Opens the console selector modal
   */
  function openModal() {
    previousFocus = document.activeElement;

    modal.setAttribute("aria-hidden", "false");
    modalBtn.setAttribute("aria-expanded", "true");
    // P0 FIX: NO scroll-lock - user must be able to scroll freely
    // document.body.style.overflow = 'hidden'; // REMOVED - violates P0 scroll policy

    // Focus the close button
    requestAnimationFrame(() => {
      const closeBtn = modal.querySelector(".console-modal__close");
      if (closeBtn) {
        closeBtn.focus();
      }
    });
  }

  /**
   * Closes the console selector modal
   */
  function closeModal() {
    modal.setAttribute("aria-hidden", "true");
    modalBtn.setAttribute("aria-expanded", "false");
    document.body.style.overflow = "";

    // Return focus to trigger button
    if (previousFocus) {
      previousFocus.focus();
    }

    // Close any open slideouts
    if (window.bbxConsoleSelector) {
      window.bbxConsoleSelector.closeAllSlideouts();
    }
  }

  /**
   * Toggle modal state
   */
  function toggleModal() {
    const isOpen = modal.getAttribute("aria-hidden") === "false";
    if (isOpen) {
      closeModal();
    } else {
      openModal();
    }
  }

  // Event: Open modal button click
  modalBtn.addEventListener("click", function (e) {
    e.preventDefault();
    toggleModal();
  });

  // Event: Close button clicks
  closeElements.forEach(function (el) {
    el.addEventListener("click", function (e) {
      e.preventDefault();
      closeModal();
    });
  });

  // Event: Escape key to close
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && modal.getAttribute("aria-hidden") === "false") {
      closeModal();
    }
  });

  // Event: Click outside to close (on backdrop)
  modal.addEventListener("click", function (e) {
    if (
      e.target === modal ||
      e.target.classList.contains("console-modal__backdrop")
    ) {
      closeModal();
    }
  });

  // Focus trap within modal
  modal.addEventListener("keydown", function (e) {
    if (e.key !== "Tab") return;
    if (modal.getAttribute("aria-hidden") === "true") return;

    const focusableElements = modal.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    const firstEl = focusableElements[0];
    const lastEl = focusableElements[focusableElements.length - 1];

    if (e.shiftKey) {
      // Shift + Tab
      if (document.activeElement === firstEl) {
        e.preventDefault();
        lastEl.focus();
      }
    } else {
      // Tab
      if (document.activeElement === lastEl) {
        e.preventDefault();
        firstEl.focus();
      }
    }
  });
})();
