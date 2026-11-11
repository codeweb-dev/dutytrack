<?php

use App\Models\AttendanceLog;
use App\Models\User;
use App\Support\Holidays;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Attendance Monitoring')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Coordinator Workspace', 'href' => '#'],
        ['label' => 'Monitoring', 'href' => '#'],
        ['label' => 'Attendance'],
    ];

    public string $title = 'Attendance Monitoring';

    public string $description = 'Track attendance logs for the students assigned to your coordinator account.';

    public string $status = '';

    #[Locked]
    public string $calendarMonth;

    #[Locked]
    public ?string $selectedCalendarDate = null;

    public function mount(): void
    {
        $this->calendarMonth = now()->format('Y-m');
    }

    public function previousCalendarMonth(): void
    {
        $this->calendarMonth = Carbon::createFromFormat('!Y-m', $this->calendarMonth)
            ->subMonthNoOverflow()
            ->format('Y-m');
    }

    public function nextCalendarMonth(): void
    {
        $this->calendarMonth = Carbon::createFromFormat('!Y-m', $this->calendarMonth)
            ->addMonthNoOverflow()
            ->format('Y-m');
    }

    public function showAttendanceDay(mixed $date): void
    {
        if (! is_string($date) || ! preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $date)) {
            return;
        }

        try {
            $selectedDate = Carbon::createFromFormat('!Y-m-d', $date);
        } catch (\Throwable) {
            return;
        }

        if (! $selectedDate || $selectedDate->format('Y-m-d') !== $date) {
            return;
        }

        $this->selectedCalendarDate = $date;
        $this->resetPage();

        Flux::modal('attendance-day-details')->show();
    }

    protected function attendanceQuery(): Builder
    {
        return AttendanceLog::query()
            ->whereHas('student', fn (Builder $query) => $query->where('coordinator_id', Auth::id()));
    }

    // Same status rules as the coordinator attendance page.
    protected function applyStatus(Builder $query): Builder
    {
        return match ($this->status) {
            'incomplete' => $query->where(fn (Builder $query) => $query
                ->whereNull('afternoon_time_out')
                ->orWhere('status', 'incomplete')),
            'late' => $query->where(fn (Builder $query) => $query
                ->where('status', 'late')
                ->orWhereTime('morning_time_in', '>', '08:00:00')),
            'present', 'absent' => $query->where('status', $this->status),
            default => $query,
        };
    }

    public function badgeColor(?string $status): string
    {
        return match ($status) {
            'present' => 'lime',
            'late' => 'amber',
            'absent' => 'red',
            'incomplete' => 'orange',
            default => 'zinc',
        };
    }

    #[Computed]
    public function calendar(): array
    {
        $month = Carbon::createFromFormat('!Y-m', $this->calendarMonth);
        $start = $month->copy()->startOfWeek(Carbon::MONDAY);
        $end = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $counts = $this->applyStatus($this->attendanceQuery())
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('DATE(attendance_date) as day_key, status, COUNT(*) as total')
            ->groupByRaw('DATE(attendance_date), status')
            ->get()
            ->groupBy('day_key')
            ->map(fn ($rows) => $rows->pluck('total', 'status')->map(fn ($total) => (int) $total)->all());
        $holidaysByDate = collect($this->holidays)->keyBy('date');

        $days = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $key = $date->toDateString();
            $days[] = [
                'date' => $date->copy(),
                'is_current_month' => $date->format('Y-m') === $month->format('Y-m'),
                'is_today' => $date->isToday(),
                'is_weekend' => $date->isWeekend(),
                'holiday' => $holidaysByDate->get($key)['name'] ?? null,
                'statuses' => $counts[$key] ?? [],
                'count' => array_sum($counts[$key] ?? []),
            ];
        }

        return ['month' => $month, 'days' => $days];
    }

    #[Computed]
    public function summary(): array
    {
        $query = $this->attendanceQuery();

        return [
            'total' => (clone $query)->count(),
            'present' => (clone $query)->where('status', 'present')->count(),
            'late' => (clone $query)->where(fn (Builder $query) => $query
                ->where('status', 'late')
                ->orWhereTime('morning_time_in', '>', '08:00:00'))
                ->count(),
            'absent' => (clone $query)->where('status', 'absent')->count(),
            'incomplete' => (clone $query)->where(fn (Builder $query) => $query
                ->whereNull('afternoon_time_out')
                ->orWhere('status', 'incomplete'))
                ->count(),
            'hours' => (float) (clone $query)->sum('hours_rendered'),
        ];
    }

    #[Computed]
    public function selectedAttendance(): LengthAwarePaginator
    {
        if (! $this->selectedCalendarDate) {
            return new LengthAwarePaginator([], 0, 10);
        }

        return $this->applyStatus($this->attendanceQuery())
            ->with(['student.studentProfile', 'assignment.companySupervisor'])
            ->whereDate('attendance_date', $this->selectedCalendarDate)
            ->orderBy(User::select('name')->whereColumn('users.id', 'attendance_logs.student_id'))
            ->orderBy('attendance_logs.id')
            ->paginate(10);
    }

    #[Computed]
    public function holidays(): array
    {
        return Holidays::forYear(Carbon::createFromFormat('!Y-m', $this->calendarMonth)->year)['holidays'];
    }

    #[Computed]
    public function holidaysAvailable(): bool
    {
        return Holidays::forYear(Carbon::createFromFormat('!Y-m', $this->calendarMonth)->year)['available'];
    }

    #[Computed]
    public function selectedHoliday(): ?array
    {
        if (! $this->selectedCalendarDate) {
            return null;
        }

        return collect(Holidays::forYear((int) substr($this->selectedCalendarDate, 0, 4))['holidays'])
            ->firstWhere('date', $this->selectedCalendarDate);
    }
};
?>
