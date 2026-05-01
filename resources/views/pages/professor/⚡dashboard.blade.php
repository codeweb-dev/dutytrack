<?php

use App\Models\AttendanceLog;
use App\Models\DutyHourLog;
use App\Models\Evaluation;
use App\Models\OjtAssignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Professor Dashboard')] class extends Component {
    use WithPagination;

    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'Professor Dashboard']];

    #[Computed]
    public function title(): string
    {
        return 'Welcome back, ' . (Auth::user()?->name ?? 'Professor') . ' 👋';
    }

    public string $description = 'A concise view of your advisees, internal OJT assignments, weekly attendance, and pending review activity.';

    public string $studentSearch = '';

    public function updatedStudentSearch(): void
    {
        $this->resetPage();
        unset($this->recentAdvisees);
    }

    protected function professorId(): int
    {
        return (int) Auth::id();
    }

    protected function professorAssignmentsQuery(): Builder
    {
        return OjtAssignment::query()->where('professor_id', $this->professorId());
    }

    protected function professorAttendanceQuery(): Builder
    {
        return AttendanceLog::query()->whereHas('assignment', function (Builder $query) {
            $query->where('professor_id', $this->professorId());
        });
    }

    protected function professorDutyHoursQuery(): Builder
    {
        return DutyHourLog::query()->whereHas('assignment', function (Builder $query) {
            $query->where('professor_id', $this->professorId());
        });
    }

    protected function compactHours(float $value): string
    {
        return number_format($value, $value >= 100 ? 0 : 1) . 'h';
    }

    #[Computed]
    public function topCards(): array
    {
        $assignments = $this->professorAssignmentsQuery();
        $attendance = $this->professorAttendanceQuery();
        $dutyHours = $this->professorDutyHoursQuery();

        return [
            [
                'title' => 'My Advisees',
                'value' => number_format((clone $assignments)->count()),
                'icon' => 'academic-cap',
                'footnote' => number_format((clone $assignments)->where('status', 'active')->count()) . ' currently active',
            ],
            [
                'title' => 'Pending Assignments',
                'value' => number_format((clone $assignments)->where('status', 'pending')->count()),
                'icon' => 'clipboard-document-check',
                'footnote' => number_format((clone $assignments)->where('status', 'completed')->count()) . ' already completed',
            ],
            [
                'title' => 'Approved Hours',
                'value' => $this->compactHours((float) (clone $dutyHours)->where('status', 'approved')->sum('approved_hours')),
                'icon' => 'clock',
                'footnote' => number_format((clone $dutyHours)->count()) . ' duty-hour entries tracked',
            ],
            [
                'title' => 'Flagged Attendance',
                'value' => number_format((clone $attendance)->where('is_flagged', true)->count()),
                'icon' => 'exclamation-triangle',
                'footnote' => number_format(Evaluation::query()->where('evaluator_id', $this->professorId())->where('status', 'pending')->count()) . ' evaluations pending',
            ],
        ];
    }

    #[Computed]
    public function weeklyAttendance(): array
    {
        $days = collect(range(6, 0, -1))
            ->map(fn(int $offset) => now()->subDays($offset)->startOfDay())
            ->push(now()->startOfDay())
            ->values();

        $counts = $this->professorAttendanceQuery()
            ->selectRaw('DATE(attendance_date) as day_key, COUNT(*) as total')
            ->whereBetween('attendance_date', [$days->first(), $days->last()->copy()->endOfDay()])
            ->groupBy(DB::raw('DATE(attendance_date)'))
            ->pluck('total', 'day_key');

        $items = $days->map(function ($day) use ($counts) {
            return [
                'label' => $day->format('D'),
                'value' => (int) ($counts[$day->toDateString()] ?? 0),
            ];
        });

        $max = max(collect($items)->max('value'), 1);

        return $items
            ->map(function (array $item) use ($max) {
                $item['height'] = max(($item['value'] / $max) * 100, $item['value'] > 0 ? 18 : 8);

                return $item;
            })
            ->all();
    }

    #[Computed]
    public function assignmentStatus(): array
    {
        $baseQuery = $this->professorAssignmentsQuery();
        $total = max((clone $baseQuery)->count(), 1);

        return [
            [
                'label' => 'Pending',
                'value' => (clone $baseQuery)->where('status', 'pending')->count(),
                'bar' => 'bg-amber-400',
                'percentage' => round(((clone $baseQuery)->where('status', 'pending')->count() / $total) * 100),
            ],
            [
                'label' => 'Active',
                'value' => (clone $baseQuery)->where('status', 'active')->count(),
                'bar' => 'bg-lime-400',
                'percentage' => round(((clone $baseQuery)->where('status', 'active')->count() / $total) * 100),
            ],
            [
                'label' => 'Completed',
                'value' => (clone $baseQuery)->where('status', 'completed')->count(),
                'bar' => 'bg-sky-400',
                'percentage' => round(((clone $baseQuery)->where('status', 'completed')->count() / $total) * 100),
            ],
            [
                'label' => 'Cancelled',
                'value' => (clone $baseQuery)->where('status', 'cancelled')->count(),
                'bar' => 'bg-red-400',
                'percentage' => round(((clone $baseQuery)->where('status', 'cancelled')->count() / $total) * 100),
            ],
        ];
    }

    #[Computed]
    public function quickSummary(): array
    {
        return [
            'internal_assignments' => $this->professorAssignmentsQuery()->where('ojt_type', 'internal')->count(),
            'submitted_evaluations' => Evaluation::query()->where('evaluator_id', $this->professorId())->where('status', 'submitted')->count(),
            'pending_evaluations' => Evaluation::query()->where('evaluator_id', $this->professorId())->where('status', 'pending')->count(),
            'flagged_logs' => $this->professorAttendanceQuery()->where('is_flagged', true)->count(),
        ];
    }

    #[Computed]
    public function recentAdvisees(): LengthAwarePaginator
    {
        return $this->professorAssignmentsQuery()
            ->with(['student.studentProfile', 'student.coordinator', 'companySupervisor'])
            ->when($this->studentSearch !== '', function (Builder $query) {
                $search = '%' . $this->studentSearch . '%';

                $query->whereHas('student', function (Builder $studentQuery) use ($search) {
                    $studentQuery
                        ->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhereHas('studentProfile', function (Builder $profileQuery) use ($search) {
                            $profileQuery->where('student_id', 'like', $search)->orWhere('course', 'like', $search);
                        });
                });
            })
            ->latest('created_at')
            ->paginate(5);
    }
};
?>

<div class="flex flex-col gap-8">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$this->title" :description="$description" />

    <section class="grid gap-4 xl:grid-cols-4">
        @foreach ($this->topCards as $card)
            <flux:card>
                <div class="flex items-start justify-between gap-4">
                    <flux:heading size="lg">
                        {{ $card['title'] }}
                    </flux:heading>

                    <div
                        class="rounded-2xl bg-zinc-50 p-3 text-zinc-900 ring-1 ring-zinc-200 dark:bg-zinc-800 dark:text-white dark:ring-zinc-700">
                        <flux:icon :name="$card['icon']" class="h-5 w-5" />
                    </div>
                </div>

                <div class="mt-6 text-5xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ $card['value'] }}
                </div>

                <flux:text class="mt-6">
                    {{ $card['footnote'] }}
                </flux:text>
            </flux:card>
        @endforeach
    </section>

    <section class="grid gap-4 xl:grid-cols-[minmax(0,1.35fr)_minmax(340px,0.95fr)]">
        <flux:card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">Weekly Attendance</flux:heading>
                    <flux:text class="mt-2">
                        Attendance logs recorded by the students assigned to you this week.
                    </flux:text>
                </div>

                <flux:badge color="red" inset="top bottom">This Week</flux:badge>
            </div>

            <div class="mt-8">
                <flux:card class="flex h-154 items-end gap-6 rounded-2xl px-6 pb-6 pt-8">
                    @foreach ($this->weeklyAttendance as $item)
                        <div class="flex min-w-0 flex-1 flex-col items-center gap-3">
                            <div class="flex h-52 w-full items-end justify-center">
                                <div class="w-full max-w-16 rounded-full bg-red-400/60"
                                    style="height: {{ $item['height'] }}%">
                                </div>
                            </div>

                            <div class="text-sm font-medium text-zinc-500">
                                {{ $item['label'] }}
                            </div>
                        </div>
                    @endforeach
                </flux:card>
            </div>
        </flux:card>

        <div class="grid gap-4">
            <flux:card>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <flux:heading size="lg">Assignment Status</flux:heading>
                        <flux:text class="mt-2">
                            The current state of the assignment records where you are the assigned professor.
                        </flux:text>
                    </div>

                    <flux:badge color="red" inset="top bottom">Overview</flux:badge>
                </div>

                <div class="mt-6 space-y-4">
                    @foreach ($this->assignmentStatus as $item)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-4">
                                <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $item['label'] }}
                                </div>
                                <div class="text-sm text-zinc-500">
                                    {{ number_format($item['value']) }} • {{ $item['percentage'] }}%
                                </div>
                            </div>

                            <div class="h-2.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full rounded-full {{ $item['bar'] }}"
                                    style="width: {{ min(max($item['percentage'], 4), 100) }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>

            <flux:card>
                <flux:heading size="lg">Quick Summary</flux:heading>
                <flux:text class="mt-2">
                    A quick read on your advisee workload and review queue.
                </flux:text>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <flux:card>
                        <flux:heading class="uppercase tracking-[0.2em]">Internal</flux:heading>
                        <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                            {{ number_format($this->quickSummary['internal_assignments']) }}
                        </div>
                    </flux:card>

                    <flux:card>
                        <flux:heading class="uppercase tracking-[0.2em]">Submitted Evaluations</flux:heading>
                        <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                            {{ number_format($this->quickSummary['submitted_evaluations']) }}
                        </div>
                    </flux:card>

                    <flux:card>
                        <flux:heading class="uppercase tracking-[0.2em]">Pending Evaluations</flux:heading>
                        <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                            {{ number_format($this->quickSummary['pending_evaluations']) }}
                        </div>
                    </flux:card>

                    <flux:card>
                        <flux:heading class="uppercase tracking-[0.2em]">Flagged Logs</flux:heading>
                        <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                            {{ number_format($this->quickSummary['flagged_logs']) }}
                        </div>
                    </flux:card>
                </div>
            </flux:card>
        </div>
    </section>

    <flux:card>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <flux:heading size="lg">Recent Advisees</flux:heading>
                <flux:text class="mt-2">
                    Students currently routed through your internal OJT assignments.
                </flux:text>
            </div>

            <flux:input wire:model.live.debounce.300ms="studentSearch" icon="magnifying-glass"
                placeholder="Search advisees..." class="lg:w-72" />
        </div>

        <div class="mt-6">
            <flux:table :paginate="$this->recentAdvisees">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Coordinator</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Hours</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->recentAdvisees as $assignment)
                        <flux:table.row :key="$assignment->id">
                            <flux:table.cell>
                                <div>
                                    <div class="font-medium text-zinc-900 dark:text-white">
                                        {{ $assignment->student?->name ?? '—' }}
                                    </div>
                                    <div class="text-sm text-zinc-500">
                                        {{ $assignment->student?->studentProfile?->student_id ?? 'No student ID' }}
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $assignment->student?->coordinator?->name ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge
                                    :color="match ($assignment->status) {
                                                                                                                                                                                    'active' => 'lime',
                                                                                                                                                                                    'pending' => 'amber',
                                                                                                                                                                                    'completed' => 'sky',
                                                                                                                                                                                    'cancelled' => 'red',
                                                                                                                                                                                    default => 'zinc',
                                                                                                                                                                                }"
                                    inset="top bottom">
                                    {{ ucfirst($assignment->status) }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ number_format($assignment->required_hours) }} hrs
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="py-8 text-center text-zinc-500">
                                No advisees found.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>
</div>
