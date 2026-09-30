# Phase 07 — OJT assignments

## Goal

Migrate coordinator placements and admin monitoring.

## Why This Phase Exists

This phase is required before dependent work because it will migrate coordinator placements and admin monitoring. It starts after 06 directories.

## Prerequisites

06 directories.

## Existing Laravel Reference

Coordinator/OjtAssignmentForm, coordinator assignment components, admin/coordinator assignment pages, CoordinatorOjtAssignmentTest.

## Scope

Coordinator own-student create/edit/delete; internal/external assignee/status; admin read/filter.

## Out of Scope

No attendance, tasks or chat.

## Tasks

- [ ] Port conditional validation and four statuses.
- [ ] Enforce unique student assignment.
- [ ] Recheck coordinator ownership and assignee role for each action.
- [ ] Write audit atomically.
- [ ] Test internal-to-external switches and duplicate student.

## Expected Files

features/assignments, coordinator/admin routes, SQL policies.

## Database Changes

ojt_assignments, audit_logs, one-student unique constraint.

## Roles / Permissions

Coordinator manages own; admin monitors.

## Business Rules

Internal professor only, external supervisor only.

## Edge Cases

Reassignment may change historical visibility.

## Security Checks

Server and RLS ownership; no forged assignee.

## Testing

Unit conditional schema; integration CRUD; permission other coordinator; database unique/FK; UI forms; regression directory.

## Verification Checklist

- [ ] Create both types, reject both-assignee and duplicate student, edit/delete and inspect audit.

## Definition of Done

Placement parity and denial tests pass.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
