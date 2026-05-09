<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="mt-8 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">
                My Students
                <flux:badge color="red" inset="top bottom">{{ $this->students->total() }}</flux:badge>
            </h2>

            <div class="flex items-center gap-3">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search students..." clearable />

                <livewire:coordinator.student-management.create />
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
                    <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                        wire:click="sort('name')">
                        Student
                    </flux:table.column>

                    <flux:table.column sortable :sorted="$sortBy === 'student_id'" :direction="$sortDirection"
                        wire:click="sort('student_id')">
                        Student ID
                    </flux:table.column>

                    <flux:table.column sortable :sorted="$sortBy === 'course'" :direction="$sortDirection"
                        wire:click="sort('course')">
                        Academic Details
                    </flux:table.column>

                    <flux:table.column sortable :sorted="$sortBy === 'ojt_required_hours'" :direction="$sortDirection"
                        wire:click="sort('ojt_required_hours')">
                        OJT Hours
                    </flux:table.column>

                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->students as $student)
                        <flux:table.row :key="$student->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar size="md" :src="$student->profile_photo_url"
                                    :name="$student->name" />

                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $student->name }}</span>
                                    <div>
                                        <span class="text-xs text-zinc-500">{{ $student->email }}</span>
                                        <span class="text-xs text-zinc-500">
                                            •
                                            {{ $student->contact_number ? '+63 ' . $student->contact_number : 'No contact number' }}
                                        </span>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-medium">
                                {{ $student->studentProfile?->student_id ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span>{{ $student->studentProfile?->course ?? '—' }}</span>
                                    <span class="text-xs text-zinc-500">
                                        Year {{ $student->studentProfile?->year_level ?? '—' }}
                                        @if ($student->studentProfile?->section)
                                            • Section {{ $student->studentProfile->section }}
                                        @endif
                                    </span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" inset="top bottom">
                                    {{ \App\Support\HourFormatter::format($student->studentProfile?->ojt_required_hours ?? 0) }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                        inset="top bottom" />

                                    <flux:menu>
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
