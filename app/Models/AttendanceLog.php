<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $fillable = [
        'student_id',
        'ojt_assignment_id',
        'attendance_date',
        'time_in',
        'time_in_latitude',
        'time_in_longitude',
        'time_in_location_accuracy',
        'time_out',
        'break_minutes',
        'hours_rendered',
        'overtime_enabled',
        'status',
        'source',
        'remarks',
        'is_flagged',
        'flag_reason',
        'reviewed_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'time_in' => 'datetime',
        'time_in_latitude' => 'float',
        'time_in_longitude' => 'float',
        'time_in_location_accuracy' => 'integer',
        'overtime_enabled' => 'boolean',
        'time_out' => 'datetime',
        'is_flagged' => 'boolean',
        'hours_rendered' => 'decimal:2',
    ];

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

    public function dutyHourLog()
    {
        return $this->hasOne(DutyHourLog::class, 'attendance_log_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
