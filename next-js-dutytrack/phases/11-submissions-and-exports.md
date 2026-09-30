# Phase 11 — Submissions and exports

## Goal

Migrate accomplishments, photos and Word-compatible report.

## Why This Phase Exists

This phase is required before dependent work because it will migrate accomplishments, photos and word-compatible report. It starts after 09 attendance; 10 for totals.

## Prerequisites

09 attendance; 10 for totals.

## Existing Laravel Reference

Student/staff reports-submissions pages, AttendanceSubmission, AccomplishmentReportDocument, export Blade.

## Scope

Student own edits/uploads/export; assigned staff read; filtered .doc output.

## Out of Scope

No staff approval or Excel export.

## Tasks

- [ ] Require fully completed attendance.
- [ ] Validate 5000-char text, six new images/save and 4MB/image.
- [ ] Store private photos with stable order.
- [ ] Port staff scoped lists and student date filter.
- [ ] Generate matching .doc with embedded authorized photos.
- [ ] Compare sample content and missing-file behavior.

## Expected Files

features/submissions, exports Route Handler, private Storage policies.

## Database Changes

attendance_submissions, private bucket.

## Roles / Permissions

Student write/export; assigned professor/supervisor read.

## Business Rules

One submission per completed attendance; export sums rendered attendance hours.

## Edge Cases

Existing images can exceed six cumulatively; source skips missing files.

## Security Checks

Signed file access, escaped output, validated download filters.

## Testing

Unit filters; integration upload/export; permission other user; database unique; UI modal; regression attendance.

## Verification Checklist

- [ ] Submit/edit text/photos, staff view and authorized/denied download.

## Definition of Done

Sample document and access parity proven.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
