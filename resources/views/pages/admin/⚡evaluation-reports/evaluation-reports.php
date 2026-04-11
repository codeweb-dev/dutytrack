<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Evaluation Reports')] class extends Component {
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Reports', 'href' => '#'],
        ['label' => 'Evaluation Reports'],
    ];

    public string $title = 'Evaluation Reports';
    public string $description = 'Preview evaluation reports once the evaluation module is available.';
};
