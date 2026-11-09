<?php

namespace App\Support;

use App\Actions\Fortify\CreateNewUser;
use App\Models\AttendanceLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Fills the EARIST OJT Certificate of Completion template. A student earns it
 * once their rendered attendance hours reach their required OJT hours.
 */
class CertificateDocument
{
    public static function data(User $student): array
    {
        $student->loadMissing(['studentProfile', 'coordinator', 'ojtAssignment.companySupervisor.supervisorProfile']);

        $logs = AttendanceLog::query()->where('student_id', $student->id);
        $required = (int) $student->studentProfile?->ojt_required_hours;
        $rendered = (float) (clone $logs)->sum('hours_rendered');
        $course = (string) $student->studentProfile?->course;
        $program = CreateNewUser::COURSES[$course]['program'] ?? null;

        return [
            'eligible' => $required > 0 && $rendered >= $required,
            'required' => $required,
            'rendered' => $rendered,
            'name' => $student->name,
            'hours' => rtrim(rtrim(number_format($rendered, 2), '0'), '.'),
            'company' => $student->ojtAssignment?->companySupervisor?->supervisorProfile?->company_name,
            'from' => self::date((clone $logs)->min('attendance_date')),
            'to' => self::date((clone $logs)->max('attendance_date')),
            'degree' => $program ? 'Bachelor of Science in '.$program : ($course ?: null),
            'coordinator' => $student->coordinator?->name,
        ];
    }

    // Students must finish their hours first; coordinators pass $requireCompletion = false.
    public static function download(User $student, bool $requireCompletion = true): Response
    {
        return self::response($student, 'attachment', $requireCompletion);
    }

    public static function inline(User $student, bool $requireCompletion = true): Response
    {
        return self::response($student, 'inline', $requireCompletion);
    }

    protected static function response(User $student, string $disposition, bool $requireCompletion): Response
    {
        $data = self::data($student);

        abort_unless($data['eligible'] || ! $requireCompletion, 404);

        $pdf = Pdf::loadView('exports.ojt-certificate', $data + [
            'border' => self::image('certificate/border.jpg'),
            'logo' => self::image('certificate/earist-logo.png'),
        ])->setPaper('a4', 'landscape')->output();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="OJT-Certificate-'.Str::slug($student->name).'.pdf"',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    protected static function date(?string $value): ?string
    {
        return $value ? Carbon::parse($value)->format('F j, Y') : null;
    }

    protected static function image(string $path): string
    {
        return 'data:'.mime_content_type(public_path($path)).';base64,'.base64_encode(file_get_contents(public_path($path)));
    }
}
