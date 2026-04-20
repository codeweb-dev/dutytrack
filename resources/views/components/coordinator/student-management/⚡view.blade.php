<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public User $student;

    public function mount(User $student): void
    {
        abort_unless((int) $student->coordinator_id === (int) Auth::id(), 403);

        $this->student = $student->load('studentProfile', 'coordinator');
    }
};
?>

<div class="space-y-6">
    <div>
        <flux:heading size="lg">Student Profile</flux:heading>
        <flux:text class="mt-1 text-zinc-500">
            Complete student information and academic details.
        </flux:text>
    </div>

    <div
        class="overflow-hidden rounded-3xl border border-zinc-200 bg-linear-to-r from-zinc-50 to-white dark:border-zinc-800 dark:from-zinc-900 dark:to-zinc-950">
        <div class="flex flex-col gap-6 p-6 md:flex-row md:items-center">
            <flux:avatar size="xl" :src="$student->profile_photo_url" :name="$student->name" />

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h3 class="text-2xl font-semibold tracking-tight">
                        {{ $student->name }}
                    </h3>

                    <flux:badge color="red" icon="check-badge" inset="top bottom">
                        Student
                    </flux:badge>
                </div>

                <div class="mt-3 grid gap-3 text-sm text-zinc-600 dark:text-zinc-300 md:grid-cols-2">
                    <div
                        class="rounded-2xl bg-white/80 px-4 py-3 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900/80 dark:ring-zinc-800">
                        <div class="text-xs uppercase tracking-wide text-zinc-400">Email
                            Address</div>
                        <div class="mt-1 font-medium text-zinc-900 dark:text-white">
                            {{ $student->email }}
                        </div>
                    </div>

                    <div
                        class="rounded-2xl bg-white/80 px-4 py-3 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900/80 dark:ring-zinc-800">
                        <div class="text-xs uppercase tracking-wide text-zinc-400">
                            Contact Number</div>
                        <div class="mt-1 font-medium text-zinc-900 dark:text-white">
                            {{ $student->contact_number ? '+63 ' . $student->contact_number : 'No contact number' }}
                        </div>
                    </div>

                    <div
                        class="rounded-2xl bg-white/80 px-4 py-3 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900/80 dark:ring-zinc-800 md:col-span-2">
                        <div class="text-xs uppercase tracking-wide text-zinc-400">
                            Assigned Coordinator</div>
                        <div class="mt-1 font-medium text-zinc-900 dark:text-white">
                            {{ $student->coordinator?->name ?? 'Unassigned' }} | {{ $student->coordinator?->professorProfile->department ?? '—' }}
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
                    <span class="text-sm text-zinc-500">First Name</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $student->first_name ?? '—' }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">Middle Name</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $student->middle_name ?: '—' }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">Last Name</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $student->last_name ?? '—' }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-4">
                    <span class="text-sm text-zinc-500">Account Role</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        Student
                    </span>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-4">
                <h4 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">
                    Academic Information
                </h4>
            </div>

            <div class="space-y-4">
                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">Student ID</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $student->studentProfile?->student_id ?? '—' }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">Course</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $student->studentProfile?->course ?? '—' }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">Year Level</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $student->studentProfile?->year_level ?? '—' }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">Section</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $student->studentProfile?->section ?? '—' }}
                    </span>
                </div>

                <div class="flex items-start justify-between gap-4">
                    <span class="text-sm text-zinc-500">OJT Required Hours</span>
                    <span class="text-sm font-medium text-right text-zinc-900 dark:text-white">
                        {{ $student->studentProfile?->ojt_required_hours ?? 0 }} hrs
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
                <div class="text-xs uppercase tracking-wide text-zinc-400">Student ID
                </div>
                <div class="mt-2 text-base font-semibold text-zinc-900 dark:text-white">
                    {{ $student->studentProfile?->student_id ?? '—' }}
                </div>
            </div>

            <div class="rounded-2xl bg-zinc-50 p-4 dark:bg-zinc-800/50">
                <div class="text-xs uppercase tracking-wide text-zinc-400">Academic
                </div>
                <div class="mt-2 text-base font-semibold text-zinc-900 dark:text-white">
                    {{ $student->studentProfile?->course ?? '—' }}
                </div>
                <div class="text-sm text-zinc-500">
                    Year {{ $student->studentProfile?->year_level ?? '—' }}
                    @if ($student->studentProfile?->section)
                        • Section {{ $student->studentProfile->section }}
                    @endif
                </div>
            </div>

            <div class="rounded-2xl bg-zinc-50 p-4 dark:bg-zinc-800/50">
                <div class="text-xs uppercase tracking-wide text-zinc-400">OJT
                    Requirement</div>
                <div class="mt-2 text-base font-semibold text-zinc-900 dark:text-white">
                    {{ $student->studentProfile?->ojt_required_hours ?? 0 }} hrs
                </div>
            </div>
        </div>
    </div>
</div>
