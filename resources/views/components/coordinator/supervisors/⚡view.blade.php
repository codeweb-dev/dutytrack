<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public User $supervisor;

    public function mount(User $supervisor): void
    {
        $this->supervisor = $supervisor;
        $actor = Auth::user();

        abort_unless(
            $actor && ($actor->hasRole('ojt_coordinator') && (int) $supervisor->coordinator_id === (int) $actor->id),
            403
        );
    }
};
?>

<div class="space-y-6">
    <div>
        <flux:heading size="lg">Supervisor Profile</flux:heading>
        <flux:text class="mt-1 text-zinc-500">
            Complete supervisor and company information.
        </flux:text>
    </div>

    <div
        class="overflow-hidden rounded-3xl border border-zinc-200 bg-linear-to-r from-zinc-50 to-white dark:border-zinc-800 dark:from-zinc-900 dark:to-zinc-950">
        <div class="flex flex-col gap-6 p-6 md:flex-row md:items-center">
            <flux:avatar size="xl" :src="$supervisor->profile_photo_url" :name="$supervisor->display_name" />

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h3 class="text-2xl font-semibold tracking-tight">{{ $supervisor->display_name }}</h3>

                    <flux:badge color="red" icon="check-badge" inset="top bottom">
                        Supervisor
                    </flux:badge>
                </div>

                <div class="mt-3 grid gap-3 text-sm text-zinc-600 dark:text-zinc-300 md:grid-cols-2">
                    <div
                        class="rounded-2xl bg-white/80 px-4 py-3 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900/80 dark:ring-zinc-800">
                        <div class="text-xs uppercase tracking-wide text-zinc-400">Email Address</div>
                        <div class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $supervisor->email }}</div>
                    </div>

                    <div
                        class="rounded-2xl bg-white/80 px-4 py-3 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900/80 dark:ring-zinc-800">
                        <div class="text-xs uppercase tracking-wide text-zinc-400">Contact Number</div>
                        <div class="mt-1 font-medium text-zinc-900 dark:text-white">
                            {{ $supervisor->contact_number ? '+63 ' . $supervisor->contact_number : 'No contact number' }}
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
                    Supervisor Information
                </h4>
            </div>

            <div class="space-y-4">
                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">Position and Department</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $supervisor->supervisorProfile?->position_department ?? '' }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">Extension Name</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $supervisor->supervisorProfile?->extension_name ?? '' }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-4">
                    <span class="text-sm text-zinc-500">Full Name</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $supervisor->display_name }}
                    </span>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-4">
                <h4 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">
                    Company / Institution Information
                </h4>
            </div>

            <div class="space-y-4">
                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">Company / Institution</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $supervisor->supervisorProfile?->company_name ?? '' }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-4">
                    <span class="text-sm text-zinc-500">Company Address</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white max-w-[18rem]">
                        {{ $supervisor->supervisorProfile?->company_address ?? 'No address provided' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mb-4">
            <h4 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">
                Quick Summary
            </h4>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl bg-zinc-50 p-4 dark:bg-zinc-800/50">
                <div class="text-xs uppercase tracking-wide text-zinc-400">Company / Institution</div>
                <div class="mt-2 text-base font-semibold text-zinc-900 dark:text-white">
                    {{ $supervisor->supervisorProfile?->company_name ?? '' }}
                </div>
            </div>

            <div class="rounded-2xl bg-zinc-50 p-4 dark:bg-zinc-800/50">
                <div class="text-xs uppercase tracking-wide text-zinc-400">Contact</div>
                <div class="mt-2 text-base font-semibold text-zinc-900 dark:text-white">
                    {{ $supervisor->contact_number ? '+63 ' . $supervisor->contact_number : 'N/A' }}
                </div>
            </div>
        </div>
    </div>
</div>
