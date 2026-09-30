# Phase 13 — Tasks and productivity

## Goal

Migrate Kanban, private notes and browser-local tools.

## Why This Phase Exists

This phase is required before dependent work because it will migrate kanban, private notes and browser-local tools. It starts after 07 assignments; 05 shell.

## Prerequisites

07 assignments; 05 shell.

## Existing Laravel Reference

Professor/supervisor/student tasks pages, student notes, pomodoro, pet-streak, StudentTask, StudentNote.

## Scope

Staff group tasks, student progress/reorder, own notes/autosave, browser-local timer/pet.

## Out of Scope

No server persistence for local tools or task alerts.

## Tasks

- [ ] Port assignment scope, group UUID, statuses and priorities.
- [ ] Implement staff group and student item reorder with bounds.
- [ ] Set/clear completed_at on done.
- [ ] Implement own notes search/pin/autosave.
- [ ] Port localStorage timer/pet.
- [ ] Add keyboard drag fallback and denial tests.

## Expected Files

features/tasks, features/notes, features/productivity, role routes.

## Database Changes

student_tasks and student_notes RLS; none for local tools.

## Roles / Permissions

Assigned staff tasks; student own tasks/notes/local.

## Business Rules

Staff group move updates all group members.

## Edge Cases

Group statuses may diverge; stale local storage.

## Security Checks

Scope every row and validate sort targets.

## Testing

Unit reorder; integration tasks/notes; permission other assignment; database group/order; UI keyboard/local reload; regression assignment.

## Verification Checklist

- [ ] Assign many, move group/item, edit note, reload timer/pet.

## Definition of Done

Task/notes parity and local persistence pass.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
