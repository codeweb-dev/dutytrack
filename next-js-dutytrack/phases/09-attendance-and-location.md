# Phase 09 — Attendance and location

## Goal

Migrate four-punch attendance, geolocation and hour generation.

## Why This Phase Exists

This phase is required before dependent work because it will migrate four-punch attendance, geolocation and hour generation. It starts after 07 assignment.

## Prerequisites

07 assignment.

## Existing Laravel Reference

Student attendance page, AttendanceLog, map components, StudentAttendanceTest.

## Scope

Four punches, one day row, location, 7:00/17:00 calculation, pending duty upsert and own history.

## Out of Scope

No submission or approval UI.

## Tasks

- [ ] Resolve legacy 60/480-minute bypass before implementation.
- [ ] Port counted time window, overtime, rounding and late status.
- [ ] Validate coords/accuracy/remarks and assignment on server.
- [ ] Make punch order/day uniqueness atomic.
- [ ] Upsert pending duty on final punch.
- [ ] Test boundaries and concurrent duplicates.

## Expected Files

features/attendance, student attendance route, map client, transaction function and policies.

## Database Changes

attendance_logs, duty_hour_logs, unique student/date.

## Roles / Permissions

Student own write; staff reads later.

## Business Rules

Four ordered punches; final creates pending duty.

## Edge Cases

Cross-midnight, concurrent punches and bypass decision.

## Security Checks

Server clock, no client-trusted assignment/time; coordinate validation.

## Testing

Unit time math; integration sequence; permission other student; database concurrency; UI map/location denied; regression assignment.

## Verification Checklist

- [ ] Perform four punches, inspect hours/duty, reject duplicates/bad coords.

## Definition of Done

Source boundary fixtures match and bypass decision recorded.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
