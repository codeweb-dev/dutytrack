<?php

use App\Models\DutyHourLog;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Duty Hours')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Professor Workspace', 'href' => '#'],
        ['label' => 'Duty Hours'],
    ];

    public string $title = 'Duty Hours';

    public string $description = 'Review and approve duty hour records submitted by your assigned students.';

    public ?string $from_date = null;

    public ?string $to_date = null;

    public string $search = '';

    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->dutyHourLogs);
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
            ->whereHas('assignment', fn (Builder $query) => $query->where('professor_id', Auth::id()));
    }

    protected function filteredDutyHourQuery(): Builder
    {
        return $this->baseDutyHourQuery()
            ->with(['student.studentProfile', 'assignment.companySupervisor', 'approver', 'attendance'])
            ->when($this->search !== '', function (Builder $query) {
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
                                ->orWhere('section', 'like', $search);
                        });
                });
            })
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->from_date, fn (Builder $query) => $query->whereDate('log_date', '>=', $this->from_date))
            ->when($this->to_date, fn (Builder $query) => $query->whereDate('log_date', '<=', $this->to_date));
    }

    protected function assignedDutyHourLog(int $logId): DutyHourLog
    {
        return $this->baseDutyHourQuery()->whereKey($logId)->firstOrFail();
    }

    public function approveDutyHour(int $logId): void
    {
        $log = $this->assignedDutyHourLog($logId);

        $log->update([
            'approved_hours' => $log->hours_rendered,
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        unset($this->dutyHourLogs, $this->summary);

        Flux::toast(variant: 'success', heading: 'Duty hours approved.', text: 'The student hours were added to approved duty hours.');
    }

    public function rejectDutyHour(int $logId): void
    {
        $log = $this->assignedDutyHourLog($logId);

        $log->update([
            'approved_hours' => 0,
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        unset($this->dutyHourLogs, $this->summary);

        Flux::toast(variant: 'success', heading: 'Duty hours rejected.', text: 'The pending duty hour log was rejected.');
    }

    #[Computed]
    public function dutyHourLogs(): LengthAwarePaginator
    {
        return $this->filteredDutyHourQuery()
            ->latest('log_date')
            ->latest('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function summary(): array
    {
        $query = $this->filteredDutyHourQuery();

        return [
            'rendered_hours' => (float) (clone $query)->sum('hours_rendered'),
            'approved_hours' => (float) (clone $query)->sum('approved_hours'),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
            'approved_count' => (clone $query)->where('status', 'approved')->count(),
            'rejected_count' => (clone $query)->where('status', 'rejected')->count(),
        ];
    }
};
