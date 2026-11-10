<?php

use App\Livewire\Concerns\WithSorting;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\AttendanceLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Accomplishment Reports')] class extends Component
{
    use WithPagination;
    use WithSorting;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Supervisor Workspace', 'href' => '#'],
        ['label' => 'Accomplishment Reports'],
    ];

    public string $title = 'Accomplishment Reports';

    public string $description = 'Review students’ daily accomplishment reports and attached photos.';

    public string $search = '';

    public string $from_date = '';

    public string $to_date = '';

    public string $status = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'from_date', 'to_date', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'from_date', 'to_date', 'status', 'sortBy', 'sortDirection');

        $this->resetPage();
        unset($this->submissions);
    }

    // Completed days of this supervisor's students; each one is expected to have a report.
    protected function baseQuery(): Builder
    {
        return AttendanceLog::query()
            ->whereHas('assignment', fn (Builder $query) => $query->where('company_supervisor_id', Auth::id()))
            ->whereNotNull('afternoon_time_out');
    }

    protected function applyStatus(Builder $query, string $status): Builder
    {
        $hasReport = fn (Builder $submission) => $submission->whereNotNull('accomplishment')->where('accomplishment', '!=', '');

        return match ($status) {
            'submitted' => $query->whereHas('submission', $hasReport),
            'today' => $query->whereDate('attendance_date', today()),
            'week' => $query->whereDate('attendance_date', '>=', now()->startOfWeek()),
            default => $query,
        };
    }

    #[Computed]
    public function summary(): array
    {
        return collect(['total' => '', 'submitted' => 'submitted', 'today' => 'today', 'week' => 'week'])
            ->map(fn (string $status) => $this->applyStatus($this->baseQuery(), $status)->count())
            ->all();
    }

    #[Computed]
    public function submissions(): LengthAwarePaginator
    {
        $query = $this->applyStatus($this->baseQuery(), $this->status)
            ->with(['student.studentProfile', 'submission'])
            ->when($this->search !== '', function (Builder $query) {
                $search = '%'.$this->search.'%';

                $query->whereHas('student', function (Builder $student) use ($search) {
                    $student->where('name', 'like', $search)
                        ->orWhere('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhereHas('studentProfile', fn (Builder $profile) => $profile->where('student_id', 'like', $search));
                });
            })
            ->when($this->from_date !== '', fn (Builder $query) => $query->whereDate('attendance_date', '>=', $this->from_date))
            ->when($this->to_date !== '', fn (Builder $query) => $query->whereDate('attendance_date', '<=', $this->to_date));

        return $this->applySorting($query, [
            'student' => User::select('name')->whereColumn('users.id', 'attendance_logs.student_id'),
            'student_id' => StudentProfile::select('student_id')->whereColumn('student_profiles.user_id', 'attendance_logs.student_id'),
            'date' => 'attendance_date',
            'hours' => 'hours_rendered',
        ])
            ->latest('attendance_date')
            ->paginate(10);
    }
};
