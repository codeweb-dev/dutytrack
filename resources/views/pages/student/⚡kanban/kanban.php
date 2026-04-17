<?php

use Livewire\Component;

new class extends Component {
    public array $columns = [];

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Productivity', 'href' => '#'],
        ['label' => 'Kanban Board'],
    ];

    public string $title = 'Kanban Board';
    public string $description = 'Organize and track your tasks with this interactive Kanban board. Move tasks across columns to reflect their current status and stay on top of your work.';

    public function mount(): void
    {
        $this->columns = [
            [
                'title' => 'Pending',
                'tasks' => [
                    [
                        'title' => 'Resolve late check-in exception flow for students',
                        'labels' => ['Bug'],
                    ],
                    [
                        'title' => 'Align partner company cards on smaller screens',
                        'labels' => ['UI'],
                    ],
                    [
                        'title' => 'Investigate missing duty log summaries in analytics',
                        'labels' => ['Bug', 'High priority'],
                    ],
                    [
                        'title' => 'Prepare database index for time log reporting',
                        'labels' => ['Backend'],
                    ],
                    [
                        'title' => 'Fix icon spacing in the student footer',
                        'labels' => ['UI'],
                    ],
                ],
            ],
            [
                'title' => 'In Progress',
                'tasks' => [
                    [
                        'title' => 'Refresh OJT handbook acknowledgement screen',
                        'labels' => ['UI'],
                    ],
                    [
                        'title' => 'Fix partner search auto-suggestions',
                        'labels' => ['Bug', 'UI'],
                    ],
                    [
                        'title' => 'Improve loading state for report submissions',
                        'labels' => ['UI'],
                    ],
                    [
                        'title' => 'Fix hour picker keyboard input issue',
                        'labels' => ['Bug'],
                    ],
                    [
                        'title' => 'Correct approval permissions for supervisors',
                        'labels' => ['Backend', 'Bug'],
                    ],
                    [
                        'title' => 'Restore broken partner logo thumbnails',
                        'labels' => ['Bug'],
                    ],
                ],
            ],
            [
                'title' => 'Done',
                'tasks' => [
                    [
                        'title' => 'Improve mobile layout for duty history',
                        'labels' => ['UI'],
                    ],
                    [
                        'title' => 'Fix sorting in the student submissions table',
                        'labels' => ['Bug', 'UI'],
                    ],
                    [
                        'title' => 'Normalize API validation error messages',
                        'labels' => ['Backend'],
                    ],
                    [
                        'title' => 'Run accessibility pass on attendance widgets',
                        'labels' => ['UI'],
                    ],
                    [
                        'title' => 'Polish the student dashboard experience',
                        'labels' => ['UI'],
                    ],
                ],
            ],
            [
                'title' => 'In Review',
                'tasks' => [
                    [
                        'title' => 'Add double-submit protection to daily logs',
                        'labels' => ['Bug', 'UI'],
                    ],
                    [
                        'title' => 'Prevent crashes during bulk file uploads',
                        'labels' => ['High priority'],
                    ],
                    [
                        'title' => 'Handle concurrent approval requests safely',
                        'labels' => ['Backend'],
                    ],
                ],
            ],
        ];
    }

    public function labelClasses(string $label): string
    {
        return match ($label) {
            'Bug' => 'red',
            'UI' => 'blue',
            'Backend' => 'green',
            'High priority' => 'yellow',
            default => 'bg-white/10 text-white/80 ring-1 ring-inset ring-white/10',
        };
    }
};
