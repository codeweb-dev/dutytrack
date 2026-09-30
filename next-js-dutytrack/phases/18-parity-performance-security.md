# Phase 18 — Parity, performance and security

## Goal

Prove behavior, access, accessibility and performance before cutover.

## Why This Phase Exists

This phase is required before dependent work because it will prove behavior, access, accessibility and performance before cutover. It starts after 01–17 complete.

## Prerequisites

01–17 complete.

## Existing Laravel Reference

tests/Feature/*, role matrix, security plan and all routed Laravel modules.

## Scope

Full regression, RLS, Storage, Realtime, accessibility and representative load.

## Out of Scope

No redesign or data cutover.

## Tasks

- [ ] Run target and Laravel parity cases.
- [ ] Test every action own/related/unrelated/direct.
- [ ] Review secrets, CSRF, uploads, exports, audit and rate limits.
- [ ] Test keyboard/mobile/dark/error states.
- [ ] Measure dashboard/list/export/chat latency.
- [ ] Record accepted deviations.

## Expected Files

tests/* and focused policy/index fixes.

## Database Changes

Only evidence-based policy/index corrections.

## Roles / Permissions

All roles and anonymous.

## Business Rules

No unexplained behavior drift.

## Edge Cases

Production-size data may reveal N+1 or filter mismatch.

## Security Checks

Independent negative tests and security review.

## Testing

Unit/integration all; permission full matrix; database RLS; UI E2E/accessibility; regression full.

## Verification Checklist

- [ ] Follow critical path per role plus export/upload/chat failure state.

## Definition of Done

Release gates pass and accepted deviations recorded.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
