<?php

use App\Models\AuditLog;
use Flux\Flux;
use Livewire\Component;

new class extends Component {
    public AuditLog $log;

    public function delete(): void
    {
        $logId = $this->log->id;

        $this->log->delete();

        Flux::toast(variant: 'success', heading: 'Audit log archived.', text: 'It can be restored from the Archive.');

        Flux::modal('delete-audit-log-' . $logId)->close();

        $this->dispatch('audit-log-deleted');
    }

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
    <div class="space-y-2">
        <flux:heading size="lg">Archive audit log?</flux:heading>
        <flux:text class="text-zinc-500">
            This entry will be hidden from the active audit log and can be restored from the Archive.
        </flux:text>
    </div>

    <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <div class="text-base font-semibold text-zinc-900 dark:text-white">
                        {{ $log->actor_name ?: 'System' }}
                    </div>
                    <div class="text-sm text-zinc-500">
                        {{ $log->actor_role ?: '' }}
                    </div>
                </div>

                <flux:badge :color="$this->badgeColor($log->action)" inset="top bottom">
                    {{ str($log->action)->replace('_', ' ')->title() }}
                </flux:badge>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <div class="text-xs uppercase tracking-wide text-zinc-400">Module</div>
                    <div class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                        {{ $log->module ?: '' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs uppercase tracking-wide text-zinc-400">Target</div>
                    <div class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                        {{ $log->target_label ?: '' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs uppercase tracking-wide text-zinc-400">Date & Time</div>
                    <div class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                        {{ $log->created_at?->format('M d, Y h:i A') ?? '' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs uppercase tracking-wide text-zinc-400">Description</div>
                    <div class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                        {{ $log->description ?: 'No description provided.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-2">
        <flux:spacer />

        <flux:modal.close>
            <flux:button variant="ghost">Cancel</flux:button>
        </flux:modal.close>

        <flux:button wire:click="delete" variant="filled" icon="archive-box">
            Archive Record
        </flux:button>
    </div>
</div>
