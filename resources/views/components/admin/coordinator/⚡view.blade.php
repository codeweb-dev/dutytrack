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
            Complete coordinator and department information.
        </flux:text>
    </div>

    <div
        class="overflow-hidden rounded-3xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-6 p-6 md:flex-row md:items-center">
            <flux:avatar size="xl" :src="$coordinator->profile_photo_url" :name="$coordinator->name" />

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h3 class="text-2xl font-semibold tracking-tight">
                        {{ $coordinator->name }}
                    </h3>

                    <flux:badge icon="check-badge" color="green" inset="top bottom">
                        OJT Coordinator
                    </flux:badge>
                </div>

                <div class="mt-3 grid gap-3 text-sm text-zinc-600 dark:text-zinc-300 md:grid-cols-2">
                    <div
                        class="rounded-2xl bg-white/80 px-4 py-3 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900/80 dark:ring-zinc-800">
                        <div class="text-xs uppercase tracking-wide text-zinc-400">Email Address</div>
                        <div class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $coordinator->email }}</div>
                    </div>

                    <div
                        class="rounded-2xl bg-white/80 px-4 py-3 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900/80 dark:ring-zinc-800">
                        <div class="text-xs uppercase tracking-wide text-zinc-400">Contact Number</div>
                        <div class="mt-1 font-medium text-zinc-900 dark:text-white">
                            {{ $coordinator->contact_number ? '+63 ' . $coordinator->contact_number : 'No contact number' }}
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
                    Personal Information
                </h4>
            </div>

            <div class="space-y-4">
                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">Employee ID</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $coordinator->coordinatorProfile?->employee_id ?? '' }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">First Name</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $coordinator->first_name ?? '' }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">Middle Name</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $coordinator->middle_name ?: '' }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-4">
                    <span class="text-sm text-zinc-500">Last Name</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $coordinator->last_name ?? '' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-4">
                <h4 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">
                    Department Information
                </h4>
            </div>

            <div class="space-y-4">
                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">Department</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $coordinator->coordinatorProfile?->department ?? '' }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-4">
                    <span class="text-sm text-zinc-500">Position</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        OJT Coordinator
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
