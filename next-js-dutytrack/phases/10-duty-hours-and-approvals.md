# Phase 10 — Duty hours and approvals

## Goal

Migrate five role duty-hour views and reviewer decisions.

## Why This Phase Exists

This phase is required before dependent work because it will migrate five role duty-hour views and reviewer decisions. It starts after 09 attendance.

## Prerequisites

09 attendance.

## Existing Laravel Reference

Five role duty-hours pages, DutyHourLog, HourFormatter.

## Scope

Student history, assigned reviewer approve/reject, admin/coordinator monitors and filters.

## Out of Scope

No manual hour entry unless source proves it.

## Tasks

- [ ] Confirm coordinator monitor scope.
- [ ] Port status/approved fields.
- [ ] Recheck reviewer assignment.
- [ ] Preserve or explicitly change repeat-action behavior.
- [ ] Build filters and summaries.
- [ ] Test unrelated reviewer and totals.

## Expected Files

features/duty-hours, five role routes, SQL policies.

## Database Changes

duty_hour_logs and assignment relations.

## Roles / Permissions

Student own; professor/supervisor assigned approve; admin/coordinator monitor.

## Business Rules

Approve rendered hours; reject zero; pending from attendance.

## Edge Cases

Reassignment and repeat approvals can alter history.

## Security Checks

Atomic reviewer fields and RLS; client cannot set approved hours.

## Testing

Unit total formatting; integration approve/reject; permission unrelated reviewer; database decimal/status; UI filters; regression attendance.

## Verification Checklist

- [ ] Approve/reject linked rows, inspect student totals and denied unrelated user.

## Definition of Done

All five views and actions match reviewed scope.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
