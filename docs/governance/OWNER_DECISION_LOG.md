# Owner Decision Log

Status: canonical owner-decision record for governance baselines that affect `blackbox-eye/blackbox-ui` documentation and future PR scope.

Last reviewed: 2026-05-14.

## D20: Legal entity baseline

- Decision ID: D20
- Area: legal entity and disclosures
- Owner-confirmed: the owning entity is the Dubai company that owns the technology, IP, and platform
- Not verified yet: exact Dubai company name, trade license number, and physical business address
- Guardrail: do not claim Danish ApS or Danish CVR
- Guardrail: do not update public legal pages until owner-provided entity details exist
- Future note: Swiss branch or company registration may be added later
- Follow-up required: yes, public legal/disclosure PR after owner evidence

## D21: CCS status baseline

- Decision ID: D21
- Area: CCS access, activation, and public posture
- Owner-confirmed: CCS login must remain gated
- Owner-confirmed: CCS must stay behind MFA or 2FA
- Owner-confirmed: full activation requires a separate security test
- Not verified yet: certified settlement infrastructure, payment readiness, production activation status, or public claims implying fully active verified infrastructure
- Guardrail: public wording must not imply fully active certified settlement infrastructure unless separately verified
- Follow-up required: yes, separate security and claims PRs if activation is later approved

## D22: Claims rewrite direction

- Decision ID: D22
- Area: public claims and trust language
- Owner-confirmed: strategic direction is approved, but legal and compliance verification remains pending
- Owner-confirmed: `Enterprise Grade Security` must be rewritten into process-oriented wording unless evidence is later provided
- Owner-confirmed: certification wording must not say certified unless certificate evidence exists
- Owner-confirmed: `24/7 Incident Response` means package-based customer availability, not open-ended support for all inbound messages
- Owner-confirmed: `GDPR Compliant` remains an intended objective and requires full compliance audit before it can be treated as verified public claim
- Allowed direction: `developed with reference to`, `mapped against`, `aligned toward`, `under preparation`
- Follow-up required: yes, claims-rewrite PR after verification scope is approved
