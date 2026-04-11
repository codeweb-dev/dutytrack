<?php

use App\Models\DutyHourLog;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Duty Hours Reports')] class extends Component {
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Reports', 'href' => '#'],
        ['label' => 'Duty Hours Reports'],
    ];

    public string $title = 'Duty Hours Reports';
    public string $description = 'Preview and filter student duty hour reports.';

    public string $search = '';
    public string $ojt_type = '';
    public string $status = '';
    public string $supervisor_id = '';
    public ?string $from_date = null;
    public ?string $to_date = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->dutyHourReports);
    }
    public function updatedOjtType(): void
    {
        $this->resetPage();
        unset($this->dutyHourReports, $this->summary);
    }
    public function updatedStatus(): void
    {
        $this->resetPage();
        unset($this->dutyHourReports, $this->summary);
    }
    public function updatedSupervisorId(): void
    {
        $this->resetPage();
        unset($this->dutyHourReports, $this->summary);
    }
    public function updatedFromDate(): void
    {
        $this->resetPage();
        unset($this->dutyHourReports, $this->summary);
    }
    public function updatedToDate(): void
    {
        $this->resetPage();
        unset($this->dutyHourReports, $this->summary);
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
        unset($this->dutyHourReports, $this->summary);
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
    public function dutyHourReports(): LengthAwarePaginator
    {
        return DutyHourLog::query()
            ->with(['student', 'assignment.professor', 'assignment.companySupervisor'])
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
                $query->whereHas('assignment', fn($assignmentQuery) => $assignmentQuery->where('ojt_type', $this->ojt_type));
            })
            ->when($this->supervisor_id !== '', function ($query) {
                $query->whereHas('assignment', function ($assignmentQuery) {
                    $assignmentQuery->where('professor_id', $this->supervisor_id)
                        ->orWhere('company_supervisor_id', $this->supervisor_id);
                });
            })
            ->when($this->status !== '', fn($query) => $query->where('status', $this->status))
            ->when($this->from_date, fn($query) => $query->whereDate('log_date', '>=', $this->from_date))
            ->when($this->to_date, fn($query) => $query->whereDate('log_date', '<=', $this->to_date))
            ->latest('log_date')
            ->paginate(10);
    }

    #[Computed]
    public function summary(): array
    {
        $query = DutyHourLog::query()
            ->when($this->ojt_type !== '', function ($query) {
                $query->whereHas('assignment', fn($assignmentQuery) => $assignmentQuery->where('ojt_type', $this->ojt_type));
            })
            ->when($this->supervisor_id !== '', function ($query) {
                $query->whereHas('assignment', function ($assignmentQuery) {
                    $assignmentQuery->where('professor_id', $this->supervisor_id)
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
