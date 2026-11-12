<?php

use App\Notifications\SystemNotification;
use App\Enums\Role as EnumsRole;
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

new #[Title('Students')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'People', 'href' => '#'],
        ['label' => 'Student Management'],
    ];

    public string $title = 'Student Management';

    public string $description = 'Manage the students who signed up under to your coordinator account.';

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    public string $search = '';

    public string $status = '';

    public string $rejectionReason = '';

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
            'active' => (clone $students)->where(fn (Builder $query) => $query->whereNull('status')->orWhereNotIn('status', ['pending', 'inactive']))->count(),
            'inactive' => (clone $students)->where('status', 'inactive')->count(),
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

        $student->notify(new SystemNotification(
            'Account approved', 'Welcome! Your coordinator approved your account.', route('dashboard'), 'check-circle',
        ));

        Flux::modal('review-student-'.$student->id)->close();

        Flux::toast(variant: 'success', heading: 'Student approved.', text: "{$student->display_name} can now log in.");

        unset($this->students, $this->summary);
    }

    // Inactive students can't log in (see FortifyServiceProvider) until reactivated.
    public function setInactive(int $studentId): void
    {
        $student = User::query()
            ->where('coordinator_id', Auth::id())
            ->where(fn (Builder $query) => $query->whereNull('status')->orWhereNotIn('status', ['pending', 'inactive']))
            ->findOrFail($studentId);

        $student->forceFill(['status' => 'inactive', 'remember_token' => Str::random(60)])->save();

        // Sign out any open sessions too; relies on the database session driver.
        DB::table('sessions')->where('user_id', $student->id)->delete();

        AuditLogger::log(
            module: 'Students',
            action: 'set_inactive',
            target: $student,
            targetLabel: $student->display_name,
            description: 'Set a Student account to inactive.',
        );

        Flux::modal('inactive-student-'.$student->id)->close();

        Flux::toast(variant: 'success', heading: 'Student set to inactive.', text: "{$student->display_name} can no longer log in.");

        unset($this->students, $this->summary);
    }

    public function activate(int $studentId): void
    {
        $student = User::query()
            ->where('coordinator_id', Auth::id())
            ->where('status', 'inactive')
            ->findOrFail($studentId);

        $student->update(['status' => 'active']);

        AuditLogger::log(
            module: 'Students',
            action: 'activated',
            target: $student,
            targetLabel: $student->display_name,
            description: 'Reactivated a Student account.',
        );

        $student->notify(new SystemNotification(
            'Account reactivated', 'Your coordinator reactivated your account.', route('dashboard'), 'check-circle',
        ));

        Flux::toast(variant: 'success', heading: 'Student activated.', text: "{$student->display_name} can log in again.");

        unset($this->students, $this->summary);
    }

    // Keep rejected sign-ups in the Archive so they can be restored; the reason is shown at login.
    public function reject(int $studentId): void
    {
        $this->validate(['rejectionReason' => ['required', 'string', 'max:1000']], attributes: ['rejectionReason' => 'reason']);

        $student = $this->pendingStudent($studentId);

        $student->update(['rejection_reason' => trim($this->rejectionReason)]);
        $this->reset('rejectionReason');

        AuditLogger::log(
            module: 'Students',
            action: 'rejected',
            target: $student,
            targetLabel: $student->display_name,
            description: 'Rejected a Student sign-up.',
        );

        $student->delete();

        Flux::modal('reject-student-'.$student->id)->close();

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
            $query->where(fn (Builder $query) => $query->whereNull('users.status')->orWhereNotIn('users.status', ['pending', 'inactive']));
        } elseif ($this->status === 'inactive') {
            $query->where('users.status', 'inactive');
        } elseif ($this->status === 'assigned') {
            $query->whereHas('ojtAssignment', fn (Builder $query) => $query->whereNotNull('company_supervisor_id'));
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

        $query->orderBy($sortColumn, $sortDirection)->orderBy('users.last_name')->orderBy('users.first_name');

        return $query->paginate(10);
    }
};
