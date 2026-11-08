<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OjtAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'ojt_type',
        'company_supervisor_id',
        'status',
        'remarks',
        'geofence_latitude',
        'geofence_longitude',
        'geofence_radius',
    ];

    protected $casts = [
        'geofence_latitude' => 'float',
        'geofence_longitude' => 'float',
        'geofence_radius' => 'integer',
    ];

    public function hasGeofence(): bool
    {
        return $this->geofence_latitude !== null && $this->geofence_longitude !== null && $this->geofence_radius;
    }

    // Haversine distance in meters from the pinned location.
    public function distanceFromGeofence(float $latitude, float $longitude): float
    {
        $dLat = deg2rad($latitude - $this->geofence_latitude);
        $dLng = deg2rad($longitude - $this->geofence_longitude);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($this->geofence_latitude)) * cos(deg2rad($latitude)) * sin($dLng / 2) ** 2;

        return 6371000 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'ojt_assignment_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function companySupervisor()
    {
        return $this->belongsTo(User::class, 'company_supervisor_id');
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class, 'ojt_assignment_id');
    }
}
