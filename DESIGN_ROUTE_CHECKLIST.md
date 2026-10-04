# DutyTrack redesign route checklist

- [x] Public: `/` landing, navigation, benefits, comparison, FAQ, team, footer.
- [x] Auth: login, register, forgot/reset password, verify email, confirm password, two factor challenge through the shared auth layout.
- [x] Shared: `/announcements`, messages for coordinator/professor/supervisor/student, app shell, mobile navigation, appearance, profile, password, two factor.
- [x] Admin: dashboard; students, coordinators, professors, supervisors; attendance, duty hours, assignments, evaluations; announcements, audit logs.
- [x] Coordinator: dashboard, student management, assignments, requirements, attendance, duty hours, messages.
- [x] Professor: dashboard, assigned students, duty hours, evaluations, report submissions, tasks, messages.
- [x] Supervisor: dashboard, assigned students, duty hours, evaluations, report submissions, tasks, messages.
- [x] Student: dashboard, attendance, duty hours, evaluations, report submissions, tasks, notes, messages, pomodoro, pet streak.
- [x] `/admin/partners` inspected: the view is not routed, so it is not exposed in navigation.

Shared Flux controls, tables, cards, forms, dialogs, colors, focus states, and responsive spacing are styled centrally. Route permissions and Livewire actions remain in place.

Visual screenshot review at 1440, 1024, 768, and 390 px remains pending because the in-app browser control tool is unavailable in this session.
