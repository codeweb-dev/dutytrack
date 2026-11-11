<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-2 gap-4 xl:grid-cols-3">
        @php($cards = ['' => ['Assigned Trainees', 'total', 'users'], 'evaluated' => ['Evaluated', 'evaluated', 'check-badge'], 'pending' => ['Pending Evaluation', 'pending', 'clock']])
        @foreach ($cards as $value => [$label, $key, $icon])
            <button type="button" wire:click="$set('status', '{{ $value }}')" class="group text-left"
                aria-pressed="{{ $status === $value ? 'true' : 'false' }}">
                <flux:card size="xs" class="hover:bg-zinc-50 dark:hover:bg-zinc-700 group-aria-pressed:border-zinc-800 group-aria-pressed:outline-1 group-aria-pressed:outline-zinc-800 dark:group-aria-pressed:border-white dark:group-aria-pressed:outline-white">
                    <flux:heading class="flex items-center gap-2">{{ $label }}
                        <flux:icon :name="$icon" :variant="$status === $value ? 'solid' : 'outline'"
                            class="ml-auto size-5 {{ $status === $value ? 'text-zinc-800 dark:text-white' : 'text-zinc-400' }}" />
                    </flux:heading>
                    <flux:heading size="2xl" class="mt-2">{{ $this->summary[$key] }}</flux:heading>
                </flux:card>
            </button>
        @endforeach
    </div>

    <flux:card>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <flux:heading size="xl">{{ $cards[$status][0] ?? 'Assigned Trainees' }}</flux:heading>
            </div>

            <div class="flex w-full items-center gap-3 xl:w-auto">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search trainees..." clearable class="w-full sm:w-64" />

                <x-filter-modal name="filter-supervisor-evaluations" heading="Filter Evaluations"
                    description="Filter by evaluation status and sort your trainees."
                    :sorts="['' => 'Default', 'student' => 'Student name', 'student_id' => 'Student ID', 'course' => 'Course', 'rating' => 'Rating']">
                    <flux:radio.group wire:model.live="status" label="Evaluation Status" variant="pills">
                        <flux:radio value="" label="All" />
                        <flux:radio value="evaluated" label="Evaluated" />
                        <flux:radio value="pending" label="Pending" />
                    </flux:radio.group>
                </x-filter-modal>
            </div>
        </div>

        @if ($this->assignments->isEmpty())
            <div class="mt-6 flex flex-col items-center gap-4 py-16 sm:py-24">
                <flux:icon name="check-badge" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No trainees found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search or filter to find what you're looking
                        for.</flux:text>
                </div>
            </div>
        @else
            <flux:table :paginate="$this->assignments" bleed container:class="mt-6"
                pagination:class="px-[var(--flux-bleed,1.5rem)] pb-3">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Student ID</flux:table.column>
                    <flux:table.column>Inclusive Dates</flux:table.column>
                    <flux:table.column>Rating</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->assignments as $assignment)
                        @php($evaluation = $assignment->student?->evaluations->sortByDesc('created_at')->first())

                        <flux:table.row :key="$assignment->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar size="md" :src="$assignment->student?->profile_photo_url"
                                    :name="$assignment->student?->display_name" />

                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $assignment->student?->display_name }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $assignment->student?->email }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $assignment->student?->studentProfile?->student_id }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $assignment->student?->studentProfile?->course }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                @if ($evaluation)
                                    {{ $evaluation->inclusive_from?->format('M d, Y') }}
                                    @if ($evaluation->inclusive_to)
                                        to {{ $evaluation->inclusive_to->format('M d, Y') }}
                                    @endif
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                @if ($evaluation)
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ number_format((float) $evaluation->rating, 1) }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $evaluation->evaluation_date?->format('M d, Y') }}</span>
                                    </div>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                @if ($evaluation)
                                    @if ($evaluation->status === 'reviewed')
                                        <flux:badge size="sm" color="green" inset="top bottom">Approved</flux:badge>
                                    @else
                                        <flux:badge size="sm" color="sky" inset="top bottom">Awaiting Approval</flux:badge>
                                    @endif
                                @else
                                    <flux:badge size="sm" color="amber" inset="top bottom">Pending</flux:badge>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                        inset="top bottom" />

                                    <flux:menu>
                                        @if ($evaluation)
                                            <flux:menu.item icon="eye" target="_blank"
                                                href="{{ route('supervisor.evaluations.view', $evaluation) }}">
                                                View
                                            </flux:menu.item>

                                            <flux:menu.item icon="arrow-down-tray" target="_blank"
                                                href="{{ route('supervisor.evaluations.download', $evaluation) }}">
                                                Download
                                            </flux:menu.item>
                                        @else
                                            <flux:menu.item icon="check-badge"
                                                wire:click="openEvaluationModal({{ $assignment->id }})">
                                                Evaluate
                                            </flux:menu.item>
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    @include('components.evaluations.practicum-modal', ['modalName' => 'company_supervisor-evaluation-form'])
</div>
