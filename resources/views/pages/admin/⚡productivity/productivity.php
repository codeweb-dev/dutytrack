<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Productivity Monitoring')] class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Monitoring', 'href' => '#'],
        ['label' => 'Productivity Monitoring'],
    ];

    public string $title = 'Productivity Monitoring';
    public string $description = 'Monitor student productivity, view performance metrics, and generate productivity reports.';
};