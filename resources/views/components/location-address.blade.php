@props(['latitude', 'longitude'])

@php
    $coordinates = number_format($latitude, 6) . ', ' . number_format($longitude, 6);
@endphp

{{-- ponytail: public Nominatim reverse lookup per view, no caching; store the address on time-in if usage grows. --}}
<flux:text wire:key="location-{{ $coordinates }}" title="{{ $coordinates }}"
    x-data="{ address: 'Loading location...' }"
    x-init="fetch('https://nominatim.openstreetmap.org/reverse?format=json&zoom=18&lat={{ $latitude }}&lon={{ $longitude }}')
        .then(response => response.ok ? response.json() : {})
        .then(result => address = result.display_name || '{{ $coordinates }}')
        .catch(() => address = '{{ $coordinates }}')"
    x-text="address" {{ $attributes }}>
    Loading location...
</flux:text>
