{{-- Move up / move down / remove buttons for one item of a repeatable resume list. --}}
@props(['field', 'index', 'count', 'label', 'confirm' => null])

<div class="flex shrink-0 items-center">
    <flux:button size="sm" variant="subtle" icon="chevron-up" :disabled="$index === 0"
        wire:click="moveItem('{{ $field }}', {{ $index }}, -1)" aria-label="Move {{ $label }} {{ $index + 1 }} up" />
    <flux:button size="sm" variant="subtle" icon="chevron-down" :disabled="$index === $count - 1"
        wire:click="moveItem('{{ $field }}', {{ $index }}, 1)" aria-label="Move {{ $label }} {{ $index + 1 }} down" />
    <flux:button size="sm" variant="subtle" icon="trash" wire:click="removeItem('{{ $field }}', {{ $index }})"
        :wire:confirm="$confirm" aria-label="Remove {{ $label }} {{ $index + 1 }}" />
</div>
