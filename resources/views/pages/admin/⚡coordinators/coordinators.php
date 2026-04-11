<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('OJT Coordinators')] class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'User Management', 'href' => '#'],
        ['label' => 'OJT Coordinators'],
    ];

    public string $title = 'OJT Coordinators';
    public string $description = 'Manage coordinator records, monitor assigned students, and oversee department coordination.';
};
