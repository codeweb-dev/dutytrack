<?php

use App\Models\AuditLog;
use Livewire\Component;

new class extends Component {
    public AuditLog $log;

    public function badgeColor(string $action): string
    {
        return match ($action) {
            'created' => 'green',
            'updated' => 'blue',
            'deleted' => 'red',
            'deactivated' => 'zinc',
            'activated' => 'green',
            'reset_password' => 'amber',
            'assigned' => 'sky',
            'reassigned' => 'purple',
            'published' => 'green',
            'archived' => 'zinc',
            default => 'zinc',
        };
    }
};
?>

<div class="space-y-6">
    <div>
        <flux:heading size="lg">Audit Log Details</flux:heading>
        <flux:text class="mt-1 text-zinc-500">
            Complete activity details for this audit log entry.
        </flux:text>
    </div>

    <div
        class="overflow-hidden rounded-3xl border border-zinc-200 bg-linear-to-r from-zinc-50 to-white dark:border-zinc-800 dark:from-zinc-900 dark:to-zinc-950">
        <div class="flex flex-col gap-6 p-6 md:flex-row md:items-center">
            <flux:avatar size="xl" :name="$log->actor_name ?: 'System'" />

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h3 class="text-2xl font-semibold tracking-tight">
                        {{ $log->actor_name ?: 'System' }}
                    </h3>

                    <flux:badge :color="$this->badgeColor($log->action)" inset="top bottom">
                        {{ str($log->action)->replace('_', ' ')->title() }}
                    </flux:badge>
                </div>

                <div class="mt-3 grid gap-3 text-sm text-zinc-600 dark:text-zinc-300 md:grid-cols-2">
                    <div
                        class="rounded-2xl bg-white/80 px-4 py-3 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900/80 dark:ring-zinc-800">
                        <div class="text-xs uppercase tracking-wide text-zinc-400">Role
                        </div>
                        <div class="mt-1 font-medium text-zinc-900 dark:text-white">
                            {{ $log->actor_role ?: 'System' }}
                        </div>
                    </div>

                    <div
                        class="rounded-2xl bg-white/80 px-4 py-3 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900/80 dark:ring-zinc-800">
                        <div class="text-xs uppercase tracking-wide text-zinc-400">Date
                            & Time</div>
                        <div class="mt-1 font-medium text-zinc-900 dark:text-white">
                            {{ $log->created_at?->format('M d, Y h:i A') ?? '—' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-4">
                <h4 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">
                    Activity Information
                </h4>
            </div>

            <div class="space-y-4">
                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">Module</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $log->module ?: '—' }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">Action</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ str($log->action)->replace('_', ' ')->title() }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">Target</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $log->target_label ?: '—' }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-4">
                    <span class="text-sm text-zinc-500">Target Type</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ class_basename($log->target_type ?? '') ?: '—' }}
                        @if ($log->target_id)
                            <span class="text-zinc-500">#{{ $log->target_id }}</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-4">
                <h4 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">
                    Request Information
                </h4>
            </div>

            <div class="space-y-4">
                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">IP Address</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $log->ip_address ?: '—' }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-4">
                    <span class="text-sm text-zinc-500">User Agent</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white break-all">
                        {{ $log->user_agent ?: '—' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mb-4">
            <h4 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">
                Description
            </h4>
        </div>

        <p class="text-sm text-zinc-700 dark:text-zinc-300">
            {{ $log->description ?: 'No description provided.' }}
        </p>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-4">
                <h4 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">
                    Old Values
                </h4>
            </div>

            @if (!empty($log->old_values))
                <div class="space-y-3">
                    @foreach ($log->old_values as $key => $value)
                        <div
                            class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                            <span class="text-sm text-zinc-500">
                                {{ str($key)->replace('_', ' ')->title() }}
                            </span>

                            <span
                                class="max-w-[60%] text-sm font-medium text-right text-zinc-900 dark:text-white wrap-break-word">
                                @if (is_array($value))
                                    {{ json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}
                                @elseif ($value === null || $value === '')
                                    —
                                @elseif (is_bool($value))
                                    {{ $value ? 'Yes' : 'No' }}
                                @else
                                    {{ $value }}
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-zinc-500">No old values recorded.</p>
            @endif
        </div>

        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-4">
                <h4 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">
                    New Values
                </h4>
            </div>

            @if (!empty($log->new_values))
                <div class="space-y-3">
                    @foreach ($log->new_values as $key => $value)
                        <div
                            class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                            <span class="text-sm text-zinc-500">
                                {{ str($key)->replace('_', ' ')->title() }}
                            </span>

                            <span
                                class="max-w-[60%] text-sm font-medium text-right text-zinc-900 dark:text-white wrap-break-word">
                                @if (is_array($value))
                                    {{ json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}
                                @elseif ($value === null || $value === '')
                                    —
                                @elseif (is_bool($value))
                                    {{ $value ? 'Yes' : 'No' }}
                                @else
                                    {{ $value }}
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-zinc-500">No new values recorded.</p>
            @endif
        </div>
    </div>

    @if (!empty($log->meta))
        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-4">
                <h4 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">
                    Meta Information
                </h4>
            </div>

            <div class="space-y-3">
                @foreach ($log->meta as $key => $value)
                    <div
                        class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                        <span class="text-sm text-zinc-500">
                            {{ str($key)->replace('_', ' ')->title() }}
                        </span>

                        <span
                            class="max-w-[60%] text-sm font-medium text-right text-zinc-900 dark:text-white wrap-break-word">
                            @if (is_array($value))
                                {{ json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}
                            @elseif ($value === null || $value === '')
                                —
                            @elseif (is_bool($value))
                                {{ $value ? 'Yes' : 'No' }}
                            @else
                                {{ $value }}
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
