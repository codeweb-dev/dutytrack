# Repository-evidenced context

CONFIRMED: Laravel 12, PHP 8.2+, Livewire 4/Volt pages, Flux UI, Tailwind 4/Vite, Fortify, Spatie roles, Reverb broadcasting, and Laravel Excel package are declared. The active code implements OJT management for five roles: admin, OJT coordinator, professor, company supervisor, student. `routes/web.php` supplies the actual route set; route-specific views live in `resources/views/pages/`, often paired with `.php` component classes. `app/Livewire/Forms/`, `app/Models/`, and `app/Support/` hold mutations and domain helpers. The repository has no application Jobs, Policies, Exports, Imports, or Services classes and no scheduled command beyond `inspire` in `routes/console.php`.

## Major modules
- Public landing and Fortify auth/settings/2FA.
- Admin staff accounts, announcements, audit log and monitoring dashboards.
- Coordinator-created students, physical requirement checklist, assignments and monitoring.
- Student attendance with geolocation and duty-hour generation; reviewer approvals.
- Daily accomplishments/photos and downloadable Word-compatible HTML `.doc` reports.
- Practicum evaluations with rubric and `.doc` export.
- Assigned task Kanban, private notes, browser-local Pomodoro and pet streak.
- One-to-one assignment/coordinator messaging with Reverb events and polling fallback.

## Important cross-cutting behavior
`users.coordinator_id` owns coordinator/student relations. One `ojt_assignments` row per student links internal professor or external company supervisor. `attendance_logs` stores four punches and location; final punch creates/updates `duty_hour_logs`. Students submit report entries against completed attendance. Professor/supervisor views filter by assignments. `AuditLogger` is called by selected CRUD forms and delete components, not all mutations. Role routing uses Spatie `role:` middleware; record checks live in query scopes and component methods. No explicit seeded permission names were found, despite Spatie permission tables. Public registration creates a user without a role, while `/dashboard` rejects unknown roles. `User` has `MustVerifyEmail` commented out despite Fortify email verification being configured. Both are OPEN QUESTIONS for parity and security review.

## Evidence / limits
This is a source-code audit, not a snapshot of production data or deployment config. `.env.example` defaults to SQLite, database queue/session/cache, log mail and null broadcasting; actual environments may differ. `app/Support/StudentDirectory.php` and `StaffDirectory.php` contain embedded directory records, and should be treated as sensitive. `admin/partners` has files but no route in `routes/web.php`; coordinator sidebar has `#` placeholders for approvals/reports. The installed Excel/maps packages do not by themselves prove active Excel exports or a maps API integration. See the inventory and open questions before adding them.

## Decision register before implementation

| Label | Issue and evidence | Required decision |
|---|---|---|
| LEGACY BEHAVIOR | `P/student/⚡attendance/attendance.php::bypassAttendance` lets students add 60/480 minutes; source test calls it a test bypass. | Remove, restrict or explicitly retain before Phase 09; document parity/security impact. |
| LEGACY BEHAVIOR | Coordinator student form defaults password to last name and accepts min 3; admin staff forms accept min 2 even though `AppServiceProvider` sets stronger production defaults for generic password rules. | Choose secure onboarding/reset path and communicate migration effect. |
| OPEN QUESTION | Public Fortify registration creates a roleless user; `User` does not implement `MustVerifyEmail` despite Fortify verification feature and `verified` route group. | Decide public registration, verification requirement and role assignment. |
| CONFIRMED | Coordinator duty-hour query starts from `DutyHourLog::query()` without `coordinator_id` predicate; source test asserts another coordinator's student appears. | Confirm business intent before reproducing or narrowing. |
| OPEN QUESTION | Production Spatie `permissions`/pivots may contain grants even though no named permissions are seeded. | Inspect live rows read-only in Phase 00. |
| OPEN QUESTION | Actual database driver, mail, broadcast, queue, timezone and deployment host are absent from source evidence. | Inspect redacted running config and deploy inventory. |
| OPEN QUESTION | Password hashes/Fortify 2FA secrets may lack supported direct Supabase Auth import. | Rehearse reset/invite/MFA re-enrolment path. |
| OPEN QUESTION | `admin/partners` page is unrouted; coordinator sidebar approvals/reports use `#`. | Decide whether these are intentionally dormant; do not invent routes. |
| INFERRED | Hard-coded student/staff directories are development/reference data, but contain personal information and duplicate student IDs. | Verify authority, retention and whether target needs them. |

## Infrastructure and API surface

CONFIRMED: `bootstrap/app.php` exposes `/up` health and registers `routes/web.php`, `routes/console.php`, and `routes/channels.php`; there is no `routes/api.php` in this repository and no custom HTTP controller beyond Laravel's base `Controller`. `composer.json` dev script runs PHP server, queue listener, Pail and Vite together; `.github/workflows/tests.yml` runs Pest on PHP 8.4/8.5 after asset build, and `lint.yml` runs Pint. `config/services.php` has conventional Postmark/Resend/SES/Slack placeholders but no application call to those APIs was found. `resources/js/echo.js` uses Laravel Echo/Pusher protocol for Reverb, while the attendance map loads Leaflet from unpkg and OpenStreetMap tiles. The landing bug-report link uses `mailto:`. These are the evidenced external touchpoints; actual production provider selection remains an open question.
