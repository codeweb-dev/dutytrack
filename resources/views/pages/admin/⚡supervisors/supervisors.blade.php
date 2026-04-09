<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="mt-8 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">
                All Supervisors
                <flux:badge color="lime" inset="top bottom">{{ $this->supervisors->total() }}</flux:badge>
            </h2>

            <div class="flex items-center gap-3">
                <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Search supervisors..." />
                <flux:button icon="funnel">Filter</flux:button>
                <flux:button icon="plus">Add Supervisor</flux:button>
            </div>
        </div>

        <flux:table :paginate="$this->supervisors">
            <flux:table.columns>
                <flux:table.column>Supervisor</flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'department'" :direction="$sortDirection"
                    wire:click="sort('department')">
                    Department
                </flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection"
                    wire:click="sort('status')">
                    Status
                </flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'students_count'" :direction="$sortDirection"
                    wire:click="sort('students_count')">
                    Students
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->supervisors as $supervisor)
                    <flux:table.row :key="$supervisor['id']">
                        <flux:table.cell class="flex items-center gap-3">
                            <flux:avatar size="xs" :src="$supervisor['avatar']" />

                            <div class="flex flex-col">
                                <span>{{ $supervisor['name'] }}</span>
                                <span class="text-xs text-zinc-400">{{ $supervisor['email'] }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            {{ $supervisor['department'] }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" :color="$supervisor['status_color']" inset="top bottom">
                                {{ $supervisor['status'] }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell variant="strong">
                            {{ $supervisor['students_count'] }}
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