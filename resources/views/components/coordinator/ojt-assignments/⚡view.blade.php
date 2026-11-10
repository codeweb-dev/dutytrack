<?php

use App\Models\OjtAssignment;
use Livewire\Component;

new class extends Component {
    public OjtAssignment $assignment;

    public function mount(OjtAssignment $assignment): void
    {
        abort_unless((int) $assignment->student?->coordinator_id === (int) auth()->id(), 403);

        $this->assignment = $assignment;
    }
};
?>

@php($student = $assignment->student)
@php($supervisor = $assignment->companySupervisor)

<div class="space-y-4">
    <div class="flex items-center gap-3 border-b border-zinc-200 pb-4 dark:border-zinc-800">
        <flux:avatar size="lg" :src="$student?->profile_photo_url" :name="$student?->display_name" />
        <div class="min-w-0 flex-1">
            <h3 class="truncate text-lg font-semibold text-zinc-900 dark:text-white">{{ $student?->display_name }}</h3>
        </div>
        <x-ojt-type-badge :type="$assignment->ojt_type" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <section class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50">
            <h4 class="text-base font-semibold text-zinc-900 dark:text-white">Student Information</h4>
            <dl class="mt-4 grid gap-y-4">
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Email</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $student?->email ?: '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Student ID</dt>
                    <dd class="mt-1 text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $student?->studentProfile?->student_id ?: '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Course</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $student?->studentProfile?->course ?: '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Required OJT Hours</dt>
                    <dd class="mt-1 text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $student?->studentProfile?->ojt_required_hours ?: '—' }}
                    </dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50">
            <h4 class="text-base font-semibold text-zinc-900 dark:text-white">Supervisor</h4>
            <dl class="mt-4 grid gap-y-4">
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Name</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $supervisor?->display_name ?? 'Unassigned' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Email</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $supervisor?->email ?: '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ $supervisor?->supervisorProfile?->supervisor_type === 'internal' ? 'Institution' : 'Company' }}
                    </dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $supervisor?->supervisorProfile?->company_name ?: '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Contact Number</dt>
                    <dd class="mt-1 text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $supervisor?->contact_number ? '+63 ' . $supervisor->contact_number : 'No contact number' }}
                    </dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50 sm:col-span-2">
            <h4 class="text-base font-semibold text-zinc-900 dark:text-white">Attendance Location</h4>
            @if ($assignment->hasGeofence())
                @once
                    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
                    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
                @endonce

                {{-- Read-only; boots when the modal opens since Leaflet can't size a hidden container. --}}
                <div
                    wire:ignore
                    x-data="{
                        map: null,
                        init() {
                            const boot = () => setTimeout(() => this.$el.offsetParent && this.boot(), 150);
                            boot();
                            document.addEventListener('modal-show', boot);
                        },
                        boot() {
                            if (this.map) {
                                this.map.invalidateSize();
                                return;
                            }

                            if (typeof L === 'undefined') {
                                return;
                            }

                            const point = [{{ (float) $assignment->geofence_latitude }}, {{ (float) $assignment->geofence_longitude }}];
                            this.map = L.map(this.$el).setView(point, 17);

                            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '&copy; <a href=\'https://www.openstreetmap.org/copyright\'>OpenStreetMap</a> contributors',
                            }).addTo(this.map);

                            L.marker(point).addTo(this.map);
                            const circle = L.circle(point, { radius: {{ (int) $assignment->geofence_radius }}, color: '#ef4444', fillOpacity: 0.12, weight: 1 }).addTo(this.map);
                            this.map.fitBounds(circle.getBounds(), { maxZoom: 17 });
                        },
                    }"
                    class="mt-4 w-full overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700"
                    style="height: 16rem"
                ></div>

                <dl class="mt-4 grid gap-4 sm:grid-cols-[1fr_auto]">
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pinned Location</dt>
                        <dd class="mt-1 text-base leading-6 font-medium">
                            <a href="{{ $assignment->geofenceMapUrl() }}" target="_blank" class="hover:underline">
                                <x-location-address :latitude="$assignment->geofence_latitude"
                                    :longitude="$assignment->geofence_longitude" />
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Radius</dt>
                        <dd class="mt-1 text-base leading-6 font-medium text-zinc-900 dark:text-white">
                            {{ $assignment->geofence_radius }} m
                        </dd>
                    </div>
                </dl>
            @else
                <flux:text class="mt-4">No attendance location pinned.</flux:text>
            @endif
        </section>

        <section class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50 sm:col-span-2">
            <h4 class="text-base font-semibold text-zinc-900 dark:text-white">Assignment Metadata</h4>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Created</dt>
                    <dd class="mt-1 text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $assignment->created_at?->format('M d, Y h:i A') ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Last Updated</dt>
                    <dd class="mt-1 text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $assignment->updated_at?->format('M d, Y h:i A') ?? '—' }}
                    </dd>
                </div>
            </dl>
        </section>
    </div>
</div>
