<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
        <flux:card class="p-4">
            <flux:heading>Total Assignments</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['total'] }}</flux:text>
        </flux:card>
        <flux:card class="p-4">
            <flux:heading>Internal</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['internal'] }}</flux:text>
        </flux:card>
        <flux:card class="p-4">
            <flux:heading>External</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['external'] }}</flux:text>
        </flux:card>
        <flux:card class="p-4">
            <flux:heading>Active</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['active'] }}</flux:text>
        </flux:card>
        <flux:card class="p-4">
            <flux:heading>Completed</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['completed'] }}</flux:text>
        </flux:card>
    </div>

    <div class="space-y-4">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <h2 class="text-xl font-semibold">
                Deployment Report Preview
                <flux:badge color="red" inset="top bottom">{{ $this->deploymentReports->total() }}</flux:badge>
            </h2>

            <div class="flex items-center gap-3">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search student..." clearable />
                <flux:modal.trigger name="deployment-report-filter">
                    <flux:button icon="funnel">Filter</flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <flux:modal name="deployment-report-filter" class="md:w-2xl">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Filter Deployment Reports</flux:heading>
                    <flux:text class="mt-2">Filter deployment records by type, status, and supervisor.</flux:text>
                </div>

                <flux:radio.group wire:model.live="ojt_type" label="OJT Type" variant="cards" :indicator="false"
                    class="max-sm:flex-col">
                    <flux:radio value="" icon="squares-2x2" label="All Types"
                        description="All deployment types" />
                    <flux:radio value="internal" icon="building-office-2" label="Internal"
                        description="Assigned to professor" />
                    <flux:radio value="external" icon="briefcase" label="External"
                        description="Assigned to company supervisor" />
                </flux:radio.group>

                <flux:radio.group wire:model.live="status" label="Status" variant="pills">
                    <flux:radio value="" label="All Status" />
                    <flux:radio value="pending" label="Pending" />
                    <flux:radio value="active" label="Active" />
                    <flux:radio value="completed" label="Completed" />
                    <flux:radio value="cancelled" label="Cancelled" />
                </flux:radio.group>

                <flux:select wire:model.live="supervisor_id" placeholder="Supervisor" label="Supervisor">
                    <option value="">All Supervisors</option>
                    @foreach ($this->supervisors as $supervisor)
                        <option value="{{ $supervisor->id }}">{{ $supervisor->first_name }} {{ $supervisor->last_name }}
                        </option>
                    @endforeach
                </flux:select>

                <flux:button type="button" wire:click="clearFilters" icon="trash" class="w-full">Clear Filter
                </flux:button>
            </div>
        </flux:modal>

        <flux:table :paginate="$this->deploymentReports">
            <flux:table.columns>
                <flux:table.column>Student</flux:table.column>
                <flux:table.column>ID</flux:table.column>
                <flux:table.column>OJT Type</flux:table.column>
                <flux:table.column>Supervisor</flux:table.column>
                <flux:table.column>Start Date</flux:table.column>
                <flux:table.column>End Date</flux:table.column>
                <flux:table.column>Required Hours</flux:table.column>
                <flux:table.column>Status</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->deploymentReports as $report)
                    @php
                        $supervisor =
                            $report->ojt_type === 'internal' ? $report->professor : $report->companySupervisor;
                    @endphp
                    <flux:table.row :key="$report->id">
                        <flux:table.cell>
                            {{ $report->student?->name ?? trim(($report->student?->first_name ?? '') . ' ' . ($report->student?->last_name ?? '')) }}
                        </flux:table.cell>
                        <flux:table.cell>{{ $report->student?->student_id ?? '—' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="sky" inset="top bottom">
                                {{ ucfirst($report->ojt_type ?? '—') }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $supervisor?->name ?? trim(($supervisor?->first_name ?? '') . ' ' . ($supervisor?->last_name ?? '')) ?: '—' }}
                        </flux:table.cell>
                        <flux:table.cell>{{ $report->start_date?->format('M d, Y') ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $report->end_date?->format('M d, Y') ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $report->required_hours ?? 0 }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$this->badgeColor($report->status)" inset="top bottom">
                                {{ ucfirst($report->status) }}</flux:badge>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="py-6 text-center text-zinc-500">No deployment report
                            records found.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
