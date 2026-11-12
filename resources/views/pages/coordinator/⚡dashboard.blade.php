<?php

use App\Livewire\Concerns\BuildsDashboardCharts;
use App\Models\AttendanceLog;
use App\Models\OjtAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Coordinator Dashboard')] class extends Component
{
    use BuildsDashboardCharts;

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
                'value' => $this->managedStudentsQuery()->whereHas('ojtAssignment.companySupervisor')->count(),
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
    public function weeklyAttendance(): array
    {
        return $this->weeklyAttendanceChart($this->managedAttendanceQuery());
    }

    #[Computed]
    public function placementMix(): array
    {
        return $this->placementMixChart($this->managedAssignmentsQuery());
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
        <x-dashboard.attendance-chart :attendance="$this->weeklyAttendance" description="Attendance logs from your students over the last 7 days." />
        <x-dashboard.placement-mix :mix="$this->placementMix" description="How your placed students split between internal and external OJT." />
    </section>
</div>
