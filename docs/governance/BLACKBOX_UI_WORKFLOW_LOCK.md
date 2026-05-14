# BLACKBOX UI Workflow Lock

Status: canonical ChatGPT, Copilot, and user workflow governance for `blackbox-eye/blackbox-ui`.

Last reviewed: 2026-05-14.

## 1. Purpose and authority

This document is the canonical workflow governance lock for repository task execution, PR packaging, QA handoff, merge control, and scope discipline.

Authority order for workflow decisions:

1. Explicit owner-approved scope and approval boundaries in the active task handoff.
2. This document.
3. [AGENTS.md](../../AGENTS.md).
4. Canonical repo docs referenced by this document, including [../DEPLOYMENT_SOURCE_OF_TRUTH.md](../DEPLOYMENT_SOURCE_OF_TRUTH.md) and [BLACKBOX_UI_PR_PACKAGE_AND_QA_SOP.md](BLACKBOX_UI_PR_PACKAGE_AND_QA_SOP.md).
5. Memory, chat summaries, and temporary notes as operational cache only.

If these sources conflict and the conflict is not already owner-resolved, execution must stop and the conflict must be reported.

## 2. ChatGPT / Copilot / User role split

- User or owner defines the approval boundary, allowed scope, forbidden scope, branch or PR intent, and merge authority.
- ChatGPT prepares or reviews bounded task packages, checks evidence quality, and performs QA review. ChatGPT must not self-approve merge or represent owner approval.
- Copilot executes only within the approved task package, reports exact scope and validation, and must refuse incomplete handoffs.
- Neither ChatGPT nor Copilot may treat prior chat output, prior memory, or prior assistant suggestions as owner approval.

## 3. Primary execution workflow

1. Prepare a task package that meets the minimum quality floor in Section 7.
2. Validate the package before editing any files.
3. Create or confirm one task branch for one task.
4. Execute one bounded scope only.
5. Validate only the changed slice using the minimum necessary checks.
6. Prepare the final PR package before opening the PR screen.
7. Run ChatGPT QA or equivalent review against the final package.
8. Record owner decisions in the PR body or linked canonical docs.
9. Merge only after owner approval.
10. Create a post-merge checkpoint only when the task changes canonical governance, deploy ownership, security baseline, or other durable operating contracts.

## 4. No workflow drift rule

- Workflow rules must not live only in temporary chat instructions, memory, or PR comments.
- If a new workflow rule becomes durable, encode it in this document, [AGENTS.md](../../AGENTS.md), or another canonical repo doc in the same PR.
- Copilot must not compensate for missing governance by guessing missing scope, PR packaging, QA gates, or approval boundaries.
- If the active handoff is smaller than the required governance package, Copilot must stop and report `handoff incomplete`.

## 5. Branch, commit, PR, and merge rules

- One task equals one branch and one PR unless the owner explicitly approves a bundled wave.
- Direct pushes to `main` are not allowed.
- Branch naming must be clear before substantive execution starts, or the handoff must state branch intent explicitly.
- Commit messages must describe only the scoped change set.
- If the GitHub PR screen opens, the PR body must already be ready.
- Empty, placeholder, or `TBD` PR bodies are not allowed.
- Copilot and ChatGPT may recommend merge readiness but must not self-approve, self-merge, or claim owner approval.
- Merge without owner approval is not allowed.

## 6. Mandatory bundle-size model

- Normal docs PR: maximum 1 canonical doc and maximum 2 alignment docs.
- Governance lock PR: may include 2 canonical governance docs, [AGENTS.md](../../AGENTS.md), [README.md](../../README.md), and one active instruction-pointer update if an active repo-local instruction file already exists.
- UI or bug PR: one route, one component, or one state contract only.
- Security PR: one security surface only.
- Deploy or governance PR: docs first, config later unless the owner explicitly approves a later implementation wave.
- Legal, claims, deploy, security, UI, and runtime scopes must not be mixed in one PR unless the owner explicitly approves a bundled wave.
- If the planned change exceeds this bundle-size model, the work must be split before editing starts.

## 7. Minimum task-package quality floor

A future ChatGPT-to-Copilot handoff is incomplete unless it includes all of the following:

- project
- task
- mode
- autonomy level
- branch name or branch intent
- allowed scope
- forbidden scope
- allowed files and tools
- forbidden files and tools
- output required
- stop conditions
- validation expectations
- PR package expectation when PR work is likely
- owner approval boundary

If any of these are missing, Copilot must stop and request a corrected handoff.

## 8. Mandatory final PR package rule

Every PR must ship with a final package before review starts.

The package must include:

- summary
- scope
- files changed
- owner decisions recorded
- validation
- risk map
- rollback or no-deploy note
- scope guard
- status lines showing `merged: NO` and `live: NO`

The PR body must match the actual changed files and actual validation, not a planned or aspirational package.

## 9. Copilot refusal rule for incomplete prompts

- If the prompt misses any item from Section 7, Copilot must not edit files.
- Copilot must respond with `handoff incomplete`, identify the missing fields, and request a corrected prompt.
- Copilot must not infer missing allowed files, forbidden files, merge rules, QA gates, or owner approval boundaries by guesswork.

## 10. Stop conditions

Execution must stop and report when any of the following occurs:

- more than the allowed files are needed
- any runtime, config, workflow, or code file becomes necessary for the requested outcome
- legal, claims, CCS, deploy implementation, security implementation, UI, and runtime scopes begin to mix without explicit owner approval
- the branch or PR intent is unclear when PR work is likely
- the owner approval boundary is missing or contradictory
- the active repo-local Copilot instruction file cannot be identified safely when the task requires editing it
- canonical repo docs and active handoff evidence materially conflict
- the required PR package or validation boundary cannot be met within scope

## 11. Manual QA boundary

- Do not ask the owner for extra manual QA unless runtime behavior changed, responsive UI changed, accessibility behavior changed, live deploy behavior changed, security surface changed, browser-specific behavior changed, or automated evidence is insufficient.
- Repeated manual QA is not allowed when the validated surface is unchanged and no new evidence invalidates the prior result.
- Docs-only and governance-only work does not require manual site QA.

## 12. Docs-only validation rules

- Run markdown diagnostics on new or changed docs.
- Confirm that changed files stay within the approved docs-only set.
- Do not fix pre-existing markdown warnings in older docs unless the current PR introduced them.
- No app tests are required when runtime behavior, generated assets, and deployment/config behavior are unchanged.
- A diff or status check is required to prove the final changed-file set stays in scope.

## 13. Forbidden scope map

- UI and runtime scope: PHP routes, templates, CSS, JS, and interactive behavior.
- Security implementation scope: live headers, auth code, server rules, endpoint behavior, and secrets handling.
- Deploy implementation scope: workflow files, hosting config, deploy scripts, `.htaccess`, and `.htaccess.production`.
- Legal and claims scope: marketing claims, compliance promises, legal text, and customer-facing assurances.
- Generated-output scope: minified assets, build artifacts, lockfiles, and generated reports.
- Mixed-wave scope: any PR that combines the categories above without explicit owner approval.

Documentation may reference these areas, but may not modify them unless they are explicitly included in the approved scope.

## 14. GitHub UI verification checklist

- Base branch is correct.
- Head branch is correct.
- PR title reflects one scoped task.
- PR body is fully prepared before the PR screen is opened.
- The PR body contains Summary, Scope, Files changed, Owner decisions recorded, Validation, Risk map, Rollback or no-deploy note, Scope guard, and Status with `merged: NO` and `live: NO`.
- Draft versus ready-for-review state is intentional.
- Files changed in GitHub match the approved bundle-size model.
- No unrelated commits or unrelated files appear in the PR.
- ChatGPT or Copilot has not self-approved the PR.
- Owner approval is recorded before merge.

## 15. Copilot Memory guidance

- Memory is operational cache only.
- Canonical operating rules live in repo docs and [AGENTS.md](../../AGENTS.md).
- Memory may contain transient context such as current branch, current blockers, recent validation commands, and repeated repo naming patterns.
- Memory must never be the only source for scope rules, merge authorization, PR body requirements, deployment source-of-truth, security decisions, legal wording, or canonical QA gates.
- If memory conflicts with canonical repo docs, repo docs win and the mismatch must be reported.

## 16. Current project baseline

- PR #128: technical checkpoint A-E is recorded in [../audits/BLACKBOX_UI_TECHNICAL_AUDIT_CHECKPOINT_A_E_v1.md](../audits/BLACKBOX_UI_TECHNICAL_AUDIT_CHECKPOINT_A_E_v1.md).
- PR #129: block public internal paths is the current server-hardening baseline referenced by [../audits/BLACKBOX_UI_PHASE_F_POST_DEPLOY_VALIDATION_PR129_v1.md](../audits/BLACKBOX_UI_PHASE_F_POST_DEPLOY_VALIDATION_PR129_v1.md).
- PR #130: Phase F validation checkpoint is recorded in [../audits/BLACKBOX_UI_PHASE_F_POST_DEPLOY_VALIDATION_PR129_v1.md](../audits/BLACKBOX_UI_PHASE_F_POST_DEPLOY_VALIDATION_PR129_v1.md).
- PR #131: production deployment source-of-truth is recorded in [../DEPLOYMENT_SOURCE_OF_TRUTH.md](../DEPLOYMENT_SOURCE_OF_TRUTH.md).

Future task packages must treat these four items as the current governance and deployment baseline unless a later owner-approved checkpoint supersedes them.
