<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
        <flux:card class="p-4">
            <flux:heading class="flex items-center gap-2">Total Assignments</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['total'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading class="flex items-center gap-2">Internal OJT</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['internal'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading class="flex items-center gap-2">External OJT</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['external'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading class="flex items-center gap-2">Active</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['active'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading class="flex items-center gap-2">Completed</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['completed'] }}</flux:text>
        </flux:card>
    </div>

    <div class="space-y-4">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <h2 class="text-xl font-semibold">
                All OJT Assignments
                <flux:badge color="red" inset="top bottom">{{ $this->assignments->total() }}</flux:badge>
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

                <flux:modal.trigger name="filter-ojt-assignments">
                    <flux:button icon="funnel">Filter</flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <flux:modal name="filter-ojt-assignments" class="md:w-2xl">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Filter OJT Assignments</flux:heading>
                    <flux:text class="mt-2">Filter assignment records by deployment type, supervisor, and status.
                    </flux:text>
                </div>

                <flux:radio.group wire:model.live="ojt_type" label="OJT Type" variant="cards" :indicator="false"
                    class="max-sm:flex-col">
                    <flux:radio value="" icon="squares-2x2" label="All Types" description="All OJT assignments" />
                    <flux:radio value="internal" icon="building-office-2" label="Internal"
                        description="Assigned to professor" />
                    <flux:radio value="external" icon="briefcase" label="External"
                        description="Assigned to company supervisor" />
                </flux:radio.group>

                <flux:radio.group wire:model.live="status" label="Assignment Status" variant="pills">
                    <flux:radio value="" label="All Status" />
                    <flux:radio value="pending" label="Pending" />
                    <flux:radio value="active" label="Active" />
                    <flux:radio value="completed" label="Completed" />
                    <flux:radio value="cancelled" label="Cancelled" />
                </flux:radio.group>

                <flux:select wire:model.live="supervisor_id" placeholder="Supervisor" label="Supervisor">
                    <option value="">All Supervisors</option>
                    @foreach ($this->supervisors as $supervisor)
                        <option value="{{ $supervisor->id }}">
                            {{ $supervisor->first_name }} {{ $supervisor->last_name }}
                        </option>
                    @endforeach
                </flux:select>

                <div class="w-full">
                    <flux:button type="button" variant="primary" wire:click="clearFilters" icon="trash"
                        class="w-full">
                        Clear Filter
                    </flux:button>
                </div>
            </div>
        </flux:modal>

        <flux:table :paginate="$this->assignments">
            <flux:table.columns>
                <flux:table.column>Student</flux:table.column>
                <flux:table.column>ID</flux:table.column>
                <flux:table.column>OJT Type</flux:table.column>
                <flux:table.column>Supervisor</flux:table.column>
                <flux:table.column>Start Date</flux:table.column>
                <flux:table.column>End Date</flux:table.column>
                <flux:table.column>Required Hours</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Remarks</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->assignments as $assignment)
                    @php
                        $supervisor =
                            $assignment->ojt_type === 'internal'
                                ? $assignment->professor
                                : $assignment->companySupervisor;
                    @endphp

                    <flux:table.row :key="$assignment->id">
                        <flux:table.cell class="flex items-center gap-3">
                            <flux:avatar size="md" :src="$assignment->student?->profile_photo_url"
                                :name="$assignment->student?->name" />

                            <div class="flex flex-col">
                                <span class="font-medium">
                                    {{ $assignment->student?->name ?? trim(($assignment->student?->first_name ?? '') . ' ' . ($assignment->student?->last_name ?? '')) }}
                                </span>
                                <span class="text-xs text-zinc-500">
                                    {{ $assignment->student?->email }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap font-medium">
                            {{ $assignment->student?->studentProfile?->student_id ?? '—' }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" color="sky" inset="top bottom">
                                {{ ucfirst($assignment->ojt_type ?? '—') }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $supervisor?->name ?? trim(($supervisor?->first_name ?? '') . ' ' . ($supervisor?->last_name ?? '')) ?: '—' }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $assignment->start_date?->format('M d, Y') ?? '—' }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $assignment->end_date?->format('M d, Y') ?? '—' }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $assignment->required_hours ?? 0 }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" :color="$this->badgeColor($assignment->status)"
                                inset="top bottom">
                                {{ ucfirst($assignment->status) }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $assignment->remarks ?: '—' }}
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="py-6 text-center text-zinc-500">
                            No OJT assignments found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
