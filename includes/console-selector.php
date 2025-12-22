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
$intel24_requires_approval = !$intel24_has_sso;
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
  <div class="console-selector__quick" aria-label="Console quick switch">
    <label for="console-quick-switch" class="console-selector__quick-label">Quick switch</label>
    <select id="console-quick-switch" class="console-selector__quick-select" data-console-quick-switch>
      <option value="ccs">CCS</option>
      <option value="gdi">GDI</option>
      <option value="intel24">Intel24</option>
    </select>
  </div>
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
        <span class="console-card__pinned" aria-hidden="true" hidden>★ Pinned</span>
        <div class="console-card__status console-card__status--operational" aria-label="Status: Operational">
          <span class="console-card__status-dot" aria-hidden="true"></span>
          <span class="console-card__status-text">Operational</span>
        </div>
        <button type="button" 
                class="console-card__fav-btn bbx-icon-btn" 
                aria-label="Pin CCS to quick switch" 
                aria-pressed="false"
                data-favorite="ccs"
                data-tooltip="Pin this console for quick access"
                data-tooltip-pos="bottom">
          <svg class="bbx-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
          </svg>
        </button>
        <button type="button" 
                class="console-card__info-btn bbx-icon-btn" 
                aria-label="Show detailed information about CCS" 
                aria-expanded="false"
                data-slideout-target="ccs-slideout"
                data-tooltip="More information"
                data-tooltip-pos="bottom">
          <svg class="bbx-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><circle cx="12" cy="8" r="0.5" fill="currentColor"/>
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
          <div class="console-card__opsbar" role="status" aria-label="Operational status">
            <span class="console-card__opschip console-card__opschip--up">Operational</span>
            <span class="console-card__opschip console-card__opschip--audit">Audit Logged</span>
            <span class="console-card__opschip console-card__opschip--mfa">MFA Required</span>
          </div>
          <p><?= t('agent_access.cards.ccs.body_primary', 'Unified settlement rails for fiat + crypto.') ?></p>
          <p><?= t('agent_access.cards.ccs.body_secondary', 'Governed by House AU1 ⟠. Balanced by HΛW ⟠.') ?></p>
          <p class="console-card__slideout-note"><?= t('agent_access.cards.ccs.requirements', 'Requires CCS clearance + MFA.') ?></p>
          <div class="console-card__readiness" aria-label="Session readiness">
            <div class="console-card__readiness-row">
              <span class="console-card__readiness-label">Session status</span>
              <span class="console-card__readiness-value" data-readiness="session"><?= isset($_SESSION['agent_id']) ? 'Active' : 'Sign-in required' ?></span>
            </div>
            <div class="console-card__readiness-row">
              <span class="console-card__readiness-label">Network</span>
              <span class="console-card__readiness-value" data-readiness="network">Secure tunnel ready</span>
            </div>
            <div class="console-card__readiness-row">
              <span class="console-card__readiness-label">MFA</span>
              <span class="console-card__readiness-value" data-readiness="mfa"><?= isset($_SESSION['mfa_verified']) ? 'Verified' : 'Required on access' ?></span>
            </div>
          </div>
          
          <!-- Mini sparkline chart (7-day trend) -->
          <div class="console-card__minichart" aria-label="Activity chart: 7-day transaction volume">
            <span class="console-card__minichart-label">7-day volume</span>
            <svg viewBox="0 0 100 30" class="console-card__sparkline" aria-hidden="true">
              <polyline fill="none" stroke="currentColor" stroke-width="2" points="0,25 15,20 30,22 45,15 60,18 75,10 90,12 100,8"/>
              <circle cx="100" cy="8" r="3" fill="currentColor"/>
            </svg>
          </div>
          
          <!-- Additional metrics -->
          <div class="console-card__metrics-grid">
            <div class="console-card__metric">
              <span class="console-card__metric-value" data-metric="avg-settlement-time">1.8s</span>
              <span class="console-card__metric-label">Avg settlement</span>
            </div>
            <div class="console-card__metric">
              <span class="console-card__metric-value" data-metric="fiat-crypto-ratio">62/38</span>
              <span class="console-card__metric-label">Fiat/Crypto %</span>
            </div>
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
        <span class="console-card__pinned" aria-hidden="true" hidden>★ Pinned</span>
        <div class="console-card__status console-card__status--available" aria-label="Status: Available">
          <span class="console-card__status-dot" aria-hidden="true"></span>
          <span class="console-card__status-text">Available</span>
        </div>
        <button type="button" 
                class="console-card__fav-btn bbx-icon-btn" 
                aria-label="Pin GDI to quick switch" 
                aria-pressed="false"
                data-favorite="gdi"
                data-tooltip="Pin this console for quick access"
                data-tooltip-pos="bottom">
          <svg class="bbx-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
          </svg>
        </button>
        <button type="button" 
                class="console-card__info-btn bbx-icon-btn" 
                aria-label="Show detailed information about GDI" 
                aria-expanded="false"
                data-slideout-target="gdi-slideout"
                data-tooltip="More information"
                data-tooltip-pos="bottom">
          <svg class="bbx-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><circle cx="12" cy="8" r="0.5" fill="currentColor"/>
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
        <span class="console-card__pinned" aria-hidden="true" hidden>★ Pinned</span>
        <div class="console-card__status console-card__status--operational" aria-label="Status: Operational">
          <span class="console-card__status-dot" aria-hidden="true"></span>
          <span class="console-card__status-text">Operational</span>
        </div>
        <button type="button" 
                class="console-card__fav-btn bbx-icon-btn" 
                aria-label="Pin Intel24 to quick switch" 
                aria-pressed="false"
                data-favorite="intel24"
                data-tooltip="Pin this console for quick access"
                data-tooltip-pos="bottom">
          <svg class="bbx-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
          </svg>
        </button>
        <button type="button" 
                class="console-card__info-btn bbx-icon-btn" 
                aria-label="Show detailed information about Intel24" 
                aria-expanded="false"
                data-slideout-target="intel24-slideout"
                data-tooltip="More information"
                data-tooltip-pos="bottom">
          <svg class="bbx-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><circle cx="12" cy="8" r="0.5" fill="currentColor"/>
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
        <a <?= $intel24_requires_approval ? 'href="#" aria-disabled="true" tabindex="-1"' : 'href="' . htmlspecialchars($intel24_console_url) . '"' ?>
           class="console-card__cta console-card__cta--intel24<?= $intel24_requires_approval ? ' is-disabled' : '' ?>"
           data-console-launch="intel24"
           <?= $intel24_requires_approval ? '' : 'target="_blank" rel="noopener"' ?>
           <?= $intel24_has_sso ? 'data-sso-active="true"' : '' ?>
           aria-label="<?= $intel24_requires_approval ? 'Intel24 requires SSO token' : 'Open Intel24 Intelligence Console' ?>">
          <?= $intel24_requires_approval ? 'Requires SSO token' : t('agent_access.cards.ts24.cta', 'Open Intel24 console') ?>
        </a>
        <?php if ($intel24_requires_approval): ?>
          <p class="console-card__cta-note">Requires clearance / SSO token</p>
          <button type="button" 
                  class="console-card__request-link" 
                  data-intel24-request="true"
                  data-testid="intel24-request-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0110 0v4"/>
            </svg>
            Request Intel24 access
          </button>
        <?php endif; ?>
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
  
  <!-- Recent Activity Section (Sprint 3: server-side API) -->
  <section class="console-selector__activity" aria-labelledby="recent-activity-heading" data-recent-activity>
    <h4 id="recent-activity-heading" class="console-selector__activity-title">
      <svg class="bbx-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
      </svg>
      Recent Activity
      <span class="console-selector__activity-badge" data-activity-source title="Activity source">local</span>
    </h4>
    <ul class="console-selector__activity-list" role="list" data-activity-list aria-live="polite">
      <!-- Populated by JS from server API / localStorage fallback -->
    </ul>
    <p class="console-selector__activity-empty" hidden>No recent activity</p>
  </section>
</div>

<!-- Include SSO Request Modal -->
<?php include __DIR__ . '/sso-request-modal.php'; ?>

<!-- Include Intel24 Request Modal -->
<?php include __DIR__ . '/intel24-request-modal.php'; ?>

<script>
(function() {
  'use strict';
  
  // Initialize console selector functionality
  function initConsoleSelector() {
    const selector = document.querySelector('[data-console-selector]');
    if (!selector) return;
    const quickSwitch = selector.querySelector('[data-console-quick-switch]');
    const cardGrid = selector.querySelector('.console-selector__grid');
    const DEFAULT_ORDER = ['ccs', 'gdi', 'intel24'];
    let activeSlideout = null;
    let previousFocus = null;
    
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
        btn.setAttribute('aria-label', (isFav ? 'Unpin ' : 'Pin ') + consoleName.toUpperCase() + (isFav ? ' from' : ' to') + ' quick switch');
        btn.setAttribute('data-tooltip', isFav ? 'Unpin from quick switch' : 'Pin this console for quick access');

        const card = selector.querySelector('[data-console="' + consoleName + '"]');
        const pinned = card ? card.querySelector('.console-card__pinned') : null;
        if (card) {
          card.classList.toggle('console-card--pinned', isFav);
        }
        if (pinned) {
          pinned.hidden = !isFav;
        }
      });

      buildQuickSwitch();
    }

    function buildQuickSwitch() {
      if (!quickSwitch) return;
      const favorites = getFavorites();
      const order = [...favorites.filter(function(id) { return DEFAULT_ORDER.includes(id); }), ...DEFAULT_ORDER.filter(function(id) { return !favorites.includes(id); })];
      quickSwitch.innerHTML = '';
      order.forEach(function(id) {
        const option = document.createElement('option');
        option.value = id;
        option.textContent = (favorites.includes(id) ? '★ ' : '') + id.toUpperCase();
        quickSwitch.appendChild(option);
      });

      reorderCards(order);
    }

    function reorderCards(order) {
      if (!cardGrid) return;
      order.forEach(function(id) {
        const card = selector.querySelector('[data-console="' + id + '"]');
        if (card) cardGrid.appendChild(card);
      });
    }
    
    selector.querySelectorAll('.console-card__fav-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const consoleName = this.getAttribute('data-favorite');
        let favorites = getFavorites();
        const wasAdded = !favorites.includes(consoleName);
        
        if (favorites.includes(consoleName)) {
          favorites = favorites.filter(function(f) { return f !== consoleName; });
        } else {
          favorites.push(consoleName);
        }
        
        saveFavorites(favorites);
        updateFavoriteUI();
        
        // Snackbar feedback
        if (window.bbxSnackbar) {
          const label = consoleName.toUpperCase();
          if (wasAdded) {
            window.bbxSnackbar.success(label + ' pinned to your quick switch');
          } else {
            window.bbxSnackbar.info(label + ' unpinned from quick switch');
          }
        }
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
      if (previousFocus) {
        previousFocus.focus();
        previousFocus = null;
      }
      activeSlideout = null;
    }

    function openSlideout(panel, trigger) {
      closeAllSlideouts();
      panel.setAttribute('aria-hidden', 'false');
      panel.classList.add('is-open');
      if (trigger) trigger.setAttribute('aria-expanded', 'true');
      activeSlideout = panel;
      previousFocus = document.activeElement;
      const focusable = panel.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
      if (focusable.length) {
        focusable[0].focus();
      }
    }
    
    selector.querySelectorAll('.console-card__info-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const targetId = this.getAttribute('data-slideout-target');
        const panel = document.getElementById(targetId);
        if (!panel) return;
        
        const isOpen = panel.classList.contains('is-open');
        if (isOpen) {
          closeAllSlideouts();
        } else {
          openSlideout(panel, this);
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

    document.addEventListener('keydown', function(e) {
      if (!activeSlideout || e.key !== 'Tab') return;
      const focusable = activeSlideout.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
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
    
    // ===== CONSOLE CTA CLICK FEEDBACK =====
    selector.querySelectorAll('.console-card__cta').forEach(function(cta) {
      cta.addEventListener('click', function(e) {
        const card = this.closest('.console-card');
        const consoleName = card ? card.getAttribute('data-console') : 'console';
        
        if (window.bbxSnackbar) {
          window.bbxSnackbar.info('Redirecting to ' + consoleName.toUpperCase() + '...');
        }
      });
    });

    if (quickSwitch) {
      quickSwitch.addEventListener('change', function() {
        highlightCard(this.value);
      });
    }

    // ===== RECENT ACTIVITY SERVER API (Sprint 3) =====
    const ACTIVITY_KEY = 'bbx_console_activity';
    const activityList = selector.querySelector('[data-activity-list]');
    const activityEmpty = selector.querySelector('.console-selector__activity-empty');
    const activitySourceBadge = selector.querySelector('[data-activity-source]');

    function formatRelativeTime(timestamp) {
      const now = Date.now();
      const diff = now - timestamp;
      const seconds = Math.floor(diff / 1000);
      const minutes = Math.floor(seconds / 60);
      const hours = Math.floor(minutes / 60);
      const days = Math.floor(hours / 24);
      const weeks = Math.floor(days / 7);

      if (seconds < 60) return 'Just now';
      if (minutes < 60) return minutes + ' minute' + (minutes > 1 ? 's' : '') + ' ago';
      if (hours < 24) return hours + ' hour' + (hours > 1 ? 's' : '') + ' ago';
      if (days === 1) return 'Yesterday';
      if (days < 7) return days + ' day' + (days > 1 ? 's' : '') + ' ago';
      if (weeks === 1) return '1 week ago';
      return weeks + ' weeks ago';
    }

    function getActivity() {
      try {
        return JSON.parse(localStorage.getItem(ACTIVITY_KEY)) || generateDummyActivity();
      } catch (e) {
        return generateDummyActivity();
      }
    }

    function saveActivity(activity) {
      try {
        localStorage.setItem(ACTIVITY_KEY, JSON.stringify(activity));
      } catch (e) {
        console.warn('Could not save activity');
      }
    }

    function generateDummyActivity() {
      // Simulate realistic activity data (ready for real backend)
      const now = Date.now();
      return [
        { console: 'gdi', timestamp: now - (5 * 60 * 60 * 1000), action: 'login' },
        { console: 'ccs', timestamp: now - (2 * 24 * 60 * 60 * 1000), action: 'login' },
        { console: 'intel24', timestamp: now - (7 * 24 * 60 * 60 * 1000), action: 'login' }
      ];
    }

    function renderActivity(activity, source) {
      if (!activityList) return;
      
      // Update source badge
      if (activitySourceBadge) {
        activitySourceBadge.textContent = source || 'local';
        activitySourceBadge.title = source === 'server' ? 'Activity from server' : 'Activity from local storage';
      }
      
      if (!activity || !activity.length) {
        activityList.innerHTML = '';
        if (activityEmpty) activityEmpty.hidden = false;
        return;
      }
      
      if (activityEmpty) activityEmpty.hidden = true;

      // Sort by most recent first
      activity.sort(function(a, b) { return b.timestamp - a.timestamp; });

      activityList.innerHTML = activity.slice(0, 5).map(function(item) {
        var consoleName = item.console || 'system';
        var actionLabel = item.action === 'mfa_success' ? 'Authenticated to' : 
                          item.action === 'sso_request' ? 'SSO requested for' : 'Last used';
        return '<li class="console-selector__activity-item" role="listitem">' +
          '<span class="console-selector__activity-dot console-selector__activity-dot--' + consoleName + '" aria-hidden="true"></span>' +
          '<span class="console-selector__activity-text">' + actionLabel + ' <strong>' + consoleName.toUpperCase() + '</strong></span>' +
          '<time class="console-selector__activity-time" datetime="' + new Date(item.timestamp).toISOString() + '">' + formatRelativeTime(item.timestamp) + '</time>' +
        '</li>';
      }).join('');
    }

    // Fetch activity from server API with localStorage fallback
    function fetchActivity() {
      fetch('/api/console-activity.php')
        .then(function(res) { return res.json(); })
        .then(function(data) {
          if (data.events && data.events.length) {
            renderActivity(data.events, 'server');
            // Cache to localStorage
            try {
              localStorage.setItem(ACTIVITY_KEY, JSON.stringify(data.events));
            } catch (e) {}
          } else {
            // No server events, fall back to local
            renderActivity(getActivity(), 'local');
          }
        })
        .catch(function() {
          // Server unavailable, use localStorage
          renderActivity(getActivity(), 'local');
        });
    }

    function recordActivity(consoleName, action) {
      var event = { console: consoleName, timestamp: Date.now(), action: action || 'login' };
      
      // Post to server
      fetch('/api/console-activity.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(event),
      }).then(function() {
        fetchActivity(); // Refresh from server
      }).catch(function() {
        // Fallback to localStorage
        var activity = getActivity();
        var existing = activity.find(function(a) { return a.console === consoleName; });
        if (existing) {
          existing.timestamp = Date.now();
          existing.action = action || 'login';
        } else {
          activity.unshift(event);
        }
        saveActivity(activity);
        renderActivity(activity, 'local');
      });
    }

    // SSO Request modal triggers
    selector.querySelectorAll('[data-sso-request]').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        var consoleName = this.getAttribute('data-sso-request') || 'ccs';
        if (window.bbxSsoRequest) {
          window.bbxSsoRequest.show({
            console: consoleName,
            onSuccess: function(result) {
              if (window.bbxSnackbar) {
                window.bbxSnackbar.success('SSO request received. Ref: ' + result.request_id);
              }
              fetchActivity(); // Refresh activity list
            }
          });
        }
      });
    });

    // Intel24 Request modal triggers
    selector.querySelectorAll('[data-intel24-request]').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        if (window.bbxIntel24Request) {
          window.bbxIntel24Request.show();
        }
      });
    });

    // Record activity on CTA click
    selector.querySelectorAll('.console-card__cta').forEach(function(cta) {
      cta.addEventListener('click', function() {
        const card = this.closest('.console-card');
        const consoleName = card ? card.getAttribute('data-console') : null;
        if (consoleName) {
          recordActivity(consoleName, 'login');
        }
      });
    });

    // Initialize activity from server (with fallback)
    fetchActivity();
    
    // ===== SMOOTH SCROLL + HIGHLIGHT =====
    function getCardTarget(cardId) {
      return document.getElementById(cardId) || document.getElementById('modal-' + cardId);
    }

    function highlightCard(cardId) {
      const card = getCardTarget(cardId);
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
