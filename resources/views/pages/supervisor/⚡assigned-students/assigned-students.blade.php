<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        @php
            $cards = ['' => ['Total Assigned', 'total', 'users'], 'completed' => ['Completed Hours', 'completed', 'check-circle'], 'in_progress' => ['In Progress', 'in_progress', 'clock'], 'not_started' => ['Not Started', 'not_started', 'pause-circle']];
        @endphp
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
                <flux:heading size="xl">{{ $cards[$status][0] ?? 'Total Assigned' }}</flux:heading>
            </div>

            <div class="flex w-full items-center gap-3 xl:w-auto">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search students..." clearable class="w-full sm:w-64" />

                <x-filter-modal name="filter-supervisor-assigned-students" heading="Filter Assigned Students"
                    description="Filter by OJT progress and sort your assigned students."
                    :sorts="['' => 'Default', 'student' => 'Student name', 'student_id' => 'Student ID', 'course' => 'Course', 'hours' => 'Rendered hours']">
                    <flux:radio.group wire:model.live="status" label="OJT Progress" variant="pills">
                        <flux:radio value="" label="All" />
                        <flux:radio value="completed" label="Completed" />
                        <flux:radio value="in_progress" label="In Progress" />
                        <flux:radio value="not_started" label="Not Started" />
                    </flux:radio.group>
                </x-filter-modal>
            </div>
        </div>

        @if ($this->assignments->isEmpty())
            <div class="mt-6 flex flex-col items-center gap-4 py-16 sm:py-24">
                <flux:icon name="academic-cap" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No assigned students found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search or filter to find assigned students.</flux:text>
                </div>
            </div>
        @else
            <flux:table :paginate="$this->assignments" bleed container:class="mt-6"
                pagination:class="px-[var(--flux-bleed,1.5rem)] pb-3">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Student ID</flux:table.column>
                    <flux:table.column>Coordinator</flux:table.column>
                    <flux:table.column>OJT Hours</flux:table.column>
                    <flux:table.column>Last Attendance</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->assignments as $assignment)
                        @php
                            $student = $assignment->student;
                            $latestAttendance = $assignment->attendanceLogs->sortByDesc('attendance_date')->first();
                            $rendered = (float) $assignment->rendered_hours;
                            $required = (int) $student?->studentProfile?->ojt_required_hours;
                            $percent = $required > 0 ? min(round($rendered / $required * 100), 100) : 0;
                        @endphp

                        <flux:table.row :key="$assignment->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar size="md" :src="$student?->profile_photo_url" :name="$student?->display_name" />

                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $student?->display_name ?? '' }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $student?->email ?? '' }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $student?->studentProfile?->student_id ?? '' }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $student?->studentProfile?->course ?? '' }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="md" :src="$student?->coordinator?->profile_photo_url"
                                        :name="$student?->coordinator?->display_name" />

                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $student?->coordinator?->display_name ?? '' }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $student?->coordinator?->email ?? '' }}</span>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <div class="flex w-36 flex-col gap-1.5">
                                    <span class="text-sm">
                                        <span class="font-medium tabular-nums">{{ \App\Support\HourFormatter::format($rendered) }}</span>
                                        <span class="text-zinc-500 dark:text-zinc-300">/ {{ number_format($required) }} hrs</span>
                                    </span>
                                    <div class="h-1.5 rounded-full bg-zinc-200 dark:bg-zinc-700" role="progressbar"
                                        aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                                        <div class="h-full rounded-full {{ $percent >= 100 ? 'bg-green-500' : 'bg-amber-500' }}" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                @if ($latestAttendance)
                                    <div class="flex flex-col">
                                        <span>{{ $latestAttendance->attendance_date?->format('M d, Y') }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ ucfirst($latestAttendance->status ?? 'recorded') }}</span>
                                    </div>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                @if ($required > 0 && $rendered >= $required)
                                    <flux:badge size="sm" color="green" inset="top bottom">Completed</flux:badge>
                                @elseif ($rendered > 0)
                                    <flux:badge size="sm" color="amber" inset="top bottom">In Progress</flux:badge>
                                @else
                                    <flux:badge size="sm" color="zinc" inset="top bottom">Not Started</flux:badge>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
