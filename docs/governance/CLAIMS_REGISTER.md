# Claims Register

Status: owner-confirmed claims-governance baseline for `blackbox-eye/blackbox-ui`.

Last reviewed: 2026-05-14.

This register separates owner-confirmed strategic direction from verification status. It is governance baseline only and does not authorize public copy changes by itself.

## Claims table

| Claim | Current risk | Owner decision | Allowed wording direction | Verification needed | Status | Implementation PR needed |
| --- | --- | --- | --- | --- | --- | --- |
| Enterprise Grade Security | High risk because current wording can imply verified security maturity beyond current evidence | Rewrite into process-oriented wording unless evidence is later provided | `developed with reference to`, `mapped against`, `aligned toward`, `under preparation` | Security and evidence review for any stronger public claim | Rewrite pending | YES |
| ISO/IEC / certification wording | High risk because certified wording implies formal certificate evidence | Do not say certified unless certificate evidence exists | Use preparation or mapping language only until certificates are verified | Certificate evidence and scope review for each cited standard | Restricted pending evidence | YES |
| 24/7 Incident Response | Medium to high risk because it can imply always-on instant support for all inbound contact | Means subscription or package-based incident response availability for customers, not open-ended instant support | Use service-package or contracted-availability wording | Service-definition review and support-operating-model confirmation | Rewrite pending | YES |
| GDPR Compliant | High risk because it implies verified legal/compliance status | Treat as intended compliance objective only until a full compliance audit is completed | Use objective-oriented wording such as `aligned toward GDPR obligations` or `compliance preparation in progress` | Full compliance audit and legal review | Verification pending | YES |
| CCS settlement / certification / security wording | High risk because public copy can imply production-ready, certified, or fully active financial infrastructure | CCS must remain gated, behind MFA or 2FA, and not be described as fully active certified settlement infrastructure unless separately verified | Use gated-preview and security-test-pending wording only | Separate security test, activation review, and evidence for any certification or settlement-readiness claims | Restricted pending activation evidence | YES |

## Allowed wording notes

- `developed with reference to`
- `mapped against`
- `aligned toward`
- `under preparation`

These examples are allowed direction only. They are not approval to update public copy without a separate scoped PR.

## Not verified baseline

- No certification claim is verified unless supporting certificate evidence exists.
- No GDPR compliance claim is locked as verified until a full compliance audit is completed.
- No CCS settlement, payment, or security infrastructure claim is locked as production-ready or certified until separately verified.

## Deferred follow-up PRs

- Marketing and public-copy claims rewrite PR
- Privacy and compliance wording alignment PR after audit/legal review
- CCS wording cleanup PR after security activation test scope is approved
