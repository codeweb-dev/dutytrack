<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupervisorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'supervisor_id',
        'supervisor_type',
        'company_name',
        'company_address',
        'position_department',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
