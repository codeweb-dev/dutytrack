<?php

use App\Models\DutyHourLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Duty Hours Monitoring')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Coordinator Workspace', 'href' => '#'],
        ['label' => 'Monitoring', 'href' => '#'],
        ['label' => 'Duty Hours'],
    ];

    public string $title = 'Duty Hours Monitoring';

    public string $description = 'Track duty hour records for all students.';

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

    protected function baseDutyHourQuery(): Builder
    {
        return DutyHourLog::query()
            ->with(['student.studentProfile', 'assignment.professor', 'assignment.companySupervisor']);
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
                                ->orWhere('course', 'like', $search)
                                ->orWhere('year_level', 'like', $search)
                                ->orWhere('section', 'like', $search);
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
                        ->where('professor_id', $this->supervisor_id)
                        ->orWhere('company_supervisor_id', $this->supervisor_id);
                });
            })
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->from_date, fn (Builder $query) => $query->whereDate('log_date', '>=', $this->from_date))
            ->when($this->to_date, fn (Builder $query) => $query->whereDate('log_date', '<=', $this->to_date));
    }

    #[Computed]
    public function dutyHourLogs(): LengthAwarePaginator
    {
        return $this->applyFilters($this->baseDutyHourQuery())
            ->latest('log_date')
            ->latest('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function summary(): array
    {
        $query = $this->applyFilters($this->baseDutyHourQuery(), includeSearch: false);

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
            ->where(function (Builder $query) {
                $query
                    ->whereHas('professorAssignments')
                    ->orWhereHas('companySupervisorAssignments');
            })
            ->orderBy('name')
            ->get();
    }
};
