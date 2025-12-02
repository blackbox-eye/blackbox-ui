'use strict';

(function initQaMode() {
  if (typeof window === 'undefined') {
    return;
  }
  const QA_ACTIVE = Boolean(window.BBX_QA_MODE);
  if (!QA_ACTIVE) {
    return;
  }

  console.info('[QA-MODE] QA mode active – enabling diagnostics');

  const guard = window.BBXRouterGuard;
  if (guard && typeof guard.enableVerboseLogging === 'function') {
    guard.enableVerboseLogging();
    logRouterSnapshot('bootstrap');
  }

  const suppressError = (label, event) => {
    console.info(`[QA-MODE] Intercepted ${label}`, event?.message || event?.reason || 'unknown error');
    if (event) {
      event.preventDefault?.();
      event.stopImmediatePropagation?.();
      event.stopPropagation?.();
    }
    return true;
  };

  window.addEventListener('error', (event) => suppressError('window.error', event));
  window.addEventListener('unhandledrejection', (event) => suppressError('promise rejection', event));

  const panel = document.getElementById('qa-debug-panel');
  const qaState = window.BBX_QA_STATE || {};

  const formatTimestamp = (value) => {
    if (!value) {
      return 'N/A';
    }
    const timestamp = typeof value === 'string' ? parseInt(value, 10) : value;
    if (!Number.isFinite(timestamp)) {
      return 'N/A';
    }
    return new Date(timestamp).toISOString();
  };

  const startHealthMonitor = (endpoint, target) => {
    if (!endpoint || !target) {
      return;
    }
    const poll = async () => {
      try {
        const response = await fetch(endpoint, { credentials: 'include' });
        if (!response.ok) {
          target.textContent = `Unreachable (${response.status})`;
          return;
        }
        const payload = await response.json();
        const healthy = payload?.sso_enabled && payload?.jwt_mint_ok;
        const statusLabel = healthy ? 'Healthy' : 'Degraded';
        target.textContent = `${statusLabel} • ${new Date().toLocaleTimeString()}`;
      } catch (error) {
        target.textContent = `Error: ${error?.message ?? 'unknown'}`;
      }
    };
    poll();
    return window.setInterval(poll, 30000);
  };

  const formatCountdown = (expiresAt) => {
    if (!expiresAt) {
      return 'N/A';
    }
    const delta = Math.floor(expiresAt - Date.now());
    if (!Number.isFinite(delta)) {
      return 'N/A';
    }
    if (delta <= 0) {
      return 'Expired';
    }
    const totalSeconds = Math.floor(delta / 1000);
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    if (hours > 0) {
      return `${hours}h ${minutes}m ${seconds}s`;
    }
    if (minutes > 0) {
      return `${minutes}m ${seconds}s`;
    }
    return `${seconds}s`;
  };

  const startCountdown = (expiresAt, target) => {
    if (!target) {
      return;
    }
    const ts = typeof expiresAt === 'string' ? parseInt(expiresAt, 10) : expiresAt;
    if (!ts || !Number.isFinite(ts)) {
      target.textContent = 'N/A';
      return;
    }
    const update = () => {
      target.textContent = formatCountdown(ts);
    };
    update();
    return window.setInterval(update, 1000);
  };

  function logRouterSnapshot(label) {
    if (!guard || !guard.debug || typeof guard.debug.snapshot !== 'function') {
      return;
    }
    try {
      const snapshot = guard.debug.snapshot();
      console.info('[QA-MODE] Router snapshot', { label, snapshot });
    } catch (error) {
      console.info('[QA-MODE] Router snapshot failed', { error: error?.message });
    }
  }

  if (panel) {
    const tokenField = panel.querySelector('[data-qa-field="token"]');
    const expField = panel.querySelector('[data-qa-field="expires"]');
    const countdownField = panel.querySelector('[data-qa-field="expiresCountdown"]');
    const redirectField = panel.querySelector('[data-qa-field="redirect"]');
    const healthField = panel.querySelector('[data-qa-field="health"]');
    const forceRefreshBtn = panel.querySelector('[data-qa-action="force-refresh"]');
    const invalidateBtn = panel.querySelector('[data-qa-action="invalidate-cookie"]');

    if (tokenField) {
      const tokenValue = panel.dataset.qaToken || qaState.token || 'N/A';
      tokenField.textContent = tokenValue;
      if (tokenValue && tokenValue !== 'N/A') {
        console.info('[QA-MODE] Token trace captured', {
          length: tokenValue.length,
          preview: `${tokenValue.slice(0, 16)}…`
        });
      }
    }
    if (expField) {
      const exp = panel.dataset.qaExp || qaState.tokenExpiresAt;
      expField.textContent = formatTimestamp(exp);
      const expValue = typeof exp === 'string' ? parseInt(exp, 10) : exp;
      if (countdownField) {
        startCountdown(expValue, countdownField);
      }
    }
    if (redirectField) {
      redirectField.textContent = panel.dataset.qaRedirect || qaState.lastRedirect || 'N/A';
      window.addEventListener('bbx:sso-redirect', (event) => {
        redirectField.textContent = event?.detail?.href || 'Pending…';
        console.info('[QA-MODE] SSO redirect initiated', event?.detail || {});
      });
      window.addEventListener('bbx:router-decision', (event) => {
        if (event?.detail?.reason === 'cookie-desync') {
          redirectField.textContent = 'Blocked (cookie desync)';
        }
        if (event?.detail) {
          console.info('[QA-MODE] Router decision', event.detail);
          logRouterSnapshot('decision');
        }
      });
    }
    if (healthField) {
      startHealthMonitor(panel.dataset.qaHealth || qaState.healthEndpoint, healthField);
    }
    forceRefreshBtn?.addEventListener('click', () => {
      window.location.reload();
    });
    invalidateBtn?.addEventListener('click', () => {
      fetch('/logout.php?qaInvalidate=1', { credentials: 'include' })
        .catch(() => null)
        .finally(() => {
          window.location.assign('/agent-login.php');
        });
    });
  }

  const ts24Launchers = document.querySelectorAll('[data-console-launch="ts24"]');
  ts24Launchers.forEach((link) => {
    link.addEventListener('click', () => {
      if (typeof window.CustomEvent === 'function') {
        window.dispatchEvent(new CustomEvent('bbx:sso-redirect', {
          detail: {
            href: link.href,
            ts: Date.now()
          }
        }));
      }
      console.info('[QA-MODE] TS24 launcher clicked', { href: link.href });
    });
  });

  console.info('[QA-MODE] Visual QA panel initialised');
})();
