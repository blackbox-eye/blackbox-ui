# AGENTS.md — blackbox-ui / Jules Pilot 1

## Purpose
This repository is currently used for a tightly scoped Jules pilot.

## Pilot 1 only
Jules may only work on:
- homepage hero / first-interaction scroll-sticky bug

## Goal
Prove that Jules can solve one narrow, reviewable frontend bug with:
- minimal diff
- clean PR
- no scope expansion

## Allowed scope
Only touch code directly required for:
- homepage hero behavior
- first-interaction scroll behavior
- sticky behavior related to the homepage hero/header transition

Allowed file types:
- existing homepage template/view files
- existing CSS files directly related to hero/header/scroll/sticky behavior
- existing JS/TS files directly related to hero/header/scroll/sticky behavior
- at most one narrow existing test adjustment, only if strictly necessary to validate the fix

## Forbidden scope
Do not touch:
- chat assistant
- SSO
- login / choose console / console separation
- IA cleanup
- copy/content rewrite
- package.json
- README.md
- CI/CD workflows
- deploy logic
- secrets/env
- routing cleanup
- repo restructuring
- broad refactors
- unrelated styling cleanup
- “while here” improvements

## Change discipline
- smallest safe fix only
- no new feature work
- no hidden refactor
- no dependency changes
- no file moves/renames unless explicitly required by the bug fix
- no edits outside direct bug path

## Required PR output
The PR must include:
1. short bug summary
2. root-cause summary
3. exact files changed
4. why the diff is minimal
5. before/after screenshots
6. what was intentionally not changed

## Stop rules
Stop and return for review if the fix appears to require:
- backend/service changes
- SSO/auth changes
- chat/integration changes
- navigation/IA redesign
- workflow/deploy/config cleanup
- broader homepage redesign
