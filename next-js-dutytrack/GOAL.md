# Goal and boundaries

Rebuild DutyTrack as an independent production-ready Next.js application while preserving validated Laravel behavior, data meaning, role boundaries, reports, and UI information architecture. The request for Next.js is a target decision, not evidence that Laravel has a technical defect.

| Parity area | Acceptance evidence |
|---|---|
| Functional | Every routed Laravel module and hidden mutation has an equivalent or an approved documented exception. |
| Database | Every domain table/column, relation, constraint, legacy ID and file reference is mapped and reconciled. |
| Permissions | Five roles and record-level ownership checks pass positive and negative tests with RLS. |
| Reports | Accomplishment and practicum evaluation `.doc` content, filtering, access and output behavior are compared. |
| UI | Five role workspaces, public landing, auth/settings, navigation, filters, feedback and responsive behavior are covered. |
| Security | Server checks, RLS, signed files, secrets, auth rate limits and audit evidence pass review. |
| Performance | Representative dashboard, list, chat and export loads have measured budgets before release. |
| Maintainability | Strict types, clear domain code, current docs, small phases, passing lint/type/test checks. |
| Deployment | Repeatable config, backups, monitoring, rollback and rehearsal are complete. |
| Final migration | Data counts/checksums and user acceptance pass before cutover. |

Not in scope: new product features, arbitrary visual redesign, speculative scheduling/notification systems, copying Laravel runtime tables merely because they exist, or implementation during this planning task. Any legacy bypass/weak password decision requires explicit review; it is not silently normalized into the target.
