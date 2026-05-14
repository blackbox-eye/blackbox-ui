# Legal Entity And Disclosures Baseline

Status: owner-confirmed governance baseline for legal/entity disclosure handling in `blackbox-eye/blackbox-ui`.

Last reviewed: 2026-05-14.

This document is governance baseline only. It is not legal advice and does not itself change public legal copy.

## Purpose

This document records the current legal/entity disclosure baseline so future ChatGPT, Copilot, and owner-approved PRs do not invent entity details, Danish registration claims, or compliance equivalence that has not been verified.

## Owner-confirmed baseline

- Current `Blackbox Codes ApS` wording is incorrect.
- The owning entity is the Dubai company that owns the technology, IP, and platform.
- The Dubai company name is pending owner-provided evidence.
- The Dubai trade license number is pending owner-provided evidence.
- The Dubai physical business address is pending owner-provided evidence.
- Dubai trade-license disclosure must not be treated as automatically equivalent to Danish CVR disclosure.
- A Swiss branch or company registration may be added later.
- Public legal page changes must wait for owner-provided entity details.

## Current incorrect or at-risk repo surfaces

The following currently create governance risk and must not be treated as verified legal baseline:

- [terms.php](../../terms.php) states that Blackbox EYE is operated by `Blackbox Codes ApS`.
- [terms.php](../../terms.php) states that IP belongs to `Blackbox Codes ApS`.
- [terms.php](../../terms.php) contains a `CVR` placeholder.
- [terms.php](../../terms.php) lists `Schweiz / UAE` as address text without owner-verified entity details.
- [privacy.php](../../privacy.php) contains GDPR controller and legal-basis wording that may require later legal/compliance review once the entity record is finalized.

These surfaces are evidence of current copy risk only. They are not authorization to edit the public pages in this governance baseline PR.

## Pending evidence list

The following evidence is still required before public legal/entity copy may be updated:

- Exact Dubai legal entity name
- Dubai trade license number or equivalent official registration identifier
- Physical business address for the owning Dubai entity
- Owner-confirmed disclosure format for how the Dubai entity should be named on public legal pages
- Owner-confirmed guidance on whether and how a Swiss branch or company should be disclosed
- Legal or compliance review confirming how privacy-controller and governing-law disclosures should be updated once the entity record is complete

## Public legal copy change gate

Public legal-page or disclosure copy must not be changed until all of the following are true:

- owner evidence for the Dubai entity details is provided
- the disclosure format is owner-confirmed
- any required legal or compliance review is complete
- the follow-up PR is explicitly approved as public legal-copy work

Until those conditions are met, governance docs may record the baseline, but public pages must remain unchanged.

## What must not be claimed yet

- Do not claim `Blackbox Codes ApS` as the operating or owning entity.
- Do not claim a Danish ApS structure.
- Do not claim a Danish CVR number.
- Do not claim that Dubai trade-license disclosure is legally equivalent to Danish CVR disclosure.
- Do not claim Swiss registration details unless owner evidence exists.
- Do not claim final legal-controller wording, governing-law validity, or registration completeness beyond the owner-confirmed baseline above.

## Swiss entity future note

The owner has indicated that a Swiss branch or company registration may be added later. That future possibility is not current proof of Swiss legal status and must remain documented as pending until owner evidence exists.

## Risk if left unresolved

- Contracting-party ambiguity on public legal pages
- Incorrect entity and IP ownership disclosures
- Privacy-controller ambiguity
- Compliance and trust claims drifting beyond verified evidence
- PR churn caused by repeated attempts to patch public legal copy before the owner record is complete

## Deferred follow-up PRs

- Public legal-page entity correction PR after owner evidence is provided
- Privacy disclosure alignment PR after legal/compliance review
- Swiss entity disclosure PR only if owner evidence is later approved
