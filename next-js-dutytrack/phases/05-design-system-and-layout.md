# Phase 05 — Design system and layout

## Goal

Build accessible shared shell for five role workspaces.

## Why This Phase Exists

This phase is required before dependent work because it will build accessible shared shell for five role workspaces. It starts after 04 authorization.

## Prerequisites

04 authorization.

## Existing Laravel Reference

layouts/*, five role sidebars, page-header, stat-card, resources/css/app.css.

## Scope

Sidebars, breadcrumbs, theme, shared cards/tables/forms/feedback and responsive layout.

## Out of Scope

No domain data pages or product redesign.

## Tasks

- [ ] Record nav order/labels and status colors.
- [ ] Build role shell with shadcn/Tailwind/Lucide.
- [ ] Add keyboard/mobile/dark mode behavior.
- [ ] Add repeated header/table/status/empty/loading patterns.
- [ ] Verify no placeholder routes appear as links.

## Expected Files

app/(workspace)/layout.tsx, components/sidebar.tsx, components/page-header.tsx, components/ui.

## Database Changes

None.

## Roles / Permissions

Five roles.

## Business Rules

Preserve information architecture and labels.

## Edge Cases

Coordinator sidebar has # placeholders; admin partners is unrouted.

## Security Checks

Hidden links do not replace server authorization.

## Testing

Unit nav mapping; integration layout; permission link scope; DB none; UI keyboard/mobile/dark; regression auth.

## Verification Checklist

- [ ] Inspect every role at mobile/desktop widths and tab through menus.

## Definition of Done

Shell accurate, accessible and no dead invented route.

## AI Implementation Prompt

Read ../AGENTS.md first and follow it strictly. Read this entire phase document. Study the referenced Laravel implementation before changing anything. Implement ONLY this phase, one feature at a time. Do not implement future phases. Preserve current behavior unless a documented decision explicitly changes it. Run required new and regression tests. Report: what was inspected, what changed, files changed, tests run, issues found, remaining work, and next recommended phase.
