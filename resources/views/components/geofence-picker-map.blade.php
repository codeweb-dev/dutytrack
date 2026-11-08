@props([
    'id',
    'height' => 'height: 18rem',
])

{{-- Binds to form.geofence_latitude / form.geofence_longitude / form.geofence_radius on the parent Livewire component. --}}

@once
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
@endonce

<div class="space-y-3">
    <div
        wire:ignore
        x-data="{
            map: null,
            marker: null,
            circle: null,
            init() {
                const boot = () => setTimeout(() => this.$refs.map.offsetParent && this.boot(), 150);
                boot();
                document.addEventListener('modal-show', boot);
                this.$wire.$watch('form.geofence_latitude', () => this.draw());
                this.$wire.$watch('form.geofence_longitude', () => this.draw());
                this.$wire.$watch('form.geofence_radius', () => this.draw());
            },
            point() {
                const lat = parseFloat(this.$wire.form.geofence_latitude);
                const lng = parseFloat(this.$wire.form.geofence_longitude);
                return Number.isFinite(lat) && Number.isFinite(lng) ? [lat, lng] : null;
            },
            boot() {
                if (this.map) {
                    this.map.invalidateSize();
                    return;
                }

                if (typeof L === 'undefined') {
                    return;
                }

                const point = this.point();
                this.map = L.map(this.$refs.map).setView(point ?? [14.599512, 120.984222], point ? 17 : 12);

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href=\'https://www.openstreetmap.org/copyright\'>OpenStreetMap</a> contributors',
                }).addTo(this.map);

                this.map.on('click', (event) => {
                    this.$wire.set('form.geofence_latitude', Number(event.latlng.lat.toFixed(7)), false);
                    this.$wire.set('form.geofence_longitude', Number(event.latlng.lng.toFixed(7)), false);

                    if (!this.$wire.form.geofence_radius) {
                        this.$wire.set('form.geofence_radius', 100, false);
                    }

                    this.draw();
                });

                this.draw();
            },
            draw() {
                if (!this.map) {
                    return;
                }

                const point = this.point();
                const radius = parseInt(this.$wire.form.geofence_radius) || 0;

                if (!point) {
                    this.marker?.remove();
                    this.circle?.remove();
                    this.marker = this.circle = null;
                    return;
                }

                this.marker = (this.marker ?? L.marker(point).addTo(this.map)).setLatLng(point);
                this.circle = (this.circle ?? L.circle(point, { color: '#ef4444', fillOpacity: 0.12, weight: 1 }).addTo(this.map))
                    .setLatLng(point)
                    .setRadius(radius);
            },
            clear() {
                this.$wire.set('form.geofence_latitude', null, false);
                this.$wire.set('form.geofence_longitude', null, false);
                this.$wire.set('form.geofence_radius', null, false);
                this.draw();
            },
        }"
        class="space-y-2"
    >
        <div
            id="{{ $id }}"
            x-ref="map"
            class="w-full overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700"
            style="{{ $height }}"
        ></div>

        <div class="flex items-center justify-between gap-3">
            <flux:text class="text-xs">Click the map to pin the OJT location.</flux:text>
            <flux:button type="button" size="sm" variant="ghost" icon="x-mark" x-on:click="clear()">Clear pin</flux:button>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <flux:input type="number" step="any" wire:model="form.geofence_latitude" label="Latitude" />
        <flux:input type="number" step="any" wire:model="form.geofence_longitude" label="Longitude" />
        <flux:input type="number" min="10" max="5000" wire:model="form.geofence_radius" label="Radius (m)" />
    </div>
</div>
