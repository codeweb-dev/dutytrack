<?php

use App\Livewire\Concerns\ManagesPracticumEvaluations;
use App\Models\Evaluation;
use App\Models\OjtAssignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Evaluations')] class extends Component
{
    use WithPagination;
    use ManagesPracticumEvaluations;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Professor Workspace', 'href' => '#'],
        ['label' => 'Evaluations'],
    ];

    public string $title = 'Evaluations';

    public string $description = 'Evaluate assigned students and review submitted practicum evaluations.';

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->evaluations);
        unset($this->assignments);
    }

    protected function evaluatorType(): string
    {
        return 'professor';
    }

    protected function evaluationPickerModalName(): ?string
    {
        return 'professor-evaluation-students';
    }

    protected function assignmentQuery(): Builder
    {
        return OjtAssignment::query()
            ->with(['student.studentProfile', 'student.coordinator', 'evaluations'])
            ->where('professor_id', Auth::id());
    }

    #[Computed]
    public function assignments(): Collection
    {
        return $this->assignmentQuery()
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
            })
            ->latest('created_at')
            ->get();
    }

    #[Computed]
    public function evaluations(): LengthAwarePaginator
    {
        return Evaluation::query()
            ->with(['student.studentProfile', 'assignment', 'evaluator'])
            ->where('evaluator_id', Auth::id())
            ->where('evaluator_type', 'professor')
            ->when($this->search !== '', function ($query) {
                $search = '%'.$this->search.'%';

                $query->where(function ($nested) use ($search) {
                    $nested->where('cooperating_program', 'like', $search)
                        ->orWhere('remarks', 'like', $search)
                        ->orWhereHas('student', function ($student) use ($search) {
                            $student->where('name', 'like', $search)
                                ->orWhere('first_name', 'like', $search)
                                ->orWhere('last_name', 'like', $search)
                                ->orWhere('email', 'like', $search);
                        });
                });
            })
            ->latest('evaluation_date')
            ->latest('created_at')
            ->paginate(10);
    }
};
?>
