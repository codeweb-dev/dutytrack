<?php

use Livewire\Component;

new class extends Component {};
?>

<flux:sidebar.nav>
    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
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

    <flux:sidebar.item icon="document-text" href="#">
        Reports / Submissions
    </flux:sidebar.item>

    <flux:sidebar.item icon="check-badge" href="#">
        Evaluation
    </flux:sidebar.item>
</flux:sidebar.nav>
