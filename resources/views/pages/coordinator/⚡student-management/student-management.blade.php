<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="mt-8 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">
                My Students
            </h2>

            <div class="flex items-center gap-3">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search students..." clearable />

                <x-filter-modal name="filter-coordinator-students" heading="Filter Students"
                    description="Filter by account status and sort your students."
                    :sorts="['name' => 'Last name', 'student_id' => 'Student ID', 'assigned_supervisor' => 'Assigned Supervisor']">
                    <flux:radio.group wire:model.live="status" label="Account Status" variant="pills">
                        <flux:radio value="" label="All" />
                        <flux:radio value="pending" label="Pending Approval" />
                        <flux:radio value="active" label="Active" />
                    </flux:radio.group>
                </x-filter-modal>

                <flux:button href="{{ route('coordinator.requirements') }}" icon:trailing="arrow-up-right">
                    View Requirements
                </flux:button>
            </div>
        </div>

        @if ($this->students->isEmpty())
            <flux:card class="flex flex-col items-center gap-4 py-24">
                <flux:icon name="academic-cap" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No students found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search or filter to find what you're looking
                        for.</flux:text>
                </div>
            </flux:card>
        @else
            <flux:table :paginate="$this->students">
                <flux:table.columns>
                    <flux:table.column>
                        Student
                    </flux:table.column>

                    <flux:table.column>
                        Student ID
                    </flux:table.column>

                    <flux:table.column>Contact Number</flux:table.column>

                    <flux:table.column>
                        OJT Hours
                    </flux:table.column>

                    <flux:table.column>
                        Status
                    </flux:table.column>

                    <flux:table.column>Actions</flux:table.column>
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
                                {{ $student->contact_number ? '+63 ' . $student->contact_number : 'No contact number' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" inset="top bottom">
                                    {{ \App\Support\HourFormatter::format($student->studentProfile?->ojt_required_hours ?? 0) }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                @if ($student->status === 'pending')
                                    <flux:badge size="sm" color="amber" inset="top bottom">Pending Approval</flux:badge>
                                @else
                                    <flux:badge size="sm" color="green" inset="top bottom">Active</flux:badge>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                        inset="top bottom" />

                                    <flux:menu>
                                        @if ($student->status === 'pending')
                                            <flux:modal.trigger :name="'review-student-'.$student->id">
                                                <flux:menu.item icon="check-circle">Approve</flux:menu.item>
                                            </flux:modal.trigger>

                                            <flux:menu.item variant="danger" icon="x-circle"
                                                wire:click="reject({{ $student->id }})"
                                                wire:confirm="Reject {{ $student->display_name }}'s sign-up? Their account will be removed so they can sign up again.">
                                                Reject
                                            </flux:menu.item>

                                            <flux:menu.separator />
                                        @endif

                                        <flux:modal.trigger :name="'view-profile-'.$student->id">
                                            <flux:menu.item icon="eye">View</flux:menu.item>
                                        </flux:modal.trigger>

                                        <flux:modal.trigger :name="'edit-student-'.$student->id">
                                            <flux:menu.item icon="pencil-square">Edit</flux:menu.item>
                                        </flux:modal.trigger>

                                        <flux:menu.separator />

                                        <flux:modal.trigger :name="'delete-student-'.$student->id">
                                            <flux:menu.item variant="danger" icon="trash">Delete</flux:menu.item>
                                        </flux:modal.trigger>
                                    </flux:menu>
                                </flux:dropdown>

                                <flux:modal :name="'view-profile-'.$student->id" flyout variant="floating"
                                    class="md:w-2xl">
                                    <livewire:coordinator.student-management.view :student="$student" :key="'view-student-' . $student->id" />
                                </flux:modal>

                                @if ($student->status === 'pending')
                                    <flux:modal :name="'review-student-'.$student->id" flyout variant="floating"
                                        class="md:w-2xl">
                                        <livewire:coordinator.student-management.view :student="$student" :key="'review-student-' . $student->id" />

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
                                @endif

                                <flux:modal :name="'edit-student-'.$student->id" flyout variant="floating"
                                    class="md:w-2xl">
                                    <livewire:coordinator.student-management.edit :student="$student" :key="'edit-student-' . $student->id" />
                                </flux:modal>

                                <flux:modal :name="'delete-student-'.$student->id" class="min-w-88">
                                    <livewire:coordinator.student-management.delete :student="$student"
                                        :key="'delete-student-' . $student->id" />
                                </flux:modal>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
