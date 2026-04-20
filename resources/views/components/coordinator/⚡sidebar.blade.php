<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<flux:sidebar.nav>
    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
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

    <flux:sidebar.group expandable icon="chart-bar-square" heading="Monitoring">
        <flux:sidebar.item icon="clipboard-document-list" href="#">
            Attendance
        </flux:sidebar.item>
        <flux:sidebar.item icon="clock" href="#">
            Duty Hours
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group expandable icon="check-badge" heading="Reviews">
        <flux:sidebar.item icon="hand-thumb-up" href="#">
            Exaltations
        </flux:sidebar.item>
        <flux:sidebar.item icon="document-text" href="#">
            Reports
        </flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>
