<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DutyHourLog extends Model
{
    protected $fillable = [
        'student_id',
        'ojt_assignment_id',
        'attendance_log_id',
        'log_date',
        'hours_rendered',
        'approved_hours',
        'status',
        'remarks',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'log_date' => 'date',
        'approved_at' => 'datetime',
        'hours_rendered' => 'decimal:2',
        'approved_hours' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assignment()
    {
        return $this->belongsTo(OjtAssignment::class, 'ojt_assignment_id');
    }

    public function attendance()
    {
        return $this->belongsTo(AttendanceLog::class, 'attendance_log_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
