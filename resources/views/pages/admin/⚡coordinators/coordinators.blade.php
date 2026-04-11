<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="mt-8 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">
                All OJT Coordinators
                <flux:badge color="red" inset="top bottom">{{ $this->coordinators->total() }}</flux:badge>
            </h2>

            <div class="flex items-center gap-3">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search coordinators..." clearable />

                <livewire:admin.coordinator.create />
            </div>
        </div>

        <flux:table :paginate="$this->coordinators">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                    wire:click="sort('name')">
                    Coordinator
                </flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'employee_id'" :direction="$sortDirection"
                    wire:click="sort('employee_id')">
                    Employee ID
                </flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'department'" :direction="$sortDirection"
                    wire:click="sort('department')">
                    Department
                </flux:table.column>

                <flux:table.column>Position</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->coordinators as $coordinator)
                    <flux:table.row :key="$coordinator->id">
                        <flux:table.cell class="flex items-center gap-3">
                            <flux:avatar size="md" :src="$coordinator->profile_photo_url"
                                :name="$coordinator->name" />

                            <div class="flex flex-col">
                                <span class="font-medium">{{ $coordinator->name }}</span>
                                <span class="text-xs text-zinc-500">{{ $coordinator->email }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap font-medium">
                            {{ $coordinator->professorProfile?->employee_id ?? '—' }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $coordinator->professorProfile?->department ?? '—' }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" color="red" inset="top bottom">
                                OJT Coordinator
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                    inset="top bottom" />

                                <flux:menu>
                                    <flux:modal.trigger :name="'view-coordinator-' . $coordinator->id">
                                        <flux:menu.item icon="eye">View</flux:menu.item>
                                    </flux:modal.trigger>

                                    <flux:modal.trigger :name="'edit-coordinator-' . $coordinator->id">
                                        <flux:menu.item icon="pencil-square">Edit</flux:menu.item>
                                    </flux:modal.trigger>

                                    <flux:menu.separator />

                                    <flux:modal.trigger :name="'delete-coordinator-' . $coordinator->id">
                                        <flux:menu.item variant="danger" icon="trash">Delete</flux:menu.item>
                                    </flux:modal.trigger>
                                </flux:menu>
                            </flux:dropdown>

                            <flux:modal :name="'view-coordinator-' . $coordinator->id" flyout variant="floating"
                                class="md:w-2xl">
                                <livewire:admin.coordinator.view :coordinator="$coordinator" :key="'view-coordinator-' . $coordinator->id" />
                            </flux:modal>

                            <flux:modal :name="'edit-coordinator-' . $coordinator->id" flyout variant="floating"
                                class="md:w-2xl">
                                <livewire:admin.coordinator.edit :coordinator="$coordinator" :key="'edit-coordinator-' . $coordinator->id" />
                            </flux:modal>

                            <flux:modal :name="'delete-coordinator-' . $coordinator->id" class="md:w-xl">
                                <livewire:admin.coordinator.delete :coordinator="$coordinator" :key="'delete-coordinator-' . $coordinator->id" />
                            </flux:modal>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-6 text-center text-zinc-500">
                            No coordinator found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
