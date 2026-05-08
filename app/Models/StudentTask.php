<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentTask extends Model
{
    protected $fillable = [
        'student_id',
        'assigned_by',
        'ojt_assignment_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'in_review' => 'In Review',
            'done' => 'Done',
        ];
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignment()
    {
        return $this->belongsTo(OjtAssignment::class, 'ojt_assignment_id');
    }
}
