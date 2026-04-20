<?php

use App\Models\AttendanceLog;
use App\Models\DutyHourLog;
use App\Models\Evaluation;
use App\Models\OjtAssignment;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Student Dashboard')] class extends Component {
    use WithPagination;

    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'Student Dashboard']];

    #[Computed]
    public function title(): string
    {
        return 'Welcome back, ' . (Auth::user()?->name ?? 'Professor') . ' 👋';
    }

    public string $title = 'Student Dashboard';

    public string $description = 'A simple overview of your placement, required hours, weekly progress, and latest attendance activity.';

    protected function studentId(): int
    {
        return (int) Auth::id();
    }

    protected function compactHours(float $value): string
    {
        return number_format($value, $value >= 100 ? 0 : 1) . 'h';
    }

    #[Computed]
    public function currentStudent(): User
    {
        return User::query()
            ->with(['studentProfile', 'coordinator', 'ojtAssignment.professor', 'ojtAssignment.companySupervisor'])
            ->findOrFail($this->studentId());
    }

    #[Computed]
    public function assignment(): ?OjtAssignment
    {
        return $this->currentStudent->ojtAssignment;
    }

    #[Computed]
    public function hoursSummary(): array
    {
        $required = (float) ($this->currentStudent->studentProfile?->ojt_required_hours ?? 0);
        $approved = (float) DutyHourLog::query()->where('student_id', $this->studentId())->where('status', 'approved')->sum('approved_hours');
        $rendered = (float) DutyHourLog::query()->where('student_id', $this->studentId())->sum('hours_rendered');
        $remaining = max($required - $approved, 0);
        $progress = $required > 0 ? min(round(($approved / $required) * 100), 100) : 0;

        return [
            'required' => $required,
            'approved' => $approved,
            'rendered' => $rendered,
            'remaining' => $remaining,
            'progress' => $progress,
        ];
    }

    #[Computed]
    public function topCards(): array
    {
        $assignment = $this->assignment;
        $hours = $this->hoursSummary;

        return [
            [
                'title' => 'Required Hours',
                'value' => number_format((float) $hours['required']),
                'icon' => 'clock',
                'footnote' => $this->compactHours((float) $hours['remaining']) . ' remaining',
            ],
            [
                'title' => 'Approved Hours',
                'value' => $this->compactHours((float) $hours['approved']),
                'icon' => 'check-badge',
                'footnote' => $hours['progress'] . '% of your requirement completed',
            ],
            [
                'title' => 'Assignment Status',
                'value' => $assignment ? ucfirst($assignment->status) : 'Unassigned',
                'icon' => 'briefcase',
                'footnote' => $assignment ? ucfirst($assignment->ojt_type) . ' OJT' : 'Waiting for placement',
            ],
            [
                'title' => 'Pending Evaluations',
                'value' => number_format(Evaluation::query()->where('student_id', $this->studentId())->where('status', 'pending')->count()),
                'icon' => 'clipboard-document-check',
                'footnote' => number_format(AttendanceLog::query()->where('student_id', $this->studentId())->where('is_flagged', true)->count()) . ' flagged attendance logs',
            ],
        ];
    }

    #[Computed]
    public function weeklyHours(): array
    {
        $days = collect(range(6, 0))->map(fn(int $offset) => now()->subDays($offset)->startOfDay())->values();

        $totals = DutyHourLog::query()
            ->selectRaw('DATE(log_date) as day_key, COALESCE(SUM(hours_rendered), 0) as total')
            ->where('student_id', $this->studentId())
            ->whereBetween('log_date', [$days->first(), $days->last()->copy()->endOfDay()])
            ->groupBy(DB::raw('DATE(log_date)'))
            ->pluck('total', 'day_key');

        $items = $days->map(function ($day) use ($totals) {
            return [
                'label' => $day->format('D'),
                'value' => (float) ($totals[$day->toDateString()] ?? 0),
            ];
        });

        $max = max((float) collect($items)->max('value'), 1);

        return $items
            ->map(function (array $item) use ($max) {
                $item['height'] = max(($item['value'] / $max) * 100, $item['value'] > 0 ? 18 : 8);

                return $item;
            })
            ->all();
    }

    #[Computed]
    public function placementSummary(): array
    {
        $assignment = $this->assignment;

        return [
            'course' => $this->currentStudent->studentProfile?->course ?? '—',
            'student_id' => $this->currentStudent->studentProfile?->student_id ?? '—',
            'coordinator' => $this->currentStudent->coordinator?->name ?? 'Not assigned yet',
            'professor' => $assignment?->professor?->name ?? 'Not assigned yet',
            'supervisor' => $assignment?->companySupervisor?->name ?? 'Not assigned yet',
            'type' => $assignment ? ucfirst($assignment->ojt_type) : 'No assignment yet',
            'status' => $assignment ? ucfirst($assignment->status) : 'Unassigned',
        ];
    }

    #[Computed]
    public function recentAttendance(): LengthAwarePaginator
    {
        return AttendanceLog::query()->where('student_id', $this->studentId())->latest('attendance_date')->latest('created_at')->paginate(5);
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
                    <flux:heading size="lg">Weekly Logged Hours</flux:heading>
                    <flux:text class="mt-2">
                        A quick look at the hours you logged throughout the current week.
                    </flux:text>
                </div>

                <flux:badge color="red" inset="top bottom">This Week</flux:badge>
            </div>

            <div class="mt-8">
                <flux:card class="flex h-92 items-end gap-6 rounded-2xl px-6 pb-6 pt-8">
                    @foreach ($this->weeklyHours as $item)
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

            <flux:card class="mt-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <flux:heading>Progress to Requirement</flux:heading>
                        <flux:text class="mt-1">
                            {{ $this->compactHours((float) $this->hoursSummary['approved']) }} approved out of
                            {{ $this->compactHours((float) $this->hoursSummary['required']) }}
                        </flux:text>
                    </div>

                    <flux:text>
                        {{ $this->hoursSummary['progress'] }}%
                    </flux:text>
                </div>

                <flux:progress class="mt-4 h-3" :value="min(max($this->hoursSummary['progress'], 0), 100)" />
            </flux:card>
        </flux:card>

        <flux:card>
            <flux:heading size="lg">Placement Overview</flux:heading>
            <flux:text class="mt-2">
                Your current placement details, academic profile, and assigned reviewers.
            </flux:text>

            <div class="mt-6 grid grid-cols-2 gap-3">
                <flux:card>
                    <flux:heading class="uppercase tracking-[0.2em]">Student Id</flux:heading>
                    <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                        {{ $this->placementSummary['student_id'] }}
                    </div>
                </flux:card>

                <flux:card>
                    <flux:heading class="uppercase tracking-[0.2em]">Course</flux:heading>
                    <div class="mt-2 text-xl font-semibold text-zinc-950 dark:text-white">
                        {{ $this->placementSummary['course'] }}
                    </div>
                </flux:card>

                <flux:card>
                    <flux:heading class="uppercase tracking-[0.2em]">Coordinator</flux:heading>
                    <div class="mt-2 text-base font-semibold text-zinc-950 dark:text-white">
                        {{ $this->placementSummary['coordinator'] }}
                    </div>
                </flux:card>

                <flux:card>
                    <flux:heading class="uppercase tracking-[0.2em]">Professor</flux:heading>
                    <div class="mt-2 text-base font-semibold text-zinc-950 dark:text-white">
                        {{ $this->placementSummary['professor'] }}
                    </div>
                </flux:card>

                <flux:card>
                    <flux:heading class="uppercase tracking-[0.2em]">Supervisor</flux:heading>
                    <div class="mt-2 text-base font-semibold text-zinc-950 dark:text-white">
                        {{ $this->placementSummary['supervisor'] }}
                    </div>
                </flux:card>

                <flux:card>
                    <flux:heading class="uppercase tracking-[0.2em]">Assignment</flux:heading>
                    <div class="mt-2 text-base font-semibold text-zinc-950 dark:text-white">
                        {{ $this->placementSummary['type'] }} <flux:badge color="lime" inset="top bottom">{{ $this->placementSummary['status'] }}</flux:badge>
                    </div>
                </flux:card>
            </div>
        </flux:card>
    </section>

    <flux:card>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <flux:heading size="lg">Recent Attendance</flux:heading>
                <flux:text class="mt-2">
                    Your latest attendance entries, including rendered hours and flagged records.
                </flux:text>
            </div>
        </div>

        <div class="mt-6">
            <flux:table :paginate="$this->recentAttendance">
                <flux:table.columns>
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>Time In</flux:table.column>
                    <flux:table.column>Time Out</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Hours</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->recentAttendance as $log)
                        <flux:table.row :key="$log->id">
                            <flux:table.cell>
                                {{ $log->attendance_date?->format('M d, Y') ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $log->time_in?->format('h:i A') ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $log->time_out?->format('h:i A') ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:badge :color="$log->is_flagged ? 'red' : 'sky'" inset="top bottom">
                                        {{ ucfirst($log->status ?? 'recorded') }}
                                    </flux:badge>

                                    @if ($log->is_flagged)
                                        <flux:badge color="amber" inset="top bottom">
                                            Flagged
                                        </flux:badge>
                                    @endif
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ number_format((float) ($log->hours_rendered ?? 0), 2) }} hrs
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="py-8 text-center text-zinc-500">
                                No attendance records found.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>
</div>
