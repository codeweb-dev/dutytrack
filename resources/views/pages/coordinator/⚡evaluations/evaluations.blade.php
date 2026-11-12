<div class="flex min-w-0 flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
        @php($cards = ['' => ['Total Evaluations', 'total', 'document-check'], 'submitted' => ['Pending Approval', 'submitted', 'inbox-arrow-down'], 'reviewed' => ['Approved', 'reviewed', 'check-circle']])
        @foreach ($cards as $value => [$label, $key, $icon])
            <button type="button" wire:click="$set('status', '{{ $value }}')" class="group text-left"
                aria-pressed="{{ $status === $value ? 'true' : 'false' }}">
                <flux:card size="xs"
                    class="hover:bg-zinc-50 dark:hover:bg-zinc-700 group-aria-pressed:border-zinc-800 group-aria-pressed:outline-1 group-aria-pressed:outline-zinc-800 dark:group-aria-pressed:border-white dark:group-aria-pressed:outline-white">
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
                <flux:heading size="xl">{{ $cards[$status][0] ?? 'Total Evaluations' }}</flux:heading>
            </div>

            <div class="flex w-full items-center gap-3 xl:w-auto">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search evaluations..." clearable class="w-full sm:w-64" />

                <x-filter-modal name="filter-coordinator-evaluations" heading="Filter Evaluations"
                    description="Filter by status and choose how supervisor evaluations are ordered." :sorts="[
                        '' => 'Newest first',
                        'student' => 'Student name',
                        'supervisor' => 'Supervisor',
                        'date' => 'Evaluation date',
                        'rating' => 'Rating',
                        'status' => 'Status',
                    ]">
                    <flux:radio.group wire:model.live="status" label="Status" variant="pills">
                        <flux:radio value="" label="All" />
                        <flux:radio value="pending" label="Pending" />
                        <flux:radio value="submitted" label="Pending Approval" />
                        <flux:radio value="reviewed" label="Approved" />
                    </flux:radio.group>
                </x-filter-modal>
            </div>
        </div>

        @if ($this->evaluations->isEmpty())
            <div class="mt-6 flex flex-col items-center gap-4 py-16 sm:py-24">
                <flux:icon name="document-check" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No evaluations found</flux:heading>
                    <flux:text class="mt-2">Supervisor evaluations for your students will appear here.</flux:text>
                </div>
            </div>
        @else
            <flux:table :paginate="$this->evaluations" bleed container:class="mt-6"
                pagination:class="px-[var(--flux-bleed,1.5rem)] pb-3">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Supervisor</flux:table.column>
                    <flux:table.column>Evaluation Date</flux:table.column>
                    <flux:table.column>Rating</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->evaluations as $evaluation)
                        <flux:table.row :key="$evaluation->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar size="md" :src="$evaluation->student?->profile_photo_url"
                                    :name="$evaluation->student?->display_name" />

                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $evaluation->student?->display_name }}</span>
                                    <span
                                        class="text-xs text-zinc-500 dark:text-zinc-300">{{ $evaluation->student?->email }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="md" :src="$evaluation->evaluator?->profile_photo_url"
                                        :name="$evaluation->evaluator?->display_name" />

                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $evaluation->evaluator?->display_name }}</span>
                                        <span
                                            class="text-xs text-zinc-500 dark:text-zinc-300">{{ $evaluation->evaluator?->email }}</span>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ $evaluation->evaluation_date?->format('M d, Y') }}</flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ number_format((float) $evaluation->rating, 1) }} / 100</flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <flux:badge
                                    :color="\App\Support\BadgeColor::for(match ($evaluation->status) { 'reviewed' => 'approved', 'submitted' => 'awaiting_approval', default => 'pending' })"
                                    size="sm" inset="top bottom">
                                    {{ match ($evaluation->status) {'reviewed' => 'Approved','submitted' => 'Pending Approval',default => str($evaluation->status)->title()} }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" tooltip="Actions" aria-label="Actions"
                                        inset="top bottom" />

                                    <flux:menu>
                                        <flux:menu.item icon="eye" target="_blank"
                                            href="{{ route('coordinator.evaluations.view', $evaluation) }}">
                                            View
                                        </flux:menu.item>

                                        <flux:menu.item icon="arrow-down-tray"
                                            href="{{ route('coordinator.evaluations.download', $evaluation) }}">
                                            Download Form
                                        </flux:menu.item>

                                        @if ($evaluation->status === 'submitted')
                                            <flux:menu.item icon="check"
                                                wire:click="markReviewed({{ $evaluation->id }})">
                                                Approve
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
</div>
