<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'actor_id',
        'actor_name',
        'actor_role',
        'module',
        'action',
        'target_type',
        'target_id',
        'target_label',
        'description',
        'old_values',
        'new_values',
        'meta',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'meta' => 'array',
    ];

    // A coordinator's own actions, plus unattended ones (e.g. sign-ups) about their own users.
    public function scopeVisibleTo(Builder $query, int $coordinatorId): Builder
    {
        return $query->where(fn (Builder $query) => $query
            ->where('actor_id', $coordinatorId)
            ->orWhere(fn (Builder $query) => $query
                ->whereNull('actor_id')
                ->where('target_type', User::class)
                ->whereIn('target_id', User::withTrashed()->select('id')->where('coordinator_id', $coordinatorId))));
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id')->withTrashed();
    }
}
