<?php

use App\Models\AttendanceLog;
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

new #[Title('Supervisor Dashboard')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'Supervisor Dashboard']];

    #[Computed]
    public function title(): string
    {
        return 'Welcome back, '.(Auth::user()?->name ?? 'Supervisor').' 👋';
    }

    public string $description = 'A focused view of your trainees, placement progress, attendance activity, and pending review work.';

    public string $studentSearch = '';

    public function updatedStudentSearch(): void
    {
        $this->resetPage();
        unset($this->recentTrainees);
    }

    protected function supervisorId(): int
    {
        return (int) Auth::id();
    }

    protected function supervisorAssignmentsQuery(): Builder
    {
        return OjtAssignment::query()->where('company_supervisor_id', $this->supervisorId());
    }

    protected function supervisorAttendanceQuery(): Builder
    {
        return AttendanceLog::query()->whereHas('assignment', function (Builder $query) {
            $query->where('company_supervisor_id', $this->supervisorId());
        });
    }


    #[Computed]
    public function topCards(): array
    {
        $assignments = $this->supervisorAssignmentsQuery();

        return [
            [
                'title' => 'Assigned Trainees',
                'value' => number_format((clone $assignments)->count()),
                'icon' => 'academic-cap',
                'footnote' => 'Students under your supervision',
            ],
            [
                'title' => 'Timed In Today',
                'value' => number_format($this->supervisorAttendanceQuery()->whereDate('attendance_date', today())->count()),
                'icon' => 'clock',
                'footnote' => 'Attendance logs recorded today',
            ],
        ];
    }

    #[Computed]
    public function weeklyAttendance(): array
    {
        $days = collect(range(6, 0))->map(fn (int $offset) => now()->subDays($offset)->startOfDay())->values();

        $counts = $this->supervisorAttendanceQuery()
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
    public function quickSummary(): array
    {
        return [
            'external_assignments' => $this->supervisorAssignmentsQuery()->where('ojt_type', 'external')->count(),
            'internal_assignments' => $this->supervisorAssignmentsQuery()->where('ojt_type', 'internal')->count(),
            'submitted_evaluations' => Evaluation::query()->where('evaluator_id', $this->supervisorId())->where('status', 'submitted')->count(),
        ];
    }

    #[Computed]
    public function recentTrainees(): LengthAwarePaginator
    {
        return $this->supervisorAssignmentsQuery()
            ->with(['student.studentProfile', 'student.coordinator', 'companySupervisor'])
            ->when($this->studentSearch !== '', function (Builder $query) {
                $search = '%'.$this->studentSearch.'%';

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
            ->paginate(10);
    }
};
?>

<div class="flex flex-col gap-8">
    <div data-tour="dashboard-overview"><x-page-header :breadcrumbs="$breadcrumbs" :title="$this->title" :description="$description" /></div>

    <section class="grid gap-4 md:grid-cols-2" data-tour="dashboard-metrics">
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

    <section class="grid gap-4 xl:grid-cols-[minmax(0,1.35fr)_minmax(340px,0.95fr)]" data-tour="dashboard-workflow">
        <flux:card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">Weekly Attendance</flux:heading>
                    <flux:text class="mt-2">
                        Attendance logs recorded by the trainees assigned under your supervision this week.
                    </flux:text>
                </div>

                <flux:badge color="red" inset="top bottom">This Week</flux:badge>
            </div>

            <div class="mt-8">
                <flux:card class="flex h-64 items-end gap-3 rounded-2xl px-4 pb-4 pt-6 sm:gap-6 sm:px-6">
                    @foreach ($this->weeklyAttendance as $item)
                        <div class="flex min-w-0 flex-1 flex-col items-center gap-3">
                            <flux:tooltip
                                :content="$item['label'].': '.$item['value'].' attendance '.($item['value'] === 1 ? 'log' : 'logs')"
                                class="block h-44 w-full">
                                <div class="flex h-full w-full items-end justify-center pt-2">
                                    <div class="w-full max-w-16 rounded-md {{ $item['value'] > 0 ? 'bg-red-400/60' : 'bg-zinc-100 dark:bg-zinc-800' }}"
                                        style="height: {{ $item['height'] }}%" tabindex="0" role="img"
                                        aria-label="{{ $item['label'] }}: {{ $item['value'] }} attendance logs">
                                    </div>
                                </div>
                            </flux:tooltip>

                            <div class="text-sm font-medium text-zinc-700 dark:text-white">
                                {{ $item['label'] }}
                            </div>
                        </div>
                    @endforeach
                </flux:card>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">Placement Summary</flux:heading>
                    <flux:text class="mt-2">
                        A simple breakdown of the trainees currently under your supervision.
                    </flux:text>
                </div>

                <flux:badge color="red" inset="top bottom">Overview</flux:badge>
            </div>

            <div class="mt-8 grid gap-3 sm:grid-cols-2">
                <flux:card>
                    <flux:heading class="uppercase tracking-[0.2em]">External Assignments</flux:heading>
                    <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                        {{ number_format($this->quickSummary['external_assignments']) }}
                    </div>
                </flux:card>

                <flux:card>
                    <flux:heading class="uppercase tracking-[0.2em]">Internal Assignments</flux:heading>
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
            </div>
        </flux:card>
    </section>

    <flux:card>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <flux:heading size="lg">Recent Trainees</flux:heading>
                <flux:text class="mt-2">
                    The latest trainees assigned to you, including their program and OJT type.
                </flux:text>
            </div>

            <div class="w-full lg:max-w-sm">
                <flux:input wire:model.live.debounce.300ms="studentSearch" icon="magnifying-glass"
                    placeholder="Search trainee, email, ID, or course..." clearable />
            </div>
        </div>

        <div class="mt-6">
            <flux:table :paginate="$this->recentTrainees">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Program</flux:table.column>
                    <flux:table.column>Coordinator</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->recentTrainees as $assignment)
                        <flux:table.row :key="$assignment->id">
                            <flux:table.cell>
                                <div class="space-y-1">
                                    <div class="font-medium text-zinc-950 dark:text-white">
                                        {{ $assignment->student?->name ?? '' }}
                                    </div>

                                    <div class="text-sm text-zinc-500 dark:text-zinc-300">
                                        {{ $assignment->student?->studentProfile?->student_id ?? ($assignment->student?->email ?? '') }}
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $assignment->student?->studentProfile?->course ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $assignment->student?->coordinator?->name ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <x-ojt-type-badge :type="$assignment->ojt_type" inset="top bottom" />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="py-8 text-center text-zinc-500">
                                No trainees assigned yet.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>
</div>
