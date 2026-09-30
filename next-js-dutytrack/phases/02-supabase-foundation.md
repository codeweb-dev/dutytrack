# Phase 02 — Supabase foundation

## Goal

Create reviewed PostgreSQL schema, identity mapping and baseline RLS.

## Why This Phase Exists

This phase is required before dependent work because it will create reviewed postgresql schema, identity mapping and baseline rls. It starts after 01 foundation and 00 schema decisions.

## Prerequisites

01 foundation and 00 schema decisions.

## Existing Laravel Reference

database/migrations/*, app/Models/*, config/permission.php, database/seeders/*.

## Scope

Dev project, domain schema, request-scoped clients, types and deny-by-default RLS.

## Out of Scope

No production data import or domain forms.

## Tasks

- [ ] Choose UUID Auth ID to legacy bigint mapping before FKs.
- [ ] Port domain tables in dependency order preserving constraints and enum values.
- [ ] Decide which Laravel runtime tables are excluded.
- [ ] Enable RLS on all application tables and private buckets.
- [ ] Generate typed clients and test migration repeatability.

## Expected Files

supabase/migrations/*.sql, types/database.ts, lib/supabase/server.ts and browser.ts.

## Database Changes

Domain tables, FKs, indexes and baseline policies.

## Roles / Permissions

All roles later; anonymous denied now.

## Business Rules

One assignment per student and exact source values.

## Edge Cases

Spatie polymorphic pivots and nullable coordinator chat assignment.

## Security Checks

Service-role server only; default deny.

## Testing

Unit ID map; integration client smoke; permission anonymous deny; database up/down and constraints; UI none; regression build.

## Verification Checklist

- [ ] Create/drop disposable database, inspect all app tables/policies and try anonymous reads.

## Definition of Done

Identity map and schema/RLS baseline proven.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
