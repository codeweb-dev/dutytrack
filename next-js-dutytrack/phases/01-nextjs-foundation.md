# Phase 01 — Next.js foundation

## Goal

Create runnable strict TypeScript App Router foundation and reproducible checks.

## Why This Phase Exists

This phase is required before dependent work because it will create runnable strict typescript app router foundation and reproducible checks. It starts after 00 audit.

## Prerequisites

00 audit.

## Existing Laravel Reference

composer.json, package.json, resources/css/app.css, .github/workflows/*.

## Scope

Scaffold latest stable Next.js, Tailwind, shadcn, Lucide, ESLint, CI, env placeholders and setup guide.

## Out of Scope

No Supabase tables, auth or domain UI.

## Tasks

- [ ] Verify current official commands and pin versions.
- [ ] Scaffold in temporary location and preserve existing Markdown.
- [ ] Add scripts for lint, typecheck, test and build.
- [ ] Add public/server env example and one smoke check.
- [ ] Run clean install, lint, typecheck, test and build.

## Expected Files

app/layout.tsx, app/page.tsx, app/globals.css, package.json, tsconfig.json, components/ui, .env.example.

## Database Changes

None.

## Roles / Permissions

None yet.

## Business Rules

No domain behavior introduced.

## Edge Cases

Scaffold must not overwrite this documentation.

## Security Checks

No server secret in browser bundle.

## Testing

Unit smoke; integration build; permission/DB none; UI root route; regression lint/typecheck.

## Verification Checklist

- [ ] Start dev server and run all scripts from clean checkout.

## Definition of Done

Clean install and all checks pass; README commands accurate.

### Future setup commands (run only during this phase)

Check current official docs and versions first. Scaffold in a temporary clean folder to preserve this plan, then move reviewed generated files into `next-js-dutytrack/`:

```sh
npx create-next-app@latest dutytrack-temp --ts --tailwind --eslint --app --src-dir=false --import-alias '@/*'
cd dutytrack-temp
npx shadcn@latest init
npm install @supabase/supabase-js @supabase/ssr zod lucide-react
npm run dev
npm run lint
npx tsc --noEmit
npm run build
```

Add one test runner and `npm test` script when selected; add React Hook Form only in a later complex-form phase. `.env.example` uses placeholders `NEXT_PUBLIC_SUPABASE_URL=` and `NEXT_PUBLIC_SUPABASE_ANON_KEY=` (PUBLIC, protected by RLS), and `SUPABASE_SERVICE_ROLE_KEY=` (SERVER-ONLY, omit until a server task needs it). Never store real keys. Supabase project/migrations are Phase 02.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.

