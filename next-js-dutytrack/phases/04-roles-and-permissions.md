# Phase 04 — Roles and authorization

## Goal

Enforce five roles and row relationships in server code and RLS.

## Why This Phase Exists

This phase is required before dependent work because it will enforce five roles and row relationships in server code and rls. It starts after 03 authentication.

## Prerequisites

03 authentication.

## Existing Laravel Reference

app/Enums/Role.php, RoleSeeder, routes/web.php, bootstrap/app.php, routes/channels.php, ownership checks.

## Scope

Role mapping, dashboard redirect, authorization helpers and table policies.

## Out of Scope

No invented named permissions or domain UI.

## Tasks

- [ ] Inspect actual Spatie grants.
- [ ] Implement five role values and roleless denial.
- [ ] Map coordinator, assignee, student and chat participant predicates.
- [ ] Protect pages, actions, handlers and Realtime.
- [ ] Test own/related/unrelated access per table.

## Expected Files

server/authz.ts, role redirect route, SQL policies, tests/permissions.

## Database Changes

Role membership and RLS policies.

## Roles / Permissions

Admin, coordinator, professor, supervisor, student and anonymous.

## Business Rules

Relationship scope matters beyond role.

## Edge Cases

Multi-role users and broad coordinator duty monitor require decisions.

## Security Checks

Default deny; never trust form role claims.

## Testing

Unit redirect; integration direct route/action; permission full matrix; database RLS; UI forbidden state; regression auth.

## Verification Checklist

- [ ] Try each role against own, related, unrelated and anonymous records.

## Definition of Done

No unauthorized path and intended paths reachable.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
