# Bug Report: DutyTrack

Audited 2026-11-12. Covers every PHP file in `app/`, `routes/` and every Livewire page and component. Blade markup was checked for unescaped output. Bugs were found by tracing the code; #22 was confirmed by running the path normalizer. The test suite was also run.

| # | Severity | Area | Summary |
|---|---|---|---|
| 22 | **Critical** | Security | A student can delete other students' uploaded files |
| 1 | High | Archive | Archiving a supervisor leaves their students attached to a dead account |
| 2 | Low | Archive | "Assigned" counts include students whose supervisor is archived |
| 3 | High | Tasks | Tasks are orphaned after reassignment |
| 4 | Medium | Tasks | Attendance follows reassignment, tasks don't |
| 5 | Medium | Messages | Old supervisor keeps the chat after reassignment |
| 6 | Low | Students | Duplicate students when sorting by supervisor |
| 7 | Medium | Archive | Archived students still count for their supervisor |
| 8 | High | Tasks | Students can approve or un-approve their own tasks |
| 9 | Medium | Evaluations | Evaluation page crashes for an archived student |
| 10 | Medium | Audit logs | Every coordinator sees all audit logs |
| 11 | Low | Archive | Any coordinator can restore any archived coordinator |
| 12 | Low | Archive | Restoring an assignment can bring back a dead supervisor link |
| 13 | High | Attendance | One failed holiday-API call blocks time-in for everyone for 24 hours |
| 14 | High | Assignments | Editing an assignment can swap its student, which breaks chat |
| 15 | Medium | Assignments | Changing a supervisor's type breaks their existing assignments |
| 16 | Medium | Assignments | Inactive supervisors and pending students can still be assigned |
| 17 | Medium | Reports | Students can't submit reports for afternoon-only days |
| 18 | Medium | Security | Coordinator-created accounts get weak, guessable passwords |
| 19 | Medium | Announcements | Announcements go to every user and have no owner |
| 20 | Low | Messages | Polling marks messages read before checking access |
| 21 | Low | Photos | Old photo is deleted before the database save commits |
| 23 | Low | Security | Changing your password doesn't sign out your other sessions |

The bugs in the table are fixes. There is also one problem with the test suite: 25 tests are outdated. See the last section.

## Fix status (2026-11-13)
All 23 have been fixed, except **#19**, which is only partly fixed. Each fix has a test that fails on the old code.

- **#19 (partly fixed):** announcements now notify active accounts only. Two things are left for you to decide:
  - **Per-coordinator announcements:** these need a new `coordinator_id` column, plus a decision on whether announcements are meant to be school-wide.
  - **Queued notifications:** queuing would silently stop *all* notifications unless a `queue:work` process is running.
- **#3:** unfinished tasks now **move to the new supervisor**. On edit-reassignment the whole assignment moves. When an assignment is archived and a new one created, its unfinished tasks move to the new one, and finished tasks stay with the old one.
- **#7:** archiving a student also archives their assignment. Restoring the student does not bring the assignment back; restore it from Archive > Assignments.
- **#18:** the last-name password default is gone. Coordinator-set passwords now follow `Password::default()`: 8 or more characters locally, and 12 or more with mixed case, numbers and symbols in production.
