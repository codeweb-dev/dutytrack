@props(['type' => null])

{{-- Internal = red, external = blue; matches the coordinator dashboard OJT type chart. --}}
@if ($type)
    <flux:badge size="sm" :color="\App\Support\BadgeColor::for($type)" {{ $attributes }}>
        {{ ucfirst($type) }}
    </flux:badge>
@endif
