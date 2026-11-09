<?php

use App\Models\AttendanceLog;
use App\Models\Evaluation;
use App\Models\OjtAssignment;
use App\Models\StudentRequirement;
use App\Models\User;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('Student Dashboard')] class extends Component
{
    use WithFileUploads;
    use WithPagination;

    public array $requirementFiles = [];

    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'Student Dashboard']];

    #[Computed]
    public function title(): string
    {
        return 'Welcome back, '.(Auth::user()?->name ?? 'Professor').' 👋';
    }

    public string $title = 'Student Dashboard';

    public string $description = 'A simple overview of your placement, requirements, attendance, and evaluation.';

    protected function studentId(): int
    {
        return (int) Auth::id();
    }

    #[Computed]
    public function currentStudent(): User
    {
        return User::query()
            ->with(['studentProfile', 'coordinator', 'ojtAssignment.companySupervisor'])
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
                'title' => 'Placement',
                'value' => $assignment ? 'Assigned' : 'Unassigned',
                'icon' => 'briefcase',
                'footnote' => $assignment ? ucfirst($assignment->ojt_type).' OJT' : 'Waiting for placement',
            ],
        ];
    }

    #[Computed]
    public function recentAttendance(): LengthAwarePaginator
    {
        return AttendanceLog::query()->where('student_id', $this->studentId())->latest('attendance_date')->latest('created_at')->paginate(10);
    }

    #[Computed]
    public function evaluation(): ?Evaluation
    {
        return Evaluation::query()
            ->with('evaluator')
            ->where('student_id', $this->studentId())
            ->where('status', 'submitted')
            ->latest('evaluation_date')
            ->latest('created_at')
            ->first();
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
        $this->validate([
            "requirementFiles.$key" => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ], attributes: ["requirementFiles.$key" => 'file']);

        $requirement = StudentRequirement::query()->where('student_id', $this->studentId())->where('key', $key)->firstOrFail();

        // Approved requirements are locked.
        abort_if($requirement->is_completed, 403);

        $oldPath = $requirement->file_path;

        $requirement->update([
            'file_path' => $this->requirementFiles[$key]->store('student-requirements/'.$this->studentId(), 'local'),
            'submitted_at' => now(),
        ]);

        if ($oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        unset($this->requirementFiles[$key], $this->requirements, $this->requirementSummary, $this->requirementSteps);

        Flux::toast(variant: 'success', heading: 'Requirement submitted.', text: 'Your coordinator can now review the attached file.');
    }
};
?>

<div class="flex flex-col gap-8">
    <div data-tour="dashboard-overview"><x-page-header :breadcrumbs="$breadcrumbs" :title="$this->title" :description="$description" /></div>

    <div class="grid items-start gap-6 lg:grid-cols-5">
        <div class="flex min-w-0 flex-col gap-6 lg:col-span-3">
            <section class="grid gap-4" data-tour="dashboard-metrics">
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
                                            @if ($log->isLate() && $log->status !== 'late')
                                                <flux:badge size="sm" color="amber" inset="top bottom">Late</flux:badge>
                                            @endif
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

            <section id="evaluations" data-tour="dashboard-evaluation">
                <flux:card>
                    <div>
                        <flux:heading size="lg">My Evaluation</flux:heading>
                        <flux:text class="mt-2">
                            Review your submitted practicum evaluation and download the official form.
                        </flux:text>
                    </div>

                    @if ($evaluation = $this->evaluation)
                        <div class="mt-6 overflow-x-auto">
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>Evaluator</flux:table.column>
                                    <flux:table.column>Inclusive Dates</flux:table.column>
                                    <flux:table.column>Cooperating Program</flux:table.column>
                                    <flux:table.column>Rating</flux:table.column>
                                    <flux:table.column>Comments</flux:table.column>
                                    <flux:table.column>Download</flux:table.column>
                                </flux:table.columns>

                                <flux:table.rows>
                                    <flux:table.row :key="$evaluation->id">
                                        <flux:table.cell>
                                            {{ $evaluation->evaluator?->display_name ?? 'Supervisor' }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            {{ $evaluation->inclusive_from?->format('M d, Y') ?? '' }}
                                            @if ($evaluation->inclusive_to)
                                                to {{ $evaluation->inclusive_to->format('M d, Y') }}
                                            @endif
                                        </flux:table.cell>
                                        <flux:table.cell>{{ $evaluation->cooperating_program ?? '' }}</flux:table.cell>
                                        <flux:table.cell>
                                            <div class="flex flex-col">
                                                <span class="font-medium">
                                                    {{ number_format((float) $evaluation->rating, 1) }}
                                                </span>
                                                <span class="text-xs text-zinc-500 dark:text-zinc-300">
                                                    {{ $evaluation->evaluation_date?->format('M d, Y') }}
                                                </span>
                                            </div>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            {{ \Illuminate\Support\Str::limit($evaluation->remarks ?? '', 80) }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:button size="sm" icon="arrow-down-tray"
                                                href="{{ route('student.evaluations.download', $evaluation) }}" target="_blank">
                                                Docs
                                            </flux:button>
                                        </flux:table.cell>
                                    </flux:table.row>
                                </flux:table.rows>
                            </flux:table>
                        </div>
                    @else
                        <flux:text class="mt-6">Submitted practicum evaluations will appear here.</flux:text>
                    @endif
                </flux:card>
            </section>
        </div>

        <section class="grid gap-4 lg:col-span-2" data-tour="dashboard-workflow">
            <flux:card>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <flux:heading size="lg">OJT Student Requirements</flux:heading>
                        <flux:text class="mt-2">
                            Download the templates, then attach and submit each file for coordinator approval.
                        </flux:text>
                    </div>

                    <flux:badge :color="$this->requirementSummary['pending'] === 0 ? 'green' : 'red'" inset="top bottom">
                        {{ $this->requirementSummary['completed'] }}/{{ $this->requirementSummary['total'] }}
                    </flux:badge>
                </div>

                <div class="mt-7">
                    <div class="flex items-start">
                        @foreach ($this->requirementSteps as $step)
                            <div class="flex min-w-0 flex-1 flex-col items-center text-center">
                                <div
                                    class="{{ $step['is_completed'] ? 'bg-red-600 text-white ring-red-600' : 'bg-white text-zinc-500 ring-zinc-300 dark:bg-zinc-900 dark:text-zinc-300 dark:ring-zinc-700' }} flex size-14 items-center justify-center rounded-full ring-2">
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
                                <div class="mt-7 h-px w-6 shrink-0 bg-zinc-300 dark:bg-zinc-700"></div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <flux:field class="mt-6">
                    <flux:label>Progress</flux:label>

                    <div class="flex items-center gap-4 -mt-2">
                        <flux:progress :value="$this->requirementSummary['progress']" />
                        <span class="text-sm tabular-nums ...">{{ $this->requirementSummary['progress'] }}%</span>
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
                                                        Approved by coordinator.
                                                    @elseif ($requirement->submitted_at)
                                                        Submitted for coordinator checking.
                                                    @else
                                                        Attach the file and submit.
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

                                            @if ($requirement->file_path)
                                                <flux:button href="{{ route('student.requirements.file', $requirement) }}"
                                                    target="_blank" icon="paper-clip" size="xs">
                                                    View file
                                                </flux:button>
                                            @endif
                                        </div>

                                        @if (!$requirement->is_completed)
                                            <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-start"
                                                wire:key="submit-{{ $requirement->id }}">
                                                <flux:input type="file" size="sm" class="flex-1"
                                                    wire:model="requirementFiles.{{ $requirement->key }}"
                                                    label="Required document" required
                                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />

                                                <flux:button wire:click="submitRequirement('{{ $requirement->key }}')"
                                                    icon="arrow-up-tray" size="sm" variant="primary"
                                                    wire:loading.attr="disabled"
                                                    wire:target="requirementFiles.{{ $requirement->key }}, submitRequirement('{{ $requirement->key }}')">
                                                    {{ $requirement->submitted_at ? 'Resubmit' : 'Submit' }}
                                                </flux:button>
                                            </div>
                                            <flux:error name="requirementFiles.{{ $requirement->key }}" />
                                        @endif
                                    </flux:card>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        </section>

    </div>
</div>
