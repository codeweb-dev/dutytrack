# Master migration checklist

A checked box requires the linked phase definition of done and evidence; planning documents being written does not complete a production audit or implementation.

## Planning / foundation
- [ ] [00 audit](phases/00-project-audit.md): compare this source audit with actual running Laravel/database and resolve open questions.
- [ ] [01 app foundation](phases/01-nextjs-foundation.md): scaffold, lint/type/test/build and setup guide.
- [ ] [02 Supabase](phases/02-supabase-foundation.md): schema, Auth ID map, RLS baseline.
- [ ] [03 authentication](phases/03-authentication.md): auth/settings/2FA parity decisions.
- [ ] [04 authorization](phases/04-roles-and-permissions.md): five-role and record access tests.
- [ ] [05 design shell](phases/05-design-system-and-layout.md): five role workspaces.

## Domain modules
- [ ] [06 directories](phases/06-directories-and-profiles.md): staff/student accounts and profiles.
- [ ] [07 assignments](phases/07-ojt-assignments.md): internal/external placements.
- [ ] [08 requirements](phases/08-requirements.md): physical checklist.
- [ ] [09 attendance](phases/09-attendance-and-location.md): punches, geolocation and bypass decision.
- [ ] [10 duty hours](phases/10-duty-hours-and-approvals.md): reviewer decisions/monitoring.
- [ ] [11 submissions/exports](phases/11-submissions-and-exports.md): photos and accomplishment doc.
- [ ] [12 evaluations](phases/12-evaluations.md): rubric and doc.
- [ ] [13 tasks/productivity](phases/13-tasks-and-productivity.md): Kanban, notes, local tools.
- [ ] [14 messaging](phases/14-messaging-and-realtime.md): private chat.
- [ ] [15 admin/system](phases/15-admin-announcements-and-audit.md): announcements/audit.
- [ ] [16 dashboards](phases/16-dashboards-and-monitoring.md): five dashboards and monitoring.

## Release
- [ ] [17 integrations](phases/17-integrations-and-automation.md): prove need before adding jobs/mail/map extras.
- [ ] [18 parity/security](phases/18-parity-performance-security.md): regression, RLS, accessibility and performance.
- [ ] [19 data rehearsal](phases/19-data-migration-rehearsal.md): repeatable import and reconciliation.
- [ ] [20 production readiness](phases/20-production-cutover.md): backup, cutover, smoke, rollback readiness.
