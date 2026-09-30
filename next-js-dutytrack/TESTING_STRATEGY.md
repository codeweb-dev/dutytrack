# Testing and parity strategy

Existing Pest suite in `tests/Feature/` covers Fortify auth/settings, coordinator student/assignment ownership, student attendance calculations and reports, evaluation download, messages, role dashboards and notes. `tests/Unit/HourFormatterTest.php` covers formatting. Source tests become parity examples, not a claim of full coverage.

1. Phase 01 chooses one TypeScript unit/integration runner and one browser E2E tool, adds lint and `tsc --noEmit` checks. Avoid per-function mirror tests.
2. Build a small table of Laravel fixture → expected Next.js result for each migrated feature. Run focused new tests and previously migrated flow smoke tests after each feature.
3. Test business boundaries: assignment type exclusivity/unique student; coordinator ownership; attendance punch order, 7:00/17:00/overtime, duplicate day, location rejection, final-punch duty upsert; approval/rejection and repeat action; rubric 20 scores, grade thresholds; task group reorder; own-message edit/delete and recipient read; completed-attendance-only submission and image limits.
4. SQL/RLS tests impersonate five roles and anonymous users across every domain table, including same-role unrelated records. Confirm Storage policies and Realtime authorization.
5. Browser E2E covers each role's navigation, form feedback, keyboard behavior, responsive shell, upload, report download, and chat reconnect/poll fallback. Snapshot only stable export/document and selected UI output where a regression would matter.
6. Data rehearsal compares source/target counts, FK integrity, status distributions, numerical sums, sampled row hashes, file hashes, auth mapping, and generated report content. Record deviations.
7. Release gate: lint/type/tests pass, representative performance load passes, security checklist passes, user acceptance and rollback rehearsal are documented.

Current gaps: no production schema/data audit, no Supabase RLS tests, no confirmed target host/test runner, no comprehensive task/announcement/export/browser parity suite. These are phase tasks, not assumed complete.
