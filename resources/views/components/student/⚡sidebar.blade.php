<?php

use Livewire\Component;

new class extends Component {};
?>

<flux:sidebar.nav data-tour="main-navigation">
    <flux:sidebar.item icon="home" data-tour="nav-dashboard" :href="route('student.dashboard')" :current="request()->routeIs('student.dashboard')"
        wire:navigate>
        Dashboard
    </flux:sidebar.item>

    <flux:sidebar.item icon="clipboard-document-check" data-tour="nav-requirements" :href="route('student.requirements')" :current="request()->routeIs('student.requirements*')"
        wire:navigate>
        Requirements
    </flux:sidebar.item>

    <flux:sidebar.item icon="chat-bubble-left-right" data-tour="nav-messages" :href="route('student.messages')" :current="request()->routeIs('student.messages')"
        wire:navigate>
        Messages
    </flux:sidebar.item>

    <flux:sidebar.group expandable icon="calendar-days" heading="Daily Logs" data-tour="nav-group-daily-logs">
        <flux:sidebar.item icon="clipboard-document-list" data-tour="nav-attendance" :href="route('student.attendance')" :current="request()->routeIs('student.attendance')"
            wire:navigate>
            Attendance
        </flux:sidebar.item>

        <flux:sidebar.item icon="document-text" data-tour="nav-reports" href="{{ route('student.reports-submissions') }}" :current="request()->routeIs('student.reports-submissions')"
            wire:navigate>
            Accomplishment Report
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group expandable icon="squares-2x2" heading="Productivity" data-tour="nav-group-productivity">
        <flux:sidebar.item icon="rectangle-stack" data-tour="nav-tasks" href="{{ route('student.tasks') }}" :current="request()->routeIs('student.tasks', 'student.kanban')"
            wire:navigate>
            Tasks
        </flux:sidebar.item>
        <flux:sidebar.item icon="pencil-square" data-tour="nav-notes" href="{{ route('student.notes') }}" :current="request()->routeIs('student.notes')"
            wire:navigate>
            Notes
        </flux:sidebar.item>
        <flux:sidebar.item icon="clock" data-tour="nav-pomodoro" href="{{ route('student.pomodoro') }}" :current="request()->routeIs('student.pomodoro')"
            wire:navigate>
            Pomodoro Timer
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group expandable icon="folder" heading="Documents" data-tour="nav-group-documents">
        <flux:sidebar.item icon="identification" data-tour="nav-resume-builder" href="{{ route('student.resumes') }}" :current="request()->routeIs('student.resumes*')"
            wire:navigate>
            Resume Builder
        </flux:sidebar.item>

        <flux:sidebar.item icon="star" data-tour="nav-evaluations" href="{{ route('student.evaluations') }}" :current="request()->routeIs('student.evaluations')"
            wire:navigate>
            My Evaluation
        </flux:sidebar.item>

        <flux:sidebar.item icon="academic-cap" data-tour="nav-certificate" href="{{ route('student.certificate') }}" :current="request()->routeIs('student.certificate')"
            wire:navigate>
            Certificate
        </flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>
