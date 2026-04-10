<?php

use Livewire\Component;

new class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'System Management', 'href' => '#'],
        ['label' => 'System Settings'],
    ];

    public string $title = 'System Settings';
    public string $description = 'Manage system-wide settings and configurations.';
};