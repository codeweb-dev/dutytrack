# DutyTrack full system redesign prompt

## Brief for the designer or coding agent

Redesign **the entire DutyTrack web application** into a colorful, modern OJT management system with a **matte palette**. The result should feel warm, organized, and credible for a college practicum program. Use the four supplied images as visual inspiration, then create one original, consistent DutyTrack identity. This is a **full UI and UX redesign**, including the public site, authentication, all five role workspaces, shared pages, settings, and responsive states. Implement the design in the existing application; do not stop after creating a mockup or one dashboard.

The attached images are visual references only. Any words, numbers, menu items, or product concepts inside them are sample content, **not requirements for DutyTrack**.

### Reference direction

1. `/Users/user/Downloads/290bc2994cea6373a32119897c0df631.jpg` — use its compact kanban cards, pastel metric blocks, and tidy sidebar as inspiration for task screens.
2. `/Users/user/Downloads/081a1fa963bacf3885d26a4707a85b3b.jpg` — use its warm ivory canvas, near black navigation, varied matte dashboard panels, and strong composition as the main visual direction.
3. `/Users/user/Downloads/171e9b2046f31a2f037786ab689d05d5.jpg` — use its airy note cards, calm whitespace, and soft color coding for notes and personal productivity.
4. `/Users/user/Downloads/b813e9151471ec3b17ec26f651c5a73e.jpg` — use its clear data hierarchy, focused chart panels, and grounded green accent for monitoring and reports.

Do not copy the reference brands, exact layouts, fake metrics, or unrelated features. DutyTrack should look like one product across every screen.

## Existing product and technical context

- DutyTrack is a Laravel 12, Livewire 4, Blade, Flux UI, Tailwind CSS 4, and Vite application.
- Its core purpose is to track OJT placement, student requirements, attendance, duty hours, tasks, reports, evaluations, and communication.
- The five roles are **Admin, OJT Coordinator, Professor, Company Supervisor, and Student**. Each has different routes and permissions; preserve those boundaries.
- Existing route definitions are in `routes/web.php` and `routes/settings.php`. Shared shell and role menus live in `resources/views/layouts/app/sidebar.blade.php` and `resources/views/components/{role}/⚡sidebar.blade.php`. Page views live in `resources/views/pages/`. Global styling starts in `resources/css/app.css`.
- Existing data, calculations, forms, Livewire actions, validation, pagination, uploads, authorization, and document downloads must keep working. This task concerns presentation and interaction quality. Introduce new backend behavior only when needed to make an existing flow usable.

## Visual system

### Palette

Build a cohesive set of CSS or Tailwind theme tokens and use them everywhere. Suggested starting colors; adjust values only to improve contrast and consistency:

| Token | Suggested color | Use |
| --- | --- | --- |
| Canvas | `#F6F2E9` | Main warm ivory background |
| Surface | `#FFFCF6` | Cards, forms, dialogs |
| Ink | `#202824` | Primary text and dark sidebar |
| Muted ink | `#5F6B63` | Secondary text |
| Border | `#DDDCD0` | Fine dividers and inputs |
| Forest | `#235444` | Main actions and strong data emphasis |
| Sage | `#B9CDB5` | Positive and progress panels |
| Butter | `#F0DB85` | Attention and pending panels |
| Blush | `#EDB7B0` | Warm highlights |
| Lavender | `#D3C1E5` | Task and productivity highlights |
| Powder blue | `#B9D0E8` | Attendance and neutral data highlights |

Use **flat, low sheen colors**: no neon, glossy gradients, glass panels, or heavy shadows. Let color identify content groups, but never rely on color alone to communicate a status. Pair statuses with text and, where helpful, an icon. Keep large reading and data surfaces light; reserve saturated forest or dark ink for navigation, primary actions, and selected data panels. Maintain a genuine dark theme with equally considered colors; the current layout forces `class="dark"`, so make the appearance preference work correctly rather than leaving light mode as a token swap.

### Form and layout language

- Use a stable sidebar plus a clean top utility area on desktop. On mobile, provide a compact header and an accessible navigation drawer. Highlight the active route clearly.
- Give pages a recognizable hierarchy: short page title, one sentence of context where useful, primary action, then summary and working content. Avoid huge empty hero areas inside the app.
- Prefer an 8 px spacing rhythm, generous but controlled whitespace, rounded cards around 16–20 px, and small consistent gaps between related controls. Use subtle borders and restrained shadows.
- Use readable typography: Poppins is already configured, but keep body text and table text comfortably legible. Establish clear sizes for page titles, section headings, metrics, labels, and help text.
- Standardize buttons, inputs, search, tabs, filters, date controls, badges, tables, modals, toast messages, progress bars, charts, empty states, and loading states through existing Flux/Blade patterns where practical.
- Use simple icons consistently. Charts need direct labels, units, dates, meaningful legends, and real data. Do not draw decorative charts with invented values.
- Use microinteractions sparingly: hover, focus, pressed, expanded, loading, and success feedback should be noticeable and quick. Respect reduced motion settings.

## Screen coverage and content hierarchy

### Shared and public screens

- **Landing page:** redesign navigation, hero, dashboard preview, benefits, comparison, FAQ, team, and footer as one coherent introduction to DutyTrack. Explain the OJT workflow with real product language. Keep the main sign in or get started path obvious.
- **Authentication:** redesign sign in, registration, forgot and reset password, email verification, password confirmation, and two factor challenge. Use one calm form pattern with clear validation and recovery guidance.
- **Shared app shell:** redesign sidebar, top bar, search or contextual utilities where they already exist, profile menu, announcements entry, theme control, breadcrumbs, notifications or toast presentation, and mobile navigation.
- **Announcements, messaging, and settings:** present announcement lists and detail states clearly; make conversation selection, message bubbles, typing or sending states, and empty states readable; align profile, password, appearance, and two factor settings with the rest of the system.

### Student workspace

- **Dashboard:** lead with required, approved, and remaining OJT hours; placement status; weekly logged hours; requirements progress; and recent attendance. Make the next useful action obvious.
- **Attendance:** emphasize the current day and the four time events (morning in/out, afternoon in/out). Make location capture and map feedback understandable, including permission or accuracy errors. Keep history and statuses easy to scan.
- **Duty hours:** show rendered versus approved hours, pending and rejected logs, useful filters, and a clear path from a log to its status.
- **Requirements and placement:** the student dashboard already shows requirement steps and placement details. Make submitted, awaiting verification, completed, and missing states distinguishable without altering their meaning.
- **Reports and submissions:** clarify daily accomplishment entry, photo upload and preview, previous submissions, and document download.
- **Evaluations:** show available results, status, and download actions plainly.
- **Tasks:** modernize the existing kanban board with matte colored columns or cards, priority and due date chips, assignee details, clear drag handle, and responsive horizontal scrolling. Preserve existing sorting and status behavior.
- **Notes:** make the existing searchable list and editor feel like an inviting personal workspace. Use color for selected or pinned notes without inventing folders unless implemented and requested.
- **Pomodoro and pet streak:** keep these student productivity screens playful but visually part of DutyTrack. Make timer controls, progress, streak, and rewards accessible and readable.

### OJT Coordinator workspace

- **Dashboard:** prioritize managed students, placement progress, pending work, weekly activity, and recent records.
- **Student management:** make search, filters, student details, forms, and actions clear and efficient.
- **OJT assignments:** distinguish internal and external placement, current status, assigned professor and supervisor, and create or edit actions.
- **Requirements:** show each student’s submitted, pending, and completed requirements with an obvious review path.
- **Attendance and duty hours:** present the records, filters, status, and review context in a consistent monitoring pattern.
- **Messages:** use the shared conversation design.

### Professor and Company Supervisor workspaces

- Give each role its own dashboard content while sharing the same visual language and reusable components.
- Redesign assigned student lists and detail panels, duty hour review, task assignment and tracking, report submissions, evaluations, and messages.
- Put student identity, placement, dates, status, and the next review action close together. Distinguish pending from completed work clearly.
- Preserve role specific permissions and language; do not imply that a professor can perform a supervisor only action, or vice versa.

### Admin workspace

- **Dashboard:** present systemwide student, assignment, review, and approved hour metrics with labeled trends and honest time comparisons.
- **User management:** modernize student, coordinator, professor, and supervisor directories, search and filters, forms, and row actions.
- **Monitoring:** redesign attendance, duty hours, OJT assignments, and evaluations with dense but readable tables, useful summary panels, clear filters, and review states.
- **System management:** make announcement creation and management straightforward; make audit logs easy to scan by actor, action, and time.
- An admin partners view exists in the repository but has no route in `routes/web.php`; inspect it before deciding whether it belongs in the implemented redesign. Do not expose an unimplemented navigation item.

## Interaction and responsive requirements

- Work at desktop, tablet, and phone widths. Target at least 1440, 1024, 768, and 390 px during review. Tables can scroll or transform into stacked records on small screens, but controls and key values must remain reachable.
- Keep all forms fully labeled, with visible required states, inline validation, disabled or busy states, and confirmation for destructive actions. Do not hide important actions in hover only controls.
- Meet WCAG AA contrast for text and controls; maintain visible keyboard focus, logical tab order, semantic headings, accessible dialogs, and touch targets around 44 px where practical.
- Design empty, loading, error, and success states for every key data surface. Use real data and existing Livewire methods. Never populate the live app with placeholder metrics or dummy records.
- Make status language and colors consistent across attendance, requirements, duty hours, assignments, tasks, submissions, and evaluations. Respect the actual statuses defined in the models and page logic.
- Keep timezone aware dates and time labels clear for users in the Philippines; avoid ambiguous date formats in important review flows.

## Implementation instructions

1. Audit the actual routes, shared components, views, and Livewire interactions before editing. Write a compact route and screen checklist so no role page is missed.
2. Create the theme tokens and shared shell first. Reuse the existing Flux and Blade components and current dependencies; add a library only for a demonstrated need.
3. Update shared patterns once, then apply them systematically to every role and page listed above. Preserve working server logic, permissions, form field names, and route names.
4. Do not settle for cosmetic recoloring. Improve the grouping, scan order, responsive behavior, labels, and action placement on each screen.
5. Review representative pages for all five roles in light and dark themes and at desktop and phone widths. Run the existing build and relevant tests, and fix visual or functional regressions.
6. Deliver the implemented redesign with a concise list of changed screens, any deliberate limitations, and screenshots of representative views.

## Definition of done

The redesign is complete when every reachable public, authentication, shared, settings, and role specific DutyTrack screen uses the same matte design language; the four references are recognizable as inspiration without being copied; all existing workflows still function; light and dark themes both work; responsive and keyboard use are practical; and no page is left with the old zinc and red default look.
