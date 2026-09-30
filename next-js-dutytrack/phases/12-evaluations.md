# Phase 12 — Evaluations

## Goal

Migrate practicum rubric and authorized evaluation document.

## Why This Phase Exists

This phase is required before dependent work because it will migrate practicum rubric and authorized evaluation document. It starts after 07 assignments; 05 shell.

## Prerequisites

07 assignments; 05 shell.

## Existing Laravel Reference

ManagesPracticumEvaluations, PracticumEvaluationForm, EvaluationDocument, evaluator/student/admin pages and export Blade.

## Scope

Professor/supervisor submit, student/admin view, student/evaluator .doc download.

## Out of Scope

No new approval step.

## Tasks

- [ ] Port 4 areas/20 keys and scores 1–5.
- [ ] Validate date order and evaluator assignment.
- [ ] Preserve multiple records/assignment.
- [ ] Build lists and authorized document.
- [ ] Compare output/logo/grade thresholds.

## Expected Files

features/evaluations, evaluation routes, export handler.

## Database Changes

evaluations JSON ratings/dates/status and RLS.

## Roles / Permissions

Assigned professor/supervisor create; student own read/export; admin read.

## Business Rules

Total out of 100, submitted status, cooperating program from coordinator name.

## Edge Cases

Old rows may have null new columns.

## Security Checks

Direct download checks student/evaluator relationship.

## Testing

Unit rubric/grade; integration submit/export; permission unrelated; database JSON/FK; UI form; regression assignment.

## Verification Checklist

- [ ] Submit from each evaluator, view as student and deny unrelated download.

## Definition of Done

Rubric, ownership and .doc parity pass.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
