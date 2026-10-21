<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'coordinator_id',
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'contact_number',
        'email',
        'password',
        'profile_photo_path',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'has_completed_onboarding' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function profilePhotoUrl(): ?string
    {
        return $this->profile_photo_path
            ? Storage::url($this->profile_photo_path)
            : null;
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profile_photo_path
            ? Storage::url($this->profile_photo_path)
            : null;
    }

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function studentRequirements()
    {
        return $this->hasMany(StudentRequirement::class, 'student_id')
            ->whereIn('key', StudentRequirement::activeKeys());
    }

    public function studentTasks()
    {
        return $this->hasMany(StudentTask::class, 'student_id');
    }

    public function studentNotes()
    {
        return $this->hasMany(StudentNote::class, 'student_id');
    }

    public function assignedStudentTasks()
    {
        return $this->hasMany(StudentTask::class, 'assigned_by');
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function ojtAssignment()
    {
        return $this->hasOne(OjtAssignment::class, 'student_id');
    }

    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function managedStudents()
    {
        return $this->hasMany(User::class, 'coordinator_id');
    }

    public function supervisorProfile()
    {
        return $this->hasOne(SupervisorProfile::class);
    }

    public function coordinatorProfile()
    {
        return $this->hasOne(CoordinatorProfile::class);
    }

    public function companySupervisorAssignments()
    {
        return $this->hasMany(OjtAssignment::class, 'company_supervisor_id');
    }

    // Comment for now with make this like a status online, offline, away, etc. in the future
    // public function statusBadgeColor(): string
    // {
    //     return match ($this->status) {
    //         'Active' => 'green',
    //         'On Leave' => 'amber',
    //         'Inactive' => 'red',
    //         default => 'zinc',
    //     };
    // }
}
