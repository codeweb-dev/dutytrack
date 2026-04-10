<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

new #[Title('Supervisors Management')] class extends Component {
    use WithPagination;

    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'Users Management', 'href' => '#'], ['label' => 'Supervisors Management']];

    public string $title = 'Supervisors Management';
    public string $description = 'Manage supervisor records, monitor assigned students, and oversee department coordination.';

    public string $sortBy = 'department';
    public string $sortDirection = 'asc';
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
    public function supervisors(): LengthAwarePaginator
    {
        $supervisors = collect([
            [
                'id' => 1,
                'name' => 'Maria Santos',
                'email' => 'maria.santos@dutytrack.test',
                'avatar' => 'https://i.pravatar.cc/100?img=11',
                'department' => 'Computer Science',
                'status' => 'Active',
                'status_color' => 'green',
                'students_count' => 12,
            ],
            [
                'id' => 2,
                'name' => 'John Reyes',
                'email' => 'john.reyes@dutytrack.test',
                'avatar' => 'https://i.pravatar.cc/100?img=12',
                'department' => 'Information Technology',
                'status' => 'Active',
                'status_color' => 'green',
                'students_count' => 9,
            ],
            [
                'id' => 3,
                'name' => 'Angela Cruz',
                'email' => 'angela.cruz@dutytrack.test',
                'avatar' => 'https://i.pravatar.cc/100?img=13',
                'department' => 'Business Administration',
                'status' => 'On Leave',
                'status_color' => 'amber',
                'students_count' => 6,
            ],
            [
                'id' => 4,
                'name' => 'Mark Villanueva',
                'email' => 'mark.villanueva@dutytrack.test',
                'avatar' => 'https://i.pravatar.cc/100?img=14',
                'department' => 'Hospitality Management',
                'status' => 'Active',
                'status_color' => 'green',
                'students_count' => 15,
            ],
            [
                'id' => 5,
                'name' => 'Patricia Gomez',
                'email' => 'patricia.gomez@dutytrack.test',
                'avatar' => 'https://i.pravatar.cc/100?img=15',
                'department' => 'Engineering',
                'status' => 'Inactive',
                'status_color' => 'red',
                'students_count' => 0,
            ],
            [
                'id' => 6,
                'name' => 'Carlos Bautista',
                'email' => 'carlos.bautista@dutytrack.test',
                'avatar' => 'https://i.pravatar.cc/100?img=16',
                'department' => 'Information Systems',
                'status' => 'Active',
                'status_color' => 'green',
                'students_count' => 7,
            ],
        ]);

        if ($this->search !== '') {
            $search = str($this->search)->lower()->value();

            $supervisors = $supervisors->filter(function ($supervisor) use ($search) {
                return str($supervisor['name'])->lower()->contains($search) || str($supervisor['email'])->lower()->contains($search) || str($supervisor['department'])->lower()->contains($search) || str($supervisor['status'])->lower()->contains($search);
            });
        }

        $supervisors = match ($this->sortBy) {
            'students_count' => $this->sortDirection === 'asc' ? $supervisors->sortBy('students_count') : $supervisors->sortByDesc('students_count'),

            'status' => $this->sortDirection === 'asc' ? $supervisors->sortBy('status') : $supervisors->sortByDesc('status'),

            'department' => $this->sortDirection === 'asc' ? $supervisors->sortBy('department') : $supervisors->sortByDesc('department'),

            default => $supervisors,
        };

        $supervisors = $supervisors->values();

        $perPage = 5;
        $page = Paginator::resolveCurrentPage() ?: 1;
        $items = $supervisors->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator($items, $supervisors->count(), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }
};
