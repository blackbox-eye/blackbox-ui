<?php
/**
 * Console Selector Component
 * 
 * Reusable console selector with three cards (CCS, GDI, Intel24).
 * Can be embedded in agent-access.php or admin dashboard modal.
 * 
 * Variables (set before include):
 * - $console_context: 'page' (full page) or 'modal' (embedded modal)
 * - $ccs_console_url: URL for CCS console
 * - $gdi_console_url: URL for GDI console
 * - $intel24_console_url: URL for Intel24 console
 * - $intel24_has_sso: Boolean if SSO is available
 */

$console_context = $console_context ?? 'page';
$is_modal = $console_context === 'modal';

// Default URLs if not set
$ccs_console_url = $ccs_console_url ?? 'ccs-login.php';
$gdi_console_url = $gdi_console_url ?? 'agent-login.php';
$intel24_console_url = $intel24_console_url ?? 'https://intel24.blackbox.codes/login';
$intel24_has_sso = $intel24_has_sso ?? false;
?>

<!-- Hexagon pattern background (shared SVG) -->
<svg class="console-selector__pattern-defs" aria-hidden="true" style="position:absolute;width:0;height:0;">
  <defs>
    <pattern id="hexPattern" width="56" height="100" patternUnits="userSpaceOnUse" patternTransform="scale(0.5)">
      <path d="M28 66L0 50V16L28 0l28 16v34L28 66zm0-50a9 9 0 100 18 9 9 0 000-18z" fill="currentColor" fill-opacity="0.03"/>
    </pattern>
  </defs>
</svg>

<div class="console-selector<?= $is_modal ? ' console-selector--modal' : '' ?>" data-console-selector>
  <div class="console-selector__grid">
    
    <!-- CCS Card -->
    <article id="<?= $is_modal ? 'modal-' : '' ?>ccs" class="console-card console-card--ccs" data-console="ccs">
      <div class="console-card__pattern" aria-hidden="true">
        <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#hexPattern)"/></svg>
      </div>
      
      <div class="console-card__header">
        <div class="console-card__badge console-card__badge--ccs" aria-label="CCS Console Badge">
          <span>CCS</span>
        </div>
        <div class="console-card__status console-card__status--operational" aria-label="Status: Operational">
          <span class="console-card__status-dot" aria-hidden="true"></span>
          <span class="console-card__status-text">Operational</span>
        </div>
        <button type="button" 
                class="console-card__fav-btn" 
                aria-label="Add CCS to favorites" 
                aria-pressed="false"
                data-favorite="ccs">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
          </svg>
        </button>
        <button type="button" 
                class="console-card__info-btn" 
                aria-label="Show detailed information about CCS" 
                aria-expanded="false"
                data-slideout-target="ccs-slideout">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
          </svg>
        </button>
      </div>
      
      <div class="console-card__body">
        <h3 class="console-card__title"><?= t('agent_access.cards.ccs.title', 'CCS Settlement Console') ?></h3>
        <p class="console-card__tagline"><?= t('agent_access.cards.ccs.subtitle', 'Cross-Currency Settlement Systems') ?></p>
        
        <span class="console-card__req-badge console-card__req-badge--audit" aria-label="Audit Logged">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8M16 17H8M10 9H8"/>
          </svg>
          Audit Logged
        </span>
        
        <div class="console-card__divider" aria-hidden="true"></div>
        
        <div class="console-card__chips" role="list" aria-label="CCS features">
          <span class="console-card__chip" role="listitem">Multi-Asset</span>
          <span class="console-card__chip" role="listitem">Settlement</span>
          <span class="console-card__chip" role="listitem">MFA Required</span>
        </div>
      </div>
      
      <div class="console-card__actions">
        <a href="<?= htmlspecialchars($ccs_console_url) ?>"
           class="console-card__cta console-card__cta--ccs"
           data-console-launch="ccs"
           aria-label="Open CCS Settlement Console">
          <?= t('agent_access.cards.ccs.cta', 'Open CCS console') ?>
        </a>
      </div>
      
      <!-- Slide-out info panel -->
      <div id="ccs-slideout" class="console-card__slideout" role="dialog" aria-label="CCS Details" aria-hidden="true">
        <div class="console-card__slideout-header">
          <h4>CCS Settlement Console</h4>
          <button type="button" class="console-card__slideout-close" aria-label="Close details panel">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div class="console-card__slideout-body">
          <p><?= t('agent_access.cards.ccs.body_primary', 'Unified settlement rails for fiat + crypto.') ?></p>
          <p><?= t('agent_access.cards.ccs.body_secondary', 'Governed by House AU1 ⟠. Balanced by HΛW ⟠.') ?></p>
          <p class="console-card__slideout-note"><?= t('agent_access.cards.ccs.requirements', 'Requires CCS clearance + MFA.') ?></p>
          
          <!-- Mini sparkline chart placeholder -->
          <div class="console-card__minichart" aria-label="Activity chart: 7-day transaction volume">
            <span class="console-card__minichart-label">7-day volume</span>
            <svg viewBox="0 0 100 30" class="console-card__sparkline" aria-hidden="true">
              <polyline fill="none" stroke="currentColor" stroke-width="2" points="0,25 15,20 30,22 45,15 60,18 75,10 90,12 100,8"/>
              <circle cx="100" cy="8" r="3" fill="currentColor"/>
            </svg>
          </div>
        </div>
      </div>
    </article>
    
    <!-- GDI Card -->
    <article id="<?= $is_modal ? 'modal-' : '' ?>gdi" class="console-card console-card--gdi" data-console="gdi">
      <div class="console-card__pattern" aria-hidden="true">
        <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#hexPattern)"/></svg>
      </div>
      
      <div class="console-card__header">
        <div class="console-card__badge console-card__badge--gdi" aria-label="GDI Console Badge">
          <span>GDI</span>
        </div>
        <div class="console-card__status console-card__status--available" aria-label="Status: Available">
          <span class="console-card__status-dot" aria-hidden="true"></span>
          <span class="console-card__status-text">Available</span>
        </div>
        <button type="button" 
                class="console-card__fav-btn" 
                aria-label="Add GDI to favorites" 
                aria-pressed="false"
                data-favorite="gdi">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
          </svg>
        </button>
        <button type="button" 
                class="console-card__info-btn" 
                aria-label="Show detailed information about GDI" 
                aria-expanded="false"
                data-slideout-target="gdi-slideout">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
          </svg>
        </button>
      </div>
      
      <div class="console-card__body">
        <h3 class="console-card__title"><?= t('agent_access.cards.gdi.title', 'Blackbox EYE Data Intelligence (GDI)') ?></h3>
        <p class="console-card__tagline">Data Intelligence Platform</p>
        
        <span class="console-card__req-badge console-card__req-badge--vpn" aria-label="VPN Required">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
          </svg>
          VPN Required
        </span>
        
        <div class="console-card__divider" aria-hidden="true"></div>
        
        <div class="console-card__chips" role="list" aria-label="GDI features">
          <span class="console-card__chip" role="listitem">Investigations</span>
          <span class="console-card__chip" role="listitem">Alert Triage</span>
          <span class="console-card__chip" role="listitem">Biometric Badge</span>
        </div>
      </div>
      
      <div class="console-card__actions">
        <a href="<?= htmlspecialchars($gdi_console_url) ?>"
           class="console-card__cta console-card__cta--gdi"
           data-console-launch="gdi"
           aria-label="Open GDI Data Intelligence Console">
          <?= t('agent_access.cards.gdi.cta', 'Open GDI console') ?>
        </a>
      </div>
      
      <div id="gdi-slideout" class="console-card__slideout" role="dialog" aria-label="GDI Details" aria-hidden="true">
        <div class="console-card__slideout-header">
          <h4>GDI Data Intelligence</h4>
          <button type="button" class="console-card__slideout-close" aria-label="Close details panel">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div class="console-card__slideout-body">
          <p><?= t('agent_access.cards.gdi.description', 'Use for internal investigations, alert triage and direct control of Blackbox EYE sensors.') ?></p>
          <p class="console-card__slideout-note"><?= t('agent_access.cards.gdi.meta', 'Requires Blackbox VPN + biometric badge') ?></p>
          
          <div class="console-card__minichart" aria-label="Activity chart: Active investigations">
            <span class="console-card__minichart-label">Active cases</span>
            <svg viewBox="0 0 100 30" class="console-card__sparkline" aria-hidden="true">
              <polyline fill="none" stroke="currentColor" stroke-width="2" points="0,20 15,18 30,22 45,12 60,15 75,8 90,10 100,5"/>
              <circle cx="100" cy="5" r="3" fill="currentColor"/>
            </svg>
          </div>
        </div>
      </div>
    </article>
    
    <!-- Intel24 Card -->
    <article id="<?= $is_modal ? 'modal-' : '' ?>intel24" class="console-card console-card--intel24" data-console="intel24">
      <div class="console-card__pattern" aria-hidden="true">
        <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#hexPattern)"/></svg>
      </div>
      
      <div class="console-card__header">
        <div class="console-card__badge console-card__badge--intel24" aria-label="Intel24 Console Badge">
          <span>I24</span>
        </div>
        <div class="console-card__status console-card__status--operational" aria-label="Status: Operational">
          <span class="console-card__status-dot" aria-hidden="true"></span>
          <span class="console-card__status-text">Operational</span>
        </div>
        <button type="button" 
                class="console-card__fav-btn" 
                aria-label="Add Intel24 to favorites" 
                aria-pressed="false"
                data-favorite="intel24">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
          </svg>
        </button>
        <button type="button" 
                class="console-card__info-btn" 
                aria-label="Show detailed information about Intel24" 
                aria-expanded="false"
                data-slideout-target="intel24-slideout">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
          </svg>
        </button>
      </div>
      
      <div class="console-card__body">
        <h3 class="console-card__title"><?= t('agent_access.cards.ts24.title', 'Intel24 Intelligence Console') ?></h3>
        <p class="console-card__tagline">Rapid Response Intelligence</p>
        
        <span class="console-card__req-badge console-card__req-badge--sso<?= $intel24_has_sso ? ' is-active' : '' ?>" aria-label="SSO <?= $intel24_has_sso ? 'Ready' : 'Available' ?>">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
          SSO <?= $intel24_has_sso ? 'Ready' : 'Available' ?>
        </span>
        
        <div class="console-card__divider" aria-hidden="true"></div>
        
        <div class="console-card__chips" role="list" aria-label="Intel24 features">
          <span class="console-card__chip" role="listitem">Telemetry</span>
          <span class="console-card__chip" role="listitem">Transport Alerts</span>
          <span class="console-card__chip" role="listitem">Tactical Briefs</span>
        </div>
      </div>
      
      <div class="console-card__actions">
        <a href="<?= htmlspecialchars($intel24_console_url) ?>"
           class="console-card__cta console-card__cta--intel24"
           data-console-launch="intel24"
           target="_blank"
           rel="noopener"
           <?= $intel24_has_sso ? 'data-sso-active="true"' : '' ?>
           aria-label="Open Intel24 Intelligence Console<?= $intel24_has_sso ? ' with SSO' : '' ?>">
          <?= t('agent_access.cards.ts24.cta', 'Open Intel24 console') ?>
        </a>
      </div>
      
      <div id="intel24-slideout" class="console-card__slideout" role="dialog" aria-label="Intel24 Details" aria-hidden="true">
        <div class="console-card__slideout-header">
          <h4>Intel24 Intelligence</h4>
          <button type="button" class="console-card__slideout-close" aria-label="Close details panel">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div class="console-card__slideout-body">
          <p><?= t('agent_access.cards.ts24.description', 'Field telemetry, Intel24 transport alerts and tactical briefings.') ?></p>
          <p><?= t('agent_access.cards.ts24.sso_notice', 'SSO token attached automatically after GDI login.') ?></p>
          <p class="console-card__slideout-note"><?= t('agent_access.cards.ts24.meta', 'Opens secure intel24.blackbox.codes session') ?></p>
          
          <div class="console-card__minichart" aria-label="Activity chart: Alert response time">
            <span class="console-card__minichart-label">Response time</span>
            <svg viewBox="0 0 100 30" class="console-card__sparkline" aria-hidden="true">
              <polyline fill="none" stroke="currentColor" stroke-width="2" points="0,15 15,12 30,18 45,10 60,14 75,6 90,8 100,4"/>
              <circle cx="100" cy="4" r="3" fill="currentColor"/>
            </svg>
          </div>
        </div>
      </div>
    </article>
    
  </div>
  
  <!-- Recent Activity Section -->
  <section class="console-selector__activity" aria-labelledby="recent-activity-heading">
    <h4 id="recent-activity-heading" class="console-selector__activity-title">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
      </svg>
      Recent Activity
    </h4>
    <ul class="console-selector__activity-list" role="list">
      <li class="console-selector__activity-item" role="listitem">
        <span class="console-selector__activity-dot console-selector__activity-dot--ccs" aria-hidden="true"></span>
        <span class="console-selector__activity-text">Last used <strong>CCS</strong></span>
        <span class="console-selector__activity-time">2d ago</span>
      </li>
      <li class="console-selector__activity-item" role="listitem">
        <span class="console-selector__activity-dot console-selector__activity-dot--gdi" aria-hidden="true"></span>
        <span class="console-selector__activity-text">Last used <strong>GDI</strong></span>
        <span class="console-selector__activity-time">5h ago</span>
      </li>
      <li class="console-selector__activity-item" role="listitem">
        <span class="console-selector__activity-dot console-selector__activity-dot--intel24" aria-hidden="true"></span>
        <span class="console-selector__activity-text">Last used <strong>Intel24</strong></span>
        <span class="console-selector__activity-time">1w ago</span>
      </li>
    </ul>
  </section>
</div>

<script>
(function() {
  'use strict';
  
  // Initialize console selector functionality
  function initConsoleSelector() {
    const selector = document.querySelector('[data-console-selector]');
    if (!selector) return;
    
    // ===== FAVORITES (localStorage) =====
    const FAVORITES_KEY = 'bbx_console_favorites';
    
    function getFavorites() {
      try {
        return JSON.parse(localStorage.getItem(FAVORITES_KEY)) || [];
      } catch (e) {
        return [];
      }
    }
    
    function saveFavorites(favorites) {
      try {
        localStorage.setItem(FAVORITES_KEY, JSON.stringify(favorites));
      } catch (e) {
        console.warn('Could not save favorites');
      }
    }
    
    function updateFavoriteUI() {
      const favorites = getFavorites();
      selector.querySelectorAll('.console-card__fav-btn').forEach(function(btn) {
        const consoleName = btn.getAttribute('data-favorite');
        const isFav = favorites.includes(consoleName);
        btn.setAttribute('aria-pressed', isFav ? 'true' : 'false');
        btn.classList.toggle('is-favorite', isFav);
        btn.setAttribute('aria-label', (isFav ? 'Remove ' : 'Add ') + consoleName.toUpperCase() + (isFav ? ' from' : ' to') + ' favorites');
      });
    }
    
    selector.querySelectorAll('.console-card__fav-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const consoleName = this.getAttribute('data-favorite');
        let favorites = getFavorites();
        
        if (favorites.includes(consoleName)) {
          favorites = favorites.filter(function(f) { return f !== consoleName; });
        } else {
          favorites.push(consoleName);
        }
        
        saveFavorites(favorites);
        updateFavoriteUI();
      });
    });
    
    // Initialize favorites UI
    updateFavoriteUI();
    
    // ===== SLIDE-OUT PANELS =====
    function closeAllSlideouts() {
      selector.querySelectorAll('.console-card__slideout').forEach(function(panel) {
        panel.setAttribute('aria-hidden', 'true');
        panel.classList.remove('is-open');
      });
      selector.querySelectorAll('.console-card__info-btn').forEach(function(btn) {
        btn.setAttribute('aria-expanded', 'false');
      });
    }
    
    selector.querySelectorAll('.console-card__info-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const targetId = this.getAttribute('data-slideout-target');
        const panel = document.getElementById(targetId);
        if (!panel) return;
        
        const isOpen = panel.classList.contains('is-open');
        closeAllSlideouts();
        
        if (!isOpen) {
          panel.setAttribute('aria-hidden', 'false');
          panel.classList.add('is-open');
          this.setAttribute('aria-expanded', 'true');
        }
      });
    });
    
    // Close buttons inside slideouts
    selector.querySelectorAll('.console-card__slideout-close').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        closeAllSlideouts();
      });
    });
    
    // Close on outside click
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.console-card__slideout') && !e.target.closest('.console-card__info-btn')) {
        closeAllSlideouts();
      }
    });
    
    // Close on Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeAllSlideouts();
      }
    });
    
    // ===== SMOOTH SCROLL + HIGHLIGHT =====
    function highlightCard(cardId) {
      const card = document.getElementById(cardId);
      if (!card) return;
      
      card.scrollIntoView({ behavior: 'smooth', block: 'center' });
      card.classList.add('console-card--highlight');
      
      setTimeout(function() {
        card.classList.remove('console-card--highlight');
      }, 2000);
    }
    
    // Handle hash on load
    if (window.location.hash) {
      const targetId = window.location.hash.substring(1);
      setTimeout(function() {
        highlightCard(targetId);
      }, 150);
    }
    
    // Expose for external use (e.g., from admin modal)
    window.bbxConsoleSelector = {
      highlightCard: highlightCard,
      closeAllSlideouts: closeAllSlideouts
    };
  }
  
  // Initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initConsoleSelector);
  } else {
    initConsoleSelector();
  }
})();
</script>
