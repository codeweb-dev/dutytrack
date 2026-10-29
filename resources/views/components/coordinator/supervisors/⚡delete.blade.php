<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

new class extends Component {
    public User $supervisor;

    public function mount(User $supervisor): void
    {
        $this->supervisor = $supervisor;
        $this->authorizeSupervisor();
    }

    protected function authorizeSupervisor(): void
    {
        $actor = Auth::user();
        $supervisor = User::query()->findOrFail($this->supervisor->id);

        abort_unless(
            $actor && ($actor->hasRole('ojt_coordinator') && (int) $supervisor->coordinator_id === (int) $actor->id),
            403
        );
    }

    public function delete(): void
    {
        $this->authorizeSupervisor();

        DB::transaction(function () {
            $supervisor = $this->supervisor->load('supervisorProfile');

            if ($supervisor->profile_photo_path) {
                Storage::disk('public')->delete($supervisor->profile_photo_path);
            }

            $supervisor->delete();
        });

        Flux::toast(variant: 'success', heading: 'Supervisor deleted.', text: 'The supervisor has been removed successfully.');

        Flux::modal('delete-supervisor-' . $this->supervisor->id)->close();

        $this->dispatch('supervisor-saved');
    }
};
?>

<div class="space-y-6">
    <div class="space-y-2">
        <flux:heading size="lg">Delete supervisor?</flux:heading>
        <flux:text class="text-zinc-500">
            You are about to permanently remove this supervisor record. This action cannot be undone.
        </flux:text>
    </div>

    <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center gap-4">
            <flux:avatar size="lg" :src="$supervisor->profile_photo_url" :name="$supervisor->name" />

            <div class="min-w-0">
                <div class="text-base font-semibold text-zinc-900 dark:text-white">
                    {{ $supervisor->name }}
                </div>
                <div class="text-sm text-zinc-500">
                    {{ $supervisor->email }}
                </div>
                <div class="mt-1 text-sm text-zinc-400">
                    Supervisor ID: {{ $supervisor->supervisorProfile?->supervisor_id ?? '' }}
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
                Deleting this supervisor will remove the account, supervisor profile, and uploaded profile photo from
                storage.
                This cannot be reversed.
            </p>
        </flux:callout.text>
    </flux:callout>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-2xl bg-zinc-50 p-4 dark:bg-zinc-800/50">
            <div class="text-xs uppercase tracking-wide text-zinc-400">Company / Institution</div>
            <div class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">
                {{ $supervisor->supervisorProfile?->company_name ?? '' }}
            </div>
        </div>

        <div class="rounded-2xl bg-zinc-50 p-4 dark:bg-zinc-800/50">
            <div class="text-xs uppercase tracking-wide text-zinc-400">Position and Department</div>
            <div class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">
                {{ $supervisor->supervisorProfile?->position_department ?? '' }}
            </div>
        </div>

        <div class="rounded-2xl bg-zinc-50 p-4 dark:bg-zinc-800/50">
            <div class="text-xs uppercase tracking-wide text-zinc-400">Contact Number</div>
            <div class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">
                {{ $supervisor->contact_number ? '+63 ' . $supervisor->contact_number : 'N/A' }}
            </div>
        </div>
    </div>

    <div class="flex gap-2">
        <flux:spacer />

        <flux:modal.close>
            <flux:button variant="ghost">Cancel</flux:button>
        </flux:modal.close>

        <flux:button wire:click="delete" variant="danger" icon="trash">
            Delete Supervisor
        </flux:button>
    </div>
</div>
