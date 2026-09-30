# Phase 14 — Messaging and Realtime

## Goal

Migrate scoped chat and live delivery with fallback.

## Why This Phase Exists

This phase is required before dependent work because it will migrate scoped chat and live delivery with fallback. It starts after 07 assignments; 04 authorization.

## Prerequisites

07 assignments; 04 authorization.

## Existing Laravel Reference

Messages page, Conversation events, chat models, routes/channels.php, resources/js/echo.js, MessagingTest.

## Scope

Coordinator pre-assignment chats, assigned chats, send/read/edit/delete, typing and private Realtime.

## Out of Scope

No admin/group chat or push alerts.

## Tasks

- [ ] Create conversations idempotently.
- [ ] Authorize both participants in rows/channels.
- [ ] Port 2000-char messages, own edit/delete, recipient read_at.
- [ ] Implement three-second typing or reviewed presence.
- [ ] Handle reconnect/duplicate events and polling fallback.
- [ ] Test cross-conversation denial.

## Expected Files

features/messages, four role routes, Realtime policies.

## Database Changes

chat_conversations/messages/typing_indicators; nullable assignment.

## Roles / Permissions

Student/coordinator/professor/supervisor participants.

## Business Rules

Coordinator chat exists before assignment; only sender edits/deletes.

## Edge Cases

Postgres null uniqueness permits duplicate coordinator conversation unless handled.

## Security Checks

Private subscriptions and RLS; server validation.

## Testing

Unit message limit; integration lifecycle; permission unrelated; database uniqueness/read; UI live/fallback; regression auth/assignment.

## Verification Checklist

- [ ] Two sessions send/read/edit/delete/typing; unrelated session denied.

## Definition of Done

Realtime and fallback work without leakage.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
