<?php

use App\Livewire\Concerns\BuildsDashboardCharts;
use App\Livewire\Concerns\WithSorting;
use App\Models\AttendanceLog;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\OjtAssignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Assigned Students')] class extends Component
{
    use BuildsDashboardCharts;
    use WithPagination;
    use WithSorting;

    // Same buckets as the dashboard's OJT Progress chart.
    private const RENDERED = '(select coalesce(sum(hours_rendered), 0) from attendance_logs where attendance_logs.student_id = ojt_assignments.student_id)';

    private const REQUIRED = '(select coalesce(max(ojt_required_hours), 0) from student_profiles where student_profiles.user_id = ojt_assignments.student_id)';

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Supervisor Workspace', 'href' => '#'],
        ['label' => 'Assigned Students'],
    ];

    public string $title = 'Assigned Students';

    public string $description = 'View the students assigned to you for OJT supervision.';

    public string $search = '';

    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->assignments);
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        unset($this->assignments);
    }

    public function clearFilters(): void
    {
        $this->reset('sortBy', 'sortDirection', 'status');
        $this->search = '';

        $this->resetPage();
        unset($this->assignments);
    }

    protected function assignmentQuery(): Builder
    {
        return OjtAssignment::query()
            ->with([
                'student.studentProfile',
                'student.coordinator',
                'attendanceLogs',
            ])
            ->where('company_supervisor_id', Auth::id());
    }

    protected function applyFilters(Builder $query): Builder
    {
        $rendered = self::RENDERED;
        $required = self::REQUIRED;

        match ($this->status) {
            'completed' => $query->whereRaw("$required > 0 and $rendered >= $required"),
            'in_progress' => $query->whereRaw("$rendered > 0 and ($required = 0 or $rendered < $required)"),
            'not_started' => $query->whereRaw("$rendered <= 0"),
            default => null,
        };

        return $query
            ->when($this->search !== '', function (Builder $query) {
                $search = '%'.$this->search.'%';

                $query->whereHas('student', function (Builder $studentQuery) use ($search) {
                    $studentQuery
                        ->where(function (Builder $nested) use ($search) {
                            $nested->where('users.name', 'like', $search)
                                ->orWhere('users.first_name', 'like', $search)
                                ->orWhere('users.last_name', 'like', $search)
                                ->orWhere('users.email', 'like', $search);
                        })
                        ->orWhereHas('studentProfile', function (Builder $profileQuery) use ($search) {
                            $profileQuery->where('student_id', 'like', $search)
                                ->orWhere('course', 'like', $search);
                        });
                });
            });
    }

    #[Computed]
    public function assignments(): LengthAwarePaginator
    {
        return $this->applySorting($this->applyFilters($this->assignmentQuery()), [
            'student' => User::select('name')->whereColumn('users.id', 'ojt_assignments.student_id'),
            'student_id' => StudentProfile::select('student_id')->whereColumn('student_profiles.user_id', 'ojt_assignments.student_id'),
            'course' => StudentProfile::select('course')->whereColumn('student_profiles.user_id', 'ojt_assignments.student_id'),
            'hours' => AttendanceLog::selectRaw('coalesce(sum(hours_rendered), 0)')->whereColumn('attendance_logs.student_id', 'ojt_assignments.student_id'),
        ])
            ->select('ojt_assignments.*')
            ->selectRaw(self::RENDERED.' as rendered_hours')
            ->latest('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function summary(): array
    {
        $counts = collect($this->ojtProgressChart(OjtAssignment::query()->where('company_supervisor_id', Auth::id()))['segments'])
            ->pluck('value', 'label');

        return [
            'total' => $counts->sum(),
            'completed' => $counts['Completed'],
            'in_progress' => $counts['In progress'],
            'not_started' => $counts['Not started'],
        ];
    }
};
