<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

            $student->delete();
            DB::table('sessions')->where('user_id', $student->id)->delete();
        });

        Flux::toast(variant: 'success', heading: 'Student archived.', text: 'The student can be restored from the Archive.');

        Flux::modal('delete-student-'.$this->student->id)->close();

        $this->dispatch('student-saved');
    }
};
?>

<div class="space-y-3">
    <div class="space-y-2">
        <flux:heading size="lg">Archive student?</flux:heading>
        <flux:text class="text-zinc-500">
            This student will be hidden from active lists. Their account and data can be restored from the Archive.
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

        <flux:button wire:click="delete" variant="filled" icon="archive-box" class="w-full">
            Archive Student
        </flux:button>
    </div>
</div>
