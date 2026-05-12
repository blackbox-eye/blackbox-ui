# BLACKBOX UI Technical Audit Checkpoint A-E v1

## 1. Scope and mode

This document captures the locked outcomes from completed read-only audit phases A-E for `blackbox-eye/blackbox-ui` before Phase F begins.

- Mode: READ-ONLY DOCUMENTATION ONLY
- Scope: repo/file architecture, PHP route/security surface, CSS cascade/responsive architecture, JavaScript runtime/build pipeline, tests/CI/deploy
- Constraints: for completed audit phases A-E and this checkpoint documentation task only, no application code changes, no branch creation, no commits, no pushes, no PR creation, no merges, no tests, no formatters, no auto-fixers, and no dependency or generated-asset changes were allowed; future approved remediation work may proceed through normal scoped branches, commits, PRs, and merges
- Intent: preserve a clear checkpoint so Phase F starts from a stable, explicitly documented risk baseline

## 2. Repository baseline

- repo: `blackbox-eye/blackbox-ui`
- default branch: `main`
- latest observed local HEAD: `922af04`
- recent merged PR baseline: PR #127, `fix(ui): stabilize agent access console layout contract`
- repo is public
- local working tree was observed clean at audit start

## 3. Phase A — Repo / File Architecture Findings

Locked findings from Phase A:

- P1 | Architectural risk | Confidence 0.97 | Separate PR: yes | Root sprawl: 121 tracked root files and 33 root PHP routes.
- P1 | Architectural risk | Confidence 0.95 | Separate PR: yes | Public, admin, debug, and operational routes are mixed in the root.
- P2 | Repo governance risk | Confidence 0.94 | Separate PR: yes | Tracked cleanup candidates: `=`, `test.txt`, `deploy_trigger.txt`, `trigger_ftp.txt`, `.htaccess.backup`, and the tracked logo zip.
- P2 | Maintainability risk | Confidence 0.93 | Separate PR: yes | Docs/report sprawl: many historical reports and 116 docs files.
- P1/P2 | Build governance risk | Confidence 0.88 | Separate PR: yes | Generated asset ownership is unclear.
- P2 | Dependency governance risk | Confidence 0.89 | Separate PR: yes | PHP dependency ownership is unclear: `vendor/` and `includes/PHPMailer` are tracked without a root `composer.json` observed.

Phase A interpretation:

The repository root is carrying too much mixed responsibility. Governance, cleanup, and asset/build ownership issues are now material contributors to audit risk even where no immediate functional failure is proven.

## 4. Phase B — PHP Routes / Security Surface Findings

Locked findings from Phase B:

- P0 before launch / P1 now | Confirmed defect / security surface | Confidence 0.98 | Separate PR: yes | `recaptcha-debug.php` is a public debug/config surface.
- P1 | Security contract gap | Confidence 0.92 | Separate PR: yes | Missing CSRF contract on authenticated mutations.
- P1 | Security hardening gap | Confidence 0.91 | Separate PR: yes | Public write endpoints need hardening.
- P1 | Security configuration risk | Confidence 0.90 | Separate PR: yes | CORS wildcard exists on write endpoints.
- P1/P2 | Information leakage risk | Confidence 0.83 | Separate PR: yes | Exception leakage candidates were identified.
- P1/P2 | Access-control uncertainty | Confidence 0.81 | Separate PR: yes | Direct access hardening was not proven.

Phase B interpretation:

The primary Phase B concern is not styling or application structure; it is exposed operational/debug surface plus missing mutation protections. The highest-priority response remains targeted security hardening, not broad cleanup.

## 5. Phase C — CSS Cascade / Responsive Architecture Findings

Locked findings from Phase C:

- P1 | CSS architecture risk | Confidence 0.95 | Separate PR: yes | CSS override sprawl is significant.
- P1 | CSS ownership risk | Confidence 0.94 | Separate PR: yes | `custom-ui.css` is overloaded.
- P1 | Runtime/cascade ownership risk | Confidence 0.92 | Separate PR: yes | Assistant and navigation CSS have too many owners.
- P1 | Layering contract risk | Confidence 0.90 | Separate PR: yes | Competing z-index contracts are present.
- P2 | Performance/render timing risk | Confidence 0.84 | Separate PR: yes | Async CSS load timing risk remains open.
- P1/P2 | Build/output governance risk | Confidence 0.87 | Separate PR: yes | Mixed minified CSS ownership is still unclear.

Important correction:

Minified CSS files are large mainly because of inline base64 source maps; size alone is not proof of drift.

Phase C interpretation:

The CSS surface shows clear ownership fragmentation. The risk is less about one broken selector and more about too many overlapping owners governing navigation, assistant, scroll, and responsive behavior.

## 6. Phase D — JavaScript Runtime / Build Pipeline Findings

Locked findings from Phase D:

- P1 | Runtime ownership risk | Confidence 0.94 | Separate PR: yes | `site.js` is a large central runtime owner.
- P1 | Runtime layering risk | Confidence 0.90 | Separate PR: yes | The public footer loads multiple runtime layers.
- P1/P2 | Ownership overlap risk | Confidence 0.89 | Separate PR: yes | Root `script.js` overlaps navigation, header, and dropdown behavior.
- P2 | Maintainability/runtime risk | Confidence 0.86 | Separate PR: yes | Inline scripts are spread across routes and includes.
- P2 | Build contract risk | Confidence 0.82 | Separate PR: yes | `interface-menu.min.js` ownership is unclear.
- P2 | Admin runtime governance risk | Confidence 0.80 | Separate PR: yes | Admin runtime loads source JS.

Important correction:

JS findings indicate runtime-collision risk, not confirmed live defects.

Phase D interpretation:

The JS problem is ownership and layering ambiguity. The current evidence supports collision risk and long-term maintainability concerns, but does not by itself prove active production failure.

## 7. Phase E — Tests / CI / Deploy Findings

Workflow inventory:

- `blog-intel-weekly.yml`
- `ci.yml`
- `cloudflare-pages.yml`
- `codeql-analysis.yml`
- `lighthouse.yml`
- `sprint5-smoke-test.yml`
- `visual-regression.yml`

Locked findings from Phase E:

- P3 | Positive baseline | Confidence 0.95 | Separate PR: no | Playwright baseline is solid.
- P3 | Positive baseline | Confidence 0.94 | Separate PR: no | PR #127 had strong targeted regression coverage.
- P1 | Deploy governance gap | Confidence 0.91 | Separate PR: yes | Deploy workflow lacks a strong pre-deploy build/test gate.
- P1 | Deploy hardening gap | Confidence 0.88 | Separate PR: yes | Deploy exclude list may be incomplete.
- P1/P2 | Coverage alignment gap | Confidence 0.87 | Separate PR: yes | CI coverage does not match the largest audit risks.
- P1/P2 | Security governance gap | Confidence 0.84 | Separate PR: yes | CodeQL is not a hard security gate.
- P2 | Deployment ownership risk | Confidence 0.83 | Separate PR: yes | Multiple deployment paths need an ownership decision.
- P2 | Observability/performance gap | Confidence 0.80 | Separate PR: yes | Lighthouse coverage is limited.
- P2 | Supply-chain/process risk | Confidence 0.79 | Separate PR: yes | Blog automation has write permissions.

Phase E interpretation:

Testing and recent regression coverage are meaningful strengths. The larger issue is that deployment and CI governance are not yet aligned with the most important repo, security-surface, and ownership risks identified in Phases A-D.

## 8. Locked findings summary

The repository has a credible recent UI-stability baseline, but the dominant risk has shifted to governance and ownership.

- Repo governance risk is concentrated in root sprawl, mixed route classes, tracked artifacts, report sprawl, and unresolved generated-asset/dependency ownership.
- Security risk is concentrated in public debug/config exposure, missing CSRF baseline, public write endpoint hardening, permissive CORS, possible exception leakage, and unproven direct-access restrictions.
- Frontend architecture risk is concentrated in CSS override sprawl, overloaded ownership, competing layering contracts, and unclear minified/source ownership.
- Runtime risk is concentrated in large central JS owners, overlapping script responsibilities, distributed inline scripts, and incomplete build/runtime ownership contracts.
- Delivery risk is concentrated in deploy gating, deployment-path ambiguity, incomplete risk-aligned CI coverage, and non-blocking security automation.

## 9. Open questions and caveats

- Branch protection and required checks were not yet verified.
- Repo security settings and CodeQL activation were not yet verified from settings.
- Production deployment source-of-truth is still unclear: FTP vs Cloudflare Pages.
- Live server direct-access behavior was not yet tested.
- Phase F is still pending.
- No intrusive testing was performed.

## 10. PR-required items

The following work should be executed as separate, scoped PRs rather than mixed into a broad refactor:

- Remove or gate public debug and operational endpoints.
- Add a CSRF baseline to authenticated mutations.
- Harden public write endpoints and review CORS policy.
- Define production deployment source-of-truth and harden exclusions/gates.
- Add risk-aligned security and deploy-surface checks.
- Define generated CSS/JS asset ownership.
- Consolidate CSS ownership for assistant/navigation/scroll layers.
- Define JS runtime ownership and decide the future of root `script.js`.
- Organize route governance, tracked artifacts, and audit/report inventory.

## 11. Documentation-only items

- Maintain this checkpoint as the pre-Phase-F baseline.
- Document the production deployment path decision once confirmed.
- Document generated asset ownership for CSS and JS.
- Document route classes and operational/debug surface ownership.
- Document CI required checks and security gate expectations.

## 12. Suggested PR roadmap

1. security: remove or gate public debug and operational endpoints
2. security: add CSRF baseline to authenticated mutations
3. deploy: harden FTP/Cloudflare deployment exclusions and define production path
4. test: add security/deploy-surface contract tests
5. build: define generated CSS/JS asset ownership
6. refactor(css): consolidate assistant/nav/scroll cascade ownership
7. refactor(js): define runtime ownership and retire root `script.js` if applicable
8. docs: organize audit reports, route registry, and repo governance

## 13. Stop-list for completed read-only audit/checkpoint work

This stop-list applied to the completed read-only audit/checkpoint task. Future scoped remediation PRs are allowed when explicitly approved, and the purpose of this list is to prevent unrelated work from being mixed into the same PR.

- no broad refactors
- no UI redesign
- no product/content rewrite
- no branch creation
- no commits
- no pushes
- no PR creation
- no merges
- no dependency updates
- no package updates
- no destructive cleanup
- no auto-formatting
- no auto-fixing
- no generated asset regeneration

## 14. Phase F prep

Phase F should begin with live-safe validation, not implementation.

- Confirm branch protection and required checks.
- Confirm repo security settings and CodeQL enforcement state.
- Confirm the production deployment source-of-truth.
- Validate direct-access behavior for sensitive/debug/operational routes without intrusive actions.
- Preserve separation between security hardening, deploy hardening, CSS refactors, JS refactors, and documentation cleanup.
- Use this checkpoint as the locked baseline for all Phase F deltas.

## 15. Final checkpoint verdict

The repository has meaningful regression coverage and recent UI stabilization, but technical debt is now concentrated in repo governance, security surface, CSS cascade ownership, JS runtime ownership, and CI/deploy alignment. The next work should not start with broad refactors; it should start with Phase F live-safe validation and then a small security/deploy hardening PR series.
