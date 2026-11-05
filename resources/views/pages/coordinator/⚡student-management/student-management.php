<?php

use App\Enums\Role as EnumsRole;
use App\Models\User;
use App\Support\AuditLogger;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Students')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Coordinator Workspace', 'href' => '#'],
        ['label' => 'Student Management'],
    ];

    public string $title = 'Student Management';

    public string $description = 'Manage the students who signed up under to your coordinator account.';

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    public string $search = '';

    public string $status = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
        unset($this->students);
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
        unset($this->students);
    }

    public function updatedSortDirection(): void
    {
        $this->resetPage();
        unset($this->students);
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'status', 'sortBy', 'sortDirection');

        $this->resetPage();
        unset($this->students);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->students);
    }

    #[On('student-saved')]
    public function refreshStudents(): void
    {
        $this->resetPage();
        unset($this->students, $this->summary);
    }

    #[Computed]
    public function summary(): array
    {
        $students = User::query()
            ->whereHas('roles', fn (Builder $query) => $query->where('name', EnumsRole::STUDENT->value))
            ->where('coordinator_id', Auth::id());

        return [
            'total' => (clone $students)->count(),
            'pending' => (clone $students)->where('status', 'pending')->count(),
            'active' => (clone $students)->where(fn (Builder $query) => $query->whereNull('status')->orWhere('status', '!=', 'pending'))->count(),
            'assigned' => (clone $students)->whereHas('ojtAssignment', fn (Builder $query) => $query->whereNotNull('company_supervisor_id'))->count(),
        ];
    }

    protected function pendingStudent(int $studentId): User
    {
        return User::query()
            ->where('coordinator_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($studentId);
    }

    public function approve(int $studentId): void
    {
        $student = $this->pendingStudent($studentId);

        $student->update(['status' => 'active']);

        AuditLogger::log(
            module: 'Students',
            action: 'approved',
            target: $student,
            targetLabel: $student->display_name,
            description: 'Approved a Student account.',
        );

        Flux::modal('review-student-'.$student->id)->close();

        Flux::toast(variant: 'success', heading: 'Student approved.', text: "{$student->display_name} can now log in.");

        unset($this->students, $this->summary);
    }

    // Keep rejected sign-ups in the Archive so they can be restored.
    public function reject(int $studentId): void
    {
        $student = $this->pendingStudent($studentId);

        AuditLogger::log(
            module: 'Students',
            action: 'rejected',
            target: $student,
            targetLabel: $student->display_name,
            description: 'Rejected a Student sign-up.',
        );

        $student->delete();

        Flux::toast(variant: 'success', heading: 'Sign-up archived.', text: "{$student->display_name}'s account can be restored from the Archive.");

        unset($this->students, $this->summary);
    }

    #[Computed]
    public function students(): LengthAwarePaginator
    {
        $query = User::query()
            ->with(['studentProfile', 'coordinator', 'coordinatorProfile', 'ojtAssignment.companySupervisor'])
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', EnumsRole::STUDENT->value);
            })
            ->where('users.coordinator_id', Auth::id())
            ->leftJoin('student_profiles', 'users.id', '=', 'student_profiles.user_id')
            ->select('users.*');

        if ($this->search !== '') {
            $search = '%'.$this->search.'%';

            $query->where(function (Builder $query) use ($search) {
                $query->where('users.name', 'like', $search)
                    ->orWhere('users.first_name', 'like', $search)
                    ->orWhere('users.last_name', 'like', $search)
                    ->orWhere('users.email', 'like', $search)
                    ->orWhere('users.contact_number', 'like', $search)
                    ->orWhere('student_profiles.student_id', 'like', $search)
                    ->orWhere('student_profiles.course', 'like', $search)
                    ->orWhere('student_profiles.ojt_required_hours', 'like', $search);
            });
        }

        if ($this->status === 'pending') {
            $query->where('users.status', 'pending');
        } elseif ($this->status === 'active') {
            $query->where(fn (Builder $query) => $query->whereNull('users.status')->orWhere('users.status', '!=', 'pending'));
        }

        $allowedSorts = [
            'name' => 'users.last_name',
            'student_id' => 'student_profiles.student_id',
            'assigned_supervisor' => 'assigned_supervisors.last_name',
        ];

        if ($this->sortBy === 'assigned_supervisor') {
            $query->leftJoin('ojt_assignments', 'users.id', '=', 'ojt_assignments.student_id')
                ->leftJoin('users as assigned_supervisors', 'ojt_assignments.company_supervisor_id', '=', 'assigned_supervisors.id');
        }

        $sortColumn = $allowedSorts[$this->sortBy] ?? 'users.last_name';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $query->orderByRaw("users.status = 'pending' desc")->orderBy($sortColumn, $sortDirection);

        return $query->paginate(10);
    }
};
