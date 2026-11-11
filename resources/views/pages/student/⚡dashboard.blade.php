<?php

use App\Livewire\Concerns\BuildsDashboardCharts;
use App\Models\AttendanceLog;
use App\Models\OjtAssignment;
use App\Models\StudentRequirement;
use App\Models\StudentTask;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Student Dashboard')] class extends Component
{
    use BuildsDashboardCharts;
    use WithPagination;

    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'Student Dashboard']];

    #[Computed]
    public function title(): string
    {
        return 'Welcome back, '.(Auth::user()?->name ?? 'Student').' 👋';
    }

    public string $description = 'A simple overview of your placement and attendance.';

    protected function studentId(): int
    {
        return (int) Auth::id();
    }

    #[Computed]
    public function currentStudent(): User
    {
        return User::query()
            ->with(['studentProfile', 'coordinator', 'ojtAssignment.companySupervisor'])
            ->findOrFail($this->studentId());
    }

    #[Computed]
    public function assignment(): ?OjtAssignment
    {
        return $this->currentStudent->ojtAssignment;
    }

    #[Computed]
    public function renderedHours(): float
    {
        return (float) AttendanceLog::query()->where('student_id', $this->studentId())->sum('hours_rendered');
    }

    #[Computed]
    public function topCards(): array
    {
        $assignment = $this->assignment;

        return [
            [
                'title' => 'Rendered Hours',
                'value' => number_format(floor($this->renderedHours)),
                'suffix' => '/ '.number_format($this->currentStudent->studentProfile?->ojt_required_hours ?? 0).' hrs',
                'icon' => 'clock',
                'href' => route('student.attendance'),
                'link' => 'View attendance',
            ],
            [
                'title' => 'Requirements',
                'value' => number_format($this->requirementSummary['completed']),
                'suffix' => '/ '.number_format($this->requirementSummary['total']),
                'icon' => 'clipboard-document-check',
                'href' => route('student.requirements'),
                'link' => 'View requirements',
            ],
            [
                'title' => 'Open Tasks',
                'value' => number_format(StudentTask::query()->where('student_id', $this->studentId())->where('status', '!=', 'done')->count()),
                'icon' => 'rectangle-stack',
                'href' => route('student.tasks'),
                'link' => 'View tasks',
            ],
            [
                'title' => 'Placement',
                'value' => $assignment ? ucfirst($assignment->ojt_type) : 'Unassigned',
                'suffix' => $assignment ? 'OJT' : null,
                'icon' => 'briefcase',
                'href' => route('student.messages'),
                'link' => $assignment ? 'Message supervisor' : 'Message coordinator',
            ],
        ];
    }

    #[Computed]
    public function weeklyAttendance(): array
    {
        return $this->weeklyAttendanceChart(AttendanceLog::query()->where('student_id', $this->studentId()));
    }

    #[Computed]
    public function ojtProgress(): array
    {
        $required = (int) ($this->currentStudent->studentProfile?->ojt_required_hours ?? 0);
        $rendered = (int) min(floor($this->renderedHours), $required);

        return $this->donutChart([
            'Rendered' => [$rendered, 'stroke-green-500', 'bg-green-500'],
            'Remaining' => [$required - $rendered, 'stroke-zinc-400', 'bg-zinc-400'],
        ]);
    }

    #[Computed]
    public function recentAttendance(): LengthAwarePaginator
    {
        return AttendanceLog::query()->where('student_id', $this->studentId())->latest('attendance_date')->latest('created_at')->paginate(10);
    }

    #[Computed]
    public function requirements()
    {
        return StudentRequirement::ensureForStudent($this->currentStudent);
    }

    #[Computed]
    public function requirementSummary(): array
    {
        $requirements = $this->requirements;

        return ['total' => $requirements->count(), 'completed' => $requirements->where('is_completed', true)->count()];
    }
};
?>

<div class="flex flex-col gap-8">
    <div data-tour="dashboard-overview"><x-page-header :breadcrumbs="$breadcrumbs" :title="$this->title" :description="$description" /></div>

    <section class="grid grid-cols-2 gap-4 xl:grid-cols-4" data-tour="dashboard-metrics">
        @foreach ($this->topCards as $card)
            <a href="{{ $card['href'] }}" @if (! str_starts_with($card['href'], '#')) wire:navigate @endif class="group text-left">
                <flux:card size="xs" class="h-full hover:bg-zinc-50 dark:hover:bg-zinc-700">
                    <flux:heading class="flex items-center gap-2">{{ $card['title'] }}
                        <flux:icon :name="$card['icon']" class="ml-auto size-5 text-zinc-400" />
                    </flux:heading>
                    <flux:heading size="2xl" class="mt-2">{{ $card['value'] }}
                        @if ($card['suffix'] ?? null)
                            <span class="text-base font-normal text-zinc-400">{{ $card['suffix'] }}</span>
                        @endif
                    </flux:heading>
                    <div
                        class="mt-3 flex items-center gap-1.5 border-t border-zinc-100 pt-3 text-sm text-zinc-500 transition group-hover:text-zinc-900 dark:border-zinc-700 dark:text-zinc-400 dark:group-hover:text-white">
                        {{ $card['link'] }}
                        <flux:icon name="arrow-right" variant="micro" class="transition group-hover:translate-x-0.5" />
                    </div>
                </flux:card>
            </a>
        @endforeach
    </section>

    <section class="grid gap-4 xl:grid-cols-[minmax(0,1.6fr)_minmax(320px,1fr)]">
        <x-dashboard.attendance-chart :attendance="$this->weeklyAttendance"
            description="Your attendance logs over the last 7 days." />
        <x-dashboard.placement-mix :mix="$this->ojtProgress" title="OJT Progress" center-label="Required hours" empty-label="No hours required yet"
            description="Your rendered hours against your required OJT hours." />
    </section>

    <div class="grid items-start gap-6">
        <div class="flex min-w-0 flex-col gap-6">
            <flux:card data-tour="dashboard-workflow">
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
                            <flux:table.column>Morning In</flux:table.column>
                            <flux:table.column>Morning Out</flux:table.column>
                            <flux:table.column>Afternoon In</flux:table.column>
                            <flux:table.column>Afternoon Out</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                            <flux:table.column>Hours</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($this->recentAttendance as $log)
                                <flux:table.row :key="$log->id">
                                    <flux:table.cell>
                                        {{ $log->attendance_date?->format('M d, Y') ?? '' }}
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        {{ $log->displayMorningTimeIn()?->format('h:i A') ?? '' }}
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        {{ $log->displayMorningTimeOut()?->format('h:i A') ?? '' }}
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        {{ $log->displayAfternoonTimeIn()?->format('h:i A') ?? '' }}
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        {{ $log->displayAfternoonTimeOut()?->format('h:i A') ?? '' }}
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        <div class="flex items-center gap-2">
                                            <flux:badge size="sm" color="sky" inset="top bottom">
                                                {{ ucfirst($log->status ?? 'recorded') }}
                                            </flux:badge>
                                            @if ($log->isLate() && $log->status !== 'late')
                                                <flux:badge size="sm" color="amber" inset="top bottom">Late</flux:badge>
                                            @endif
                                        </div>
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        {{ \App\Support\HourFormatter::format($log->hours_rendered ?? 0) }}
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

        </div>

    </div>
</div>
