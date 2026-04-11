<?php

use App\Enums\Role as EnumsRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Professors Management')] class extends Component {
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Users Management', 'href' => '#'],
        ['label' => 'Professors'],
    ];

    public string $title = 'Professors';
    public string $description = 'Manage professor records and department details.';

    public string $sortBy = 'name';
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
        unset($this->professors);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->professors);
    }

    #[On('professor-saved')]
    public function refreshProfessors(): void
    {
        $this->resetPage();
        unset($this->professors);
    }

    #[Computed]
    public function professors(): LengthAwarePaginator
    {
        $query = User::query()
            ->with('professorProfile')
            ->whereHas('roles', function (Builder $roleQuery) {
                $roleQuery->where('name', EnumsRole::PROFESSOR->value);
            })
            ->leftJoin('professor_profiles', 'users.id', '=', 'professor_profiles.user_id')
            ->select('users.*');

        if ($this->search !== '') {
            $search = '%' . $this->search . '%';

            $query->where(function (Builder $query) use ($search) {
                $query->where('users.name', 'like', $search)
                    ->orWhere('users.first_name', 'like', $search)
                    ->orWhere('users.last_name', 'like', $search)
                    ->orWhere('users.email', 'like', $search)
                    ->orWhere('users.contact_number', 'like', $search)
                    ->orWhere('professor_profiles.employee_id', 'like', $search)
                    ->orWhere('professor_profiles.department', 'like', $search);
            });
        }

        $allowedSorts = [
            'name' => 'users.name',
            'employee_id' => 'professor_profiles.employee_id',
            'department' => 'professor_profiles.department',
        ];

        $sortColumn = $allowedSorts[$this->sortBy] ?? 'users.name';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate(5);
    }
};
