<?php

namespace App\Support;

use App\Models\User;

class OnboardingTours
{
    public static function for(User $user): array
    {
        $role = match (true) {
            $user->hasRole('ojt_coordinator') => 'coordinator',
            $user->hasRole('supervisor') => 'supervisor',
            $user->hasRole('student') => 'student',
            default => null,
        };

        if ($role === null) {
            return [];
        }

        $tour = match ($role) {
            'coordinator' => self::coordinator(),
            'supervisor' => self::supervisor(),
            'student' => self::student(),
        };

        return [
            ['target' => 'main-navigation', 'title' => 'Your ' . $tour['role'] . ' workspace', 'text' => $tour['intro']],
            ...$tour['steps'],
            ['target' => 'dashboard-overview', 'title' => 'You’re ready to get started', 'text' => 'Begin with your dashboard, then open the section that matches what you need to do. Your role controls which tools are available.'],
        ];
    }

    private static function coordinator(): array
    {
        return [
            'role' => 'OJT coordinator',
            'intro' => 'Coordinate your students’ placements, requirements, and attendance, and keep in touch through messages.',
            'steps' => [
                self::step('nav-dashboard', 'Dashboard', 'See your student and placement counts, weekly attendance, placement mix, and recent students. Use this overview to decide what to follow up on.'),
                self::step('dashboard-metrics', 'Student and placement counts', 'These cards summarize students, pending placements, and assignments under your coordination.'),
                self::step('dashboard-workflow', 'Attendance and progress summaries', 'Review weekly attendance, placement mix, and the recent student list. Search the list to find a student.'),
                self::step('nav-messages', 'Messages', 'Use conversations to communicate with students connected to your coordinator account.'),
                self::step('nav-group-people', 'People', 'This group holds your students, supervisors, and the OJT assignments that connect them.'),
                self::step('nav-student-management', 'Student Management', 'Open the student records you coordinate. Use the available actions to maintain student details and review their status.'),
                self::step('nav-supervisor-management', 'Supervisor Management', 'Create and manage the supervisor accounts assigned to your coordinator account.'),
                self::step('nav-assignments', 'OJT Assignments', 'Create placement records by assigning your students to supervisors, then review assignment type.'),
                self::step('nav-group-monitoring', 'Monitoring', 'This group brings together student requirements, attendance, evaluations, and certificates.'),
                self::step('nav-requirements', 'Requirements', 'Review requirement completion and check submitted physical documents for students you coordinate.'),
                self::step('nav-attendance', 'Attendance', 'Monitor attendance logs for your students and use the available filters to narrow the records.'),
                self::step('nav-evaluations', 'Evaluations', 'Review practicum evaluations submitted by supervisors.'),
                self::step('nav-certificates', 'Certificates', 'See which students have completed their required OJT hours, then view their completion certificates.'),
                self::step('nav-group-system', 'System Management', 'This group contains tools for application announcements and accountability history.'),
                self::step('nav-announcements', 'Announcements', 'Create and manage system-wide announcements so everyone sees important updates.'),
                self::step('nav-audit-logs', 'Audit Logs', 'Search recorded system activity when you need an accountability or security history.'),
                self::step('topbar-announcements', 'Announcements feed', 'Open this feed to read published updates. Use System Management to manage them.'),
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
                self::step('nav-messages', 'Messages', 'Use conversations to communicate with students assigned to you.'),
                self::step('nav-group-monitoring', 'Monitoring', 'This group brings together trainee attendance and tasks.'),
                self::step('nav-attendance', 'Attendance', 'Open the calendar to review every assigned trainee’s attendance by day.'),
                self::step('nav-tasks', 'Tasks', 'Create tasks for assigned trainees and monitor their progress as they work.'),
                self::step('nav-group-reviews', 'Reviews', 'This group holds the accomplishment reports and evaluations you review.'),
                self::step('nav-reports', 'Accomplishment Reports', 'Review students’ daily reports and attached photos.'),
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
                self::step('nav-dashboard', 'Dashboard', 'Your dashboard shows your placement status, required documents, recent attendance, and evaluation. Use it to check your progress.'),
                self::step('dashboard-metrics', 'Your OJT at a glance', 'These cards show your rendered hours, approved requirements, open tasks, and placement status.'),
                self::step('dashboard-workflow', 'Requirements and recent attendance', 'Follow the requirement checklist, download templates, and mark physical copies submitted. The attendance table shows your latest records.'),
                self::step('dashboard-evaluation', 'Evaluation', 'Review your submitted practicum evaluation and download the official form.'),
                self::step('nav-messages', 'Messages', 'Use conversations to contact the coordinator or supervisor connected to your OJT.'),
                self::step('nav-group-daily-logs', 'Daily Logs', 'Open this group to record your attendance and daily accomplishments.'),
                self::step('nav-attendance', 'Attendance', 'Record your daily OJT attendance and review your attendance history.'),
                self::step('nav-reports', 'Accomplishment Report', 'Add your daily accomplishments and photos after completing attendance.'),
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
