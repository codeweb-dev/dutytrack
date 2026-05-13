<?php

namespace App\Support;

use App\Models\AttendanceLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class AccomplishmentReportDocument
{
    public static function download(User $student, ?string $from = null, ?string $to = null): Response
    {
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : null;
        $toDate = $to ? Carbon::parse($to)->endOfDay() : null;

        $logs = AttendanceLog::query()
            ->with(['submission', 'assignment.professor', 'assignment.companySupervisor'])
            ->where('student_id', $student->id)
            ->whereNotNull('afternoon_time_out')
            ->when($fromDate, fn ($query) => $query->whereDate('attendance_date', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->whereDate('attendance_date', '<=', $toDate))
            ->oldest('attendance_date')
            ->get();

        $filename = 'accomplishment-report-'.$student->id.'.doc';

        return response()
            ->view('exports.accomplishment-report-document', [
                'student' => $student->loadMissing('studentProfile'),
                'logs' => $logs,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'totalHours' => (float) $logs->sum('hours_rendered'),
                'supervisorName' => self::supervisorName($logs),
                'photoData' => self::photoData($logs),
            ], 200, [
                'Content-Type' => 'application/msword; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Cache-Control' => 'max-age=0',
            ]);
    }

    protected static function supervisorName(Collection $logs): string
    {
        $assignment = $logs->first()?->assignment;

        return $assignment?->professor?->name
            ?? $assignment?->companySupervisor?->name
            ?? '';
    }

    protected static function photoData(Collection $logs): array
    {
        return $logs
            ->mapWithKeys(function (AttendanceLog $log): array {
                $photos = collect($log->submission?->photo_paths ?? [])
                    ->map(function (string $path): ?string {
                        $disk = Storage::disk('public');

                        if (! $disk->exists($path)) {
                            return null;
                        }

                        $mime = $disk->mimeType($path) ?: 'image/jpeg';

                        return 'data:'.$mime.';base64,'.base64_encode($disk->get($path));
                    })
                    ->filter()
                    ->values()
                    ->all();

                return [$log->id => $photos];
            })
            ->all();
    }
}
