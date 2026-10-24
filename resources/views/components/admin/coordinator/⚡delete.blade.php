<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

new class extends Component {
    public User $coordinator;

    public function delete(): void
    {
        DB::transaction(function () {
            $coordinator = $this->coordinator->load('coordinatorProfile');

            if ($coordinator->profile_photo_path) {
                Storage::disk('public')->delete($coordinator->profile_photo_path);
            }

            $coordinator->delete();
        });

        Flux::toast(variant: 'success', heading: 'Coordinator deleted.', text: 'The coordinator record has been removed successfully.');

        Flux::modal('delete-coordinator-' . $this->coordinator->id)->close();

        $this->dispatch('coordinator-saved');
    }
};
?>

<div class="space-y-6">
    <div class="space-y-2">
        <flux:heading size="lg">Delete coordinator?</flux:heading>
        <flux:text class="text-zinc-500">
            You are about to permanently remove this coordinator record. This action cannot be undone.
        </flux:text>
    </div>

    <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center gap-4">
            <flux:avatar size="lg" :src="$coordinator->profile_photo_url" :name="$coordinator->name" />

            <div class="min-w-0">
                <div class="text-base font-semibold text-zinc-900 dark:text-white">
                    {{ $coordinator->name }}
                </div>
                <div class="text-sm text-zinc-500">
                    {{ $coordinator->email }}
                </div>
            </div>
        </div>
    </div>

    <flux:callout variant="danger" icon="exclamation-triangle">
        <flux:callout.heading>
            Permanent action
        </flux:callout.heading>

        <flux:callout.text>
            <p>
                Deleting this coordinator will remove the account, profile, and uploaded profile photo from storage.
                This cannot be reversed.
            </p>
        </flux:callout.text>
    </flux:callout>

    <div class="flex gap-2">
        <flux:spacer />

        <flux:modal.close>
            <flux:button variant="ghost">Cancel</flux:button>
        </flux:modal.close>

        <flux:button wire:click="delete" variant="danger" icon="trash">
            Delete Record
        </flux:button>
    </div>
</div>
