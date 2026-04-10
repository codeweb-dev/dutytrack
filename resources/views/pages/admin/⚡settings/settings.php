<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('System Settings')] class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'System Management', 'href' => '#'],
        ['label' => 'System Settings'],
    ];

    public string $title = 'System Settings';
    public string $description = 'Manage system-wide settings and configurations.';
};