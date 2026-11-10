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
            query: '',
            results: [],
            searching: false,
            searched: false,
            lookup: 0,
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
                    this.pin(event.latlng.lat, event.latlng.lng);
                    this.describe(event.latlng.lat, event.latlng.lng);
                });

                this.draw();
            },
            pin(lat, lng) {
                this.$wire.set('form.geofence_latitude', Number(Number(lat).toFixed(7)), false);
                this.$wire.set('form.geofence_longitude', Number(Number(lng).toFixed(7)), false);

                if (!this.$wire.form.geofence_radius) {
                    this.$wire.set('form.geofence_radius', 100, false);
                }

                this.draw();
            },
            async describe(lat, lng) {
                const loading = 'Loading address…';
                const lookup = ++this.lookup;
                this.query = loading;
                this.results = [];
                this.searched = false;

                let address = null;
                try {
                    const response = await fetch('https://nominatim.openstreetmap.org/reverse?format=json&zoom=18&lat=' + lat + '&lon=' + lng);
                    address = response.ok ? (await response.json()).display_name : null;
                } catch {}

                // Skip stale lookups when the map was clicked again (or the field edited) meanwhile.
                if (lookup === this.lookup && this.query === loading) {
                    this.query = address || lat.toFixed(6) + ', ' + lng.toFixed(6);
                }
            },
            async search() {
                const q = this.query.trim();
                if (!q || this.searching) {
                    return;
                }

                // ponytail: public Nominatim (1 req/s, search on submit only); self-host or swap provider if usage grows.
                this.searching = true;
                try {
                    const response = await fetch('https://nominatim.openstreetmap.org/search?format=json&limit=5&q=' + encodeURIComponent(q));
                    this.results = response.ok ? await response.json() : [];
                } catch {
                    this.results = [];
                } finally {
                    this.searching = false;
                    this.searched = true;
                }
            },
            choose(result) {
                this.results = [];
                this.searched = false;
                this.query = result.display_name;
                this.pin(result.lat, result.lon);
                this.map?.setView([parseFloat(result.lat), parseFloat(result.lon)], 17);
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
                this.query = '';
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

        <div class="relative" x-on:click.outside="results = []; searched = false">
            <div class="flex gap-2">
                <flux:input
                    x-model="query"
                    x-on:keydown.enter.prevent="search()"
                    icon="magnifying-glass"
                    placeholder="Search a place or address"
                    class="flex-1"
                />
                <flux:button type="button" x-on:click="search()" x-bind:disabled="searching">
                    <span x-text="searching ? 'Searching…' : 'Search'"></span>
                </flux:button>
            </div>

            <div
                x-show="results.length || searched"
                x-cloak
                class="absolute z-[1000] mt-1 w-full overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
            >
                <template x-for="result in results" :key="result.place_id">
                    <button
                        type="button"
                        x-on:click="choose(result)"
                        x-text="result.display_name"
                        class="block w-full px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700"
                    ></button>
                </template>
                <p x-show="!results.length" class="px-3 py-2 text-sm text-zinc-500">No places found.</p>
            </div>
        </div>

        <div class="flex items-center justify-between gap-3">
            <flux:text class="text-xs">Search or click the map to pin the OJT location.</flux:text>
            <flux:button type="button" size="sm" variant="ghost" icon="x-mark" x-on:click="clear()">Clear pin</flux:button>
        </div>
    </div>

    <flux:error name="form.geofence_latitude" />
    <flux:error name="form.geofence_longitude" />

    <flux:input type="number" min="10" max="5000" wire:model="form.geofence_radius" label="Radius (m)" />
</div>
