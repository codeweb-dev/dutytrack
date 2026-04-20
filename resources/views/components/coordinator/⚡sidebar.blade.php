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

    <flux:sidebar.item icon="clipboard-document-check" :href="route('coordinator.student-management')" :current="request()->routeIs('coordinator.student-management')"
        wire:navigate>
        Student Management
    </flux:sidebar.item>

    <flux:sidebar.group expandable icon="computer-desktop" heading="Monitoring">
        <flux:sidebar.item href="#">
            Attendance
        </flux:sidebar.item>
        <flux:sidebar.item href="#">
            Duty Hours
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group expandable icon="sparkles" heading="Reviews">
        <flux:sidebar.item href="#">
            Exaltations
        </flux:sidebar.item>
        <flux:sidebar.item href="#">
            Reports
        </flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>