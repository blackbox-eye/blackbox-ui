export type GuardDecision = {
  action: 'allow' | 'redirect';
  target: string;
  reason: string;
  meta?: Record<string, unknown>;
};

export interface GuardConfig {
  loginRoute: string;
  fallbackRoute?: string;
  protectedRoutes: string[];
  ts24Entry: string;
  expectedIssuer: string;
  expectedAudience: string;
  qaMode?: boolean;
}

export interface BootstrapState {
  token?: string | null;
  tokenExpiresAt?: number | null;
  lastRedirect?: string | null;
  cookieFingerprint?: string | null;
}

type GuardMetrics = {
  doubleNavigate: number;
  desyncEvents: number;
  evaluations: number;
};

interface StoredToken {
  token: string;
  payload: TokenPayload | null;
  expiresAt: number;
  issuer?: string;
  audience?: string;
  fingerprint?: string | null;
}

interface TokenPayload {
  iss?: string;
  aud?: string;
  exp?: number;
  [claim: string]: unknown;
}

type StorageDriver = 'session' | 'local';

export class AlphaSSORouterGuard {
  private readonly storageKey = 'bbx.sso.token';
  private readonly fingerprintKey = 'bbx.sso.fingerprint';
  private readonly qaOverrideStorageKey = 'bbx.qa.override';
  private readonly fallbackTargets: string[];
  private lastDecision: GuardDecision | null = null;
  private tokenCache: StoredToken | null = null;
  private cookieFingerprint: string | null = null;
  private qaOverride = false;
  private verbose = false;
  private lastRedirectAt = 0;
  private readonly metrics: GuardMetrics = {
    doubleNavigate: 0,
    desyncEvents: 0,
    evaluations: 0
  };

  public readonly debug?: {
    injectToken: (token: string, options?: { expiresAt?: number; storage?: StorageDriver; fingerprint?: string }) => void;
    purgeTokens: () => void;
    snapshot: () => Record<string, unknown>;
    overrideCookieFingerprint: (fingerprint: string | null) => void;
    setQaOverride: (value: boolean) => void;
    metrics: GuardMetrics;
  };

  constructor(private readonly config: GuardConfig) {
    this.fallbackTargets = this.buildFallbackChain();

    if (config.qaMode) {
      this.debug = {
        injectToken: (token, options) => {
          const expiresAt = options?.expiresAt ?? Date.now() + 600_000;
          const payload = this.decodeToken(token);
          const stored: StoredToken = {
            token,
            payload,
            expiresAt,
            issuer: payload?.iss,
            audience: payload?.aud,
            fingerprint: options?.fingerprint ?? null
          };
          this.persistToken(stored, options?.storage ?? 'session');
          this.tokenCache = stored;
        },
        purgeTokens: () => this.clearStoredTokens(),
        snapshot: () => ({
          decision: this.lastDecision,
          tokenCache: this.tokenCache,
          cookieFingerprint: this.cookieFingerprint,
          qaOverride: this.qaOverride,
          metrics: { ...this.metrics }
        }),
        overrideCookieFingerprint: (fingerprint) => {
          this.cookieFingerprint = fingerprint;
        },
        setQaOverride: (value: boolean) => {
          this.qaOverride = value;
        },
        metrics: this.metrics
      };
      this.bootstrapQaOverride();
    }
  }

  public hydrate(state: BootstrapState = {}): void {
    if (typeof state.cookieFingerprint === 'string' && state.cookieFingerprint !== '') {
      this.cookieFingerprint = state.cookieFingerprint;
      window.sessionStorage.setItem(this.fingerprintKey, state.cookieFingerprint);
    }

    if (state.token) {
      const stored: StoredToken = {
        token: state.token,
        payload: this.decodeToken(state.token),
        expiresAt: state.tokenExpiresAt ?? Date.now() + 600_000,
        issuer: this.decodeToken(state.token)?.iss,
        audience: this.decodeToken(state.token)?.aud,
        fingerprint: this.cookieFingerprint
      };
      this.persistToken(stored, 'session');
      this.tokenCache = stored;
    }

    if (state.lastRedirect) {
      this.log('Hydrated last redirect from bootstrap', { target: state.lastRedirect });
    }
  }

  public enableVerboseLogging(): void {
    this.verbose = true;
  }

  public evaluateNavigation(targetPath: string): GuardDecision {
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

  public ensure(targetPath?: string): GuardDecision {
    const decision = this.evaluateNavigation(targetPath ?? window.location.pathname);
    if (decision.action === 'redirect') {
      const currentPath = window.location.pathname;
      if (currentPath === decision.target) {
        this.log('Suppressing redundant redirect', { reason: decision.reason, target: decision.target }, true);
        return decision;
      }
      this.log('Guard issued redirect', { reason: decision.reason, target: decision.target }, true);
      window.location.replace(decision.target);
    }
    return decision;
  }

  private storeDecision(decision: GuardDecision): GuardDecision {
    if (this.lastDecision && this.lastDecision.action === 'redirect' && decision.action === 'redirect' && this.lastDecision.reason === decision.reason) {
      this.metrics.doubleNavigate += 1;
    }
    this.lastDecision = decision;
    if (typeof window !== 'undefined' && typeof window.dispatchEvent === 'function') {
      try {
        window.dispatchEvent(new CustomEvent('bbx:router-decision', { detail: decision }));
      } catch (error) {
        this.log('Failed to dispatch router decision event', { error: (error as Error).message });
      }
    }
    return decision;
  }

  private redirectDecision(reason: string, meta?: Record<string, unknown>): GuardDecision {
    const target = this.resolveRedirectTarget();
    this.lastRedirectAt = Date.now();
    this.log('Redirect decision issued', { reason, target, ...(meta ?? {}) }, true);
    return this.storeDecision({
      action: 'redirect',
      target,
      reason,
      meta
    });
  }

  private getActiveToken(): StoredToken | null {
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

  private readStoredToken(): StoredToken | null {
    const raw = window.sessionStorage.getItem(this.storageKey) || window.localStorage.getItem(this.storageKey);
    if (!raw) {
      return null;
    }
    try {
      const parsed = JSON.parse(raw) as StoredToken;
      parsed.payload = this.decodeToken(parsed.token);
      return parsed;
    } catch (error) {
      this.log('Failed to parse stored token', { error: (error as Error).message });
      return null;
    }
  }

  private persistToken(token: StoredToken, driver: StorageDriver): void {
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
      this.log('Unable to persist token', { error: (error as Error).message });
    }
  }

  private clearStoredTokens(): void {
    window.sessionStorage.removeItem(this.storageKey);
    window.localStorage.removeItem(this.storageKey);
    window.sessionStorage.removeItem(this.fingerprintKey);
    this.tokenCache = null;
  }

  private detectCookieDesync(): boolean {
    const storedFingerprint = window.sessionStorage.getItem(this.fingerprintKey);
    if (!storedFingerprint || !this.cookieFingerprint) {
      return false;
    }
    return storedFingerprint !== this.cookieFingerprint;
  }

  private isProtectedPath(path: string): boolean {
    return this.config.protectedRoutes.some((route) => route === path || path.startsWith(route));
  }

  private isTokenFresh(exp?: number): boolean {
    if (!exp) {
      return false;
    }
    return exp * 1000 > Date.now();
  }

  private isIssuerValid(issuer?: string): boolean {
    return issuer === this.config.expectedIssuer;
  }

  private isAudienceValid(aud?: string): boolean {
    if (!aud) {
      return false;
    }
    const canonicalAud = this.config.expectedAudience.replace(/\/$/, '');
    return aud.replace(/\/$/, '') === canonicalAud;
  }

  private isValidAlgorithm(token: string): boolean {
    const [headerSegment] = token.split('.');
    if (!headerSegment) {
      return false;
    }
    try {
      const headerJson = this.decodeSegment(headerSegment);
      const header = JSON.parse(headerJson) as { alg?: string };
      return header.alg === 'HS256';
    } catch (error) {
      this.log('Invalid JWT header segment', { error: (error as Error).message });
      return false;
    }
  }

  private decodeToken(token: string): TokenPayload | null {
    const segments = token.split('.');
    if (segments.length !== 3) {
      return null;
    }
    try {
      const payloadJson = this.decodeSegment(segments[1]);
      return JSON.parse(payloadJson) as TokenPayload;
    } catch (error) {
      this.log('Unable to decode token payload', { error: (error as Error).message });
      return null;
    }
  }

  private decodeSegment(segment: string): string {
    const normalized = segment.replace(/-/g, '+').replace(/_/g, '/');
    const pad = normalized.length % 4;
    const padded = pad ? normalized + '='.repeat(4 - pad) : normalized;
    if (typeof window !== 'undefined' && typeof window.atob === 'function') {
      return window.atob(padded);
    }
    return Buffer.from(padded, 'base64').toString('utf8');
  }

  private log(message: string, details?: Record<string, unknown>, force = false): void {
    this.logInternal(message, details, force);
  }

  private logInternal(message: string, details?: Record<string, unknown>, force?: boolean): void {
    if (!force && !this.verbose && !this.config.qaMode) {
      return;
    }
    if (details) {
      console.info('[ALPHA-SSO]', message, details);
    } else {
      console.info('[ALPHA-SSO]', message);
    }
  }

  private buildFallbackChain(): string[] {
    const chain = [this.config.loginRoute, this.config.fallbackRoute, '/login'];
    return chain.filter((value, index, self) => typeof value === 'string' && value.length > 0 && self.indexOf(value) === index) as string[];
  }

  private resolveRedirectTarget(): string {
    if (typeof window === 'undefined') {
      return this.fallbackTargets[0] ?? '/login';
    }
    const currentPath = window.location.pathname;
    const target = this.fallbackTargets.find((route) => route !== currentPath);
    return target ?? '/login';
  }

  private bootstrapQaOverride(): void {
    if (typeof window === 'undefined') {
      return;
    }
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
      this.log('Unable to bootstrap QA override', { error: (error as Error).message });
    }
    this.qaOverride = overrideActive;
    if (overrideActive) {
      this.log('QA override enabled via bootstrap', undefined, true);
    }
  }

  private detectInlineQaOverride(): boolean {
    try {
      const params = new URLSearchParams(window.location.search);
      const flag = params.get('qaRouterOverride') ?? params.get('qaOverride') ?? params.get('qa');
      return flag === '1' || flag === 'true';
    } catch (error) {
      this.log('QA override query parse failed', { error: (error as Error).message });
      return false;
    }
  }
}

export function initializeRouterGuard(config: GuardConfig, state?: BootstrapState): AlphaSSORouterGuard {
  const guard = new AlphaSSORouterGuard(config);
  guard.hydrate(state);
  return guard;
}

declare global {
  interface Window {
    BBXRouterGuard?: AlphaSSORouterGuard;
    BBX_QA_MODE?: boolean;
    BBX_QA_STATE?: Record<string, unknown>;
  }
}
