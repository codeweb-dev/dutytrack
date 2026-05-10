<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <flux:card class="p-4">
            <flux:heading>Total Assigned</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['total'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading>Active</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['active'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading>Pending</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['pending'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading>Completed</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['completed'] }}</flux:text>
        </flux:card>
    </div>

    <div class="space-y-4">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <h2 class="text-xl font-semibold">
                My Assigned Students
                <flux:badge color="red" inset="top bottom">{{ $this->assignments->total() }}</flux:badge>
            </h2>

            <div class="flex items-center gap-3">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search students..." clearable />

                <flux:modal.trigger name="filter-supervisor-assigned-students">
                    <flux:button icon="funnel">Filter</flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <flux:modal name="filter-supervisor-assigned-students" class="md:w-lg">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Filter Assigned Students</flux:heading>
                    <flux:text class="mt-2">Filter your assigned students by assignment status.</flux:text>
                </div>

                <flux:radio.group wire:model.live="status" label="Status" variant="pills">
                    <flux:radio value="" label="All" />
                    <flux:radio value="pending" label="Pending" />
                    <flux:radio value="active" label="Active" />
                    <flux:radio value="completed" label="Completed" />
                    <flux:radio value="cancelled" label="Cancelled" />
                </flux:radio.group>

                <flux:button type="button" variant="primary" wire:click="clearFilters" icon="trash" class="w-full">
                    Clear Filter
                </flux:button>
            </div>
        </flux:modal>

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
                    <flux:table.column>Professor</flux:table.column>
                    <flux:table.column>Hours</flux:table.column>
                    <flux:table.column>Attendance</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->assignments as $assignment)
                        @php
                            $approvedHours = $assignment->dutyHourLogs->where('status', 'approved')->sum('approved_hours');
                            $latestAttendance = $assignment->attendanceLogs->sortByDesc('attendance_date')->first();
                        @endphp

                        <flux:table.row :key="$assignment->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar size="md" :src="$assignment->student?->profile_photo_url"
                                    :name="$assignment->student?->name" />

                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $assignment->student?->name ?? '' }}</span>
                                    <span class="text-xs text-zinc-500">{{ $assignment->student?->email ?? '' }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-medium">
                                {{ $assignment->student?->studentProfile?->student_id ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span>{{ $assignment->student?->studentProfile?->course ?? '' }}</span>
                                    <span class="text-xs text-zinc-500">
                                        Year {{ $assignment->student?->studentProfile?->year_level ?? '' }}
                                        @if ($assignment->student?->studentProfile?->section)
                                            • Section {{ $assignment->student->studentProfile->section }}
                                        @endif
                                    </span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $assignment->student?->coordinator?->name ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $assignment->professor?->name ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ \App\Support\HourFormatter::format($approvedHours) }} approved
                            </flux:table.cell>

                            <flux:table.cell>
                                @if ($latestAttendance)
                                    <div class="flex flex-col">
                                        <span>{{ $latestAttendance->attendance_date?->format('M d, Y') }}</span>
                                        <span class="text-xs text-zinc-500">
                                            {{ ucfirst($latestAttendance->status ?? 'recorded') }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" :color="$this->badgeColor($assignment->status)"
                                    inset="top bottom">
                                    {{ ucfirst($assignment->status) }}
                                </flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
