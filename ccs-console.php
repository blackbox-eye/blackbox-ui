<?php
/**
 * CCS Console Page
 * 
 * Cross-Currency Settlement operational console overview.
 * Sprint 3: Added ops bar, authentication gate, and design language alignment.
 */

session_start();
require_once __DIR__ . '/includes/env.php';
require_once __DIR__ . '/includes/i18n.php';

$current_page = 'ccs-console';
$page_title = t('agent_access.cards.ccs.title');
$meta_description = t('agent_access.cards.ccs.body_primary');
$meta_og_title = $page_title;
$meta_og_description = $meta_description;

// Check authentication (mock for Sprint 3)
$is_authenticated = isset($_SESSION['ccs_authenticated']) && $_SESSION['ccs_authenticated'] === true;
$require_login = !$is_authenticated && !isset($_GET['demo']);

include 'includes/site-header.php';
?>

<!-- CCS Console Styles -->
<link rel="stylesheet" href="/assets/css/components/bbx-snackbar.css">
<style>
/* Ops Bar */
.ccs-ops-bar {
  position: sticky;
  top: 0;
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 0.75rem 1rem;
  background: rgba(10, 14, 20, 0.95);
  border-bottom: 1px solid rgba(46, 204, 113, 0.15);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.ccs-ops-bar__chip {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.75rem;
  border-radius: 2rem;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.ccs-ops-bar__chip--operational {
  background: rgba(46, 204, 113, 0.15);
  border: 1px solid rgba(46, 204, 113, 0.3);
  color: #7cf0ba;
}

.ccs-ops-bar__chip--audit {
  background: rgba(59, 130, 246, 0.15);
  border: 1px solid rgba(59, 130, 246, 0.3);
  color: #93c5fd;
}

.ccs-ops-bar__chip--mfa {
  background: rgba(249, 115, 22, 0.15);
  border: 1px solid rgba(249, 115, 22, 0.3);
  color: #fdba74;
}

.ccs-ops-bar__chip--locked {
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #fca5a5;
}

.ccs-ops-bar__dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
  animation: ccs-pulse 2s ease-in-out infinite;
}

@keyframes ccs-pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

/* Auth Gate */
.ccs-auth-gate {
  min-height: 60vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 2rem;
}

.ccs-auth-gate__icon {
  width: 5rem;
  height: 5rem;
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: rgba(46, 204, 113, 0.1);
  border: 1px solid rgba(46, 204, 113, 0.2);
  border-radius: 1rem;
  color: #2ecc71;
}

.ccs-auth-gate__icon svg {
  width: 100%;
  height: 100%;
}

.ccs-auth-gate__title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #fff;
  margin: 0 0 0.75rem;
}

.ccs-auth-gate__text {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.7);
  margin: 0 0 1.5rem;
  max-width: 400px;
}

.ccs-auth-gate__btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.75rem;
  background: linear-gradient(135deg, #2ecc71, #27ae60);
  border: none;
  border-radius: 0.625rem;
  color: #0a0e14;
  font-size: 0.95rem;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.2s ease;
}

.ccs-auth-gate__btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(46, 204, 113, 0.3);
}

.ccs-auth-gate__demo {
  margin-top: 1rem;
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.5);
}

.ccs-auth-gate__demo a {
  color: #2ecc71;
  text-decoration: none;
}

.ccs-auth-gate__demo a:hover {
  text-decoration: underline;
}
</style>

<!-- Ops Bar -->
<div class="ccs-ops-bar" role="status" aria-label="CCS operational status" data-testid="ccs-ops-bar">
  <span class="ccs-ops-bar__chip ccs-ops-bar__chip--operational">
    <span class="ccs-ops-bar__dot" aria-hidden="true"></span>
    Operational
  </span>
  <span class="ccs-ops-bar__chip ccs-ops-bar__chip--audit">
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
      <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/>
    </svg>
    Audit Logged
  </span>
  <span class="ccs-ops-bar__chip ccs-ops-bar__chip--mfa">
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
    </svg>
    MFA Required
  </span>
  <?php if (!$is_authenticated): ?>
  <span class="ccs-ops-bar__chip ccs-ops-bar__chip--locked">
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
    </svg>
    Not Authenticated
  </span>
  <?php endif; ?>
</div>

<?php if ($require_login): ?>
<!-- Authentication Gate -->
<main id="main-content" class="ccs-auth-gate">
  <div class="ccs-auth-gate__icon">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
      <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
      <circle cx="12" cy="16" r="1"/>
    </svg>
  </div>
  <h1 class="ccs-auth-gate__title">Authentication Required</h1>
  <p class="ccs-auth-gate__text">
    CCS Settlement Console requires MFA authentication. Please sign in to continue.
  </p>
  <a href="ccs-login.php" class="ccs-auth-gate__btn" data-testid="ccs-login-btn">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
      <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
      <polyline points="10 17 15 12 10 7"/>
      <line x1="15" y1="12" x2="3" y2="12"/>
    </svg>
    Sign in to CCS
  </a>
  <p class="ccs-auth-gate__demo">
    Or <a href="?demo=1">view demo mode</a> (read-only)
  </p>
</main>
<?php else: ?>
<main id="main-content" class="graphene-page">
  <section class="graphene-section py-16 md:py-24">
    <div class="container mx-auto px-4">
      <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
        <div class="space-y-6">
          <p class="text-xs uppercase tracking-[0.25em] text-[var(--bbx-gold-light)] font-semibold">CCS Platform</p>
          <h1 class="text-3xl md:text-4xl font-semibold text-white leading-tight">
            <?= t('agent_access.cards.ccs.title') ?>
          </h1>
          <p class="text-lg text-gray-200 max-w-2xl">
            <?= t('agent_access.cards.ccs.body_primary') ?>
          </p>
          <p class="text-base text-gray-300 max-w-2xl">
            <?= t('agent_access.cards.ccs.body_secondary') ?>
          </p>
          <div class="flex flex-wrap gap-3 pt-2">
            <span class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-emerald-400/30 bg-emerald-400/10 text-emerald-100 text-sm font-semibold">Verified supply chain</span>
            <span class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-amber-300/30 bg-amber-300/10 text-amber-50 text-sm font-semibold">Human-in-the-loop</span>
            <span class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-sky-300/30 bg-sky-300/10 text-sky-50 text-sm font-semibold">Real-time intelligence</span>
          </div>
          <div class="flex flex-wrap gap-3">
            <a href="contact.php" class="bbx-btn-pill">
              <?= t('header.cta.book_demo', 'Book demo') ?>
            </a>
            <a href="agent-access.php" class="bbx-btn-pill bbx-btn-pill--ghost">
              <?= t('agent_access.cards.ccs.cta') ?>
            </a>
          </div>
        </div>
        <div class="relative">
          <div class="rounded-3xl border border-emerald-400/20 bg-black/50 backdrop-blur-xl shadow-2xl p-6 lg:p-8">
            <div class="flex items-center justify-between mb-4">
              <div>
                <p class="text-sm text-emerald-100 font-semibold mb-1">Secure overview</p>
                <p class="text-xs text-emerald-200/70">Consular supply corridors</p>
              </div>
              <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-400/15 text-emerald-100 text-xs font-semibold">Live</span>
            </div>
            <div class="space-y-3 text-sm text-gray-200">
              <div class="flex items-center justify-between p-3 rounded-xl border border-white/5 bg-white/5">
                <div>
                  <p class="font-semibold">Critical assets</p>
                  <p class="text-xs text-gray-400">Tracked and verified</p>
                </div>
                <span class="text-xl font-bold text-emerald-200">128</span>
              </div>
              <div class="flex items-center justify-between p-3 rounded-xl border border-white/5 bg-white/5">
                <div>
                  <p class="font-semibold">Active corridors</p>
                  <p class="text-xs text-gray-400">Low-latency telemetry</p>
                </div>
                <span class="text-xl font-bold text-emerald-200">42</span>
              </div>
              <div class="flex items-center justify-between p-3 rounded-xl border border-white/5 bg-white/5">
                <div>
                  <p class="font-semibold">Response time</p>
                  <p class="text-xs text-gray-400">Human validation SLA</p>
                </div>
                <span class="text-xl font-bold text-emerald-200">&lt; 4m</span>
              </div>
            </div>
            <div class="mt-5 p-3 rounded-xl border border-emerald-300/30 bg-emerald-300/10 text-emerald-50 text-sm">
              <?= t('agent_access.cards.ccs.requirements') ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
<?php endif; ?>

<script src="/assets/js/bbx-snackbar.js"></script>
<script>
// Show auth status on page load
(function() {
  var isDemo = <?= isset($_GET['demo']) ? 'true' : 'false' ?>;
  if (isDemo && window.bbxSnackbar) {
    window.bbxSnackbar.info('Demo mode — read-only access');
  }
})();
</script>

<?php include 'includes/site-footer.php'; ?>
