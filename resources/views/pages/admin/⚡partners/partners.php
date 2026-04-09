

<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

new class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Users Management', 'href' => '#'],
        ['label' => 'Company & Partners Management'],
    ];

    public string $title = 'Company & Partners Management';
    public string $description = 'Manage company and partner records, monitor affiliations, and oversee student deployment.';

    public string $sortBy = 'industry';
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
    public function companies(): LengthAwarePaginator
    {
        $companies = collect([
            [
                'id' => 1,
                'name' => 'Accenture Philippines',
                'contact_person' => 'Maria Santos',
                'logo' => 'https://ui-avatars.com/api/?name=Accenture',
                'industry' => 'Information Technology',
                'status' => 'Active',
                'status_color' => 'green',
                'students_assigned' => 18,
            ],
            [
                'id' => 2,
                'name' => 'Globe Telecom',
                'contact_person' => 'John Reyes',
                'logo' => 'https://ui-avatars.com/api/?name=Globe',
                'industry' => 'Telecommunications',
                'status' => 'Active',
                'status_color' => 'green',
                'students_assigned' => 12,
            ],
            [
                'id' => 3,
                'name' => 'Ayala Corporation',
                'contact_person' => 'Angela Cruz',
                'logo' => 'https://ui-avatars.com/api/?name=Ayala',
                'industry' => 'Conglomerate',
                'status' => 'Pending',
                'status_color' => 'amber',
                'students_assigned' => 6,
            ],
            [
                'id' => 4,
                'name' => 'PLDT Inc.',
                'contact_person' => 'Carlos Bautista',
                'logo' => 'https://ui-avatars.com/api/?name=PLDT',
                'industry' => 'Telecommunications',
                'status' => 'Active',
                'status_color' => 'green',
                'students_assigned' => 10,
            ],
            [
                'id' => 5,
                'name' => 'IBM Philippines',
                'contact_person' => 'Patricia Gomez',
                'logo' => 'https://ui-avatars.com/api/?name=IBM',
                'industry' => 'Technology Consulting',
                'status' => 'Inactive',
                'status_color' => 'red',
                'students_assigned' => 0,
            ],
            [
                'id' => 6,
                'name' => 'Shopee PH',
                'contact_person' => 'Mark Villanueva',
                'logo' => 'https://ui-avatars.com/api/?name=Shopee',
                'industry' => 'E-commerce',
                'status' => 'Active',
                'status_color' => 'green',
                'students_assigned' => 14,
            ],
        ]);

        if ($this->search !== '') {
            $search = str($this->search)->lower()->value();

            $companies = $companies->filter(function ($company) use ($search) {
                return str($company['name'])->lower()->contains($search)
                    || str($company['contact_person'])->lower()->contains($search)
                    || str($company['industry'])->lower()->contains($search)
                    || str($company['status'])->lower()->contains($search);
            });
        }

        $companies = match ($this->sortBy) {
            'students_assigned' => $this->sortDirection === 'asc'
                ? $companies->sortBy('students_assigned')
                : $companies->sortByDesc('students_assigned'),

            'status' => $this->sortDirection === 'asc'
                ? $companies->sortBy('status')
                : $companies->sortByDesc('status'),

            'industry' => $this->sortDirection === 'asc'
                ? $companies->sortBy('industry')
                : $companies->sortByDesc('industry'),

            default => $companies,
        };

        $companies = $companies->values();

        $perPage = 5;
        $page = Paginator::resolveCurrentPage() ?: 1;
        $items = $companies->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $companies->count(),
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }
};
