<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

<flux:sidebar.nav>
    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
        wire:navigate>
        Dashboard
    </flux:sidebar.item>

    <flux:sidebar.item icon="clipboard-document-check" href="#">
        My Assignments
    </flux:sidebar.item>

    <flux:sidebar.item icon="clipboard-document-check" href="#">
        Attendance
    </flux:sidebar.item>

    <flux:sidebar.item icon="clipboard-document-check" href="#">
        Duty Hours
    </flux:sidebar.item>

    <flux:sidebar.item icon="clipboard-document-check" href="#">
        Reports / Submissions
    </flux:sidebar.item>

    <flux:sidebar.item icon="clipboard-document-check" href="#">
        Evaluation
    </flux:sidebar.item>

    <flux:sidebar.group expandable icon="computer-desktop" heading="Productivity">
        <flux:sidebar.item href="{{ route('student.kanban') }}" :current="request()->routeIs('student.kanban')"
            wire:navigate>
            Tasks
        </flux:sidebar.item>
        <flux:sidebar.item href="{{ route('student.pomodoro') }}" :current="request()->routeIs('student.pomodoro')"
            wire:navigate>
            Pomodoro Timer
        </flux:sidebar.item>
        <flux:sidebar.item href="{{ route('student.pet-streak') }}" :current="request()->routeIs('student.pet-streak')"
            wire:navigate>
            Pet Streak
        </flux:sidebar.item>
</flux:sidebar.nav>
