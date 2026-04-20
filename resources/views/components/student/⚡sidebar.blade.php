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

    <flux:sidebar.item icon="briefcase" href="#">
        My Assignments
    </flux:sidebar.item>

    <flux:sidebar.item icon="clipboard-document-list" href="#">
        Attendance
    </flux:sidebar.item>

    <flux:sidebar.item icon="clock" href="#">
        Duty Hours
    </flux:sidebar.item>

    <flux:sidebar.item icon="document-text" href="#">
        Reports / Submissions
    </flux:sidebar.item>

    <flux:sidebar.item icon="check-badge" href="#">
        Evaluation
    </flux:sidebar.item>

    <flux:sidebar.group expandable icon="squares-2x2" heading="Productivity">
        <flux:sidebar.item icon="rectangle-stack" href="{{ route('student.kanban') }}" :current="request()->routeIs('student.kanban')"
            wire:navigate>
            Tasks
        </flux:sidebar.item>
        <flux:sidebar.item icon="bolt" href="{{ route('student.pomodoro') }}" :current="request()->routeIs('student.pomodoro')"
            wire:navigate>
            Pomodoro Timer
        </flux:sidebar.item>
        <flux:sidebar.item icon="fire" href="{{ route('student.pet-streak') }}" :current="request()->routeIs('student.pet-streak')"
            wire:navigate>
            Pet Streak
        </flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>
