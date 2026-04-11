<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
        <flux:card class="p-4">
            <flux:heading>Total Records</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['total'] }}</flux:text>
        </flux:card>
        <flux:card class="p-4">
            <flux:heading>Present</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['present'] }}</flux:text>
        </flux:card>
        <flux:card class="p-4">
            <flux:heading>Late</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['late'] }}</flux:text>
        </flux:card>
        <flux:card class="p-4">
            <flux:heading>Absent</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['absent'] }}</flux:text>
        </flux:card>
        <flux:card class="p-4">
            <flux:heading>Incomplete</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['incomplete'] }}</flux:text>
        </flux:card>
    </div>

    <div class="space-y-4">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <h2 class="text-xl font-semibold">
                Attendance Report Preview
                <flux:badge color="red" inset="top bottom">{{ $this->attendanceReports->total() }}</flux:badge>
            </h2>

            <div class="flex items-center gap-3">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search student..." clearable />
                <flux:modal.trigger name="attendance-report-filter">
                    <flux:button icon="funnel">Filter</flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <flux:modal name="attendance-report-filter" class="md:w-2xl">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Filter Attendance Reports</flux:heading>
                    <flux:text class="mt-2">Filter attendance report records by type, status, supervisor, and date
                        range.</flux:text>
                </div>

                <flux:radio.group wire:model.live="ojt_type" label="OJT Type" variant="cards" :indicator="false"
                    class="max-sm:flex-col">
                    <flux:radio value="" icon="squares-2x2" label="All Types" description="All OJT types" />
                    <flux:radio value="internal" icon="building-office-2" label="Internal" description="Internal OJT" />
                    <flux:radio value="external" icon="briefcase" label="External" description="External OJT" />
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
                        <option value="{{ $supervisor->id }}">{{ $supervisor->first_name }} {{ $supervisor->last_name }}
                        </option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input type="date" wire:model.live="from_date" label="From Date" />
                    <flux:input type="date" wire:model.live="to_date" label="To Date" />
                </div>

                <flux:button type="button" wire:click="clearFilters" icon="trash" class="w-full">Clear Filter
                </flux:button>
            </div>
        </flux:modal>

        <flux:table :paginate="$this->attendanceReports">
            <flux:table.columns>
                <flux:table.column>Student</flux:table.column>
                <flux:table.column>ID</flux:table.column>
                <flux:table.column>OJT Type</flux:table.column>
                <flux:table.column>Supervisor</flux:table.column>
                <flux:table.column>Date</flux:table.column>
                <flux:table.column>Time In</flux:table.column>
                <flux:table.column>Time Out</flux:table.column>
                <flux:table.column>Hours</flux:table.column>
                <flux:table.column>Status</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->attendanceReports as $report)
                    @php
                        $assignment = $report->assignment;
                        $supervisor =
                            $assignment?->ojt_type === 'internal'
                                ? $assignment?->professor
                                : $assignment?->companySupervisor;
                    @endphp
                    <flux:table.row :key="$report->id">
                        <flux:table.cell>
                            {{ $report->student?->name ?? trim(($report->student?->first_name ?? '') . ' ' . ($report->student?->last_name ?? '')) }}
                        </flux:table.cell>
                        <flux:table.cell>{{ $report->student?->student_id ?? '—' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="sky" inset="top bottom">
                                {{ ucfirst($assignment?->ojt_type ?? '—') }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $supervisor?->name ?? trim(($supervisor?->first_name ?? '') . ' ' . ($supervisor?->last_name ?? '')) ?: '—' }}
                        </flux:table.cell>
                        <flux:table.cell>{{ $report->attendance_date?->format('M d, Y') ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $report->time_in?->format('h:i A') ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $report->time_out?->format('h:i A') ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ number_format((float) $report->hours_rendered, 2) }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$this->badgeColor($report->status)" inset="top bottom">
                                {{ ucfirst($report->status) }}</flux:badge>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="py-6 text-center text-zinc-500">No attendance report
                            records found.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
