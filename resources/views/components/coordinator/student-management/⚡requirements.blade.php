<?php

use App\Models\StudentRequirement;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public User $student;

    public function mount(User $student): void
    {
        abort_unless((int) $student->coordinator_id === (int) Auth::id(), 403);

        StudentRequirement::ensureForStudent($student);

        $this->student = $student->load('studentProfile', 'studentRequirements');
    }

    public function approveRequirement(int $requirementId): void
    {
        abort_unless((int) $this->student->coordinator_id === (int) Auth::id(), 403);

        $requirement = StudentRequirement::query()
            ->where('student_id', $this->student->id)
            ->findOrFail($requirementId);

        // Approval is final: no unapprove.
        if ($requirement->is_completed) {
            return;
        }

        if (! $requirement->file_path) {
            Flux::toast(
                variant: 'danger',
                heading: 'No file attached.',
                text: $requirement->label.' cannot be approved until the student attaches a file.'
            );

            return;
        }

        $requirement->update([
            'is_completed' => true,
            'completed_at' => now(),
            'completed_by' => Auth::id(),
        ]);

        $this->student = $this->student->fresh('studentProfile', 'studentRequirements');
        unset($this->requirementSteps, $this->requirementSummary);

        $this->dispatch('student-saved');

        Flux::toast(
            variant: 'success',
            heading: 'Requirement approved.',
            text: $requirement->label.' has been approved.'
        );
    }

    #[Computed]
    public function requirementSteps(): array
    {
        $requirements = $this->student->studentRequirements->sortBy('sort_order')->groupBy('step');

        return collect(StudentRequirement::stepLabels())
            ->map(function (string $label, string $step) use ($requirements): array {
                $items = $requirements->get($step, collect());
                $completed = $items->where('is_completed', true)->count();

                return [
                    'label' => $label,
                    'items' => $items,
                    'total' => $items->count(),
                    'completed' => $completed,
                    'is_completed' => $items->isNotEmpty() && $completed === $items->count(),
                ];
            })
            ->values()
            ->all();
    }

    #[Computed]
    public function requirementSummary(): array
    {
        $requirements = $this->student->studentRequirements;
        $completed = $requirements->where('is_completed', true)->count();

        return [
            'total' => $requirements->count(),
            'completed' => $completed,
            'pending' => $requirements->count() - $completed,
        ];
    }
};
?>

<div class="space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <flux:heading size="lg">Requirement Tracking</flux:heading>
            <flux:text class="mt-1 text-zinc-500">
                {{ $student->name }} can start OJT with pending requirements. Review each attached file, then approve it. Approval cannot be undone.
            </flux:text>
        </div>
    </div>

    <div class="space-y-6">
        @foreach ($this->requirementSteps as $step)
            <div>
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div class="font-medium text-zinc-900 dark:text-white">{{ $step['label'] }}</div>
                    <flux:badge size="sm" :color="$step['is_completed'] ? 'green' : null" inset="top bottom">
                        {{ $step['completed'] }}/{{ $step['total'] }}
                    </flux:badge>
                </div>

                <div class="divide-y divide-zinc-100 rounded-lg border border-zinc-200 dark:divide-zinc-800 dark:border-zinc-800">
                    @foreach ($step['items'] as $requirement)
                        <div class="flex flex-col gap-3 p-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $requirement->label }}
                                    </span>
                                    <span class="mt-1 block text-xs text-zinc-500">
                                        @if ($requirement->is_completed)
                                            Approved {{ $requirement->completed_at?->format('M d, Y') ?? '' }}
                                        @elseif ($requirement->submitted_at)
                                            Student submitted a file {{ $requirement->submitted_at->format('M d, Y') }}.
                                        @else
                                            Waiting for student file.
                                        @endif
                                    </span>
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                @if ($requirement->is_completed)
                                    <flux:badge color="green" size="sm" inset="top bottom">Approved</flux:badge>
                                @elseif ($requirement->submitted_at)
                                    <flux:badge color="sky" size="sm" inset="top bottom">For checking</flux:badge>
                                @else
                                    <flux:badge color="amber" size="sm" inset="top bottom">Missing</flux:badge>
                                @endif

                                @if ($requirement->template_path)
                                    <flux:button href="{{ asset($requirement->template_path) }}" target="_blank" download
                                        icon="document-arrow-down" size="xs">
                                        Template
                                    </flux:button>
                                @endif

                                @if ($requirement->file_path)
                                    <flux:button href="{{ Storage::url($requirement->file_path) }}" target="_blank"
                                        icon="paper-clip" size="xs">
                                        View file
                                    </flux:button>
                                @endif

                                <flux:button wire:click="approveRequirement({{ $requirement->id }})"
                                    wire:confirm="Approve {{ $requirement->label }}? This cannot be undone."
                                    :disabled="$requirement->is_completed || ! $requirement->file_path" icon="check" size="xs"
                                    variant="primary" color="green">
                                    {{ $requirement->is_completed ? 'Approved' : 'Approve' }}
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
