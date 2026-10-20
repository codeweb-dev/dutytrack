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

new #[Title('Supervisors')] class extends Component {
    use WithPagination;

    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'User Management', 'href' => '#'], ['label' => 'Supervisors']];

    public string $title = 'Supervisors';
    public string $description = 'Manage supervisor records, monitor assigned students, and oversee department coordination.';

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
        unset($this->supervisors);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->supervisors);
    }

    #[On('supervisor-saved')]
    public function refreshSupervisors(): void
    {
        $this->resetPage();
        unset($this->supervisors);
    }

    #[Computed]
    public function supervisors(): LengthAwarePaginator
    {
        $query = User::query()
            ->with('supervisorProfile')
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', EnumsRole::SUPERVISOR->value);
            })
            ->leftJoin('supervisor_profiles', 'users.id', '=', 'supervisor_profiles.user_id')
            ->select('users.*');

        if ($this->search !== '') {
            $search = '%' . $this->search . '%';

            $query->where(function (Builder $query) use ($search) {
                $query->where('users.name', 'like', $search)->orWhere('users.email', 'like', $search)->orWhere('users.contact_number', 'like', $search)->orWhere('supervisor_profiles.supervisor_id', 'like', $search)->orWhere('supervisor_profiles.company_name', 'like', $search)->orWhere('supervisor_profiles.company_address', 'like', $search);
            });
        }

        $allowedSorts = [
            'name' => 'users.name',
            'supervisor_id' => 'supervisor_profiles.supervisor_id',
            'company_name' => 'supervisor_profiles.company_name',
        ];

        $sortColumn = $allowedSorts[$this->sortBy] ?? 'users.name';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate(10);
    }
};
