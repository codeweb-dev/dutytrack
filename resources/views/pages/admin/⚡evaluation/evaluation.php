<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Evaluation Monitoring')] class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Monitoring', 'href' => '#'],
        ['label' => 'Evaluation Monitoring'],
    ];

    public string $title = 'Evaluation Monitoring';
    public string $description = 'Monitor student evaluations, view performance metrics, and generate evaluation reports.';
};