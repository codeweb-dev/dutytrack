<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <section class="duty-metrics duty-attendance-metrics grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <flux:card class="p-4">
            <flux:heading>Total Records</flux:heading>
            <div class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">{{ $this->summary['total'] }}</div>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading>Present</flux:heading>
            <div class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">{{ $this->summary['present'] }}</div>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading>Incomplete</flux:heading>
            <div class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">{{ $this->summary['incomplete'] }}
            </div>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading>Rendered Hours</flux:heading>
            <div class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">
                {{ \App\Support\HourFormatter::format($this->summary['hours']) }}
            </div>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading>Approved Hours</flux:heading>
            <div class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">
                {{ \App\Support\HourFormatter::format($this->summary['approved_hours']) }}
            </div>
            <flux:text class="mt-2 text-xs">
                {{ \App\Support\HourFormatter::format($this->summary['pending_hours']) }} pending
            </flux:text>
        </flux:card>
    </section>

    <section class="flex flex-col gap-6">
        <flux:card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">Today</flux:heading>
                    <flux:text class="mt-2">{{ now()->format('F d, Y') }} · Philippine time (PHT)</flux:text>
                </div>

                @if ($this->todayAttendance)
                    <flux:badge :color="$this->badgeColor($this->todayAttendance->status)" inset="top bottom">
                        {{ ucfirst($this->todayAttendance->status) }}
                    </flux:badge>
                @else
                    <flux:badge color="zinc" inset="top bottom">Not recorded</flux:badge>
                @endif
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <flux:card class="p-4">
                    <flux:heading>Morning In</flux:heading>
                    <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                        {{ $this->todayAttendance?->displayMorningTimeIn()?->format('h:i A') ?? 'Not recorded' }}
                    </div>
                </flux:card>

                <flux:card class="p-4">
                    <flux:heading>Morning Out</flux:heading>
                    <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                        {{ $this->todayAttendance?->displayMorningTimeOut()?->format('h:i A') ?? 'Not recorded' }}
                    </div>
                </flux:card>

                <flux:card class="p-4">
                    <flux:heading>Afternoon In</flux:heading>
                    <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                        {{ $this->todayAttendance?->displayAfternoonTimeIn()?->format('h:i A') ?? 'Not recorded' }}
                    </div>
                </flux:card>

                <flux:card class="p-4">
                    <flux:heading>Afternoon Out</flux:heading>
                    <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                        {{ $this->todayAttendance?->displayAfternoonTimeOut()?->format('h:i A') ?? 'Not recorded' }}
                    </div>
                </flux:card>
            </div>

            @if ($this->todayAttendance)
                <div class="mt-4">
                    <flux:card>
                        <div class="flex items-center justify-between gap-3">
                            <flux:heading>Time-in Location</flux:heading>
                            @if ($this->todayAttendance->timeInMapUrl())
                                <flux:button size="sm" variant="ghost" icon="map"
                                    :href="$this->todayAttendance->timeInMapUrl()" target="_blank">
                                    Open Map
                                </flux:button>
                            @endif
                        </div>

                        @if ($this->todayAttendance->hasTimeInLocation())
                            <div class="mt-3">
                                <x-attendance-map id="todayAttendanceMap{{ $this->todayAttendance->id }}"
                                    :latitude="$this->todayAttendance->time_in_latitude" :longitude="$this->todayAttendance->time_in_longitude" />
                            </div>
                            <flux:text class="mt-2 text-xs">
                                {{ number_format($this->todayAttendance->time_in_latitude, 6) }},
                                {{ number_format($this->todayAttendance->time_in_longitude, 6) }}
                                @if ($this->todayAttendance->time_in_location_accuracy)
                                    · ±{{ $this->todayAttendance->time_in_location_accuracy }}m
                                @endif
                            </flux:text>
                        @else
                            <flux:text class="mt-3">No location captured.</flux:text>
                        @endif
                    </flux:card>
                </div>
            @endif

            <form wire:submit="recordNextPunch" class="mt-6 space-y-4">
                @if ($this->nextPunch() === 'morning_time_in')
                    <div x-data="{
                        locating: false,
                        message: 'Allow location access to attach your time-in map.',
                        captureLocation() {
                            if (!navigator.geolocation) {
                                this.message = 'Location is not supported by this browser.';
                                return;
                            }
                    
                            this.locating = true;
                            this.message = 'Accept the location permission request to move the map to your current location.';
                    
                            navigator.geolocation.getCurrentPosition(
                                (position) => {
                                    const location = {
                                        id: 'capturedTimeInMap',
                                        latitude: position.coords.latitude,
                                        longitude: position.coords.longitude,
                                        accuracy: Math.round(position.coords.accuracy || 0),
                                    };
                    
                                    this.locating = false;
                                    this.message = 'Location captured.';
                                    window.dispatchEvent(new CustomEvent('attendance-location-found', { detail: location }));
                                    window.dispatchEvent(new CustomEvent('attendance-location-found', {
                                        detail: { ...location, id: 'capturedTimeInFullscreenMap' },
                                    }));
                                    $wire.set('time_in_latitude', location.latitude);
                                    $wire.set('time_in_longitude', location.longitude);
                                    $wire.set('time_in_location_accuracy', location.accuracy);
                                },
                                (error) => {
                                    this.locating = false;
                                    this.message = error.code === error.PERMISSION_DENIED ?
                                        'Location permission was denied. Allow location access in your browser, then capture again.' :
                                        'Unable to get your current location. Check your connection or try again.';
                                }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 },
                            );
                        },
                    }" x-init="captureLocation()"
                        class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <flux:heading>Time-in Location</flux:heading>
                                <flux:text class="mt-1" x-text="message"></flux:text>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <flux:button type="button" icon="map-pin" x-on:click="captureLocation()"
                                    x-bind:disabled="locating">
                                    Capture Location
                                </flux:button>

                                <flux:modal.trigger name="attendance-location-map">
                                    <flux:button type="button" variant="ghost" icon="arrows-pointing-out">
                                        View Map
                                    </flux:button>
                                </flux:modal.trigger>
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-attendance-location-picker-map id="capturedTimeInMap" :latitude="$time_in_latitude"
                                :longitude="$time_in_longitude" />
                        </div>

                        @if ($time_in_latitude && $time_in_longitude)
                            <div
                                class="mt-2 flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                                <span>{{ number_format($time_in_latitude, 6) }},
                                    {{ number_format($time_in_longitude, 6) }}</span>
                                @if ($time_in_location_accuracy)
                                    <span>±{{ $time_in_location_accuracy }}m</span>
                                @endif
                                <a class="font-medium text-forest hover:underline dark:text-sage"
                                    href="{{ $this->mapUrl($time_in_latitude, $time_in_longitude) }}" target="_blank">
                                    Open map
                                </a>
                            </div>
                        @else
                            <flux:text class="mt-2 text-xs">
                                Capture your location to place your time-in marker on the map.
                            </flux:text>
                        @endif

                        <flux:modal name="attendance-location-map" class="w-[calc(100vw-2rem)] max-w-6xl!">
                            <div class="space-y-4">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <flux:heading size="lg">Time-in Location Map</flux:heading>
                                        <flux:text class="mt-2">
                                            Capture your location to center the map and place your time-in marker.
                                        </flux:text>
                                    </div>

                                    <flux:button type="button" icon="map-pin" x-on:click="captureLocation()"
                                        x-bind:disabled="locating">
                                        Capture Location
                                    </flux:button>
                                </div>

                                <x-attendance-location-picker-map id="capturedTimeInFullscreenMap" :latitude="$time_in_latitude"
                                    :longitude="$time_in_longitude" height="height: min(72vh, 42rem)" :zoomLevel="$time_in_latitude && $time_in_longitude ? 17 : 13" />

                                @if ($time_in_latitude && $time_in_longitude)
                                    <div
                                        class="flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                                        <span>{{ number_format($time_in_latitude, 6) }},
                                            {{ number_format($time_in_longitude, 6) }}</span>
                                        @if ($time_in_location_accuracy)
                                            <span>±{{ $time_in_location_accuracy }}m</span>
                                        @endif
                                        <a class="font-medium text-forest hover:underline dark:text-sage"
                                            href="{{ $this->mapUrl($time_in_latitude, $time_in_longitude) }}"
                                            target="_blank">
                                            Open map
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </flux:modal>

                        @error('time_in_latitude')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        @error('time_in_longitude')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @elseif ($this->nextPunch() === 'afternoon_time_out')
                    <flux:switch wire:model="overtime_enabled" label="Enable OT"
                        description="Continue counting rendered hours after 5:00 PM." />
                @endif

                <flux:textarea wire:model="remarks" label="Remarks" rows="3" badge="Optional"
                    placeholder="Add a note for this attendance record" />

                @if ($this->assignment && $this->nextPunch())
                    <flux:callout icon="cube" variant="secondary" inline>
                        <flux:callout.heading>Your Testing Bypass</flux:callout.heading>
                        <flux:callout.text>Create a completed attendance log without waiting for the timer.
                        </flux:callout.text>

                        <x-slot name="actions">
                            <flux:button type="button" variant="ghost" icon="bolt"
                                wire:click="bypassAttendance(60)">
                                Add 1 hour
                            </flux:button>
                            <flux:button type="button" variant="primary" icon="bolt"
                                wire:click="bypassAttendance(480)">
                                Add full day
                            </flux:button>
                        </x-slot>
                    </flux:callout>
                @endif

                @if (!$this->assignment)
                    <flux:callout icon="exclamation-triangle" color="amber">
                        <flux:callout.heading>No OJT assignment found</flux:callout.heading>
                        <flux:callout.text>
                            Ask your coordinator to assign your OJT placement before recording attendance.
                        </flux:callout.text>
                    </flux:callout>
                @endif

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    @if ($this->nextPunch())
                        <flux:button type="submit" variant="primary" :icon="$this->nextPunchIcon()"
                            :disabled="! $this->assignment">
                            {{ $this->nextPunchLabel() }}
                        </flux:button>
                    @else
                        <flux:button variant="ghost" icon="check-circle" disabled>
                            Attendance Complete
                        </flux:button>
                    @endif

                    <flux:text>
                        @if ($this->assignment)
                            {{ ucfirst($this->assignment->ojt_type) }} OJT · Morning and afternoon attendance
                        @else
                            Waiting for assignment
                        @endif
                    </flux:text>
                </div>
            </form>
        </flux:card>

        <flux:card>
            <div>
                <flux:heading size="lg">Attendance History</flux:heading>
                <flux:text class="mt-2">Your recorded attendance logs appear here after you clock in.</flux:text>
            </div>

            <div class="mt-6">
                <flux:table :paginate="$this->attendanceLogs">
                    <flux:table.columns>
                        <flux:table.column>Date</flux:table.column>
                        <flux:table.column>Morning In</flux:table.column>
                        <flux:table.column>Morning Out</flux:table.column>
                        <flux:table.column>Afternoon In</flux:table.column>
                        <flux:table.column>Afternoon Out</flux:table.column>
                        <flux:table.column>Hours</flux:table.column>
                        <flux:table.column>Duty Hours</flux:table.column>
                        <flux:table.column>Location</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($this->attendanceLogs as $log)
                            <flux:table.row :key="$log->id">
                                <flux:table.cell>{{ $log->attendance_date?->format('M d, Y') ?? '' }}
                                </flux:table.cell>
                                <flux:table.cell>{{ $log->displayMorningTimeIn()?->format('h:i A') ?? '' }}</flux:table.cell>
                                <flux:table.cell>{{ $log->displayMorningTimeOut()?->format('h:i A') ?? '' }}</flux:table.cell>
                                <flux:table.cell>{{ $log->displayAfternoonTimeIn()?->format('h:i A') ?? '' }}</flux:table.cell>
                                <flux:table.cell>{{ $log->displayAfternoonTimeOut()?->format('h:i A') ?? '' }}</flux:table.cell>
                                <flux:table.cell>
                                    {{ \App\Support\HourFormatter::format($log->hours_rendered) }}
                                    @if ($log->overtime_enabled)
                                        <flux:badge size="sm" color="blue" inset="top bottom">OT</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($log->dutyHourLog)
                                        <div class="flex flex-col">
                                            <span>{{ \App\Support\HourFormatter::format($log->dutyHourLog->approved_hours) }}
                                                approved</span>
                                            <div class="mt-1">
                                                <flux:badge size="sm"
                                                    :color="$this->dutyBadgeColor($log->dutyHourLog->status)"
                                                    inset="top bottom">
                                                    {{ ucfirst($log->dutyHourLog->status) }}
                                                </flux:badge>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-zinc-400"></span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex items-center gap-2">
                                        @if ($log->timeInMapUrl())
                                            <flux:button size="sm" variant="ghost" icon="map"
                                                :href="$log->timeInMapUrl()" target="_blank" />
                                        @else
                                            <span class="text-zinc-400">—</span>
                                        @endif
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$this->badgeColor($log->status)"
                                        inset="top bottom">
                                        {{ ucfirst($log->status) }}
                                    </flux:badge>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="9" class="py-8 text-center text-zinc-500">
                                    No attendance records found.
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:card>
    </section>
</div>
