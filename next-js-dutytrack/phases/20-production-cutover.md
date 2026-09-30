# Phase 20 — Production cutover and readiness

## Goal

Cut over to Next.js with backup, smoke tests and rollback readiness.

## Why This Phase Exists

This phase is required before dependent work because it will cut over to next.js with backup, smoke tests and rollback readiness. It starts after 19 rehearsal; release approval.

## Prerequisites

19 rehearsal; release approval.

## Existing Laravel Reference

DEPLOYMENT.md, rehearsal report, source deployment/config and all phase acceptance records.

## Scope

Backup, write freeze, final delta, deploy/switch, five-role smoke and observation.

## Out of Scope

No new features or schema redesign during cutover.

## Tasks

- [ ] Confirm owner/window/comms/backup/rollback trigger.
- [ ] Freeze Laravel writes and import final delta.
- [ ] Deploy signed build and reviewed migrations.
- [ ] Smoke role routing, attendance, approvals, evaluations, reports, files, chat and audit.
- [ ] Monitor errors/DB/email/Storage/Realtime.
- [ ] Reconcile writes if rollback.
- [ ] Record acceptance and retention decision.

## Expected Files

Deployment runbook, final reconciliation/acceptance record.

## Database Changes

Final production import and reviewed migrations.

## Roles / Permissions

Five roles and anonymous.

## Business Rules

Accepted parity behaviors after traffic switch.

## Edge Cases

Writes after switch complicate rollback.

## Security Checks

TLS, backups, RLS, secrets and incident access verified.

## Testing

Unit/integration release build; permission safe production smoke; database counts; UI critical paths; regression staging full.

## Verification Checklist

- [ ] Execute signed cutover checklist and post-release smoke with rollback ready.

## Definition of Done

Owner accepts parity, monitoring stable and rollback window closed explicitly.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
