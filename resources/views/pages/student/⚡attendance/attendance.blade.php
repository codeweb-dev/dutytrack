<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
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
            <flux:heading>Total Hours</flux:heading>
            <div class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">
                {{ number_format($this->summary['hours'], 2) }}
            </div>
        </flux:card>
    </section>

    <section class="grid gap-4 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
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

            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                <flux:card class="p-4">
                    <flux:heading>Time In</flux:heading>
                    <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                        {{ $this->todayAttendance?->time_in?->format('h:i A') ?? '—' }}
                    </div>
                </flux:card>

                <flux:card class="p-4">
                    <flux:heading>Time Out</flux:heading>
                    <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                        {{ $this->todayAttendance?->time_out?->format('h:i A') ?? '—' }}
                    </div>
                </flux:card>
            </div>

            @if ($this->todayAttendance)
                <div class="mt-4 grid gap-3 lg:grid-cols-2">
                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="flex items-center justify-between gap-3">
                            <flux:heading>Time-in Location</flux:heading>
                            @if ($this->todayAttendance->timeInMapUrl())
                                <flux:button size="sm" variant="ghost" icon="map" :href="$this->todayAttendance->timeInMapUrl()" target="_blank">
                                    Open Map
                                </flux:button>
                            @endif
                        </div>

                        @if ($this->todayAttendance->hasTimeInLocation())
                            <div class="mt-3">
                                <x-attendance-map
                                    id="todayAttendanceMap{{ $this->todayAttendance->id }}"
                                    :latitude="$this->todayAttendance->time_in_latitude"
                                    :longitude="$this->todayAttendance->time_in_longitude"
                                />
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
                    </div>

                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <flux:heading>Time-in Selfie</flux:heading>

                        @if ($this->todayAttendance->timeInSelfieUrl())
                            <img src="{{ $this->todayAttendance->timeInSelfieUrl() }}" alt="Time-in selfie"
                                class="mt-3 h-44 w-full rounded-lg object-cover">
                        @else
                            <flux:text class="mt-3">No selfie captured.</flux:text>
                        @endif
                    </div>
                </div>
            @endif

            <form wire:submit="{{ $this->todayAttendance ? 'clockOut' : 'clockIn' }}" class="mt-6 space-y-4">
                @if (! $this->todayAttendance)
                    <div x-data="{
                        locating: false,
                        message: 'Allow location access to attach your time-in map.',
                        captureLocation() {
                            if (!navigator.geolocation) {
                                this.message = 'Location is not supported by this browser.';
                                return;
                            }

                            this.locating = true;
                            this.message = 'Getting your current location...';

                            navigator.geolocation.getCurrentPosition(
                                (position) => {
                                    this.locating = false;
                                    this.message = 'Location captured.';
                                    $wire.set('time_in_latitude', position.coords.latitude);
                                    $wire.set('time_in_longitude', position.coords.longitude);
                                    $wire.set('time_in_location_accuracy', Math.round(position.coords.accuracy || 0));
                                },
                                () => {
                                    this.locating = false;
                                    this.message = 'Location permission is needed before clocking in.';
                                },
                                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 },
                            );
                        },
                    }" x-init="captureLocation()" class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <flux:heading>Time-in Location</flux:heading>
                                <flux:text class="mt-1" x-text="message"></flux:text>
                            </div>

                            <flux:button type="button" icon="map-pin" x-on:click="captureLocation()" x-bind:disabled="locating">
                                Capture Location
                            </flux:button>
                        </div>

                        @if ($time_in_latitude && $time_in_longitude)
                            <div class="mt-4">
                                <x-attendance-map
                                    id="capturedTimeInMap"
                                    :latitude="$time_in_latitude"
                                    :longitude="$time_in_longitude"
                                />
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                                <span>{{ number_format($time_in_latitude, 6) }}, {{ number_format($time_in_longitude, 6) }}</span>
                                @if ($time_in_location_accuracy)
                                    <span>±{{ $time_in_location_accuracy }}m</span>
                                @endif
                                <a class="font-medium text-red-600 hover:underline dark:text-red-400"
                                    href="{{ $this->mapUrl($time_in_latitude, $time_in_longitude) }}" target="_blank">
                                    Open map
                                </a>
                            </div>
                        @endif

                        @error('time_in_latitude')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        @error('time_in_longitude')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-custom-file-upload model="selfie" label="Upload time-in selfie" accept="image/*" capture="user" />
                @elseif (! $this->todayAttendance->time_out)
                    <flux:input type="number" min="0" max="720" wire:model="break_minutes" label="Break minutes" />
                    <flux:switch wire:model="overtime_enabled" label="Enable OT"
                        description="Continue counting rendered hours after 5:00 PM." />
                @endif

                <flux:textarea wire:model="remarks" label="Remarks" rows="3" badge="Optional"
                    placeholder="Add a note for this attendance record" />

                @if (! $this->assignment)
                    <flux:callout icon="exclamation-triangle" color="amber">
                        <flux:callout.heading>No OJT assignment found</flux:callout.heading>
                        <flux:callout.text>
                            Ask your coordinator to assign your OJT placement before recording attendance.
                        </flux:callout.text>
                    </flux:callout>
                @endif

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    @if (! $this->todayAttendance)
                        <flux:button type="submit" variant="primary" icon="arrow-right-end-on-rectangle" :disabled="! $this->assignment">
                            Clock In
                        </flux:button>
                    @elseif (! $this->todayAttendance->time_out)
                        <flux:button type="submit" variant="primary" icon="arrow-left-end-on-rectangle">
                            Clock Out
                        </flux:button>
                    @else
                        <flux:button variant="ghost" icon="check-circle" disabled>
                            Attendance Complete
                        </flux:button>
                    @endif

                    <flux:text>
                        @if ($this->assignment)
                            {{ ucfirst($this->assignment->ojt_type) }} OJT · 7:00 AM to 5:00 PM
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
                        <flux:table.column>Time In</flux:table.column>
                        <flux:table.column>Time Out</flux:table.column>
                        <flux:table.column>Break</flux:table.column>
                        <flux:table.column>Hours</flux:table.column>
                        <flux:table.column>Evidence</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($this->attendanceLogs as $log)
                            <flux:table.row :key="$log->id">
                                <flux:table.cell>{{ $log->attendance_date?->format('M d, Y') ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $log->time_in?->format('h:i A') ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $log->time_out?->format('h:i A') ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $log->break_minutes }} min</flux:table.cell>
                                <flux:table.cell>
                                    {{ number_format((float) $log->hours_rendered, 2) }}
                                    @if ($log->overtime_enabled)
                                        <flux:badge size="sm" color="blue" inset="top bottom">OT</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex items-center gap-2">
                                        @if ($log->timeInSelfieUrl())
                                            <img src="{{ $log->timeInSelfieUrl() }}" alt="Time-in selfie"
                                                class="h-9 w-9 rounded-md object-cover">
                                        @endif

                                        @if ($log->timeInMapUrl())
                                            <flux:button size="sm" variant="ghost" icon="map" :href="$log->timeInMapUrl()" target="_blank" />
                                        @else
                                            <span class="text-zinc-400">—</span>
                                        @endif
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$this->badgeColor($log->status)" inset="top bottom">
                                        {{ ucfirst($log->status) }}
                                    </flux:badge>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="7" class="py-8 text-center text-zinc-500">
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
