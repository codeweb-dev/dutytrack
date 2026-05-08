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

    <flux:sidebar.item icon="chart-bar-square" href="#">
        Productivity
    </flux:sidebar.item>

    <flux:sidebar.item icon="chat-bubble-left-right" href="{{ route('supervisor.messages') }}"
        :current="request()->routeIs('supervisor.messages')" wire:navigate>
        Messages
    </flux:sidebar.item>

    <flux:sidebar.item icon="document-text" href="#">
        Reports / Submissions
    </flux:sidebar.item>

    <flux:sidebar.item icon="check-badge" href="#">
        Evaluation
    </flux:sidebar.item>
</flux:sidebar.nav>
