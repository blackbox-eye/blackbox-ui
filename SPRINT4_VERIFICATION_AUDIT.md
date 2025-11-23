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
