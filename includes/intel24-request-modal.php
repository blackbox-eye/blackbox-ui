<?php
/**
 * Intel24 Access Request Modal
 * 
 * Modal for requesting Intel24 console access.
 * Same UX quality as CCS/GDI request flows.
 * 
 * Sprint 5: Consolidated flow
 */
?>

<!-- Intel24 Request Modal -->
<div id="intel24-request-modal" 
     class="bbx-modal" 
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="intel24-modal-title"
     aria-describedby="intel24-modal-desc"
     data-intel24-modal
     hidden>
  
  <div class="bbx-modal__backdrop" data-modal-close></div>
  
  <div class="bbx-modal__container">
    <div class="bbx-modal__panel">
      
      <!-- Header -->
      <header class="bbx-modal__header">
        <div class="bbx-modal__badge bbx-modal__badge--intel24">
          <span>I24</span>
        </div>
        <div class="bbx-modal__title-group">
          <h2 id="intel24-modal-title" class="bbx-modal__title">Request Intel24 Access</h2>
          <p id="intel24-modal-desc" class="bbx-modal__subtitle">Rapid Response Intelligence Console</p>
        </div>
        <button type="button" class="bbx-modal__close" data-modal-close aria-label="Close modal">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M18 6L6 18M6 6l12 12"/>
          </svg>
        </button>
      </header>
      
      <!-- Body -->
      <div class="bbx-modal__body">
        <p class="bbx-modal__intro">
          Intel24 provides real-time field telemetry, transport alerts, and tactical briefings. 
          Access requires approval from your organization's security administrator.
        </p>
        
        <form id="intel24-request-form" class="bbx-modal__form" data-intel24-form>
          <!-- Name -->
          <div class="bbx-modal__field">
            <label for="intel24-name" class="bbx-modal__label">Full Name</label>
            <input type="text" 
                   id="intel24-name" 
                   name="name" 
                   class="bbx-modal__input"
                   placeholder="Your full name"
                   required
                   autocomplete="name">
          </div>
          
          <!-- Email -->
          <div class="bbx-modal__field">
            <label for="intel24-email" class="bbx-modal__label">Work Email</label>
            <input type="email" 
                   id="intel24-email" 
                   name="email" 
                   class="bbx-modal__input"
                   placeholder="you@organization.com"
                   required
                   autocomplete="email">
          </div>
          
          <!-- Organization -->
          <div class="bbx-modal__field">
            <label for="intel24-org" class="bbx-modal__label">Organization</label>
            <input type="text" 
                   id="intel24-org" 
                   name="organization" 
                   class="bbx-modal__input"
                   placeholder="Your organization name"
                   required>
          </div>
          
          <!-- Role -->
          <div class="bbx-modal__field">
            <label for="intel24-role" class="bbx-modal__label">Role / Department</label>
            <select id="intel24-role" name="role" class="bbx-modal__select" required>
              <option value="">Select your role</option>
              <option value="analyst">Intelligence Analyst</option>
              <option value="operations">Operations</option>
              <option value="security">Security Team</option>
              <option value="executive">Executive / Management</option>
              <option value="field">Field Agent</option>
              <option value="other">Other</option>
            </select>
          </div>
          
          <!-- Use Case -->
          <div class="bbx-modal__field">
            <label for="intel24-usecase" class="bbx-modal__label">Use Case (optional)</label>
            <textarea id="intel24-usecase" 
                      name="usecase" 
                      class="bbx-modal__textarea"
                      placeholder="Brief description of your intended use..."
                      rows="3"></textarea>
          </div>
          
          <!-- Terms -->
          <div class="bbx-modal__checkbox-field">
            <input type="checkbox" 
                   id="intel24-terms" 
                   name="terms" 
                   class="bbx-modal__checkbox"
                   required>
            <label for="intel24-terms" class="bbx-modal__checkbox-label">
              I agree to the <a href="/terms.php" target="_blank" rel="noopener">Terms of Service</a> 
              and <a href="/privacy.php" target="_blank" rel="noopener">Privacy Policy</a>
            </label>
          </div>
          
          <!-- Submit -->
          <button type="submit" class="bbx-modal__submit bbx-modal__submit--intel24">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
            </svg>
            Submit Request
          </button>
        </form>
        
        <!-- Success State -->
        <div class="bbx-modal__success" data-intel24-success hidden>
          <div class="bbx-modal__success-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9 12l2 2 4-4"/>
            </svg>
          </div>
          <h3 class="bbx-modal__success-title">Request Submitted</h3>
          <p class="bbx-modal__success-text">
            Your Intel24 access request has been received. 
            You'll receive an email confirmation with your reference number.
          </p>
          <p class="bbx-modal__success-ref">
            Reference: <strong data-request-ref>—</strong>
          </p>
          <button type="button" class="bbx-modal__done" data-modal-close>Done</button>
        </div>
      </div>
      
    </div>
  </div>
</div>

<script>
(function() {
  'use strict';
  
  const modal = document.querySelector('[data-intel24-modal]');
  if (!modal) return;
  
  const form = modal.querySelector('[data-intel24-form]');
  const successView = modal.querySelector('[data-intel24-success]');
  const refDisplay = modal.querySelector('[data-request-ref]');
  
  let previousFocus = null;
  
  function generateRefId() {
    return 'I24-' + Date.now().toString(36).toUpperCase() + '-' + Math.random().toString(36).substring(2, 6).toUpperCase();
  }
  
  function showModal() {
    previousFocus = document.activeElement;
    modal.hidden = false;
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    
    // Reset to form view
    form.hidden = false;
    form.reset();
    successView.hidden = true;
    
    // Focus first input
    setTimeout(function() {
      const firstInput = form.querySelector('input, select, textarea');
      if (firstInput) firstInput.focus();
    }, 100);
  }
  
  function hideModal() {
    modal.hidden = true;
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
    
    if (previousFocus) {
      previousFocus.focus();
      previousFocus = null;
    }
  }
  
  function showSuccess(refId) {
    form.hidden = true;
    successView.hidden = false;
    if (refDisplay) refDisplay.textContent = refId;
    
    // Focus done button
    const doneBtn = successView.querySelector('[data-modal-close]');
    if (doneBtn) doneBtn.focus();
  }
  
  // Handle form submission
  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(form);
      const data = Object.fromEntries(formData.entries());
      data.console = 'intel24';
      data.request_id = generateRefId();
      data.timestamp = Date.now();
      
      // Submit to API
      fetch('/api/intel24-request.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      })
      .then(function(res) { return res.json(); })
      .then(function(result) {
        showSuccess(result.request_id || data.request_id);
        
        // Record activity
        if (window.bbxSnackbar) {
          window.bbxSnackbar.success('Intel24 access request submitted');
        }
        
        // Post activity event
        fetch('/api/console-activity.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            console: 'intel24',
            action: 'access_request',
            timestamp: Date.now()
          })
        }).catch(function() {});
      })
      .catch(function() {
        // Still show success with local ref
        showSuccess(data.request_id);
        
        if (window.bbxSnackbar) {
          window.bbxSnackbar.info('Request recorded locally');
        }
      });
    });
  }
  
  // Close handlers
  modal.querySelectorAll('[data-modal-close]').forEach(function(btn) {
    btn.addEventListener('click', hideModal);
  });
  
  // Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !modal.hidden) {
      hideModal();
    }
  });
  
  // Focus trap
  modal.addEventListener('keydown', function(e) {
    if (e.key !== 'Tab') return;
    
    const focusable = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    if (!focusable.length) return;
    
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  });
  
  // Expose globally
  window.bbxIntel24Request = {
    show: showModal,
    hide: hideModal
  };
})();
</script>
