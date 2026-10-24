<?php

namespace App\Support;

use App\Models\User;

class OnboardingTours
{
    public static function for(User $user): array
    {
        $role = match (true) {
            $user->hasRole('admin') => 'admin',
            $user->hasRole('ojt_coordinator') => 'coordinator',
            $user->hasRole('supervisor') => 'supervisor',
            $user->hasRole('student') => 'student',
            default => null,
        };

        if ($role === null) {
            return [];
        }

        $tour = match ($role) {
            'admin' => self::admin(),
            'coordinator' => self::coordinator(),
            'supervisor' => self::supervisor(),
            'student' => self::student(),
        };

        return [
            ['target' => 'main-navigation', 'title' => 'Your '.$tour['role'].' workspace', 'text' => $tour['intro']],
            ...$tour['steps'],
            ['target' => 'profile-menu', 'title' => 'Replay this tour any time', 'text' => 'Open your profile menu and choose Take a Tour to revisit this walkthrough.'],
            ['target' => 'dashboard-overview', 'title' => 'You’re ready to get started', 'text' => 'Begin with your dashboard, then open the section that matches what you need to do. Your role controls which tools are available.'],
        ];
    }

    private static function admin(): array
    {
        return [
            'role' => 'admin',
            'intro' => 'Manage DutyTrack accounts, monitor OJT activity, review evaluations, and maintain system records.',
            'steps' => [
                self::step('nav-dashboard', 'Dashboard', 'Your overview brings together student and assignment totals, pending reviews, weekly activity, and recent assignments. Start here to see where attention is needed.'),
                self::step('dashboard-metrics', 'Dashboard statistics', 'The summary cards show student, assignment, and pending review counts. Use them to spot changes at a glance.'),
                self::step('dashboard-workflow', 'Activity and assignment summaries', 'The charts compare weekly attendance activity and assignment types. Recent Assignments lets you search and filter current records.'),
                self::step('nav-group-user-management', 'User Management', 'This group contains the account directories. Open it when you need to review or maintain a user record.'),
                self::step('nav-students', 'Students', 'Find student accounts and review or manage their records using the actions available on the Students page.'),
                self::step('nav-coordinators', 'OJT Coordinators', 'Manage coordinator accounts and the people responsible for coordinating student placements.'),
                self::step('nav-supervisors', 'Supervisors', 'Manage supervisor accounts for the people who oversee students during OJT.'),
                self::step('nav-group-monitoring', 'Monitoring', 'Use these pages to review attendance, placements, and evaluation activity across DutyTrack.'),
                self::step('nav-attendance', 'Attendance', 'Open attendance records to monitor student participation and review recorded activity.'),
                self::step('nav-assignments', 'OJT Assignments', 'Review placement records, assigned supervisors, OJT type, and assignment status. Use filters to find records.'),
                self::step('nav-evaluations', 'Evaluation', 'Review evaluation records and follow up on evaluations awaiting attention.'),
                self::step('nav-group-system', 'System Management', 'This group contains tools for application announcements and accountability history.'),
                self::step('nav-admin-announcements', 'System Announcements', 'Create and manage system-wide announcements so users can see important updates.'),
                self::step('nav-audit-logs', 'Audit Logs', 'Search recorded system activity when you need an accountability or security history.'),
                self::step('topbar-announcements', 'Announcements feed', 'Open this feed to read published updates. Use System Announcements to manage them.'),
                self::step('account-settings', 'Settings', 'Update your own profile and account details here.'),
            ],
        ];
    }

    private static function coordinator(): array
    {
        return [
            'role' => 'OJT coordinator',
            'intro' => 'Coordinate your students’ placements, requirements, and attendance, and keep in touch through messages.',
            'steps' => [
                self::step('nav-dashboard', 'Dashboard', 'See your student and placement counts, weekly attendance, assignment status, and recent students. Use this overview to decide what to follow up on.'),
                self::step('dashboard-metrics', 'Student and placement counts', 'These cards summarize students, pending placements, and active assignments under your coordination.'),
                self::step('dashboard-workflow', 'Attendance and progress summaries', 'Review weekly attendance, assignment status, placement mix, and the recent student list. Search the list to find a student.'),
                self::step('nav-student-management', 'Student Management', 'Open the student records you coordinate. Use the available actions to maintain student details and review their status.'),
                self::step('nav-supervisor-management', 'Supervisor Management', 'Create and manage the supervisor accounts assigned to your coordinator account.'),
                self::step('nav-assignments', 'OJT Assignments', 'Create placement records by assigning your students to supervisors, then review assignment type and status.'),
                self::step('nav-messages', 'Messages', 'Use conversations to communicate with students connected to your coordinator account.'),
                self::step('nav-group-monitoring', 'Monitoring', 'This group brings together student requirements and attendance follow-up.'),
                self::step('nav-requirements', 'Requirements', 'Review requirement completion and check submitted physical documents for students you coordinate.'),
                self::step('nav-attendance', 'Attendance', 'Monitor attendance logs for your students and use the available filters to narrow the records.'),
                self::step('topbar-announcements', 'Announcements', 'Read published DutyTrack updates here.'),
                self::step('account-settings', 'Settings', 'Update your own profile and account details here.'),
            ],
        ];
    }

    private static function supervisor(): array
    {
        return [
            'role' => 'supervisor',
            'intro' => 'Follow the trainees assigned to you, guide their work, and review their submitted activities and evaluations.',
            'steps' => [
                self::step('nav-dashboard', 'Dashboard', 'Your overview summarizes assigned trainees, placement status, weekly attendance, and evaluation activity.'),
                self::step('dashboard-metrics', 'Placement summary', 'These cards show the trainees and pending placements assigned to you.'),
                self::step('dashboard-workflow', 'Attendance and recent trainees', 'Review weekly attendance and placement status, then search the recent trainee list to find someone.'),
                self::step('nav-assigned-students', 'Assigned Students', 'View the students assigned to your supervision and check their placement status.'),
                self::step('nav-attendance', 'Attendance', 'Open the calendar to review every assigned trainee’s attendance by day.'),
                self::step('nav-tasks', 'Tasks', 'Create tasks for assigned trainees and monitor their progress as they work.'),
                self::step('nav-messages', 'Messages', 'Use conversations to communicate with students assigned to you.'),
                self::step('nav-reports', 'Reports / Submissions', 'Read completed trainee accomplishment reports and review their attached photos.'),
                self::step('nav-evaluations', 'Evaluations', 'Evaluate assigned trainees and review submitted practicum evaluations.'),
                self::step('topbar-announcements', 'Announcements', 'Read published DutyTrack updates here.'),
                self::step('account-settings', 'Settings', 'Update your own profile and account details here.'),
            ],
        ];
    }

    private static function student(): array
    {
        return [
            'role' => 'student',
            'intro' => 'Track your OJT activity, complete requirements, share daily accomplishments, and keep up with your work.',
            'steps' => [
                self::step('nav-dashboard', 'Dashboard', 'Your dashboard shows your placement status, required documents, and recent attendance. Use it to check your progress.'),
                self::step('dashboard-metrics', 'Placement status', 'This card shows your current assignment status and OJT type, or tells you if you are waiting for a placement.'),
                self::step('dashboard-workflow', 'Requirements and recent attendance', 'Follow the requirement checklist, download templates, and mark physical copies submitted. The attendance table shows your latest records.'),
                self::step('nav-attendance', 'Attendance', 'Record your daily OJT attendance and review your attendance history.'),
                self::step('nav-evaluations', 'Evaluations', 'View submitted practicum evaluations and download the official form when it is available.'),
                self::step('nav-messages', 'Messages', 'Use conversations to contact the coordinator or supervisor connected to your OJT.'),
                self::step('nav-reports', 'Reports / Submissions', 'Add your daily accomplishments and photos after completing attendance.'),
                self::step('nav-group-productivity', 'Productivity', 'Open this group for tools that help you organize tasks, notes, and focused work.'),
                self::step('nav-tasks', 'Tasks', 'Review tasks assigned by your supervisor and update their progress as you work.'),
                self::step('nav-notes', 'Notes', 'Keep private OJT notes, reminders, and drafts in one place.'),
                self::step('nav-pomodoro', 'Pomodoro Timer', 'Plan focused work sessions and short breaks while working on OJT tasks or documentation.'),
                self::step('topbar-announcements', 'Announcements', 'Read published DutyTrack updates here.'),
                self::step('account-settings', 'Settings', 'Update your own profile and account details here.'),
            ],
        ];
    }

    private static function step(string $target, string $title, string $text): array
    {
        return compact('target', 'title', 'text');
    }
}
