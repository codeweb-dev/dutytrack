<?php

use Livewire\Component;

new class extends Component {};
?>

<flux:sidebar.nav>
    <flux:sidebar.item icon="home" :href="route('coordinator.dashboard')" :current="request()->routeIs('coordinator.dashboard')"
        wire:navigate>
        Dashboard
    </flux:sidebar.item>

    <flux:sidebar.item icon="academic-cap" :href="route('coordinator.student-management')" :current="request()->routeIs('coordinator.student-management')"
        wire:navigate>
        Student Management
    </flux:sidebar.item>

    <flux:sidebar.item icon="briefcase" :href="route('coordinator.ojt-assignments')" :current="request()->routeIs('coordinator.ojt-assignments')"
        wire:navigate>
        OJT Assignments
    </flux:sidebar.item>

    <flux:sidebar.item icon="chat-bubble-left-right" :href="route('coordinator.messages')" :current="request()->routeIs('coordinator.messages')"
        wire:navigate>
        Messages
    </flux:sidebar.item>

    <flux:sidebar.group expandable icon="chart-bar-square" heading="Monitoring">
        <flux:sidebar.item icon="clipboard-document-check" href="{{ route('coordinator.requirements') }}"
            :current="request()->routeIs('coordinator.requirements')" wire:navigate>
            Requirements
        </flux:sidebar.item>

        <flux:sidebar.item icon="clipboard-document-list" href="{{ route('coordinator.attendance') }}"
            :current="request()->routeIs('coordinator.attendance')" wire:navigate>
            Attendance
        </flux:sidebar.item>
        <flux:sidebar.item icon="clock" href="{{ route('coordinator.duty-hours') }}"
            :current="request()->routeIs('coordinator.duty-hours')" wire:navigate>
            Duty Hours
        </flux:sidebar.item>
    </flux:sidebar.group>

    {{-- <flux:sidebar.group expandable icon="check-badge" heading="Reviews">
        <flux:sidebar.item icon="hand-thumb-up" :badge="$this->managedEvaluationsCount" href="#">
            Exaltations
        </flux:sidebar.item>
        <flux:sidebar.item icon="document-text" href="#">
            Reports
        </flux:sidebar.item>
    </flux:sidebar.group> --}}
</flux:sidebar.nav>
