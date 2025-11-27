<?php

/**
 * Request Access Component
 *
 * Complete access request workflow with:
 * - Full form fields (name, email, organization, role, reason)
 * - Honeypot field for bot detection
 * - reCAPTCHA v3 integration
 * - PHPMailer notification to security team
 *
 * Bruges på login-siden for brugere uden adgang
 */

// Get reCAPTCHA site key if available
$recaptchaSiteKey = defined('BBX_RECAPTCHA_SITE_KEY') ? BBX_RECAPTCHA_SITE_KEY : '';
?>
<section class="request-access" aria-labelledby="request-access-heading">
  <h2 id="request-access-heading" class="sr-only">Anmodning om adgang</h2>
  <p class="request-access__text">
    Har du brug for operatør-login?
    <strong>Anmod om adgang</strong> og modtag en tidsbegrænset sikker invitation.
  </p>
  <div class="request-access__actions">
    <button type="button"
      id="requestAccessInit"
      class="request-access__btn"
      aria-haspopup="dialog"
      aria-controls="requestAccessDialog">
      Anmod om adgang
    </button>
    <a href="mailto:ops@blackbox.codes?subject=GreyEYE%20Access%20Request"
      class="request-access__link">
      Eller kontakt GreyEYE sikkerhedsdesk direkte
    </a>
  </div>
  <p class="request-access__note">
    Alle forespørgsler verificeres manuelt. Autoriserede brugere modtager et krypteret link og multi-faktor onboarding.
  </p>
</section>

<!-- Request Access Modal -->
<div class="request-modal-overlay" id="requestAccessOverlay" role="presentation">
  <div class="request-modal"
    role="dialog"
    id="requestAccessDialog"
    aria-modal="true"
    aria-labelledby="requestModalTitle">

    <button type="button"
      class="request-modal__close"
      id="requestAccessClose"
      aria-label="Luk dialog">
      &times;
    </button>

    <h2 id="requestModalTitle" class="request-modal__title">
      Anmod om sikker adgang
    </h2>

    <p class="request-modal__description">
      Indsend dine kontaktoplysninger og operationelt scope.
      Vores sikkerhedsteam udsteder et unikt onboarding-link via krypteret e-mail (PGP/GPG) inden for 24-48 timer.
    </p>

    <form id="requestAccessForm" class="request-modal__form" action="/api/request-access.php" method="POST">
      <!-- Honeypot field - hidden from users, bots will fill it -->
      <div class="request-modal__honeypot" aria-hidden="true">
        <label for="website_url">Website URL</label>
        <input type="text"
          name="website_url"
          id="website_url"
          tabindex="-1"
          autocomplete="off">
      </div>

      <label class="request-modal__label">
        <span class="request-modal__label-text">
          Fulde navn <span class="request-modal__required" aria-hidden="true">*</span>
        </span>
        <input type="text"
          name="name"
          id="requestName"
          placeholder="Dit fulde navn"
          required
          autocomplete="name"
          class="request-modal__input">
      </label>

      <label class="request-modal__label">
        <span class="request-modal__label-text">
          Sikker e-mail <span class="request-modal__required" aria-hidden="true">*</span>
        </span>
        <input type="email"
          name="email"
          id="requestEmail"
          placeholder="navn@virksomhed.dk"
          required
          autocomplete="email"
          class="request-modal__input">
        <span class="request-modal__hint">Brug PGP-aktiveret eller virksomheds-email for sikker kommunikation</span>
      </label>

      <label class="request-modal__label">
        <span class="request-modal__label-text">
          Organisation <span class="request-modal__required" aria-hidden="true">*</span>
        </span>
        <input type="text"
          name="organization"
          id="requestOrganization"
          placeholder="Virksomhed eller organisation"
          required
          autocomplete="organization"
          class="request-modal__input">
      </label>

      <label class="request-modal__label">
        <span class="request-modal__label-text">Ønsket rolle</span>
        <select name="role" id="requestRole" class="request-modal__select">
          <option value="">Vælg rolle (valgfrit)</option>
          <option value="observer">Observer – kun læseadgang</option>
          <option value="operator">Operator – standard operationel adgang</option>
          <option value="analyst">Analytiker – avanceret dataanalyse</option>
          <option value="admin">Administrator – fuld systemadgang</option>
        </select>
        <span class="request-modal__hint">Din endelige rolle bestemmes under sikkerhedsgennemgang</span>
      </label>

      <label class="request-modal__label">
        <span class="request-modal__label-text">
          Begrundelse <span class="request-modal__required" aria-hidden="true">*</span>
        </span>
        <textarea name="reason"
          id="requestReason"
          placeholder="Beskriv hvorfor du har brug for adgang til GreyEYE™ portalen og dit operationelle scope"
          required
          rows="4"
          class="request-modal__textarea"></textarea>
      </label>

      <!-- reCAPTCHA token field -->
      <input type="hidden" name="recaptcha_token" id="requestRecaptchaToken">

      <div class="request-modal__actions">
        <button type="button"
          id="requestAccessCancel"
          class="request-modal__btn request-modal__btn--secondary">
          Annuller
        </button>
        <button type="submit"
          id="requestAccessSubmit"
          class="request-modal__btn request-modal__btn--primary">
          <span class="request-modal__btn-text">Send anmodning</span>
          <span class="request-modal__btn-loading" aria-hidden="true">
            <svg class="request-modal__spinner" viewBox="0 0 24 24" width="18" height="18">
              <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="31.4" stroke-linecap="round" />
            </svg>
            Sender...
          </span>
        </button>
      </div>

      <p class="request-modal__status"
        id="requestAccessStatus"
        role="status"
        aria-live="polite"></p>

      <p class="request-modal__privacy">
        Ved at indsende denne formular accepterer du, at dine data behandles i overensstemmelse med vores
        <a href="/privacy.php" target="_blank" rel="noopener">privatlivspolitik</a>.
      </p>
    </form>
  </div>
</div>

<?php if ($recaptchaSiteKey): ?>
  <script src="https://www.google.com/recaptcha/api.js?render=<?php echo htmlspecialchars($recaptchaSiteKey); ?>"></script>
<?php endif; ?>

<script>
  (function() {
    'use strict';

    const RECAPTCHA_SITE_KEY = '<?php echo htmlspecialchars($recaptchaSiteKey); ?>';

    // DOM Elements
    const overlay = document.getElementById('requestAccessOverlay');
    const dialog = document.getElementById('requestAccessDialog');
    const form = document.getElementById('requestAccessForm');
    const initBtn = document.getElementById('requestAccessInit');
    const closeBtn = document.getElementById('requestAccessClose');
    const cancelBtn = document.getElementById('requestAccessCancel');
    const submitBtn = document.getElementById('requestAccessSubmit');
    const statusEl = document.getElementById('requestAccessStatus');
    const tokenField = document.getElementById('requestRecaptchaToken');

    if (!overlay || !dialog || !form) return;

    // Track focusable elements for trap
    let focusableElements = [];
    let firstFocusable = null;
    let lastFocusable = null;

    function updateFocusableElements() {
      focusableElements = dialog.querySelectorAll(
        'button:not([disabled]), input:not([disabled]):not([tabindex="-1"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
      );
      firstFocusable = focusableElements[0];
      lastFocusable = focusableElements[focusableElements.length - 1];
    }

    function openModal() {
      overlay.classList.add('is-open');
      dialog.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      updateFocusableElements();

      // Focus first input
      const firstInput = form.querySelector('input:not([type="hidden"]):not([tabindex="-1"])');
      if (firstInput) {
        setTimeout(() => firstInput.focus(), 100);
      }
    }

    function closeModal() {
      overlay.classList.remove('is-open');
      dialog.classList.remove('is-open');
      document.body.style.overflow = '';
      form.reset();
      statusEl.textContent = '';
      statusEl.className = 'request-modal__status';
      submitBtn.disabled = false;
      submitBtn.classList.remove('is-loading');

      // Return focus to trigger button
      initBtn.focus();
    }

    function trapFocus(e) {
      if (e.key !== 'Tab') return;

      if (e.shiftKey) {
        if (document.activeElement === firstFocusable) {
          e.preventDefault();
          lastFocusable.focus();
        }
      } else {
        if (document.activeElement === lastFocusable) {
          e.preventDefault();
          firstFocusable.focus();
        }
      }
    }

    async function getRecaptchaToken() {
      if (!RECAPTCHA_SITE_KEY || typeof grecaptcha === 'undefined') {
        return '';
      }

      try {
        return await grecaptcha.execute(RECAPTCHA_SITE_KEY, {
          action: 'request_access'
        });
      } catch (err) {
        console.error('reCAPTCHA error:', err);
        return '';
      }
    }

    function showStatus(message, type = 'info') {
      statusEl.textContent = message;
      statusEl.className = 'request-modal__status request-modal__status--' + type;
    }

    async function handleSubmit(e) {
      e.preventDefault();

      // Disable submit and show loading state
      submitBtn.disabled = true;
      submitBtn.classList.add('is-loading');
      showStatus('Sender din anmodning...', 'info');

      // Get reCAPTCHA token
      const token = await getRecaptchaToken();
      tokenField.value = token;

      // Collect form data
      const formData = new FormData(form);

      try {
        const response = await fetch('/api/request-access.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          showStatus(result.message, 'success');

          // Close modal after delay
          setTimeout(() => {
            closeModal();
          }, 3000);
        } else {
          showStatus(result.message || 'Der opstod en fejl. Prøv igen.', 'error');
          submitBtn.disabled = false;
          submitBtn.classList.remove('is-loading');
        }
      } catch (err) {
        console.error('Submit error:', err);
        showStatus('Netværksfejl. Kontrollér din forbindelse og prøv igen.', 'error');
        submitBtn.disabled = false;
        submitBtn.classList.remove('is-loading');
      }
    }

    // Event listeners
    initBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    form.addEventListener('submit', handleSubmit);

    // Close on overlay click
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        closeModal();
      }
    });

    // Keyboard handling
    document.addEventListener('keydown', (e) => {
      if (!overlay.classList.contains('is-open')) return;

      if (e.key === 'Escape') {
        closeModal();
      } else {
        trapFocus(e);
      }
    });

  })();
</script>
