# Phase 17 — Integrations and automation review

## Goal

Port only evidenced external integrations and automation.

## Why This Phase Exists

This phase is required before dependent work because it will port only evidenced external integrations and automation. It starts after 09, 11, 14, 15 modules.

## Prerequisites

09, 11, 14, 15 modules.

## Existing Laravel Reference

composer.json, package.json, routes/console.php, queue/mail/broadcast/filesystem config, map and Echo code.

## Scope

Maps/OSM, Auth email, Storage, Realtime and operations provider decisions.

## Out of Scope

No invented worker, cron, Excel or custom notifications.

## Tasks

- [ ] Inventory external URLs and redacted live config.
- [ ] Verify OSM tile/map reliability and fallback.
- [ ] Configure Auth email delivery.
- [ ] Smoke private Storage and Realtime.
- [ ] Document no-op for absent Jobs/schedules/Excel/custom notifications unless live evidence differs.

## Expected Files

DEPLOYMENT.md, env docs, narrow adapters if needed.

## Database Changes

No new table without proved need.

## Roles / Permissions

All users email; students maps; chat participants.

## Business Rules

Existing email/map/chat behavior only.

## Edge Cases

Reverb source may default null; actual production differs.

## Security Checks

Provider secrets server only, private files and quotas.

## Testing

Unit none speculative; integration email/map/Storage/Realtime; permission channels/files; DB none; UI fallback; regression auth/chat/attendance.

## Verification Checklist

- [ ] Exercise staging provider success/failure with test accounts.

## Definition of Done

Only confirmed integrations active, no unused worker.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
