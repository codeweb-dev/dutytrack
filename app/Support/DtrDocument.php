<?php

namespace App\Support;

use App\Models\AttendanceLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Fills the EARIST Daily Time Record (A4 portrait) for one month of a student's attendance.
 * Holidays print "HOLIDAY" across the row instead of times.
 */
class DtrDocument
{
    public static function data(User $student, Carbon $month): array
    {
        $student->loadMissing(['studentProfile', 'ojtAssignment.companySupervisor.supervisorProfile']);

        $logs = AttendanceLog::query()
            ->where('student_id', $student->id)
            ->whereBetween('attendance_date', [$month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->get()
            ->keyBy(fn (AttendanceLog $log) => $log->attendance_date->day);
        $holidays = collect(Holidays::forYear($month->year)['holidays'])->keyBy('date');

        $rows = [];

        for ($day = 1; $day <= 31; $day++) {
            $date = $day <= $month->daysInMonth ? $month->copy()->day($day) : null;
            $log = $logs->get($day);
            $late = $log ? self::lateMinutes($log->morning_time_in, 8) + self::lateMinutes($log->afternoon_time_in, 13) : 0;

            $rows[] = [
                'day' => $day,
                'exists' => $date !== null,
                'holiday' => $date && ! $log ? ($holidays->get($date->toDateString())['name'] ?? null) : null,
                'arrival' => ($log?->morning_time_in ?? $log?->afternoon_time_in)?->format('g:i A'),
                'departure' => ($log?->afternoon_time_out ?? $log?->morning_time_out)?->format('g:i A'),
                'late' => $late,
                'hours' => $log ? (float) $log->hours_rendered : null,
            ];
        }

        return [
            'name' => $student->name,
            'month' => $month->format('F Y'),
            'office' => $student->ojtAssignment?->companySupervisor?->supervisorProfile?->company_name,
            'dept' => $student->studentProfile?->course,
            'rows' => $rows,
            'totalLate' => array_sum(array_column($rows, 'late')),
            'totalHours' => array_sum(array_column($rows, 'hours')),
        ];
    }

    public static function download(User $student, ?string $month): Response
    {
        return self::response($student, $month, 'attachment');
    }

    public static function inline(User $student, ?string $month): Response
    {
        return self::response($student, $month, 'inline');
    }

    protected static function response(User $student, ?string $month, string $disposition): Response
    {
        $month = is_string($month) && preg_match('/\A\d{4}-(0[1-9]|1[0-2])\z/', $month)
            ? Carbon::createFromFormat('!Y-m', $month)
            : now()->startOfMonth();

        $pdf = Pdf::loadView('exports.dtr', self::data($student, $month) + [
            'logo' => 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('certificate/earist-logo.png'))),
        ])->setPaper('a4', 'portrait')->output();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="DTR-'.Str::slug($student->name).'-'.$month->format('Y-m').'.pdf"',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    protected static function lateMinutes(?CarbonInterface $timeIn, int $startHour): int
    {
        $start = $timeIn?->copy()->setTime($startHour, 0);

        return $timeIn && $timeIn->greaterThan($start) ? (int) $start->diffInMinutes($timeIn) : 0;
    }
}
