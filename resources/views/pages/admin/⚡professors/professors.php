<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Professors')] class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'User Management', 'href' => '#'],
        ['label' => 'Professors'],
    ];

    public string $title = 'Professors';
    public string $description = 'Manage professor records, monitor assigned students, and oversee department coordination.';
};
