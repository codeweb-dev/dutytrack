<?php

use App\Models\AttendanceLog;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Attendance Monitoring')] class extends Component {
    use WithPagination;

    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'Monitoring', 'href' => '#'], ['label' => 'Attendance Monitoring']];

    public string $title = 'Attendance Monitoring';
    public string $description = 'Track student attendance, view daily logs, and monitor attendance status.';

    public string $search = '';
    public string $ojt_type = '';
    public string $status = '';
    public string $supervisor_id = '';
    public ?string $from_date = null;
    public ?string $to_date = null;

    public ?AttendanceLog $selectedRecord = null;

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
        $this->search = '';
        $this->ojt_type = '';
        $this->status = '';
        $this->supervisor_id = '';
        $this->from_date = null;
        $this->to_date = null;

        $this->resetPage();
        unset($this->attendanceLogs, $this->summary);
    }

    public function badgeColor(string $status): string
    {
        return match ($status) {
            'present' => 'lime',
            'late' => 'amber',
            'absent' => 'red',
            'incomplete' => 'zinc',
            default => 'zinc',
        };
    }

    #[Computed]
    public function attendanceLogs(): LengthAwarePaginator
    {
        return AttendanceLog::query()
            ->with(['student', 'assignment.professor', 'assignment.companySupervisor', 'student.studentProfile'])
            ->when($this->search !== '', function ($query) {
                $search = '%' . $this->search . '%';

                $query->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery->where(function ($nested) use ($search) {
                        $nested->where('first_name', 'like', $search)->orWhere('last_name', 'like', $search)->orWhere('name', 'like', $search)->orWhere('student_id', 'like', $search)->orWhere('email', 'like', $search);
                    });
                });
            })
            ->when($this->ojt_type !== '', function ($query) {
                $query->whereHas('assignment', function ($assignmentQuery) {
                    $assignmentQuery->where('ojt_type', $this->ojt_type);
                });
            })
            ->when($this->supervisor_id !== '', function ($query) {
                $query->whereHas('assignment', function ($assignmentQuery) {
                    $assignmentQuery->where('professor_id', $this->supervisor_id)->orWhere('company_supervisor_id', $this->supervisor_id);
                });
            })
            ->when($this->status !== '', fn($query) => $query->where('status', $this->status))
            ->when($this->from_date, fn($query) => $query->whereDate('attendance_date', '>=', $this->from_date))
            ->when($this->to_date, fn($query) => $query->whereDate('attendance_date', '<=', $this->to_date))
            ->latest('attendance_date')
            ->latest('time_in')
            ->paginate(10);
    }

    #[Computed]
    public function summary(): array
    {
        $query = AttendanceLog::query()
            ->when($this->ojt_type !== '', function ($query) {
                $query->whereHas('assignment', function ($assignmentQuery) {
                    $assignmentQuery->where('ojt_type', $this->ojt_type);
                });
            })
            ->when($this->supervisor_id !== '', function ($query) {
                $query->whereHas('assignment', function ($assignmentQuery) {
                    $assignmentQuery->where('professor_id', $this->supervisor_id)->orWhere('company_supervisor_id', $this->supervisor_id);
                });
            })
            ->when($this->status !== '', fn($query) => $query->where('status', $this->status))
            ->when($this->from_date, fn($query) => $query->whereDate('attendance_date', '>=', $this->from_date))
            ->when($this->to_date, fn($query) => $query->whereDate('attendance_date', '<=', $this->to_date));

        return [
            'total' => (clone $query)->count(),
            'present' => (clone $query)->where('status', 'present')->count(),
            'late' => (clone $query)->where('status', 'late')->count(),
            'absent' => (clone $query)->where('status', 'absent')->count(),
            'incomplete' => (clone $query)->where('status', 'incomplete')->count(),
        ];
    }

    #[Computed]
    public function supervisors()
    {
        return User::query()
            ->role(['professor', 'company_supervisor'])
            ->orderBy('first_name')
            ->get();
    }
};
