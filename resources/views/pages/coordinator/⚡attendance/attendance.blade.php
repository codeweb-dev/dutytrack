<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-2 gap-4 xl:grid-cols-5">
        @php
            $legend = ['present' => ['Present', 'bg-emerald-600'], 'late' => ['Late', 'bg-amber-500'], 'incomplete' => ['Incomplete', 'bg-orange-500'], 'absent' => ['Absent', 'bg-red-500']];
            $cards = ['' =>['Total Records', 'total', 'clipboard-document-list'], 'present' => ['Present', 'present', 'check-circle'], 'late' => ['Late', 'late', 'clock'], 'absent' => ['Absent', 'absent', 'x-circle'], 'incomplete' => ['Incomplete', 'incomplete', 'exclamation-circle']];
        @endphp
        @foreach ($cards as $value => [$label, $key, $icon])
            <button type="button" wire:click="$set('status', '{{ $value }}')" class="group text-left"
                aria-pressed="{{ $status === $value ? 'true' : 'false' }}">
                <flux:card size="xs" class="hover:bg-zinc-50 dark:hover:bg-zinc-700 group-aria-pressed:border-zinc-800 group-aria-pressed:outline-1 group-aria-pressed:outline-zinc-800 dark:group-aria-pressed:border-white dark:group-aria-pressed:outline-white">
                    <flux:heading class="flex items-center gap-2">{{ $label }}
                        <flux:icon :name="$icon" :variant="$status === $value ? 'solid' : 'outline'"
                            class="ml-auto size-5 {{ $status === $value ? 'text-zinc-800 dark:text-white' : 'text-zinc-400' }}" />
                    </flux:heading>
                    <flux:heading size="2xl" class="mt-2">{{ $this->summary[$key] }}</flux:heading>
                </flux:card>
            </button>
        @endforeach
    </div>

    <section>
        <flux:card class="p-5">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <flux:heading size="lg">Attendance Calendar</flux:heading>
                    <flux:text class="mt-1">
                        {{ $this->calendar['month']->format('F Y') }} ·
                        {{ \App\Support\HourFormatter::format($this->summary['hours']) }} rendered in total
                    </flux:text>
                </div>
                <div class="flex flex-wrap items-center gap-1">
                    <flux:modal.trigger name="attendance-holidays">
                        <flux:button type="button" size="sm" variant="ghost" icon="calendar-days">View all holidays
                        </flux:button>
                    </flux:modal.trigger>
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
                    <button type="button" wire:key="coordinator-calendar-day-{{ $day['date']->format('Y-m-d') }}"
                        wire:click="showAttendanceDay('{{ $day['date']->format('Y-m-d') }}')"
                        @disabled($day['is_weekend']) @class([
                            'flex min-h-14 w-full flex-col items-center rounded-xl border py-2 text-sm',
                            'cursor-pointer transition-colors hover:bg-zinc-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest dark:hover:bg-zinc-800/50' => ! $day['is_weekend'],
                            'cursor-not-allowed bg-zinc-100 text-zinc-400 dark:bg-zinc-800/40 dark:text-zinc-600' => $day['is_weekend'],
                            'text-zinc-900 dark:text-white' => $day['is_current_month'] && ! $day['is_weekend'],
                            'text-zinc-400 dark:text-zinc-600' => ! $day['is_current_month'],
                            'border-transparent' => ! $day['is_today'] && ! $day['holiday'],
                            'border-amber-300 bg-amber-50 dark:bg-amber-950/30' => $day['holiday'] && ! $day['is_today'],
                            'border-red-500 bg-red-50 font-semibold dark:bg-red-950/30' => $day['is_today'],
                        ])
                        aria-label="View attendance for {{ $day['date']->format('F j, Y') }}{{ $day['holiday'] ? ': Holiday, ' . $day['holiday'] : '' }}: {{ $day['count'] }} {{ \Illuminate\Support\Str::plural('record', $day['count']) }}"
                        title="{{ $day['date']->format('F j, Y') }}{{ $day['holiday'] ? ' · ' . $day['holiday'] : '' }} · {{ $day['count'] }} {{ \Illuminate\Support\Str::plural('record', $day['count']) }}">
                        <time datetime="{{ $day['date']->toDateString() }}">{{ $day['date']->format('j') }}</time>
                        @if ($day['holiday'])
                            <span class="sr-only">Holiday: {{ $day['holiday'] }}</span>
                            <span class="mt-1 h-2 w-2 rounded-full bg-amber-500"></span>
                        @endif
                        @if ($day['count'] > 0)
                            <span class="mt-1 flex flex-wrap justify-center gap-1">
                                @foreach ($legend as $key => [$label, $color])
                                    @if ($day['statuses'][$key] ?? 0)
                                        <span class="inline-flex min-w-5 items-center justify-center rounded-full px-1.5 text-[10px] font-semibold leading-5 text-white {{ $color }}"
                                            title="{{ $label }}">
                                            {{ $day['statuses'][$key] }}<span class="sr-only"> {{ $label }}</span>
                                        </span>
                                    @endif
                                @endforeach
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>

            <div class="mt-5 flex flex-wrap gap-x-4 gap-y-2 text-xs text-zinc-600 dark:text-zinc-400">
                @foreach ($legend as [$label, $color])
                    <span class="inline-flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full {{ $color }}"></span>{{ $label }}
                    </span>
                @endforeach
                <span class="inline-flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>Holiday
                </span>
            </div>
        </flux:card>

    </section>

    <flux:modal name="attendance-day-details" class="w-[90vw] max-w-[90vw]!">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $selectedCalendarDate ? \Illuminate\Support\Carbon::parse($selectedCalendarDate)->format('F j, Y') : 'Attendance Details' }}
                </flux:heading>
                <flux:text class="mt-2">{{ $this->selectedAttendance->total() }} {{ $status === '' ? 'attendance' : strtolower($cards[$status][0]) }} records from your students.</flux:text>
            </div>

            @if ($this->selectedAttendance->isEmpty() && ($selectedHoliday = $this->selectedHoliday))
                <flux:callout icon="calendar-days" color="amber">
                    <flux:callout.heading>Public holiday</flux:callout.heading>
                    <flux:callout.text>{{ $selectedHoliday['name'] }}. Students cannot time in on holidays.</flux:callout.text>
                </flux:callout>
            @elseif ($this->selectedAttendance->isEmpty())
                <flux:callout icon="information-circle">
                    <flux:callout.text>No attendance records for this date.</flux:callout.text>
                </flux:callout>
            @else
                <flux:table :paginate="$this->selectedAttendance">
                    <flux:table.columns>
                        <flux:table.column>Student</flux:table.column>
                        <flux:table.column>Student ID</flux:table.column>
                        <flux:table.column>Supervisor</flux:table.column>
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
                                <flux:table.cell>{{ $attendance->student?->display_name ?? 'Unknown student' }}</flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap font-medium">{{ $attendance->student?->studentProfile?->student_id ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $attendance->assignment?->companySupervisor?->display_name ?? '—' }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$this->badgeColor($attendance->status)" inset="top bottom">
                                        {{ ucfirst($attendance->status ?? 'recorded') }}
                                    </flux:badge>
                                    @if ($attendance->isLate() && $attendance->status !== 'late')
                                        <flux:badge size="sm" color="amber" inset="top bottom">Late</flux:badge>
                                    @endif
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

    <flux:modal name="attendance-holidays" class="md:w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Public holidays {{ $this->calendar['month']->format('Y') }}</flux:heading>
                <flux:text class="mt-2">Philippines public holidays for the selected calendar year.</flux:text>
            </div>

            @if ($this->holidays)
                <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($this->holidays as $holiday)
                        <li wire:key="holiday-{{ $holiday['date'] }}" class="flex justify-between gap-4 py-3">
                            <span>{{ $holiday['name'] }}</span>
                            <time datetime="{{ $holiday['date'] }}"
                                class="shrink-0 text-zinc-500">{{ \Illuminate\Support\Carbon::parse($holiday['date'])->format('M j, Y') }}</time>
                        </li>
                    @endforeach
                </ul>
            @elseif (! $this->holidaysAvailable)
                <flux:callout icon="exclamation-triangle" color="amber">
                    <flux:callout.text>Public holidays could not be loaded.</flux:callout.text>
                </flux:callout>
            @else
                <flux:callout icon="information-circle">
                    <flux:callout.text>No public holidays are available for this year.</flux:callout.text>
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
