<?php

namespace App\Models;

use App\Enums\ResumeTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Resume extends Model
{
    /** Blank shape of one entry for each repeatable entry section. */
    public const ENTRIES = [
        'education' => ['level' => 'College', 'school' => '', 'program' => '', 'address' => '', 'start_year' => '', 'end_year' => '', 'current' => false],
        'achievements' => ['title' => '', 'organization' => '', 'period' => '', 'description' => ''],
        'experience' => ['title' => '', 'company' => '', 'type' => '', 'start' => '', 'end' => '', 'current' => false, 'description' => ''],
    ];

    /** Repeatable single-line sections. */
    public const LISTS = ['technical_skills', 'personal_skills'];

    public const IMAGES = ['photo', 'signature'];

    public const CERTIFICATION = 'Hereby certify that the foregoing facts written are true and correct to the best of my knowledge and belief.';

    protected $fillable = [
        'student_id',
        'title',
        'template',
        'data',
        'photo_path',
        'signature_path',
    ];

    protected $casts = [
        'data' => 'array',
        'template' => ResumeTemplate::class,
    ];

    protected static function booted(): void
    {
        static::deleting(function (Resume $resume) {
            foreach (self::IMAGES as $type) {
                $resume->deleteImage($type, save: false);
            }
        });
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public static function emptyData(): array
    {
        return [
            'first_name' => '',
            'middle_name' => '',
            'last_name' => '',
            'phone' => '',
            'email' => '',
            'address' => '',
            'objective' => '',
            'technical_skills' => [''],
            'personal_skills' => [''],
            'education' => [self::ENTRIES['education']],
            'achievements' => [],
            'experience' => [],
            'show_certification' => true,
        ];
    }

    /** New resume data prefilled from the student's DutyTrack profile. */
    public static function prefilledData(User $student): array
    {
        $student->loadMissing('studentProfile');
        $data = self::emptyData();

        $data['first_name'] = (string) $student->first_name;
        $data['middle_name'] = (string) $student->middle_name;
        $data['last_name'] = (string) $student->last_name;
        $data['phone'] = (string) $student->contact_number;
        $data['email'] = (string) $student->email;
        $data['address'] = (string) $student->studentProfile?->address;
        $data['education'][0]['program'] = (string) $student->studentProfile?->course;

        return $data;
    }

    /** Stored data merged over the current shape, so older rows gain new keys. */
    public function formData(): array
    {
        return array_replace(self::emptyData(), $this->data ?? []);
    }

    public function fullName(): string
    {
        return self::nameFrom($this->formData());
    }

    public static function nameFrom(array $data): string
    {
        $parts = [$data['first_name'] ?? '', $data['middle_name'] ?? '', $data['last_name'] ?? ''];

        return trim(preg_replace('/\s+/', ' ', implode(' ', array_map('strval', $parts))));
    }

    public function fileName(): string
    {
        $data = $this->formData();
        $parts = array_filter([Str::slug($data['first_name'], '_'), Str::slug($data['last_name'], '_')]);

        return 'Resume_'.(Str::title(implode('_', $parts)) ?: 'Draft').'.pdf';
    }

    public function imagePath(string $type): ?string
    {
        return $this->{$type.'_path'};
    }

    public function storeImage(string $type, UploadedFile $file): void
    {
        $old = $this->imagePath($type);

        $this->update([$type.'_path' => $file->store('resumes/'.$this->student_id, 'local')]);

        if ($old) {
            Storage::disk('local')->delete($old);
        }
    }

    public function deleteImage(string $type, bool $save = true): void
    {
        if ($path = $this->imagePath($type)) {
            Storage::disk('local')->delete($path);
        }

        $this->{$type.'_path'} = null;

        if ($save) {
            $this->save();
        }
    }

    public function duplicate(): self
    {
        $copy = $this->replicate();
        $copy->title = Str::limit($this->title.' (Copy)', 255, '');

        foreach (self::IMAGES as $type) {
            $path = $this->imagePath($type);

            if ($path && Storage::disk('local')->exists($path)) {
                $newPath = 'resumes/'.$this->student_id.'/'.Str::random(40).'.'.pathinfo($path, PATHINFO_EXTENSION);
                Storage::disk('local')->copy($path, $newPath);
                $copy->{$type.'_path'} = $newPath;
            } else {
                $copy->{$type.'_path'} = null;
            }
        }

        $copy->save();

        return $copy;
    }
}
