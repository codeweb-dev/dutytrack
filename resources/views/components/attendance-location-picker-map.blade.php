@props([
    'id',
    'latitude' => null,
    'longitude' => null,
    'centerLatitude' => 14.599512,
    'centerLongitude' => 120.984222,
    'height' => 'height: 24rem',
    'zoomLevel' => 13,
    'geofence' => null, // ['latitude' => float, 'longitude' => float, 'radius' => int meters]
])

@php
    $config = [
        'id' => $id,
        'latitude' => $latitude === null ? null : (float) $latitude,
        'longitude' => $longitude === null ? null : (float) $longitude,
        'centerLatitude' => (float) ($geofence['latitude'] ?? $centerLatitude),
        'centerLongitude' => (float) ($geofence['longitude'] ?? $centerLongitude),
        'zoomLevel' => (int) $zoomLevel,
        'geofence' => $geofence,
    ];
@endphp

@once
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <style>
        .attendance-leaflet-marker {
            align-items: center;
            background: #ef4444;
            border: 3px solid #ffffff;
            border-radius: 9999px;
            box-shadow: 0 10px 24px rgba(239, 68, 68, 0.35);
            display: flex;
            height: 1.35rem;
            justify-content: center;
            width: 1.35rem;
        }

        .attendance-leaflet-marker::after {
            background: #ffffff;
            border-radius: 9999px;
            content: "";
            display: block;
            height: 0.4rem;
            width: 0.4rem;
        }
    </style>
@endonce

<div
    id="{{ $id }}"
    wire:ignore
    class="w-full overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700"
    data-attendance-location-picker-map
    style="{{ $height }}"
></div>

<script>
    (() => {
        const config = @json($config);

        const initMap = () => {
            const element = document.getElementById(config.id);

            if (!element || element.dataset.leafletReady === 'true' || typeof L === 'undefined') {
                return;
            }

            element.dataset.leafletReady = 'true';

            const center = [
                config.latitude ?? config.centerLatitude,
                config.longitude ?? config.centerLongitude,
            ];
            const hasInitialMarker = config.latitude !== null && config.longitude !== null;

            const map = L.map(element, {
                zoomControl: true,
                attributionControl: true,
            }).setView(center, hasInitialMarker ? 17 : config.zoomLevel);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            }).addTo(map);

            const markerIcon = L.divIcon({
                className: '',
                html: '<span class="attendance-leaflet-marker"></span>',
                iconSize: [22, 22],
                iconAnchor: [11, 11],
            });

            let marker = null;
            let accuracyCircle = null;
            let currentPoint = hasInitialMarker ? center : null;

            const geofenceCircle = config.geofence ? L.circle([config.geofence.latitude, config.geofence.longitude], {
                radius: config.geofence.radius,
                color: '#3b82f6',
                fillColor: '#3b82f6',
                fillOpacity: 0.1,
                weight: 2,
            }).addTo(map) : null;

            if (geofenceCircle) {
                L.marker(geofenceCircle.getLatLng()).bindPopup('Assigned OJT location').addTo(map);
            }

            // Keeps both the OJT radius and the current location in view.
            const focus = (animate = false) => {
                if (geofenceCircle) {
                    const bounds = geofenceCircle.getBounds();

                    if (currentPoint) {
                        bounds.extend(currentPoint);
                    }

                    animate
                        ? map.flyToBounds(bounds, { padding: [24, 24], maxZoom: 17, duration: 0.8 })
                        : map.fitBounds(bounds, { padding: [24, 24], maxZoom: 17 });
                } else if (currentPoint) {
                    animate ? map.flyTo(currentPoint, 17, { duration: 0.8 }) : map.setView(currentPoint, 17);
                }
            };

            focus();

            const setMarker = (latitude, longitude, accuracy = null) => {
                const point = [latitude, longitude];
                currentPoint = point;

                if (marker) {
                    marker.setLatLng(point);
                } else {
                    marker = L.marker(point, { icon: markerIcon }).addTo(map);
                }

                marker.bindPopup('Your current location');
                map.invalidateSize();
                focus(true);
                marker.openPopup();

                if (accuracy) {
                    if (accuracyCircle) {
                        accuracyCircle.setLatLng(point).setRadius(accuracy);
                    } else {
                        accuracyCircle = L.circle(point, {
                            radius: accuracy,
                            color: '#ef4444',
                            fillColor: '#ef4444',
                            fillOpacity: 0.12,
                            weight: 1,
                        }).addTo(map);
                    }
                }
            };

            if (hasInitialMarker) {
                setMarker(config.latitude, config.longitude);
            }

            window.addEventListener('attendance-location-found', (event) => {
                if (!event.detail || event.detail.latitude === undefined || event.detail.longitude === undefined) {
                    return;
                }

                if (event.detail?.id && event.detail.id !== config.id) {
                    return;
                }

                setMarker(
                    Number(event.detail.latitude),
                    Number(event.detail.longitude),
                    event.detail.accuracy ? Number(event.detail.accuracy) : null,
                );
            });

            document.addEventListener('modal-show', () => {
                setTimeout(() => {
                    map.invalidateSize();
                    focus();
                }, 150);
            });

            setTimeout(() => {
                map.invalidateSize();
                focus();
            }, 150);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMap, { once: true });
        } else {
            initMap();
        }

        document.addEventListener('livewire:navigated', initMap);
    })();
</script>
