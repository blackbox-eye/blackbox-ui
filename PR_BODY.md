chore(security): scrub hardcoded secrets and require env vars

This PR removes hardcoded secrets from the repository and replaces them with placeholders. See `docs/SPRINT4_VERIFICATION_AUDIT.md` for the security audit and remediation checklist.

Changes include:
- `db.php`: read DB password from `DB_PASSWORD` environment variable.
- `config.js`: removed client-side `GEMINI_API_KEY` and added placeholder guidance.
- `.htaccess` and backups: replaced reCAPTCHA site/secret keys with placeholders.
- Various docs updated to remove example secrets and provide instructions for adding secrets to hosting environment or GitHub Actions Secrets.

Security checklist:
- [ ] Rotate any potentially exposed credentials (DB, SMTP, Cloudflare tokens).
- [ ] Add required secrets to GitHub Actions secrets: `DB_PASSWORD`, `CF_API_TOKEN`, `CF_ZONE_ID`, `FTP_USERNAME`, `FTP_PASSWORD`, `GEMINI_API_KEY`.
- [ ] Enable GitHub secret scanning and branch protection.
- [ ] Consider using a secrets manager (Vault / AWS Secrets Manager) for long-term storage.

Please review the `security-scrub` branch changes and merge when approved.
