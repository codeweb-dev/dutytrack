<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'student_id',
        'extension_name',
        'birthdate',
        'course',
        'year_level',
        'address',
        'guardian_name',
        'guardian_address',
        'guardian_contact_number',
        'ojt_required_hours',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
