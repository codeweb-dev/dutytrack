<?php

use App\Enums\Role as EnumsRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Supervisor Management')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Coordinator Workspace', 'href' => '#'],
        ['label' => 'Supervisor Management'],
    ];

    public string $title = 'Supervisor Management';

    public string $description = 'Create and manage only the supervisors assigned to your coordinator account.';

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    public string $search = '';

    public function updatedSortBy(): void
    {
        $this->resetPage();
        unset($this->supervisors);
    }

    public function updatedSortDirection(): void
    {
        $this->resetPage();
        unset($this->supervisors);
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'sortBy', 'sortDirection');

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
            ->role(EnumsRole::SUPERVISOR->value)
            ->where('users.coordinator_id', Auth::id())
            ->leftJoin('supervisor_profiles', 'users.id', '=', 'supervisor_profiles.user_id')
            ->select('users.*');

        if ($this->search !== '') {
            $search = '%'.$this->search.'%';

            $query->where(function (Builder $query) use ($search) {
                $query->where('users.name', 'like', $search)
                    ->orWhere('users.email', 'like', $search)
                    ->orWhere('users.contact_number', 'like', $search)
                    ->orWhere('supervisor_profiles.company_name', 'like', $search)
                    ->orWhere('supervisor_profiles.company_address', 'like', $search);
            });
        }

        $sortColumn = [
            'name' => 'users.last_name',
            'company_name' => 'supervisor_profiles.company_name',
        ][$this->sortBy] ?? 'users.last_name';

        $query->orderBy($sortColumn, $this->sortDirection === 'desc' ? 'desc' : 'asc');

        return $query->paginate(10);
    }
};
