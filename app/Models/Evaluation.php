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
        'rating',
        'remarks',
        'status',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
        'rating' => 'decimal:2',
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
