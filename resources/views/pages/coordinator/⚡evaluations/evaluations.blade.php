<div class="flex min-w-0 flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
            placeholder="Search evaluations..." clearable class="w-full sm:max-w-sm" />

        <x-filter-modal name="filter-coordinator-evaluations" heading="Sort Evaluations"
            description="Choose how supervisor evaluations are ordered."
            :sorts="['' => 'Newest first', 'student' => 'Student name', 'student_id' => 'Student ID', 'supervisor' => 'Supervisor', 'date' => 'Evaluation date', 'rating' => 'Rating', 'status' => 'Status']" />
    </div>

    @if ($this->evaluations->isEmpty())
        <flux:card class="flex flex-col items-center gap-3 py-16 text-center">
            <flux:icon name="document-check" class="size-10" />
            <flux:heading size="lg">No evaluations found</flux:heading>
            <flux:text>Supervisor evaluations for your students will appear here.</flux:text>
        </flux:card>
    @else
        <div class="min-w-0 overflow-x-auto">
            <flux:table :paginate="$this->evaluations">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Immediate Supervisor</flux:table.column>
                    <flux:table.column>Evaluation Date</flux:table.column>
                    <flux:table.column>Rating</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->evaluations as $evaluation)
                        <flux:table.row :key="$evaluation->id">
                            <flux:table.cell>
                                <div class="font-medium">{{ $evaluation->student?->display_name }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $evaluation->student?->studentProfile?->student_id }}</div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $evaluation->evaluator?->display_name }}</flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap">{{ $evaluation->evaluation_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap">{{ number_format((float) $evaluation->rating, 1) }} / 100</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$evaluation->status === 'reviewed' ? 'green' : 'amber'" size="sm">
                                    {{ str($evaluation->status)->replace('_', ' ')->title() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-wrap items-center gap-2">
                                    <flux:button size="xs" icon="arrow-down-tray"
                                        href="{{ route('coordinator.evaluations.download', $evaluation) }}">
                                        Download Form
                                    </flux:button>
                                    @if ($evaluation->status === 'submitted')
                                        <flux:button size="xs" variant="primary" icon="check"
                                            wire:click="markReviewed({{ $evaluation->id }})" wire:loading.attr="disabled">
                                            Mark Reviewed
                                        </flux:button>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    @endif
</div>
