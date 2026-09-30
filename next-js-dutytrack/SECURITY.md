# Security and authorization plan

## Current evidence
Laravel uses Fortify sessions, login/2FA rate limits (5/min), Spatie roles and record-scoped Livewire queries. `routes/channels.php` authorizes user and conversation channels. `AuditLogger` records selected account/assignment/announcement mutations. Public registration creates a roleless user; `MustVerifyEmail` is commented; student creation allows passwords as short as three characters and admin staff creation as short as two and autofills from last name. Attendance has a testing bypass exposed to students. LEGACY BEHAVIOR: do not reproduce these without an explicit parity/security decision.

## Target controls
- Authenticate server requests with Supabase Auth; map Auth ID to one app profile and current role. Deny missing/ambiguous role. Keep cookies HTTP-only/secure/same-site per selected SSR flow; check action/handler CSRF assumptions in Phase 03.
- Server authorization for every read/write/download plus PostgreSQL RLS for browser and user-scoped server clients. Policies cover users/profiles, roles, assignments, requirements, attendance, duty, evaluations, submissions, tasks, notes, messages, announcements, and audit. Test cross-coordinator, cross-assignment, own/other and anonymous cases.
- Use admin client only for narrow account provisioning/migration operations; service-role key server-only. Never trust role/user IDs from form payloads. Enforce status/relationship checks in the same transaction as mutation.
- Store personal photos and accomplishments privately with signed time-limited access. Validate size/type/count and path ownership server side; avoid trusting extension or browser MIME alone. Static requirement templates can remain public after inventory.
- Escape rendered message/notes/accomplishment content; avoid unsanitized HTML. Exports need output encoding and scoped queries. Restrict Realtime channels to actual participants and prevent client writes beyond RLS.
- Log sensitive mutations, including account/assignment/approval decisions, with actor and target; preserve current audit events and decide expanded coverage before cutover. Protect audit records against casual edits; current admin delete behavior is a parity decision.
- Rate limit login, reset, 2FA, messages, uploads, and expensive export routes to measured need. Handle secrets via deployment secret store; no production values in repo, docs, or client bundle.

## Required security tests
Each table/action: anonymous deny, unrelated role deny, unrelated same-role deny, valid owner/assignee allow. Add direct Route Handler/Server Action tests, SQL RLS policy tests, private Storage access tests, Realtime subscription tests and bypass/roleless-registration decision tests. See [TESTING_STRATEGY.md](TESTING_STRATEGY.md).
