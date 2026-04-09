<?php

use Livewire\Component;

new class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Users Management', 'href' => '#'],
        ['label' => 'Supervisors Management'],
    ];

    public string $title = 'Supervisors Management';
    public string $description = 'Manage supervisor records, monitor progress, and generate reports.';
};