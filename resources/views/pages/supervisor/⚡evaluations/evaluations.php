<?php

use App\Livewire\Concerns\WithSorting;
use App\Models\User;
use App\Models\StudentProfile;
use App\Livewire\Concerns\ManagesPracticumEvaluations;
use App\Models\Evaluation;
use App\Models\OjtAssignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Evaluations')] class extends Component
{
    use WithPagination;
    use WithSorting;
    use ManagesPracticumEvaluations;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Supervisor Workspace', 'href' => '#'],
        ['label' => 'Evaluations'],
    ];

    public string $title = 'Evaluations';

    public string $description = 'Evaluate assigned trainees and review submitted practicum evaluations.';

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

    protected function evaluatorType(): string
    {
        return 'company_supervisor';
    }

    protected function assignmentQuery(): Builder
    {
        return OjtAssignment::query()
            ->with([
                'student.studentProfile',
                'student.coordinator',
                'student.evaluations' => fn ($query) => $query
                    ->where('evaluator_type', $this->evaluatorType()),
            ])
            ->where('company_supervisor_id', Auth::id());
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'status', 'sortBy', 'sortDirection');

        $this->resetPage();
        unset($this->assignments);
    }

    #[Computed]
    public function assignments(): LengthAwarePaginator
    {
        $evaluated = fn (Builder $query) => $query->where('evaluator_type', $this->evaluatorType());

        $query = $this->assignmentQuery()
            ->when($this->status === 'evaluated', fn (Builder $query) => $query->whereHas('student.evaluations', $evaluated))
            ->when($this->status === 'pending', fn (Builder $query) => $query->whereDoesntHave('student.evaluations', $evaluated))
            ->when($this->search !== '', function (Builder $query) {
                $search = '%'.$this->search.'%';

                $query->whereHas('student', function (Builder $student) use ($search) {
                    $student->where('name', 'like', $search)
                        ->orWhere('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhereHas('studentProfile', function (Builder $profile) use ($search) {
                            $profile->where('student_id', 'like', $search)
                                ->orWhere('course', 'like', $search);
                        });
                });
            });

        return $this->applySorting($query, [
            'student' => User::select('name')->whereColumn('users.id', 'ojt_assignments.student_id'),
            'student_id' => StudentProfile::select('student_id')->whereColumn('student_profiles.user_id', 'ojt_assignments.student_id'),
            'course' => StudentProfile::select('course')->whereColumn('student_profiles.user_id', 'ojt_assignments.student_id'),
            'rating' => Evaluation::selectRaw('max(rating)')
                ->whereColumn('evaluations.student_id', 'ojt_assignments.student_id')
                ->where('evaluator_type', $this->evaluatorType()),
        ])
            ->latest('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function summary(): array
    {
        $total = OjtAssignment::query()->where('company_supervisor_id', Auth::id())->count();
        $evaluated = OjtAssignment::query()
            ->where('company_supervisor_id', Auth::id())
            ->whereHas('student.evaluations', fn (Builder $query) => $query->where('evaluator_type', $this->evaluatorType()))
            ->count();

        return ['total' => $total, 'evaluated' => $evaluated, 'pending' => $total - $evaluated];
    }
};
?>
