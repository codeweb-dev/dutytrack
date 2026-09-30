# Phase 19 — Data migration rehearsal

## Goal

Prove repeatable import, file transfer and reconciliation.

## Why This Phase Exists

This phase is required before dependent work because it will prove repeatable import, file transfer and reconciliation. It starts after 18 parity/security; approved data plan.

## Prerequisites

18 parity/security; approved data plan.

## Existing Laravel Reference

database/migrations/*, all models, read-only production snapshot, DATABASE_MIGRATION.md.

## Scope

Disposable full rehearsal, Auth/legacy map, files, counts/hashes, restart and rollback timing.

## Out of Scope

No production cutover or source writes.

## Tasks

- [ ] Snapshot/hash source securely.
- [ ] Import parent-to-child with deterministic IDs.
- [ ] Validate counts/statuses/FKs/totals/files/docs.
- [ ] Test restart and partial failure.
- [ ] Measure freeze/delta/rollback windows.
- [ ] Obtain password/MFA decision signoff.

## Expected Files

Migration scripts/runbooks and reconciliation report during future phase.

## Database Changes

Staging domain tables, Auth and Storage only.

## Roles / Permissions

All roles tested on imported accounts.

## Business Rules

Preserve timestamps, decimals and relationships.

## Edge Cases

Password hash compatibility, missing files, orphans and timezone.

## Security Checks

Encrypt snapshots, least privilege, no PII in logs.

## Testing

Unit mapper; integration import; permission imported roles; database counts/FK/hash; UI sampled flows; regression reports.

## Verification Checklist

- [ ] Repeat rehearsal twice from clean snapshot and compare metrics.

## Definition of Done

Reconciliation clean or exceptions accepted; cutover plan signed.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
