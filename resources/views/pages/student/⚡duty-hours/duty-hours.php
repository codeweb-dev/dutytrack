<?php

use App\Models\DutyHourLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('My Duty Hours')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'My Duty Hours'],
    ];

    public string $title = 'My Duty Hours';

    public string $description = 'Track your rendered, pending, approved, and rejected duty hour records.';

    public ?string $from_date = null;

    public ?string $to_date = null;

    public string $status = '';

    protected function studentId(): int
    {
        return (int) Auth::id();
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

    protected function filteredDutyHourQuery()
    {
        return DutyHourLog::query()
            ->with(['assignment.professor', 'assignment.companySupervisor', 'approver', 'attendance'])
            ->where('student_id', $this->studentId())
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->from_date, fn ($query) => $query->whereDate('log_date', '>=', $this->from_date))
            ->when($this->to_date, fn ($query) => $query->whereDate('log_date', '<=', $this->to_date));
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
