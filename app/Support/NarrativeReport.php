<?php

namespace App\Support;

use App\Models\AttendanceLog;
use App\Models\AttendanceSubmission;
use App\Models\StudentRequirement;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Builds the EARIST narrative report of OJT experiences from a draft the
 * student edits. Drafts are prefilled from the student's profile, placement,
 * accomplishment reports, and attendance.
 */
class NarrativeReport
{
    public const KEY = 'narrative_report';

    public const IMAGES = ['org_chart', 'sketch'];

    public const MAX_PHOTOS = 12;

    /** Cover and signatory details. */
    public const DETAILS = [
        'name' => 'Your name',
        'program' => 'Program',
        'company_name' => 'Company',
        'company_location' => 'Company location',
        'coordinator_name' => 'Practicum coordinator',
        'course_code' => 'Course code',
        'hours' => 'OJT hours',
        'semester' => 'Semester',
        'school_year' => 'School year',
        'supervisor_name' => 'Immediate supervisor',
        'supervisor_position' => 'Supervisor position',
        'manager_name' => 'Manager',
        'manager_position' => 'Manager position',
    ];

    /** Written sections, in report order. Required ones must be filled before submitting. */
    public const SECTIONS = [
        'acknowledgment' => ['label' => 'Acknowledgment', 'required' => true],
        'introduction' => ['label' => 'Introduction', 'required' => true],
        'course_syllabus' => ['label' => 'Course Syllabus', 'required' => false],
        'vision_mission' => ['label' => 'Vision and Mission Statement', 'required' => false],
        'corporate_profile' => ['label' => 'Corporate Profile', 'required' => false],
        'history' => ['label' => 'History of the Company', 'required' => false],
        'products_services' => ['label' => 'Products/Services', 'required' => false],
        'job_description' => ['label' => 'Job Description (Duties and Responsibilities)', 'required' => true],
        'experiences' => ['label' => 'Experiences', 'required' => true],
        'evaluation' => ['label' => 'Evaluation/Impression', 'required' => true],
        'insights' => ['label' => 'Insights Gained', 'required' => true],
    ];

    public static function defaults(User $student): array
    {
        $profile = RequirementForm::prefill($student);
        $month = now()->month;
        $acceptance = StudentRequirement::query()->where('student_id', $student->id)->where('key', 'acceptance_letter')->first()?->form_data;

        return [
            'name' => $profile['name'] ?? '',
            'program' => $profile['program'] ?? '',
            'company_name' => $profile['company_name'] ?? '',
            'company_location' => $profile['company_address'] ?? '',
            'coordinator_name' => $profile['coordinator_name'] ?? '',
            'course_code' => '',
            'hours' => (string) ($profile['hours'] ?? ''),
            'semester' => match (true) {
                $month >= 8 => 'First Semester',
                $month <= 5 => 'Second Semester',
                default => 'Summer',
            },
            'school_year' => $profile['school_year'] ?? '',
            'supervisor_name' => $profile['supervisor_name'] ?? '',
            'supervisor_position' => $profile['supervisor_position'] ?? '',
            'manager_name' => '',
            'manager_position' => '',
            'acknowledgment' => self::acknowledgment($student, $profile),
            'introduction' => '',
            'course_syllabus' => '',
            'vision_mission' => '',
            'corporate_profile' => '',
            'history' => '',
            'products_services' => '',
            'job_description' => $acceptance['job_description'] ?? '',
            'experiences' => self::submissions($student)
                ->map(fn (AttendanceSubmission $s) => $s->attendanceLog->attendance_date->format('l, F j, Y')."\n".trim($s->accomplishment))
                ->implode("\n\n"),
            'evaluation' => '',
            'insights' => self::submissions($student)
                ->flatMap(fn (AttendanceSubmission $s) => preg_split('/[\r\n,;]+/', (string) $s->skills_learned))
                ->map(fn (string $line) => trim($line, " \t-•*"))
                ->filter()
                ->unique(fn (string $line) => Str::lower($line))
                ->map(fn (string $line) => '• '.$line)
                ->implode("\n"),
            'org_chart' => null,
            'sketch' => null,
            'photos' => [],
            'include_letters' => true,
            'include_dtr' => true,
        ];
    }

    public static function rules(User $student, bool $submitting = false): array
    {
        $text = fn (int $max, bool $required = false) => [$submitting && $required ? 'required' : 'nullable', 'string', 'max:'.$max];
        $image = ['nullable', 'string', 'starts_with:'.self::folder($student), 'not_regex:/\.\./'];

        return [
            ...collect(self::DETAILS)->mapWithKeys(fn ($label, $name) => [
                "data.$name" => $text(255, ! in_array($name, ['course_code', 'manager_name', 'manager_position', 'supervisor_position'], true)),
            ]),
            ...collect(self::SECTIONS)->mapWithKeys(fn ($section, $name) => ["data.$name" => $text(20000, $section['required'])]),
            'data.org_chart' => $image,
            'data.sketch' => $image,
            'data.photos' => ['array', 'max:'.self::MAX_PHOTOS],
            'data.photos.*' => ['string', 'regex:/\A\d+:\d+\z/'],
            'data.include_letters' => ['boolean'],
            'data.include_dtr' => ['boolean'],
        ];
    }

    public static function attributes(): array
    {
        return collect(self::DETAILS)->mapWithKeys(fn ($label, $name) => ["data.$name" => Str::lower($label)])
            ->merge(collect(self::SECTIONS)->mapWithKeys(fn ($section, $name) => ["data.$name" => Str::lower($section['label'])]))
            ->all();
    }

    /** Where uploaded org charts and sketches live; render() only reads images from here. */
    public static function folder(User $student): string
    {
        return 'student-requirements/'.$student->id.'/narrative/';
    }

    /** Accomplishment report photos the student can pick, newest first, keyed "submission:index". */
    public static function photoOptions(User $student): Collection
    {
        return self::submissions($student)
            ->reverse()
            ->flatMap(fn (AttendanceSubmission $s) => collect($s->photo_paths ?? [])->keys()->map(fn (int $index) => [
                'key' => $s->id.':'.$index,
                'url' => route('reports.photo', [$s, $index]),
                'date' => $s->attendanceLog->attendance_date->format('M j, Y'),
            ]))
            ->values();
    }

    public static function render(User $student, array $data): string
    {
        $data = array_merge(self::defaults($student), array_intersect_key($data, self::defaults($student)));
        $view = [
            'd' => array_map(fn ($value) => is_scalar($value) || $value === null ? (string) $value : $value, $data),
            'text' => fn (?string $value) => collect(preg_split('/\R\s*\R/', trim((string) $value)))
                ->filter()
                // Multi-line paragraphs (lists, dated entries) keep their own layout instead of a first-line indent.
                ->map(fn (string $paragraph) => '<p'.(str_contains(trim($paragraph), "\n") ? '' : ' class="indent"').'>'.nl2br(e(trim($paragraph))).'</p>')
                ->implode(''),
            'logo' => 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('certificate/earist-logo.png'))),
            'images' => collect(self::IMAGES)->mapWithKeys(fn ($type) => [$type => self::upload($student, $data[$type] ?? null)])->all(),
            'photos' => self::photos($student, (array) ($data['photos'] ?? [])),
            'letters' => ($data['include_letters'] ?? false) ? self::letters($student) : [],
            'dtr' => ($data['include_dtr'] ?? false) ? self::dtr($student) : [],
        ];

        // Two passes: the first records the page each section lands on, the second prints them in the contents.
        $pages = [];
        $first = Pdf::loadView('exports.narrative-report', $view + ['pages' => []])->setPaper('a4');
        $first->getDomPDF()->setCallbacks([['event' => 'end_frame', 'f' => function ($frame, $canvas) use (&$pages) {
            $node = $frame->get_node();

            if ($node instanceof DOMElement && str_starts_with($id = $node->getAttribute('id'), 'toc-')) {
                $pages[$id] ??= $canvas->get_page_number();
            }
        }]]);
        $first->render();

        $pdf = Pdf::loadView('exports.narrative-report', $view + ['pages' => $pages])->setPaper('a4');
        $pdf->render();
        $pdf->getDomPDF()->getCanvas()->page_script(function (int $page, int $count, $canvas, $fonts) {
            if ($page > 1) {
                $canvas->text(297.6 - 6, 812, (string) $page, $fonts->getFont('DejaVu Serif'), 9);
            }
        });

        return $pdf->output();
    }

    /** @return Collection<int, AttendanceSubmission> in date order */
    protected static function submissions(User $student): Collection
    {
        return AttendanceSubmission::query()
            ->with('attendanceLog')
            ->where('student_id', $student->id)
            ->whereHas('attendanceLog')
            ->get()
            ->sortBy(fn (AttendanceSubmission $s) => $s->attendanceLog->attendance_date)
            ->values();
    }

    protected static function acknowledgment(User $student, array $profile): string
    {
        $blank = str_repeat('_', 20);

        return implode("\n\n", [
            'With affection and gratitude, I would like to express my deep appreciation to the following persons who shared their time, expertise, assistance, guidance, moral support, and inspiration for the realization of my Internship/On-the-Job Training.',
            'I am grateful to my parents, '.($profile['guardian_name'] ?? $blank).', for their continuous support and guidance.',
            'To my OJT Coordinator, '.($profile['coordinator_name'] ?? $blank).', for the untiring assistance and for sharing their wisdom so I could be a good and effective trainee.',
            'To my immediate supervisor, '.($profile['supervisor_name'] ?? $blank).', for their patience, guidance, and kindness throughout my training at '.($profile['company_name'] ?? $blank).'.',
            'To the Dean, '.$blank.', for giving me the opportunity to be one of the trainees and to prepare for actual working experience.',
            'To my friends and the many other persons who inspired and encouraged me with their thoughts and prayers;',
            'And above all, to our Lord for the blessings, good health, strength, and wisdom He showered upon me.',
        ]);
    }

    protected static function upload(User $student, mixed $path): ?string
    {
        return is_string($path) && str_starts_with($path, self::folder($student)) && ! str_contains($path, '..')
            ? self::image($path, 1600)
            : null;
    }

    /** Picked photos, verified to belong to the student, with their report date as the caption. */
    protected static function photos(User $student, array $keys): array
    {
        $picked = collect($keys)->filter(fn ($key) => is_string($key) && preg_match('/\A\d+:\d+\z/', $key))->take(self::MAX_PHOTOS);
        $submissions = AttendanceSubmission::query()
            ->with('attendanceLog')
            ->where('student_id', $student->id)
            ->whereIn('id', $picked->map(fn ($key) => (int) Str::before($key, ':')))
            ->get()
            ->keyBy('id');

        return $picked->map(function (string $key) use ($submissions) {
            [$id, $index] = array_map('intval', explode(':', $key));
            $submission = $submissions->get($id);
            $path = $submission?->photo_paths[$index] ?? null;
            $src = is_string($path) ? self::image($path, 1000) : null;

            return $src ? ['src' => $src, 'caption' => $submission->attendanceLog?->attendance_date?->format('F j, Y')] : null;
        })->filter()->values()->all();
    }

    /** Application and acceptance letters the student filled out online, as HTML pages. */
    protected static function letters(User $student): array
    {
        return StudentRequirement::query()
            ->where('student_id', $student->id)
            ->whereIn('key', ['application_for_ojt_individual', 'acceptance_letter'])
            ->whereNotNull('form_data')
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (StudentRequirement $r) => [
                $r->key === 'acceptance_letter' ? 'Acceptance Letter' : 'Application Letter' => Str::between(RequirementForm::html($r, $r->form_data), '<body>', '</body>'),
            ])
            ->all();
    }

    /** One Daily Time Record per month the student has attendance. */
    protected static function dtr(User $student): array
    {
        $range = AttendanceLog::query()->where('student_id', $student->id)->selectRaw('min(attendance_date) as first, max(attendance_date) as last')->first();

        if (! $range?->first) {
            return [];
        }

        $months = [];

        for ($month = Carbon::parse($range->first)->startOfMonth(); $month->lte(Carbon::parse($range->last)); $month->addMonth()) {
            $months[] = DtrDocument::data($student, $month->copy());
        }

        return $months;
    }

    /**
     * Downscaled JPEG data URI, so phone photos don't bloat the PDF.
     * ponytail: resized copies are cached under narrative-cache/ and never pruned; add cleanup if disk use matters.
     */
    protected static function image(string $path, int $max): ?string
    {
        $disk = Storage::disk('local');

        if (! $disk->exists($path)) {
            return null;
        }

        $cache = 'narrative-cache/'.md5($path.'|'.$disk->lastModified($path).'|'.$max).'.jpg';

        if (! $disk->exists($cache)) {
            $source = @imagecreatefromstring($disk->get($path));

            if (! $source) {
                return null;
            }

            $scale = min(1, $max / max(imagesx($source), imagesy($source)));
            $width = max(1, (int) (imagesx($source) * $scale));
            $height = max(1, (int) (imagesy($source) * $scale));
            $canvas = imagecreatetruecolor($width, $height);
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));

            ob_start();
            imagejpeg($canvas, null, 82);
            $disk->put($cache, ob_get_clean());
        }

        return 'data:image/jpeg;base64,'.base64_encode($disk->get($cache));
    }
}
