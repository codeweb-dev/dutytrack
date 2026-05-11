<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $fillable = [
        'student_id',
        'ojt_assignment_id',
        'evaluator_id',
        'evaluator_type',
        'evaluation_date',
        'inclusive_from',
        'inclusive_to',
        'cooperating_program',
        'rating',
        'ratings',
        'remarks',
        'status',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
        'inclusive_from' => 'date',
        'inclusive_to' => 'date',
        'rating' => 'decimal:2',
        'ratings' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assignment()
    {
        return $this->belongsTo(OjtAssignment::class, 'ojt_assignment_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}
