# Phase 08 — Requirements

## Goal

Migrate nine physical OJT checklist rows and state handoff.

## Why This Phase Exists

This phase is required before dependent work because it will migrate nine physical ojt checklist rows and state handoff. It starts after 06 directories; 07 only for related UI links.

## Prerequisites

06 directories; 07 only for related UI links.

## Existing Laravel Reference

StudentRequirement model, student dashboard submitRequirement, coordinator requirements component/page.

## Scope

Student marks submitted; coordinator receives/unreceives; templates and counts.

## Out of Scope

No upload approvals or attendance gate.

## Tasks

- [ ] Port exact nine keys/order/steps and static templates.
- [ ] Ensure rows idempotently.
- [ ] Implement student submitted_at once.
- [ ] Implement coordinator completed_by/at toggle.
- [ ] Test counts and cross-owner denial.

## Expected Files

features/requirements, coordinator route, public template assets.

## Database Changes

student_requirements, unique student/key, RLS.

## Roles / Permissions

Student own and coordinator managed students.

## Business Rules

Pending requirements do not block OJT.

## Edge Cases

Inactive old keys may exist; physical copy is required for check.

## Security Checks

Student cannot self-complete.

## Testing

Unit definitions; integration submit/toggle; permission owner; database idempotency; UI checklist; regression directory.

## Verification Checklist

- [ ] Student submits, coordinator checks/unchecks, verify counts and download template.

## Definition of Done

Nine definitions and transitions match Laravel.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
