<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * One badge color per meaning, used app-wide. A color belongs to exactly one meaning, so never reuse one for another.
 */
class BadgeColor
{
    protected const MAP = [
        // Pending / not started
        'pending' => 'yellow',
        'pending_approval' => 'yellow',
        'not_started' => 'yellow',

        // Waiting for review
        'for_checking' => 'sky',
        'awaiting_approval' => 'sky',
        'in_review' => 'sky',

        'in_progress' => 'blue',

        // Needs fixing
        'revision' => 'orange',
        'incomplete' => 'orange',

        'missing' => 'rose',
        'not_submitted' => 'rose',

        // Critical / failed
        'absent' => 'red',
        'overdue' => 'red',
        'urgent' => 'red',
        'deleted' => 'red',
        'disabled' => 'red',
        'high' => 'red',
        'unread' => 'red',

        // Warning
        'late' => 'amber',
        'due_soon' => 'amber',
        'warning' => 'amber',
        'reset_password' => 'amber',

        // Finished / approved
        'approved' => 'green',
        'complete' => 'green',
        'completed' => 'green',
        'done' => 'green',
        'submitted' => 'green',
        'published' => 'green',

        // On / active
        'active' => 'emerald',
        'enabled' => 'emerald',
        'activated' => 'emerald',
        'created' => 'emerald',

        'present' => 'lime',
        'attached' => 'lime',

        // Changed / system
        'updated' => 'cyan',
        'maintenance' => 'cyan',

        'assigned' => 'teal',
        'reassigned' => 'teal',

        'coordinator' => 'purple',
        'supervisor' => 'indigo',
        'student' => 'violet',

        'internal' => 'fuchsia',
        'external' => 'pink',
    ];

    // Accepts keys ("in_review") or labels ("Pending Approval"); anything unmapped is neutral zinc.
    public static function for(?string $status): string
    {
        return self::MAP[Str::snake((string) $status)] ?? 'zinc';
    }
}
