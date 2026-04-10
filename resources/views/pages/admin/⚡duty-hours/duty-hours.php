<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Duty Hours Tracking')] class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Monitoring', 'href' => '#'],
        ['label' => 'Duty Hours Tracking'],
    ];

    public string $title = 'Duty Hours Tracking';
    public string $description = 'Track student duty hours, view daily logs, and generate duty hours reports.';
};