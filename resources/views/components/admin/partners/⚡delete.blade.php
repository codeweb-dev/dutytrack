<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

new class extends Component {
    public User $professor;

    public function delete(): void
    {
        DB::transaction(function () {
            $professor = $this->professor->load('professorProfile');

            if ($professor->profile_photo_path) {
                Storage::disk('public')->delete($professor->profile_photo_path);
            }

            $professor->delete();
        });

        Flux::toast(variant: 'success', heading: 'Professor or coordinator deleted.', text: 'The record has been removed successfully.');

        Flux::modal('delete-professor-' . $this->professor->id)->close();

        $this->dispatch('professor-saved');
    }
};
?>

<div class="space-y-6">
    <div class="space-y-2">
        <flux:heading size="lg">Delete professor or coordinator?</flux:heading>
        <flux:text class="text-zinc-500">
            You are about to permanently remove this employee record. This action cannot be undone.
        </flux:text>
    </div>

    <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center gap-4">
            <flux:avatar size="lg" :src="$professor->profile_photo_url" :name="$professor->name" />

            <div class="min-w-0">
                <div class="text-base font-semibold text-zinc-900 dark:text-white">
                    {{ $professor->name }}
                </div>
                <div class="text-sm text-zinc-500">
                    {{ $professor->email }}
                </div>
                <div class="mt-1 text-sm text-zinc-400">
                    Employee ID: {{ $professor->professorProfile?->employee_id ?? '—' }}
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
                Deleting this employee will remove the account, professor/coordinator profile, and uploaded profile
                photo from storage.
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
