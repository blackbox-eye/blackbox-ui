# Smoke Test Fix - PR #285
## QA: verify deployed CSS + remove legacy bronze overrides

### Executive Summary
Fixed smoke test failure in PR #285 by adding proper wait times for Cloudflare cache propagation and implementing retry logic with improved error handling.

---

## Problem Statement

**Issue**: CI workflow smoke test was failing with:
```
curl: (22) The requested URL returned error: 404
URL: https://***/assets/css/marketing.min.css?v=24a97fc99cc1b7b13944ad313f72fbe8d1c841ea
```

**Context**: Test 0 in the smoke-tests job was attempting to verify that the deployed marketing.min.css file:
1. Is accessible on production
2. Does not contain legacy bronze/yellow CTA values (#9a7630, rgba(154,118,48,...))

---

## Root Cause Analysis

### Timeline Investigation
1. **FTP Deployment completes** (ftp-deploy job)
2. **Smoke test starts** → waits 15 seconds for deployment propagation
3. **Cloudflare cache purge** → API returns 200 OK immediately
4. **Test 0 executes** → **IMMEDIATELY after purge, no wait time**
5. **Result**: 404 error because Cloudflare edge nodes haven't propagated the purge yet

### Key Findings

#### 1. **Cloudflare Cache Purge is Asynchronous**
- The Cloudflare API returns 200 OK when the purge *request* is accepted
- **NOT** when the purge is fully propagated to all edge nodes
- Edge node propagation can take 10-30+ seconds depending on:
  - Geographic distribution of edge nodes
  - Current edge node load
  - Network conditions

#### 2. **No Wait Time After Purge**
```yaml
# BEFORE (problematic flow):
- name: Purge Cloudflare cache
  run: |
    # ... purge API call ...
    echo "✅ Cloudflare cache purged successfully."
    # ❌ Immediately proceeds to next step!

- name: Configure test environment
  # Test runs immediately...
```

#### 3. **No Retry Logic**
- Single curl attempt with `-f` flag (fail on HTTP errors)
- No resilience against transient network issues or cache propagation delays

---

## Solution Implemented

### Change 1: Add 30-Second Wait After Cache Purge

**Location**: `.github/workflows/ci.yml` lines 364-368

```yaml
echo "✅ Cloudflare cache purged successfully."

echo "⏳ Waiting for Cloudflare cache propagation..."
echo "   Duration: 30 seconds"
sleep 30
echo "✅ Cache propagation complete"
```

**Rationale**:
- Allows Cloudflare edge nodes time to receive purge notification
- Ensures fresh content is fetched from origin on next request
- 30 seconds is a conservative wait time based on Cloudflare documentation

### Change 2: Add Retry Logic with Better Error Messages

**Location**: `.github/workflows/ci.yml` lines 427-450

```bash
# Retry logic for cache propagation delays
MAX_RETRIES=3
RETRY_DELAY=10
css=""

for i in $(seq 1 $MAX_RETRIES); do
  if css=$(curl -fsSL "$CSS_URL" 2>&1); then
    echo "✅ Successfully fetched marketing.min.css (attempt $i/$MAX_RETRIES)"
    break
  else
    if [ $i -lt $MAX_RETRIES ]; then
      echo "⚠️  Attempt $i/$MAX_RETRIES failed, retrying in ${RETRY_DELAY}s..."
      sleep $RETRY_DELAY
    else
      echo "::error::Failed to fetch marketing.min.css after $MAX_RETRIES attempts" >&2
      echo "URL: $CSS_URL" >&2
      echo "This could indicate:" >&2
      echo "  - File not deployed to server" >&2
      echo "  - Cloudflare cache not fully purged" >&2
      echo "  - Server configuration blocking access" >&2
      exit 1
    fi
  fi
done
```

**Benefits**:
1. **Resilience**: Up to 3 attempts with 10-second delays
2. **Observability**: Clear logging of each attempt
3. **Debugging**: Specific error messages for different failure scenarios
4. **Graceful degradation**: Handles transient network issues

---

## Total Wait Time Calculation

| Phase | Duration | Cumulative |
|-------|----------|------------|
| Deployment propagation | 15s | 15s |
| Cache purge API call | ~2-5s | 17-20s |
| **Cache propagation wait** | **30s** | **47-50s** |
| Configure test environment | ~5s | 52-55s |
| **Test 0 - Attempt 1** | ~2-5s | ~55-60s |
| Retry delay (if needed) | 10s × 2 | up to 80s |

**Before fix**: ~20-25s from deploy to test  
**After fix**: ~55-60s minimum (up to 80s with retries)

---

## Verification

### Pre-Deployment Checks
- [x] YAML syntax validated with Python yaml parser
- [x] Bash retry logic tested locally
- [x] marketing.min.css file exists in repo
- [x] marketing.min.css does NOT contain legacy bronze values
- [x] File is properly tracked in git (SHA: 2bfb2055ce86f4f14229b29cc38b6fc54155d08c)
- [x] FTP deploy excludes don't block CSS files

### Expected Behavior
1. ✅ Cache purge completes successfully
2. ✅ 30-second propagation wait executes
3. ✅ Test fetches CSS file (with retries if needed)
4. ✅ Content validation passes (no legacy bronze values)
5. ✅ Smoke test job succeeds

---

## Monitoring & Observability

### Success Indicators
```
✅ Cloudflare cache purged successfully.
⏳ Waiting for Cloudflare cache propagation...
✅ Cache propagation complete
🔎 Fetching: https://blackbox.codes/assets/css/marketing.min.css?v={sha}
✅ Successfully fetched marketing.min.css (attempt 1/3)
✅ marketing.min.css does not contain legacy hero CTA yellow/bronze values
```

### Failure Scenarios

#### Scenario 1: Immediate Failure (File Not Deployed)
```
⚠️  Attempt 1/3 failed, retrying in 10s...
⚠️  Attempt 2/3 failed, retrying in 10s...
::error::Failed to fetch marketing.min.css after 3 attempts
  - File not deployed to server  ← Check FTP deploy logs
```

#### Scenario 2: Delayed Success (Cache Propagation)
```
⚠️  Attempt 1/3 failed, retrying in 10s...
✅ Successfully fetched marketing.min.css (attempt 2/3)
```

#### Scenario 3: Legacy Values Detected
```
✅ Successfully fetched marketing.min.css (attempt 1/3)
::error::marketing.min.css still contains legacy hero CTA yellow/bronze values
  - Deploy didn't upload updated assets  ← Rebuild and redeploy
```

---

## Related Files

| File | Change | Purpose |
|------|--------|---------|
| `.github/workflows/ci.yml` | Modified | Added wait time and retry logic |
| `assets/css/marketing.min.css` | No change | Target file being tested |
| `assets/css/marketing.css` | No change | Source file (not minified) |
| `includes/site-header.php` | No change | Loads CSS with version parameter |

---

## Future Improvements

### Potential Enhancements
1. **Dynamic wait time**: Adjust based on previous deployment times
2. **Health check endpoint**: Verify deployment before running tests
3. **Cloudflare purge verification**: Poll Cloudflare API to confirm purge completion
4. **Exponential backoff**: Increase retry delay with each attempt
5. **Separate asset verification**: Test static assets before smoke tests

### Alternative Approaches Considered

#### Option A: Remove Query Parameter
**Rejected**: Query parameter is essential for cache-busting and version verification

#### Option B: Use Static Version String
**Rejected**: Git SHA provides better traceability and invalidates caches on every deploy

#### Option C: Selective Cache Purge
**Considered**: Purge only `/assets/css/*` instead of entire cache
**Decision**: Keep purge_everything for now; optimize later if needed

---

## Testing Checklist

- [ ] Verify workflow runs successfully on next main branch deployment
- [ ] Confirm marketing.min.css accessible at: `https://blackbox.codes/assets/css/marketing.min.css`
- [ ] Check browser DevTools shows correct cache headers
- [ ] Validate no legacy bronze values in production CSS
- [ ] Monitor workflow logs for:
  - [ ] Cache purge success message
  - [ ] 30-second wait completion
  - [ ] CSS fetch success on first attempt
  - [ ] No retry attempts needed
- [ ] If retries occur, investigate root cause

---

## Rollback Plan

If issues persist:

1. **Increase wait time**: Change 30s to 45s or 60s
2. **Increase retry count**: Change MAX_RETRIES from 3 to 5
3. **Add manual verification**: Temporarily disable automated test
4. **Check FTP logs**: Verify files actually uploaded
5. **Bypass Cloudflare**: Test direct origin access

---

## Contact & Escalation

**Issue**: PR #285 - QA: verify deployed CSS + remove legacy bronze overrides  
**Agent**: ALPHA-Web-Diagnostics-Agent  
**Priority**: HIGH 🔥  
**Repository**: blackbox-eye/blackbox-ui

For questions or issues:
1. Review workflow logs in GitHub Actions
2. Check Cloudflare dashboard for purge history
3. Verify FTP server logs for deployment status
4. Contact repository maintainers if 404 errors persist after fix

---

**Document Status**: ✅ Complete  
**Last Updated**: 2025-12-13  
**Version**: 1.0  
