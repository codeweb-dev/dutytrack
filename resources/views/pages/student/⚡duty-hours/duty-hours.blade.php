<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
        <flux:card class="p-4">
            <flux:heading>Rendered Hours</flux:heading>
            <flux:text class="mt-2">{{ \App\Support\HourFormatter::format($this->summary['rendered_hours']) }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading>Approved Hours</flux:heading>
            <flux:text class="mt-2">{{ \App\Support\HourFormatter::format($this->summary['approved_hours']) }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading>Pending Logs</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['pending_count'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading>Approved Logs</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['approved_count'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading>Rejected Logs</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['rejected_count'] }}</flux:text>
        </flux:card>
    </div>

    <div class="space-y-4">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <h2 class="text-xl font-semibold">
                My Duty Hour Records
                <flux:badge color="red" inset="top bottom">{{ $this->dutyHourLogs->total() }}</flux:badge>
            </h2>

            <flux:modal.trigger name="filter-student-duty-hours">
                <flux:button icon="funnel">Filter</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:modal name="filter-student-duty-hours" class="md:w-lg">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Filter Duty Hour Records</flux:heading>
                    <flux:text class="mt-2">Filter your duty hour records by date or status.</flux:text>
                </div>

                <flux:radio.group wire:model.live="status" label="Status" variant="pills">
                    <flux:radio value="" label="All Status" />
                    <flux:radio value="pending" label="Pending" />
                    <flux:radio value="approved" label="Approved" />
                    <flux:radio value="rejected" label="Rejected" />
                </flux:radio.group>

                <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                    <flux:input type="date" wire:model.live="from_date" label="From Date" />
                    <flux:input type="date" wire:model.live="to_date" label="To Date" />
                </div>

                <flux:button type="button" variant="primary" wire:click="clearFilters" icon="trash" class="w-full">
                    Clear Filter
                </flux:button>
            </div>
        </flux:modal>

        @if ($this->dutyHourLogs->isEmpty())
            <flux:card class="flex flex-col items-center gap-4 py-24">
                <flux:icon name="clock" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No duty hour logs found</flux:heading>
                    <flux:text class="mt-2">Completed attendance records will appear here after clock out.</flux:text>
                </div>
            </flux:card>
        @else
            <flux:table :paginate="$this->dutyHourLogs">
                <flux:table.columns>
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>OJT Type</flux:table.column>
                    <flux:table.column>Supervisor</flux:table.column>
                    <flux:table.column>Time</flux:table.column>
                    <flux:table.column>Rendered</flux:table.column>
                    <flux:table.column>Approved</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Reviewed By</flux:table.column>
                    <flux:table.column>Remarks</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->dutyHourLogs as $log)
                        @php
                            $assignment = $log->assignment;
                            $supervisor =
                                $assignment?->ojt_type === 'internal'
                                    ? $assignment?->professor
                                    : $assignment?->companySupervisor;
                        @endphp

                        <flux:table.row :key="$log->id">
                            <flux:table.cell>{{ $log->log_date?->format('M d, Y') ?? '—' }}</flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" color="sky" inset="top bottom">
                                    {{ ucfirst($assignment?->ojt_type ?? '—') }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $supervisor?->name ?? trim(($supervisor?->first_name ?? '') . ' ' . ($supervisor?->last_name ?? '')) ?: '—' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col text-sm">
                                    <span>{{ $log->attendance?->time_in?->format('h:i A') ?? '—' }}</span>
                                    <span class="text-xs text-zinc-500">
                                        Out {{ $log->attendance?->time_out?->format('h:i A') ?? '—' }}
                                    </span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>{{ \App\Support\HourFormatter::format($log->hours_rendered) }}</flux:table.cell>
                            <flux:table.cell>{{ \App\Support\HourFormatter::format($log->approved_hours) }}</flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" :color="$this->badgeColor($log->status)" inset="top bottom">
                                    {{ ucfirst($log->status) }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>{{ $log->approver?->name ?? '—' }}</flux:table.cell>
                            <flux:table.cell>{{ $log->remarks ?: '—' }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
