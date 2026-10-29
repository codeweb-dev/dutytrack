<?php

use App\Livewire\Concerns\WithSorting;
use App\Models\StudentProfile;
use App\Models\OjtAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('OJT Assignments')] class extends Component
{
    use WithPagination;
    use WithSorting;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Coordinator Workspace', 'href' => '#'],
        ['label' => 'OJT Assignments'],
    ];

    public string $title = 'OJT Assignments';

    public string $description = 'Assign your students to a supervisor, one assignment per student.';

    public string $search = '';

    public string $ojt_type = '';

    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->assignments, $this->summary);
    }

    public function updatedOjtType(): void
    {
        $this->resetPage();
        unset($this->assignments, $this->summary);
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        unset($this->assignments, $this->summary);
    }

    #[On('assignment-saved')]
    public function refreshAssignments(): void
    {
        $this->resetPage();
        unset($this->assignments, $this->summary);
    }

    public function clearFilters(): void
    {
        $this->reset('sortBy', 'sortDirection');
        $this->search = '';
        $this->ojt_type = '';
        $this->status = '';

        $this->resetPage();
        unset($this->assignments, $this->summary);
    }

    public function badgeColor(string $status): string
    {
        return match ($status) {
            'active' => 'lime',
            'pending' => 'amber',
            'completed' => 'sky',
            'cancelled' => 'red',
            default => 'zinc',
        };
    }

    #[Computed]
    public function assignments(): LengthAwarePaginator
    {
        $coordinatorId = Auth::id();

        $query = OjtAssignment::query()
            ->with([
                'student.studentProfile',
                'student.coordinator',
                'companySupervisor',
            ])
            ->whereHas('student', fn (Builder $query) => $query->where('coordinator_id', $coordinatorId))
            ->when($this->search !== '', function (Builder $query) {
                $search = '%'.$this->search.'%';

                $query->where(function (Builder $nested) use ($search) {
                    $nested->whereHas('student', function (Builder $studentQuery) use ($search) {
                        $studentQuery->where(function (Builder $studentFields) use ($search) {
                            $studentFields->where('name', 'like', $search)
                                ->orWhere('first_name', 'like', $search)
                                ->orWhere('last_name', 'like', $search)
                                ->orWhere('email', 'like', $search);
                        })->orWhereHas('studentProfile', function (Builder $profileQuery) use ($search) {
                            $profileQuery->where('student_id', 'like', $search)
                                ->orWhere('course', 'like', $search);
                        });
                    })->orWhereHas('companySupervisor', function (Builder $supervisorQuery) use ($search) {
                        $supervisorQuery->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    });
                });
            })
            ->when($this->ojt_type !== '', fn (Builder $query) => $query->where('ojt_type', $this->ojt_type))
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status));

        return $this->applySorting($query, [
            'student' => User::select('name')->whereColumn('users.id', 'ojt_assignments.student_id'),
            'student_id' => StudentProfile::select('student_id')->whereColumn('student_profiles.user_id', 'ojt_assignments.student_id'),
            'type' => 'ojt_type',
            'supervisor' => User::select('name')->whereColumn('users.id', 'ojt_assignments.company_supervisor_id'),
            'status' => 'status',
        ])
            ->latest('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function summary(): array
    {
        $coordinatorId = Auth::id();

        $query = OjtAssignment::query()
            ->whereHas('student', fn (Builder $studentQuery) => $studentQuery->where('coordinator_id', $coordinatorId))
            ->when($this->search !== '', function (Builder $query) {
                $search = '%'.$this->search.'%';

                $query->where(function (Builder $nested) use ($search) {
                    $nested->whereHas('student', function (Builder $studentQuery) use ($search) {
                        $studentQuery->where(function (Builder $studentFields) use ($search) {
                            $studentFields->where('name', 'like', $search)
                                ->orWhere('first_name', 'like', $search)
                                ->orWhere('last_name', 'like', $search)
                                ->orWhere('email', 'like', $search);
                        })->orWhereHas('studentProfile', function (Builder $profileQuery) use ($search) {
                            $profileQuery->where('student_id', 'like', $search)
                                ->orWhere('course', 'like', $search);
                        });
                    })->orWhereHas('companySupervisor', function (Builder $supervisorQuery) use ($search) {
                        $supervisorQuery->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    });
                });
            })
            ->when($this->ojt_type !== '', fn (Builder $query) => $query->where('ojt_type', $this->ojt_type))
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status));

        return [
            'total' => (clone $query)->count(),
            'internal' => (clone $query)->where('ojt_type', 'internal')->count(),
            'external' => (clone $query)->where('ojt_type', 'external')->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
        ];
    }

    #[Computed]
    public function assignableStudents()
    {
        return User::query()
            ->with(['studentProfile', 'ojtAssignment'])
            ->where('coordinator_id', Auth::id())
            ->role('student')
            ->orderBy('first_name')
            ->get();
    }
};
