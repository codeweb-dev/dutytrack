# Proposed folder structure

Create folders only when a phase needs them.

```text
next-js-dutytrack/
  app/
    (public)/page.tsx
    (auth)/...
    (workspace)/admin|coordinator|professor|supervisor|student/...
    api/exports/.../route.ts
  actions/                    # small authenticated mutations when shared by a page
  components/ui/              # shadcn generated primitives
  components/                 # shared sidebar, page header, table/filter, status UI
  features/
    directory/ assignments/ requirements/ attendance/ duty-hours/
    submissions/ evaluations/ tasks/ notes/ messages/ announcements/ dashboard/
  lib/supabase/server.ts
  lib/supabase/browser.ts
  lib/utils.ts
  server/authz.ts
  server/audit.ts
  schemas/                    # only schemas reused across features
  types/database.ts           # generated from target schema
  hooks/                      # only reusable browser hooks
  supabase/migrations/
  tests/
```

Each feature may contain `components/`, `server/`, `schema.ts`, and tests when needed. Pages compose; server modules own business rules and queries. Keep role variants within a domain when rules overlap, with explicit role scope. Avoid a generic repository layer or global client store until a concrete repeated need appears. The Laravel `resources/views/pages/*` tree is the route map, not a mandate to mirror every file.
