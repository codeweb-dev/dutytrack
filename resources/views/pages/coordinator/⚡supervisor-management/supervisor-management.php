<?php

use App\Enums\Role as EnumsRole;
use App\Models\User;
use Flux\Flux;
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

    public ?int $selectedSupervisorId = null;

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
        unset($this->supervisors, $this->summary);
    }

    public function viewAssignedStudents(int $supervisorId): void
    {
        $supervisor = User::query()
            ->role(EnumsRole::SUPERVISOR->value)
            ->where('coordinator_id', Auth::id())
            ->findOrFail($supervisorId);

        $this->selectedSupervisorId = $supervisor->id;
        unset($this->selectedSupervisor, $this->assignedStudents);

        Flux::modal('assigned-students')->show();
    }

    public function closeAssignedStudents(): void
    {
        $this->reset('selectedSupervisorId');
        unset($this->selectedSupervisor, $this->assignedStudents);
    }

    #[Computed]
    public function selectedSupervisor(): ?User
    {
        return $this->selectedSupervisorId
            ? User::query()
                ->role(EnumsRole::SUPERVISOR->value)
                ->where('coordinator_id', Auth::id())
                ->find($this->selectedSupervisorId)
            : null;
    }

    #[Computed]
    public function assignedStudents()
    {
        $supervisor = $this->selectedSupervisor;

        if (! $supervisor) {
            return collect();
        }

        return User::query()
            ->with('studentProfile')
            ->role(EnumsRole::STUDENT->value)
            ->where('coordinator_id', Auth::id())
            ->whereHas('ojtAssignment', fn (Builder $query) => $query->where('company_supervisor_id', $supervisor->id))
            ->orderBy('last_name')
            ->get();
    }

    #[Computed]
    public function summary(): array
    {
        $supervisors = User::query()
            ->role(EnumsRole::SUPERVISOR->value)
            ->where('coordinator_id', Auth::id());

        return [
            'total' => (clone $supervisors)->count(),
            'internal' => (clone $supervisors)->whereHas('supervisorProfile', fn (Builder $query) => $query->where('supervisor_type', 'internal'))->count(),
            'external' => (clone $supervisors)->whereHas('supervisorProfile', fn (Builder $query) => $query->where('supervisor_type', 'external'))->count(),
            'active' => (clone $supervisors)->where(fn (Builder $query) => $query->whereNull('status')->orWhere('status', '!=', 'pending'))->count(),
        ];
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

        $query->withCount(['companySupervisorAssignments as assigned_students_count' => fn (Builder $query) => $query->whereHas('student', fn (Builder $studentQuery) => $studentQuery
            ->role(EnumsRole::STUDENT->value)
            ->where('coordinator_id', Auth::id()))]);

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
