<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

new #[Title('Students OJT Management')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Users Management', 'href' => '#'],
        ['label' => 'Students OJT Management'],
    ];

    public string $title = 'Students OJT Management';
    public string $description = 'Manage student records, monitor progress, and generate reports.';

    public string $sortBy = 'start_date';
    public string $sortDirection = 'desc';
    public string $search = '';

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function students(): LengthAwarePaginator
    {
        $students = collect([
            [
                'id' => 1,
                'name' => 'Juan Dela Cruz',
                'company' => 'Accenture',
                'avatar' => 'https://i.pravatar.cc/100?img=1',
                'start_date' => '2026-07-01',
                'status' => 'Active',
                'status_color' => 'green',
                'hours_rendered' => 320,
            ],
            [
                'id' => 2,
                'name' => 'Maria Santos',
                'company' => 'Globe Telecom',
                'avatar' => 'https://i.pravatar.cc/100?img=2',
                'start_date' => '2026-06-20',
                'status' => 'Completed',
                'status_color' => 'sky',
                'hours_rendered' => 486,
            ],
            [
                'id' => 3,
                'name' => 'Carlos Reyes',
                'company' => 'PLDT',
                'avatar' => 'https://i.pravatar.cc/100?img=3',
                'start_date' => '2026-07-10',
                'status' => 'Incomplete',
                'status_color' => 'amber',
                'hours_rendered' => 150,
            ],
            [
                'id' => 4,
                'name' => 'Angela Bautista',
                'company' => 'Shopee PH',
                'avatar' => 'https://i.pravatar.cc/100?img=4',
                'start_date' => '2026-07-05',
                'status' => 'Active',
                'status_color' => 'green',
                'hours_rendered' => 210,
            ],
            [
                'id' => 5,
                'name' => 'Mark Villanueva',
                'company' => 'Ayala Corp',
                'avatar' => 'https://i.pravatar.cc/100?img=5',
                'start_date' => '2026-06-15',
                'status' => 'Dropped',
                'status_color' => 'red',
                'hours_rendered' => 80,
            ],
            [
                'id' => 6,
                'name' => 'Patricia Gomez',
                'company' => 'IBM',
                'avatar' => 'https://i.pravatar.cc/100?img=6',
                'start_date' => '2026-07-03',
                'status' => 'Active',
                'status_color' => 'green',
                'hours_rendered' => 260,
            ],
        ]);

        if ($this->search !== '') {
            $search = str($this->search)->lower()->value();

            $students = $students->filter(function ($student) use ($search) {
                return str($student['name'])->lower()->contains($search)
                    || str($student['company'])->lower()->contains($search)
                    || str($student['status'])->lower()->contains($search);
            });
        }

        $students = match ($this->sortBy) {
            'hours_rendered' => $this->sortDirection === 'asc'
                ? $students->sortBy('hours_rendered')
                : $students->sortByDesc('hours_rendered'),

            'status' => $this->sortDirection === 'asc'
                ? $students->sortBy('status')
                : $students->sortByDesc('status'),

            'start_date' => $this->sortDirection === 'asc'
                ? $students->sortBy('start_date')
                : $students->sortByDesc('start_date'),

            default => $students,
        };

        $students = $students->values();

        $perPage = 5;
        $page = Paginator::resolveCurrentPage() ?: 1;
        $items = $students->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $students->count(),
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }
};
