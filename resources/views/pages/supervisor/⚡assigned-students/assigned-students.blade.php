<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <flux:card class="p-4">
            <flux:heading>Total Assigned</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['total'] }}</flux:text>
        </flux:card>
    </div>

    <div class="space-y-4">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <h2 class="text-xl font-semibold">
                My Assigned Students
            </h2>

            <div class="flex w-full flex-wrap items-center gap-3 xl:w-auto">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search students..." clearable class="w-full sm:w-64" />

                <x-filter-modal name="filter-supervisor-assigned-students" heading="Filter Assigned Students"
                    description="Sort your assigned students."
                    :sorts="['' => 'Default', 'student' => 'Student name', 'student_id' => 'Student ID', 'course' => 'Course']">
                </x-filter-modal>
            </div>
        </div>

        @if ($this->assignments->isEmpty())
            <flux:card class="flex flex-col items-center gap-4 py-24">
                <flux:icon name="academic-cap" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No assigned students found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search or filter to find assigned students.</flux:text>
                </div>
            </flux:card>
        @else
            <flux:table :paginate="$this->assignments">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Student ID</flux:table.column>
                    <flux:table.column>Academic Details</flux:table.column>
                    <flux:table.column>Coordinator</flux:table.column>
                    <flux:table.column>Attendance</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->assignments as $assignment)
                        @php
                            $latestAttendance = $assignment->attendanceLogs->sortByDesc('attendance_date')->first();
                        @endphp

                        <flux:table.row :key="$assignment->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar size="md" :src="$assignment->student?->profile_photo_url"
                                    :name="$assignment->student?->display_name" />

                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $assignment->student?->display_name ?? '' }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $assignment->student?->email ?? '' }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-medium">
                                {{ $assignment->student?->studentProfile?->student_id ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $assignment->student?->studentProfile?->course ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $assignment->student?->coordinator?->name ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                @if ($latestAttendance)
                                    <div class="flex flex-col">
                                        <span>{{ $latestAttendance->attendance_date?->format('M d, Y') }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-300">
                                            {{ ucfirst($latestAttendance->status ?? 'recorded') }}{{ $latestAttendance->isLate() && $latestAttendance->status !== 'late' ? ' · Late' : '' }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>

                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
