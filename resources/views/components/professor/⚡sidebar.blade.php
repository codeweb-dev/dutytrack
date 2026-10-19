<?php

use Livewire\Component;

new class extends Component {};
?>

<flux:sidebar.nav>
    <flux:sidebar.item icon="home" :href="route('professor.dashboard')" :current="request()->routeIs('professor.dashboard')"
        wire:navigate>
        Dashboard
    </flux:sidebar.item>

    <flux:sidebar.item icon="academic-cap" href="{{ route('professor.assigned-students') }}"
        :current="request()->routeIs('professor.assigned-students')" wire:navigate>
        Assigned Students
    </flux:sidebar.item>


    <flux:sidebar.item icon="rectangle-stack" href="{{ route('professor.tasks') }}"
        :current="request()->routeIs('professor.tasks')" wire:navigate>
        Tasks
    </flux:sidebar.item>

    <flux:sidebar.item icon="chat-bubble-left-right" href="{{ route('professor.messages') }}"
        :current="request()->routeIs('professor.messages')" wire:navigate>
        Messages
    </flux:sidebar.item>

    <flux:sidebar.item icon="document-text" href="{{ route('professor.reports-submissions') }}"
        :current="request()->routeIs('professor.reports-submissions')" wire:navigate>
        Reports / Submissions
    </flux:sidebar.item>

    <flux:sidebar.item icon="check-badge" href="{{ route('professor.evaluations') }}"
        :current="request()->routeIs('professor.evaluations')" wire:navigate>
        Evaluations
    </flux:sidebar.item>
</flux:sidebar.nav>
