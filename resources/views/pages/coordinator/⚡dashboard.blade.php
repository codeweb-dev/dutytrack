<?php

use App\Models\AttendanceLog;
use App\Models\OjtAssignment;
use App\Models\StudentRequirement;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Coordinator Dashboard')] class extends Component
{
    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'Coordinator Dashboard']];

    #[Computed]
    public function title(): string
    {
        return 'Welcome back, '.(Auth::user()?->name ?? 'Coordinator').' 👋';
    }

    public string $description = 'A focused view of your students, placement progress, weekly activity, and OJT progress.';

    protected function coordinatorId(): int
    {
        return (int) Auth::id();
    }

    protected function managedStudentsQuery(): Builder
    {
        return User::query()->where('coordinator_id', $this->coordinatorId())->role('student');
    }

    protected function managedAssignmentsQuery(): Builder
    {
        return OjtAssignment::query()->whereHas('student', function (Builder $query) {
            $query->where('coordinator_id', $this->coordinatorId());
        });
    }

    protected function managedAttendanceQuery(): Builder
    {
        return AttendanceLog::query()->whereHas('student', function (Builder $query) {
            $query->where('coordinator_id', $this->coordinatorId());
        });
    }


    #[Computed]
    public function topCards(): array
    {
        return [
            [
                'title' => 'Total Students',
                'value' => $this->managedStudentsQuery()->count(),
                'icon' => 'users',
                'href' => route('coordinator.student-management'),
                'link' => 'View students',
            ],
            [
                'title' => 'Assigned Students',
                'value' => $this->managedStudentsQuery()->whereHas('ojtAssignment', fn (Builder $query) => $query->whereNotNull('company_supervisor_id'))->count(),
                'icon' => 'briefcase',
                'href' => route('coordinator.ojt-assignments'),
                'link' => 'View assignments',
            ],
            [
                'title' => 'Total Supervisors',
                'value' => User::query()->where('coordinator_id', $this->coordinatorId())->role('supervisor')->count(),
                'icon' => 'building-office-2',
                'href' => route('coordinator.supervisor-management'),
                'link' => 'View supervisors',
            ],
            [
                'title' => 'Present Today',
                'value' => $this->managedAttendanceQuery()->whereDate('attendance_date', today())->distinct()->count('student_id'),
                'icon' => 'clipboard-document-list',
                'href' => route('coordinator.attendance'),
                'link' => 'View attendance',
            ],
        ];
    }

    #[Computed]
    public function hoursProgress(): array
    {
        $students = $this->managedStudentsQuery()
            ->leftJoin('student_profiles', 'users.id', '=', 'student_profiles.user_id')
            ->select('users.id', 'student_profiles.ojt_required_hours')
            ->selectSub(AttendanceLog::query()->selectRaw('coalesce(sum(hours_rendered), 0)')->whereColumn('student_id', 'users.id'), 'rendered_hours')
            ->get();

        $required = (float) $students->sum('ojt_required_hours');
        $rendered = (float) $students->sum(fn ($student) => min((float) $student->rendered_hours, (float) $student->ojt_required_hours));
        $completed = $students->filter(fn ($student) => $student->ojt_required_hours > 0 && $student->rendered_hours >= $student->ojt_required_hours)->count();
        $started = $students->filter(fn ($student) => $student->rendered_hours > 0)->count();

        return [
            'rendered' => $rendered,
            'required' => $required,
            'percentage' => $required > 0 ? round($rendered / $required * 100) : 0,
            'completed' => $completed,
            'in_progress' => $started - $completed,
            'not_started' => $students->count() - $started,
        ];
    }

    #[Computed]
    public function requirementsProgress(): array
    {
        $total = $this->managedStudentsQuery()->count();
        $complete = $this->managedStudentsQuery()
            ->whereHas('studentRequirements')
            ->whereDoesntHave('studentRequirements', fn (Builder $query) => $query->where('is_completed', false))
            ->count();

        return [
            'total' => $total,
            'complete' => $complete,
            'pending' => $total - $complete,
            'percentage' => $total > 0 ? round($complete / $total * 100) : 0,
            'for_checking' => StudentRequirement::query()
                ->whereIn('key', StudentRequirement::activeKeys())
                ->where('is_completed', false)
                ->whereNotNull('submitted_at')
                ->whereHas('student', fn (Builder $query) => $query->where('coordinator_id', $this->coordinatorId()))
                ->count(),
        ];
    }

    #[Computed]
    public function todayAttendance(): array
    {
        $counts = $this->managedAttendanceQuery()
            ->whereDate('attendance_date', today())
            ->selectRaw('status, COUNT(DISTINCT student_id) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $assigned = $this->managedStudentsQuery()->whereHas('ojtAssignment', fn (Builder $query) => $query->whereNotNull('company_supervisor_id'))->count();

        $rows = collect([
            'present' => ['Present', 'bg-emerald-500'],
            'late' => ['Late', 'bg-amber-500'],
            'incomplete' => ['Incomplete', 'bg-sky-500'],
            'absent' => ['Absent', 'bg-red-500'],
        ])->map(fn (array $meta, string $status) => ['label' => $meta[0], 'color' => $meta[1], 'value' => (int) ($counts[$status] ?? 0)]);

        // ponytail: any status outside the four above is folded into "No log yet".
        $rows['none'] = ['label' => 'No log yet', 'color' => 'bg-zinc-300 dark:bg-zinc-600', 'value' => max($assigned - $rows->sum('value'), 0)];

        return ['total' => max($assigned, $rows->sum('value')), 'logged' => $rows->except('none')->sum('value'), 'rows' => $rows->values()->all()];
    }

    #[Computed]
    public function weeklyAttendance(): array
    {
        $days = collect(range(6, 0, -1))
            ->map(fn (int $offset) => now()->subDays($offset)->startOfDay())
            ->values();

        $counts = $this->managedAttendanceQuery()
            ->selectRaw('DATE(attendance_date) as day_key, COUNT(*) as total')
            ->whereBetween('attendance_date', [$days->first(), $days->last()->copy()->endOfDay()])
            ->groupBy(DB::raw('DATE(attendance_date)'))
            ->pluck('total', 'day_key');

        $values = $days->map(fn ($day) => (int) ($counts[$day->toDateString()] ?? 0));
        $step = max((int) ceil($values->max() / 4), 1);
        $top = $step * 4;

        // Coordinates are percentages of the plot area (y grows downward).
        $points = $days->map(fn ($day, int $i) => [
            'label' => $day->format('D'),
            'date' => $day->format('F j'),
            'value' => $values[$i],
            'x' => round($i * 100 / ($days->count() - 1), 2),
            'y' => round(100 - $values[$i] / $top * 100, 2),
        ]);

        // Horizontal-midpoint control points give a smooth curve that never overshoots the data.
        $line = 'M'.$points[0]['x'].' '.$points[0]['y'];
        for ($i = 1; $i < $points->count(); $i++) {
            [$a, $b] = [$points[$i - 1], $points[$i]];
            $mid = ($a['x'] + $b['x']) / 2;
            $line .= " C{$mid} {$a['y']} {$mid} {$b['y']} {$b['x']} {$b['y']}";
        }

        return [
            'points' => $points->all(),
            'line' => $line,
            'area' => $line.' L100 100 L0 100 Z',
            'ticks' => range(0, $top, $step),
        ];
    }

    #[Computed]
    public function placementMix(): array
    {
        $counts = $this->managedAssignmentsQuery()
            ->selectRaw('ojt_type, COUNT(*) as total')
            ->groupBy('ojt_type')
            ->pluck('total', 'ojt_type');

        $total = (int) $counts->sum();
        $types = ['internal' => ['stroke-red-500', 'bg-red-500'], 'external' => ['stroke-blue-500', 'bg-blue-500']];
        $gap = collect($types)->keys()->filter(fn ($type) => ($counts[$type] ?? 0) > 0)->count() > 1 ? 0.8 : 0;
        $offset = 0;
        $segments = [];

        // The donut circle has a circumference of 100, so each slice length is its percentage.
        foreach ($types as $type => [$stroke, $dot]) {
            $value = (int) ($counts[$type] ?? 0);
            $percentage = $total > 0 ? $value / $total * 100 : 0;

            $segments[] = [
                'label' => ucfirst($type),
                'value' => $value,
                'percentage' => round($percentage),
                'stroke' => $stroke,
                'dot' => $dot,
                'dash' => max($percentage - $gap, 0).' '.(100 - max($percentage - $gap, 0)),
                'offset' => -$offset,
            ];

            $offset += $percentage;
        }

        return ['total' => $total, 'segments' => $segments];
    }
};
?>

<div class="flex flex-col gap-8">
    <div data-tour="dashboard-overview"><x-page-header :breadcrumbs="$breadcrumbs" :title="$this->title" :description="$description" /></div>

    <section class="grid grid-cols-2 gap-4 xl:grid-cols-4" data-tour="dashboard-metrics">
        @foreach ($this->topCards as $card)
            <a href="{{ $card['href'] }}" wire:navigate class="group text-left">
                <flux:card size="xs" class="h-full hover:bg-zinc-50 dark:hover:bg-zinc-700">
                    <flux:heading class="flex items-center gap-2">{{ $card['title'] }}
                        <flux:icon :name="$card['icon']" class="ml-auto size-5 text-zinc-400" />
                    </flux:heading>
                    <flux:heading size="2xl" class="mt-2">{{ number_format($card['value']) }}</flux:heading>
                    <div class="mt-3 flex items-center gap-1.5 border-t border-zinc-100 pt-3 text-sm text-zinc-500 transition group-hover:text-zinc-900 dark:border-zinc-700 dark:text-zinc-400 dark:group-hover:text-white">
                        {{ $card['link'] }}
                        <flux:icon name="arrow-right" variant="micro" class="transition group-hover:translate-x-0.5" />
                    </div>
                </flux:card>
            </a>
        @endforeach
    </section>

    <section class="grid gap-4 xl:grid-cols-[minmax(0,1.6fr)_minmax(320px,1fr)]" data-tour="dashboard-workflow">
        @php $attendance = $this->weeklyAttendance; @endphp
        <flux:card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">Weekly Attendance</flux:heading>
                    <flux:text class="mt-2">Attendance logs from your students over the last 7 days.</flux:text>
                </div>

                <flux:badge color="red" inset="top bottom">Last 7 Days</flux:badge>
            </div>

            <div class="mt-8 flex gap-3" x-data="{ active: null, points: @js($attendance['points']) }">
                <div class="relative h-60 w-8 shrink-0 text-right text-xs text-zinc-500 dark:text-zinc-400">
                    @foreach ($attendance['ticks'] as $tick)
                        <span class="absolute right-0 translate-y-1/2" style="bottom: {{ $loop->index * 100 / ($loop->count - 1) }}%">{{ number_format($tick) }}</span>
                    @endforeach
                </div>

                <div class="relative flex-1 pb-7">
                    <div class="relative h-60"
                        @mousemove="const r = $el.getBoundingClientRect(); active = Math.round(Math.min(Math.max(($event.clientX - r.left) / r.width, 0), 1) * (points.length - 1))"
                        @mouseleave="active = null">
                        @foreach ($attendance['ticks'] as $tick)
                            <div class="absolute inset-x-0 border-t {{ $loop->first ? 'border-zinc-300 dark:border-zinc-600' : 'border-zinc-100 dark:border-zinc-700/60' }}"
                                style="bottom: {{ $loop->index * 100 / ($loop->count - 1) }}%"></div>
                        @endforeach

                        <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 size-full overflow-visible" role="img"
                            aria-label="Attendance logs: {{ collect($attendance['points'])->map(fn ($p) => $p['label'].' '.$p['value'])->join(', ') }}">
                            <defs>
                                <linearGradient id="attendance-fill" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#ef4444" stop-opacity="0.35" />
                                    <stop offset="100%" stop-color="#ef4444" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            <path d="{{ $attendance['area'] }}" fill="url(#attendance-fill)" />
                            <path d="{{ $attendance['line'] }}" fill="none" class="stroke-red-500" stroke-width="2.5" stroke-linecap="round" vector-effect="non-scaling-stroke" />
                        </svg>

                        <template x-if="active !== null">
                            <div>
                                <div class="pointer-events-none absolute inset-y-0 border-l border-dashed border-zinc-400 dark:border-zinc-500" :style="`left: ${points[active].x}%`"></div>
                                <div class="pointer-events-none absolute size-3 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white bg-red-500 shadow dark:border-zinc-900"
                                    :style="`left: ${points[active].x}%; top: ${points[active].y}%`"></div>
                                <div class="pointer-events-none absolute top-4 z-10 min-w-40 overflow-hidden rounded-lg border border-zinc-200 bg-white text-sm shadow-lg dark:border-zinc-600 dark:bg-zinc-800"
                                    :style="points[active].x > 60 ? `right: calc(${100 - points[active].x}% + 12px)` : `left: calc(${points[active].x}% + 12px)`">
                                    <div class="border-b border-zinc-200 px-3 py-2 font-medium text-zinc-900 dark:border-zinc-600 dark:text-white" x-text="points[active].date"></div>
                                    <div class="flex items-center gap-2 px-3 py-2">
                                        <span class="size-2.5 rounded-full bg-red-500"></span>
                                        <span class="text-zinc-600 dark:text-zinc-300">Attendance</span>
                                        <span class="ml-auto font-medium text-zinc-900 tabular-nums dark:text-white" x-text="points[active].value"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    @foreach ($attendance['points'] as $point)
                        <span class="absolute bottom-0 -translate-x-1/2 whitespace-nowrap text-xs text-zinc-500 dark:text-zinc-400" style="left: {{ $point['x'] }}%">{{ $point['label'] }}</span>
                    @endforeach
                </div>
            </div>
        </flux:card>

        @php $mix = $this->placementMix; @endphp
        <flux:card class="flex flex-col">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">Placement Mix</flux:heading>
                    <flux:text class="mt-2">How your placed students split between internal and external OJT.</flux:text>
                </div>

                <flux:badge color="red" inset="top bottom">Overview</flux:badge>
            </div>

            <div class="relative mx-auto mt-6 size-56" x-data="{ active: null, tip: { x: 0, y: 0 }, segments: @js($mix['segments']) }"
                @mousemove="const r = $el.getBoundingClientRect(); tip = { x: $event.clientX - r.left, y: $event.clientY - r.top }">
                <svg viewBox="0 0 42 42" class="size-full -rotate-90" role="img"
                    aria-label="Placement mix: {{ collect($mix['segments'])->map(fn ($s) => $s['label'].' '.$s['value'])->join(', ') }}">
                    <circle cx="21" cy="21" r="15.915" fill="none" stroke-width="7" class="stroke-zinc-100 dark:stroke-zinc-800" />
                    @if ($mix['total'] > 0)
                        @foreach ($mix['segments'] as $segment)
                            <circle cx="21" cy="21" r="15.915" fill="none" stroke-width="7" class="{{ $segment['stroke'] }} cursor-pointer transition-opacity"
                                stroke-dasharray="{{ $segment['dash'] }}" stroke-dashoffset="{{ $segment['offset'] }}"
                                :class="active !== null && active !== {{ $loop->index }} && 'opacity-40'"
                                @mouseenter="active = {{ $loop->index }}" @mouseleave="active = null" />
                        @endforeach
                    @endif
                </svg>

                <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                    <div class="text-3xl font-semibold tracking-tight text-zinc-900 tabular-nums dark:text-white">{{ number_format($mix['total']) }}</div>
                    <flux:text>{{ $mix['total'] > 0 ? 'Placed students' : 'No placements yet' }}</flux:text>
                </div>

                <template x-if="active !== null">
                    <div class="pointer-events-none absolute z-10 flex items-center gap-2 whitespace-nowrap rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-lg dark:border-zinc-600 dark:bg-zinc-800"
                        :style="`left: ${tip.x + 12}px; top: ${tip.y + 12}px`">
                        <span class="size-2.5 rounded-full" :class="segments[active].dot"></span>
                        <span class="text-zinc-600 dark:text-zinc-300" x-text="segments[active].label"></span>
                        <span class="ml-2 font-medium text-zinc-900 tabular-nums dark:text-white" x-text="`${segments[active].value} (${segments[active].percentage}%)`"></span>
                    </div>
                </template>
            </div>

            <div class="mt-auto flex justify-center gap-6 pt-6">
                @foreach ($mix['segments'] as $segment)
                    <div class="flex items-center gap-2 text-sm">
                        <span class="size-2.5 rounded-full {{ $segment['dot'] }}"></span>
                        <span class="text-zinc-600 dark:text-zinc-300">{{ $segment['label'] }}</span>
                        <span class="font-medium text-zinc-900 tabular-nums dark:text-white">{{ number_format($segment['value']) }}</span>
                    </div>
                @endforeach
            </div>
        </flux:card>
    </section>

    @php
        $hours = $this->hoursProgress;
        $requirements = $this->requirementsProgress;
        $today = $this->todayAttendance;
    @endphp
    <section class="grid gap-4 lg:grid-cols-3">
        <flux:card class="flex flex-col">
            <div class="flex items-start justify-between gap-4">
                <flux:heading size="lg">OJT Hours Progress</flux:heading>
                <flux:button size="xs" variant="ghost" icon-trailing="arrow-right" :href="route('coordinator.certificates')" wire:navigate>Certificates</flux:button>
            </div>

            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-semibold tracking-tight tabular-nums text-zinc-900 dark:text-white">{{ $hours['percentage'] }}%</span>
                <flux:text>of required hours rendered</flux:text>
            </div>
            <flux:progress :value="$hours['percentage']" color="emerald" class="mt-4" />
            <flux:text class="mt-2 text-xs">
                {{ \App\Support\HourFormatter::format($hours['rendered']) }} of {{ \App\Support\HourFormatter::format($hours['required']) }}
            </flux:text>

            <div class="mt-auto space-y-3 pt-6">
                @foreach ([['Completed hours', $hours['completed'], 'bg-emerald-500'], ['In progress', $hours['in_progress'], 'bg-amber-500'], ['Not started', $hours['not_started'], 'bg-zinc-300 dark:bg-zinc-600']] as [$label, $value, $dot])
                    <div class="flex items-center gap-2 text-sm">
                        <span class="size-2.5 rounded-full {{ $dot }}"></span>
                        <span class="text-zinc-600 dark:text-zinc-300">{{ $label }}</span>
                        <span class="ml-auto font-medium tabular-nums text-zinc-900 dark:text-white">{{ number_format($value) }}</span>
                    </div>
                @endforeach
            </div>
        </flux:card>

        <flux:card class="flex flex-col">
            <div class="flex items-start justify-between gap-4">
                <flux:heading size="lg">Requirements</flux:heading>
                <flux:button size="xs" variant="ghost" icon-trailing="arrow-right" :href="route('coordinator.requirements')" wire:navigate>View</flux:button>
            </div>

            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-semibold tracking-tight tabular-nums text-zinc-900 dark:text-white">{{ $requirements['percentage'] }}%</span>
                <flux:text>of students complete</flux:text>
            </div>
            <flux:progress :value="$requirements['percentage']" color="blue" class="mt-4" />
            <flux:text class="mt-2 text-xs">
                {{ number_format($requirements['complete']) }} of {{ number_format($requirements['total']) }} students submitted everything
            </flux:text>

            <div class="mt-auto space-y-3 pt-6">
                @foreach ([['Complete', $requirements['complete'], 'bg-blue-500'], ['Still pending', $requirements['pending'], 'bg-zinc-300 dark:bg-zinc-600'], ['Files to check', $requirements['for_checking'], 'bg-amber-500']] as [$label, $value, $dot])
                    <div class="flex items-center gap-2 text-sm">
                        <span class="size-2.5 rounded-full {{ $dot }}"></span>
                        <span class="text-zinc-600 dark:text-zinc-300">{{ $label }}</span>
                        <span class="ml-auto font-medium tabular-nums text-zinc-900 dark:text-white">{{ number_format($value) }}</span>
                    </div>
                @endforeach
            </div>
        </flux:card>

        <flux:card class="flex flex-col">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">Today's Attendance</flux:heading>
                    <flux:text class="mt-1 text-xs">{{ today()->format('l, F j') }}</flux:text>
                </div>
                <flux:button size="xs" variant="ghost" icon-trailing="arrow-right" :href="route('coordinator.attendance')" wire:navigate>View</flux:button>
            </div>

            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-semibold tracking-tight tabular-nums text-zinc-900 dark:text-white">{{ number_format($today['logged']) }}</span>
                <flux:text>of {{ number_format($today['total']) }} deployed students logged</flux:text>
            </div>

            <div class="mt-4 flex h-1.5 gap-0.5 overflow-hidden rounded-full bg-zinc-200 dark:bg-white/10">
                @foreach ($today['rows'] as $row)
                    @if ($today['total'] > 0 && $row['value'] > 0)
                        <div class="h-full {{ $row['color'] }}" style="width: {{ $row['value'] / $today['total'] * 100 }}%" title="{{ $row['label'] }}: {{ $row['value'] }}"></div>
                    @endif
                @endforeach
            </div>

            <div class="mt-auto space-y-3 pt-6">
                @foreach ($today['rows'] as $row)
                    <div class="flex items-center gap-2 text-sm">
                        <span class="size-2.5 rounded-full {{ $row['color'] }}"></span>
                        <span class="text-zinc-600 dark:text-zinc-300">{{ $row['label'] }}</span>
                        <span class="ml-auto font-medium tabular-nums text-zinc-900 dark:text-white">{{ number_format($row['value']) }}</span>
                    </div>
                @endforeach
            </div>
        </flux:card>
    </section>
</div>
