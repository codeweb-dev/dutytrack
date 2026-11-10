<?php

use App\Livewire\Concerns\BuildsDashboardCharts;
use App\Models\AttendanceLog;
use App\Models\Evaluation;
use App\Models\OjtAssignment;
use App\Models\StudentTask;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Supervisor Dashboard')] class extends Component {
    use BuildsDashboardCharts, WithPagination;

    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'Supervisor Dashboard']];

    #[Computed]
    public function title(): string
    {
        return 'Welcome back, ' . (Auth::user()?->name ?? 'Supervisor') . ' 👋';
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
        $trainees = $this->supervisorAssignmentsQuery()->count();

        return [
            [
                'title' => 'Assigned Students',
                'value' => $trainees,
                'icon' => 'academic-cap',
                'href' => route('supervisor.assigned-students'),
                'link' => 'View students',
            ],
            [
                'title' => 'Present Today',
                'value' => $this->supervisorAttendanceQuery()->whereDate('attendance_date', today())->distinct()->count('student_id'),
                'suffix' => '/ ' . number_format($trainees),
                'icon' => 'clock',
                'href' => route('supervisor.attendance'),
                'link' => 'View attendance',
            ],
            [
                'title' => 'Tasks to Review',
                'value' => StudentTask::query()->where('assigned_by', $this->supervisorId())->where('status', 'in_review')->whereHas('assignment', fn(Builder $query) => $query->where('company_supervisor_id', $this->supervisorId()))->count(),
                'icon' => 'rectangle-stack',
                'href' => route('supervisor.tasks'),
                'link' => 'Review tasks',
            ],
            [
                'title' => 'Evaluations Submitted',
                'value' => Evaluation::query()->where('evaluator_id', $this->supervisorId())->where('status', 'submitted')->count(),
                'icon' => 'check-badge',
                'href' => route('supervisor.evaluations'),
                'link' => 'View evaluations',
            ],
        ];
    }

    #[Computed]
    public function weeklyAttendance(): array
    {
        return $this->weeklyAttendanceChart($this->supervisorAttendanceQuery());
    }

    #[Computed]
    public function ojtProgress(): array
    {
        return $this->ojtProgressChart($this->supervisorAssignmentsQuery());
    }

    #[Computed]
    public function recentTrainees(): LengthAwarePaginator
    {
        return $this->supervisorAssignmentsQuery()
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
            ->paginate(10);
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
                    <flux:heading size="2xl" class="mt-2">{{ number_format($card['value']) }}
                        @isset($card['suffix'])
                            <span class="text-base font-normal text-zinc-400">{{ $card['suffix'] }}</span>
                        @endisset
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

    <section class="grid gap-4 xl:grid-cols-[minmax(0,1.6fr)_minmax(320px,1fr)]" data-tour="dashboard-workflow">
        <x-dashboard.attendance-chart :attendance="$this->weeklyAttendance"
            description="Attendance logs from your trainees over the last 7 days." />
        <x-dashboard.placement-mix :mix="$this->ojtProgress" title="OJT Progress" center-label="Students" empty-label="No students yet"
            description="Your students by rendered hours against their required OJT hours." />
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
                                        {{ $assignment->student?->display_name ?? '' }}
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
                                {{ $assignment->student?->coordinator?->display_name ?? '' }}
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
