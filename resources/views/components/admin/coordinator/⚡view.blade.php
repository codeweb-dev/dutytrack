<?php

use App\Models\User;
use Livewire\Component;

new class extends Component {
    public User $coordinator;
};
?>

<div class="space-y-6">
    <div>
        <flux:heading size="lg">Coordinator Profile</flux:heading>
        <flux:text class="mt-1 text-zinc-500">
            Complete coordinator, department, and program information.
        </flux:text>
    </div>

    <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex min-w-0 flex-col gap-5 p-4 sm:p-6 md:flex-row md:items-start">
            <flux:avatar size="xl" :src="$coordinator->profile_photo_url" :name="$coordinator->name" />

            <div class="min-w-0 flex-1 space-y-4">
                <div class="flex flex-wrap items-center gap-3">
                    <h3 class="min-w-0 break-words text-2xl font-semibold tracking-tight [overflow-wrap:anywhere]">
                        {{ $coordinator->name }}
                    </h3>

                    <flux:badge icon="check-badge" color="green" inset="top bottom">
                        OJT Coordinator
                    </flux:badge>
                </div>

                <div class="grid min-w-0 gap-3 text-sm text-zinc-600 dark:text-zinc-300">
                    @foreach ([
                        'Email Address' => $coordinator->email,
                        'Contact Number' => $coordinator->contact_number ? '+63 ' . $coordinator->contact_number : 'No contact number',
                        'Department' => $coordinator->coordinatorProfile?->department ?? '',
                        'Program' => $coordinator->coordinatorProfile?->program ?? '',
                    ] as $label => $value)
                        <div class="min-w-0 rounded-2xl bg-white/80 px-4 py-3 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900/80 dark:ring-zinc-800">
                            <div class="text-xs uppercase tracking-wide text-zinc-400">{{ $label }}</div>
                            <div class="mt-1 min-w-0 break-words font-medium text-zinc-900 [overflow-wrap:anywhere] dark:text-white">
                                {{ $value }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Personal Information</h4>

            <div class="space-y-4">
                @foreach ([
                    'First Name' => $coordinator->first_name ?? '',
                    'Middle Name' => $coordinator->middle_name ?: '',
                    'Last Name' => $coordinator->last_name ?? '',
                ] as $label => $value)
                    <div class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 last:border-0 last:pb-0 dark:border-zinc-800">
                        <span class="text-sm text-zinc-500">{{ $label }}</span>
                        <span class="break-words text-right text-sm font-medium text-zinc-900 dark:text-white">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Position Information</h4>

            <div class="flex items-start justify-between gap-4">
                <span class="text-sm text-zinc-500">Position</span>
                <span class="text-right text-sm font-medium text-zinc-900 dark:text-white">OJT Coordinator</span>
            </div>
        </div>
    </div>
</div>
