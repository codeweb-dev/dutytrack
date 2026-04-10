<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="mt-8 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">
                All Students
                <flux:badge color="lime" inset="top bottom">{{ $this->students->total() }}</flux:badge>
            </h2>

            <div class="flex items-center gap-3">
                <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Search students..." />

                <livewire:admin.students.create />
            </div>
        </div>

        <flux:table :paginate="$this->students">
            <flux:table.columns>
                <flux:table.column>Student</flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'start_date'" :direction="$sortDirection"
                    wire:click="sort('start_date')">
                    Start Date
                </flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection"
                    wire:click="sort('status')">
                    Status
                </flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'hours_rendered'" :direction="$sortDirection"
                    wire:click="sort('hours_rendered')">
                    Hours
                </flux:table.column>

                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->students as $student)
                    <flux:table.row :key="$student['id']">
                        <flux:table.cell class="flex items-center gap-3">
                            <flux:avatar size="xs" :src="$student['avatar']" />

                            <div class="flex flex-col">
                                <span>{{ $student['name'] }}</span>
                                <span class="text-xs text-zinc-400">{{ $student['company'] }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            {{ $student['start_date'] }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" :color="$student['status_color']" inset="top bottom">
                                {{ $student['status'] }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell variant="strong">
                            {{ $student['hours_rendered'] }} hrs
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</div>
