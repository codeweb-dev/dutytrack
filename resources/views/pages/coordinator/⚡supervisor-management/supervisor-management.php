<?php

use App\Notifications\SystemNotification;
use App\Enums\Role as EnumsRole;
use App\Models\AttendanceLog;
use App\Models\User;
use App\Support\AuditLogger;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
        ['label' => 'People', 'href' => '#'],
        ['label' => 'Supervisor Management'],
    ];

    public string $title = 'Supervisor Management';

    public string $description = 'Create and manage only the supervisors assigned to your coordinator account.';

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    public string $search = '';

    public string $status = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
        unset($this->supervisors);
    }

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
        $this->reset('search', 'status', 'sortBy', 'sortDirection');

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

    // Inactive supervisors can't log in (see FortifyServiceProvider) until reactivated.
    public function setInactive(int $supervisorId): void
    {
        $supervisor = User::query()
            ->role(EnumsRole::SUPERVISOR->value)
            ->where('coordinator_id', Auth::id())
            ->where(fn (Builder $query) => $query->whereNull('status')->orWhereNotIn('status', ['pending', 'inactive']))
            ->findOrFail($supervisorId);

        $supervisor->forceFill(['status' => 'inactive', 'remember_token' => Str::random(60)])->save();

        // Sign out any open sessions too; relies on the database session driver.
        DB::table('sessions')->where('user_id', $supervisor->id)->delete();

        AuditLogger::log(
            module: 'Supervisors',
            action: 'set_inactive',
            target: $supervisor,
            targetLabel: $supervisor->display_name,
            description: 'Set a Supervisor account to inactive.',
        );

        Flux::modal('inactive-supervisor-'.$supervisor->id)->close();

        Flux::toast(variant: 'success', heading: 'Supervisor set to inactive.', text: "{$supervisor->display_name} can no longer log in.");

        unset($this->supervisors, $this->summary);
    }

    public function activate(int $supervisorId): void
    {
        $supervisor = User::query()
            ->role(EnumsRole::SUPERVISOR->value)
            ->where('coordinator_id', Auth::id())
            ->where('status', 'inactive')
            ->findOrFail($supervisorId);

        $supervisor->update(['status' => 'active']);

        AuditLogger::log(
            module: 'Supervisors',
            action: 'activated',
            target: $supervisor,
            targetLabel: $supervisor->display_name,
            description: 'Reactivated a Supervisor account.',
        );

        $supervisor->notify(new SystemNotification(
            'Account reactivated', 'The coordinator reactivated your account.', route('dashboard'), 'check-circle',
        ));

        Flux::toast(variant: 'success', heading: 'Supervisor activated.', text: "{$supervisor->display_name} can log in again.");

        unset($this->supervisors, $this->summary);
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
            'active' => (clone $supervisors)->where(fn (Builder $query) => $query->whereNull('status')->orWhereNotIn('status', ['pending', 'inactive']))->count(),
            'inactive' => (clone $supervisors)->where('status', 'inactive')->count(),
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

        $ownStudents = fn ($query) => $query->whereHas('student', fn (Builder $studentQuery) => $studentQuery
            ->role(EnumsRole::STUDENT->value)
            ->where('coordinator_id', Auth::id()));

        $query->withCount(['companySupervisorAssignments as assigned_students_count' => $ownStudents])
            ->with(['companySupervisorAssignments' => fn ($query) => $ownStudents($query)->with(['student' => fn ($studentQuery) => $studentQuery
                ->with('studentProfile')
                ->select('users.*')
                ->selectSub(AttendanceLog::query()->selectRaw('coalesce(sum(hours_rendered), 0)')->whereColumn('student_id', 'users.id'), 'rendered_hours')])]);

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

        if (in_array($this->status, ['internal', 'external'], true)) {
            $query->where('supervisor_profiles.supervisor_type', $this->status);
        } elseif ($this->status === 'active') {
            $query->where(fn (Builder $query) => $query->whereNull('users.status')->orWhereNotIn('users.status', ['pending', 'inactive']));
        } elseif ($this->status === 'inactive') {
            $query->where('users.status', 'inactive');
        }

        $sortColumn = [
            'name' => 'users.last_name',
            'company_name' => 'supervisor_profiles.company_name',
        ][$this->sortBy] ?? 'users.last_name';

        $query->orderBy($sortColumn, $this->sortDirection === 'desc' ? 'desc' : 'asc')->orderBy('users.last_name')->orderBy('users.first_name');

        return $query->paginate(10);
    }
};
