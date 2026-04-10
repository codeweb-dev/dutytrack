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

<div class="space-y-6">
    <div>
        <div class="flex items-center gap-3">
            <flux:heading size="lg">{{ $announcement->title }}</flux:heading>

            <flux:badge :color="$this->badgeColor($announcement->type)" inset="top bottom">
                {{ ucfirst($announcement->type) }}
            </flux:badge>
        </div>

        <flux:text class="mt-1 text-zinc-500">
            Created on {{ $announcement->created_at?->format('M d, Y h:i A') }}
        </flux:text>
    </div>

    <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="text-sm leading-7 text-zinc-700 dark:text-zinc-300">
            {{ $announcement->message }}
        </div>
    </div>

    <flux:callout variant="secondary" icon="megaphone">
        <flux:callout.heading>
            System Notice
        </flux:callout.heading>

        <flux:callout.text>
            <p>This announcement is visible in the system announcements module and serves as an official notice for
                users.</p>
        </flux:callout.text>
    </flux:callout>
</div>
