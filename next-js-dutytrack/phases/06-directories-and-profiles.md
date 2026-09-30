# Phase 06 — Directories and profiles

## Goal

Migrate staff and coordinator-owned student account management.

## Why This Phase Exists

This phase is required before dependent work because it will migrate staff and coordinator-owned student account management. It starts after 05 shell.

## Prerequisites

05 shell.

## Existing Laravel Reference

Admin and coordinator Livewire Forms, StudentDirectory, StaffDirectory, admin pages, coordinator student components.

## Scope

Admin staff CRUD/read students; coordinator own-student CRUD, photos, roles, profiles, requirements and audit.

## Out of Scope

No assignments or attendance.

## Tasks

- [ ] Port unique email/IDs, required hours and photo validation.
- [ ] Create staff Auth/profile/role/audit transactionally.
- [ ] Create own students with profile/requirements.
- [ ] Keep embedded directories server-only and review duplicates/privacy.
- [ ] Test cross-coordinator denial and cleanup.

## Expected Files

features/directory, admin/coordinator student routes, private profile bucket.

## Database Changes

users, profile tables, roles, student_requirements, audit_logs, Storage policies.

## Roles / Permissions

Admin staff management; coordinator own students.

## Business Rules

One coordinator per student; create seeds nine requirements.

## Edge Cases

Last-name password and personal directory data are legacy review items.

## Security Checks

Provision Auth server-only; no public directory exposure.

## Testing

Unit validation; integration CRUD/audit; permission other coordinator; database FK/unique; UI forms/uploads; regression auth.

## Verification Checklist

- [ ] Create all staff types and own student, edit/delete, attempt cross-owner operations.

## Definition of Done

Account/profile/role/file/audit consistency proven.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
