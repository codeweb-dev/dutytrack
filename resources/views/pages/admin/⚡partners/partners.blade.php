<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="mt-8 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">
                All Professor & Coordinators
                <flux:badge color="lime" inset="top bottom">{{ $this->professors->total() }}</flux:badge>
            </h2>

            <div class="flex items-center gap-3">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search professors or coordinators..." clearable />

                <livewire:admin.partners.create />
            </div>
        </div>

        <flux:table :paginate="$this->professors">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                    wire:click="sort('name')">
                    Professor / Coordinator
                </flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'employee_id'" :direction="$sortDirection"
                    wire:click="sort('employee_id')">
                    Employee ID
                </flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'department'" :direction="$sortDirection"
                    wire:click="sort('department')">
                    Department
                </flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'position'" :direction="$sortDirection"
                    wire:click="sort('position')">
                    Position
                </flux:table.column>

                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->professors as $professor)
                    <flux:table.row :key="$professor->id">
                        <flux:table.cell class="flex items-center gap-3">
                            <flux:avatar size="md" :src="$professor->profile_photo_url"
                                :name="$professor->name" />

                            <div class="flex flex-col">
                                <span class="font-medium">{{ $professor->name }}</span>
                                <span class="text-xs text-zinc-500">{{ $professor->email }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap font-medium">
                            {{ $professor->professorProfile?->employee_id ?? '' }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $professor->professorProfile?->department ?? '' }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" :color="$this->badgeColor($professor->professorProfile?->position ?? '')"
                                inset="top bottom">
                                {{ $professor->professorProfile?->position ?? '' }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                    inset="top bottom" />

                                <flux:menu>
                                    <flux:modal.trigger :name="'view-professor-'.$professor->id">
                                        <flux:menu.item icon="eye">View</flux:menu.item>
                                    </flux:modal.trigger>

                                    <flux:modal.trigger :name="'edit-professor-'.$professor->id">
                                        <flux:menu.item icon="pencil-square">Edit</flux:menu.item>
                                    </flux:modal.trigger>

                                    <flux:menu.separator />

                                    <flux:modal.trigger :name="'delete-professor-'.$professor->id">
                                        <flux:menu.item variant="danger" icon="trash">Delete</flux:menu.item>
                                    </flux:modal.trigger>
                                </flux:menu>
                            </flux:dropdown>

                            <flux:modal :name="'view-professor-'.$professor->id" flyout variant="floating"
                                class="md:w-2xl">
                                <livewire:admin.partners.view :professor="$professor" :key="'view-professor-' . $professor->id" />
                            </flux:modal>

                            <flux:modal :name="'edit-professor-'.$professor->id" flyout variant="floating"
                                class="md:w-2xl">
                                <livewire:admin.partners.edit :professor="$professor" :key="'edit-professor-' . $professor->id" />
                            </flux:modal>

                            <flux:modal :name="'delete-professor-'.$professor->id" class="md:w-xl">
                                <livewire:admin.partners.delete :professor="$professor" :key="'delete-professor-' . $professor->id" />
                            </flux:modal>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-6 text-center text-zinc-500">
                            No professor or coordinator found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
