<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

new class extends Component
{
    public User $student;

    public function mount(User $student): void
    {
        abort_unless((int) $student->coordinator_id === (int) Auth::id(), 403);

        $this->student = $student;
    }

    public function delete(): void
    {
        DB::transaction(function () {
            $student = $this->student->load('studentProfile');

            abort_unless((int) $student->coordinator_id === (int) Auth::id(), 403);

            if ($student->profile_photo_path) {
                Storage::disk('public')->delete($student->profile_photo_path);
            }

            $student->delete();
        });

        Flux::toast(variant: 'success', heading: 'Student deleted.', text: 'The student has been removed successfully.');

        Flux::modal('delete-student-'.$this->student->id)->close();

        $this->dispatch('student-saved');
    }
};
?>

<div class="space-y-3">
    <div class="space-y-2">
        <flux:heading size="lg">Delete student?</flux:heading>
        <flux:text class="text-zinc-500">
            You are about to permanently remove this student record. This action cannot be undone.
        </flux:text>
    </div>

    <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center gap-4">
            <flux:avatar size="lg" :src="$student->profile_photo_url" :name="$student->display_name" />

            <div class="min-w-0">
                <div class="text-base font-semibold text-zinc-900 dark:text-white">
                    {{ $student->display_name }}
                </div>
                <div class="text-sm text-zinc-500">
                    {{ $student->email }}
                </div>
                <div class="mt-1 text-sm text-zinc-400">
                    Student ID: {{ $student->studentProfile?->student_id ?? '' }}
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
                Deleting this student will remove the account, academic profile, and uploaded profile photo from
                storage.
                This cannot be reversed.
            </p>
        </flux:callout.text>
    </flux:callout>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl bg-zinc-50 p-4 dark:bg-zinc-800/50">
            <div class="text-xs uppercase tracking-wide text-zinc-400">Student ID</div>
            <div class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">
                {{ $student->studentProfile?->student_id ?? '' }}
            </div>
        </div>

        <div class="rounded-2xl bg-zinc-50 p-4 dark:bg-zinc-800/50">
            <div class="text-xs uppercase tracking-wide text-zinc-400">Course</div>
            <div class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">
                {{ $student->studentProfile?->course ?? '' }}
            </div>
        </div>

        <div class="rounded-2xl bg-zinc-50 p-4 dark:bg-zinc-800/50">
            <div class="text-xs uppercase tracking-wide text-zinc-400">OJT Hours</div>
            <div class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">
                {{ \App\Support\HourFormatter::format($student->studentProfile?->ojt_required_hours ?? 0) }}
            </div>
        </div>
    </div>

    <div class="flex gap-2">
        <flux:modal.close class="w-full">
            <flux:button variant="filled" class="w-full">Cancel</flux:button>
        </flux:modal.close>

        <flux:button wire:click="delete" variant="danger" icon="trash" class="w-full">
            Delete Student
        </flux:button>
    </div>
</div>
