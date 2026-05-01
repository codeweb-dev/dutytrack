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
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('My Attendance')] class extends Component
{
    use WithFileUploads;
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'My Attendance'],
    ];

    public string $title = 'My Attendance';

    public string $description = 'Record your daily OJT attendance and review your attendance history.';

    public int $break_minutes = 0;

    public ?string $remarks = null;

    public $selfie = null;

    public ?float $time_in_latitude = null;

    public ?float $time_in_longitude = null;

    public ?int $time_in_location_accuracy = null;

    public bool $overtime_enabled = false;

    public function mount(): void
    {
        $this->overtime_enabled = (bool) ($this->todayAttendance?->overtime_enabled ?? false);
    }

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
        $countedStart = $timeIn->greaterThan($this->workdayStart($timeIn))
            ? $timeIn->copy()
            : $this->workdayStart($timeIn);

        $countedEnd = $this->overtime_enabled
            ? $timeOut->copy()
            : $timeOut->copy()->min($this->workdayEnd($timeIn));

        $minutes = $countedEnd->greaterThan($countedStart)
            ? max($countedStart->diffInMinutes($countedEnd) - $breakMinutes, 0)
            : 0;

        return round($minutes / 60, 2);
    }

    protected function workdayStart(CarbonInterface $date): Carbon
    {
        return Carbon::instance($date->copy())->setTime(7, 0);
    }

    protected function workdayEnd(CarbonInterface $date): Carbon
    {
        return Carbon::instance($date->copy())->setTime(17, 0);
    }

    protected function clockInStatus(CarbonInterface $timeIn): string
    {
        return $timeIn->greaterThan($this->workdayStart($timeIn)) ? 'late' : 'incomplete';
    }

    protected function completedStatus(CarbonInterface $timeIn): string
    {
        return $timeIn->greaterThan($this->workdayStart($timeIn)) ? 'late' : 'present';
    }

    public function mapUrl(?float $latitude = null, ?float $longitude = null): ?string
    {
        if ($latitude === null || $longitude === null) {
            return null;
        }

        return sprintf(
            'https://www.openstreetmap.org/?mlat=%s&mlon=%s#map=17/%s/%s',
            $latitude,
            $longitude,
            $latitude,
            $longitude,
        );
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

        $this->validate([
            'selfie' => ['required', 'image', 'max:4096'],
            'time_in_latitude' => ['required', 'numeric', 'between:-90,90'],
            'time_in_longitude' => ['required', 'numeric', 'between:-180,180'],
            'time_in_location_accuracy' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $timeIn = now();

        AttendanceLog::create([
            'student_id' => $this->studentId(),
            'ojt_assignment_id' => $assignment->id,
            'attendance_date' => $this->today(),
            'time_in' => $timeIn,
            'time_in_latitude' => $this->time_in_latitude,
            'time_in_longitude' => $this->time_in_longitude,
            'time_in_location_accuracy' => $this->time_in_location_accuracy,
            'time_in_selfie_path' => $this->selfie->store('attendance/selfies', 'public'),
            'break_minutes' => 0,
            'hours_rendered' => 0,
            'overtime_enabled' => false,
            'status' => $this->clockInStatus($timeIn),
            'source' => 'student',
            'remarks' => $this->remarks,
        ]);

        $this->remarks = null;
        $this->selfie = null;
        $this->time_in_latitude = null;
        $this->time_in_longitude = null;
        $this->time_in_location_accuracy = null;
        $this->overtime_enabled = false;
        $this->resetPage();
        $this->resetAttendanceState();

        Flux::toast(variant: 'success', heading: 'Clocked in.', text: 'Your attendance time in has been recorded.');
    }

    public function clockOut(): void
    {
        $this->validate([
            'break_minutes' => ['required', 'integer', 'min:0', 'max:720'],
            'overtime_enabled' => ['boolean'],
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
            'overtime_enabled' => $this->overtime_enabled,
            'status' => $this->completedStatus($timeIn),
            'remarks' => $this->remarks ?: $attendance->remarks,
        ]);

        $this->break_minutes = 0;
        $this->remarks = null;
        $this->overtime_enabled = false;
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
            'incomplete' => (clone $query)->whereNull('time_out')->count(),
            'hours' => (float) (clone $query)->sum('hours_rendered'),
        ];
    }
};
