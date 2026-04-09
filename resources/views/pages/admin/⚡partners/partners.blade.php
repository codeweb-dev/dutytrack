<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="mt-8 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">
                All Companies & Partners
                <flux:badge color="lime" inset="top bottom">{{ $this->companies->total() }}</flux:badge>
            </h2>

            <div class="flex items-center gap-3">
                <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Search companies or partners..." />
                <flux:button icon="funnel">Filter</flux:button>
                <flux:button icon="plus">Add Company</flux:button>
            </div>
        </div>

        <flux:table :paginate="$this->companies">
            <flux:table.columns>
                <flux:table.column>Company</flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'industry'"
                    :direction="$sortDirection"
                    wire:click="sort('industry')"
                >
                    Industry
                </flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'status'"
                    :direction="$sortDirection"
                    wire:click="sort('status')"
                >
                    Status
                </flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'students_assigned'"
                    :direction="$sortDirection"
                    wire:click="sort('students_assigned')"
                >
                    Students Assigned
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->companies as $company)
                    <flux:table.row :key="$company['id']">
                        <flux:table.cell class="flex items-center gap-3">
                            <flux:avatar size="xs" :src="$company['logo']" />

                            <div class="flex flex-col">
                                <span>{{ $company['name'] }}</span>
                                <span class="text-xs text-zinc-400">{{ $company['contact_person'] }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            {{ $company['industry'] }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" :color="$company['status_color']" inset="top bottom">
                                {{ $company['status'] }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell variant="strong">
                            {{ $company['students_assigned'] }}
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