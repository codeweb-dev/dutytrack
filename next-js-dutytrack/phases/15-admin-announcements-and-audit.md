# Phase 15 — Admin announcements and audit

## Goal

Migrate announcements and selected audit events.

## Why This Phase Exists

This phase is required before dependent work because it will migrate announcements and selected audit events. It starts after 06 directories; 04 authorization.

## Prerequisites

06 directories; 04 authorization.

## Existing Laravel Reference

Admin AnnouncementForm, AuditLogger, admin announcement/audit pages, shared announcement component.

## Scope

Admin announcement CRUD; authenticated read; audit create/filter/view/delete parity.

## Out of Scope

No email/push alerts or universal audit listener.

## Tasks

- [ ] Port four types and validation.
- [ ] Show all-user announcements.
- [ ] Write existing account/assignment/announcement audit events.
- [ ] Review admin audit deletion.
- [ ] Build whitelisted sort/filter.
- [ ] Test non-admin denial and safe old/new values.

## Expected Files

features/announcements, server/audit.ts, admin routes, policies.

## Database Changes

announcements and audit_logs RLS.

## Roles / Permissions

Admin writes; authenticated reads announcements; admin audit.

## Business Rules

Only selected operations currently produce audit entries.

## Edge Cases

Deleted actor retains name/role and nullable FK.

## Security Checks

Server-side transaction and safe output.

## Testing

Unit type/sort; integration CRUD/audit; permission non-admin; database actor nulling; UI banner/filter; regression directory.

## Verification Checklist

- [ ] Create/update/delete announcement and inspect audit; deny student admin route.

## Definition of Done

Announcement and audit parity pass.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
