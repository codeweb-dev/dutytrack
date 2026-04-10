<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Attendance Monitoring')] class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Monitoring', 'href' => '#'],
        ['label' => 'Attendance Monitoring'],
    ];

    public string $title = 'Attendance Monitoring';
    public string $description = 'Track student attendance, view daily logs, and generate attendance reports.';
};
