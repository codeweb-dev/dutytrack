# Approved and conditional stack

| Layer / dependency | Purpose | Approved / required | Alternative and reason |
|---|---|---|---|
| Next.js latest stable at Phase 01 | App Router/SSR/actions/handlers | Yes / yes | Plain React requires extra routing/server stack. Pin after checking current docs. |
| React, TypeScript strict | UI/types | Yes / yes | Existing Laravel view stack is source-only. |
| Tailwind CSS, shadcn/ui, Lucide React | Styling/primitives/icons | Yes / yes | Avoid a second UI library or copied Flux icons. |
| Supabase PostgreSQL/Auth/RLS | Data, identity, DB access control | Yes / yes | No parallel custom auth service. |
| Supabase Storage | Profile and accomplishment photos | Yes / when files migrate | Local public disk lacks target portability; use private buckets/signed URLs. |
| Supabase Realtime | Assignment-linked chat | Yes / only chat | Polling fallback if availability requires it; no realtime for ordinary dashboard counts. |
| `@supabase/supabase-js`, `@supabase/ssr` | Typed, cookie-aware clients | Recommend / yes | Raw HTTP duplicates auth/session handling. Confirm current package guidance in Phase 01. |
| Zod | Server input validation | Yes / yes | Native checks alone make complex forms inconsistent. |
| React Hook Form (+ resolver) | Complex assignment/account/evaluation forms | Yes / conditional | Native form/Server Action for small forms. |
| ESLint, `tsc --noEmit` | Static checks | Yes / yes | No formatter package required initially; use consistent project formatting. |
| Test runner + browser E2E tool | Domain, RLS and flows | Choice pending / yes | Select one runner and one E2E tool in Phase 01; do not duplicate suites. |
| Deployment host, email provider, monitoring/logging | Runtime operations | OPEN QUESTION / conditional | Decide from production requirements and actual host. Supabase Auth emails need deliverability plan. |
| Scheduled/background processing | None confirmed beyond queue infrastructure | Deferred / no | No active Jobs or schedules in source; use native hosted cron only for a proven need. |
| Word-compatible `.doc` output | Existing two exports | Yes / yes | Existing code sends HTML with MS Word content type. Match output first; add document library only if fidelity tests fail. |
| Map library | Attendance location picker/display | Conditional | Current UI loads Leaflet/OpenStreetMap by CDN; choose a maintained client integration only when implementing maps. |
| Laravel Excel equivalent | None | No / no | Package installed but no active `app/Exports`/`Excel::` flow found. |
