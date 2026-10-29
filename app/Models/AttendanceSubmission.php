<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AttendanceSubmission extends Model
{
    protected $fillable = [
        'attendance_log_id',
        'student_id',
        'ojt_assignment_id',
        'accomplishment',
        'photo_paths',
    ];

    protected $casts = [
        'photo_paths' => 'array',
    ];

    public function photoUrls(): array
    {
        return collect($this->photo_paths ?? [])
            ->map(fn (string $path): string => Storage::disk('public')->url($path))
            ->all();
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assignment()
    {
        return $this->belongsTo(OjtAssignment::class, 'ojt_assignment_id');
    }
}
