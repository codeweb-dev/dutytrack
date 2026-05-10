<?php

use Livewire\Component;

new class extends Component {};
?>

<flux:sidebar.nav>
    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
        wire:navigate>
        Dashboard
    </flux:sidebar.item>

    <flux:sidebar.item icon="clipboard-document-list" :href="route('student.attendance')" :current="request()->routeIs('student.attendance')"
        wire:navigate>
        Attendance
    </flux:sidebar.item>

    <flux:sidebar.item icon="clock" :href="route('student.duty-hours')" :current="request()->routeIs('student.duty-hours')"
        wire:navigate>
        Duty Hours
    </flux:sidebar.item>

    <flux:sidebar.item icon="chat-bubble-left-right" :href="route('student.messages')" :current="request()->routeIs('student.messages')"
        wire:navigate>
        Messages
    </flux:sidebar.item>

    {{-- <flux:sidebar.item icon="document-text" href="#">
        Reports / Submissions
    </flux:sidebar.item> --}}

    <flux:sidebar.group expandable icon="squares-2x2" heading="Productivity">
        <flux:sidebar.item icon="rectangle-stack" href="{{ route('student.tasks') }}" :current="request()->routeIs('student.tasks', 'student.kanban')"
            wire:navigate>
            Tasks
        </flux:sidebar.item>
        <flux:sidebar.item icon="rectangle-stack" href="{{ route('student.notes') }}" :current="request()->routeIs('student.notes')"
            wire:navigate>
            Notes
        </flux:sidebar.item>
        <flux:sidebar.item icon="clock" href="{{ route('student.pomodoro') }}" :current="request()->routeIs('student.pomodoro')"
            wire:navigate>
            Pomodoro Timer
        </flux:sidebar.item>
        <flux:sidebar.item icon="fire" href="{{ route('student.pet-streak') }}" :current="request()->routeIs('student.pet-streak')"
            wire:navigate badge="In Progress" badgeColor="red">
            Pet Streak
        </flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>
