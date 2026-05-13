<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="space-y-4">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <h2 class="text-xl font-semibold">
                Submitted Evaluations
                <flux:badge color="red" inset="top bottom">{{ $this->evaluations->total() }}</flux:badge>
            </h2>

            <div class="flex items-center gap-3">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search evaluations..." clearable />

                <flux:modal.trigger name="professor-evaluation-students">
                    <flux:button icon="check-badge" variant="primary">
                        Evaluate Student
                    </flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        @if ($this->evaluations->isEmpty())
            <flux:card class="flex flex-col items-center gap-4 py-24">
                <flux:icon name="check-badge" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No evaluations submitted</flux:heading>
                    <flux:text class="mt-2">Use Assigned Students to submit a practicum evaluation.</flux:text>
                </div>
            </flux:card>
        @else
            <flux:table :paginate="$this->evaluations">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Student ID</flux:table.column>
                    <flux:table.column>Course</flux:table.column>
                    <flux:table.column>Inclusive Dates</flux:table.column>
                    <flux:table.column>Rating</flux:table.column>
                    <flux:table.column>Comments</flux:table.column>
                    <flux:table.column>Download</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->evaluations as $evaluation)
                        <flux:table.row :key="$evaluation->id">
                            <flux:table.cell>{{ $evaluation->student?->name ?? '' }}</flux:table.cell>
                            <flux:table.cell>{{ $evaluation->student?->studentProfile?->student_id ?? '' }}
                            </flux:table.cell>
                            <flux:table.cell>{{ $evaluation->student?->studentProfile?->course ?? '' }}
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $evaluation->inclusive_from?->format('M d, Y') ?? '' }}
                                @if ($evaluation->inclusive_to)
                                    to {{ $evaluation->inclusive_to->format('M d, Y') }}
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ number_format((float) $evaluation->rating, 1) }}</span>
                                    <span class="text-xs text-zinc-500">
                                        {{ $evaluation->evaluation_date?->format('M d, Y') }}
                                    </span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ \Illuminate\Support\Str::limit($evaluation->remarks ?? '', 80) }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" icon="arrow-down-tray"
                                    wire:key="download-{{ $evaluation->id }}"
                                    href="{{ route('professor.evaluations.download', $evaluation) }}" target="_blank">
                                    Docs
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>

    <flux:modal name="professor-evaluation-students" class="!max-w-7xl !w-full !h-full">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Students to Evaluate</flux:heading>
                <flux:text class="mt-2">Choose an assigned student and submit a new evaluation record.</flux:text>
            </div>

            @if ($this->assignments->isEmpty())
                <flux:card class="flex flex-col items-center gap-4 py-16">
                    <flux:icon name="academic-cap" class="size-12" />
                    <div class="text-center">
                        <flux:heading size="xl">No assigned students found</flux:heading>
                        <flux:text class="mt-2">Assigned students will appear here for evaluation.</flux:text>
                    </div>
                </flux:card>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Student</flux:table.column>
                        <flux:table.column>Student ID</flux:table.column>
                        <flux:table.column>Course</flux:table.column>
                        <flux:table.column>Cooperating Program</flux:table.column>
                        <flux:table.column>Evaluation</flux:table.column>
                        <flux:table.column>Actions</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->assignments as $assignment)
                            @php
                                $myEvaluations = $assignment->evaluations->where('evaluator_id', auth()->id());
                                $latestEvaluation = $myEvaluations->sortByDesc('created_at')->first();
                            @endphp

                            <flux:table.row :key="$assignment->id">
                                <flux:table.cell>{{ $assignment->student?->name ?? '' }}</flux:table.cell>
                                <flux:table.cell>{{ $assignment->student?->studentProfile?->student_id ?? '' }}
                                </flux:table.cell>
                                <flux:table.cell>{{ $assignment->student?->studentProfile?->course ?? '' }}
                                </flux:table.cell>
                                <flux:table.cell>{{ $this->cooperatingProgramFor($assignment) }}</flux:table.cell>
                                <flux:table.cell>
                                    @if ($latestEvaluation)
                                        <div class="flex flex-col">
                                            <span
                                                class="font-medium">{{ $myEvaluations->count() }} submitted</span>
                                            <span class="text-xs text-zinc-500">
                                                Latest: {{ $latestEvaluation->evaluation_date?->format('M d, Y') }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-zinc-400">Not evaluated</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:button type="button" size="sm" icon="check-badge"
                                        wire:key="evaluate-{{ $assignment->id }}"
                                        wire:click="openEvaluationModal({{ $assignment->id }})">
                                        Evaluate
                                    </flux:button>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </div>
    </flux:modal>

    @include('components.evaluations.practicum-modal', ['modalName' => 'professor-evaluation-form'])
</div>
