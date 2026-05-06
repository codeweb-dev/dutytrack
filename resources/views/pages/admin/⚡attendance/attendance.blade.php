<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
        <flux:card class="p-4">
            <flux:heading class="flex items-center gap-2">Total Records</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['total'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading class="flex items-center gap-2">Present</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['present'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading class="flex items-center gap-2">Late</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['late'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading class="flex items-center gap-2">Absent</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['absent'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading class="flex items-center gap-2">Incomplete</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['incomplete'] }}</flux:text>
        </flux:card>
    </div>

    <div class="space-y-4">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <h2 class="text-xl font-semibold">
                All Attendance Records
                <flux:badge color="red" inset="top bottom">{{ $this->attendanceLogs->total() }}</flux:badge>
            </h2>

            <div class="flex items-center gap-3">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search student..." clearable />
                <flux:button icon="arrow-down-tray"
                    x-on:click="$flux.toast({
                        heading: 'In Progress',
                        text: 'This export report is still not yer working.',
                        variant: 'warning',
                    })">
                    Export Report
                </flux:button>
                <flux:modal.trigger name="filter">
                    <flux:button icon="funnel">Filter</flux:button>
                </flux:modal.trigger>
            </div>
        </div>


        <flux:modal name="filter" class="md:w-2xl">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Filter Attendance Records</flux:heading>
                    <flux:text class="mt-2">Filter attendance records by various criteria.</flux:text>
                </div>

                <flux:radio.group wire:model.live="ojt_type" label="OJT Type" variant="cards" :indicator="false"
                    class="max-sm:flex-col">
                    <flux:radio value="" icon="squares-2x2" label="All Types" description="All OJT types" />
                    <flux:radio value="internal" icon="globe-alt" label="Internal" description="Internal OJT" />
                    <flux:radio value="external" icon="academic-cap" label="External" description="External OJT" />
                </flux:radio.group>

                <flux:radio.group wire:model.live="status" label="Status" variant="pills">
                    <flux:radio value="" label="All Status" />
                    <flux:radio value="present" label="Present" />
                    <flux:radio value="late" label="Late" />
                    <flux:radio value="absent" label="Absent" />
                    <flux:radio value="incomplete" label="Incomplete" />
                </flux:radio.group>

                <flux:select wire:model.live="supervisor_id" placeholder="Supervisor" label="Supervisor">
                    <option value="">All Supervisors</option>
                    @foreach ($this->supervisors as $supervisor)
                        <option value="{{ $supervisor->id }}">
                            {{ $supervisor->first_name }} {{ $supervisor->last_name }}
                        </option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                    <flux:input type="date" wire:model.live="from_date" label="From Date" />
                    <flux:input type="date" wire:model.live="to_date" label="To Date" />
                </div>

                <div class="w-full">
                    <flux:button type="submit" variant="primary" wire:click="clearFilters" icon="trash"
                        class="w-full">Clear Filter</flux:button>
                </div>
            </div>
        </flux:modal>

        @if ($this->attendanceLogs->isEmpty())
            <flux:card class="flex flex-col items-center gap-4 py-24">
                <flux:icon name="clipboard-document-list" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No attendance logs found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search or filter to find what you're looking
                        for.</flux:text>
                </div>
            </flux:card>
        @else
            <flux:table :paginate="$this->attendanceLogs">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>ID</flux:table.column>
                    <flux:table.column>OJT Type</flux:table.column>
                    <flux:table.column>Supervisor</flux:table.column>
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>Time In</flux:table.column>
                    <flux:table.column>Time Out</flux:table.column>
                    <flux:table.column>Hours</flux:table.column>
                    <flux:table.column>Location</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Remarks</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->attendanceLogs as $log)
                        @php
                            $assignment = $log->assignment;
                            $supervisor =
                                $assignment?->ojt_type === 'internal'
                                    ? $assignment?->professor
                                    : $assignment?->companySupervisor;
                        @endphp

                        <flux:table.row :key="$log->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar size="md" :src="$log->student?->profile_photo_url"
                                    :name="$log->student?->name" />

                                <div class="flex flex-col">
                                    <span class="font-medium">
                                        {{ $log->student?->name ?? $log->student?->first_name . ' ' . $log->student?->last_name }}
                                    </span>
                                    <span class="text-xs text-zinc-500">
                                        {{ $log->student?->email }}
                                    </span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-medium">
                                {{ $log->student?->studentProfile?->student_id ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" color="sky" inset="top bottom">
                                    {{ ucfirst($assignment?->ojt_type ?? '—') }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $supervisor?->name ?? trim(($supervisor?->first_name ?? '') . ' ' . ($supervisor?->last_name ?? '')) ?: '—' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $log->attendance_date?->format('M d, Y') ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $log->time_in?->format('h:i A') ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $log->time_out?->format('h:i A') ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ number_format((float) $log->hours_rendered, 2) }}
                                @if ($log->overtime_enabled)
                                    <flux:badge size="sm" color="blue" inset="top bottom">OT</flux:badge>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    @if ($log->timeInMapUrl())
                                        <flux:button size="sm" variant="ghost" icon="map"
                                            :href="$log->timeInMapUrl()" target="_blank" />
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" :color="$this->badgeColor($log->status)"
                                    inset="top bottom">
                                    {{ ucfirst($log->status) }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $log->remarks ?: '—' }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
