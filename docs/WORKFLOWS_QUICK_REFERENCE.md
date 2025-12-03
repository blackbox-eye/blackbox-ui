# 🚀 Quick Workflow Reference

Hurtig reference til almindelige workflow-opgaver.

## ⚡ Quick Actions

### Deploy til Production
```bash
git checkout main
git pull origin main
git push origin main  # Trigger automatisk deployment
```

### Run Manual Workflow
1. Gå til: https://github.com/AlphaAcces/blackbox-ui/actions
2. Vælg workflow i venstre menu
3. Klik "Run workflow" (højre side)
4. Vælg branch og parametre
5. Klik grøn "Run workflow" knap

### Check Workflow Status
```bash
# Via CLI (kræver gh CLI)
gh run list --workflow=ci.yml --limit 5

# Via Browser
https://github.com/AlphaAcces/blackbox-ui/actions
```

## 🔧 Common Fixes

### Fix: Missing Secrets Error
```bash
# 1. Check hvilke secrets der mangler i workflow output
# 2. Gå til Settings → Secrets and variables → Actions
# 3. Klik "New repository secret"
# 4. Navngiv og indsæt værdi
# 5. Kør workflow igen
```

### Fix: FTP Connection Failed
```bash
# Check secrets er korrekte
# - FTP_HOST: server hostname (uden http://)
# - FTP_USERNAME: FTP bruger
# - FTP_PASSWORD: FTP password
# - FTP_REMOTE_PATH: /public_html eller /
```

### Fix: Smoke Tests Failed
```bash
# Check at site er tilgængeligt
curl -I https://blackbox.codes

# Check at SITE_URL secret er sat korrekt
# Settings → Secrets → SITE_URL = https://blackbox.codes
```

## 📊 Workflow Status Icons

| Icon | Meaning |
|------|---------|
| 🟢 ✓ | Success - alt OK |
| 🟡 ⚠ | Warning - ikke kritisk |
| 🔴 ✗ | Failed - kræver attention |
| 🔵 ● | Running - workflow kører |
| ⚪ ○ | Queued - venter på at starte |
| ⊘ | Cancelled - stoppet manuelt |

## 🔐 Required Secrets Reference

### Minimum Required (for deployment)
```
FTP_HOST          = ftp.example.com
FTP_USERNAME      = username
FTP_PASSWORD      = ********
FTP_REMOTE_PATH   = /public_html
```

### Optional (for enhanced features)
```
SITE_URL          = https://blackbox.codes
CF_ZONE_ID        = ********************************
CF_API_TOKEN      = ********************************
```

## 📝 Workflow Files

| File | Purpose |
|------|---------|
| `.github/workflows/ci.yml` | Main deploy pipeline |
| `.github/workflows/codeql-analysis.yml` | Security scanning |
| `.github/workflows/lighthouse.yml` | Performance audit |
| `.github/workflows/visual-regression.yml` | Visual testing |

## 🎯 Common Commands

### View Workflow Logs
```bash
# Via gh CLI
gh run view <run-id>
gh run view --log

# Download logs
gh run download <run-id>
```

### Cancel Running Workflow
```bash
# Via gh CLI
gh run cancel <run-id>

# Via browser
Actions → Running workflow → Cancel workflow
```

### Re-run Failed Workflow
```bash
# Via gh CLI
gh run rerun <run-id>

# Via browser
Actions → Failed workflow → Re-run all jobs
```

## 🆘 Emergency Procedures

### Emergency Rollback
```bash
# 1. Find forrige working commit
git log --oneline -10

# 2. Create hotfix branch
git checkout -b hotfix/rollback-issue main

# 3. Revert til working commit
git revert <bad-commit-sha>

# 4. Push og opret PR
git push origin hotfix/rollback-issue

# 5. Merge til main (trigger deployment)
```

### Stop Deployment Mid-Flight
```bash
# Via browser
Actions → Running "CI & Deploy" → Cancel workflow

# Via gh CLI
gh run list --workflow=ci.yml --limit 1
gh run cancel <run-id>
```

### Manually Trigger Deployment
```bash
# Via gh CLI
gh workflow run ci.yml --ref main

# Via browser
Actions → CI & Deploy (Secure) → Run workflow
```

## 📞 Support Quick Links

- 📖 Full Guide: [WORKFLOWS_GUIDE.md](WORKFLOWS_GUIDE.md)
- 🔧 Setup Guide: [CI_CD_SETUP_GUIDE.md](CI_CD_SETUP_GUIDE.md)
- 📧 Support: ops@blackbox.codes
- 🐛 Issues: https://github.com/AlphaAcces/blackbox-ui/issues

---

**Pro Tip**: Bookmark this page for quick access! 🔖
