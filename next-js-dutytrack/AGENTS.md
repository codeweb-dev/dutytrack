# DutyTrack migration agent instructions

## Role and project
Act as a senior Next.js, TypeScript, Supabase/PostgreSQL, migration, and security engineer. DutyTrack tracks OJT students, coordinators, professors, company supervisors, assignments, attendance, duty hours, requirements, evaluations, tasks, submissions, and messages. The new independent app is being planned at the owner's request; the repository does not state a business reason for changing frameworks. Build functional parity and a secure, maintainable deployment.

## Source of truth
**The existing Laravel DutyTrack application is the behavioral source of truth during migration.** If this documentation and Laravel behavior conflict, inspect the Laravel route, Livewire class/form, model, migration, and test before changing behavior. `../routes/web.php`, `../resources/views/pages/`, `../app/`, and `../database/migrations/` are primary evidence. Labels: CONFIRMED = code verified; INFERRED = likely, requires verification; OPEN QUESTION = repository cannot decide; LEGACY BEHAVIOR = existing behavior needing product/security review.

## Approved stack
Next.js latest stable at implementation time, App Router, React, strict TypeScript, Tailwind CSS, shadcn/ui, Lucide React, Supabase PostgreSQL/Auth/Storage/RLS, Zod, React Hook Form only for complex forms, ESLint. Recheck official versions when Phase 01 begins. No package is installed by this documentation.

## Development loop
1. Read this file, the relevant phase, and referenced Laravel code before every task.
2. Implement one phase and one feature at a time. Preserve behavior; do not add speculative product features or unrelated refactors.
3. Use the simplest readable implementation. Add a dependency only after documenting its concrete need and approval.
4. Review the diff; run new feature, permission, and regression checks; do not advance until the phase definition of done passes.
5. Record any CURRENT → TARGET → WHY → MIGRATION IMPACT decision affecting schema or behavior.

## Boundaries
- `app/`: App Router route groups, pages, layouts, `loading.tsx`, `error.tsx`, `not-found.tsx`; thin composition.
- `components/ui/`: generated shadcn primitives; `components/`: shared product UI only when reused.
- `features/<domain>/`: domain screens, small client widgets, schemas, and server operations for assignments, attendance, duty hours, requirements, evaluations, tasks, reports, chat, directory, admin.
- `server/`: authorization, domain rules, transactions, document generation and sensitive integrations. Never import into browser code.
- `actions/`: focused Server Actions for authenticated form mutations; use `server/` underneath.
- `lib/`: small cross-domain utilities and Supabase clients. `lib/supabase/server.ts` uses request cookies, `browser.ts` uses public credentials only.
- `hooks/`: browser-only reusable hooks if actually needed. `types/`: shared domain types and generated database types. `schemas/`: shared Zod inputs only when reused.
- `supabase/migrations/`: reviewed SQL migrations and RLS policies. `tests/`: focused behavior, RLS, and end-to-end parity tests.
Do not create every folder on day one; create it when a phase needs it.

## Next.js rules
Server Components by default. Client Components only for interactive widgets (maps, chat, task drag, local timer/pet). Server Actions validate with Zod and recheck authorization; Route Handlers handle downloads, callbacks, or external API needs. Authenticate on the server for every protected request. Use `loading.tsx`, `error.tsx`, `not-found.tsx`, and Suspense where route data or UX requires them. Explicitly choose dynamic/no-store behavior for private, changing data; revalidate after mutations. Never cache another user's data.

## Supabase and security rules
Use Supabase Auth IDs mapped explicitly to legacy bigint user IDs; settle mapping in Phase 02 before data import. Browser access uses anon key plus RLS; server user client also respects RLS. Service-role access is server-only and narrowly scoped for migration/admin operations, never for normal user queries. Enable RLS on every application table and Storage bucket; test own, assigned, unrelated, and unauthenticated access. Revalidate role and relationship on every write and download. Keep secrets out of `NEXT_PUBLIC_*`. Validate uploaded type, size, count, ownership and path; use signed access for private files. Evaluate CSRF/session behavior of chosen auth/action flow, rate-limit auth and abusive mutations, and make audit-sensitive writes transactional. Treat directory data in `app/Support/*Directory.php` as sensitive source data, not seed fixtures for public clients.

## UI and TypeScript
Preserve role-specific navigation and information hierarchy from the five sidebars. Use shadcn/ui, Tailwind, Lucide icons; preserve accessible labels, keyboard operation, status text, and responsive tables/forms. Strict TypeScript, no `any` without documented reason, typed database access, and server-side Zod validation. Never infer authorization from hidden UI controls.

## Database rules
Derive table design from migrations and models. Review constraints, status values, cascade behavior, decimal precision, timestamps, and relationships. Document all changes as CURRENT → TARGET → WHY → MIGRATION IMPACT. Do not treat Laravel framework cache/session/jobs tables as automatically required in Supabase.

## Reporting after every task
Report (1) what was inspected, (2) what changed, (3) files changed, (4) tests run, (5) issues discovered, (6) next recommended phase. The attached PDF's anchor → task → constraints → reference, focused feature loop, diff review, and regression check are the working method; its Expo/Clerk/mobile stack examples are reference material, not DutyTrack requirements.
