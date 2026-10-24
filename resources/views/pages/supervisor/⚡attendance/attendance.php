<?php

use App\Models\AttendanceLog;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Attendance')] class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Supervisor Workspace', 'href' => '#'],
        ['label' => 'Attendance'],
    ];

    public string $title = 'Attendance';

    public string $description = 'Review attendance recorded by the students assigned to your supervision.';

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

        Flux::modal('attendance-day-details')->show();
    }

    protected function attendanceQuery(): Builder
    {
        return AttendanceLog::query()
            ->whereHas('assignment', fn (Builder $query) => $query->where('company_supervisor_id', Auth::id()));
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
        $counts = $this->attendanceQuery()
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('DATE(attendance_date) as day_key, COUNT(*) as total')
            ->groupByRaw('DATE(attendance_date)')
            ->pluck('total', 'day_key');

        $days = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $key = $date->toDateString();
            $days[] = [
                'date' => $date->copy(),
                'is_current_month' => $date->format('Y-m') === $month->format('Y-m'),
                'is_today' => $date->isToday(),
                'count' => (int) ($counts[$key] ?? 0),
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
            'incomplete' => (clone $query)->whereNull('afternoon_time_out')->count(),
            'hours' => (float) (clone $query)->sum('hours_rendered'),
        ];
    }

    #[Computed]
    public function selectedAttendance()
    {
        if (! $this->selectedCalendarDate) {
            return collect();
        }

        return $this->attendanceQuery()
            ->with('student.studentProfile')
            ->whereDate('attendance_date', $this->selectedCalendarDate)
            ->orderBy('student_id')
            ->get()
            ->sortBy(fn (AttendanceLog $attendance) => $attendance->student?->name)
            ->values();
    }
};
?>
