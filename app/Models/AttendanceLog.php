<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $fillable = [
        'student_id',
        'ojt_assignment_id',
        'attendance_date',
        'time_in',
        'time_out',
        'break_minutes',
        'hours_rendered',
        'status',
        'source',
        'remarks',
        'is_flagged',
        'flag_reason',
        'reviewed_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'is_flagged' => 'boolean',
        'hours_rendered' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assignment()
    {
        return $this->belongsTo(OjtAssignment::class, 'ojt_assignment_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
