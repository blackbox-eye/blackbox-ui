# CI/CD Workflow Setup Guide

## Overview

This document describes the CI/CD workflow configuration for the ALPHA Interface GUI project. The workflow automates FTP deployment, ensures index.html is removed from the remote server, and runs smoke tests to verify the deployment.

## Workflow Trigger

The workflow is configured to run **ONLY** on:
- Push to the `main` branch
- Manual trigger via `workflow_dispatch` (GitHub Actions UI)

**Important:** The workflow does NOT run on pull request events. This ensures that deployment and FTP operations only occur when code is merged to main.

## Required Secrets

Before the workflow can run successfully, you must configure the following secrets in GitHub:

1. Navigate to your repository on GitHub
2. Go to **Settings** → **Secrets and variables** → **Actions**
3. Click **New repository secret** and add each of the following:

| Secret Name | Description | Example |
|------------|-------------|---------|
| `FTP_HOST` | FTP server hostname | `ftp.example.com` |
| `FTP_USERNAME` | FTP username for authentication | `username@example.com` |
| `FTP_PASSWORD` | FTP password for authentication | `your-secure-password` |
| `FTP_REMOTE_PATH` | Path on remote server where files should be deployed | `/public_html/` or `/` |

## Workflow Jobs

The workflow consists of four sequential jobs:

### 1. Build Job (✅ Lint & Verify)
**Runs:** Always on any trigger
**Purpose:** Basic file verification

- Verifies README.md exists
- Verifies index.php exists

This job ensures the repository has the required files before attempting deployment.

### 2. Delete index.html Job (🗑️ Delete index.html on remote)
**Runs:** Only on push to main branch
**Purpose:** Remove index.html from remote server

- Installs `lftp` FTP client
- Connects to FTP server using secrets
- Deletes `index.html` if it exists on the remote server
- Continues even if file doesn't exist (`continue-on-error: true`)

**Why this is needed:** The project uses `index.php` as the main entry point. If `index.html` exists, it may take precedence on many web servers, preventing `index.php` from being served.

### 3. FTP Deploy Job (🚀 FTP Deploy to remote)
**Runs:** Only on push to main branch
**Purpose:** Upload all files to remote server

- Checks out the repository
- Uses `SamKirkland/FTP-Deploy-Action@v4.3.5` to deploy files
- Uploads all files from the local directory to the remote server path

### 4. Smoke Tests Job (🧪 Smoke Tests)
**Runs:** Only on push to main branch
**Purpose:** Verify deployment was successful

The smoke tests verify:

1. **Site Accessibility**
   - Tests that the site responds with HTTP 200, 301, or 302
   - URL tested: `http://{FTP_HOST}/`

2. **index.php is Served**
   - Verifies the page content contains expected HTML markers
   - Looks for "ALPHA Interface", "<!DOCTYPE", or "<html" in response

3. **index.html is NOT Served**
   - Tests that `http://{FTP_HOST}/index.html` returns 404 or 403
   - Confirms successful deletion in step 2

4. **Test Summary**
   - Displays commit SHA, branch name, and event type

## Workflow Execution Flow

```
Push to main
    ↓
Build Job (Verify files)
    ↓
Delete index.html Job (Remove old index.html)
    ↓
FTP Deploy Job (Upload all files)
    ↓
Smoke Tests Job (Verify deployment)
    ↓
Success / Failure notification
```

## Testing the Workflow

### Before Merging to Main

1. Create a pull request (e.g., PR #3)
2. The workflow will NOT run on the PR (as designed)
3. Review the code changes in the PR
4. Ensure all required secrets are configured in repository settings

### After Merging to Main

1. Merge the PR to main branch
2. The workflow will automatically trigger
3. Monitor the workflow execution in **Actions** tab
4. Check each job for success/failure

### Manual Trigger

You can also trigger the workflow manually:

1. Go to **Actions** tab in GitHub
2. Select **CI & Deploy** workflow
3. Click **Run workflow**
4. Select `main` branch
5. Click **Run workflow** button

## Troubleshooting

### Workflow Not Running

- **Check:** Is the push to the `main` branch?
- **Check:** Are there any syntax errors in `.github/workflows/ci.yml`?
- **Solution:** Verify the branch name and YAML syntax

### FTP Connection Failed

- **Check:** Are all FTP secrets configured correctly?
- **Check:** Is the FTP server accessible from GitHub Actions?
- **Solution:** Verify secrets and test FTP connection manually

### Smoke Tests Failed

- **Check:** Did the FTP deploy complete successfully?
- **Check:** Is the `FTP_HOST` the same as the website URL?
- **Solution:** Verify FTP_HOST matches your website URL, check deployment logs

### index.html Still Being Served

- **Check:** Did the delete-index-html job complete successfully?
- **Check:** Does the web server configuration prioritize index.html?
- **Solution:** Manually check remote server, verify .htaccess configuration

## Security Best Practices

1. **Never commit secrets to the repository**
   - Always use GitHub Secrets for sensitive data

2. **Use strong FTP passwords**
   - Rotate passwords regularly
   - Consider using separate FTP user for CI/CD with limited permissions

3. **Monitor workflow runs**
   - Check Actions tab regularly for failed runs
   - Set up notifications for workflow failures

4. **Limit FTP user permissions**
   - Create a dedicated FTP user for automated deployments
   - Grant only necessary permissions to deployment directory

## Secret Rotation

To rotate secrets:

1. Update the secret value in your FTP server/hosting provider
2. Update the corresponding secret in GitHub Settings → Secrets
3. Manually trigger the workflow to test the new credentials
4. Revoke old credentials from hosting provider

## Verification Steps

After a successful workflow run:

1. **Check the live site:** Visit your website URL
2. **Verify index.php loads:** The main page should display correctly
3. **Verify index.html is gone:** Navigate to `/index.html` - should return 404 or 403
4. **Check workflow logs:** Review each job's output in Actions tab

## Support

For issues or questions:
- Email: ops@blackbox.codes
- Check workflow logs in GitHub Actions tab
- Review this documentation
- Contact ALPHA Lead for enterprise integration

---

**Last Updated:** 2025-11-19
**Version:** 1.0
**Workflow File:** `.github/workflows/ci.yml`
