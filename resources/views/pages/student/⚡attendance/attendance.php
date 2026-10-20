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

new #[Title('My Attendance')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Attendance'],
    ];

    public string $title = 'My Attendance';

    public string $description = 'Record your daily OJT attendance and review your attendance history.';

    public ?string $remarks = null;

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

    protected function calculateSessionMinutes(?CarbonInterface $timeIn, ?CarbonInterface $timeOut): int
    {
        if (! $timeIn || ! $timeOut) {
            return 0;
        }

        $countedStart = $timeIn->greaterThan($this->workdayStart($timeIn))
            ? $timeIn->copy()
            : $this->workdayStart($timeIn);

        $countedEnd = $this->overtime_enabled
            ? $timeOut->copy()
            : $timeOut->copy()->min($this->workdayEnd($timeIn));

        return $countedEnd->greaterThan($countedStart)
            ? max($countedStart->diffInMinutes($countedEnd), 0)
            : 0;
    }

    protected function calculateHours(AttendanceLog $attendance, CarbonInterface $afternoonTimeOut): float
    {
        $minutes = $this->calculateSessionMinutes($attendance->displayMorningTimeIn(), $attendance->morning_time_out)
            + $this->calculateSessionMinutes($attendance->afternoon_time_in, $afternoonTimeOut);

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

    public function nextPunch(): ?string
    {
        $attendance = $this->todayAttendance;

        if (! $attendance) {
            return 'morning_time_in';
        }

        if ($attendance->isComplete()) {
            return null;
        }

        if (! $attendance->morning_time_out) {
            return 'morning_time_out';
        }

        if (! $attendance->afternoon_time_in) {
            return 'afternoon_time_in';
        }

        if (! $attendance->afternoon_time_out) {
            return 'afternoon_time_out';
        }

        return null;
    }

    public function nextPunchLabel(): string
    {
        return match ($this->nextPunch()) {
            'morning_time_in' => 'Morning Time In',
            'morning_time_out' => 'Morning Time Out',
            'afternoon_time_in' => 'Afternoon Time In',
            'afternoon_time_out' => 'Afternoon Time Out',
            default => 'Attendance Complete',
        };
    }

    public function nextPunchIcon(): string
    {
        return match ($this->nextPunch()) {
            'morning_time_in', 'afternoon_time_in' => 'arrow-right-end-on-rectangle',
            'morning_time_out', 'afternoon_time_out' => 'arrow-left-end-on-rectangle',
            default => 'check-circle',
        };
    }

    public function recordNextPunch(): void
    {
        $nextPunch = $this->nextPunch();

        match ($nextPunch) {
            'morning_time_in' => $this->clockMorningIn(),
            'morning_time_out' => $this->clockMorningOut(),
            'afternoon_time_in' => $this->clockAfternoonIn(),
            'afternoon_time_out' => $this->clockAfternoonOut(),
            default => $this->attendanceAlreadyComplete(),
        };
    }

    public function clockIn(): void
    {
        $this->clockMorningIn();
    }

    public function clockOut(): void
    {
        $nextPunch = $this->nextPunch();

        if ($nextPunch === 'morning_time_out') {
            $this->clockMorningOut();

            return;
        }

        if ($nextPunch === 'afternoon_time_out') {
            $this->clockAfternoonOut();

            return;
        }

        Flux::toast(variant: 'warning', heading: 'Time in needed.', text: 'Please record the matching time in before clocking out.');
    }

    protected function clockMorningIn(): void
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
            'morning_time_in' => $timeIn,
            'time_in_latitude' => $this->time_in_latitude,
            'time_in_longitude' => $this->time_in_longitude,
            'time_in_location_accuracy' => $this->time_in_location_accuracy,
            'hours_rendered' => 0,
            'overtime_enabled' => false,
            'status' => $this->clockInStatus($timeIn),
            'remarks' => $this->remarks,
        ]);

        $this->clearFormState();

        Flux::toast(variant: 'success', heading: 'Morning time in recorded.', text: 'Your morning time in has been saved.');
    }

    protected function clockMorningOut(): void
    {
        $this->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $attendance = $this->todayAttendance;

        if (! $attendance) {
            Flux::toast(variant: 'warning', heading: 'No time in found.', text: 'Please clock in before clocking out.');

            return;
        }

        if ($attendance->morning_time_out) {
            Flux::toast(variant: 'warning', heading: 'Morning out already recorded.', text: 'Your morning time out has already been saved.');

            return;
        }

        $attendance->update([
            'morning_time_out' => now(),
            'remarks' => $this->remarks ?: $attendance->remarks,
        ]);

        $this->clearFormState();

        Flux::toast(variant: 'success', heading: 'Morning time out recorded.', text: 'Your morning session has been saved.');
    }

    protected function clockAfternoonIn(): void
    {
        $this->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $attendance = $this->todayAttendance;

        if (! $attendance) {
            Flux::toast(variant: 'warning', heading: 'No morning time in found.', text: 'Please record morning time in first.');

            return;
        }

        if (! $attendance->morning_time_out) {
            Flux::toast(variant: 'warning', heading: 'Morning out needed.', text: 'Please record morning time out before afternoon time in.');

            return;
        }

        if ($attendance->afternoon_time_in) {
            Flux::toast(variant: 'warning', heading: 'Afternoon in already recorded.', text: 'Your afternoon time in has already been saved.');

            return;
        }

        $attendance->update([
            'afternoon_time_in' => now(),
            'remarks' => $this->remarks ?: $attendance->remarks,
        ]);

        $this->clearFormState();

        Flux::toast(variant: 'success', heading: 'Afternoon time in recorded.', text: 'Your afternoon time in has been saved.');
    }

    protected function clockAfternoonOut(): void
    {
        $this->validate([
            'overtime_enabled' => ['boolean'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $attendance = $this->todayAttendance;

        if (! $attendance) {
            Flux::toast(variant: 'warning', heading: 'No morning time in found.', text: 'Please record morning time in first.');

            return;
        }

        if (! $attendance->afternoon_time_in) {
            Flux::toast(variant: 'warning', heading: 'Afternoon in needed.', text: 'Please record afternoon time in before afternoon time out.');

            return;
        }

        if ($attendance->afternoon_time_out) {
            $this->attendanceAlreadyComplete();

            return;
        }

        $timeOut = now();
        $timeIn = $attendance->displayMorningTimeIn();
        $hours = $this->calculateHours($attendance, $timeOut);

        $attendance->update([
            'afternoon_time_out' => $timeOut,
            'hours_rendered' => $hours,
            'overtime_enabled' => $this->overtime_enabled,
            'status' => $this->completedStatus($timeIn),
            'remarks' => $this->remarks ?: $attendance->remarks,
        ]);

        $this->clearFormState();

        Flux::toast(variant: 'success', heading: 'Afternoon time out recorded.', text: 'Your rendered hours have been calculated.');
    }

    protected function attendanceAlreadyComplete(): void
    {
        Flux::toast(variant: 'warning', heading: 'Attendance complete.', text: 'Today already has a completed attendance record.');
    }

    protected function clearFormState(): void
    {
        $this->remarks = null;
        $this->time_in_latitude = null;
        $this->time_in_longitude = null;
        $this->time_in_location_accuracy = null;
        $this->overtime_enabled = false;
        $this->resetPage();
        $this->resetAttendanceState();
    }

    public function bypassAttendance(int $minutes): void
    {
        if (! in_array($minutes, [60, 480], true)) {
            abort(404);
        }

        $assignment = $this->assignment;

        if (! $assignment) {
            Flux::toast(variant: 'warning', heading: 'No assignment yet.', text: 'You need an OJT assignment before creating attendance.');

            return;
        }

        $attendance = $this->todayAttendance;

        if ($attendance?->isComplete()) {
            Flux::toast(variant: 'warning', heading: 'Attendance complete.', text: 'Today already has a completed attendance record.');

            return;
        }

        if ($minutes === 480) {
            $timeIn = now()->setTime(7, 0);
            $morningTimeOut = now()->setTime(12, 0);
            $afternoonTimeIn = now()->setTime(13, 0);
            $timeOut = now()->setTime(16, 0);
        } else {
            $timeOut = now();
            $timeIn = $timeOut->copy()->subMinutes($minutes);
            $morningTimeOut = $timeOut->copy();
            $afternoonTimeIn = $timeOut->copy();
        }

        $hours = round($minutes / 60, 2);

        if (! $attendance) {
            $attendance = AttendanceLog::query()->create([
                'student_id' => $this->studentId(),
                'ojt_assignment_id' => $assignment->id,
                'attendance_date' => $this->today(),
                'morning_time_in' => $timeIn,
                'time_in_latitude' => $this->time_in_latitude,
                'time_in_longitude' => $this->time_in_longitude,
                'time_in_location_accuracy' => $this->time_in_location_accuracy,
                'hours_rendered' => 0,
                'overtime_enabled' => true,
                'status' => $this->clockInStatus($timeIn),
                'remarks' => $this->remarks,
            ]);
        }

        $attendance->update([
            'morning_time_in' => $timeIn,
            'morning_time_out' => $morningTimeOut,
            'afternoon_time_in' => $afternoonTimeIn,
            'afternoon_time_out' => $timeOut,
            'hours_rendered' => $hours,
            'overtime_enabled' => true,
            'status' => $this->completedStatus($timeIn),
            'remarks' => $this->remarks ?: $attendance->remarks,
        ]);

        $this->clearFormState();

        Flux::toast(variant: 'success', heading: 'Attendance bypassed.', text: "{$minutes} minutes were logged for approval.");
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
            ->with(['companySupervisor'])
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
            'incomplete' => (clone $query)->whereNull('afternoon_time_out')->count(),
            'hours' => (float) (clone $query)->sum('hours_rendered'),
        ];
    }
};
