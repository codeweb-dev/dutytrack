<?php

use App\Livewire\Concerns\WithSorting;
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
    use WithPagination;
    use WithSorting;

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
        unset($this->assignments, $this->summary);
    }

    public function clearFilters(): void
    {
        $this->reset('sortBy', 'sortDirection');
        $this->search = '';
        $this->status = '';

        $this->resetPage();
        unset($this->assignments, $this->summary);
    }

    public function badgeColor(?string $status): string
    {
        return match ($status) {
            'active' => 'lime',
            'pending' => 'amber',
            'completed' => 'sky',
            'cancelled' => 'red',
            default => 'zinc',
        };
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
            })
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status));
    }

    #[Computed]
    public function assignments(): LengthAwarePaginator
    {
        return $this->applySorting($this->applyFilters($this->assignmentQuery()), [
            'student' => User::select('name')->whereColumn('users.id', 'ojt_assignments.student_id'),
            'student_id' => StudentProfile::select('student_id')->whereColumn('student_profiles.user_id', 'ojt_assignments.student_id'),
            'course' => StudentProfile::select('course')->whereColumn('student_profiles.user_id', 'ojt_assignments.student_id'),
            'status' => 'status',
        ])
            ->latest('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function summary(): array
    {
        $query = $this->assignmentQuery();

        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
        ];
    }
};
