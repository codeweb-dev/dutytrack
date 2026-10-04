<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="duty-metrics duty-hours-metrics grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
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
                Assigned Duty Hour Records
                <flux:badge color="green" inset="top bottom">{{ $this->dutyHourLogs->total() }}</flux:badge>
            </h2>

            <div class="flex items-center gap-3">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    aria-label="Search student..." placeholder="Search student..." clearable />

                <flux:modal.trigger name="filter-supervisor-duty-hours">
                    <flux:button icon="funnel">Filter</flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <flux:modal name="filter-supervisor-duty-hours" class="md:w-lg">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Filter Duty Hour Records</flux:heading>
                    <flux:text class="mt-2">Filter your assigned students' duty hour records.</flux:text>
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
                    <flux:text class="mt-2">Try adjusting your search or filter to find records.</flux:text>
                </div>
            </flux:card>
        @else
            <flux:table :paginate="$this->dutyHourLogs">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>ID</flux:table.column>
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>Time</flux:table.column>
                    <flux:table.column>Rendered</flux:table.column>
                    <flux:table.column>Approved</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Remarks</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->dutyHourLogs as $log)
                        <flux:table.row :key="$log->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar size="md" :src="$log->student?->profile_photo_url"
                                    :name="$log->student?->name" />

                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $log->student?->name ?? '' }}</span>
                                    <span class="text-xs text-zinc-500">{{ $log->student?->email ?? '' }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-medium">
                                {{ $log->student?->studentProfile?->student_id ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>{{ $log->log_date?->format('M d, Y') ?? '' }}</flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col text-sm">
                                    <span>{{ $log->attendance?->displayMorningTimeIn()?->format('h:i A') ?? '' }}</span>
                                    <span class="text-xs text-zinc-500">
                                        Out {{ $log->attendance?->displayAfternoonTimeOut()?->format('h:i A') ?? '' }}
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

                            <flux:table.cell>{{ $log->remarks ?: '' }}</flux:table.cell>

                            <flux:table.cell>
                                @if ($log->status === 'pending')
                                    <div class="flex flex-wrap gap-2">
                                        <flux:button type="button" size="sm" variant="primary" icon="check"
                                            wire:click="approveDutyHour({{ $log->id }})">
                                            Approve
                                        </flux:button>
                                        <flux:button type="button" size="sm" variant="danger" icon="x-mark"
                                            wire:click="rejectDutyHour({{ $log->id }})">
                                            Reject
                                        </flux:button>
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-500">
                                        {{ $log->approver?->name ?? 'Reviewed' }}
                                    </span>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
