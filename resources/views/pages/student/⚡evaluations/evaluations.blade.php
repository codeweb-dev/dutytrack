<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="space-y-4">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <h2 class="text-xl font-semibold">
                My Evaluation Records
                <flux:badge color="red" inset="top bottom">{{ $this->evaluations->total() }}</flux:badge>
            </h2>

            <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                placeholder="Search evaluations..." clearable />
        </div>

        @if ($this->evaluations->isEmpty())
            <flux:card class="flex flex-col items-center gap-4 py-24">
                <flux:icon name="check-badge" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No evaluations yet</flux:heading>
                    <flux:text class="mt-2">Submitted practicum evaluations will appear here.</flux:text>
                </div>
            </flux:card>
        @else
            <flux:table :paginate="$this->evaluations">
                <flux:table.columns>
                    <flux:table.column>Evaluator</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Inclusive Dates</flux:table.column>
                    <flux:table.column>Cooperating Program</flux:table.column>
                    <flux:table.column>Rating</flux:table.column>
                    <flux:table.column>Comments</flux:table.column>
                    <flux:table.column>Download</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->evaluations as $evaluation)
                        <flux:table.row :key="$evaluation->id">
                            <flux:table.cell>
                                {{ $evaluation->evaluator?->name ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" color="sky" inset="top bottom">
                                    {{ $this->evaluatorLabel($evaluation->evaluator_type) }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $evaluation->inclusive_from?->format('M d, Y') ?? '' }}
                                @if ($evaluation->inclusive_to)
                                    to {{ $evaluation->inclusive_to->format('M d, Y') }}
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $evaluation->cooperating_program ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ number_format((float) $evaluation->rating, 1) }}</span>
                                    <span class="text-xs text-zinc-500">
                                        {{ $evaluation->evaluation_date?->format('M d, Y') }}
                                    </span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ \Illuminate\Support\Str::limit($evaluation->remarks ?? '', 80) }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:button size="sm" icon="arrow-down-tray"
                                    href="{{ route('student.evaluations.download', $evaluation) }}" target="_blank">
                                    Excel
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
