<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
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

    </div>

    <div class="space-y-4">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <h2 class="text-xl font-semibold">
                Student Assignments
            </h2>

            <div class="flex w-full flex-wrap items-center gap-3 xl:w-auto">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search student or supervisor..." clearable class="w-full sm:w-64" />

                <x-filter-modal name="filter-coordinator-ojt-assignments" heading="Filter Assignments"
                    description="Filter and sort your assigned students by OJT type."
                    :sorts="['' => 'Default', 'student' => 'Student last name', 'student_id' => 'Student ID', 'type' => 'OJT type', 'supervisor' => 'Supervisor last name']">
                    <flux:select wire:model.live="ojt_type" label="OJT Type">
                        <option value="">All Types</option>
                        <option value="internal">Internal</option>
                        <option value="external">External</option>
                    </flux:select>
                </x-filter-modal>

                <livewire:coordinator.ojt-assignments.create />
            </div>
        </div>


        @if ($this->assignments->isEmpty())
            <flux:card class="flex flex-col items-center gap-4 py-24">
                <flux:icon name="briefcase" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No assignments found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search or filter to find what you're looking
                        for.</flux:text>
                </div>
            </flux:card>
        @else
            <flux:table :paginate="$this->assignments" class="w-full">
                <flux:table.columns>
                    <flux:table.column class="w-[26%]">Student</flux:table.column>
                    <flux:table.column class="w-[26%]">Supervisor</flux:table.column>
                    <flux:table.column class="w-[28%]">Pinned Location</flux:table.column>
                    <flux:table.column class="w-[10%]">Type</flux:table.column>
                    <flux:table.column class="w-[10%]">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->assignments as $assignment)
                        @php
                            $assignee =
                                $assignment->companySupervisor;
                        @endphp

                        <flux:table.row :key="$assignment->id">
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="md" :src="$assignment->student?->profile_photo_url"
                                        :name="$assignment->student?->display_name" />

                                    <div class="flex min-w-0 flex-col whitespace-normal">
                                        <span class="font-medium">{{ $assignment->student?->display_name ?? '' }}</span>
                                        <span class="break-words text-xs text-zinc-500 dark:text-zinc-300">{{ $assignment->student?->email ?? '' }}</span>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="md" :src="$assignee?->profile_photo_url"
                                        :name="$assignee?->display_name ?? 'Unassigned'" />

                                    <div class="flex min-w-0 flex-col whitespace-normal">
                                        <span class="font-medium">{{ $assignee?->display_name ?? 'Unassigned' }}</span>
                                        <span class="break-words text-xs text-zinc-500 dark:text-zinc-300">
                                            {{ $assignee?->email ?? 'No supervisor assigned' }}
                                        </span>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                @if ($assignment->hasGeofence())
                                    <a href="{{ $assignment->geofenceMapUrl() }}" target="_blank" class="block whitespace-normal hover:underline">
                                        <x-location-address class="line-clamp-2 text-sm" :latitude="$assignment->geofence_latitude"
                                            :longitude="$assignment->geofence_longitude" />
                                    </a>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" color="sky" inset="top bottom">
                                    {{ ucfirst($assignment->ojt_type) }}
                                </flux:badge>
                            </flux:table.cell>


                            <flux:table.cell>
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

                                <flux:modal :name="'delete-ojt-assignment-'.$assignment->id" class="w-[calc(100vw-2rem)] max-w-sm!">
                                    <livewire:coordinator.ojt-assignments.delete :assignment="$assignment" :key="'delete-ojt-assignment-' . $assignment->id" />
                                </flux:modal>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
