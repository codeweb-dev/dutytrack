<?php

use Livewire\Component;

new class extends Component {};
?>

<flux:sidebar.nav data-tour="main-navigation">
    <flux:sidebar.item icon="home" data-tour="nav-dashboard" :href="route('coordinator.dashboard')" :current="request()->routeIs('coordinator.dashboard')"
        wire:navigate>
        Dashboard
    </flux:sidebar.item>

    <flux:sidebar.item icon="academic-cap" data-tour="nav-student-management" :href="route('coordinator.student-management')" :current="request()->routeIs('coordinator.student-management')"
        wire:navigate>
        Student Management
    </flux:sidebar.item>

    <flux:sidebar.item icon="building-office-2" data-tour="nav-supervisor-management" :href="route('coordinator.supervisor-management')" :current="request()->routeIs('coordinator.supervisor-management')"
        wire:navigate>
        Supervisor Management
    </flux:sidebar.item>

    <flux:sidebar.item icon="briefcase" data-tour="nav-assignments" :href="route('coordinator.ojt-assignments')" :current="request()->routeIs('coordinator.ojt-assignments')"
        wire:navigate>
        OJT Assignments
    </flux:sidebar.item>

    <flux:sidebar.item icon="chat-bubble-left-right" data-tour="nav-messages" :href="route('coordinator.messages')" :current="request()->routeIs('coordinator.messages')"
        wire:navigate>
        Messages
    </flux:sidebar.item>

    <flux:sidebar.group expandable icon="chart-bar-square" heading="Monitoring" data-tour="nav-group-monitoring">
        <flux:sidebar.item icon="clipboard-document-check" data-tour="nav-requirements" href="{{ route('coordinator.requirements') }}"
            :current="request()->routeIs('coordinator.requirements')" wire:navigate>
            Requirements
        </flux:sidebar.item>

        <flux:sidebar.item icon="clipboard-document-list" data-tour="nav-attendance" href="{{ route('coordinator.attendance') }}"
            :current="request()->routeIs('coordinator.attendance')" wire:navigate>
            Attendance
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group expandable icon="shield-check" heading="System Management" data-tour="nav-group-system">
        <flux:sidebar.item icon="megaphone" data-tour="nav-announcements" href="{{ route('coordinator.announcements') }}"
            :current="request()->routeIs('coordinator.announcements')" wire:navigate>
            Announcements
        </flux:sidebar.item>

        <flux:sidebar.item icon="document-text" data-tour="nav-audit-logs" href="{{ route('coordinator.audit-logs') }}"
            :current="request()->routeIs('coordinator.audit-logs')" wire:navigate>
            Audit Logs
        </flux:sidebar.item>

        <flux:sidebar.item icon="archive-box" href="{{ route('coordinator.archive') }}"
            :current="request()->routeIs('coordinator.archive')" wire:navigate>
            Archive
        </flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>
