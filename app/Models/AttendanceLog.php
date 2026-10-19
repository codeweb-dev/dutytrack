<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $fillable = [
        'student_id',
        'ojt_assignment_id',
        'attendance_date',
        'morning_time_in',
        'morning_time_out',
        'afternoon_time_in',
        'afternoon_time_out',
        'time_in_latitude',
        'time_in_longitude',
        'time_in_location_accuracy',
        'hours_rendered',
        'overtime_enabled',
        'status',
        'remarks',
        'reviewed_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'morning_time_in' => 'datetime',
        'morning_time_out' => 'datetime',
        'afternoon_time_in' => 'datetime',
        'afternoon_time_out' => 'datetime',
        'time_in_latitude' => 'float',
        'time_in_longitude' => 'float',
        'time_in_location_accuracy' => 'integer',
        'overtime_enabled' => 'boolean',
        'hours_rendered' => 'decimal:2',
    ];

    public function isComplete(): bool
    {
        return $this->afternoon_time_out !== null;
    }

    public function displayMorningTimeIn()
    {
        return $this->morning_time_in;
    }

    public function displayMorningTimeOut()
    {
        return $this->morning_time_out;
    }

    public function displayAfternoonTimeIn()
    {
        return $this->afternoon_time_in;
    }

    public function displayAfternoonTimeOut()
    {
        return $this->afternoon_time_out;
    }

    public function hasTimeInLocation(): bool
    {
        return $this->time_in_latitude !== null && $this->time_in_longitude !== null;
    }

    public function timeInMapUrl(): ?string
    {
        if (! $this->hasTimeInLocation()) {
            return null;
        }

        return sprintf(
            'https://www.openstreetmap.org/?mlat=%s&mlon=%s#map=17/%s/%s',
            $this->time_in_latitude,
            $this->time_in_longitude,
            $this->time_in_latitude,
            $this->time_in_longitude,
        );
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assignment()
    {
        return $this->belongsTo(OjtAssignment::class, 'ojt_assignment_id');
    }

    public function submission()
    {
        return $this->hasOne(AttendanceSubmission::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
