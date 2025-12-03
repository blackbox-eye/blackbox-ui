# Blue/Green Deployment Checklist — ALPHA-GUI

## Pre-Deploy Verification

- [ ] QA RUN Round 1 completed with PASS status
- [ ] `QA_RUN_R1_TEMPLATE.md` filled and archived
- [ ] TS24 confirmation received: `QA PASS — APPROVED FOR RELEASE`
- [ ] All P0/P1 issues resolved (or none found)
- [ ] WAR ROOM Stage 3 activated
- [ ] Rollback runbook reviewed by ops team

## Environment Preparation

- [ ] Blue environment (current production) tagged: `blue-prod-YYYYMMDD`
- [ ] Green environment provisioned and verified
- [ ] Database migrations (if any) applied to green
- [ ] Environment variables synced:
  - `QA_MODE=0` (production)
  - `BBX_SITE_BASE_URL` set correctly
  - `BBX_TS24_CONSOLE_URL` pointing to production TS24
- [ ] SSL certificates valid on green
- [ ] CDN/cache purge scheduled

## Deploy Execution

- [ ] Run FTP/FTPS sync to green environment
- [ ] Verify file integrity (checksum or manifest)
- [ ] Run `npm run build:tailwind` on green (if not pre-built)
- [ ] Health endpoint check: `/tools/sso_health.php`
- [ ] Smoke test: Agent login → Dashboard access
- [ ] Cookie integrity check: `gdi_sso_token` attributes (Secure, HttpOnly, SameSite=Lax)

## Traffic Switch

- [ ] Update load balancer / DNS to point to green
- [ ] Monitor error rates (5xx, 4xx) for 5 minutes
- [ ] Verify TS24 SSO redirect flow end-to-end
- [ ] Confirm no cookie desync warnings in logs

## Post-Deploy Verification

- [ ] Run `scripts/post-deploy-verify.ps1`
- [ ] Check Playwright smoke (optional): `npm run qa:gui:ci`
- [ ] Verify QA debug panel is **hidden** (QA_MODE=0)
- [ ] Review `[Blackbox-SSO]` logs for anomalies
- [ ] Confirm version badge NOT showing `-QA` suffix

## Rollback Trigger Criteria

| Condition | Action |
| --- | --- |
| 5xx error rate > 5% for 2 minutes | Immediate rollback to blue |
| SSO redirect failure rate > 10% | Immediate rollback |
| P0 bug reported within 15 minutes | Evaluate rollback |
| Cookie integrity failure detected | Immediate rollback |

## Sign-Off

- [ ] Deploy lead sign-off: `__NAME__` / `__DATE__`
- [ ] TS24 notified of successful deploy
- [ ] WAR ROOM Stage 3 transitioned to monitoring mode
