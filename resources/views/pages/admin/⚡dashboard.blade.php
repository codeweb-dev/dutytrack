<?php

use App\Enums\Role as EnumsRole;
use App\Models\AttendanceLog;
use App\Models\DutyHourLog;
use App\Models\Evaluation;
use App\Models\OjtAssignment;
use App\Models\User;
use App\Support\HourFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Admin Dashboard')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'Admin Dashboard']];

    public string $title = 'Welcome back, Admin👋';

    public string $description = 'Here\'s a summary of the latest platform activity and key metrics at a glance. Dive into the details below to monitor student assignments, attendance trends, and more.';

    public string $search = '';

    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->recentAssignments);
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        unset($this->recentAssignments);
    }

    protected function applyRoleFilter(Builder $query, string $role): void
    {
        $query->whereHas('roles', function (Builder $roleQuery) use ($role) {
            $roleQuery->where('name', $role);
        });
    }

    protected function roleCount(string $role): int
    {
        $query = User::query();
        $this->applyRoleFilter($query, $role);

        return $query->count();
    }

    protected function periodBounds(int $monthsAgo = 0): array
    {
        $date = now()->startOfMonth()->subMonths($monthsAgo);

        return [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];
    }

    protected function monthlyCount(string $modelClass, string $dateColumn, int $monthsAgo = 0, ?\Closure $scope = null): int
    {
        [$start, $end] = $this->periodBounds($monthsAgo);
        $query = $modelClass::query()->whereBetween($dateColumn, [$start, $end]);

        if ($scope) {
            $scope($query);
        }

        return $query->count();
    }

    protected function monthlySum(string $modelClass, string $dateColumn, string $valueColumn, int $monthsAgo = 0, ?\Closure $scope = null): float
    {
        [$start, $end] = $this->periodBounds($monthsAgo);
        $query = $modelClass::query()->whereBetween($dateColumn, [$start, $end]);

        if ($scope) {
            $scope($query);
        }

        return (float) $query->sum($valueColumn);
    }

    protected function deltaMeta(float|int $current, float|int $previous): array
    {
        $delta = $current - $previous;
        $positive = $delta >= 0;

        if ((float) $previous === 0.0) {
            $percent = $current > 0 ? 100 : 0;
        } else {
            $percent = round((abs($delta) / $previous) * 100, 1);
        }

        return [
            'label' => ($positive ? '+' : '-').$percent.'%',
            'color' => $positive ? 'lime' : 'red',
            'tone' => $positive ? 'bg-lime-50 text-lime-700 ring-lime-200 dark:bg-lime-500/10 dark:text-lime-300 dark:ring-lime-500/20' : 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/20',
        ];
    }

    protected function compactHours(float $value): string
    {
        return HourFormatter::format($value);
    }

    #[Computed]
    public function topCards(): array
    {
        $studentCurrent = $this->monthlyCount(User::class, 'created_at', 0, fn (Builder $query) => $this->applyRoleFilter($query, EnumsRole::STUDENT->value));
        $studentPrevious = $this->monthlyCount(User::class, 'created_at', 1, fn (Builder $query) => $this->applyRoleFilter($query, EnumsRole::STUDENT->value));

        $assignmentCurrent = $this->monthlyCount(OjtAssignment::class, 'created_at');
        $assignmentPrevious = $this->monthlyCount(OjtAssignment::class, 'created_at', 1);

        $reviewCurrent = Evaluation::query()->where('status', 'pending')->count();
        $reviewPrevious = $this->monthlyCount(Evaluation::class, 'created_at', 1, fn (Builder $query) => $query->where('status', 'pending'));

        $hoursCurrent = $this->monthlySum(DutyHourLog::class, 'approved_at', 'approved_hours', 0, fn (Builder $query) => $query->where('status', 'approved')->whereNotNull('approved_at'));
        $hoursPrevious = $this->monthlySum(DutyHourLog::class, 'approved_at', 'approved_hours', 1, fn (Builder $query) => $query->where('status', 'approved')->whereNotNull('approved_at'));

        return [
            [
                'title' => 'Total Students',
                'value' => number_format($this->roleCount(EnumsRole::STUDENT->value)),
                'icon' => 'academic-cap',
                'delta' => $this->deltaMeta($studentCurrent, $studentPrevious),
                'footnote' => 'Last month: '.number_format($studentPrevious),
                'link' => '/admin/students',
            ],
            [
                'title' => 'New Assignments',
                'value' => number_format(OjtAssignment::query()->count()),
                'icon' => 'briefcase',
                'delta' => $this->deltaMeta($assignmentCurrent, $assignmentPrevious),
                'footnote' => 'Last month: '.number_format($assignmentPrevious),
                'link' => '/admin/ojt-assignments',
            ],
            [
                'title' => 'Pending Reviews',
                'value' => number_format($reviewCurrent),
                'icon' => 'clipboard-document-check',
                'delta' => $this->deltaMeta($reviewCurrent, $reviewPrevious),
                'footnote' => 'Last month: '.number_format($reviewPrevious),
                'link' => '/admin/evaluations',
            ],
            [
                'title' => 'Approved Hours',
                'value' => $this->compactHours((float) DutyHourLog::query()->where('status', 'approved')->sum('approved_hours')),
                'icon' => 'clock',
                'delta' => $this->deltaMeta($hoursCurrent, $hoursPrevious),
                'footnote' => 'Last month: '.$this->compactHours($hoursPrevious),
                'link' => '/admin/duty-hours',
            ],
        ];
    }

    #[Computed]
    public function weeklyActivity(): array
    {
        $days = collect(range(6, 0, -1))
            ->map(fn (int $offset) => now()->subDays($offset)->startOfDay())
            ->push(now()->startOfDay())
            ->values();

        $counts = AttendanceLog::query()
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
    public function monthlyAssignmentMix(): array
    {
        $months = collect(range(5, 0, -1))
            ->map(fn (int $offset) => now()->subMonths($offset)->startOfMonth())
            ->push(now()->startOfMonth())
            ->values();

        $items = $months->map(function ($month) {
            $internal = OjtAssignment::query()
                ->where('ojt_type', 'internal')
                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count();

            $external = OjtAssignment::query()
                ->where('ojt_type', 'external')
                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count();

            return [
                'label' => $month->format('M'),
                'internal' => $internal,
                'external' => $external,
                'total' => $internal + $external,
            ];
        });

        $max = max(collect($items)->max('total'), 1);

        return $items
            ->map(function (array $item) use ($max) {
                $item['internal_height'] = $item['total'] > 0 ? ($item['internal'] / $max) * 100 : 0;
                $item['external_height'] = $item['total'] > 0 ? ($item['external'] / $max) * 100 : 0;

                return $item;
            })
            ->all();
    }

    #[Computed]
    public function recentAssignments(): LengthAwarePaginator
    {
        return OjtAssignment::query()
            ->with(['student.studentProfile', 'student.coordinator', 'professor', 'companySupervisor'])
            ->when($this->search !== '', function (Builder $query) {
                $search = '%'.$this->search.'%';

                $query->where(function (Builder $nested) use ($search) {
                    $nested
                        ->whereHas('student', function (Builder $studentQuery) use ($search) {
                            $studentQuery
                                ->where('name', 'like', $search)
                                ->orWhere('email', 'like', $search)
                                ->orWhereHas('studentProfile', function (Builder $profileQuery) use ($search) {
                                    $profileQuery->where('student_id', 'like', $search);
                                });
                        })
                        ->orWhereHas('professor', function (Builder $professorQuery) use ($search) {
                            $professorQuery->where('name', 'like', $search);
                        })
                        ->orWhereHas('companySupervisor', function (Builder $supervisorQuery) use ($search) {
                            $supervisorQuery->where('name', 'like', $search);
                        });
                });
            })
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->latest('created_at')
            ->paginate(5);
    }
};
?>

<div class="flex flex-col gap-8">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

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

                <div class="mt-6 flex items-center gap-3">
                    <div class="text-5xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                        {{ $card['value'] }}
                    </div>

                    <flux:badge color="red">
                        {{ $card['delta']['label'] }}
                    </flux:badge>
                </div>

                <div class="flex items-center justify-between mt-6">
                    <flux:text>
                        {{ $card['footnote'] }}
                    </flux:text>

                    <flux:button href="{{ $card['link'] }}" size="xs" icon:trailing="arrow-up-right" wire:navigate>
                        View Details
                    </flux:button>
                </div>
            </flux:card>
        @endforeach
    </section>

    <section class="grid gap-4 xl:grid-cols-[minmax(0,1.4fr)_minmax(340px,0.9fr)]">
        <flux:card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">Weekly Activity</flux:heading>
                    <flux:text class="mt-2">
                        Attendance logs recorded this week across the platform.
                    </flux:text>
                </div>

                <flux:badge color="red" inset="top bottom">This Week</flux:badge>
            </div>

            <div class="mt-8">
                <flux:card class="flex h-92 items-end gap-6 px-6 pb-6 pt-8">
                    @foreach ($this->weeklyActivity as $item)
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

        <flux:card>
            <div>
                <flux:heading size="lg">Assignment Mix</flux:heading>
                <flux:text class="mt-2">
                    Internal and external assignment volume by month.
                </flux:text>
            </div>

            <flux:card class="mt-6">
                <div class="flex items-center justify-between text-sm">
                    <div class="font-medium text-zinc-800 dark:text-zinc-200">Internal vs External</div>

                    <div class="flex items-center gap-4 text-zinc-500">
                        <span class="inline-flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-red-400/60"></span>
                            Internal
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-zinc-900 dark:bg-zinc-100"></span>
                            External
                        </span>
                    </div>
                </div>

                <div class="mt-6 flex h-72 items-end gap-4">
                    @foreach ($this->monthlyAssignmentMix as $item)
                        <div class="flex min-w-0 flex-1 flex-col items-center gap-3">
                            <div class="flex h-52 w-full max-w-14 flex-col justify-end gap-1">
                                <div class="rounded-2xl bg-red-400/60"
                                    style="height: {{ max($item['internal_height'], $item['internal'] > 0 ? 12 : 0) }}%">
                                </div>
                                <div class="rounded-2xl bg-zinc-900 dark:bg-zinc-100"
                                    style="height: {{ max($item['external_height'], $item['external'] > 0 ? 12 : 0) }}%">
                                </div>
                            </div>

                            <div class="text-sm font-medium text-zinc-500">
                                {{ $item['label'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        </flux:card>
    </section>

    <flux:card>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <flux:heading size="lg">Recent Assignments</flux:heading>
                <flux:text class="mt-2">
                    Latest student deployments across coordinators, professors, and company supervisors.
                </flux:text>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                    placeholder="Search assignments..." class="sm:w-72" />

                <flux:select wire:model.live="status" class="sm:w-44">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </flux:select>
            </div>
        </div>

        <div class="mt-6">
            @if ($this->recentAssignments->isEmpty())
                <flux:card class="flex flex-col items-center gap-4 py-24">
                    <flux:icon name="briefcase" class="size-12" />
                    <div class="text-center">
                        <flux:heading size="xl">No assignments found</flux:heading>
                        <flux:text class="mt-2">Try adjusting your search or filter to find what you're looking
                            for.</flux:text>
                    </div>
                </flux:card>
            @else
                <flux:table :paginate="$this->recentAssignments">
                    <flux:table.columns>
                        <flux:table.column>Student</flux:table.column>
                        <flux:table.column>Coordinator</flux:table.column>
                        <flux:table.column>Assignee</flux:table.column>
                        <flux:table.column>Type</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->recentAssignments as $assignment)
                            @php
                                $assignee =
                                    $assignment->ojt_type === 'internal'
                                        ? $assignment->professor
                                        : $assignment->companySupervisor;
                            @endphp

                            <flux:table.row :key="$assignment->id">
                                <flux:table.cell>
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <div class="font-medium text-zinc-900 dark:text-white">
                                                {{ $assignment->student?->name ?? '' }}
                                            </div>
                                            <div class="text-sm text-zinc-500">
                                                {{ $assignment->student?->studentProfile?->student_id ?? 'No student ID' }}
                                            </div>
                                        </div>
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell>
                                    {{ $assignment->student?->coordinator?->name ?? 'Unassigned' }}
                                </flux:table.cell>

                                <flux:table.cell>
                                    {{ $assignee?->name ?? '' }}
                                </flux:table.cell>

                                <flux:table.cell>
                                    {{ ucfirst($assignment->ojt_type) }}
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

                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </div>
    </flux:card>
</div>
