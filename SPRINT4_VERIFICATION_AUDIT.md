# Sprint 4 — Verification Audit

Summary of verification steps, artifacts, and results for `v1.0.0-sprint4`.

Checklist
- [ ] Confirm `https://blackbox.codes` header shows `FAQ` and language buttons visible at mobile/tablet/desktop widths
- [ ] Cross-browser screenshots (Chrome / Firefox / Edge / Brave)
- [ ] Lighthouse scores (Performance / Accessibility / Best Practices / SEO)
- [ ] Cloudflare purge confirmed (timestamped)
- [ ] DB connectivity & table import verification (documented queries / rows)
- [ ] Release notes and changelog entry

Artifacts
- Visual screenshots: GitHub Actions artifact `visual-screenshots`
- Lighthouse reports: GitHub Actions artifact from `lighthouse` workflow

How to view artifacts
- Open the repository on GitHub > Actions > select the Visual Regression or Lighthouse run > Artifacts > download.

Notes
- The Cloudflare token that was previously exposed must be revoked (confirmed by user) and a new zone-scoped token added to GitHub Secrets as `CF_API_TOKEN` and `CF_ZONE_ID`.
- Use the `visual-regression` workflow dispatch to re-run at any time.

**Document summaries (brief)**

- **concept_plan.md:** Platform vision and commercial packaging for Blackbox EYE (MVP → Premium → Enterprise); UX and performance recommendations; compliance (GDPR/NIS2) and roadmap.
- **EXECUTIVE_SUMMARY_v2.0.md:** CI/CD security hardening summary — FTPS/TLS, extended smoke tests, fail‑fast behavior; recommends merge to main.
- **DEPLOYMENT_VALIDATION.md:** Step‑by‑step validations (DB checks, table import, Cloudflare purge, cross‑browser checks, Lighthouse audits) and test scripts.
- **CI_CD_SETUP_GUIDE.md:** Workflow architecture and required secrets; guidance for deployment and smoke tests.
- **PERFORMANCE_AUDIT.md:** Completed server optimizations and prioritized performance actions (Tailwind prod, minify JS, inline critical CSS).
- **NEXT_ITERATION_RECOMMENDATIONS.md:** Staging, secret rotation automation, parallel smoke tests and SFTP migration recommendations.

**Security findings (summary)**

- Hardcoded secrets were found and replaced in repo files: production reCAPTCHA keys and SMTP password, and a hardcoded MySQL password in `verify_deployment.ps1`. These have been replaced with placeholders or moved to require `DB_PASSWORD` environment variable. (See branch `security-scrub`.)
- Action items: rotate any potentially exposed credentials immediately (DB, SMTP, Cloudflare tokens) and confirm revocation of old Cloudflare token.
- Recommendations: enable GitHub Secret Scanning, migrate to SFTP/SSH keys for deploys, and automate secret rotation (Vault + Actions) as per `NEXT_ITERATION_RECOMMENDATIONS.md`.

**Cross‑browser verification (placeholder)**

When `visual-screenshots` artifact is available, paste screenshots below (or link to artifact files). Verify these items per browser/viewport:
- Navigation color and hover states
- Mobile menu overlay and hamburger behavior
- Language switcher visibility
- Dark‑mode behavior (Brave)

- Chrome (desktop, mobile, tablet): [add screenshot paths / notes]
- Brave (desktop, dark mode): [add screenshot paths / notes]
- Firefox (desktop, mobile): [add screenshot paths / notes]
- Edge (desktop): [add screenshot paths / notes]

**Lighthouse scores (placeholder)**

Add the Lighthouse CI artifact reports here once available. Use the numeric scores and CWV values:

Desktop:
- Performance: __ /100
- Accessibility: __ /100
- Best Practices: __ /100
- SEO: __ /100
- LCP: __s, FID/TBT: __ms, CLS: __

Mobile:
- Performance: __ /100
- Accessibility: __ /100
- Best Practices: __ /100
- SEO: __ /100
- LCP: __s, FID/TBT: __ms, CLS: __

Notes / quick advice for any failing metric:
- Performance <85: prioritize Tailwind production build + JS minification + inline critical CSS.
- Accessibility <90: fix color contrast and ARIA labels.
- CLS >0.1: add width/height attributes or CSS containment for shifting elements.

**Release & final steps**

1. Confirm rotation/revocation of any exposed credentials.
2. Download `visual-screenshots` and Lighthouse artifacts from Actions and paste screenshots + scores into this document.
3. Run a final manual cross‑browser check against `https://blackbox.codes` and note any regressions.
4. Create branch `sprint4-verification-final` (I will create and push it) containing:
	- this updated audit document
	- scrubbed secrets (already on `security-scrub`)
	- release notes draft
5. Open PR `sprint4-verification-final -> main` for review. After approval, publish GitHub Release `v1.0.0-sprint4` with final report and artifacts.

**How I will fetch artifacts (options)**
- Manual (recommended): Go to GitHub Actions → select run → Artifacts → download `visual-screenshots` and `lighthouse` reports and upload the needed images into this repo or paste links.
- Automated (requires a GitHub token with repo access): use `gh` CLI or GitHub API to download artifacts and commit them into the `sprint4-verification-final` branch (I can prepare these commands if you provide a token).

---

_This file was updated programmatically as part of the Sprint 4 verification workflow. Fill the placeholders after you (or I) download the CI artifacts._
