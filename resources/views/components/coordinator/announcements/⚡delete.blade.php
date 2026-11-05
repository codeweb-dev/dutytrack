<?php

use App\Models\Announcement;
use Flux\Flux;
use Livewire\Component;

new class extends Component {
    public Announcement $announcement;

    public function delete(): void
    {
        $this->announcement->delete();

        Flux::toast(variant: 'success', heading: 'Announcement archived.', text: 'It can be restored from the Archive.');

        Flux::modal('delete-announcement-' . $this->announcement->id)->close();

        $this->dispatch('announcement-saved');
    }

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
    <div class="space-y-2">
        <flux:heading size="lg">Archive announcement?</flux:heading>
        <flux:text class="text-zinc-500">
            This announcement will be hidden from users and can be restored from the Archive.
        </flux:text>
    </div>

    <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-base font-semibold text-zinc-900 dark:text-white">
                    {{ $announcement->title }}
                </div>
                <div class="mt-2 text-sm text-zinc-500 line-clamp-3">
                    {{ $announcement->message }}
                </div>
            </div>

            <flux:badge :color="$this->badgeColor($announcement->type)" inset="top bottom">
                {{ ucfirst($announcement->type) }}
            </flux:badge>
        </div>
    </div>

    <div class="flex gap-2">
        <flux:modal.close class="w-full">
            <flux:button variant="filled" class="w-full">Cancel</flux:button>
        </flux:modal.close>

        <flux:button wire:click="delete" variant="filled" icon="archive-box" class="w-full">
            Archive Announcement
        </flux:button>
    </div>
</div>
