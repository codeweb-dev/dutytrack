@props(['type' => null])

{{-- Internal = red, external = blue; matches the coordinator dashboard OJT type chart. --}}
@if ($type)
    <flux:badge size="sm" :color="$type === 'internal' ? 'red' : 'blue'" {{ $attributes }}>
        {{ ucfirst($type) }}
    </flux:badge>
@endif
