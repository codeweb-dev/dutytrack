@props(['name', 'heading' => 'Filter', 'description' => null, 'sorts' => [], 'width' => 'md:w-xl'])

{{-- ponytail: one wrapper so the hidden <ui-modal> isn't its own flex item (extra gap) --}}
<div>
    <flux:modal.trigger :name="$name">
        <flux:button variant="filled" icon="funnel">Filter</flux:button>
    </flux:modal.trigger>

    <flux:modal :name="$name" :class="$width">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $heading }}</flux:heading>
                @if ($description)
                    <flux:text class="mt-2">{{ $description }}</flux:text>
                @endif
            </div>

            {{ $slot }}

            @if ($sorts)
                <div class="grid grid-cols-2 gap-4">
                    <flux:select wire:model.live="sortBy" label="Sort by">
                        @foreach ($sorts as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="sortDirection" label="Order">
                        <option value="asc">Ascending</option>
                        <option value="desc">Descending</option>
                    </flux:select>
                </div>
            @endif

            <flux:button type="button" variant="primary" wire:click="clearFilters" icon="trash" class="w-full">
                Clear Filter
            </flux:button>
        </div>
    </flux:modal>
</div>
