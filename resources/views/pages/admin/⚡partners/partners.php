<?php

use Livewire\Component;

new class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Users Management', 'href' => '#'],
        ['label' => 'Company & Partners Management'],
    ];

    public string $title = 'Company & Partners Management';
    public string $description = 'Manage company and partner records, monitor progress, and generate reports.';
};