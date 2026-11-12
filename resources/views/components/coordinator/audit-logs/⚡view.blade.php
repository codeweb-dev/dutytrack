<?php

use App\Models\AuditLog;
use Livewire\Component;

new class extends Component {
    public AuditLog $log;


    public function formatValue(mixed $value): string
    {
        return match (true) {
            is_array($value) => json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $value === null || $value === '' => '—',
            is_bool($value) => $value ? 'Yes' : 'No',
            default => (string) $value,
        };
    }
};
?>

<div class="space-y-4">
    @php
        $sections = [
            'Actor Information' => [
                'Name' => $log->actor_name ?: 'System',
                'Role' => $log->actor_role ?: 'System',
                'Date & Time' => $log->created_at?->format('M d, Y h:i A'),
            ],
            'Activity Information' => [
                'Module' => $log->module,
                'Action' => str($log->action)->replace('_', ' ')->title(),
                'Target' => $log->target_label,
                'Target Type' => trim(class_basename($log->target_type ?? '').($log->target_id ? ' #'.$log->target_id : '')),
            ],
        ];
        $changes = ['Old Values' => $log->old_values, 'New Values' => $log->new_values];
    @endphp

    <div class="flex items-center gap-3 border-b border-zinc-200 pb-4 dark:border-zinc-800">
        <flux:avatar size="lg" :src="$log->actor?->profile_photo_url" :name="$log->actor_name ?: 'System'" />
        <div class="min-w-0 flex-1">
            <h3 class="truncate text-lg font-semibold text-zinc-900 dark:text-white">{{ $log->actor_name ?: 'System' }}</h3>
        </div>
        <flux:badge :color="\App\Support\BadgeColor::for($log->action)" size="sm">{{ str($log->action)->replace('_', ' ')->title() }}</flux:badge>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ($sections as $heading => $fields)
            <section class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50">
                <h4 class="text-base font-semibold text-zinc-900 dark:text-white">{{ $heading }}</h4>
                <dl class="mt-4 grid gap-y-4">
                    @foreach ($fields as $label => $value)
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $label }}</dt>
                            <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ $this->formatValue((string) $value) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endforeach

        <section class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50 sm:col-span-2">
            <h4 class="text-base font-semibold text-zinc-900 dark:text-white">Request Information</h4>
            <dl class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">IP Address</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ $log->ip_address ?: '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">User Agent</dt>
                    <dd class="mt-1 break-all text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ $log->user_agent ?: '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Description</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ $log->description ?: 'No description provided.' }}</dd>
                </div>
            </dl>
        </section>

        @foreach ($changes as $heading => $values)
            <section class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50">
                <h4 class="text-base font-semibold text-zinc-900 dark:text-white">{{ $heading }}</h4>
                @if (! empty($values))
                    <dl class="mt-4 grid gap-y-4">
                        @foreach ($values as $key => $value)
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ str($key)->replace('_', ' ')->title() }}</dt>
                                <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ $this->formatValue($value) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <flux:text class="mt-4">No {{ str($heading)->lower() }} recorded.</flux:text>
                @endif
            </section>
        @endforeach

        @if (! empty($log->meta))
            <section class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50 sm:col-span-2">
                <h4 class="text-base font-semibold text-zinc-900 dark:text-white">Meta Information</h4>
                <dl class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    @foreach ($log->meta as $key => $value)
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ str($key)->replace('_', ' ')->title() }}</dt>
                            <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ $this->formatValue($value) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endif
    </div>
</div>
