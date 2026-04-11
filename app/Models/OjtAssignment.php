<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OjtAssignment extends Model
{
    protected $fillable = [
        'student_id',
        'ojt_type',
        'professor_id',
        'company_supervisor_id',
        'start_date',
        'end_date',
        'required_hours',
        'status',
        'remarks',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    public function companySupervisor()
    {
        return $this->belongsTo(User::class, 'company_supervisor_id');
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class, 'ojt_assignment_id');
    }

    public function dutyHourLogs()
    {
        return $this->hasMany(DutyHourLog::class, 'ojt_assignment_id');
    }
}
