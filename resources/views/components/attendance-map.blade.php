@props([
    'id',
    'latitude' => null,
    'longitude' => null,
    'centerLatitude' => 14.599512,
    'centerLongitude' => 120.984222,
    'height' => 'height: 11rem',
    'zoomLevel' => 17,
])

@php
    $hasMarker = $latitude !== null && $longitude !== null;
    $mapLatitude = (float) ($latitude ?? $centerLatitude);
    $mapLongitude = (float) ($longitude ?? $centerLongitude);
    $point = ['lat' => $mapLatitude, 'long' => $mapLongitude];
    $markers = $hasMarker
        ? [
            [
                'lat' => (float) $latitude,
                'long' => (float) $longitude,
                'info' => 'Time-in location',
            ],
        ]
        : [];
@endphp

<div wire:ignore>
    <x-maps-leaflet
        :id="$id"
        :centerPoint="$point"
        :markers="$markers"
        :zoomLevel="(int) $zoomLevel"
        leafletVersion="1.9.4"
        class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700"
        :style="$height"
    />
</div>
