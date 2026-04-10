<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Report Generation')] class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Reports & Evaluation', 'href' => '#'],
        ['label' => 'Report Generation'],
    ];

    public string $title = 'Report Generation';
    public string $description = 'Generate comprehensive reports on student performance and productivity.';
};