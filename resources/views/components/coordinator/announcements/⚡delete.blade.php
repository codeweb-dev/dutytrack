<?php

use App\Models\Announcement;
use Flux\Flux;
use Livewire\Component;

new class extends Component {
    public Announcement $announcement;

    public function delete(): void
    {
        $this->announcement->delete();

        Flux::toast(variant: 'success', heading: 'Announcement deleted.', text: 'The announcement has been removed successfully.');

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
        <flux:heading size="lg">Delete announcement?</flux:heading>
        <flux:text class="text-zinc-500">
            You are about to permanently remove this announcement. This action cannot be undone.
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

    <flux:callout variant="danger" icon="exclamation-triangle">
        <flux:callout.heading>
            Permanent action
        </flux:callout.heading>

        <flux:callout.text>
            <p>This announcement will be permanently removed from the system and will no longer be visible to users.</p>
        </flux:callout.text>
    </flux:callout>

    <div class="flex gap-2">
        <flux:modal.close class="w-full">
            <flux:button variant="filled" class="w-full">Cancel</flux:button>
        </flux:modal.close>

        <flux:button wire:click="delete" variant="danger" icon="trash" class="w-full">
            Delete Announcement
        </flux:button>
    </div>
</div>
