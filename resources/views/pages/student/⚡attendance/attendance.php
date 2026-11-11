<?php

use App\Models\AttendanceLog;
use App\Models\OjtAssignment;
use App\Models\StudentProfile;
use App\Support\Holidays;
use Carbon\CarbonInterface;
use Flux\Flux;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('My Attendance')] class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Daily Logs', 'href' => '#'],
        ['label' => 'Attendance'],
    ];

    public string $title = 'My Attendance';

    public string $description = 'Record your daily OJT attendance and review your attendance history.';

    public ?float $time_in_latitude = null;

    public ?float $time_in_longitude = null;

    public ?int $time_in_location_accuracy = null;

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
        $this->calendarMonth = Carbon::createFromFormat('!Y-m', $this->calendarMonth)->subMonthNoOverflow()->format('Y-m');
    }

    public function nextCalendarMonth(): void
    {
        $this->calendarMonth = Carbon::createFromFormat('!Y-m', $this->calendarMonth)->addMonthNoOverflow()->format('Y-m');
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
        unset($this->selectedAttendance);

        Flux::modal('attendance-day-details')->show();
    }

    protected function studentId(): int
    {
        return (int) Auth::id();
    }

    protected function today(): string
    {
        return now()->toDateString();
    }

    // OJT rule: no duty on Saturdays and Sundays.
    public function isRestDay(): bool
    {
        return now()->isWeekend();
    }

    protected function restDayBlocked(): bool
    {
        if (! $this->isRestDay()) {
            return false;
        }

        Flux::toast(variant: 'warning', heading: 'Rest day.', text: 'Attendance cannot be recorded on Saturdays and Sundays.');

        return true;
    }

    protected function resetAttendanceState(): void
    {
        unset($this->todayAttendance, $this->calendar, $this->selectedAttendance, $this->summary);
    }

    protected function calculateSessionMinutes(?CarbonInterface $timeIn, ?CarbonInterface $timeOut, CarbonInterface $earliestStart, CarbonInterface $latestEnd): int
    {
        if (! $timeIn || ! $timeOut) {
            return 0;
        }

        // Sessions only count 8–12 and 1–5, so a day maxes out at 8 hours.
        $countedStart = $timeIn->copy()->max($earliestStart);

        $countedEnd = $timeOut->copy()->min($latestEnd);

        return $countedEnd->greaterThan($countedStart)
            ? max($countedStart->diffInMinutes($countedEnd), 0)
            : 0;
    }

    protected function calculateHours(AttendanceLog $attendance, CarbonInterface $afternoonTimeOut): float
    {
        $morningTimeIn = $attendance->displayMorningTimeIn();
        $afternoonTimeIn = $attendance->afternoon_time_in;
        $minutes = $this->calculateSessionMinutes(
            $morningTimeIn,
            $attendance->morning_time_out,
            $morningTimeIn ? $this->workdayStart($morningTimeIn) : now(),
            $morningTimeIn ? $this->morningCutoff($morningTimeIn) : now(),
        ) + $this->calculateSessionMinutes(
            $afternoonTimeIn,
            $afternoonTimeOut,
            $afternoonTimeIn ? $this->afternoonStart($afternoonTimeIn) : now(),
            $afternoonTimeIn ? $this->workdayEnd($afternoonTimeIn) : now(),
        );

        return round(min($minutes, 480) / 60, 2);
    }

    // Total rendered hours never exceed the course's required hours (BSCS 300, BSIT 500).
    protected function withinRequiredHours(AttendanceLog $attendance, float $hours): float
    {
        $required = (float) StudentProfile::query()->where('user_id', $this->studentId())->value('ojt_required_hours');
        $otherDays = (float) AttendanceLog::query()
            ->where('student_id', $this->studentId())
            ->whereKeyNot($attendance->id)
            ->sum('hours_rendered');

        return $required > 0 ? max(min($hours, $required - $otherDays), 0) : $hours;
    }

    protected function workdayStart(CarbonInterface $date): Carbon
    {
        return Carbon::instance($date->copy())->setTime(8, 0);
    }

    protected function morningCutoff(CarbonInterface $date): Carbon
    {
        return Carbon::instance($date->copy())->setTime(12, 0);
    }

    protected function afternoonStart(CarbonInterface $date): Carbon
    {
        return Carbon::instance($date->copy())->setTime(13, 0);
    }

    protected function workdayEnd(CarbonInterface $date): Carbon
    {
        return Carbon::instance($date->copy())->setTime(17, 0);
    }

    protected function clockInStatus(CarbonInterface $timeIn): string
    {
        return $timeIn->greaterThan($this->workdayStart($timeIn)) ? 'late' : 'incomplete';
    }

    protected function attendanceStatus(AttendanceLog $attendance, float $hours): string
    {
        if ($hours < 8) {
            return 'incomplete';
        }

        $morningTimeIn = $attendance->displayMorningTimeIn();

        return $morningTimeIn && $morningTimeIn->greaterThan($this->workdayStart($morningTimeIn))
            ? 'late'
            : 'present';
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
        return $this->nextPunchAt(now());
    }

    protected function nextPunchAt(CarbonInterface $now): ?string
    {
        $attendance = $this->todayAttendance;

        if (! $attendance) {
            if ($now->lessThan($this->morningCutoff($now))) {
                return 'morning_time_in';
            }

            return $now->greaterThanOrEqualTo($this->afternoonStart($now))
                && $now->lessThan($this->workdayEnd($now))
                ? 'afternoon_time_in'
                : null;
        }

        if ($attendance->isComplete()) {
            return null;
        }

        if (! $attendance->morning_time_in) {
            if ($attendance->morning_time_out) {
                return null;
            }

            if (! $attendance->afternoon_time_in) {
                return $now->greaterThanOrEqualTo($this->afternoonStart($now))
                    && $now->lessThan($this->workdayEnd($now))
                    ? 'afternoon_time_in'
                    : null;
            }

            return $now->greaterThanOrEqualTo($attendance->afternoon_time_in) ? 'afternoon_time_out' : null;
        }

        if (! $attendance->morning_time_out) {
            return $attendance->afternoon_time_in
                ? null
                : ($now->greaterThanOrEqualTo($attendance->morning_time_in) ? 'morning_time_out' : null);
        }

        if (! $attendance->afternoon_time_in) {
            return $now->greaterThanOrEqualTo($this->afternoonStart($now))
                && $now->lessThan($this->workdayEnd($now))
                && $now->greaterThanOrEqualTo($attendance->morning_time_out)
                ? 'afternoon_time_in'
                : null;
        }

        return $now->greaterThanOrEqualTo($attendance->afternoon_time_in) ? 'afternoon_time_out' : null;
    }

    public function nextPunchLabel(): string
    {
        return match ($this->nextPunch()) {
            'morning_time_in' => 'Morning Time In',
            'morning_time_out' => 'Morning Time Out',
            'afternoon_time_in' => 'Afternoon Time In',
            'afternoon_time_out' => 'Afternoon Time Out',
            default => $this->unavailablePunchLabel(),
        };
    }

    protected function unavailablePunchLabel(): string
    {
        if ($this->todayAttendance?->isComplete()) {
            return 'Attendance Complete';
        }

        $now = now();

        return $now->greaterThanOrEqualTo($this->workdayEnd($now))
            ? 'Attendance Closed'
            : 'Afternoon In starts at 1:00 PM';
    }

    public function nextPunchIcon(): string
    {
        return match ($this->nextPunch()) {
            'morning_time_in', 'afternoon_time_in' => 'arrow-right-end-on-rectangle',
            'morning_time_out', 'afternoon_time_out' => 'arrow-left-end-on-rectangle',
            default => 'check-circle',
        };
    }

    public function recordNextPunch(string $requestedPunch): void
    {
        $now = now();
        $nextPunch = $this->nextPunchAt($now);

        if (! $nextPunch || $requestedPunch !== $nextPunch) {
            Flux::toast(variant: 'warning', heading: 'Attendance action unavailable.', text: 'Your attendance step or the allowed time window has changed.');

            return;
        }

        if (in_array($nextPunch, ['morning_time_in', 'afternoon_time_in'], true) && $this->holidayTimeInBlocked()) {
            return;
        }

        if ($this->restDayBlocked()) {
            return;
        }

        if ($this->outsideGeofence()) {
            return;
        }

        match ($nextPunch) {
            'morning_time_in' => $this->clockMorningIn($now),
            'morning_time_out' => $this->clockMorningOut($now),
            'afternoon_time_in' => $this->clockAfternoonIn($now),
            'afternoon_time_out' => $this->clockAfternoonOut($now),
            default => $this->attendanceAlreadyComplete(),
        };
    }

    // ponytail: trusts browser GPS, so a spoofed location passes; anti-spoofing would need a native app.
    protected function outsideGeofence(): bool
    {
        $assignment = $this->assignment;

        if (! $assignment?->hasGeofence()) {
            return false;
        }

        if ($this->time_in_latitude === null || $this->time_in_longitude === null) {
            Flux::toast(variant: 'warning', heading: 'Location required.', text: 'Allow location access so your attendance location can be verified.');

            return true;
        }

        $distance = $assignment->distanceFromGeofence($this->time_in_latitude, $this->time_in_longitude);

        if ($distance <= $assignment->geofence_radius) {
            return false;
        }

        $this->clearFormState();

        Flux::toast(
            variant: 'danger',
            heading: 'Outside your OJT location.',
            text: sprintf('You are about %s m away. Attendance is only allowed within %d m of your assigned OJT location.', number_format($distance), $assignment->geofence_radius),
        );

        return true;
    }

    protected function clockMorningIn(CarbonInterface $timeIn): void
    {
        $assignment = $this->assignment;

        if (! $assignment) {
            Flux::toast(variant: 'warning', heading: 'No assignment yet.', text: 'You need an OJT assignment before creating attendance.');

            return;
        }

        $this->validate([
            'time_in_latitude' => ['required', 'numeric', 'between:-90,90'],
            'time_in_longitude' => ['required', 'numeric', 'between:-180,180'],
            'time_in_location_accuracy' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        $attendance = AttendanceLog::query()->firstOrCreate([
            'student_id' => $this->studentId(),
            'attendance_date' => $timeIn->toDateString(),
        ], [
            'ojt_assignment_id' => $assignment->id,
            'morning_time_in' => $timeIn,
            'time_in_latitude' => $this->time_in_latitude,
            'time_in_longitude' => $this->time_in_longitude,
            'time_in_location_accuracy' => $this->time_in_location_accuracy,
            'hours_rendered' => 0,
            'status' => $this->clockInStatus($timeIn),
        ]);

        if (! $attendance->wasRecentlyCreated) {
            $this->attendanceActionUnavailable();

            return;
        }

        $this->clearFormState();

        Flux::toast(variant: 'success', heading: 'Morning time in recorded.', text: 'Your morning time in has been saved.');
    }

    protected function clockMorningOut(CarbonInterface $timeOut): void
    {
        $attendance = $this->todayAttendance;

        if (! $attendance) {
            Flux::toast(variant: 'warning', heading: 'No time in found.', text: 'Please clock in before clocking out.');

            return;
        }

        $attendance->morning_time_out = $timeOut;
        $hours = $this->calculateHours($attendance, $timeOut);
        $updated = AttendanceLog::query()->whereKey($attendance->id)
            ->whereNotNull('morning_time_in')
            ->whereNull('morning_time_out')
            ->whereNull('afternoon_time_in')
            ->update([
                'morning_time_out' => $timeOut,
                'hours_rendered' => $this->withinRequiredHours($attendance, $hours),
            ]);

        if (! $updated) {
            $this->attendanceActionUnavailable();

            return;
        }

        $this->clearFormState();

        Flux::toast(variant: 'success', heading: 'Morning time out recorded.', text: 'Your morning session has been saved.');
    }

    protected function clockAfternoonIn(CarbonInterface $timeIn): void
    {
        $attendance = $this->todayAttendance;

        if (! $attendance) {
            $assignment = $this->assignment;

            if (! $assignment) {
                Flux::toast(variant: 'warning', heading: 'No assignment yet.', text: 'You need an OJT assignment before creating attendance.');

                return;
            }

            $attendance = AttendanceLog::query()->firstOrCreate([
                'student_id' => $this->studentId(),
                'attendance_date' => $timeIn->toDateString(),
            ], [
                'ojt_assignment_id' => $assignment->id,
                'afternoon_time_in' => $timeIn,
                'time_in_latitude' => $this->time_in_latitude,
                'time_in_longitude' => $this->time_in_longitude,
                'time_in_location_accuracy' => $this->time_in_location_accuracy,
                'hours_rendered' => 0,
                'status' => 'incomplete',
            ]);

            if (! $attendance->wasRecentlyCreated) {
                $this->attendanceActionUnavailable();

                return;
            }
        } else {
            $query = AttendanceLog::query()->whereKey($attendance->id)
                ->whereNull('afternoon_time_in')
                ->whereNull('afternoon_time_out');

            if ($attendance->morning_time_in) {
                $query->whereNotNull('morning_time_out');
            } else {
                $query->whereNull('morning_time_out');
            }

            if (! $query->update(['afternoon_time_in' => $timeIn])) {
                $this->attendanceActionUnavailable();

                return;
            }
        }

        $this->clearFormState();

        Flux::toast(variant: 'success', heading: 'Afternoon time in recorded.', text: 'Your afternoon time in has been saved.');
    }

    protected function clockAfternoonOut(CarbonInterface $timeOut): void
    {
        $attendance = $this->todayAttendance;

        if (! $attendance) {
            Flux::toast(variant: 'warning', heading: 'No morning time in found.', text: 'Please record morning time in first.');

            return;
        }

        if (! $attendance->afternoon_time_in) {
            Flux::toast(variant: 'warning', heading: 'Afternoon in needed.', text: 'Please record afternoon time in before afternoon time out.');

            return;
        }

        $hours = $this->calculateHours($attendance, $timeOut);

        $updated = AttendanceLog::query()->whereKey($attendance->id)
            ->whereNotNull('afternoon_time_in')
            ->whereNull('afternoon_time_out')
            ->when($attendance->morning_time_in, fn ($query) => $query->whereNotNull('morning_time_out'))
            ->update([
                'afternoon_time_out' => $timeOut,
                'hours_rendered' => $this->withinRequiredHours($attendance, $hours),
                'status' => $this->attendanceStatus($attendance, $hours),
            ]);

        if (! $updated) {
            $this->attendanceActionUnavailable();

            return;
        }

        $this->clearFormState();

        Flux::toast(variant: 'success', heading: 'Afternoon time out recorded.', text: 'Your rendered hours have been calculated.');
    }

    protected function attendanceAlreadyComplete(): void
    {
        Flux::toast(variant: 'warning', heading: 'Attendance complete.', text: 'Today already has a completed attendance record.');
    }

    protected function attendanceActionUnavailable(): void
    {
        Flux::toast(variant: 'warning', heading: 'Attendance action unavailable.', text: 'This attendance step was already recorded or is no longer valid.');
    }

    protected function clearFormState(): void
    {
        $this->time_in_latitude = null;
        $this->time_in_longitude = null;
        $this->time_in_location_accuracy = null;
        $this->resetAttendanceState();
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
    public function holidays(): array
    {
        return $this->holidayDataForYear(Carbon::createFromFormat('!Y-m', $this->calendarMonth)->year)['holidays'];
    }

    #[Computed]
    public function holidaysAvailable(): bool
    {
        return $this->holidayDataForYear(Carbon::createFromFormat('!Y-m', $this->calendarMonth)->year)['available'];
    }

    #[Computed]
    public function todayHolidayCheck(): array
    {
        $holidayData = $this->holidayDataForYear((int) now()->format('Y'));

        return [
            'available' => $holidayData['available'],
            'holiday' => collect($holidayData['holidays'])->firstWhere('date', $this->today()),
        ];
    }

    protected function holidayDataForYear(int $year): array
    {
        return Holidays::forYear($year);
    }

    protected function holidayTimeInBlocked(): bool
    {
        $holidayCheck = $this->todayHolidayCheck;

        if (! $holidayCheck['available']) {
            Flux::toast(variant: 'warning', heading: 'Holiday check unavailable.', text: 'Attendance time in is disabled until public holidays can be verified.');

            return true;
        }

        $holiday = $holidayCheck['holiday'];

        if (! $holiday) {
            return false;
        }

        Flux::toast(variant: 'warning', heading: 'Public holiday.', text: "Attendance time in is unavailable today ({$holiday['name']}).");

        return true;
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
    public function calendar(): array
    {
        $month = Carbon::createFromFormat('!Y-m', $this->calendarMonth);
        $start = $month->copy()->startOfWeek(Carbon::MONDAY);
        $end = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $attendanceByDate = AttendanceLog::query()
            ->where('student_id', $this->studentId())
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get(['attendance_date', 'morning_time_in', 'status'])
            ->keyBy(fn (AttendanceLog $attendance) => $attendance->attendance_date->toDateString());
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
                'status' => $attendanceByDate->get($key)?->status,
                'late' => $attendanceByDate->get($key)?->isLate() ?? false,
            ];
        }

        return ['month' => $month, 'days' => $days];
    }

    #[Computed]
    public function selectedAttendance(): ?AttendanceLog
    {
        if (! $this->selectedCalendarDate) {
            return null;
        }

        return AttendanceLog::query()
            ->where('student_id', $this->studentId())
            ->whereDate('attendance_date', $this->selectedCalendarDate)
            ->first();
    }

    #[Computed]
    public function selectedHoliday(): ?array
    {
        if (! $this->selectedCalendarDate) {
            return null;
        }

        return collect($this->holidayDataForYear((int) substr($this->selectedCalendarDate, 0, 4))['holidays'])
            ->firstWhere('date', $this->selectedCalendarDate);
    }

    #[Computed]
    public function summary(): array
    {
        $query = AttendanceLog::query()->where('student_id', $this->studentId());

        return [
            'total' => (clone $query)->count(),
            'present' => (clone $query)->where('status', 'present')->count(),
            'incomplete' => (clone $query)->where(fn ($query) => $query
                ->whereNull('afternoon_time_out')
                ->orWhere('status', 'incomplete'))
                ->count(),
            'hours' => (float) (clone $query)->sum('hours_rendered'),
        ];
    }
};
