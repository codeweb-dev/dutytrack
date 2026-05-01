@props([
    'id',
    'latitude',
    'longitude',
    'height' => 'height: 11rem',
])

@php
    $latitude = (float) $latitude;
    $longitude = (float) $longitude;
    $point = ['lat' => $latitude, 'long' => $longitude];
    $markers = [
        [
            'lat' => $latitude,
            'long' => $longitude,
            'info' => 'Time-in location',
        ],
    ];
@endphp

<div wire:ignore>
    <x-maps-leaflet
        :id="$id"
        :centerPoint="$point"
        :markers="$markers"
        :zoomLevel="17"
        leafletVersion="1.9.4"
        class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700"
        :style="$height"
    />
</div>
