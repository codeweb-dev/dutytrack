<?php

use Livewire\Component;

new class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Users Management', 'href' => '#'],
        ['label' => 'Students OJT Management'],
    ];

    public string $title = 'Students OJT Management';
    public string $description = 'Manage student records, monitor progress, and generate reports.';
};
