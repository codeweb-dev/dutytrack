<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <flux:card class="p-4">
            <flux:heading>Total Students</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['students'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading>Complete</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['complete'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading>Pending</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['pending'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading>For Checking</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['for_checking'] }}</flux:text>
        </flux:card>
    </div>

    <div class="space-y-4">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <h2 class="text-xl font-semibold">
                Requirement Tracking
                <flux:badge color="red" inset="top bottom">{{ $this->students->total() }}</flux:badge>
            </h2>

            <div>
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search students..." clearable />
            </div>
        </div>

        @if ($this->students->isEmpty())
            <flux:card class="flex flex-col items-center gap-4 py-24">
                <flux:icon name="clipboard-document-check" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No students found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search to find the student requirements.</flux:text>
                </div>
            </flux:card>
        @else
            <flux:table :paginate="$this->students">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Student ID</flux:table.column>
                    <flux:table.column>Academic Details</flux:table.column>
                    <flux:table.column>Requirements</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->students as $student)
                        @php
                            $completedRequirements = $student->studentRequirements
                                ->where('is_completed', true)
                                ->count();
                            $totalRequirements = $student->studentRequirements->count();
                            $submittedRequirements = $student->studentRequirements
                                ->where('is_completed', false)
                                ->whereNotNull('submitted_at')
                                ->count();
                            $isComplete = $totalRequirements > 0 && $completedRequirements === $totalRequirements;
                        @endphp

                        <flux:table.row :key="$student->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar size="md" :src="$student->profile_photo_url" :name="$student->name" />

                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $student->name }}</span>
                                    <span class="text-xs text-zinc-500">{{ $student->email }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-medium">
                                {{ $student->studentProfile?->student_id ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $student->studentProfile?->course ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col gap-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <flux:badge :color="$isComplete ? 'green' : 'red'" size="sm"
                                            inset="top bottom">
                                            {{ $completedRequirements }}/{{ $totalRequirements }} received
                                        </flux:badge>

                                        @if ($submittedRequirements > 0)
                                            <flux:badge color="sky" size="sm" inset="top bottom">
                                                {{ $submittedRequirements }} for checking
                                            </flux:badge>
                                        @endif
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge :color="$isComplete ? 'green' : 'amber'" size="sm" inset="top bottom">
                                    {{ $isComplete ? 'Complete' : 'Pending' }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:modal.trigger :name="'requirements-'.$student->id">
                                    <flux:button size="xs" icon="clipboard-document-check">
                                        Track
                                    </flux:button>
                                </flux:modal.trigger>

                                <flux:modal :name="'requirements-'.$student->id" flyout variant="floating"
                                    class="md:w-2xl">
                                    <livewire:coordinator.student-management.requirements :student="$student"
                                        :key="'monitoring-requirements-student-' . $student->id" />
                                </flux:modal>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
