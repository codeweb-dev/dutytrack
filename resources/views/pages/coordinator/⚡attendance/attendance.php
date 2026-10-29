<?php

use App\Livewire\Concerns\WithSorting;
use App\Models\StudentProfile;
use App\Models\AttendanceLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Attendance Monitoring')] class extends Component
{
    use WithPagination;
    use WithSorting;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Coordinator Workspace', 'href' => '#'],
        ['label' => 'Monitoring', 'href' => '#'],
        ['label' => 'Attendance'],
    ];

    public string $title = 'Attendance Monitoring';

    public string $description = 'Track attendance logs for the students assigned to your coordinator account.';

    public string $search = '';

    public string $ojt_type = '';

    public string $status = '';

    public string $supervisor_id = '';

    public ?string $from_date = null;

    public ?string $to_date = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->attendanceLogs);
    }

    public function updatedOjtType(): void
    {
        $this->resetPage();
        unset($this->attendanceLogs, $this->summary);
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        unset($this->attendanceLogs, $this->summary);
    }

    public function updatedSupervisorId(): void
    {
        $this->resetPage();
        unset($this->attendanceLogs);
    }

    public function updatedFromDate(): void
    {
        $this->resetPage();
        unset($this->attendanceLogs, $this->summary);
    }

    public function updatedToDate(): void
    {
        $this->resetPage();
        unset($this->attendanceLogs, $this->summary);
    }

    public function clearFilters(): void
    {
        $this->reset('sortBy', 'sortDirection');
        $this->search = '';
        $this->ojt_type = '';
        $this->status = '';
        $this->supervisor_id = '';
        $this->from_date = null;
        $this->to_date = null;

        $this->resetPage();
        unset($this->attendanceLogs, $this->summary);
    }

    public function badgeColor(?string $status): string
    {
        return match ($status) {
            'present' => 'lime',
            'late' => 'amber',
            'absent' => 'red',
            'incomplete' => 'zinc',
            default => 'zinc',
        };
    }

    protected function baseAttendanceQuery(): Builder
    {
        return AttendanceLog::query()
            ->with(['student.studentProfile', 'assignment.companySupervisor'])
            ->whereHas('student', function (Builder $query) {
                $query->where('coordinator_id', Auth::id());
            });
    }

    protected function applyFilters(Builder $query, bool $includeSearch = true): Builder
    {
        return $query
            ->when($includeSearch && $this->search !== '', function (Builder $query) {
                $search = '%'.$this->search.'%';

                $query->whereHas('student', function (Builder $studentQuery) use ($search) {
                    $studentQuery
                        ->where(function (Builder $nested) use ($search) {
                            $nested->where('users.name', 'like', $search)
                                ->orWhere('users.first_name', 'like', $search)
                                ->orWhere('users.last_name', 'like', $search)
                                ->orWhere('users.email', 'like', $search);
                        })
                        ->orWhereHas('studentProfile', function (Builder $profileQuery) use ($search) {
                            $profileQuery->where('student_id', 'like', $search)
                                ->orWhere('course', 'like', $search);
                        });
                });
            })
            ->when($this->ojt_type !== '', function (Builder $query) {
                $query->whereHas('assignment', function (Builder $assignmentQuery) {
                    $assignmentQuery->where('ojt_type', $this->ojt_type);
                });
            })
            ->when($this->supervisor_id !== '', function (Builder $query) {
                $query->whereHas('assignment', function (Builder $assignmentQuery) {
                    $assignmentQuery
                        ->where('company_supervisor_id', $this->supervisor_id);
                });
            })
            ->when($this->status !== '', function (Builder $query) {
                $this->status === 'incomplete'
                    ? $query->whereNull('afternoon_time_out')
                    : $query->where('status', $this->status);
            })
            ->when($this->from_date, fn (Builder $query) => $query->whereDate('attendance_date', '>=', $this->from_date))
            ->when($this->to_date, fn (Builder $query) => $query->whereDate('attendance_date', '<=', $this->to_date));
    }

    #[Computed]
    public function attendanceLogs(): LengthAwarePaginator
    {
        return $this->applySorting($this->applyFilters($this->baseAttendanceQuery()), [
            'student' => User::select('name')->whereColumn('users.id', 'attendance_logs.student_id'),
            'student_id' => StudentProfile::select('student_id')->whereColumn('student_profiles.user_id', 'attendance_logs.student_id'),
            'date' => 'attendance_date',
            'hours' => 'hours_rendered',
            'status' => 'status',
        ])
            ->latest('attendance_date')
            ->latest('morning_time_in')
            ->paginate(10);
    }

    #[Computed]
    public function summary(): array
    {
        $query = $this->applyFilters($this->baseAttendanceQuery(), includeSearch: false);

        return [
            'total' => (clone $query)->count(),
            'present' => (clone $query)->where('status', 'present')->count(),
            'late' => (clone $query)->where('status', 'late')->count(),
            'absent' => (clone $query)->where('status', 'absent')->count(),
            'incomplete' => (clone $query)->whereNull('afternoon_time_out')->count(),
        ];
    }

    #[Computed]
    public function supervisors()
    {
        return User::query()
            ->where(function (Builder $query) {
                $query
                    ->whereHas('companySupervisorAssignments.student', function (Builder $studentQuery) {
                        $studentQuery->where('coordinator_id', Auth::id());
                    });
            })
            ->orderBy('name')
            ->get();
    }
};
