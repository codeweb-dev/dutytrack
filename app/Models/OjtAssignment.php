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
        'status',
        'remarks',
    ];

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'ojt_assignment_id');
    }

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

    public function attendanceSubmissions()
    {
        return $this->hasMany(AttendanceSubmission::class, 'ojt_assignment_id');
    }

    public function studentTasks()
    {
        return $this->hasMany(StudentTask::class, 'ojt_assignment_id');
    }

    public function chatConversations()
    {
        return $this->hasMany(ChatConversation::class, 'ojt_assignment_id');
    }
}
