<?php

/**
 * Blackbox EYE™ - Cookie Consent Banner
 *
 * GDPR/ePrivacy compliant cookie consent with:
 * - First-visit detection via localStorage
 * - Accept/Decline functionality
 * - Analytics category support
 * - Privacy policy link
 */

// Get current language for i18n
$banner_lang = function_exists('bbx_get_language') ? bbx_get_language() : 'da';
$privacy_url = 'privacy.php';

// Banner text translations
$banner_texts = [
  'da' => [
    'title' => 'Vi bruger cookies',
    'description' => 'Vi bruger cookies til at forbedre din oplevelse og analysere trafik. Ved at klikke "Acceptér" samtykker du til vores brug af cookies.',
    'accept' => 'Acceptér',
    'decline' => 'Kun nødvendige',
    'privacy_link' => 'Læs vores privatlivspolitik',
    'manage' => 'Administrer præferencer',
  ],
  'en' => [
    'title' => 'We use cookies',
    'description' => 'We use cookies to improve your experience and analyze traffic. By clicking "Accept" you consent to our use of cookies.',
    'accept' => 'Accept',
    'decline' => 'Essential only',
    'privacy_link' => 'Read our privacy policy',
    'manage' => 'Manage preferences',
  ],
];

$texts = $banner_texts[$banner_lang] ?? $banner_texts['en'];
?>

<!-- Cookie Consent Banner -->
<div id="cookie-banner"
  class="cookie-banner"
  role="dialog"
  aria-modal="true"
  aria-labelledby="cookie-banner-title"
  aria-describedby="cookie-banner-description"
  hidden>
  <div class="cookie-banner__content">
    <div class="cookie-banner__text">
      <h2 id="cookie-banner-title" class="cookie-banner__title"><?= htmlspecialchars($texts['title']) ?></h2>
      <p id="cookie-banner-description" class="cookie-banner__description">
        <?= htmlspecialchars($texts['description']) ?>
        <a href="<?= htmlspecialchars($privacy_url) ?>" class="cookie-banner__link"><?= htmlspecialchars($texts['privacy_link']) ?></a>
      </p>
    </div>
    <div class="cookie-banner__actions">
      <button type="button"
        id="cookie-accept-btn"
        class="cookie-banner__btn cookie-banner__btn--accept"
        data-consent="all">
        <?= htmlspecialchars($texts['accept']) ?>
      </button>
      <button type="button"
        id="cookie-decline-btn"
        class="cookie-banner__btn cookie-banner__btn--decline"
        data-consent="essential">
        <?= htmlspecialchars($texts['decline']) ?>
      </button>
    </div>
  </div>
</div>

<style>
  .cookie-banner {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 70;
    padding: 1rem;
    padding-bottom: calc(1rem + env(safe-area-inset-bottom));
    background: var(--surface-card-bg, rgba(17, 24, 39, 0.98));
    border-top: 1px solid var(--surface-border, rgba(255, 255, 255, 0.08));
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 -8px 32px rgba(0, 0, 0, 0.3);
    transform: translateY(100%);
    transition: transform 0.3s ease-out;
  }

  .cookie-banner[data-visible="true"] {
    transform: translateY(0);
  }

  .cookie-banner__content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  @media (min-width: 768px) {
    .cookie-banner__content {
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
    }
  }

  .cookie-banner__text {
    flex: 1;
  }

  .cookie-banner__title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-high-emphasis, #EAEAEA);
    margin: 0 0 0.5rem 0;
  }

  .cookie-banner__description {
    font-size: 0.875rem;
    color: var(--text-medium-emphasis, #B0B8C6);
    margin: 0;
    line-height: 1.5;
  }

  .cookie-banner__link {
    color: var(--primary-accent, #FFC700);
    text-decoration: underline;
    text-underline-offset: 2px;
  }

  .cookie-banner__link:hover {
    color: var(--cta-background-hover, #ffd445);
  }

  .cookie-banner__actions {
    display: flex;
    gap: 0.75rem;
    flex-shrink: 0;
  }

  .cookie-banner__btn {
    padding: 0.65rem 1.25rem;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
  }

  .cookie-banner__btn--accept {
    background: var(--cta-background, #F5C400);
    color: var(--cta-contrast, #020202);
  }

  .cookie-banner__btn--accept:hover {
    background: var(--cta-background-hover, #ffd445);
    transform: translateY(-1px);
  }

  .cookie-banner__btn--decline {
    background: transparent;
    color: var(--text-medium-emphasis, #B0B8C6);
    border: 1px solid var(--surface-border, rgba(255, 255, 255, 0.12));
  }

  .cookie-banner__btn--decline:hover {
    border-color: var(--primary-accent, #FFC700);
    color: var(--text-high-emphasis, #EAEAEA);
  }

  /* Light mode overrides */
  :root[data-theme="light"] .cookie-banner {
    background: rgba(255, 255, 255, 0.98);
    border-top-color: rgba(15, 23, 42, 0.1);
    box-shadow: 0 -8px 32px rgba(0, 0, 0, 0.1);
  }

  :root[data-theme="light"] .cookie-banner__title {
    color: #1f2937;
  }

  :root[data-theme="light"] .cookie-banner__description {
    color: #4b5563;
  }

  :root[data-theme="light"] .cookie-banner__btn--decline {
    color: #4b5563;
    border-color: rgba(15, 23, 42, 0.15);
  }

  :root[data-theme="light"] .cookie-banner__btn--decline:hover {
    color: #1f2937;
  }
</style>

<script>
  (function() {
    'use strict';

    var CONSENT_KEY = 'bbx_cookie_consent';
    var CONSENT_VERSION = '1';

    var banner = document.getElementById('cookie-banner');
    var acceptBtn = document.getElementById('cookie-accept-btn');
    var declineBtn = document.getElementById('cookie-decline-btn');

    if (!banner) return;

    // Check existing consent
    function getConsent() {
      try {
        var stored = localStorage.getItem(CONSENT_KEY);
        if (stored) {
          var parsed = JSON.parse(stored);
          if (parsed.version === CONSENT_VERSION) {
            return parsed;
          }
        }
      } catch (e) {}
      return null;
    }

    // Save consent
    function setConsent(level) {
      var consent = {
        version: CONSENT_VERSION,
        level: level,
        timestamp: new Date().toISOString(),
        categories: level === 'all' ? ['essential', 'analytics', 'marketing'] : ['essential']
      };
      try {
        localStorage.setItem(CONSENT_KEY, JSON.stringify(consent));
      } catch (e) {}

      // Fire custom event for analytics integration
      window.dispatchEvent(new CustomEvent('bbx:consent', {
        detail: consent
      }));

      // Log consent (via beacon to avoid blocking)
      if (navigator.sendBeacon) {
        navigator.sendBeacon('/api/consent-log.php', JSON.stringify({
          action: 'set',
          level: level
        }));
      }
    }

    // Show banner
    function showBanner() {
      banner.hidden = false;
      banner.setAttribute('data-visible', 'true');
      document.body.classList.add('cookie-banner-open');
      // Focus first button for accessibility
      setTimeout(function() {
        acceptBtn.focus();
      }, 100);
    }

    // Hide banner
    function hideBanner() {
      banner.setAttribute('data-visible', 'false');
      setTimeout(function() {
        banner.hidden = true;
      }, 300);
      document.body.classList.remove('cookie-banner-open');
    }

    // Handle accept
    acceptBtn.addEventListener('click', function() {
      setConsent('all');
      hideBanner();
    });

    // Handle decline
    declineBtn.addEventListener('click', function() {
      setConsent('essential');
      hideBanner();
    });

    // Check on load
    var existingConsent = getConsent();
    if (!existingConsent) {
      // Show banner after short delay for better UX
      setTimeout(showBanner, 1000);
    }

    // Expose for external use
    window.BBXCookieConsent = {
      getConsent: getConsent,
      hasAnalytics: function() {
        var c = getConsent();
        return c && c.categories && c.categories.indexOf('analytics') !== -1;
      },
      showBanner: showBanner
    };
  })();
</script>
