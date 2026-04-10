<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfessorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',
        'department',
        'position',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
