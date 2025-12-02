# Rollback Runbook — ALPHA-GUI Blue/Green

## Purpose

This runbook provides step-by-step instructions for rolling back from Green (new release) to Blue (previous stable) in the event of a P0/P1 incident during or after deployment.

## Rollback Decision Matrix

| Severity | Symptom | Decision Window | Action |
| --- | --- | --- | --- |
| P0 | Complete SSO failure, 5xx > 10% | Immediate | Auto-rollback |
| P0 | Cookie integrity breach | Immediate | Auto-rollback |
| P1 | Partial SSO failures, UI broken | 5 minutes | Manual evaluation |
| P2 | Minor UI glitches, non-blocking | 30 minutes | Defer to next release |

## Pre-Requisites

- Blue environment tagged and preserved (do NOT delete during deploy window)
- Load balancer / DNS TTL set to 60 seconds or lower
- FTP/FTPS credentials available for emergency push
- WAR ROOM communication channel open

## Rollback Procedure

### Step 1: Confirm Rollback Decision

```text
WAR ROOM: "ROLLBACK INITIATED — [REASON]"
```

Notify TS24 team via EKSTERN-POST if SSO-related.

### Step 2: Switch Traffic to Blue

**Option A: Load Balancer**

```powershell
# Example: Azure / AWS CLI or dashboard
# Point traffic back to blue target group
```

**Option B: DNS Failover**

```powershell
# Update A/CNAME record to blue IP
# TTL propagation: ~60 seconds
```

### Step 3: Verify Blue is Serving

```powershell
# Health check
Invoke-WebRequest -Uri "https://[BLUE_URL]/tools/sso_health.php" -UseBasicParsing | Select-Object StatusCode, Content
```

Expected: `200 OK` with `sso_enabled: true`

### Step 4: Confirm SSO Flow

1. Navigate to `/agent-login.php`
2. Authenticate with test credentials
3. Verify redirect to dashboard
4. Check cookie `gdi_sso_token` is present and valid

### Step 5: Notify Stakeholders

```text
WAR ROOM: "ROLLBACK COMPLETE — Blue environment active"
```

Send update to TS24:

```text
📡 EKSTERN-POST // ALPHA-GUI → TS24
Emne: ROLLBACK EXECUTED — Green deploy reverted

Status: Blue environment restored
Reason: [P0/P1 description]
Impact: [Duration of incident]
Next steps: [Investigation plan]
```

### Step 6: Post-Rollback Actions

- [ ] Preserve Green environment logs for analysis
- [ ] Document incident in `docs/INCIDENT_LOG.md`
- [ ] Schedule post-mortem within 24 hours
- [ ] Update QA test cases if gap identified

## Emergency Contacts

| Role | Contact |
| --- | --- |
| Deploy Lead | `__NAME__` |
| TS24 Liaison | `__NAME__` |
| Infrastructure | `__NAME__` |

## Rollback Verification Script

```powershell
# Quick verification after rollback
$healthUrl = "https://[PRODUCTION_URL]/tools/sso_health.php"
$response = Invoke-WebRequest -Uri $healthUrl -UseBasicParsing -TimeoutSec 10
if ($response.StatusCode -eq 200) {
    Write-Host "✅ Health check passed" -ForegroundColor Green
} else {
    Write-Host "❌ Health check failed: $($response.StatusCode)" -ForegroundColor Red
}
```
