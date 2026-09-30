# DutyTrack Laravel to Next.js + Supabase Migration Prompt

This prompt was prepared after reviewing the Laravel routes, Livewire components, database migrations, tests, design system, screenshots, authentication, realtime messaging, documents, and role permissions.

The proposed foundation follows the current official guidance for [Next.js App Router](https://nextjs.org/docs/app/getting-started/installation), [Supabase SSR authentication](https://supabase.com/docs/guides/auth/server-side), [Supabase RLS](https://supabase.com/docs/guides/auth), and [shadcn/ui with Next.js](https://ui.shadcn.com/docs/installation/next).

## Migration prompt

```text
You are a senior full-stack migration engineer. Migrate the existing DutyTrack Laravel 12 + Livewire 4 application to a production-ready Next.js application backed by Supabase.

This is a functional and visual migration, not a redesign.

WORKSPACE PATHS — USE THESE EXACT LOCATIONS

- Existing Laravel source project: `/Users/user/dutytrack`
- New Next.js destination project: `/Users/user/duty-track-nextjs`
- This Markdown prompt may be placed inside the destination project, but the Laravel source is its sibling directory at `/Users/user/dutytrack`.

Before doing any work:

1. Verify that `/Users/user/dutytrack` exists and contains `artisan`, `composer.json`, `routes/`, `app/`, `resources/`, `database/`, and `tests/`.
2. Verify that `/Users/user/duty-track-nextjs` exists.
3. Treat `/Users/user/dutytrack` as read-only reference material. Do not edit, move, rename, or delete anything in it.
4. Create and modify the migrated application only inside `/Users/user/duty-track-nextjs`.
5. Do not create another nested `next-app` or `duty-track-nextjs` directory.
6. Do not assume the current working directory is the Laravel source. Always use the absolute paths above when inspecting or writing files.
7. If either path is missing or points to an unexpected project, stop and report the problem instead of creating a replacement directory elsewhere.

FIRST: AUDIT BEFORE CODING

Inspect the entire Laravel repository at `/Users/user/dutytrack` before changing anything in the destination. Laravel remains the source of truth for behavior and design.

At minimum, inspect:

- routes/web.php
- routes/settings.php
- app/Models/**
- app/Livewire/**
- app/Support/**
- database/migrations/**
- database/seeders/**
- resources/views/pages/**
- resources/views/components/**
- resources/views/layouts/**
- resources/css/app.css
- resources/js/**
- tests/Feature/**
- tests/Unit/**
- public/**
- final-flow-duty-track.md

Trace every reachable route, mutation, authorization rule, query/filter, validation rule, relationship, file upload, realtime event, document download, and role-specific interface.

Ignore orphaned, commented-out, duplicated, or unreachable code unless it is required by a reachable page or test. For example, do not automatically migrate the orphaned admin partners page unless it becomes an explicit requirement.

Do not delete or overwrite the Laravel application during development. Build the Next.js version directly in `/Users/user/duty-track-nextjs` until feature parity is verified.

Before implementation, create these documents:

1. `/Users/user/duty-track-nextjs/docs/current-system-audit.md`
2. `/Users/user/duty-track-nextjs/docs/route-parity-matrix.md`
3. `/Users/user/duty-track-nextjs/docs/database-mapping.md`
4. `/Users/user/duty-track-nextjs/docs/migration-risks.md`

The route parity matrix must identify each Laravel route as:

- Migrated
- Intentionally removed
- Prototype/client-only
- Deferred with reason

TARGET TECHNOLOGY STACK

Use the smallest dependable stack:

- Current stable Next.js
- App Router
- React
- Strict TypeScript
- Tailwind CSS v4
- shadcn/ui using the New York-style visual language
- Lucide React icons only
- Supabase Postgres
- Supabase Auth
- `@supabase/supabase-js`
- `@supabase/ssr`
- Supabase Storage
- Supabase Realtime
- Zod for trust-boundary validation
- React Hook Form only for complex interactive forms
- Sonner for toast notifications
- `next/font/google` with Poppins
- `next-themes` for light/dark appearance
- Leaflet with OpenStreetMap for attendance maps

Do not add Prisma, Drizzle, Redux, Zustand, a repository layer, a generic service framework, a chart library, or a second component library.

Use:

- React Server Components for initial data and read-heavy pages
- Server Actions for normal authenticated mutations
- Route Handlers for auth callbacks, downloads, webhook-like endpoints, and operations requiring special response headers
- Client Components only for realtime chat, drag-and-drop, maps, timers, file previews, modal state, and similar browser interactions
- Plain CSS/Tailwind for the existing simple bar charts
- Native `Intl` date/number formatting where practical
- Native browser geolocation for attendance
- Native drag-and-drop with accessible move controls if sufficient; add a drag library only if touch/accessibility testing proves it necessary

Generate Supabase TypeScript database types and use them instead of manually duplicating row types.

PROJECT STRUCTURE

Use a straightforward structure similar to:

/Users/user/duty-track-nextjs/
  app/
    (public)/
    (auth)/
    (dashboard)/
      admin/
      coordinator/
      professor/
      supervisor/
      student/
    auth/callback/
    api/
  components/
    ui/
    layout/
    dashboard/
  lib/
    auth/
    supabase/
    validation/
    attendance.ts
    evaluations.ts
    permissions.ts
  supabase/
    migrations/
    seed.sql
  public/
  tests/
  docs/

Do not create one folder or abstraction per database table unless it reduces real duplication.

AUTHENTICATION AND AUTHORIZATION

Replace Laravel Fortify and Spatie Permission with Supabase Auth and one application role per user.

Roles:

- admin
- ojt_coordinator
- professor
- company_supervisor
- student

Implement:

- Email/password login
- Logout
- Email verification
- Forgot/reset password
- Password update
- Profile update
- TOTP MFA enrollment, challenge, verification, and removal
- Auth callback route
- Cookie-based SSR session handling
- Session refresh through the current Supabase SSR pattern
- Role-aware redirect from `/dashboard`
- Protected route layouts
- Server-side role checks
- Database RLS as the final security authority
- Account status enforcement
- Rate limiting for sensitive auth and mutation endpoints where appropriate

Do not trust route middleware alone.

Public self-registration should be disabled unless a valid role assignment/invitation flow is implemented. The Laravel registration currently creates an account without a role, after which `/dashboard` returns 403. Treat this as an existing defect rather than behavior to preserve.

Admins create coordinators, professors, and company supervisors using a server-only Supabase Admin client. Coordinators create students they manage.

Never expose the Supabase service-role key to Client Components, browser code, `NEXT_PUBLIC_*`, logs, or error messages.

Do not continue the insecure practice of deriving initial passwords from last names. Use invitations, password-reset links, or cryptographically random temporary passwords that must be changed.

DATABASE DESIGN

Use `auth.users` for authentication and `public.profiles` for application identity.

Recommended tables:

1. profiles
   - id UUID primary key references auth.users(id) on delete cascade
   - role app_role not null
   - coordinator_id UUID nullable references profiles(id)
   - first_name
   - middle_name
   - last_name
   - name
   - email
   - contact_number
   - profile_photo_path
   - status
   - created_at
   - updated_at

2. student_profiles
   - user_id UUID primary key references profiles(id)
   - student_id unique
   - course
   - ojt_required_hours

3. professor_profiles
   - user_id UUID primary key
   - employee_id unique
   - department
   - position

4. supervisor_profiles
   - user_id UUID primary key
   - supervisor_id unique
   - company_name
   - company_address

5. announcements
   - Preserve title, message, type, timestamps
   - Allowed types: info, warning, urgent, maintenance
   - Do not invent publish/archive functionality during parity work because it does not exist in the current schema

6. ojt_assignments
   - student_id
   - ojt_type: internal or external
   - professor_id nullable
   - company_supervisor_id nullable
   - status: pending, active, completed, cancelled
   - remarks
   - timestamps
   - Database constraint:
     - internal requires professor_id and prohibits company_supervisor_id
     - external requires company_supervisor_id and prohibits professor_id

7. attendance_logs
   - Preserve all current punch timestamps
   - attendance_date
   - morning_time_in/out
   - afternoon_time_in/out
   - first time-in coordinates and accuracy
   - hours_rendered
   - overtime_enabled
   - status
   - remarks
   - reviewed_by
   - unique student/date constraint

8. duty_hour_logs
   - Preserve attendance link, rendered hours, approved hours, approval status, approver, remarks, and approval timestamp

9. attendance_submissions
   - One per attendance log
   - accomplishment
   - photo_paths JSONB
   - student and assignment relationships

10. evaluations
    - Preserve evaluator relationship and type
    - inclusive dates
    - cooperating program
    - ratings JSONB
    - total rating
    - remarks
    - status and evaluation date

11. student_tasks
    - Preserve task group UUID
    - student, assigner, assignment
    - title, description
    - statuses: pending, in_progress, in_review, done
    - priorities: low, normal, high
    - due date, sort order, completed timestamp

12. student_notes
    - Private student notes
    - title, body, pinned state, timestamps

13. student_requirements
    - Preserve key, label, step, order, template path
    - submitted/completed timestamps
    - coordinator who completed it

14. chat_conversations
    - student
    - participant
    - participant role
    - optional assignment
    - uniqueness preventing duplicate conversations

15. chat_messages
    - conversation
    - sender
    - body
    - read_at
    - edited_at
    - timestamps

16. audit_logs
    - actor UUID
    - actor snapshot name/role
    - module and action
    - target type and text target ID
    - target label
    - description
    - old/new values JSONB
    - metadata JSONB
    - IP and user agent where available
    - timestamps

Use indexed foreign-key columns and indexes matching real search/filter patterns.

Use database CHECK constraints for known status values and assignment invariants.

Use UUIDs for auth identities. Business records may use generated bigint IDs. If mixed IDs are used, audit target IDs must be text.

SUPABASE RLS

Enable RLS on every application table and relevant Storage bucket.

Create small, reviewed SQL authorization helpers such as:

- current_app_role()
- is_admin()
- coordinator_manages(student_uuid)
- is_assigned_professor(student_uuid)
- is_assigned_supervisor(student_uuid)
- can_access_conversation(conversation_id)

Avoid recursive policies.

Required access behavior:

Admin:
- Read all application data
- Manage staff accounts and announcements
- Monitor students, assignments, attendance, hours, evaluations, and audit logs
- Admin student page currently provides monitoring/view behavior; do not silently add destructive student management

Coordinator:
- Read and manage only students where profiles.coordinator_id = auth.uid()
- Create/update/delete those students
- Manage only those students’ requirements and OJT assignments
- Read their attendance and duty-hour data
- Message only students they manage

Professor:
- Access internal assignments where professor_id = auth.uid()
- Read assigned students, attendance, duty hours, reports, and evaluations
- Approve/reject only assigned students’ duty hours
- Assign tasks only to assigned students
- Create evaluations only for assigned students
- Message only assigned students

Company supervisor:
- Same operational capabilities as professor
- Scope exclusively to external assignments where company_supervisor_id = auth.uid()

Student:
- Read/update only their own allowed data
- Read their coordinator and assigned professor/supervisor
- Record only their own attendance
- Read their duty hours
- Create/update submissions only for their completed attendance
- Update only their own task status/order
- CRUD only their own notes
- Read their own evaluations
- Message only linked coordinator/professor/supervisor
- Mark a requirement as submitted, but cannot mark it verified/completed

Users must never change their own role, coordinator ownership, approval state, approved hours, evaluator identity, or other privileged columns.

ATTENDANCE BUSINESS RULES

Move attendance mutation logic into one transactional Postgres RPC or equally authoritative server-only operation. Use server time, not client-submitted timestamps.

Application timezone: Asia/Manila.

Preserve these rules exactly:

- Student needs an OJT assignment before recording attendance
- One attendance record per student per date
- Punch order:
  1. Morning Time In
  2. Morning Time Out
  3. Afternoon Time In
  4. Afternoon Time Out
- First time-in requires latitude and longitude
- Latitude range: -90 to 90
- Longitude range: -180 to 180
- Accuracy is optional and between 0 and 100000
- Remarks maximum: 1000 characters
- Work hours start counting at 07:00
- A time-in after 07:00 marks the record late
- Without overtime, counted time stops at 17:00
- With overtime enabled, time after 17:00 is included
- Hours are rounded to two decimal places
- Completing afternoon time-out creates or resets the related duty-hour log to pending
- Approved hours start at zero
- A rejected/resubmitted attendance must not silently retain an old approval

Do not migrate the `bypassAttendance(60|480)` production UI or endpoint. It is a test backdoor. Replace it with test fixtures or a local-development-only seed helper that cannot be deployed.

Use OpenStreetMap/Leaflet for:

- Capturing location
- Showing a fullscreen picker
- Displaying saved time-in position
- Showing accuracy where available
- Graceful handling when permission is denied or unavailable

DUTY-HOUR APPROVAL

Professors and supervisors may approve or reject only logs belonging to students assigned to them.

Approval must:

- Set status to approved
- Set approved_hours to hours_rendered
- Set approved_by to the authenticated approver
- Set approved_at to server time

Rejection must:

- Set status to rejected
- Set approved_hours to zero
- Record approver and timestamp as appropriate

Students and coordinators can monitor records but cannot approve them.

TASK/KANBAN SYSTEM

Preserve:

- Multi-student assignment by professors/supervisors
- One task row per student
- Shared task_group_id for a multi-student task
- Title, description, priority, due date
- Four states:
  - Pending
  - In Progress
  - Done
  - In Review
- Student drag/reorder affects only their task
- Staff drag/reorder of a grouped card updates all rows in the task group
- completed_at is set only when status becomes done
- Stable per-column sort order
- Search and status filters
- Summary counts

Provide accessible alternatives to dragging, such as a status menu and move-up/move-down actions.

EVALUATIONS

Preserve the full practicum evaluation form.

Areas:

1. Competence and Dependability — 6 criteria
2. Effectiveness / Accuracy & Work Habits — 5 criteria
3. Interest Cooperation — 5 criteria
4. Personality / Interpersonal Relationship — 4 criteria

Each criterion requires an integer score from 1 to 5.

Preserve:

- Inclusive from/to dates
- To date cannot precede from date
- Remarks maximum 5000
- Multiple evaluations per student are allowed
- Evaluator type is professor or company_supervisor
- Total score is the sum of all 20 ratings
- Maximum score is 100
- Grade equivalents:
  - 97+: 1.0
  - 94+: 1.25
  - 91+: 1.5
  - 88+: 1.75
  - 85+: 2.0
  - 82+: 2.25
  - 79+: 2.5
  - 76+: 2.75
  - 73+: 3.0
  - Below 73: 5.0
- Students can view submitted evaluations
- Students/evaluators can download only authorized evaluation documents
- Admin can monitor all evaluations with search, evaluator, status, type, and date filters

REPORTS AND FILES

Supabase Storage buckets:

- `avatars`
- `accomplishment-photos`
- `requirement-templates`

Use private buckets and signed URLs for sensitive student content. A public avatar bucket is acceptable only if that privacy decision is explicit.

Submission rules:

- Only completed attendance records can receive a submission
- Accomplishment maximum: 5000 characters
- Up to six new images per save
- Maximum 4 MB per image
- Validate MIME type and actual file type
- Use deterministic user-scoped paths
- Never trust a client-provided object owner/path
- Staff only see submissions belonging to assigned students

Preserve the current accomplishment-report and evaluation downloads.

For parity, generating Word-compatible HTML from a Route Handler with the current MIME/content-disposition behavior is acceptable. Do not add a document-generation library unless a real `.docx` requirement is confirmed.

REQUIREMENTS

Create the nine current requirement definitions:

1. OJT Orientation
2. Student Health Record
3. Recommendation for OJT Individual
4. Application for OJT Individual
5. Acceptance Letter
6. Application for OJT Training Template
7. OJT Rules and Regulations
8. Parents Waiver
9. Narrative Report

Preserve the three steps:

- Orientation
- Medical
- Forms

Students may mark a missing physical requirement as submitted. Their coordinator verifies/unverifies it and records completed_by/completed_at.

Migrate the existing requirement templates from `public/requirements/`.

REALTIME MESSAGING

Preserve:

- Student ↔ coordinator before an OJT assignment exists
- Student ↔ assigned professor for internal OJT
- Student ↔ assigned company supervisor for external OJT
- Conversation list ordered by latest activity
- Unread status
- Mark messages read
- Send messages
- Edit only your own messages
- Delete only your own messages
- Show “edited” only after an actual edit
- Typing indicator
- Automatic realtime updates
- Authorization before selecting/opening a conversation

Store messages in Postgres.

Use Supabase Realtime Broadcast or authorized database-change subscriptions for new/edited/deleted messages. Use ephemeral Broadcast for typing indicators instead of migrating expired `chat_typing_indicators` rows unless persisted typing state proves necessary.

Use private channel authorization so guessing a conversation ID does not grant subscription access.

DASHBOARDS AND ROUTES

Preserve the public routes:

- `/`
- `/login`
- `/forgot-password`
- `/reset-password`
- `/verify-email`
- MFA challenge
- `/announcements`
- `/settings/profile`
- `/settings/password`
- `/settings/appearance`
- `/settings/two-factor`

Role redirect:

- `/dashboard` → appropriate role dashboard

Admin:

- `/admin/dashboard`
- `/admin/students`
- `/admin/coordinators`
- `/admin/professors`
- `/admin/supervisors`
- `/admin/attendance`
- `/admin/duty-hours`
- `/admin/ojt-assignments`
- `/admin/evaluations`
- `/admin/announcements`
- `/admin/audit-logs`

Coordinator:

- `/coordinator/dashboard`
- `/coordinator/student-management`
- `/coordinator/ojt-assignments`
- `/coordinator/requirements`
- `/coordinator/attendance`
- `/coordinator/duty-hours`
- `/coordinator/messages`

Professor:

- `/professor/dashboard`
- `/professor/assigned-students`
- `/professor/duty-hours`
- `/professor/tasks`
- `/professor/messages`
- `/professor/reports-submissions`
- `/professor/evaluations`

Company supervisor:

- `/supervisor/dashboard`
- `/supervisor/assigned-students`
- `/supervisor/duty-hours`
- `/supervisor/tasks`
- `/supervisor/messages`
- `/supervisor/reports-submissions`
- `/supervisor/evaluations`

Student:

- `/student/dashboard`
- `/student/attendance`
- `/student/duty-hours`
- `/student/evaluations`
- `/student/messages`
- `/student/reports-submissions`
- `/student/tasks`
- `/student/notes`
- `/student/pomodoro`
- `/student/pet`

Preserve dashboard calculations:

- Role counts
- Required/approved/pending hours
- Assignment status and type
- Weekly attendance/hours
- Assignment status distribution
- Recent students/trainees/assignments
- Requirement completion
- Pending review counts
- Filtered paginated tables

Use query-string-backed search, sorting, filters, and pagination where practical so pages are linkable and browser navigation works.

DESIGN SYSTEM

Do not redesign DutyTrack.

Preserve:

- Poppins typography
- DutyTrack logo and public assets
- Red primary/accent color
- Zinc/gray neutral palette
- White and zinc-50 light surfaces
- Zinc-800/900/950 dark surfaces
- Thin zinc borders
- Compact dashboard density
- Rounded cards and controls
- Light shadows
- Responsive sidebar
- Mobile sheet/sidebar
- Sticky top header
- Breadcrumbs
- Page title and description
- Dashboard statistic cards
- Search/filter toolbars
- Tables on desktop
- Usable stacked/card presentation on small screens
- Dark/light/system appearance
- Toast feedback
- Confirmation dialogs for destructive actions

Suggested semantic tokens:

- primary: red-500/red-600
- destructive: red
- success: green/lime
- warning: amber
- info: sky/blue
- background light: white/zinc-50
- background dark: zinc-900/zinc-950
- border light: zinc-200
- border dark: zinc-700/800

Use shadcn/ui components for:

- Sidebar
- Sheet
- Breadcrumb
- Button
- Card
- Input
- Textarea
- Select
- Checkbox
- Dialog
- AlertDialog
- DropdownMenu
- Popover
- Command
- Badge
- Avatar
- Table
- Tabs
- Progress
- Tooltip
- Skeleton
- Pagination
- Collapsible
- Sonner

Use Lucide icons consistently. Do not mix icon libraries or use emoji as functional icons.

Landing page sections to preserve:

- Sticky navigation
- Hero
- Dashboard preview
- Benefits
- Comparison
- FAQ
- Team
- Footer
- Login/dashboard CTA
- Light/dark mode

Preserve the restrained entrance/float animations, but support `prefers-reduced-motion`.

AUTHENTICATED LAYOUT

Create one shared authenticated shell that:

- Chooses navigation from the authenticated role
- Supports desktop collapse
- Uses a mobile Sheet
- Displays announcements, appearance, settings, avatar, email, and logout
- Prevents content overflow at common laptop widths
- Preserves the compact layout shown in the existing preview screenshots

Do not duplicate the entire shell five times. Use a role-to-navigation configuration with Lucide icon component references.

PRODUCTIVITY PROTOTYPES

Pomodoro:

- Port to a Client Component
- Focus 25 minutes
- Short break 5 minutes
- Long break 15 minutes
- Start, pause, reset, skip, ±1 minute
- Four-session cycle
- Persist state in localStorage
- Preserve session/focus statistics
- Use a stable timestamp-based timer so background tabs do not lose time

Pet Streak:

- Port the existing localStorage prototype faithfully
- Keep its “In Progress” designation
- Preserve pet stats, streak, rewards, care actions, decay, and activity log
- Do not create Supabase tables for it during parity work

STUDENT NOTES

Preserve:

- Create
- Select
- Autosave title/body
- Untitled fallback
- Search title/body
- Pin/unpin
- Delete
- Pinned-first and recently-updated ordering
- 255-character title limit
- 20,000-character body limit
- Strict owner-only RLS

AUDIT LOGS

Record important mutations:

- Account creation/update/deletion
- Assignment creation/update/deletion
- Announcement changes
- Requirement verification
- Duty-hour approval/rejection
- Other privileged administrative mutations

Capture old/new values without passwords, tokens, MFA secrets, service keys, or file binary data.

Admin can search/filter/sort audit logs. Other roles cannot read the global audit log.

DATA MIGRATION

Create an idempotent migration/import strategy.

Requirements:

- Export existing Laravel users and related records
- Create Supabase Auth identities
- Build a legacy Laravel user ID → Supabase UUID mapping
- Import profiles and role-specific profiles
- Import assignments before dependent records
- Import attendance, duty hours, submissions, evaluations, tasks, notes, requirements, conversations, messages, announcements, and audit logs
- Preserve timestamps where possible
- Copy uploaded files into the correct Storage buckets
- Rewrite stored paths
- Verify row counts and foreign-key relationships
- Produce a reconciliation report
- Never print password hashes or secrets
- If Laravel password hashes cannot be migrated safely using a supported Supabase mechanism, require password resets rather than handling plaintext passwords

Do not copy hardcoded student-directory PII into browser code. If the directory is still required, import it into a protected staff-only table or an encrypted/admin-controlled import source.

TESTING

Treat the existing Laravel feature tests as the behavioral specification.

Create tests for at least:

1. Role redirect and forbidden cross-role access
2. Coordinator can access only their students
3. Internal assignment requires professor
4. External assignment requires company supervisor
5. One applicable assignment per student
6. Attendance punch order
7. Location validation
8. Late status after 07:00
9. Early time starts counting at 07:00
10. Hours stop at 17:00 unless overtime is enabled
11. Duty-hour approval authorization
12. Staff cannot approve unrelated students
13. Task assignment authorization
14. Student task movement and completed_at
15. Grouped staff task movement
16. Evaluation validation and total score
17. Student sees only their evaluations
18. Submission requires completed attendance
19. File-count and size validation
20. Private notes ownership
21. Messaging participant authorization
22. Own-message edit/delete only
23. Requirement submit/verify permissions
24. RLS denial tests using separate users
25. Storage policy tests

Add focused end-to-end flows for each role. Do not create a giant test framework unrelated to the migration.

VERIFICATION

Before calling the migration complete:

- Run TypeScript checking
- Run lint
- Run unit/integration tests
- Run production build
- Test every route in the parity matrix
- Test desktop and mobile layouts
- Test light and dark themes
- Test keyboard navigation
- Test empty, loading, error, and populated states
- Test RLS using real authenticated sessions
- Test files with unauthorized users
- Test realtime chat between two accounts
- Compare major pages against existing screenshots and Blade pages
- Confirm there is no service-role key or secret in the browser bundle

SECURITY CLEANUP REQUIRED

During migration:

- Remove the attendance bypass from deployable code
- Rotate and restrict the Google API key currently stored in `todo.txt`
- Remove secrets and personal directory data from source control
- Do not expose student phone numbers or emails through overly broad RLS
- Sanitize downloadable document content
- Validate all Server Action and Route Handler input
- Prevent role changes through profile updates
- Prevent client-supplied timestamps for attendance and approvals
- Use signed URLs for private files
- Add confirmation for destructive operations

DELIVERABLES

The migration is complete only when it includes:

- Running Next.js application
- Supabase SQL migrations
- RLS policies
- Storage policies
- Auth flows
- Data migration/import script
- Generated Supabase TypeScript types
- Route parity matrix
- Test suite
- Environment-variable example without secrets
- Local setup documentation
- Deployment instructions
- Migration reconciliation report
- Rollback/cutover checklist

WORKING STYLE

- Preserve behavior before making improvements
- Make small, reviewable changes
- Reuse shared behavior only where real duplication exists
- Do not build speculative abstractions
- Do not silently drop features
- Do not silently “fix” business rules without documenting the difference
- Stop and document ambiguity when the Laravel implementation and roadmap disagree
- Keep Laravel intact until the Next.js parity checklist is accepted
```

## Important review findings

- `todo.txt` contains an exposed Google API key; rotate and restrict it.
- The repository hardcodes significant student PII in `StudentDirectory.php`; never send that data in the Next.js client bundle.
- The attendance page contains production-callable test bypasses for 60 minutes and 8 hours; these should not be migrated.
- Public registration creates users without roles, making their dashboard unusable.
- The database currently allows only one assignment ever per student—not merely one active assignment. Decide whether historical assignments are needed after parity.
- Pomodoro and Pet Streak are localStorage prototypes; they do not currently use the backend.
