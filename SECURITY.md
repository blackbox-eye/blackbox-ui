name=SECURITY.md url=https://github.com/AlphaAcces/blackbox-ui/blob/main/SECURITY.md
# Security & Code Scanning

This repository uses CodeQL for code scanning. Due to repository/plan permissions (GitHub Advanced Security), JavaScript CodeQL scanning is gated. PHP scanning runs by default.

How to enable JavaScript CodeQL scanning:

1. Verify repository permissions/plan
   - For private repositories, Code scanning alerts (Advanced Security) may require an Organization with GitHub Advanced Security enabled. If you see "Code scanning alerts • Disabled" in Security → Overview, it may mean Advanced Security isn't available for this repo. See: https://docs.github.com/en/code-security/secure-your-code

2. Option A — Enable Advanced Security (Organization)
   - If this repository belongs to an Organization with Admin access: Org owner should enable Advanced Security for the organization or repository via Organization settings → Security & analysis / Advanced Security licensing. After that, the Security → Code scanning UI will allow enabling CodeQL.

3. Option B — Toggle the JS scan via repository secret (recommended when UI is not available)
   - Go to Settings → Secrets and variables → Actions → New repository secret
   - Name: ENABLE_JS_CODEQL
   - Value: true
   - Save the secret. When set to "true", the workflow will run JavaScript scanning. When not present or set to anything else, JavaScript scanning will be skipped to avoid failures.

Notes:
- The secret toggle is used so that the workflow doesn't fail or attempt unsupported operations where Advanced Security is not available.
- After enabling Advanced Security in the GitHub settings, you can also flip the secret to "true" to activate JS scanning immediately.
