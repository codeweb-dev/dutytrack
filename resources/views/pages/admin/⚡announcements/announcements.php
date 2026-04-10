<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('System Announcements')] class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'System Management', 'href' => '#'],
        ['label' => 'System Announcements'],
    ];

    public string $title = 'System Announcements';
    public string $description = 'Manage and view system-wide announcements.';
};