<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 grid-cols-2 sm:grid-cols-3">
        @php($cards = ['' => ['Total Assignments', 'total', 'clipboard-document-list'], 'internal' => ['Internal OJT', 'internal', 'academic-cap'], 'external' => ['External OJT', 'external', 'briefcase']])
        @foreach ($cards as $value => [$label, $key, $icon])
            <button type="button" wire:click="$set('ojt_type', '{{ $value }}')" class="text-left"
                aria-pressed="{{ $ojt_type === $value ? 'true' : 'false' }}">
                <flux:card size="xs" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                    <flux:heading class="flex items-center gap-2">{{ $label }}
                        <flux:icon :name="$icon" :variant="$ojt_type === $value ? 'solid' : 'outline'"
                            class="ml-auto size-5 {{ $ojt_type === $value ? 'text-zinc-800 dark:text-white' : 'text-zinc-400' }}" />
                    </flux:heading>
                    <flux:heading size="2xl" class="mt-2">{{ $this->summary[$key] }}</flux:heading>
                </flux:card>
            </button>
        @endforeach
    </div>

    <flux:card>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <flux:heading size="xl">{{ $cards[$ojt_type][0] ?? 'Total Assignments' }}</flux:heading>
            </div>

            <div class="flex w-full flex-wrap items-center gap-3 xl:w-auto">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search student or supervisor..." clearable class="w-full sm:w-64" />

                <x-filter-modal name="filter-coordinator-ojt-assignments" heading="Filter Assignments"
                    description="Filter and sort your assigned students by OJT type." :sorts="[
                        '' => 'Default',
                        'student' => 'Student last name',
                        'student_id' => 'Student ID',
                        'type' => 'OJT type',
                        'supervisor' => 'Supervisor last name',
                    ]">
                    <flux:radio.group wire:model.live="ojt_type" label="OJT Type" variant="pills">
                        <flux:radio value="" label="All" />
                        <flux:radio value="internal" label="Internal" />
                        <flux:radio value="external" label="External" />
                    </flux:radio.group>
                </x-filter-modal>

                <livewire:coordinator.ojt-assignments.create />
            </div>
        </div>

        @if ($this->assignments->isEmpty())
            <div class="mt-6 flex flex-col items-center gap-4 py-16 sm:py-24">
                <flux:icon name="briefcase" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No assignments found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search or filter to find what you're looking
                        for.</flux:text>
                </div>
            </div>
        @else
            <flux:table :paginate="$this->assignments" bleed container:class="mt-6"
                pagination:class="px-[var(--flux-bleed,1.5rem)] pb-3">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Supervisor</flux:table.column>
                    <flux:table.column>Pinned Location</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->assignments as $assignment)
                        @php($assignee = $assignment->companySupervisor)

                        <flux:table.row :key="$assignment->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar size="md" :src="$assignment->student?->profile_photo_url"
                                    :name="$assignment->student?->display_name" />

                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $assignment->student?->display_name ?? '' }}</span>
                                    <span
                                        class="text-xs text-zinc-500 dark:text-zinc-300">{{ $assignment->student?->email ?? '' }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="md" :src="$assignee?->profile_photo_url"
                                        :name="$assignee?->display_name ?? 'Unassigned'" />

                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $assignee?->display_name ?? 'Unassigned' }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $assignee?->email ?? 'No supervisor assigned' }}</span>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="max-w-xs">
                                @if ($assignment->hasGeofence())
                                    <a href="{{ $assignment->geofenceMapUrl() }}" target="_blank"
                                        class="block whitespace-normal hover:underline">
                                        <x-location-address class="line-clamp-2 text-sm" :latitude="$assignment->geofence_latitude"
                                            :longitude="$assignment->geofence_longitude" />
                                    </a>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <x-ojt-type-badge :type="$assignment->ojt_type" inset="top bottom" />
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                        inset="top bottom" />

                                    <flux:menu>
                                        <flux:modal.trigger :name="'edit-ojt-assignment-'.$assignment->id">
                                            <flux:menu.item icon="pencil-square">Edit</flux:menu.item>
                                        </flux:modal.trigger>

                                        <flux:menu.separator />

                                        <flux:modal.trigger :name="'delete-ojt-assignment-'.$assignment->id">
                                            <flux:menu.item icon="archive-box">Archive</flux:menu.item>
                                        </flux:modal.trigger>
                                    </flux:menu>
                                </flux:dropdown>

                                <flux:modal :name="'edit-ojt-assignment-'.$assignment->id" flyout variant="floating"
                                    class="md:w-2xl">
                                    <livewire:coordinator.ojt-assignments.edit :assignment="$assignment" :key="'edit-ojt-assignment-' . $assignment->id" />
                                </flux:modal>

                                <flux:modal :name="'delete-ojt-assignment-'.$assignment->id"
                                    class="w-[calc(100vw-2rem)] max-w-sm!">
                                    <livewire:coordinator.ojt-assignments.delete :assignment="$assignment" :key="'delete-ojt-assignment-' . $assignment->id" />
                                </flux:modal>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
