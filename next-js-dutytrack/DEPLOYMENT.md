# Deployment and operations plan

Target hosting is OPEN QUESTION. Phase 01 pins runtime and package versions; Phase 02 provisions separate Supabase dev/staging/prod projects. Keep application deployment, SQL migrations and data import independently repeatable. Production service-role credentials stay in server secret storage; public URL/anon key may be exposed only with complete RLS.

## Environment and release
- Required public: `NEXT_PUBLIC_SUPABASE_URL`, `NEXT_PUBLIC_SUPABASE_ANON_KEY`. Server-only if actually needed: `SUPABASE_SERVICE_ROLE_KEY`. Add email/map/observability secrets only after their phase selects a provider. Document exact callback/redirect URLs and domain cookie settings.
- Use CI for install, lint, strict typecheck, unit/integration, RLS tests and production build. Run smoke E2E in staging against sanitized data. Never run mutable production migrations implicitly on every app boot.
- Review migrations and backups before apply; include reversible or forward-repair instructions. Monitor auth failures, upload/export errors, DB latency, storage, Realtime disconnects, and app logs without exposing personal data.
- Configure TLS, backups/restore drills, retention, alert ownership, access to Supabase dashboard and key rotation. Establish incident and rollback contacts.
- Cutover: Laravel snapshot and write freeze, final import/reconciliation, target smoke tests for all five roles, switch traffic, monitor, rollback if thresholds fail. Rollback must account for writes made in Next.js after traffic switch.

No custom Laravel scheduled jobs or queued app jobs were found. Do not add cron/worker infrastructure without a documented workload. Fortify auth emails require a deliverable target email configuration; custom notification emails are not evidenced.
