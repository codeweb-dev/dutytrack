# Bug Report: DutyTrack

Audited 2026-11-12. Found by tracing the code, not reproduced in the running app.

| # | Severity | Area | Summary |
|---|---|---|---|
| 1 | High | Archive | Archiving a supervisor leaves their students attached to a dead account |
| 2 | Low | Archive | "Assigned" counts include students whose supervisor is archived |
| 3 | High | Tasks | Tasks are orphaned after reassignment |
| 4 | Medium | Tasks | Attendance follows reassignment, tasks don't |
| 5 | Medium | Messages | Old supervisor keeps the chat after reassignment |
| 6 | Low | Students | Duplicate students when sorting by supervisor |
| 7 | Medium | Archive | Archived students still count for their supervisor |
| 8 | High | Tasks | Students can approve or un-approve their own tasks |
| 9 | Medium | Evaluations | Evaluation page crashes for an archived student |
| 10 | Medium | Audit logs | Every coordinator sees all audit logs |
| 11 | Low | Archive | Any coordinator can restore any archived coordinator |
| 12 | Low | Archive | Restoring an assignment can bring back a dead supervisor link |
| 13 | High | Attendance | One failed holiday-API call blocks time-in for everyone for 24 hours |
| 14 | High | Assignments | Editing an assignment can swap its student, which breaks chat |
| 15 | Medium | Assignments | Changing a supervisor's type breaks their existing assignments |
| 16 | Medium | Assignments | Inactive supervisors and pending students can still be assigned |
| 17 | Medium | Reports | Students can't submit reports for afternoon-only days |
| 18 | Medium | Security | Coordinator-created accounts get weak, guessable passwords |
| 19 | Medium | Announcements | Announcements go to every user and have no owner |
| 20 | Low | Messages | Polling marks messages read before checking access |
| 21 | Low | Photos | Old photo is deleted before the database save commits |

## Scenario 1: Supervisor archived while students are still assigned

### 1. Archiving a supervisor ignores their assigned students
- **Where:** `resources/views/components/coordinator/supervisors/⚡delete.blade.php:31`
- **What happens:** Only the user is soft-deleted. Their `ojt_assignments` rows still point to the archived account.
  - **Student:** still has an assignment, but the supervisor shows as blank or "Unassigned". Attendance submissions, report submissions and tasks sent "for review" go to nobody, because `companySupervisor` is null and the notifications go nowhere.
  - **Coordinator:** the student doesn't appear in "Create assignment", because `whereDoesntHave('ojtAssignment')` still sees the old assignment. You can only fix it by finding the assignment and editing it, and nothing tells you this is needed.
  - **Messages:** `resources/views/pages/⚡messages.blade.php:204` still creates or shows a chat between the student and the archived supervisor, so the student can send messages nobody will read.
- **Fix:** Block the archive while the supervisor has active assignments, e.g. "Reassign these 3 students first." This works like the existing guard in `restoreAssignment`.

### 2. "Assigned" counts include students whose supervisor is archived
- **Where:**
  - `resources/views/pages/coordinator/⚡student-management/student-management.php:94`, `:246`
  - `resources/views/pages/coordinator/⚡dashboard.blade.php:65`
- **What happens:** These use `whereNotNull('company_supervisor_id')`, so a student whose supervisor is archived is counted as **Assigned**, while their row says **Unassigned**.
- **Fix:** Use `whereHas('ojtAssignment.companySupervisor')`, which ignores archived supervisors.

## Scenario 2: Student moved to a new supervisor (old tasks)

### 3. Tasks are orphaned after reassignment (most serious)
- **Where:**
  - `resources/views/pages/supervisor/⚡tasks/tasks.php:299-300` (`taskQuery`)
  - `resources/views/components/supervisor/⚡sidebar.blade.php:25-29`
  - `resources/views/pages/supervisor/⚡dashboard.blade.php:67`
  - `resources/views/pages/student/⚡tasks/tasks.php:62`
- **What happens:** The supervisor board only shows a task when both of these are true:
  ```php
  ->where('assigned_by', Auth::id())
  ->whereHas('assignment', fn ($q) => $q->where('company_supervisor_id', Auth::id()))
  ```
  Editing the assignment to change the supervisor keeps the same assignment row. After reassignment:

  | Who | Sees the old tasks? | Why |
  |---|---|---|
  | Old supervisor | No | The assignment now belongs to the new supervisor |
  | New supervisor | No | `assigned_by` is still the old supervisor |
  | Student | Yes | The student board filters by `student_id` only |

  The student can still move old tasks to **In Review**, but the notification goes to `assignedBy`, the old supervisor, who can't see the task. If that supervisor is archived, nobody is notified. These tasks stay stuck in review forever and inflate the student's "pending tasks" count on the sidebar and dashboard.

  The same thing happens if you **archive the assignment and create a new one**: `whereHas('assignment')` skips the soft-deleted assignment, so the old tasks disappear for every supervisor but stay on the student's board.
- **Fix:** Remove `where('assigned_by', ...)` from the supervisor `taskQuery`, the sidebar counter and the dashboard counter, and rely on the assignment's supervisor. In the student `sortTask`, notify `$task->assignment?->companySupervisor` instead of `assignedBy`.
- **Open decision:** Should unfinished tasks **go to the new supervisor** (recommended; the fix above does this) or be **archived**?

### 4. Attendance and tasks behave differently
- **What happens:** Attendance and report submissions move to the new supervisor on reassignment, because they only check `assignment.company_supervisor_id`. Tasks don't, which points to tasks being the inconsistent one.
- **Fix:** Covered by #3.

### 5. The old supervisor keeps the chat and isn't notified
- **Where:**
  - `resources/views/pages/⚡messages.blade.php:276-278` (`conversationQuery`)
  - `app/Livewire/Forms/Coordinator/OjtAssignmentForm.php` (`update`)
- **What happens:** After reassignment, the old supervisor's conversation with the student stays open (`conversationQuery` filters by `participant_id`), so they can keep messaging a student who is no longer theirs. They also get no "trainee removed" notification, because `update` only notifies the new supervisor.
- **Fix:** Limit supervisor conversations to assignments where they are the current `company_supervisor_id`. In `update`, notify the old supervisor when it changes.

## Other bugs

### 6. Duplicate students when sorting by supervisor
- **Where:** `resources/views/pages/coordinator/⚡student-management/student-management.php:256`
- **What happens:** The `leftJoin('ojt_assignments', ...)` has no `whereNull('ojt_assignments.deleted_at')`. A student who has both an archived and an active assignment shows up twice.
- **Fix:** Add the `deleted_at IS NULL` condition to the join.

### 7. Archived students still count for their supervisor
- **Where:**
  - `resources/views/components/coordinator/student-management/⚡delete.blade.php`
  - `resources/views/components/supervisor/⚡sidebar.blade.php:31`
  - supervisor assigned-students, evaluations and tasks queries
- **What happens:** Archiving a student leaves their assignment active. The supervisor's trainee totals, the evaluations badge and task cards still include them, just with an empty name or avatar.
- **Fix:** Add `whereHas('student')` to the supervisor assignment and task queries, or archive the assignment when the student is archived.

### 8. Students can approve or un-approve their own tasks
- **Where:**
  - `resources/views/pages/student/⚡tasks/tasks.php:44`
  - `resources/views/pages/student/⚡tasks/tasks.blade.php:29-32`
- **What happens:** The code only blocks a student from moving a task into **Revision**. Nothing stops them dragging a task straight to **Done**, which skips the supervisor's review. They can also drag an approved task back out of **Done**, which undoes the approval.
- **Fix:** In `sortTask`, add `abort_if($status === 'done' || $previousStatus === 'done', 403)`. In the blade, lock the Done column with `put: false, pull: false`.

### 9. A supervisor's evaluation page crashes for an archived student
- **Where:** `app/Livewire/Concerns/ManagesPracticumEvaluations.php:46`, `:77`
- **What happens:** This follows from #7: the student's assignment stays in the supervisor's Evaluations list. When the supervisor clicks Evaluate, line 46 reads `$assignment->student->display_name` on a student that is now null, which gives a 500 error. Saving would also fail, with a 404 at line 77.
- **Fix:** Covered by #7. Also use `?->` at line 46.

### 10. Every coordinator sees every other coordinator's audit logs
- **Where:**
  - `resources/views/pages/coordinator/⚡audit-logs/audit-logs.php:126`
  - `resources/views/pages/coordinator/⚡archive/archive.php:130`, `:176`
- **What happens:** `AuditLog::query()` has no filter, even though `audit_logs` has an `actor_id` column. The logs include student names, student numbers and supervisor names from other coordinators. Restoring logs on the Archive page isn't filtered either. This only matters if you have more than one coordinator.
- **Fix:** Filter by `actor_id = Auth::id()` on the audit logs page and on the Archive page's logs tab and restore action.

### 11. Any coordinator can restore any archived coordinator
- **Where:** `resources/views/pages/coordinator/⚡archive/archive.php:55`
- **What happens:** `->orWhereHas('roles', ojt_coordinator)` means every coordinator's Archive tab lists all archived coordinators and can restore them.
- **Fix:** Remove the `orWhereHas` branch, unless restoring other coordinators is intended.

### 12. Restoring an assignment can bring back a dead supervisor link
- **Where:** `resources/views/pages/coordinator/⚡archive/archive.php:160` (`restoreAssignment`)
- **What happens:** It doesn't check whether that assignment's supervisor is also archived. You can restore an assignment that points to an archived account, which puts you straight back into #1.
- **Fix:** Refuse the restore, with a toast, when `companySupervisor` is null.

## Rest of the system

### 13. One failed holiday-API call blocks time-in for everyone for 24 hours
- **Where:**
  - `app/Support/Holidays.php:13-33`
  - `resources/views/pages/student/⚡attendance/attendance.php:589`
- **What happens:** `Cache::remember(..., now()->addDay(), ...)` also caches the failure result `['available' => false]`. If date.nager.at times out once (the timeout is 3 seconds), every student's morning and afternoon time-in is blocked with "Holiday check unavailable" until the cache expires a day later.
- **Fix:** Cache only successful lookups. Call `Cache::get` first; on failure return without caching, or cache for about 5 minutes.

### 14. Editing an assignment can swap its student, which breaks chat
- **Where:**
  - `app/Livewire/Forms/Coordinator/OjtAssignmentForm.php:251-258`
  - `resources/views/components/coordinator/ojt-assignments/⚡edit.blade.php:28-39`
  - `resources/views/pages/⚡messages.blade.php:254-269`
- **What happens:** The Edit form lets you change the **student**, and it updates the same assignment row. Everything linked to that row by `ojt_assignment_id` stays attached to it: attendance logs, report submissions, tasks and the supervisor chat. Example: student A's assignment is changed to student B.
  - **Chat:** `ensureConversation` does a `firstOrCreate` on `(ojt_assignment_id, participant_id, role)`, so it finds A's existing conversation and never creates one for B. **B can't message the supervisor**, while A, who is no longer assigned, still can.
  - **A's data:** A's old logs, reports and tasks stay visible to the supervisor under an assignment that now belongs to B.
- **Fix:** Lock the student in the Edit form. To move a student, archive the assignment and create a new one.

### 15. Changing a supervisor's type breaks their existing assignments
- **Where:** `app/Livewire/Forms/SupervisorForm.php:290-296`
- **What happens:** A supervisor can be switched from internal to external, or the other way, while they have assignments. Those assignments keep the old `ojt_type`, so the types no longer match. When you next edit one of those assignments, the supervisor isn't in the dropdown, because it filters by `ojt_type`. Saving then fails the `Rule::exists(... supervisor_type)` check.
- **Fix:** Block the type change while the supervisor has active assignments, or update those assignments' `ojt_type` at the same time.

### 16. Inactive supervisors and pending students can still be assigned
- **Where:**
  - `resources/views/components/coordinator/ojt-assignments/⚡create.blade.php:17-32`
  - `⚡edit.blade.php:28-45`
  - `OjtAssignmentForm::ensureCompanySupervisor` / `ensureManagedStudent`
- **What happens:** Neither list filters on `users.status`, and neither does the server-side check. A coordinator can assign a student to a supervisor who has been set to **inactive** and can't log in, which leads to the same dead end as #1. They can also assign a **pending** sign-up who hasn't been approved yet.
- **Fix:** Add `where(fn ($q) => $q->whereNull('status')->orWhere('status', 'active'))` to both lists and both `ensure*` checks. Also block setting a supervisor inactive while they have assignments, the same as the archive fix in #1.

### 17. Students can't submit reports for afternoon-only days
- **Where:**
  - `resources/views/pages/student/⚡reports-submissions/reports-submissions.php:146-155`
  - `resources/views/pages/supervisor/⚡reports-submissions/reports-submissions.php:54-59`
- **What happens:** Attendance allows an afternoon-only day, with time-in at 1 PM and time-out at 5 PM. The student's report list requires **all four** punches, so that day never shows up and the student can't submit a report for it. The supervisor's list and the generated Weekly Accomplishment document only require `afternoon_time_out`, so the supervisor sees the day as missing a report.
- **Fix:** On the student side, use the same rule as the supervisor: `whereNotNull('afternoon_time_out')`.

### 18. Coordinator-created accounts get weak, guessable passwords
- **Where:**
  - `app/Livewire/Forms/SupervisorForm.php:73`, `:114-121`
  - `app/Livewire/Forms/Coordinator/StudentForm.php:91`
- **What happens:** Supervisor passwords need only `min:2`, and `fillPasswordFromLastName()` sets the password to the supervisor's **last name**. Student passwords set by the coordinator need only `min:3`. Nothing forces a password change on first login. Anyone who knows a supervisor's email and last name can log in as them, and see their students' attendance, GPS locations and photos.
- **Fix:** Use the same `passwordRules()` as registration. Either drop the last-name default or force a password change on first login.

### 19. Announcements go to every user and have no owner
- **Where:**
  - `app/Livewire/Forms/AnnouncementForm.php:83-84`
  - `database/migrations/2026_04_10_072855_create_announcements_table.php`
- **What happens:**
  - **Every user is notified:** `Notification::send(User::query()->whereKeyNot(Auth::id())->get(), ...)` reaches every user, including other coordinators' students, pending sign-ups and inactive accounts.
  - **No owner:** the `announcements` table has no owner column, so any coordinator can edit, archive or restore any other coordinator's announcement.
  - **Slow to send:** `SystemNotification` isn't queued (no `ShouldQueue`, even though `QUEUE_CONNECTION=database`), so posting one inserts a row for every user while the request waits.
- **Fix:** If announcements are meant to be per program, add an owner column (e.g. `coordinator_id`) and scope the notification to that coordinator's students and supervisors. Either way, add `implements ShouldQueue` to `SystemNotification`.

### 20. Polling marks messages read before checking access
- **Where:** `resources/views/pages/⚡messages.blade.php:23`, `:177-181`
- **What happens:** `selectedConversationId` isn't `#[Locked]`, and `pollMessages()` calls `markConversationRead()` before anything authorizes the conversation. A user can tamper with the ID to mark another conversation's messages as read. It can't be used to read them: `chatMessages` does authorize and throws a 403.
- **Fix:** Add `#[Locked]` to `selectedConversationId`, or call `authorizeConversation()` first in `pollMessages`.

### 21. Old photo is deleted before the database save commits
- **Where:**
  - `app/Livewire/Forms/Coordinator/StudentForm.php:207-214`
  - `app/Livewire/Forms/SupervisorForm.php:257-264`
- **What happens:** The old photo file is deleted inside the transaction, before the save commits. If the save fails, for example because of a duplicate email or student ID, the transaction rolls back but the file is already gone. The account then points at a photo that no longer exists.
- **Fix:** Delete the old file after `DB::transaction(...)` returns.

## Checked and fine
- File and PDF routes in `routes/web.php`: every one checks ownership.
- Login for archived, rejected, pending and inactive accounts (`FortifyServiceProvider`). Sessions are cleared on archive and on set-inactive (`SESSION_DRIVER=database`).
- Attendance punch order, the 8–12 and 1–5 windows, the 8-hour daily cap, the cap at required hours, and the guards against double punching.
- Geofence distance math. It trusts browser GPS, which is already noted in the code.
- Timezone is `Asia/Manila`.
- Requirements: uploads are tied to the student who owns them, approval is final, and only files that exist can be approved.
- Evaluations: there's a lock and a duplicate check, and only the coordinator who owns the student can approve.
- Notes, resumes and notifications are all scoped to their owner.
- Deleted chat messages hide their text from the other person.
- Certificate, DTR and accomplishment report: inputs are validated, and the files are generated only for the logged-in student or the coordinator who owns them.
- Hours carry over when a student changes supervisor, because they're summed by `student_id`.
