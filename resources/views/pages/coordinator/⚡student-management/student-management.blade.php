<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 grid-cols-2 xl:grid-cols-5">
        @php($cards = ['' => ['Total Students', 'total', 'users'], 'pending' => ['Pending Approval', 'pending', 'clock'], 'active' => ['Active Students', 'active', 'check-circle'], 'assigned' => ['Assigned Students', 'assigned', 'briefcase'], 'inactive' => ['Inactive Students', 'inactive', 'no-symbol']])
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
                <flux:heading size="xl">{{ $cards[$status][0] ?? 'Total Students' }}</flux:heading>
            </div>

            <div class="flex w-full items-center gap-3 xl:w-auto">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search students..." clearable class="w-full sm:w-64" />

                <x-filter-modal name="filter-coordinator-students" heading="Filter Students"
                    description="Filter by account status and sort your students." :sorts="[
                        'name' => 'Last name',
                        'student_id' => 'Student ID',
                        'assigned_supervisor' => 'Assigned Supervisor',
                    ]">
                    <flux:radio.group wire:model.live="status" label="Account Status" variant="pills">
                        <flux:radio value="" label="All" />
                        <flux:radio value="pending" label="Pending Approval" />
                        <flux:radio value="active" label="Active" />
                        <flux:radio value="assigned" label="Assigned" />
                        <flux:radio value="inactive" label="Inactive" />
                    </flux:radio.group>
                </x-filter-modal>
            </div>
        </div>

        @if ($this->students->isEmpty())
            <div class="mt-6 flex flex-col items-center gap-4 py-16 sm:py-24">
                <flux:icon name="academic-cap" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No students found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search or filter to find what you're looking
                        for.</flux:text>
                </div>
            </div>
        @else
            <flux:table :paginate="$this->students" bleed container:class="mt-6"
                pagination:class="px-[var(--flux-bleed,1.5rem)] pb-3">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Student ID</flux:table.column>
                    <flux:table.column>Assigned Supervisor</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->students as $student)
                        <flux:table.row :key="$student->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar size="md" :src="$student->profile_photo_url"
                                    :name="$student->display_name" />

                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $student->display_name }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $student->email }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-medium">
                                {{ $student->studentProfile?->student_id ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="md"
                                        :src="$student->ojtAssignment?->companySupervisor?->profile_photo_url"
                                        :name="$student->ojtAssignment?->companySupervisor?->display_name ?? 'Unassigned'" />

                                    <div class="flex flex-col">
                                        <span class="font-medium">
                                            {{ $student->ojtAssignment?->companySupervisor?->display_name ?? 'Unassigned' }}
                                        </span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-300">
                                            {{ $student->ojtAssignment?->companySupervisor?->email ?? 'No supervisor assigned' }}
                                        </span>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                @if ($student->status === 'pending')
                                    <flux:badge size="sm" color="amber" inset="top bottom">Pending Approval
                                    </flux:badge>
                                @elseif ($student->status === 'inactive')
                                    <flux:badge size="sm" color="zinc" inset="top bottom">Inactive</flux:badge>
                                @else
                                    <flux:badge size="sm" color="green" inset="top bottom">Active</flux:badge>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                        inset="top bottom" />

                                    <flux:menu>
                                        @if ($student->status === 'pending')
                                            <flux:modal.trigger :name="'review-student-'.$student->id">
                                                <flux:menu.item icon="check-circle">Approve</flux:menu.item>
                                            </flux:modal.trigger>

                                            <flux:modal.trigger :name="'reject-student-'.$student->id">
                                                <flux:menu.item variant="danger" icon="x-circle">Reject</flux:menu.item>
                                            </flux:modal.trigger>

                                            <flux:menu.separator />
                                        @else
                                            <flux:modal.trigger :name="'view-profile-'.$student->id">
                                                <flux:menu.item icon="eye">View</flux:menu.item>
                                            </flux:modal.trigger>
                                        @endif

                                        <flux:modal.trigger :name="'edit-student-'.$student->id">
                                            <flux:menu.item icon="pencil-square">Edit</flux:menu.item>
                                        </flux:modal.trigger>

                                        @if ($student->status === 'inactive')
                                            <flux:menu.item icon="check-circle" wire:click="activate({{ $student->id }})">
                                                Activate
                                            </flux:menu.item>
                                        @elseif ($student->status !== 'pending')
                                            <flux:modal.trigger :name="'inactive-student-'.$student->id">
                                                <flux:menu.item icon="no-symbol">Set Inactive</flux:menu.item>
                                            </flux:modal.trigger>
                                        @endif

                                        <flux:menu.separator />

                                        <flux:modal.trigger :name="'delete-student-'.$student->id">
                                            <flux:menu.item icon="archive-box">Archive</flux:menu.item>
                                        </flux:modal.trigger>
                                    </flux:menu>
                                </flux:dropdown>

                                @if ($student->status !== 'pending')
                                    <flux:modal :name="'view-profile-'.$student->id" flyout variant="floating"
                                        class="md:w-2xl">
                                        <livewire:coordinator.student-management.view :student="$student"
                                            :key="'view-student-' . $student->id" />
                                    </flux:modal>
                                @else
                                    <flux:modal :name="'review-student-'.$student->id" flyout variant="floating"
                                        class="md:w-2xl">
                                        <livewire:coordinator.student-management.view :student="$student"
                                            :key="'review-student-' . $student->id" />

                                        <div class="mt-6 flex justify-end gap-2">
                                            <flux:modal.close>
                                                <flux:button variant="ghost">Cancel</flux:button>
                                            </flux:modal.close>

                                            <flux:button variant="primary" icon="check-circle"
                                                wire:click="approve({{ $student->id }})">
                                                Confirm &amp; Approve
                                            </flux:button>
                                        </div>
                                    </flux:modal>

                                    <flux:modal :name="'reject-student-'.$student->id"
                                        class="w-[calc(100vw-2rem)] max-w-md!">
                                        <form wire:submit="reject({{ $student->id }})" class="space-y-6">
                                            <div>
                                                <flux:heading size="lg">Reject sign-up</flux:heading>
                                                <flux:text class="mt-2">
                                                    {{ $student->display_name }} will see this reason when they try to
                                                    log in. The sign-up can be restored from the Archive.
                                                </flux:text>
                                            </div>

                                            <flux:textarea wire:model="rejectionReason" label="Reason" rows="4"
                                                required placeholder="e.g. Student ID does not match the masterlist." />

                                            <div class="flex justify-end gap-2">
                                                <flux:modal.close>
                                                    <flux:button variant="ghost">Cancel</flux:button>
                                                </flux:modal.close>

                                                <flux:button type="submit" variant="danger" icon="x-circle">Reject
                                                </flux:button>
                                            </div>
                                        </form>
                                    </flux:modal>
                                @endif

                                <flux:modal :name="'edit-student-'.$student->id" flyout variant="floating"
                                    class="md:w-2xl">
                                    <livewire:coordinator.student-management.edit :student="$student" :key="'edit-student-' . $student->id" />
                                </flux:modal>

                                <flux:modal :name="'delete-student-'.$student->id"
                                    class="w-[calc(100vw-2rem)] max-w-sm!">
                                    <livewire:coordinator.student-management.delete :student="$student"
                                        :key="'delete-student-' . $student->id" />
                                </flux:modal>

                                @if (! in_array($student->status, ['pending', 'inactive'], true))
                                    <flux:modal :name="'inactive-student-'.$student->id"
                                        class="w-[calc(100vw-2rem)] max-w-md!">
                                        <div class="space-y-6">
                                            <div>
                                                <flux:heading size="lg">Set student to inactive?</flux:heading>
                                                <flux:text class="mt-2">
                                                    {{ $student->display_name }} will be logged out and won't be able
                                                    to log in until you activate their account again.
                                                </flux:text>
                                            </div>

                                            <div class="flex justify-end gap-2">
                                                <flux:modal.close>
                                                    <flux:button variant="ghost">Cancel</flux:button>
                                                </flux:modal.close>

                                                <flux:button variant="danger" icon="no-symbol"
                                                    wire:click="setInactive({{ $student->id }})">
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
</div>
