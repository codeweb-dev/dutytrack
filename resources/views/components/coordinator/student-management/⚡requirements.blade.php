<?php

use App\Models\StudentRequirement;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
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

    public function toggleRequirement(int $requirementId): void
    {
        abort_unless((int) $this->student->coordinator_id === (int) Auth::id(), 403);

        $requirement = StudentRequirement::query()
            ->where('student_id', $this->student->id)
            ->findOrFail($requirementId);

        $completed = ! $requirement->is_completed;

        $requirement->update([
            'is_completed' => $completed,
            'completed_at' => $completed ? now() : null,
            'completed_by' => $completed ? Auth::id() : null,
        ]);

        $this->student = $this->student->fresh('studentProfile', 'studentRequirements');
        unset($this->requirementSteps, $this->requirementSummary);

        $this->dispatch('student-saved');

        Flux::toast(
            variant: 'success',
            heading: $completed ? 'Requirement checked.' : 'Requirement unchecked.',
            text: $requirement->label.' has been updated.'
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
                {{ $student->name }} can start OJT with pending requirements. Check each row after receiving the physical copy.
            </flux:text>
        </div>

        <flux:badge :color="$this->requirementSummary['pending'] === 0 ? 'green' : 'amber'" inset="top bottom">
            {{ $this->requirementSummary['completed'] }}/{{ $this->requirementSummary['total'] }} received
        </flux:badge>
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
                            <label class="flex min-w-0 items-start gap-3">
                                <input type="checkbox" @checked($requirement->is_completed)
                                    wire:click="toggleRequirement({{ $requirement->id }})"
                                    class="mt-1 size-4 rounded border-zinc-300 text-red-600 focus:ring-red-500 dark:border-zinc-700 dark:bg-zinc-900">

                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $requirement->label }}
                                    </span>
                                    <span class="mt-1 block text-xs text-zinc-500">
                                        @if ($requirement->is_completed)
                                            Received {{ $requirement->completed_at?->format('M d, Y') ?? '' }}
                                        @elseif ($requirement->submitted_at)
                                            Student marked this as submitted {{ $requirement->submitted_at->format('M d, Y') }}.
                                        @else
                                            Waiting for student physical copy.
                                        @endif
                                    </span>
                                </span>
                            </label>

                            <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                @if ($requirement->is_completed)
                                    <flux:badge color="green" size="sm" inset="top bottom">Checked</flux:badge>
                                @elseif ($requirement->submitted_at)
                                    <flux:badge color="sky" size="sm" inset="top bottom">For checking</flux:badge>
                                @else
                                    <flux:badge color="amber" size="sm" inset="top bottom">Missing</flux:badge>
                                @endif

                                @if ($requirement->template_path)
                                    <flux:button href="{{ asset($requirement->template_path) }}" target="_blank" download
                                        icon="document-arrow-down" size="sm">
                                        Template
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
