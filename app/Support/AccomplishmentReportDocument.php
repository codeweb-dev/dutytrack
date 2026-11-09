<?php

namespace App\Support;

use App\Models\AttendanceLog;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use ZipArchive;

/**
 * Fills resources/templates/weekly-accomplishment-report.docx: one Weekly Accomplishment Form
 * per calendar week, followed by a "Weekly Evidence" page with that week's attached photos.
 */
class AccomplishmentReportDocument
{
    protected const TEMPLATE = 'templates/weekly-accomplishment-report.docx';

    protected const EMU_PER_INCH = 914400;

    public static function download(User $student, mixed $from = null, mixed $to = null): Response
    {
        $toRules = ['nullable', 'date_format:Y-m-d'];

        if (is_string($from) && $from !== '') {
            $toRules[] = 'after_or_equal:from';
        }

        $dates = Validator::make(compact('from', 'to'), [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => $toRules,
        ])->validate();

        $logs = AttendanceLog::query()
            ->with('submission')
            ->where('student_id', $student->id)
            ->whereNotNull('afternoon_time_out')
            ->when($dates['from'], fn ($query, $from) => $query->whereDate('attendance_date', '>=', $from))
            ->when($dates['to'], fn ($query, $to) => $query->whereDate('attendance_date', '<=', $to))
            ->oldest('attendance_date')
            ->get();

        $student->loadMissing(['studentProfile', 'coordinator', 'ojtAssignment.companySupervisor.supervisorProfile']);
        $supervisor = $student->ojtAssignment?->companySupervisor;
        // Week numbers count from the student's first attendance so filtered downloads keep their real week.
        $firstWeek = Carbon::parse(AttendanceLog::query()->where('student_id', $student->id)->min('attendance_date') ?? now())->startOfWeek();

        $photos = [];
        $weeks = $logs
            ->groupBy(fn (AttendanceLog $log) => $log->attendance_date->copy()->startOfWeek()->toDateString())
            ->map(function (Collection $weekLogs, string $monday) use ($firstWeek, &$photos) {
                return self::week($weekLogs, Carbon::parse($monday), $firstWeek, $photos);
            })
            ->values()
            ->whenEmpty(fn ($weeks) => $weeks->push(['number' => '', 'dates' => '', 'month' => '', 'rows' => [], 'evidence' => []]));

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.view('exports.accomplishment-report-docx', [
            'weeks' => $weeks,
            'name' => $student->name,
            'course' => $student->studentProfile?->course,
            'agency' => $supervisor?->supervisorProfile?->company_name,
            'office' => $supervisor?->supervisorProfile?->position_department,
            'address' => $supervisor?->supervisorProfile?->company_address,
            'supervisorName' => $supervisor?->name,
            'coordinatorName' => $student->coordinator?->name,
        ])->render();

        return response(self::docx($xml, $photos), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="accomplishment-report-'.$student->id.'.docx"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    protected static function week(Collection $logs, Carbon $monday, Carbon $firstWeek, array &$photos): array
    {
        $evidence = [];

        foreach ($logs as $log) {
            $dayPhotos = [];

            foreach ($log->submission?->photo_paths ?? [] as $path) {
                if ($photo = self::photo($path)) {
                    $photo['index'] = count($photos) + 1;
                    $photos[] = $dayPhotos[] = $photo;
                }
            }

            if ($dayPhotos) {
                $evidence[] = ['label' => $log->attendance_date->format('l, m/d/Y'), 'photos' => $dayPhotos];
            }
        }

        $first = $logs->first()->attendance_date;
        $last = $logs->last()->attendance_date;

        return [
            'number' => (int) $firstWeek->diffInWeeks($monday) + 1,
            'dates' => $first->isSameDay($last) ? $first->format('M d, Y') : $first->format('M d').' - '.$last->format('M d, Y'),
            'month' => $logs->map(fn (AttendanceLog $log) => $log->attendance_date->format('F Y'))->unique()->implode(' / '),
            'rows' => $logs->map(fn (AttendanceLog $log) => [
                'date' => $log->attendance_date->format('m/d/Y'),
                'day' => $log->attendance_date->format('l'),
                'work' => self::lines($log->submission?->accomplishment),
                'skills' => self::lines($log->submission?->skills_learned),
            ])->all(),
            'evidence' => $evidence,
        ];
    }

    // Word only renders jpeg/png/gif reliably; anything else (webp, bmp) is re-encoded to png.
    protected static function photo(string $path): ?array
    {
        $disk = Storage::disk('local');

        if (! $disk->exists($path) || ! ($size = @getimagesize($disk->path($path)))) {
            return null;
        }

        $photo = ['ext' => [IMAGETYPE_JPEG => 'jpeg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif'][$size[2]] ?? null, 'file' => $disk->path($path)];

        if (! $photo['ext']) {
            $image = @imagecreatefromstring($disk->get($path));

            if (! $image) {
                return null;
            }

            ob_start();
            imagepng($image);
            $photo = ['ext' => 'png', 'bytes' => ob_get_clean()];
        }

        // Fit inside a 3in x 2.25in box so two photos sit side by side.
        $scale = min(3 * self::EMU_PER_INCH / $size[0], 2.25 * self::EMU_PER_INCH / $size[1]);

        return $photo + ['cx' => (int) ($size[0] * $scale), 'cy' => (int) ($size[1] * $scale)];
    }

    protected static function docx(string $documentXml, array $photos): string
    {
        $path = tempnam(sys_get_temp_dir(), 'accomplishment');
        copy(resource_path(self::TEMPLATE), $path);

        $zip = new ZipArchive;
        $zip->open($path);
        $zip->addFromString('word/document.xml', $documentXml);

        $relationships = '';

        foreach ($photos as $photo) {
            $target = 'media/photo'.$photo['index'].'.'.$photo['ext'];
            isset($photo['bytes']) ? $zip->addFromString('word/'.$target, $photo['bytes']) : $zip->addFile($photo['file'], 'word/'.$target);
            $relationships .= '<Relationship Id="rIdPhoto'.$photo['index'].'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="'.$target.'"/>';
        }

        $zip->addFromString('word/_rels/document.xml.rels', str_replace('</Relationships>', $relationships.'</Relationships>', $zip->getFromName('word/_rels/document.xml.rels')));
        $zip->addFromString('[Content_Types].xml', str_replace('</Types>', '<Default Extension="jpeg" ContentType="image/jpeg"/><Default Extension="gif" ContentType="image/gif"/></Types>', $zip->getFromName('[Content_Types].xml')));
        $zip->close();

        $docx = file_get_contents($path);
        unlink($path);

        return $docx;
    }

    // Drops characters XML 1.0 forbids so student text can never corrupt the document.
    protected static function lines(?string $text): array
    {
        $text = (string) preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', (string) $text);

        return $text === '' ? [] : preg_split('/\R/', $text);
    }
}
