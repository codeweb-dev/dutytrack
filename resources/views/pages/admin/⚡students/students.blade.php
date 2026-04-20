<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="mt-8 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">
                All Students
                <flux:badge color="red" inset="top bottom">{{ $this->students->total() }}</flux:badge>
            </h2>

            <div>
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search students..." clearable />
            </div>
        </div>

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
                @forelse ($this->students as $student)
                    <flux:table.row :key="$student->id">
                        <flux:table.cell class="flex items-center gap-3">
                            <flux:avatar size="md" :src="$student->profile_photo_url" :name="$student->name" />

                            <div class="flex flex-col">
                                <span class="font-medium">{{ $student->name }}</span>
                                <div class="flex items-center gap-1">
                                    <flux:text class="text-xs">{{ $student->email }}</flux:text>
                                    <flux:text class="text-xs">•</flux:text>
                                    <flux:text class="text-xs">
                                        {{ $student->contact_number ? '+63 ' . $student->contact_number : 'No contact number' }}
                                    </flux:text>
                                </div>
                                <flux:text class="text-xs text-zinc-500">
                                    Coordinator: {{ $student->coordinator?->name ?? 'Unassigned' }} |
                                    {{ $student->coordinator?->professorProfile->department ?? '—' }}
                                </flux:text>
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
                                {{ $student->studentProfile?->ojt_required_hours ?? 0 }} hrs
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
                                </flux:menu>
                            </flux:dropdown>

                            <flux:modal :name="'view-profile-'.$student->id" flyout variant="floating"
                                class="md:w-2xl">
                                <livewire:admin.students.view :student="$student" :key="'view-student-' . $student->id" />
                            </flux:modal>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-6 text-center text-zinc-500">
                            No students found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
