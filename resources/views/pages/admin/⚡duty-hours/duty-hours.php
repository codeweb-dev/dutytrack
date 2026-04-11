<?php

use App\Models\DutyHourLog;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Duty Hours Tracking')] class extends Component {
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Monitoring', 'href' => '#'],
        ['label' => 'Duty Hours Tracking'],
    ];

    public string $title = 'Duty Hours Tracking';
    public string $description = 'Track student duty hours, view daily logs, and generate duty hours reports.';

    public ?string $from_date = null;
    public ?string $to_date = null;
    public string $search = '';
    public string $ojt_type = '';
    public string $supervisor_id = '';
    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->dutyHourLogs);
    }

    public function updatedOjtType(): void
    {
        $this->resetPage();
        unset($this->dutyHourLogs, $this->summary);
    }

    public function updatedSupervisorId(): void
    {
        $this->resetPage();
        unset($this->dutyHourLogs, $this->summary);
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        unset($this->dutyHourLogs, $this->summary);
    }

    public function updatedFromDate(): void
    {
        $this->resetPage();
        unset($this->dutyHourLogs, $this->summary);
    }

    public function updatedToDate(): void
    {
        $this->resetPage();
        unset($this->dutyHourLogs, $this->summary);
    }

    public function clearFilters(): void
    {
        $this->from_date = null;
        $this->to_date = null;
        $this->search = '';
        $this->ojt_type = '';
        $this->supervisor_id = '';
        $this->status = '';

        $this->resetPage();
        unset($this->dutyHourLogs, $this->summary);
    }

    public function badgeColor(string $status): string
    {
        return match ($status) {
            'approved' => 'lime',
            'pending' => 'amber',
            'rejected' => 'red',
            default => 'zinc',
        };
    }

    #[Computed]
    public function dutyHourLogs(): LengthAwarePaginator
    {
        return DutyHourLog::query()
            ->with([
                'student',
                'assignment.professor',
                'assignment.companySupervisor',
            ])
            ->when($this->search !== '', function ($query) {
                $search = '%' . $this->search . '%';

                $query->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery->where(function ($nested) use ($search) {
                        $nested->where('first_name', 'like', $search)
                            ->orWhere('last_name', 'like', $search)
                            ->orWhere('name', 'like', $search)
                            ->orWhere('student_id', 'like', $search)
                            ->orWhere('email', 'like', $search);
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
                    $assignmentQuery
                        ->where('professor_id', $this->supervisor_id)
                        ->orWhere('company_supervisor_id', $this->supervisor_id);
                });
            })
            ->when($this->status !== '', fn($query) => $query->where('status', $this->status))
            ->when($this->from_date, fn($query) => $query->whereDate('log_date', '>=', $this->from_date))
            ->when($this->to_date, fn($query) => $query->whereDate('log_date', '<=', $this->to_date))
            ->latest('log_date')
            ->latest('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function summary(): array
    {
        $query = DutyHourLog::query()
            ->when($this->search !== '', function ($query) {
                $search = '%' . $this->search . '%';

                $query->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery->where(function ($nested) use ($search) {
                        $nested->where('first_name', 'like', $search)
                            ->orWhere('last_name', 'like', $search)
                            ->orWhere('name', 'like', $search)
                            ->orWhere('student_id', 'like', $search)
                            ->orWhere('email', 'like', $search);
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
                    $assignmentQuery
                        ->where('professor_id', $this->supervisor_id)
                        ->orWhere('company_supervisor_id', $this->supervisor_id);
                });
            })
            ->when($this->status !== '', fn($query) => $query->where('status', $this->status))
            ->when($this->from_date, fn($query) => $query->whereDate('log_date', '>=', $this->from_date))
            ->when($this->to_date, fn($query) => $query->whereDate('log_date', '<=', $this->to_date));

        return [
            'rendered_hours' => (float) (clone $query)->sum('hours_rendered'),
            'approved_hours' => (float) (clone $query)->sum('approved_hours'),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
            'approved_count' => (clone $query)->where('status', 'approved')->count(),
            'rejected_count' => (clone $query)->where('status', 'rejected')->count(),
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
