export interface QADebugPanelTelemetry {
  lastToken?: string;
  tokenExpiresAt?: number | null;
  lastRedirect?: string | null;
  cookieFingerprint?: string | null;
  healthEndpoint?: string;
}

export type QADebugPanelCallbacks = {
  onForceRefresh?: () => void;
  onInvalidateCookie?: () => void;
  onToggleQaOverride?: (value: boolean) => void;
};

/**
 * Lightweight view-model for the QA debug panel rendered server-side.
 * Keeps logic separated from PHP templates so we can reuse it in SPA builds later.
 */
export class QADebugPanel {
  constructor(private readonly telemetry: QADebugPanelTelemetry, private readonly callbacks: QADebugPanelCallbacks = {}) {}

  public render(): string {
    const segments = [
      `<div class="qa-debug-panel" data-component="qa-panel">`,
      `<div class="qa-debug-panel__header">`,
      `<strong>QA Debug Panel</strong>`,
      `<button type="button" data-qa-action="force-refresh">Force refresh</button>`,
      `<button type="button" data-qa-action="invalidate-cookie">Invalidate cookie</button>`,
      `</div>`,
      `<dl class="qa-debug-panel__grid">`,
      `<dt>Last token</dt><dd>${this.telemetry.lastToken ? this.telemetry.lastToken : 'n/a'}</dd>`,
      `<dt>Expires</dt><dd>${this.telemetry.tokenExpiresAt ? new Date(this.telemetry.tokenExpiresAt).toISOString() : 'n/a'}</dd>`,
      `<dt>Last redirect</dt><dd>${this.telemetry.lastRedirect || 'n/a'}</dd>`,
      `<dt>Cookie fingerprint</dt><dd>${this.telemetry.cookieFingerprint || 'n/a'}</dd>`,
      `<dt>Health endpoint</dt><dd>${this.telemetry.healthEndpoint || '/tools/sso_health.php'}</dd>`,
      `</dl>`,
      `</div>`
    ];
    return segments.join('');
  }
}
