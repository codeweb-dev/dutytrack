# Current UI and target component map

CONFIRMED: Livewire/Flux Blade + Tailwind. `resources/views/layouts/app/sidebar.blade.php` and five `components/<role>/⚡sidebar.blade.php` define the information architecture. `components/page-header.blade.php`, `stat-card.blade.php`, `pages/shared/reports-submissions-table.blade.php`, and role dashboards establish repeated patterns. Preserve labels/order and responsive behavior unless a phase records a redesign decision.

| Current UI | Target shadcn/Tailwind/Lucide direction |
|---|---|
| Five sidebar groups, role dashboards, account menu, mobile collapse | shadcn Sidebar or simple accessible nav, role-controlled links, Lucide icons; route-level server guards remain mandatory. |
| Page header/breadcrumbs, cards/stat metrics | Breadcrumb, Card and shared page header only where repeated. |
| Paginated sortable/filterable tables | Table, Input, Select, native date inputs, Pagination; preserve query scope and URL state. |
| Multi-step student creation and assignment forms | Form fields with Zod; React Hook Form only when multi-step/conditional complexity warrants it. |
| Create/edit/view/delete modals | Dialog/AlertDialog; Sheet only where existing UX genuinely uses a side panel. |
| Status badges and priority colors | Badge with text plus color: attendance present/late/incomplete/absent; duty pending/approved/rejected; assignment states; task states; announcement types. |
| Tabs/dropdowns/tooltips/toasts | shadcn Tabs, DropdownMenu, Tooltip, feedback pattern; preserve success/error copy intent. |
| Dashboard charts and summaries | Simple server-calculated cards and charts; choose chart dependency only after reviewing concrete needs. |
| Attendance map/geolocation | Small Client Component, permission/error states, Leaflet/OpenStreetMap equivalent, keyboard-accessible text coordinates. |
| Kanban drag/reorder | Focused client widget with keyboard fallback and server validation. |
| Chat message list/typing/read state | Client live region, accessible composer, private Realtime subscription with refresh fallback. |
| Empty/loading/error states | Respect existing empty copy; route `loading.tsx`, `error.tsx`, shadcn Skeleton where helpful. |
| Typography/spacing/theme | Inspect `resources/css/app.css`, Flux variants and Blade classes during Phase 05; preserve current dark-mode appearance, hierarchy, red accents, compact tables and responsive breakpoints. |
| Destructive actions | Confirm via AlertDialog, show outcome, keep focus management. |

Do not treat presentational placeholders (`#` coordinator approvals/reports) as routed features. Pomodoro and pet streak are browser-local widgets, not database modules. Avoid copying the large `resources/views/flux/` vendor component tree into the target.
