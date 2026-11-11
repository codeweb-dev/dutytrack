<?php

use Livewire\Component;

new class extends Component {};
?>

<flux:sidebar.nav data-tour="main-navigation">
    <flux:sidebar.item icon="home" data-tour="nav-dashboard" :href="route('supervisor.dashboard')" :current="request()->routeIs('supervisor.dashboard')"
        wire:navigate>
        Dashboard
    </flux:sidebar.item>

    <flux:sidebar.item icon="academic-cap" data-tour="nav-assigned-students" href="{{ route('supervisor.assigned-students') }}"
        :current="request()->routeIs('supervisor.assigned-students')" wire:navigate>
        Assigned Students
    </flux:sidebar.item>

    <flux:sidebar.item icon="chat-bubble-left-right" data-tour="nav-messages" href="{{ route('supervisor.messages') }}"
        :current="request()->routeIs('supervisor.messages')" wire:navigate>
        Messages
    </flux:sidebar.item>

    <flux:sidebar.group expandable icon="chart-bar-square" heading="Monitoring" data-tour="nav-group-monitoring">
        <flux:sidebar.item icon="calendar-days" data-tour="nav-attendance" href="{{ route('supervisor.attendance') }}"
            :current="request()->routeIs('supervisor.attendance')" wire:navigate>
            Attendance
        </flux:sidebar.item>

        <flux:sidebar.item icon="rectangle-stack" data-tour="nav-tasks" href="{{ route('supervisor.tasks') }}"
            :current="request()->routeIs('supervisor.tasks')" wire:navigate>
            Tasks
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group expandable icon="document-magnifying-glass" heading="Reviews" data-tour="nav-group-reviews">
        <flux:sidebar.item icon="document-text" data-tour="nav-reports" href="{{ route('supervisor.reports-submissions') }}"
            :current="request()->routeIs('supervisor.reports-submissions')" wire:navigate>
            Accomplishment Reports
        </flux:sidebar.item>

        <flux:sidebar.item icon="check-badge" data-tour="nav-evaluations" href="{{ route('supervisor.evaluations') }}"
            :current="request()->routeIs('supervisor.evaluations')" wire:navigate>
            Evaluations
        </flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>
