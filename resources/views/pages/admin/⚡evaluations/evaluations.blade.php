<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
        <flux:card class="p-4">
            <flux:heading>Total Evaluations</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['total'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading>Pending</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['pending'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading>Submitted</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['submitted'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading>Reviewed</flux:heading>
            <flux:text class="mt-2">{{ $this->summary['reviewed'] }}</flux:text>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading>Average Rating</flux:heading>
            <flux:text class="mt-2">{{ number_format($this->summary['average_rating'], 2) }}</flux:text>
        </flux:card>
    </div>

    <div class="space-y-4">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <h2 class="text-xl font-semibold">
                All Evaluation Records
                <flux:badge color="red" inset="top bottom">{{ $this->evaluations->total() }}</flux:badge>
            </h2>

            <div class="flex items-center gap-3">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search student..." clearable />

                <flux:modal.trigger name="filter-evaluations">
                    <flux:button icon="funnel">Filter</flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <flux:modal name="filter-evaluations" class="md:w-2xl">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Filter Evaluation Records</flux:heading>
                    <flux:text class="mt-2">Filter evaluation records by type, evaluator, status, and date.
                    </flux:text>
                </div>

                <flux:radio.group wire:model.live="ojt_type" label="OJT Type" variant="cards" :indicator="false"
                    class="max-sm:flex-col">
                    <flux:radio value="" icon="squares-2x2" label="All Types" description="All OJT types" />
                    <flux:radio value="internal" icon="building-office-2" label="Internal"
                        description="Professor evaluation" />
                    <flux:radio value="external" icon="briefcase" label="External"
                        description="Company supervisor evaluation" />
                </flux:radio.group>

                <flux:radio.group wire:model.live="status" label="Status" variant="pills">
                    <flux:radio value="" label="All Status" />
                    <flux:radio value="pending" label="Pending" />
                    <flux:radio value="submitted" label="Submitted" />
                    <flux:radio value="reviewed" label="Reviewed" />
                </flux:radio.group>

                <flux:select wire:model.live="evaluator_id" label="Evaluator">
                    <option value="">All Evaluators</option>
                    @foreach ($this->evaluators as $evaluator)
                        <option value="{{ $evaluator->id }}">
                            {{ $evaluator->first_name }} {{ $evaluator->last_name }}
                        </option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input type="date" wire:model.live="from_date" label="From Date" />
                    <flux:input type="date" wire:model.live="to_date" label="To Date" />
                </div>

                <flux:button type="button" wire:click="clearFilters" icon="trash" class="w-full">
                    Clear Filter
                </flux:button>
            </div>
        </flux:modal>

        @if ($this->evaluations->isEmpty())
            <flux:card class="flex flex-col items-center gap-4 py-24">
                <flux:icon name="check-badge" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No Evaluation Records found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search or filter to find what you're looking
                        for.</flux:text>
                </div>
            </flux:card>
        @else
            <flux:table :paginate="$this->evaluations">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>ID</flux:table.column>
                    <flux:table.column>OJT Type</flux:table.column>
                    <flux:table.column>Evaluator</flux:table.column>
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>Rating</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Remarks</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->evaluations as $evaluation)
                        <flux:table.row :key="$evaluation->id">
                            <flux:table.cell>
                                {{ $evaluation->student?->name ?? trim(($evaluation->student?->first_name ?? '') . ' ' . ($evaluation->student?->last_name ?? '')) }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $evaluation->student?->student_id ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" color="sky" inset="top bottom">
                                    {{ ucfirst($evaluation->assignment?->ojt_type ?? '') }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $evaluation->evaluator?->name ?? trim(($evaluation->evaluator?->first_name ?? '') . ' ' . ($evaluation->evaluator?->last_name ?? '')) ?: '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $evaluation->evaluation_date?->format('M d, Y') ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ number_format((float) $evaluation->rating, 2) }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" :color="$this->badgeColor($evaluation->status)"
                                    inset="top bottom">
                                    {{ ucfirst($evaluation->status) }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $evaluation->remarks ?: '' }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="8" class="py-6 text-center text-zinc-500">
                                No evaluation records found.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
