<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function log(
        string $module,
        string $action,
        ?Model $target = null,
        ?string $targetLabel = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $meta = null,
    ): AuditLog {
        $actor = Auth::user();

        return AuditLog::create([
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name,
            'actor_role' => $actor && method_exists($actor, 'getRoleNames')
                ? $actor->getRoleNames()->first()
                : null,

            'module' => $module,
            'action' => $action,

            'target_type' => $target ? $target::class : null,
            'target_id' => $target?->getKey(),
            'target_label' => $targetLabel,

            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'meta' => $meta,

            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
