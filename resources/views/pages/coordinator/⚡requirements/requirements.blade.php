<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 grid-cols-2 xl:grid-cols-4">
        @php $cards = ['' => ['Total Students', 'students', 'users'], 'complete' => ['Complete', 'complete', 'check-circle'], 'pending' => ['Pending', 'pending', 'clock'], 'for_checking' => ['For Checking', 'for_checking', 'document-magnifying-glass']]; @endphp
        @foreach ($cards as $value => [$label, $key, $icon])
            <button type="button" wire:click="$set('status', '{{ $value }}')" class="group text-left"
                aria-pressed="{{ $status === $value ? 'true' : 'false' }}">
                <flux:card size="xs" class="hover:bg-zinc-50 dark:hover:bg-zinc-700 group-aria-pressed:border-zinc-800 group-aria-pressed:outline-1 group-aria-pressed:outline-zinc-800 dark:group-aria-pressed:border-white dark:group-aria-pressed:outline-white">
                    <flux:heading class="flex items-center gap-2">{{ $label }}
                        <flux:icon :name="$icon" :variant="$status === $value ? 'solid' : 'outline'"
                            class="ml-auto size-5 {{ $status === $value ? 'text-zinc-800 dark:text-white' : 'text-zinc-400' }}" />
                    </flux:heading>
                    <flux:heading size="2xl" class="mt-2">{{ $this->summary[$key] }}</flux:heading>
                </flux:card>
            </button>
        @endforeach
    </div>

    <flux:card>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <flux:heading size="xl">{{ $cards[$status][0] ?? 'Total Students' }}</flux:heading>
            </div>

            <div class="flex w-full items-center gap-3 xl:w-auto">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search students..." clearable class="w-full sm:w-64" />

                <x-filter-modal name="filter-coordinator-requirements" heading="Filter Requirements"
                    description="Filter by requirement status and sort the students."
                    :sorts="['' => 'Default', 'name' => 'Last name', 'student_id' => 'Student ID', 'course' => 'Course']">
                    <flux:radio.group wire:model.live="status" label="Requirement Status" variant="pills">
                        <flux:radio value="" label="All" />
                        <flux:radio value="complete" label="Complete" />
                        <flux:radio value="pending" label="Pending" />
                        <flux:radio value="for_checking" label="For Checking" />
                    </flux:radio.group>
                </x-filter-modal>
            </div>
        </div>

        @if ($this->students->isEmpty())
            <div class="mt-6 flex flex-col items-center gap-4 py-16 sm:py-24">
                <flux:icon name="clipboard-document-check" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No students found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search or filter to find the student requirements.</flux:text>
                </div>
            </div>
        @else
            <flux:table :paginate="$this->students" bleed container:class="mt-6"
                pagination:class="px-[var(--flux-bleed,1.5rem)] pb-3">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Student ID</flux:table.column>
                    <flux:table.column>Requirements</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
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
                                <flux:avatar size="md" :src="$student->profile_photo_url" :name="$student->display_name" />

                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $student->display_name }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $student->email }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-medium">
                                {{ $student->studentProfile?->student_id ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col gap-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <flux:badge :color="\App\Support\BadgeColor::for($isComplete ? 'complete' : 'missing')" size="sm"
                                            inset="top bottom">
                                            {{ $completedRequirements }}/{{ $totalRequirements }} received
                                        </flux:badge>

                                        @if ($submittedRequirements > 0)
                                            <flux:badge :color="\App\Support\BadgeColor::for('for_checking')" size="sm" inset="top bottom">
                                                {{ $submittedRequirements }} for checking
                                            </flux:badge>
                                        @endif
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge :color="\App\Support\BadgeColor::for($isComplete ? 'complete' : 'pending')" size="sm" inset="top bottom">
                                    {{ $isComplete ? 'Complete' : 'Pending' }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell align="end">
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
    </flux:card>
</div>
