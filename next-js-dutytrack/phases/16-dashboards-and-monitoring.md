# Phase 16 — Dashboards and monitoring

## Goal

Migrate five dashboards and read-only monitoring views.

## Why This Phase Exists

This phase is required before dependent work because it will migrate five dashboards and read-only monitoring views. It starts after 06–15 relevant modules.

## Prerequisites

06–15 relevant modules.

## Existing Laravel Reference

Five dashboard Blade classes; admin/coordinator monitoring and staff assigned-students pages.

## Scope

Cards, trends, recent lists, assignment/attendance/duty/evaluation monitors and filters.

## Out of Scope

No new analytics service.

## Tasks

- [ ] List every current metric/query/date window.
- [ ] Port server aggregates with role scope and pagination.
- [ ] Build monitor lists and filters.
- [ ] Compare totals on shared fixtures.
- [ ] Resolve coordinator duty scope.
- [ ] Measure query plans and index only measured bottlenecks.

## Expected Files

features/dashboard, role dashboard/monitor routes.

## Database Changes

Read-only indexes only if justified.

## Roles / Permissions

All five roles; admin broad monitor.

## Business Rules

Rendered vs approved totals and source status counts.

## Edge Cases

Empty month and timezone boundaries.

## Security Checks

No cross-user cache; RLS on aggregate inputs.

## Testing

Unit period math; integration fixture metrics; permission scope; database query plans; UI filters; regression modules.

## Verification Checklist

- [ ] Compare card/list values per role/date to Laravel fixtures.

## Definition of Done

Every routed metric/list matches or has signed deviation.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
