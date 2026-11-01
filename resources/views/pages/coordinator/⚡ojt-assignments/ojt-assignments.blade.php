<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
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
    </div>

    <div class="space-y-4">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <h2 class="text-xl font-semibold">
                Student Assignments
            </h2>

            <div class="flex items-center gap-3">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search student or assignee..." clearable />

                <x-filter-modal name="filter-coordinator-ojt-assignments" heading="Filter Assignments"
                    description="Filter and sort your assigned students by OJT type and assignment status."
                    :sorts="['' => 'Default', 'student' => 'Student last name', 'student_id' => 'Student ID', 'type' => 'OJT type', 'supervisor' => 'Supervisor last name', 'status' => 'Status']">
                    <flux:select wire:model.live="ojt_type" label="OJT Type">
                        <option value="">All Types</option>
                        <option value="internal">Internal</option>
                        <option value="external">External</option>
                    </flux:select>

                    <flux:select wire:model.live="status" label="Status">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
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
            <flux:table :paginate="$this->assignments">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Student ID</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Assigned To</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->assignments as $assignment)
                        @php
                            $assignee =
                                $assignment->companySupervisor;
                        @endphp

                        <flux:table.row :key="$assignment->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar size="md" :src="$assignment->student?->profile_photo_url"
                                    :name="$assignment->student?->display_name" />

                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $assignment->student?->display_name ?? '' }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $assignment->student?->email ?? '' }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-medium">
                                {{ $assignment->student?->studentProfile?->student_id ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" color="sky" inset="top bottom">
                                    {{ ucfirst($assignment->ojt_type) }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span>{{ $assignee?->display_name ?? '' }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">
                                        {{ $assignment->ojt_type === 'internal' ? 'Supervisor' : 'Supervisor' }}
                                    </span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" :color="$this->badgeColor($assignment->status)"
                                    inset="top bottom">
                                    {{ ucfirst($assignment->status) }}
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
                                            <flux:menu.item variant="danger" icon="trash">Delete</flux:menu.item>
                                        </flux:modal.trigger>
                                    </flux:menu>
                                </flux:dropdown>

                                <flux:modal :name="'edit-ojt-assignment-'.$assignment->id" flyout variant="floating"
                                    class="md:w-2xl">
                                    <livewire:coordinator.ojt-assignments.edit :assignment="$assignment" :key="'edit-ojt-assignment-' . $assignment->id" />
                                </flux:modal>

                                <flux:modal :name="'delete-ojt-assignment-'.$assignment->id" class="min-w-88">
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
