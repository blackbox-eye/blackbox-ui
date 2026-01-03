---
name: TS24 Healthcheck Stub Fixer
description: >
  A custom agent designed to fix issues with the TS24 healthcheck stub.
  This agent ensures that the healthcheck URL is accessible and correctly returns a valid JSON response.
  It also ensures the PHP server on port 8091 is correctly configured and running.
---

tasks:
  - name: Ensure TS24 healthcheck stub is accessible
    description: |
      The agent will ensure that the `ts24_health_stub.php` file is accessible at the expected URL (http://127.0.0.1:8091/tools/ts24_health_stub.php).
      If the file is missing, the agent will create it and ensure it returns the correct JSON response:
      ```json
      {
        "stub": true,
        "secretConfigured": true,
        "usesHS256": true,
        "expectedIss": "https://blackbox.codes",
        "expectedAud": "ts24",
        "recentErrors": [],
        "notes": "TS24 stub response for local testing",
        "timestamp": "2025-11-30T18:26:28"
      }
      ```

  - name: Start PHP server on port 8091
    description: |
      The agent will start the PHP server on port 8091 to serve the healthcheck and stub response files.
      If the server is already running, it will restart it to ensure there are no conflicts or stale sessions.

  - name: Verify server and stub availability
    description: |
      The agent will verify that the PHP server is up and running at the correct port (8091).
      It will also check the healthcheck and ensure that the server responds to requests on `/tools/ts24_health_stub.php`.

  - name: Run SSO healthcheck
    description: |
      The agent will run the healthcheck command (`npm run sso:health`) to verify that the healthcheck passes with the updated stub.
      It will ensure the healthcheck verifies both the GUI and TS24 systems.

  - name: Run Playwright tests
    description: |
      After confirming that the stub is correctly configured, the agent will run the Playwright tests to verify that the healthcheck integration works as expected.
      It will validate that the SSO flow works end-to-end and no visual regressions are present.

