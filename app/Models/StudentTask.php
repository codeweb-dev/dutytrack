<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StudentTask extends Model
{
    protected $fillable = [
        'student_id',
        'assigned_by',
        'ojt_assignment_id',
        'task_group_id',
        'title',
        'description',
        'revision_note',
        'status',
        'sort_order',
        'priority',
        'due_date',
        'completed_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    // Archived tasks vanish from every board and count; opt in with withoutGlobalScope('unarchived').
    protected static function booted(): void
    {
        static::addGlobalScope('unarchived', fn (Builder $query) => $query->whereNull($query->qualifyColumn('archived_at')));
    }

    public static function statusLabels(): array
    {
        return [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'in_review' => 'In Review',
            'revision' => 'Revision',
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
