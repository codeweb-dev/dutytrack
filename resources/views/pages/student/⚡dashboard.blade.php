<?php

use App\Models\AttendanceLog;
use App\Models\OjtAssignment;
use App\Models\StudentRequirement;
use App\Models\User;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Student Dashboard')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'Student Dashboard']];

    #[Computed]
    public function title(): string
    {
        return 'Welcome back, '.(Auth::user()?->name ?? 'Student').' 👋';
    }

    public string $title = 'Student Dashboard';

    public string $description = 'A simple overview of your placement, requirements, and latest attendance activity.';

    protected function studentId(): int
    {
        return (int) Auth::id();
    }

    #[Computed]
    public function currentStudent(): User
    {
        return User::query()
            ->with(['studentProfile', 'coordinator', 'ojtAssignment.professor', 'ojtAssignment.companySupervisor'])
            ->findOrFail($this->studentId());
    }

    #[Computed]
    public function assignment(): ?OjtAssignment
    {
        return $this->currentStudent->ojtAssignment;
    }

    #[Computed]
    public function topCards(): array
    {
        $assignment = $this->assignment;

        return [
            [
                'title' => 'Assignment Status',
                'value' => $assignment ? ucfirst($assignment->status) : 'Unassigned',
                'icon' => 'briefcase',
                'footnote' => $assignment ? ucfirst($assignment->ojt_type).' OJT' : 'Waiting for placement',
            ],
        ];
    }

    #[Computed]
    public function placementSummary(): array
    {
        $assignment = $this->assignment;

        return [
            'course' => $this->currentStudent->studentProfile?->course ?? '',
            'student_id' => $this->currentStudent->studentProfile?->student_id ?? '',
            'coordinator' => $this->currentStudent->coordinator?->name ?? 'Not assigned yet',
            'professor' => $assignment?->professor?->name ?? 'Not assigned yet',
            'supervisor' => $assignment?->companySupervisor?->name ?? 'Not assigned yet',
            'type' => $assignment ? ucfirst($assignment->ojt_type) : 'No assignment yet',
            'status' => $assignment ? ucfirst($assignment->status) : 'Unassigned',
        ];
    }

    #[Computed]
    public function recentAttendance(): LengthAwarePaginator
    {
        return AttendanceLog::query()->where('student_id', $this->studentId())->latest('attendance_date')->latest('created_at')->paginate(5);
    }

    #[Computed]
    public function requirements()
    {
        return StudentRequirement::ensureForStudent($this->currentStudent);
    }

    #[Computed]
    public function requirementSummary(): array
    {
        $requirements = $this->requirements;
        $completed = $requirements->where('is_completed', true)->count();

        return [
            'total' => $requirements->count(),
            'completed' => $completed,
            'pending' => $requirements->count() - $completed,
            'progress' => $requirements->isNotEmpty() ? round(($completed / $requirements->count()) * 100) : 0,
        ];
    }

    #[Computed]
    public function requirementSteps(): array
    {
        $requirements = $this->requirements->groupBy('step');
        $icons = [
            'orientation' => 'academic-cap',
            'medical' => 'heart',
            'forms' => 'clipboard-document-list',
        ];

        return collect(StudentRequirement::stepLabels())
            ->map(function (string $label, string $step) use ($requirements, $icons): array {
                $items = $requirements->get($step, collect());
                $completed = $items->where('is_completed', true)->count();

                return [
                    'key' => $step,
                    'label' => $label,
                    'icon' => $icons[$step] ?? 'clipboard-document-check',
                    'items' => $items,
                    'total' => $items->count(),
                    'completed' => $completed,
                    'is_completed' => $items->isNotEmpty() && $completed === $items->count(),
                ];
            })
            ->values()
            ->all();
    }

    public function submitRequirement(string $key): void
    {
        $requirement = StudentRequirement::query()->where('student_id', $this->studentId())->where('key', $key)->firstOrFail();

        if (! $requirement->is_completed && ! $requirement->submitted_at) {
            $requirement->update(['submitted_at' => now()]);
        }

        unset($this->requirements, $this->requirementSummary, $this->requirementSteps);

        Flux::toast(variant: 'success', heading: 'Requirement submitted.', text: 'Your coordinator can now verify the physical copy.');
    }
};
?>

<div class="flex flex-col gap-8">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$this->title" :description="$description" />

    <div class="flex flex-wrap gap-2">
        <flux:button variant="primary" icon="clock" :href="route('student.attendance')" wire:navigate>Record attendance</flux:button>
        <flux:button icon="document-text" :href="route('student.reports-submissions')" wire:navigate>Daily report</flux:button>
    </div>

    <section class="duty-metrics grid gap-4 md:grid-cols-2 xl:grid-cols-1">
        @foreach ($this->topCards as $card)
            <flux:card>
                <div class="flex items-start justify-between gap-4">
                    <flux:heading size="lg">
                        {{ $card['title'] }}
                    </flux:heading>

                    <div
                        class="rounded-2xl bg-zinc-50 p-3 text-zinc-900 ring-1 ring-zinc-200 dark:bg-zinc-800 dark:text-white dark:ring-zinc-700">
                        <flux:icon :name="$card['icon']" class="h-5 w-5" />
                    </div>
                </div>

                <div class="mt-6 text-5xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ $card['value'] }}
                </div>

                <flux:text class="mt-6">
                    {{ $card['footnote'] }}
                </flux:text>
            </flux:card>
        @endforeach
    </section>

    <section class="grid gap-4">
        <flux:card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">OJT Student Requirements</flux:heading>
                    <flux:text class="mt-2">
                        Download missing templates and mark physical copies submitted for coordinator checking.
                    </flux:text>
                </div>

                <flux:badge :color="$this->requirementSummary['pending'] === 0 ? 'green' : 'amber'" inset="top bottom">
                    {{ $this->requirementSummary['completed'] }}/{{ $this->requirementSummary['total'] }}
                </flux:badge>
            </div>

            <div class="mt-7">
                <div class="flex items-start">
                    @foreach ($this->requirementSteps as $step)
                        <div class="flex min-w-0 flex-1 flex-col items-center text-center">
                            <div
                                class="{{ $step['is_completed'] ? 'bg-forest text-white ring-forest' : 'bg-white text-zinc-500 ring-zinc-300 dark:bg-zinc-900 dark:text-zinc-300 dark:ring-zinc-700' }} flex size-14 items-center justify-center rounded-full ring-2">
                                <flux:icon :name="$step['icon']" class="size-6" />
                            </div>

                            <div class="mt-3 text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ $step['label'] }}
                            </div>

                            <flux:text class="mt-1">
                                {{ $step['completed'] }}/{{ $step['total'] }} done
                            </flux:text>
                        </div>

                        @if (!$loop->last)
                            <div class="mt-6 h-px w-10 bg-zinc-300 dark:bg-zinc-700 md:w-16"></div>
                        @endif
                    @endforeach
                </div>
            </div>

            <flux:field class="mt-6">
                <flux:label>Progress</flux:label>

                <div class="flex items-center gap-4 -mt-2">
                    <flux:progress :value="$this->requirementSummary['progress']" />
                    <span class="text-sm tabular-nums">{{ $this->requirementSummary['progress'] }}%</span>
                </div>
            </flux:field>

            <div class="mt-6 space-y-6">
                @foreach ($this->requirementSteps as $step)
                    <div>
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $step['label'] }}</h3>
                            <flux:badge size="sm" :color="$step['is_completed'] ? 'green' : null"
                                inset="top bottom">
                                {{ $step['is_completed'] ? 'Complete' : 'In progress' }}
                            </flux:badge>
                        </div>

                        <div class="space-y-3">
                            @foreach ($step['items'] as $requirement)
                                <flux:card class="px-5 py-3">
                                    <div class="flex min-w-0 items-start gap-3">
                                        <span
                                            class="{{ $requirement->is_completed ? 'bg-green-600 text-white' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500' }} mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full">
                                            <flux:icon :name="$requirement->is_completed ? 'check-circle' : 'clock'"
                                                class="size-4" />
                                        </span>

                                        <div class="min-w-0">
                                            <flux:heading>
                                                {{ $requirement->label }}
                                            </flux:heading>
                                            <flux:text class="mt-1">
                                                @if ($requirement->is_completed)
                                                    Verified by coordinator.
                                                @elseif ($requirement->submitted_at)
                                                    Submitted for coordinator checking.
                                                @else
                                                    Missing physical copy.
                                                @endif
                                            </flux:text>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                        @if ($requirement->is_completed)
                                            <flux:badge color="green" size="sm" inset="top bottom">Done
                                            </flux:badge>
                                        @elseif ($requirement->submitted_at)
                                            <flux:badge color="sky" size="sm" inset="top bottom">Submitted
                                            </flux:badge>
                                        @else
                                            <flux:badge color="amber" size="sm" inset="top bottom">Missing
                                            </flux:badge>
                                        @endif

                                        @if ($requirement->template_path)
                                            <flux:button href="{{ asset($requirement->template_path) }}"
                                                target="_blank" download icon="document-arrow-down" size="xs">
                                                Template
                                            </flux:button>
                                        @endif

                                        @if (!$requirement->is_completed)
                                            <flux:button wire:click="submitRequirement('{{ $requirement->key }}')"
                                                icon="arrow-up-tray" size="xs" variant="primary"
                                                :disabled="$requirement->submitted_at !== null"
                                                wire:key="submit-{{ $requirement->id }}">
                                                {{ $requirement->submitted_at ? 'Submitted' : 'Submit' }}
                                            </flux:button>
                                        @endif
                                    </div>
                                </flux:card>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </flux:card>
    </section>

    <flux:card>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <flux:heading size="lg">Recent Attendance</flux:heading>
                <flux:text class="mt-2">
                    Your latest attendance entries, including rendered hours and flagged records.
                </flux:text>
            </div>
        </div>

        <div class="mt-6">
            <flux:table :paginate="$this->recentAttendance">
                <flux:table.columns>
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>Morning In</flux:table.column>
                    <flux:table.column>Morning Out</flux:table.column>
                    <flux:table.column>Afternoon In</flux:table.column>
                    <flux:table.column>Afternoon Out</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Hours</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->recentAttendance as $log)
                        <flux:table.row :key="$log->id">
                            <flux:table.cell>
                                {{ $log->attendance_date?->format('M d, Y') ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $log->displayMorningTimeIn()?->format('h:i A') ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $log->displayMorningTimeOut()?->format('h:i A') ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $log->displayAfternoonTimeIn()?->format('h:i A') ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $log->displayAfternoonTimeOut()?->format('h:i A') ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:badge size="sm" color="sky" inset="top bottom">
                                        {{ ucfirst($log->status ?? 'recorded') }}
                                    </flux:badge>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ \App\Support\HourFormatter::format($log->hours_rendered ?? 0) }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="7" class="py-8 text-center text-zinc-500">
                                No attendance records found.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>
</div>
