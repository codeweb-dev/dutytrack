<?php

use Livewire\Component;

new class extends Component {};
?>

<flux:sidebar.nav>
    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
        wire:navigate>
        Dashboard
    </flux:sidebar.item>

    <flux:sidebar.item icon="academic-cap" href="{{ route('supervisor.assigned-students') }}"
        :current="request()->routeIs('supervisor.assigned-students')" wire:navigate>
        Assigned Students
    </flux:sidebar.item>

    <flux:sidebar.item icon="clock" href="{{ route('supervisor.duty-hours') }}"
        :current="request()->routeIs('supervisor.duty-hours')" wire:navigate>
        Duty Hours
    </flux:sidebar.item>

    <flux:sidebar.item icon="rectangle-stack" href="{{ route('supervisor.tasks') }}"
        :current="request()->routeIs('supervisor.tasks')" wire:navigate>
        Tasks
    </flux:sidebar.item>

    <flux:sidebar.item icon="chat-bubble-left-right" href="{{ route('supervisor.messages') }}"
        :current="request()->routeIs('supervisor.messages')" wire:navigate>
        Messages
    </flux:sidebar.item>

    <flux:sidebar.item icon="document-text" href="{{ route('supervisor.reports-submissions') }}"
        :current="request()->routeIs('supervisor.reports-submissions')" wire:navigate>
        Reports / Submissions
    </flux:sidebar.item>

    <flux:sidebar.item icon="check-badge" href="{{ route('supervisor.evaluations') }}"
        :current="request()->routeIs('supervisor.evaluations')" wire:navigate>
        Evaluations
    </flux:sidebar.item>
</flux:sidebar.nav>
