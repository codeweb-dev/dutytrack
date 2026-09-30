# Phase 00 — Project audit

## Goal

Confirm source inventory against live Laravel behavior and production schema before implementation.

## Why This Phase Exists

This phase is required before dependent work because it will confirm source inventory against live laravel behavior and production schema before implementation. It starts after Planning docs complete.

## Prerequisites

Planning docs complete.

## Existing Laravel Reference

routes/*, app/*, database/migrations/*, tests/* and read-only running configuration.

## Scope

Read-only route, schema, data, roles, storage and deployment audit.

## Out of Scope

No app scaffold, source refactor or data writes.

## Tasks

- [ ] Inventory every route and five sidebars including orphan/placeholder links.
- [ ] Trace one complete path per domain and compare 29 migration tables to live DB.
- [ ] Resolve or assign owner to registration, verification, bypass, default password, coordinator duty scope and 2FA questions.
- [ ] Record actual config, row counts, permissions and source test gaps.

## Expected Files

Planning Markdown revisions.

## Database Changes

No target database changes.

## Roles / Permissions

All five roles and anonymous.

## Business Rules

Laravel remains source of truth.

## Edge Cases

Production data and configuration may differ from source.

## Security Checks

Redact PII/secrets; read-only access.

## Testing

Unit none; integration route smoke; permission five-role review; database counts/DDL; UI navigation review; regression Laravel suite.

## Verification Checklist

- [ ] Evidence register and decision gates signed off.

## Definition of Done

Baseline verified and unresolved decisions assigned.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
