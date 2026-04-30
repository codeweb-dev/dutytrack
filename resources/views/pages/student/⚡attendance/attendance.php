<?php

use App\Models\AttendanceLog;
use App\Models\OjtAssignment;
use Carbon\CarbonInterface;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('My Attendance')] class extends Component {
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'My Attendance'],
    ];

    public string $title = 'My Attendance';
    public string $description = 'Record your daily OJT attendance and review your attendance history.';

    public int $break_minutes = 0;
    public ?string $remarks = null;

    protected function studentId(): int
    {
        return (int) Auth::id();
    }

    protected function today(): string
    {
        return now()->toDateString();
    }

    protected function resetAttendanceState(): void
    {
        unset($this->todayAttendance, $this->attendanceLogs, $this->summary);
    }

    protected function calculateHours(CarbonInterface $timeIn, CarbonInterface $timeOut, int $breakMinutes): float
    {
        $minutes = max($timeIn->diffInMinutes($timeOut) - $breakMinutes, 0);

        return round($minutes / 60, 2);
    }

    public function clockIn(): void
    {
        $assignment = $this->assignment;

        if (! $assignment) {
            Flux::toast(variant: 'warning', heading: 'No assignment yet.', text: 'You need an OJT assignment before creating attendance.');

            return;
        }

        if ($this->todayAttendance) {
            Flux::toast(variant: 'warning', heading: 'Already clocked in.', text: 'You already have an attendance record for today.');

            return;
        }

        AttendanceLog::create([
            'student_id' => $this->studentId(),
            'ojt_assignment_id' => $assignment->id,
            'attendance_date' => $this->today(),
            'time_in' => now(),
            'break_minutes' => 0,
            'hours_rendered' => 0,
            'status' => 'incomplete',
            'source' => 'student',
            'remarks' => $this->remarks,
        ]);

        $this->remarks = null;
        $this->resetPage();
        $this->resetAttendanceState();

        Flux::toast(variant: 'success', heading: 'Clocked in.', text: 'Your attendance time in has been recorded.');
    }

    public function clockOut(): void
    {
        $this->validate([
            'break_minutes' => ['required', 'integer', 'min:0', 'max:720'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $attendance = $this->todayAttendance;

        if (! $attendance) {
            Flux::toast(variant: 'warning', heading: 'No time in found.', text: 'Please clock in before clocking out.');

            return;
        }

        if ($attendance->time_out) {
            Flux::toast(variant: 'warning', heading: 'Already clocked out.', text: 'Your attendance for today is already complete.');

            return;
        }

        $timeOut = now();
        $timeIn = $attendance->time_in instanceof Carbon ? $attendance->time_in : Carbon::parse($attendance->time_in);
        $hours = $this->calculateHours($timeIn, $timeOut, $this->break_minutes);

        $attendance->update([
            'time_out' => $timeOut,
            'break_minutes' => $this->break_minutes,
            'hours_rendered' => $hours,
            'status' => 'present',
            'remarks' => $this->remarks ?: $attendance->remarks,
        ]);

        $this->break_minutes = 0;
        $this->remarks = null;
        $this->resetPage();
        $this->resetAttendanceState();

        Flux::toast(variant: 'success', heading: 'Clocked out.', text: 'Your rendered hours have been calculated.');
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
    public function assignment(): ?OjtAssignment
    {
        return OjtAssignment::query()
            ->with(['professor', 'companySupervisor'])
            ->where('student_id', $this->studentId())
            ->latest()
            ->first();
    }

    #[Computed]
    public function todayAttendance(): ?AttendanceLog
    {
        return AttendanceLog::query()
            ->where('student_id', $this->studentId())
            ->whereDate('attendance_date', $this->today())
            ->first();
    }

    #[Computed]
    public function attendanceLogs(): LengthAwarePaginator
    {
        return AttendanceLog::query()
            ->where('student_id', $this->studentId())
            ->latest('attendance_date')
            ->latest('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function summary(): array
    {
        $query = AttendanceLog::query()->where('student_id', $this->studentId());

        return [
            'total' => (clone $query)->count(),
            'present' => (clone $query)->where('status', 'present')->count(),
            'incomplete' => (clone $query)->where('status', 'incomplete')->count(),
            'hours' => (float) (clone $query)->sum('hours_rendered'),
        ];
    }
};
