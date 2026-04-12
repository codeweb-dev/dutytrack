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
        Assigned Students
    </flux:sidebar.item>

    <flux:sidebar.item icon="clipboard-document-check" href="#">
        Productivity
    </flux:sidebar.item>

    <flux:sidebar.item icon="clipboard-document-check" href="#">
        Reports / Submissions
    </flux:sidebar.item>

    <flux:sidebar.item icon="clipboard-document-check" href="#">
        Evaluation
    </flux:sidebar.item>
</flux:sidebar.nav>
