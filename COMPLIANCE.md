name=COMPLIANCE.md url=https://github.com/AlphaAcces/ALPHA-Interface-GUI/blob/main/COMPLIANCE.md
# Security & Compliance Changes — Ready for Review

Summary of changes in this PR:
- Added a CodeQL workflow that runs PHP analysis by default and only runs JS analysis when the repository secret ENABLE_JS_CODEQL == 'true'.
- Added SECURITY.md with step-by-step instructions for enabling Code scanning and how to toggle JS scanning via repository secrets when Advanced Security is not available.

Reviewer checklist:
- [ ] Confirm .github/workflows/codeql-analysis.yml is present and syntactically correct.
- [ ] Confirm CONFIG: The team intends to toggle JS scanning by adding secret ENABLE_JS_CODEQL=true OR to enable Advanced Security in org settings.
- [ ] Confirm installation steps (composer/npm) are acceptable for CI environment. Adjust build steps if project requires special build.
- [ ] Confirm documentation (SECURITY.md) is accurate and clear.

Merge instructions:
- Once reviewed, merge the PR to main. If you want JS scans to run immediately after merge, set the repository secret ENABLE_JS_CODEQL to "true" in Settings → Secrets and variables → Actions.
