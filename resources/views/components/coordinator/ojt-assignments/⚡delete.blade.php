<?php

use App\Models\OjtAssignment;
use App\Support\AuditLogger;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component
{
    public OjtAssignment $assignment;

    public function mount(OjtAssignment $assignment): void
    {
        $assignment->load(['student.studentProfile', 'professor', 'companySupervisor']);

        abort_unless((int) $assignment->student?->coordinator_id === (int) auth()->id(), 403);

        $this->assignment = $assignment;
    }

    public function delete(): void
    {
        DB::transaction(function () {
            $assignment = $this->assignment->load(['student.studentProfile', 'professor', 'companySupervisor']);

            abort_unless((int) $assignment->student?->coordinator_id === (int) auth()->id(), 403);

            $assignee = $assignment->ojt_type === 'internal'
                ? $assignment->professor?->name
                : $assignment->companySupervisor?->name;

            $oldValues = [
                'student_name' => $assignment->student?->name,
                'student_number' => $assignment->student?->studentProfile?->student_id,
                'ojt_type' => $assignment->ojt_type,
                'professor_name' => $assignment->professor?->name,
                'company_supervisor_name' => $assignment->companySupervisor?->name,
                'status' => $assignment->status,
                'remarks' => $assignment->remarks,
            ];

            $label = ($assignment->student?->name ?? 'Student').' -> '.($assignee ?? 'Assignee');

            AuditLogger::log(
                module: 'OJT Assignments',
                action: 'deleted',
                target: $assignment,
                targetLabel: $label,
                description: 'Deleted an OJT assignment.',
                oldValues: $oldValues,
            );

            $assignment->delete();
        });

        Flux::toast(
            variant: 'success',
            heading: 'Assignment deleted.',
            text: 'The student assignment has been removed successfully.'
        );

        Flux::modal('delete-ojt-assignment-'.$this->assignment->id)->close();

        $this->dispatch('assignment-saved');
    }
};
?>

<div class="space-y-4">
    <div class="space-y-2">
        <flux:heading size="lg">Delete assignment?</flux:heading>
        <flux:text class="text-zinc-500">
            You are about to remove this student assignment. The student will become unassigned again.
        </flux:text>
    </div>

    <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="space-y-3">
            <div>
                <div class="text-xs uppercase tracking-wide text-zinc-400">Student</div>
                <div class="mt-1 text-base font-semibold text-zinc-900 dark:text-white">
                    {{ $assignment->student?->name ?? '' }}
                </div>
                <div class="text-sm text-zinc-500">
                    {{ $assignment->student?->studentProfile?->student_id ?? '' }}
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl bg-zinc-50 p-4 dark:bg-zinc-800/50">
                    <div class="text-xs uppercase tracking-wide text-zinc-400">Type</div>
                    <div class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">
                        {{ ucfirst($assignment->ojt_type) }}
                    </div>
                </div>

                <div class="rounded-2xl bg-zinc-50 p-4 dark:bg-zinc-800/50">
                    <div class="text-xs uppercase tracking-wide text-zinc-400">Assigned To</div>
                    <div class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">
                        {{ $assignment->ojt_type === 'internal' ? ($assignment->professor?->name ?? '') : ($assignment->companySupervisor?->name ?? '') }}
                    </div>
                </div>

                <div class="rounded-2xl bg-zinc-50 p-4 dark:bg-zinc-800/50">
                    <div class="text-xs uppercase tracking-wide text-zinc-400">Status</div>
                    <div class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">
                        {{ ucfirst($assignment->status) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-2">
        <flux:modal.close class="w-full">
            <flux:button variant="filled" class="w-full">Cancel</flux:button>
        </flux:modal.close>

        <flux:button wire:click="delete" variant="danger" icon="trash" class="w-full">
            Delete Assignment
        </flux:button>
    </div>
</div>
