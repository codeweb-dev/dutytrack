<?php

use Livewire\Component;

new class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'System Management', 'href' => '#'],
        ['label' => 'Audit Logs'],
    ];

    public string $title = 'Audit Logs';
    public string $description = 'View and analyze system audit logs for security and compliance purposes.';
};