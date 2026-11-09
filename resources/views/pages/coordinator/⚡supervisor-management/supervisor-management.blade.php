<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 grid-cols-2 xl:grid-cols-5">
        @php($cards = ['' => ['Total Supervisors', 'total', 'users'], 'internal' => ['Internal Supervisors', 'internal', 'building-library'], 'external' => ['External Supervisors', 'external', 'building-office-2'], 'active' => ['Active Supervisors', 'active', 'check-circle'], 'inactive' => ['Inactive Supervisors', 'inactive', 'no-symbol']])
        @foreach ($cards as $value => [$label, $key, $icon])
            <button type="button" wire:click="$set('status', '{{ $value }}')" class="text-left"
                aria-pressed="{{ $status === $value ? 'true' : 'false' }}">
                <flux:card size="xs" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
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
                <flux:heading size="xl">{{ $cards[$status][0] ?? 'Total Supervisors' }}</flux:heading>
            </div>

            <div class="flex w-full flex-wrap items-center gap-3 xl:w-auto">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    aria-label="Search supervisors..." placeholder="Search supervisors..." clearable class="w-full sm:w-64" />

                <x-filter-modal name="filter-coordinator-supervisors" heading="Filter Supervisors"
                    description="Filter by type or account status and sort your supervisors."
                    :sorts="['name' => 'Last name', 'company_name' => 'Company / institution']">
                    <flux:radio.group wire:model.live="status" label="Type / Account Status" variant="pills">
                        <flux:radio value="" label="All" />
                        <flux:radio value="internal" label="Internal" />
                        <flux:radio value="external" label="External" />
                        <flux:radio value="active" label="Active" />
                        <flux:radio value="inactive" label="Inactive" />
                    </flux:radio.group>
                </x-filter-modal>

                <livewire:coordinator.supervisors.create />
            </div>
        </div>

        @if ($this->supervisors->isEmpty())
            <div class="mt-6 flex flex-col items-center gap-4 py-16 sm:py-24">
                <flux:icon name="building-office-2" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No supervisors found</flux:heading>
                    <flux:text class="mt-2">Create a supervisor account or adjust your search or filter.</flux:text>
                </div>
            </div>
        @else
            <flux:table :paginate="$this->supervisors" bleed container:class="mt-6"
                pagination:class="px-[var(--flux-bleed,1.5rem)] pb-3">
                <flux:table.columns>
                    <flux:table.column>Supervisor</flux:table.column>
                    <flux:table.column>Company / Institution</flux:table.column>
                    <flux:table.column>Assigned Students</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->supervisors as $supervisor)
                        <flux:table.row :key="$supervisor->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar size="md" :src="$supervisor->profile_photo_url" :name="$supervisor->display_name" />

                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $supervisor->display_name }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $supervisor->email }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $supervisor->supervisorProfile?->company_name ?? '' }}</span>
                                    <x-ojt-type-badge :type="$supervisor->supervisorProfile?->supervisor_type" class="mt-1 w-fit" />
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:button variant="filled" size="xs" icon="user" inset="top bottom"
                                    wire:click="viewAssignedStudents({{ $supervisor->id }})">
                                    Students ({{ $supervisor->assigned_students_count }})
                                </flux:button>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                @if ($supervisor->status === 'pending')
                                    <flux:badge size="sm" color="amber" inset="top bottom">Pending Approval</flux:badge>
                                @elseif ($supervisor->status === 'inactive')
                                    <flux:badge size="sm" color="zinc" inset="top bottom">Inactive</flux:badge>
                                @else
                                    <flux:badge size="sm" color="green" inset="top bottom">Active</flux:badge>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />

                                    <flux:menu>
                                        <flux:modal.trigger :name="'edit-supervisor-'.$supervisor->id">
                                            <flux:menu.item icon="pencil-square">Edit</flux:menu.item>
                                        </flux:modal.trigger>

                                        @if ($supervisor->status === 'inactive')
                                            <flux:menu.item icon="check-circle" wire:click="activate({{ $supervisor->id }})">
                                                Activate
                                            </flux:menu.item>
                                        @elseif ($supervisor->status !== 'pending')
                                            <flux:modal.trigger :name="'inactive-supervisor-'.$supervisor->id">
                                                <flux:menu.item icon="no-symbol">Set Inactive</flux:menu.item>
                                            </flux:modal.trigger>
                                        @endif

                                        <flux:menu.separator />

                                        <flux:modal.trigger :name="'delete-supervisor-'.$supervisor->id">
                                            <flux:menu.item icon="archive-box">Archive</flux:menu.item>
                                        </flux:modal.trigger>
                                    </flux:menu>
                                </flux:dropdown>

                                <flux:modal :name="'edit-supervisor-'.$supervisor->id" flyout variant="floating" class="md:w-2xl">
                                    <livewire:coordinator.supervisors.edit :supervisor="$supervisor" :key="'edit-supervisor-'.$supervisor->id" />
                                </flux:modal>

                                <flux:modal :name="'delete-supervisor-'.$supervisor->id" class="w-[calc(100vw-2rem)] max-w-sm!">
                                    <livewire:coordinator.supervisors.delete :supervisor="$supervisor" :key="'delete-supervisor-'.$supervisor->id" />
                                </flux:modal>

                                @if (! in_array($supervisor->status, ['pending', 'inactive'], true))
                                    <flux:modal :name="'inactive-supervisor-'.$supervisor->id"
                                        class="w-[calc(100vw-2rem)] max-w-md!">
                                        <div class="space-y-6 text-left">
                                            <div>
                                                <flux:heading size="lg">Set supervisor to inactive?</flux:heading>
                                                <flux:text class="mt-2">
                                                    {{ $supervisor->display_name }} will be logged out and won't be able
                                                    to log in until you activate their account again.
                                                </flux:text>
                                            </div>

                                            <div class="flex justify-end gap-2">
                                                <flux:modal.close>
                                                    <flux:button variant="ghost">Cancel</flux:button>
                                                </flux:modal.close>

                                                <flux:button variant="danger" icon="no-symbol"
                                                    wire:click="setInactive({{ $supervisor->id }})">
                                                    Set Inactive
                                                </flux:button>
                                            </div>
                                        </div>
                                    </flux:modal>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    <flux:modal name="assigned-students" class="md:w-2xl" @close="closeAssignedStudents">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Assigned Students</flux:heading>
                <flux:subheading>{{ $this->selectedSupervisor?->display_name ?? 'Supervisor' }}</flux:subheading>
            </div>

            @if ($this->assignedStudents->isEmpty())
                <flux:card class="py-10 text-center">
                    <flux:icon name="academic-cap" class="mx-auto size-10" />
                    <flux:heading class="mt-3">No students assigned</flux:heading>
                    <flux:text class="mt-1">This supervisor does not have any assigned students yet.</flux:text>
                </flux:card>
            @else
                <div class="max-h-[60vh] overflow-y-auto">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Student</flux:table.column>
                            <flux:table.column>Student ID</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($this->assignedStudents as $student)
                                <flux:table.row :key="$student->id">
                                    <flux:table.cell>
                                        <div class="flex items-center gap-3">
                                            <flux:avatar size="md" :src="$student->profile_photo_url"
                                                :name="$student->display_name" />
                                            <div class="flex min-w-0 flex-col whitespace-normal">
                                                <span class="font-medium">{{ $student->display_name }}</span>
                                                <span class="break-words text-xs text-zinc-500 dark:text-zinc-300">{{ $student->email }}</span>
                                            </div>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">
                                        {{ $student->studentProfile?->student_id ?? '—' }}
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">
                                        @if ($student->status === 'pending')
                                            <flux:badge size="sm" color="amber">Pending Approval</flux:badge>
                                        @else
                                            <flux:badge size="sm" color="green">Active</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endif
        </div>
    </flux:modal>
</div>
