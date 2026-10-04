<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
        <h2 class="text-xl font-semibold">
            Student Submissions
            <flux:badge color="green" inset="top bottom">{{ $this->submissions->total() }}</flux:badge>
        </h2>

        <div>
            <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                aria-label="Search student..." placeholder="Search student..." clearable />
        </div>
    </div>

    @include('pages::shared.reports-submissions-table', ['logs' => $this->submissions])
</div>
