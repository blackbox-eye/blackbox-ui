# BLACKBOX UI PR Package And QA SOP

Status: standard PR package, QA, and merge-readiness operating procedure for `blackbox-eye/blackbox-ui`.

Last reviewed: 2026-05-14.

## 1. Standard PR package template

Use this template for any scoped PR unless a more specific template below is a better fit.

```md
## Summary
- <one-scope summary>

## Scope
- <what is included>
- <what is intentionally excluded>

## Files changed
- <path>
- <path>

## Owner decisions recorded
- <explicit owner approvals, boundaries, or deferrals>

## Validation
- <checks run>
- <checks not run and why>

## Risk map
- <primary risk>
- <rollback risk>

## Rollback / no-deploy note
- <rollback path or explicit no-deploy statement>

## Scope guard
- <why the diff stayed bounded>

## Status
- merged: NO
- live: NO
```

## 2. Docs-only PR template

```md
## Summary
- Docs-only governance or documentation change.

## Scope
- Included: <canonical doc or alignment docs>
- Excluded: runtime, workflows, configs, generated assets

## Files changed
- <path>

## Owner decisions recorded
- <docs-only approval boundary>

## Validation
- Markdown diagnostics on changed docs
- Changed-file scope check
- No app tests required because runtime and generated behavior are unchanged

## Risk map
- Low risk: documentation alignment only

## Rollback / no-deploy note
- No deploy
- Revert doc changes only if needed

## Scope guard
- No runtime, workflow, or config files changed

## Status
- merged: NO
- live: NO
```

## 3. Security PR template

```md
## Summary
- <one security surface hardening change>

## Scope
- Included: <one security surface>
- Excluded: unrelated UI, deploy, legal, or runtime cleanup

## Files changed
- <path>

## Owner decisions recorded
- <approved security boundary>

## Validation
- <targeted security-safe checks>
- <follow-up checks deferred>

## Risk map
- Surface addressed: <surface>
- Regression risk: <local risk>

## Rollback / no-deploy note
- <rollback plan>

## Scope guard
- One security surface only

## Status
- merged: NO
- live: NO
```

## 4. UI or bug PR template

```md
## Summary
- <one route, component, or state-contract fix>

## Scope
- Included: <one UI or bug surface>
- Excluded: unrelated runtime cleanup, deploy, security, legal

## Files changed
- <path>

## Owner decisions recorded
- <approved visual or behavior boundary>

## Validation
- <targeted viewport or behavior checks>
- <tests run>

## Risk map
- User-facing risk: <risk>
- Rollback risk: <risk>

## Rollback / no-deploy note
- <rollback path>

## Scope guard
- One route, component, or state contract only

## Status
- merged: NO
- live: NO
```

## 5. Deploy or governance PR template

```md
## Summary
- <one deploy-governance or operating-contract change>

## Scope
- Included: <docs or config surface approved>
- Excluded: unrelated runtime, UI, security, or legal work

## Files changed
- <path>

## Owner decisions recorded
- <deployment or governance boundary>

## Validation
- <documentation checks or safe config checks>
- <live validation status if applicable>

## Risk map
- Governance risk: <risk>
- Rollback risk: <risk>

## Rollback / no-deploy note
- <rollback plan or no-deploy statement>

## Scope guard
- Docs first, config later unless explicitly approved

## Status
- merged: NO
- live: NO
```

## 6. Copilot task prompt template

Use this template when handing work from ChatGPT or a human owner to Copilot.

```text
Project:
<repo>

Task:
<one bounded task>

Mode:
<documentation | bug fix | audit | security | UI>

Autonomy level:
<level>

Branch or branch intent:
<branch name or exact branch rule>

Allowed scope:
<included surfaces>

Forbidden scope:
<excluded surfaces>

Allowed files:
<paths>

Forbidden files:
<paths>

Allowed tools:
<tools>

Forbidden tools:
<tools>

Output required:
<exact final output>

Stop conditions:
<exact stop rules>

Validation expectations:
<checks required>

PR package expectation:
<recommended PR title/body or exact package rule>

Owner approval boundary:
<what requires explicit owner approval>
```

If any field above is missing and PR work is likely, the handoff is incomplete.

## 7. Copilot completion report template

```md
## Files inspected
- <path>

## Files changed
- <path>

## Summary
- <one-paragraph outcome>

## Governance rules added or applied
- <rule>

## Validation performed
- <check>

## Scope compliance
- <why the diff stayed in scope>

## Deferred items
- <deferred item or none>

## Recommended PR title
- <title>

## Recommended PR body
<ready-to-paste PR body>

## Branch name
- <branch>

## Commit message
- <message>
```

## 8. ChatGPT QA checklist

- The task package met the quality floor from [BLACKBOX_UI_WORKFLOW_LOCK.md](BLACKBOX_UI_WORKFLOW_LOCK.md).
- The bundle size matches the allowed PR model.
- The diff stayed within approved files.
- The PR body contains all mandatory fields.
- Validation matches the actual touched surface.
- Manual QA was not requested unless the boundary in Section 11 required it.
- No deploy, legal, security, UI, and runtime scopes were silently mixed.
- No Copilot or ChatGPT self-approval language appears.
- Owner decisions are recorded clearly.

## 9. Final merge-readiness checklist

- Branch name is correct.
- PR title matches one scoped task.
- PR body is complete before review.
- Files changed match the approved task package.
- Validation evidence is attached or summarized.
- Risks and rollback are stated.
- Scope guard is explicit.
- `merged: NO` and `live: NO` are present before merge.
- Required owner approval is recorded.
- No self-approval or self-merge occurred.

## 10. Post-merge checkpoint checklist

- Record merged commit or PR number if the change becomes canonical governance or baseline documentation.
- Update any canonical source-of-truth doc affected by the merge.
- If live behavior changed, record whether a live checkpoint is required.
- If no deploy occurred, keep the no-deploy record explicit.
- Create follow-up tasks only for intentionally deferred items.

## 11. Do not ask owner for extra manual QA unless rule

- Do not ask the owner for extra manual QA unless runtime behavior changed, UI layout changed, accessibility behavior changed, browser-specific behavior changed, security surface changed, or deploy/live behavior changed.
- Do not ask for repeat manual QA when an unchanged surface already has adequate current evidence.
- Docs-only PRs should not request manual site QA.

## 12. If the PR screen opens, the PR body must already be ready rule

- If the GitHub PR screen opens, the PR body must already be written.
- Empty PR bodies, placeholder PR bodies, and `TBD` PR bodies are not allowed.
- If the package is not ready, stop and finish the package before the PR is opened.

## 13. Exact fields every PR body must include

Every PR body must include these exact fields or clearly equivalent headings:

- Summary
- Scope
- Files changed
- Owner decisions recorded
- Validation
- Risk map
- Rollback / no-deploy note
- Scope guard
- Status: `merged NO` / `live NO`
