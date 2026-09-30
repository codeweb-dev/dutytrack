# Laravel → target map

| Current evidence | Target responsibility | Notes |
|---|---|---|
| `routes/web.php`, `routes/settings.php` | App Router route groups/pages | Preserve route-level role and `/dashboard` redirect. |
| Livewire/Volt `.php` + `.blade.php` | Server Component + focused Server Action | Client widget for geolocation, Kanban, chat, local timers. |
| `app/Livewire/Forms/*` | Zod schema + domain operation | Keep conditional validation and ownership checks. |
| Eloquent models and migrations | Typed PostgreSQL schema/queries | See schema map; keep legacy IDs. |
| Spatie `role:` middleware and query scopes | Server authorization + RLS | Five role values; no named permissions seeded. |
| `app/Support/AuditLogger.php` | Transactional audit operation | Preserve selected audit events; decide additional coverage explicitly. |
| `AttendanceLog` punch logic | Attendance domain operation + DB transaction | Four ordered punches, time window, overtime, duty log. |
| `PracticumEvaluationForm` | Typed rubric constants and total calculator | Four areas, 20 items, scores 1–5. |
| `EvaluationDocument`, `AccomplishmentReportDocument` and export Blades | Authorized download Route Handlers | Existing `.doc` is HTML/Word-compatible; compare rendered outputs. |
| Livewire uploads / Laravel public disk | Supabase Storage with signed URLs | Photos and report images; static requirement templates remain static assets. |
| `ConversationMessageSent`, `ConversationTypingStarted`, Reverb/Echo | Supabase Realtime private chat | RLS/channel authorization, fallback refresh. |
| Fortify, `User`/2FA, settings views | Supabase Auth + profile/settings UI | Confirm 2FA/password/email parity; public registration creates roleless user today. |
| `app/Support/*Directory.php` | Reviewed, access-controlled import/reference | Contains hard-coded personal records; never bundle client side. |
| `routes/console.php`, `config/queue.php` | No job/schedule port until proven | No custom queued jobs or scheduled task found. |
| Flux UI/Tailwind and role sidebars | shadcn/Tailwind/Lucide shell | Preserve IA/status meaning; do not copy Flux package. |
