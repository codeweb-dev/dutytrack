<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
        <h2 class="text-xl font-semibold">
            Trainee Submissions
        </h2>

        <div class="flex w-full flex-wrap items-center gap-3 xl:w-auto">
            <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                placeholder="Search trainee..." clearable class="w-full sm:w-64" />

            <x-filter-modal name="filter-supervisor-reports" heading="Filter Submissions"
                description="Filter by date range and sort trainee submissions."
                :sorts="['' => 'Default', 'student' => 'Student name', 'student_id' => 'Student ID', 'date' => 'Date', 'hours' => 'Hours']">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input type="date" wire:model.live="from_date" label="From Date" />
                    <flux:input type="date" wire:model.live="to_date" label="To Date" />
                </div>
            </x-filter-modal>
        </div>
    </div>

    @include('pages::shared.reports-submissions-table', ['logs' => $this->submissions])
</div>
