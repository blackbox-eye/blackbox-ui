# AGENTS.md — blackbox-ui

## Purpose

This repository is used for scoped frontend and UI maintenance tasks on the Blackbox EYE public website.

This file defines the mandatory operating contract for AI coding agents working in this repository.

Agents must keep tasks small, isolated, testable, and reviewable.

## Core operating rules

- One task = one branch = one pull request.
- Do not reuse an old branch or old PR for a new task unless explicitly instructed.
- Do not change `main` directly.
- Do not expand scope.
- Do not perform unrelated cleanup.
- Do not perform broad refactors.
- Do not make visual/design changes unless the task explicitly requests them.
- Do not add new helper scripts unless explicitly requested.
- Do not commit temporary diagnostic scripts.
- Do not touch secrets, environment files, deployment files, or hosting configuration.

## Task categories

Allowed task categories include:

1. narrow bug fixes
2. narrow UI regression fixes
3. narrow accessibility fixes
4. narrow test additions
5. narrow documentation updates
6. scoped frontend component improvements

Every task must clearly state which category it belongs to.

## Required pre-code plan

Before editing files, produce a short plan containing:

1. root-cause hypothesis
2. exact files expected to change
3. exact files that must not change
4. acceptance criteria
5. tests/checks to run
6. rollback risk

Do not start coding until the plan is approved, unless the task explicitly says execution is already approved.

## Scope discipline

Only edit files directly required by the task.

If the requested fix appears to require touching more than five files, stop and report:

- why more files are needed
- which files
- whether the task should be split into smaller PRs

## Forbidden areas unless explicitly requested

Do not touch:

- authentication
- SSO
- login / console selector / agent-access flows
- chat assistant / Alphabot / EYE assistant
- copy/content rewrites
- IA/navigation redesign
- package dependencies
- lockfiles
- CI/CD workflows
- deployment logic
- secrets/env files
- routing cleanup
- repo restructuring
- broad CSS cleanup
- unrelated styling polish
- “while here” improvements

## CSS rules

CSS changes must be minimal and local to the issue.

Prefer:

- one state contract
- one source of truth
- smallest conflicting rule fix
- scoped selectors
- predictable cascade
- existing tokens/classes when available

Avoid:

- global `!important` dumps
- duplicate state systems
- inline style enforcement from JavaScript
- broad visual rewrites
- unrelated design cleanup
- changing generated CSS unless required by the repo build process

If generated CSS must change, state why.

## JavaScript rules

JavaScript may toggle classes and ARIA state.

JavaScript must not force layout with inline styles unless explicitly approved.

Preserve accessibility state where relevant:

- `aria-expanded`
- `aria-hidden`
- `aria-controls`
- focus behavior
- keyboard access

Avoid duplicate event handlers and competing state logic.

## Mobile navigation / burger menu contract

For mobile drawer or burger menu fixes, prefer a single open-state contract:

- `#mobile-menu.active`
- `#mobile-menu-overlay.active`
- `body.mobile-menu-open` only if already required by existing code

Do not mix `.active`, `.is-open`, `.is-visible`, `[aria-hidden="false"]`, and inline styles unless the task explicitly requires compatibility with existing code.

If mixed state systems already exist, normalize only the smallest set needed for the current bug.

## Header / navigation rules

When touching header or navigation:

Must preserve:

- desktop navigation
- mobile burger visibility
- language switch behavior
- theme toggle behavior
- console/login dropdown behavior unless explicitly out of scope
- header height stability
- breadcrumbs visibility
- scroll behavior

Do not redesign navigation unless explicitly requested.

## Test requirements

Before marking a PR ready, run the smallest relevant test set.

For UI/header/mobile navigation work, validate at minimum:

- 320px mobile viewport
- 390px mobile viewport
- 768px tablet viewport
- 1024px desktop threshold
- desktop width above 1280px when desktop navigation is affected

For mobile burger menu work, verify:

- menu is hidden on initial load
- burger opens menu
- overlay becomes visible and clickable
- close button closes menu
- overlay click closes menu
- menu links are clickable
- desktop nav still works
- no header clipping
- no scroll lock regression

If tests cannot be run, state exactly why.

## Pull request requirements

Every PR must include:

1. short bug summary
2. root-cause summary
3. exact files changed
4. why the diff is minimal
5. tests/checks run
6. target pages and viewports validated
7. what was intentionally not changed
8. risk level
9. rollback note

## Required final response format

When finished, return only:

- PR link
- exact changed file list
- checks status
- target pages/viewports validated
- blockers, if any
- merged: NO
- live: NO

## Stop rules

Stop and return for review if:

- root cause is unclear
- the fix needs broad refactor
- more than five files are required
- tests fail twice
- the task conflicts with this AGENTS.md
- unrelated files are being pulled into the diff
- generated artifacts are unclear
- the branch becomes polluted with temporary scripts

## Branch and PR naming

Use clear names.

Examples:

- `fix/mobile-burger-open-state`
- `fix/header-breadcrumb-spacing`
- `fix/desktop-more-dropdown`
- `test/mobile-nav-smoke`
- `docs/update-agents-contract`

PR titles should be specific.

Examples:

- `fix(ui): mobile burger menu open state`
- `fix(ui): header spacing prevents breadcrumb clipping`
- `test(ui): add mobile navigation smoke coverage`

## Human review rule

A passing CI run is required but not sufficient.

Do not claim the issue is fixed unless:

- the target behavior is validated
- the changed files are listed
- the diff is scoped
- the acceptance criteria are satisfied