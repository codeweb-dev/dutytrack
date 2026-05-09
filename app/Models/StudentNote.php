<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentNote extends Model
{
    protected $fillable = [
        'student_id',
        'title',
        'body',
        'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
