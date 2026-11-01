<?php

use App\Models\AttendanceLog;
use App\Models\OjtAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Coordinator Dashboard')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'Coordinator Dashboard']];

    #[Computed]
    public function title(): string
    {
        return 'Welcome back, '.(Auth::user()?->name ?? 'Coordinator').' 👋';
    }

    public string $description = 'A focused view of your students, placement progress, weekly activity, and the latest assignment records.';

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
        $totalStudents = $this->managedStudentsQuery()->count();
        $assignedStudents = $this->managedAssignmentsQuery()->count();
        $unassignedStudents = max($totalStudents - $assignedStudents, 0);
        $pendingAssignments = $this->managedAssignmentsQuery()->where('status', 'pending')->count();

        return [
            [
                'title' => 'My Students',
                'value' => number_format($totalStudents),
                'icon' => 'academic-cap',
                'footnote' => number_format($assignedStudents).' already deployed',
            ],
            [
                'title' => 'Pending Placements',
                'value' => number_format($unassignedStudents),
                'icon' => 'user-plus',
                'footnote' => number_format($pendingAssignments).' assignment records still pending',
            ],
            [
                'title' => 'Active Assignments',
                'value' => number_format($this->managedAssignmentsQuery()->where('status', 'active')->count()),
                'icon' => 'briefcase',
                'footnote' => number_format($this->managedAssignmentsQuery()->count()).' total assignment records',
            ],
        ];
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
        $total = max($this->managedAssignmentsQuery()->count(), 1);

        return [
            [
                'label' => 'Pending',
                'value' => $this->managedAssignmentsQuery()->where('status', 'pending')->count(),
                'bar' => 'bg-amber-400',
                'percentage' => round(($this->managedAssignmentsQuery()->where('status', 'pending')->count() / $total) * 100),
            ],
            [
                'label' => 'Active',
                'value' => $this->managedAssignmentsQuery()->where('status', 'active')->count(),
                'bar' => 'bg-lime-400',
                'percentage' => round(($this->managedAssignmentsQuery()->where('status', 'active')->count() / $total) * 100),
            ],
            [
                'label' => 'Completed',
                'value' => $this->managedAssignmentsQuery()->where('status', 'completed')->count(),
                'bar' => 'bg-teal-500',
                'percentage' => round(($this->managedAssignmentsQuery()->where('status', 'completed')->count() / $total) * 100),
            ],
            [
                'label' => 'Cancelled',
                'value' => $this->managedAssignmentsQuery()->where('status', 'cancelled')->count(),
                'bar' => 'bg-red-400',
                'percentage' => round(($this->managedAssignmentsQuery()->where('status', 'cancelled')->count() / $total) * 100),
            ],
        ];
    }

    #[Computed]
    public function recentStudents(): LengthAwarePaginator
    {
        return $this->managedStudentsQuery()
            ->with(['studentProfile', 'ojtAssignment.companySupervisor'])
            ->latest('created_at')
            ->paginate(10);
    }
};
?>

<div class="flex flex-col gap-8">
    <div data-tour="dashboard-overview"><x-page-header :breadcrumbs="$breadcrumbs" :title="$this->title" :description="$description" /></div>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" data-tour="dashboard-metrics">
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
                        Attendance logs captured from the students assigned to you this week.
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

        <div class="grid gap-4">
            <flux:card>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <flux:heading size="lg">Assignment Status</flux:heading>
                        <flux:text class="mt-2">
                            A quick breakdown of where your current placement records stand.
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
                                <flux:text>
                                    {{ number_format($item['value']) }} • {{ $item['percentage'] }}%
                                </flux:text>
                            </div>

                            <div class="h-2.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full rounded-full {{ $item['bar'] }}"
                                    style="width: {{ min($item['percentage'], 100) }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        </div>
    </section>

    <flux:card>
        <div>
            <flux:heading size="lg">Recent Students</flux:heading>
            <flux:text class="mt-2">
                The latest students under your coordination, including their placement status.
            </flux:text>
        </div>

        <div class="mt-6">
            @if ($this->recentStudents->isEmpty())
                <flux:card class="flex flex-col items-center gap-4 py-24">
                    <flux:icon name="academic-cap" class="size-12" />
                    <div class="text-center">
                        <flux:heading size="xl">No students yet</flux:heading>
                        <flux:text class="mt-2">Students assigned to you will show up here.</flux:text>
                    </div>
                </flux:card>
            @else
                <flux:table :paginate="$this->recentStudents">
                    <flux:table.columns>
                        <flux:table.column>Student</flux:table.column>
                        <flux:table.column>Course</flux:table.column>
                        <flux:table.column>Assignment</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column>Hours</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->recentStudents as $student)
                            @php
                                $assignment = $student->ojtAssignment;
                                $assignee =
                                    $assignment?->companySupervisor?->display_name;
                            @endphp

                            <flux:table.row :key="$student->id">
                                <flux:table.cell>
                                    <div>
                                        <div class="font-medium text-zinc-900 dark:text-white">
                                            {{ $student->display_name }}
                                        </div>
                                        <div class="text-sm text-zinc-500">
                                            {{ $student->studentProfile?->student_id ?? 'No student ID' }}
                                        </div>
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <div class="text-sm text-zinc-900 dark:text-white">
                                        {{ $student->studentProfile?->course ?? '' }}
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell>
                                    {{ $assignee ?? 'Not assigned yet' }}
                                </flux:table.cell>

                                <flux:table.cell>
                                    @if ($assignment)
                                        <flux:badge size="sm"
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
                                    @else
                                        <flux:badge size="sm" color="zinc" inset="top bottom">
                                            Unassigned
                                        </flux:badge>
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell>
                                    {{ \App\Support\HourFormatter::format($student->studentProfile?->ojt_required_hours ?? 0) }}
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </div>
    </flux:card>
</div>
