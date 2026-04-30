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

            <form wire:submit="{{ $this->todayAttendance ? 'clockOut' : 'clockIn' }}" class="mt-6 space-y-4">
                @if ($this->todayAttendance && ! $this->todayAttendance->time_out)
                    <flux:input type="number" min="0" max="720" wire:model="break_minutes" label="Break minutes" />
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
                            {{ ucfirst($this->assignment->ojt_type) }} OJT
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
                        <flux:table.column>Status</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($this->attendanceLogs as $log)
                            <flux:table.row :key="$log->id">
                                <flux:table.cell>{{ $log->attendance_date?->format('M d, Y') ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $log->time_in?->format('h:i A') ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $log->time_out?->format('h:i A') ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $log->break_minutes }} min</flux:table.cell>
                                <flux:table.cell>{{ number_format((float) $log->hours_rendered, 2) }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$this->badgeColor($log->status)" inset="top bottom">
                                        {{ ucfirst($log->status) }}
                                    </flux:badge>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="6" class="py-8 text-center text-zinc-500">
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
