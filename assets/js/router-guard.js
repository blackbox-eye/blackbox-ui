'use strict';

(function bootstrapRouterGuard() {
  if (typeof window === 'undefined') {
    return;
  }
  if (window.BBXRouterGuard) {
    return;
  }

  const DEFAULT_CONFIG = {
    loginRoute: '/agent-login.php',
    fallbackRoute: '/agent-login.php',
    protectedRoutes: ['/dashboard.php', '/admin.php', '/settings.php', '/agent-access.php', '/api-keys.php', '/intel-vault.php', '/download-logs.php', '/access-requests.php'],
    ts24Entry: 'https://intel24.blackbox.codes/sso-login',
    expectedIssuer: window.location.origin,
    expectedAudience: 'https://intel24.blackbox.codes/sso-login',
    qaMode: false
  };

  const config = Object.assign({}, DEFAULT_CONFIG, window.BBX_ROUTER_CONFIG || {});
  const bootstrapState = window.BBX_QA_STATE || {};

  class RouterGuard {
    constructor(cfg) {
      this.config = cfg;
      this.storageKey = 'bbx.sso.token';
      this.fingerprintKey = 'bbx.sso.fingerprint';
      this.qaOverrideStorageKey = 'bbx.qa.override';
      this.fallbackTargets = this.buildFallbackChain();
      this.lastDecision = null;
      this.tokenCache = null;
      this.cookieFingerprint = null;
      this.qaOverride = false;
      this.verbose = false;
      this.lastRedirectAt = 0;
      this.metrics = {
        doubleNavigate: 0,
        desyncEvents: 0,
        evaluations: 0
      };

      if (cfg.qaMode) {
        this.debug = {
          injectToken: (token, options) => {
            const expiresAt = (options && options.expiresAt) || Date.now() + 600000;
            const stored = {
              token,
              payload: this.decodeToken(token),
              expiresAt,
              issuer: this.decodeToken(token)?.iss,
              audience: this.decodeToken(token)?.aud,
              fingerprint: (options && options.fingerprint) || null
            };
            this.persistToken(stored, (options && options.storage) || 'session');
            this.tokenCache = stored;
          },
          purgeTokens: () => this.clearStoredTokens(),
          snapshot: () => ({
            decision: this.lastDecision,
            tokenCache: this.tokenCache,
            cookieFingerprint: this.cookieFingerprint,
            qaOverride: this.qaOverride,
            metrics: Object.assign({}, this.metrics)
          }),
          overrideCookieFingerprint: (fingerprint) => {
            this.cookieFingerprint = fingerprint;
          },
          setQaOverride: (value) => {
            this.qaOverride = Boolean(value);
          },
          metrics: this.metrics
        };
        this.bootstrapQaOverride();
      }
    }

    hydrate(state) {
      if (state && typeof state.cookieFingerprint === 'string' && state.cookieFingerprint !== '') {
        this.cookieFingerprint = state.cookieFingerprint;
        try {
          window.sessionStorage.setItem(this.fingerprintKey, state.cookieFingerprint);
        } catch (error) {
          this.log('Unable to persist fingerprint', { error: error?.message });
        }
      }

      if (state && state.token) {
        const payload = this.decodeToken(state.token);
        const stored = {
          token: state.token,
          payload,
          expiresAt: state.tokenExpiresAt || Date.now() + 600000,
          issuer: payload?.iss,
          audience: payload?.aud,
          fingerprint: this.cookieFingerprint
        };
        this.persistToken(stored, 'session');
        this.tokenCache = stored;
      }
    }

    enableVerboseLogging() {
      this.verbose = true;
    }

    evaluateNavigation(targetPath) {
      this.metrics.evaluations += 1;
      if (!this.isProtectedPath(targetPath)) {
        return this.storeDecision({ action: 'allow', target: targetPath, reason: 'public' });
      }

      if (this.config.qaMode && this.qaOverride) {
        this.log('QA override active, allowing navigation', { path: targetPath }, true);
        return this.storeDecision({ action: 'allow', target: targetPath, reason: 'qa-override' });
      }

      const token = this.getActiveToken();
      if (!token) {
        return this.redirectDecision('missing', { path: targetPath });
      }

      if (this.detectCookieDesync()) {
        this.metrics.desyncEvents += 1;
        return this.redirectDecision('cookie-desync', { path: targetPath });
      }

      const payload = token.payload;
      if (!payload) {
        return this.redirectDecision('malformed', { path: targetPath });
      }

      if (!this.isValidAlgorithm(token.token)) {
        return this.redirectDecision('algorithm', { path: targetPath });
      }

      if (!this.isTokenFresh(payload.exp)) {
        return this.redirectDecision('expired', { path: targetPath, exp: payload.exp });
      }

      if (!this.isIssuerValid(payload.iss)) {
        return this.redirectDecision('issuer', { path: targetPath, issuer: payload.iss });
      }

      if (!this.isAudienceValid(payload.aud)) {
        return this.redirectDecision('audience', { path: targetPath, audience: payload.aud });
      }

      return this.storeDecision({
        action: 'allow',
        target: targetPath,
        reason: 'ok',
        meta: {
          exp: payload.exp,
          iss: payload.iss,
          aud: payload.aud
        }
      });
    }

    ensure(targetPath) {
      const decision = this.evaluateNavigation(targetPath || window.location.pathname);
      if (decision.action === 'redirect') {
        const currentPath = window.location.pathname;
        if (currentPath === decision.target) {
          this.log('Suppressing redundant redirect', { reason: decision.reason, target: decision.target }, true);
          return decision;
        }
        this.log('Guard redirect', { reason: decision.reason, target: decision.target }, true);
        window.location.replace(decision.target);
      }
      return decision;
    }

    storeDecision(decision) {
      if (
        this.lastDecision &&
        this.lastDecision.action === 'redirect' &&
        decision.action === 'redirect' &&
        this.lastDecision.reason === decision.reason
      ) {
        this.metrics.doubleNavigate += 1;
      }
      this.lastDecision = decision;
      try {
        if (typeof window.CustomEvent === 'function') {
          window.dispatchEvent(new CustomEvent('bbx:router-decision', { detail: decision }));
        }
      } catch (error) {
        this.log('Decision event dispatch failed', { error: error?.message });
      }
      return decision;
    }

    redirectDecision(reason, meta) {
      const target = this.resolveRedirectTarget();
      this.lastRedirectAt = Date.now();
      this.log('Redirect decision issued', Object.assign({ reason, target }, meta || {}), true);
      return this.storeDecision({ action: 'redirect', target, reason, meta });
    }

    getActiveToken() {
      if (this.tokenCache && this.tokenCache.expiresAt > Date.now()) {
        return this.tokenCache;
      }
      const stored = this.readStoredToken();
      if (!stored) {
        return null;
      }
      if (stored.expiresAt <= Date.now()) {
        this.clearStoredTokens();
        return null;
      }
      this.tokenCache = stored;
      return stored;
    }

    readStoredToken() {
      let raw = null;
      try {
        raw = window.sessionStorage.getItem(this.storageKey) || window.localStorage.getItem(this.storageKey);
      } catch (error) {
        this.log('Storage unavailable', { error: error?.message });
      }
      if (!raw) {
        return null;
      }
      try {
        const parsed = JSON.parse(raw);
        parsed.payload = this.decodeToken(parsed.token);
        return parsed;
      } catch (error) {
        this.log('Failed to parse stored token', { error: error?.message });
        return null;
      }
    }

    persistToken(token, driver) {
      try {
        const payload = JSON.stringify(token);
        if (driver === 'local') {
          window.localStorage.setItem(this.storageKey, payload);
        } else {
          window.sessionStorage.setItem(this.storageKey, payload);
        }
        if (token.fingerprint) {
          window.sessionStorage.setItem(this.fingerprintKey, token.fingerprint);
        }
      } catch (error) {
        this.log('Unable to persist token', { error: error?.message });
      }
    }

    clearStoredTokens() {
      try {
        window.sessionStorage.removeItem(this.storageKey);
        window.localStorage.removeItem(this.storageKey);
        window.sessionStorage.removeItem(this.fingerprintKey);
      } catch (error) {
        this.log('Unable to clear storage', { error: error?.message });
      }
      this.tokenCache = null;
    }

    detectCookieDesync() {
      let storedFingerprint = null;
      try {
        storedFingerprint = window.sessionStorage.getItem(this.fingerprintKey);
      } catch (error) {
        this.log('Fingerprint read failed', { error: error?.message });
      }
      if (!storedFingerprint || !this.cookieFingerprint) {
        return false;
      }
      return storedFingerprint !== this.cookieFingerprint;
    }

    isProtectedPath(path) {
      return this.config.protectedRoutes.some((route) => route === path || path.startsWith(route));
    }

    isTokenFresh(exp) {
      if (!exp) {
        return false;
      }
      return exp * 1000 > Date.now();
    }

    isIssuerValid(issuer) {
      return issuer === this.config.expectedIssuer;
    }

    isAudienceValid(audience) {
      if (!audience) {
        return false;
      }
      const expected = (this.config.expectedAudience || '').replace(/\/$/, '');
      return audience.replace(/\/$/, '') === expected;
    }

    isValidAlgorithm(token) {
      const parts = token.split('.');
      if (parts.length < 1) {
        return false;
      }
      try {
        const header = JSON.parse(this.decodeSegment(parts[0]));
        return header.alg === 'HS256';
      } catch (error) {
        this.log('JWT header parse failure', { error: error?.message });
        return false;
      }
    }

    decodeToken(token) {
      const parts = token.split('.');
      if (parts.length !== 3) {
        return null;
      }
      try {
        const payloadJson = this.decodeSegment(parts[1]);
        return JSON.parse(payloadJson);
      } catch (error) {
        this.log('Payload decode failed', { error: error?.message });
        return null;
      }
    }

    decodeSegment(segment) {
      const normalized = segment.replace(/-/g, '+').replace(/_/g, '/');
      const pad = normalized.length % 4;
      const padded = pad ? normalized + '='.repeat(4 - pad) : normalized;
      try {
        return window.atob(padded);
      } catch (error) {
        this.log('Base64 decode failed', { error: error?.message });
        return '';
      }
    }

    log(message, details, force) {
      if (!force && !this.verbose && !this.config.qaMode) {
        return;
      }
      if (details) {
        console.info('[ALPHA-SSO]', message, details);
      } else {
        console.info('[ALPHA-SSO]', message);
      }
    }

    buildFallbackChain() {
      const chain = [this.config.loginRoute, this.config.fallbackRoute, '/login'];
      return chain.filter((value, index, self) => typeof value === 'string' && value.length > 0 && self.indexOf(value) === index);
    }

    resolveRedirectTarget() {
      const currentPath = window.location.pathname;
      const target = this.fallbackTargets.find((route) => route !== currentPath);
      return target || '/login';
    }

    bootstrapQaOverride() {
      let overrideActive = false;
      try {
        const stored = window.sessionStorage.getItem(this.qaOverrideStorageKey);
        if (stored === '1') {
          overrideActive = true;
        } else if (this.detectInlineQaOverride()) {
          window.sessionStorage.setItem(this.qaOverrideStorageKey, '1');
          overrideActive = true;
        }
      } catch (error) {
        this.log('Unable to bootstrap QA override', { error: error?.message });
      }
      this.qaOverride = overrideActive;
      if (overrideActive) {
        this.log('QA override enabled via bootstrap', null, true);
      }
    }

    detectInlineQaOverride() {
      try {
        const params = new URLSearchParams(window.location.search);
        const flag = params.get('qaRouterOverride') || params.get('qaOverride') || params.get('qa');
        return flag === '1' || flag === 'true';
      } catch (error) {
        this.log('QA override query parse failed', { error: error?.message });
        return false;
      }
    }
  }

  const guard = new RouterGuard(config);
  try {
    guard.hydrate(bootstrapState);
  } catch (error) {
    console.error('[ALPHA-SSO] Failed to hydrate guard', error);
  }

  if (config.qaMode) {
    guard.enableVerboseLogging();
  }

  window.BBXRouterGuard = guard;

  document.addEventListener('DOMContentLoaded', () => {
    guard.ensure(window.location.pathname);
  });
})();
