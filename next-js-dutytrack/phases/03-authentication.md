# Phase 03 — Authentication

## Goal

Implement identity and current account settings flows.

## Why This Phase Exists

This phase is required before dependent work because it will implement identity and current account settings flows. It starts after 02 Supabase foundation.

## Prerequisites

02 Supabase foundation.

## Existing Laravel Reference

FortifyServiceProvider, Actions/Fortify, User model, routes/settings.php, auth/settings views, tests/Feature/Auth.

## Scope

Login/logout, reset, verification, profile/password/appearance/delete and reviewed MFA path.

## Out of Scope

No staff provisioning or role dashboard.

## Tasks

- [ ] Map Auth user to profile and session refresh.
- [ ] Port login/reset/logout with rate limits.
- [ ] Resolve roleless registration and MustVerifyEmail mismatch.
- [ ] Port own profile/password/appearance/delete.
- [ ] Decide supported 2FA migration or re-enrolment and test callbacks.

## Expected Files

app/(auth), app/(workspace)/settings, server/auth.ts, features/auth.

## Database Changes

Auth-managed data and app profile mapping.

## Roles / Permissions

All authenticated and anonymous users.

## Business Rules

Fortify-visible flows and redirects subject to signed decisions.

## Edge Cases

Existing 2FA secrets may be incompatible; roleless users currently get dashboard 403.

## Security Checks

Secure cookies, CSRF review, rate limits and no account enumeration.

## Testing

Unit input rules; integration auth/reset; permission cross-account denial; database identity; UI login/settings; regression build.

## Verification Checklist

- [ ] Login, reset, verify, edit profile, MFA and logout with actual redirect URLs.

## Definition of Done

Chosen auth flows work and exceptions documented.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
