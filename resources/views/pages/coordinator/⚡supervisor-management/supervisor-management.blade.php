<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="mt-8 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">
                My Supervisors
            </h2>

            <div class="flex items-center gap-3">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    aria-label="Search supervisors..." placeholder="Search supervisors..." clearable />

                <x-filter-modal name="filter-coordinator-supervisors" heading="Filter Supervisors"
                    description="Sort your supervisors."
                    :sorts="['name' => 'Supervisor name', 'supervisor_id' => 'Supervisor ID', 'company_name' => 'Company / institution']" />

                <livewire:coordinator.supervisors.create />
            </div>
        </div>

        @if ($this->supervisors->isEmpty())
            <flux:card class="flex flex-col items-center gap-4 py-24">
                <flux:icon name="building-office-2" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No supervisors found</flux:heading>
                    <flux:text class="mt-2">Create a supervisor account or adjust your search.</flux:text>
                </div>
            </flux:card>
        @else
            <flux:table :paginate="$this->supervisors">
                <flux:table.columns>
                    <flux:table.column>Supervisor</flux:table.column>
                    <flux:table.column>Supervisor ID</flux:table.column>
                    <flux:table.column>Company / Institution</flux:table.column>
                    <flux:table.column>Contact</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->supervisors as $supervisor)
                        <flux:table.row :key="$supervisor->id">
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="md" :src="$supervisor->profile_photo_url" :name="$supervisor->name" />
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $supervisor->name }}</span>
                                        <span class="text-xs text-zinc-500">{{ $supervisor->email }}</span>
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap font-medium">
                                {{ $supervisor->supervisorProfile?->supervisor_id ?? '' }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span>{{ $supervisor->supervisorProfile?->company_name ?? '' }}</span>
                                    <span class="text-xs text-zinc-500 line-clamp-1">
                                        {{ $supervisor->supervisorProfile?->company_address ?? 'No address provided' }}
                                    </span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $supervisor->contact_number ? '+63 '.$supervisor->contact_number : 'No contact number' }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                                    <flux:menu>
                                        <flux:modal.trigger :name="'view-supervisor-'.$supervisor->id">
                                            <flux:menu.item icon="eye">View</flux:menu.item>
                                        </flux:modal.trigger>
                                        <flux:modal.trigger :name="'edit-supervisor-'.$supervisor->id">
                                            <flux:menu.item icon="pencil-square">Edit</flux:menu.item>
                                        </flux:modal.trigger>
                                        <flux:menu.separator />
                                        <flux:modal.trigger :name="'delete-supervisor-'.$supervisor->id">
                                            <flux:menu.item variant="danger" icon="trash">Delete</flux:menu.item>
                                        </flux:modal.trigger>
                                    </flux:menu>
                                </flux:dropdown>

                                <flux:modal :name="'view-supervisor-'.$supervisor->id" flyout variant="floating" class="md:w-2xl">
                                    <livewire:coordinator.supervisors.view :supervisor="$supervisor" :key="'view-supervisor-'.$supervisor->id" />
                                </flux:modal>
                                <flux:modal :name="'edit-supervisor-'.$supervisor->id" flyout variant="floating" class="md:w-2xl">
                                    <livewire:coordinator.supervisors.edit :supervisor="$supervisor" :key="'edit-supervisor-'.$supervisor->id" />
                                </flux:modal>
                                <flux:modal :name="'delete-supervisor-'.$supervisor->id" class="md:w-xl">
                                    <livewire:coordinator.supervisors.delete :supervisor="$supervisor" :key="'delete-supervisor-'.$supervisor->id" />
                                </flux:modal>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
