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

new #[Title('OJT Coordinators Management')] class extends Component {
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Users Management', 'href' => '#'],
        ['label' => 'OJT Coordinators'],
    ];

    public string $title = 'OJT Coordinators';
    public string $description = 'Manage OJT coordinator records and department details.';

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
        unset($this->coordinators);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->coordinators);
    }

    #[On('coordinator-saved')]
    public function refreshCoordinators(): void
    {
        $this->resetPage();
        unset($this->coordinators);
    }

    #[Computed]
    public function coordinators(): LengthAwarePaginator
    {
        $query = User::query()
            ->with('coordinatorProfile')
            ->whereHas('roles', function (Builder $roleQuery) {
                $roleQuery->where('name', EnumsRole::OJTCOORDINATOR->value);
            })
            ->leftJoin('coordinator_profiles', 'users.id', '=', 'coordinator_profiles.user_id')
            ->select('users.*');

        if ($this->search !== '') {
            $search = '%' . $this->search . '%';

            $query->where(function (Builder $query) use ($search) {
                $query->where('users.name', 'like', $search)
                    ->orWhere('users.first_name', 'like', $search)
                    ->orWhere('users.last_name', 'like', $search)
                    ->orWhere('users.email', 'like', $search)
                    ->orWhere('users.contact_number', 'like', $search)
                    ->orWhere('coordinator_profiles.department', 'like', $search);
            });
        }

        $allowedSorts = [
            'name' => 'users.name',
            'department' => 'coordinator_profiles.department',
        ];

        $sortColumn = $allowedSorts[$this->sortBy] ?? 'users.name';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate(10);
    }
};
