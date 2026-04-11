<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="mt-8 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">
                All OJT Coordinators
                {{-- <flux:badge color="red" inset="top bottom">{{ $this->coordinators->total() }}</flux:badge> --}}
            </h2>

            <div class="flex items-center gap-3">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search coordinators..." clearable />

                {{-- <livewire:admin.coordinators.create /> --}}
            </div>
        </div>
    </div>
</div>
