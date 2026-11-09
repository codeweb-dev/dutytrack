<?php

use App\Models\Announcement;
use Livewire\Component;

new class extends Component {
    public Announcement $announcement;

    public function badgeColor(string $type): string
    {
        return match ($type) {
            'warning' => 'amber',
            'urgent' => 'red',
            'maintenance' => 'sky',
            default => 'zinc',
        };
    }
};
?>

<div class="space-y-4">
    <div class="flex items-center gap-3 border-b border-zinc-200 pb-4 dark:border-zinc-800">
        <flux:avatar size="lg" icon="megaphone" />
        <div class="min-w-0 flex-1">
            <h3 class="truncate text-lg font-semibold text-zinc-900 dark:text-white">{{ $announcement->title }}</h3>
        </div>
        <flux:badge :color="$this->badgeColor($announcement->type)" size="sm">{{ ucfirst($announcement->type) }}</flux:badge>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <section class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50 sm:col-span-2">
            <h4 class="text-base font-semibold text-zinc-900 dark:text-white">Announcement Details</h4>
            <dl class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Title</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ $announcement->title }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Type</dt>
                    <dd class="mt-1 text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ ucfirst($announcement->type) }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Message</dt>
                    <dd class="mt-1 whitespace-pre-line break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ $announcement->message }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50 sm:col-span-2">
            <h4 class="text-base font-semibold text-zinc-900 dark:text-white">Announcement Metadata</h4>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Created</dt>
                    <dd class="mt-1 text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $announcement->created_at?->format('M d, Y h:i A') ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Last Updated</dt>
                    <dd class="mt-1 text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $announcement->updated_at?->format('M d, Y h:i A') ?? '—' }}
                    </dd>
                </div>
            </dl>
        </section>
    </div>
</div>
