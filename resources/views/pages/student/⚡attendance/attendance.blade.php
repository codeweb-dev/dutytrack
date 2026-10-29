<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <section class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(24rem,1fr)]">
        <flux:card class="p-5">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <flux:heading size="lg">Attendance Calendar</flux:heading>
                    <flux:text class="mt-1">{{ $this->calendar['month']->format('F Y') }}</flux:text>
                </div>
                <div class="flex items-center gap-1">
                    <flux:button type="button" variant="ghost" icon="chevron-left" aria-label="Previous month"
                        wire:click="previousCalendarMonth" />
                    <flux:button type="button" variant="ghost" icon="chevron-right" aria-label="Next month"
                        wire:click="nextCalendarMonth" />
                </div>
            </div>

            <div class="mt-6 grid grid-cols-7 text-center text-xs font-semibold uppercase tracking-wide text-zinc-500">
                @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
                    <div class="py-2">{{ $weekday }}</div>
                @endforeach
            </div>

            <div class="grid grid-cols-7 gap-1">
                @foreach ($this->calendar['days'] as $day)
                    <button type="button" wire:key="calendar-day-{{ $day['date']->format('Y-m-d') }}"
                        wire:click="showAttendanceDay('{{ $day['date']->format('Y-m-d') }}')" @class([
                        'flex min-h-14 flex-col items-center rounded-xl border border-transparent py-2 text-sm',
                        'w-full cursor-pointer transition-colors hover:bg-zinc-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest dark:hover:bg-zinc-800/50',
                        'text-zinc-900 dark:text-white' => $day['is_current_month'],
                        'text-zinc-400 dark:text-zinc-600' => ! $day['is_current_month'],
                        'border-red-500 bg-red-50 font-semibold dark:bg-red-950/30' => $day['is_today'],
                    ])
                        aria-label="View attendance for {{ $day['date']->format('F j, Y') }}{{ $day['status'] ? ': '.ucfirst($day['status']) : '' }}"
                        title="{{ $day['date']->format('F j, Y') }}{{ $day['status'] ? ' · '.ucfirst($day['status']) : '' }}">
                        <time datetime="{{ $day['date']->toDateString() }}">{{ $day['date']->format('j') }}</time>
                        @if ($day['status'])
                            <span class="sr-only">{{ ucfirst($day['status']) }}</span>
                            <span @class([
                                'mt-1 h-2 w-2 rounded-full',
                                'bg-emerald-600' => $day['status'] === 'present',
                                'bg-amber-500' => $day['status'] === 'late',
                                'bg-orange-500' => $day['status'] === 'incomplete',
                                'bg-red-500' => $day['status'] === 'absent',
                                'bg-zinc-500' => ! in_array($day['status'], ['present', 'late', 'incomplete', 'absent'], true),
                            ])></span>
                        @endif
                    </button>
                @endforeach
            </div>

            <div class="mt-5 flex flex-wrap gap-x-4 gap-y-2 text-xs text-zinc-600 dark:text-zinc-400">
                @foreach ([['Present', 'bg-emerald-600'], ['Late', 'bg-amber-500'], ['Incomplete', 'bg-orange-500'], ['Absent', 'bg-red-500']] as [$label, $color])
                    <span class="inline-flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full {{ $color }}"></span>{{ $label }}
                    </span>
                @endforeach
            </div>
        </flux:card>

        <section class="duty-metrics duty-attendance-metrics grid gap-4 sm:grid-cols-2">
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
                <div class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">{{ $this->summary['incomplete'] }}</div>
            </flux:card>

            <flux:card class="p-4">
                <flux:heading>Rendered Hours</flux:heading>
                <div class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">
                    {{ \App\Support\HourFormatter::format($this->summary['hours']) }}
                </div>
            </flux:card>
        </section>
    </section>

    <section class="flex flex-col gap-6">
        <flux:card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">Today</flux:heading>
                    <flux:text class="mt-2">{{ now()->format('F d, Y') }}</flux:text>
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
                        {{ $this->todayAttendance?->displayMorningTimeIn()?->format('h:i A') ?? '' }}
                    </div>
                </flux:card>

                <flux:card class="p-4">
                    <flux:heading>Morning Out</flux:heading>
                    <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                        {{ $this->todayAttendance?->displayMorningTimeOut()?->format('h:i A') ?? '' }}
                    </div>
                </flux:card>

                <flux:card class="p-4">
                    <flux:heading>Afternoon In</flux:heading>
                    <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                        {{ $this->todayAttendance?->displayAfternoonTimeIn()?->format('h:i A') ?? '' }}
                    </div>
                </flux:card>

                <flux:card class="p-4">
                    <flux:heading>Afternoon Out</flux:heading>
                    <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                        {{ $this->todayAttendance?->displayAfternoonTimeOut()?->format('h:i A') ?? '' }}
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
                                <a class="font-medium text-red-600 hover:underline dark:text-red-400"
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
                                        <a class="font-medium text-red-600 hover:underline dark:text-red-400"
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

    </section>

    <flux:modal name="attendance-day-details" class="md:w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $selectedCalendarDate ? \Illuminate\Support\Carbon::parse($selectedCalendarDate)->format('F j, Y') : 'Attendance Details' }}
                </flux:heading>
                <flux:text class="mt-2">Daily attendance details.</flux:text>
            </div>

            @if ($this->selectedAttendance)
                <div class="flex flex-wrap items-center gap-2">
                    <flux:badge :color="$this->badgeColor($this->selectedAttendance->status)" inset="top bottom">
                        {{ ucfirst($this->selectedAttendance->status) }}
                    </flux:badge>
                    @if ($this->selectedAttendance->overtime_enabled)
                        <flux:badge color="blue" inset="top bottom">Overtime enabled</flux:badge>
                    @endif
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ([
                        ['Morning In', $this->selectedAttendance->displayMorningTimeIn()],
                        ['Morning Out', $this->selectedAttendance->displayMorningTimeOut()],
                        ['Afternoon In', $this->selectedAttendance->displayAfternoonTimeIn()],
                        ['Afternoon Out', $this->selectedAttendance->displayAfternoonTimeOut()],
                    ] as [$label, $time])
                        <flux:card class="p-4">
                            <flux:heading>{{ $label }}</flux:heading>
                            <div class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">
                                {{ $time?->format('h:i A') ?? 'Not recorded' }}
                            </div>
                        </flux:card>
                    @endforeach
                </div>

                <div class="space-y-4">
                    <div>
                        <flux:heading>Rendered Hours</flux:heading>
                        <flux:text class="mt-1">{{ \App\Support\HourFormatter::format($this->selectedAttendance->hours_rendered) }}</flux:text>
                    </div>
                    <div>
                        <flux:heading>Remarks</flux:heading>
                        <flux:text class="mt-1 whitespace-pre-line">{{ $this->selectedAttendance->remarks ?: 'No remarks.' }}</flux:text>
                    </div>
                    <div>
                        <flux:heading>Time-in Location</flux:heading>
                        @if ($this->selectedAttendance->hasTimeInLocation())
                            <flux:text class="mt-1">
                                {{ number_format($this->selectedAttendance->time_in_latitude, 6) }},
                                {{ number_format($this->selectedAttendance->time_in_longitude, 6) }}
                                @if ($this->selectedAttendance->time_in_location_accuracy)
                                    · ±{{ $this->selectedAttendance->time_in_location_accuracy }}m
                                @endif
                            </flux:text>
                            <flux:button class="mt-2" size="sm" variant="ghost" icon="map"
                                :href="$this->selectedAttendance->timeInMapUrl()" target="_blank">
                                Open Map
                            </flux:button>
                        @else
                            <flux:text class="mt-1">No location captured.</flux:text>
                        @endif
                    </div>
                </div>
            @else
                <flux:callout icon="information-circle">
                    <flux:callout.text>No attendance record for this date.</flux:callout.text>
                </flux:callout>
            @endif

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">Close</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
