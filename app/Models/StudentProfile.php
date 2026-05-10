<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'student_id',
        'course',
        'ojt_required_hours',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
