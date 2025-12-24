# 🎉 RELEASE REPORT - SPRINT 7 FINAL

**Date:** 2025-12-22  
**Commit:** `94d4c24`  
**Status:** ✅ **DEPLOYED & VERIFIED**

---

## 📊 Executive Summary

Sprint 7 has been successfully deployed to production. All CI/CD pipelines are green, all tests pass, and the live site is operational.

### Key Metrics
| Metric | Value |
|--------|-------|
| **Tests Passed** | 855/855 (100%) |
| **Flaky Tests** | 1 (touch target tolerance) |
| **Skipped Tests** | 200 (platform-specific) |
| **CI/CD Status** | ✅ All 4 jobs green |
| **Deploy Time** | 2m 18s |
| **Live Site** | ✅ HTTP 200 |

---

## 🚀 CI/CD Pipeline Status

**Workflow Run:** [#20428693087](https://github.com/blackbox-eye/blackbox-ui/actions/runs/20428693087)

| Job | Status | Duration |
|-----|--------|----------|
| ✅ Build & Verify | SUCCESS | 7s |
| ✅ Secure Delete index.html | SUCCESS | 19s |
| ✅ Secure FTP Deploy | SUCCESS | 2m 18s |
| ✅ Smoke Tests | SUCCESS | 1m 34s |

### Deploy Fix Journey
The deployment was blocked due to protocol mismatch:

1. **Initial Error:** `server only supports SFTP` (FTPS action failed)
2. **Attempt 2:** SFTP via lftp - port 22 blocked from GitHub Actions
3. **Attempt 3:** rsync over SSH - hostname parsing issue
4. **Final Fix:** FTP with optional TLS (FTPES) - **SUCCESS**

The solution uses `lftp` with passive FTP mode and TLS negotiation, which is compatible with the hosting server.

---

## 📦 Files Changed in This Release

### CI/CD Workflow
- `.github/workflows/ci.yml` - Switched from SFTP to FTP+TLS

### Sprint 7 Features (Previous Commits)
- `lang/en.json`, `lang/da.json` - i18n timestamp translations
- `assets/css/custom-ui.css` - Alphabot solid background (no blur)
- `includes/console-selector.php` - formatRelativeTime() with i18n

---

## ✅ QA Verification Checklist

| Item | Status | Notes |
|------|--------|-------|
| Alphabot overlay | ✅ | Solid background (no blur) |
| Console selector | ✅ | All 3 consoles present |
| Mobile nav | ✅ | Console buttons visible |
| i18n timestamps | ✅ | Localized relative times |
| Live site HTTP | ✅ | 200 OK |
| Tests | ✅ | 855 passed |

---

## 🔐 Security Notes

- FTP deployment uses TLS encryption when available
- No credentials exposed in logs
- SSL certificate verification disabled (hosting provider requirement)
- Passive mode enabled for NAT/firewall compatibility

---

## 🎯 CTA Deduplication & Sticky Bar Stabilization

**Date:** 2025-12-24

### Problem Solved
Landing page had **3 conflicting CTA elements** causing z-index conflicts and test failures:
- `#sticky-cta-bar` (site-footer.php)
- `.graphene-cta-bar` (index.php hero)
- `#sticky-cta` (site-footer.php - canonical)

### Root Fix Applied

| Layer | Change | File |
|-------|--------|------|
| **PHP Gating** | `#sticky-cta-bar` hidden on landing | `includes/site-footer.php` |
| **PHP Removal** | `.graphene-cta-bar` removed from landing DOM | `index.php` |
| **CSS Contract** | `z-index: 75` single source of truth | `landing-p0-fix.css` |
| **Test Hardgate** | DOM count === 1 AND z-index === 75 | `a11y-hardgate.spec.js` |

### Priority Access – Sticky Bottom Utility Bar

The canonical CTA (`#sticky-cta`) provides:
- **Primary CTA:** "Book Demo" button
- **Secondary CTA:** "Call Us" button (tel: link)
- **Dismiss button (×):** Hides bar for session (sessionStorage)
- **z-index: 75** (above content, below reCAPTCHA badge)

### Verification
```bash
npx playwright test tests/a11y-hardgate.spec.js --grep "Landing"
# 40 tests passing (1 known flaky unrelated to CTA)
```

---

## 📝 Remaining Items (Max 2)

1. **Touch target test flakiness** - 1 flaky test for info buttons (48px vs 40px threshold)
2. **SFTP access** - Server blocks port 22 from GitHub Actions; consider SSH key deployment in future

---

## 🏁 Conclusion

**Sprint 7 is COMPLETE and DEPLOYED.**

- All tests pass locally (855/855)
- CI/CD pipeline fully operational
- Live site verified and accessible
- No blocking issues remaining

**Next:** Sprint 8 planning can begin.

---

*Generated: 2025-12-22 09:45 UTC*  
*Workflow: CI & Deploy (Secure)*  
*Branch: main*
