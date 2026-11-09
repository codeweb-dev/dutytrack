<?php

namespace App\Support;

use App\Models\Resume;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Renders a resume to PDF. The live preview and the download use the same
 * output, so what students see is exactly what they get.
 */
class ResumeDocument
{
    public static function render(Resume $resume): string
    {
        return Pdf::loadView($resume->template->view(), self::viewData($resume))
            ->setPaper('a4')
            ->output();
    }

    public static function download(Resume $resume): Response
    {
        return self::response($resume, 'attachment');
    }

    public static function inline(Resume $resume): Response
    {
        return self::response($resume, 'inline');
    }

    protected static function response(Resume $resume, string $disposition): Response
    {
        return response(self::render($resume), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$resume->fileName().'"',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    public static function viewData(Resume $resume): array
    {
        $data = $resume->formData();

        return [
            'name' => $resume->fullName(),
            'data' => $data,
            'technicalSkills' => self::lines($data['technical_skills']),
            'personalSkills' => self::lines($data['personal_skills']),
            'education' => self::entries($data, 'education', fn (array $item) => [
                'heading' => $item['program'] ?: $item['level'],
                // EARIST workbook style: "(Graduating, 2027)" for current students with an expected year.
                'period' => $item['current'] && $item['end_year']
                    ? 'Graduating, '.$item['end_year']
                    : self::period($item['start_year'], $item['end_year'], $item['current']),
            ]),
            'achievements' => self::entries($data, 'achievements'),
            'experience' => self::entries($data, 'experience', fn (array $item) => [
                'period' => self::period(self::month($item['start']), self::month($item['end']), $item['current']),
            ]),
            'photo' => self::image($resume->photo_path),
            'signature' => self::image($resume->signature_path),
            'certification' => $data['show_certification'] ? Resume::CERTIFICATION : null,
        ];
    }

    /** Inline style that fits an image inside a box (in points) without distorting it. */
    public static function fit(array $image, float $maxWidth, float $maxHeight): string
    {
        $width = min($maxWidth, $maxHeight * $image['ratio']);

        return sprintf('width:%.2fpt;height:%.2fpt;', $width, $width / $image['ratio']);
    }

    protected static function lines(array $items): array
    {
        return array_values(array_filter(array_map(fn ($item) => trim((string) $item), $items), 'strlen'));
    }

    /** Drops entries the student left blank and adds display fields. */
    protected static function entries(array $data, string $section, ?callable $extra = null): array
    {
        return collect($data[$section])
            ->map(fn ($item) => array_map(
                fn ($value) => is_bool($value) ? $value : trim((string) $value),
                array_replace(Resume::ENTRIES[$section], (array) $item),
            ))
            ->filter(fn (array $item) => collect($item)->except(['level', 'current'])->contains(fn ($value) => $value !== ''))
            ->map(fn (array $item) => $extra ? array_merge($item, $extra($item)) : $item)
            ->values()
            ->all();
    }

    protected static function period(?string $start, ?string $end, mixed $current): string
    {
        return implode(' – ', array_filter([$start, $current ? 'Present' : $end]));
    }

    protected static function month(?string $value): ?string
    {
        return $value ? Carbon::createFromFormat('!Y-m', $value)->format('M Y') : null;
    }

    protected static function image(?string $path): ?array
    {
        $disk = Storage::disk('local');

        if (! $path || ! $disk->exists($path)) {
            return null;
        }

        $contents = $disk->get($path);
        $size = @getimagesizefromstring($contents);

        if (! $size || ! $size[0] || ! $size[1]) {
            return null;
        }

        return [
            'src' => 'data:'.$size['mime'].';base64,'.base64_encode($contents),
            'ratio' => $size[0] / $size[1],
        ];
    }
}
