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
                    <button type="button" wire:key="supervisor-calendar-day-{{ $day['date']->format('Y-m-d') }}"
                        wire:click="showAttendanceDay('{{ $day['date']->format('Y-m-d') }}')" @class([
                            'flex min-h-14 w-full cursor-pointer flex-col items-center rounded-xl border border-transparent py-2 text-sm transition-colors hover:bg-zinc-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest dark:hover:bg-zinc-800/50',
                            'text-zinc-900 dark:text-white' => $day['is_current_month'],
                            'text-zinc-400 dark:text-zinc-600' => ! $day['is_current_month'],
                            'border-red-500 bg-red-50 font-semibold dark:bg-red-950/30' => $day['is_today'],
                        ])
                        aria-label="View attendance for {{ $day['date']->format('F j, Y') }}: {{ $day['count'] }} {{ \Illuminate\Support\Str::plural('record', $day['count']) }}"
                        title="{{ $day['date']->format('F j, Y') }} · {{ $day['count'] }} {{ \Illuminate\Support\Str::plural('record', $day['count']) }}">
                        <time datetime="{{ $day['date']->toDateString() }}">{{ $day['date']->format('j') }}</time>
                        @if ($day['count'] > 0)
                            <span class="mt-1 inline-flex min-w-5 items-center justify-center rounded-full bg-emerald-100 px-1.5 text-[10px] font-semibold leading-5 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-100">
                                {{ $day['count'] }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>

            <div class="mt-5 flex flex-wrap gap-x-4 gap-y-2 text-xs text-zinc-600 dark:text-zinc-400">
                <span class="inline-flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-emerald-600"></span>Attendance records
                </span>
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

    <flux:modal name="attendance-day-details" class="w-[90vw] max-w-[90vw]!">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $selectedCalendarDate ? \Illuminate\Support\Carbon::parse($selectedCalendarDate)->format('F j, Y') : 'Attendance Details' }}
                </flux:heading>
                <flux:text class="mt-2">{{ $this->selectedAttendance->count() }} attendance records from your assigned students.</flux:text>
            </div>

            @if ($this->selectedAttendance->isEmpty())
                <flux:callout icon="information-circle">
                    <flux:callout.text>No attendance records for this date.</flux:callout.text>
                </flux:callout>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Student</flux:table.column>
                        <flux:table.column>Student ID</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column>Morning In</flux:table.column>
                        <flux:table.column>Morning Out</flux:table.column>
                        <flux:table.column>Afternoon In</flux:table.column>
                        <flux:table.column>Afternoon Out</flux:table.column>
                        <flux:table.column>Hours</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->selectedAttendance as $attendance)
                            <flux:table.row :key="$attendance->id">
                                <flux:table.cell>{{ $attendance->student?->name ?? 'Unknown student' }}</flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap font-medium">{{ $attendance->student?->studentProfile?->student_id ?? '—' }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$this->badgeColor($attendance->status)" inset="top bottom">
                                        {{ ucfirst($attendance->status ?? 'recorded') }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>{{ $attendance->displayMorningTimeIn()?->format('h:i A') ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $attendance->displayMorningTimeOut()?->format('h:i A') ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $attendance->displayAfternoonTimeIn()?->format('h:i A') ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $attendance->displayAfternoonTimeOut()?->format('h:i A') ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ \App\Support\HourFormatter::format($attendance->hours_rendered) }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">Close</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
