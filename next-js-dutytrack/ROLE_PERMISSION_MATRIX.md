# Roles and effective permissions

Seeded roles: `admin`, `ojt_coordinator`, `professor`, `company_supervisor`, `student` (`app/Enums/Role.php`, `database/seeders/RoleSeeder.php`). `*` means only records related by ownership/assignment, `R` read, `C` create, `E` edit/status mutation, `D` delete, `A` approve, `X` export, `M` manage. Blank means no routed capability found; it does **not** grant an untested permission. Spatie `HasRoles` and role middleware are active; no named permission seeding or policy/gate classes found. `model_has_permissions` and `role_has_permissions` still exist in schema.

| Role | Module | View | Create | Edit | Delete | Approve | Export | Manage / special |
|---|---|---|---|---|---|---|---|---|
| Admin | Dashboard, students, attendance, duty, assignments, evaluations | R all | | | | | | Monitoring/filter |
| Admin | Coordinators/professors/supervisors | R | C | E | D | | | Profiles, photos, roles, audit |
| Admin | Announcements/audit logs | R | C announcements | E announcements | D announcements/logs | | | Audit filtering |
| Coordinator | Own students/profile/requirements | R* | C student | E* | D* | A* physical receipt | | Directory autofill |
| Coordinator | Own assignments | R* | C* | E* | D* | | | Internal/external placement |
| Coordinator | Attendance | R* | | | | | | Monitor |
| Coordinator | Duty hours | R (current query appears broad) | | | | | | Monitor; verify scope |
| Coordinator | Messages | R* | C message | E own | D own | | | Own-student chat |
| Professor | Assigned students/duty hours | R* | | | | A*/reject | | Linked via `professor_id` |
| Professor | Evaluations | R* | C* | | | | X own evaluations | Rubric submission |
| Professor | Tasks | R* | C* | E*/reorder | | | | Multi-student group |
| Professor | Reports/messages | R* | C message | E own message | D own message | | | Assigned only |
| Supervisor | Assigned students/duty hours | R* | | | | A*/reject | | Linked via `company_supervisor_id` |
| Supervisor | Evaluations/tasks/reports/messages | R* | C* | E task/own message | D own message | | X own evaluations | Same scoped flows as professor |
| Student | Own attendance/duty/report | R own | C punch/submission | E submission | | | X accomplishment | Geolocation; LEGACY bypass |
| Student | Own evaluations/requirements | R own | C submitted marker | | | | X own evaluations | No self-approval |
| Student | Own tasks/notes/messages | R own | C note/message | E own task/note/message | D own note/message | | | Local Pomodoro/pet |
| All authenticated | Announcements/settings | R | | E own settings | D own account via settings | | | Profile/password/2FA |

## Enforcement evidence and target
`routes/web.php` wraps role workspaces in Spatie `role:*`; `auth,verified` wraps `/dashboard` and announcements but the role groups themselves do not visibly include an `auth` group. Spatie role middleware denies without role. Record-level rules appear in forms and scoped queries, not Laravel Policies. Examples: coordinator student `coordinator_id`, assigned staff `ojt_assignments.professor_id/company_supervisor_id`, student `student_id`, conversation participant, evaluator download ownership. Target: Supabase Auth → `public.users.auth_user_id` identity mapping → role membership (five role names) → server authorization → RLS on each table and Storage path. Keep a permission table only if production data reveals named grants; inspect live Spatie tables in Phase 00/02. Test every operation against own/assigned/unrelated/anonymous users, including direct actions/handlers and Realtime subscriptions.
