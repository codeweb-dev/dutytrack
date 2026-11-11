<?php

use App\Livewire\Concerns\BuildsDashboardCharts;
use App\Models\AttendanceLog;
use App\Models\OjtAssignment;
use App\Models\StudentRequirement;
use App\Models\StudentTask;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Student Dashboard')] class extends Component
{
    use BuildsDashboardCharts;

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

    <section data-tour="dashboard-workflow" class="grid gap-4 xl:grid-cols-[minmax(0,1.6fr)_minmax(320px,1fr)]">
        <x-dashboard.attendance-chart :attendance="$this->weeklyAttendance"
            description="Your attendance logs over the last 7 days." />
        <x-dashboard.placement-mix :mix="$this->ojtProgress" title="OJT Progress" center-label="Required hours" empty-label="No hours required yet"
            description="Your rendered hours against your required OJT hours." />
    </section>
</div>
