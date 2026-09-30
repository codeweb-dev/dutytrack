# DutyTrack Next.js migration plan

This folder contains **planning Markdown only**. The Laravel application one directory up remains the behavioral source of truth. No Next.js application, database migration, or Laravel refactor is part of this planning task. The target is an independent Next.js App Router, TypeScript, shadcn/ui and Supabase app.

Start with [AGENTS.md](AGENTS.md), then [PROJECT_CONTEXT.md](PROJECT_CONTEXT.md), [FEATURE_INVENTORY.md](FEATURE_INVENTORY.md), [ROLE_PERMISSION_MATRIX.md](ROLE_PERMISSION_MATRIX.md), [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md), and the relevant [phase](phases/). Use [MIGRATION_CHECKLIST.md](MIGRATION_CHECKLIST.md) to record verified progress. Do not skip phases without understanding dependencies. Phase 00 is the first implementation-time audit; these planning documents are its baseline, not a live production data audit.

## Future setup, only in Phase 01
From the repository root, after Phase 00 is signed off:
```sh
npx create-next-app@latest dutytrack-temp --ts --tailwind --eslint --app --src-dir=false --import-alias '@/*'
cd dutytrack-temp
npx shadcn@latest init
npm install @supabase/supabase-js @supabase/ssr zod lucide-react
# Add react-hook-form and @hookform/resolvers only for a complex form phase.
npm run dev
npm run lint
npx tsc --noEmit
npm test # after the test script is selected in Phase 01
```
The current folder already contains planning files: the command above scaffolds a clean temporary directory. Move reviewed generated files here after Phase 00 and Phase 01 approval, preserving these Markdown files. Verify current official commands and versions at execution time. No secret belongs in Git.

Future `.env.local` placeholders:
```dotenv
NEXT_PUBLIC_SUPABASE_URL=
NEXT_PUBLIC_SUPABASE_ANON_KEY=
SUPABASE_SERVICE_ROLE_KEY=
```
The first two are public identifiers/anon credentials guarded by RLS. `SUPABASE_SERVICE_ROLE_KEY` is server-only and should be absent until a real server task requires it. Configure a Supabase project and migrations in Phase 02; never paste production values into prompts.

## Order
00 audit → 01 app foundation → 02 database/RLS foundation → 03 auth → 04 role authorization → 05 UI shell → 06 directories/profiles → 07 assignments → 08 requirements → 09 attendance → 10 duty approvals → 11 submissions/exports → 12 evaluations → 13 tasks/productivity → 14 chat/realtime → 15 admin/announcements/audit → 16 dashboards/monitoring → 17 integration/automation review → 18 parity/security/performance → 19 data rehearsal → 20 cutover/readiness.
