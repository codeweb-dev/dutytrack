<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    protected $fillable = [
        'ojt_assignment_id',
        'student_id',
        'participant_id',
        'participant_role',
    ];

    // A supervisor chat stays open only while that supervisor is the student's current one.
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where(fn (Builder $query) => $query
            ->where('participant_role', '!=', 'supervisor')
            ->orWhereExists(fn ($assignments) => $assignments->selectRaw('1')->from('ojt_assignments')
                ->whereColumn('ojt_assignments.student_id', 'chat_conversations.student_id')
                ->whereColumn('ojt_assignments.company_supervisor_id', 'chat_conversations.participant_id')
                ->whereNull('ojt_assignments.deleted_at')));
    }

    public function assignment()
    {
        return $this->belongsTo(OjtAssignment::class, 'ojt_assignment_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function participant()
    {
        return $this->belongsTo(User::class, 'participant_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class)->withTrashed()->latestOfMany();
    }
}
