<?php

use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Evaluation Monitoring')] class extends Component {
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Monitoring', 'href' => '#'],
        ['label' => 'Evaluation'],
    ];

    public string $title = 'Evaluation Monitoring';
    public string $description = 'Track submitted student evaluations from supervisors.';

    public string $search = '';
    public string $ojt_type = '';
    public string $status = '';
    public string $evaluator_id = '';
    public ?string $from_date = null;
    public ?string $to_date = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->evaluations);
    }

    public function updatedOjtType(): void
    {
        $this->resetPage();
        unset($this->evaluations, $this->summary);
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        unset($this->evaluations, $this->summary);
    }

    public function updatedEvaluatorId(): void
    {
        $this->resetPage();
        unset($this->evaluations, $this->summary);
    }

    public function updatedFromDate(): void
    {
        $this->resetPage();
        unset($this->evaluations, $this->summary);
    }

    public function updatedToDate(): void
    {
        $this->resetPage();
        unset($this->evaluations, $this->summary);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->ojt_type = '';
        $this->status = '';
        $this->evaluator_id = '';
        $this->from_date = null;
        $this->to_date = null;

        $this->resetPage();
        unset($this->evaluations, $this->summary);
    }

    public function badgeColor(string $status): string
    {
        return match ($status) {
            'submitted' => 'lime',
            'reviewed' => 'sky',
            'pending' => 'amber',
            default => 'zinc',
        };
    }

    #[Computed]
    public function evaluations(): LengthAwarePaginator
    {
        return Evaluation::query()
            ->with([
                'student.studentProfile',
                'assignment',
                'evaluator',
            ])
            ->when($this->search !== '', function ($query) {
                $search = '%' . $this->search . '%';

                $query->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery->where(function ($nested) use ($search) {
                        $nested->where('first_name', 'like', $search)
                            ->orWhere('last_name', 'like', $search)
                            ->orWhere('name', 'like', $search)
                            ->orWhere('student_id', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    });
                });
            })
            ->when($this->ojt_type !== '', function ($query) {
                $query->whereHas('assignment', function ($assignmentQuery) {
                    $assignmentQuery->where('ojt_type', $this->ojt_type);
                });
            })
            ->when($this->status !== '', fn($query) => $query->where('status', $this->status))
            ->when($this->evaluator_id !== '', fn($query) => $query->where('evaluator_id', $this->evaluator_id))
            ->when($this->from_date, fn($query) => $query->whereDate('evaluation_date', '>=', $this->from_date))
            ->when($this->to_date, fn($query) => $query->whereDate('evaluation_date', '<=', $this->to_date))
            ->latest('evaluation_date')
            ->latest('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function summary(): array
    {
        $query = Evaluation::query()
            ->when($this->ojt_type !== '', function ($query) {
                $query->whereHas('assignment', function ($assignmentQuery) {
                    $assignmentQuery->where('ojt_type', $this->ojt_type);
                });
            })
            ->when($this->status !== '', fn($query) => $query->where('status', $this->status))
            ->when($this->evaluator_id !== '', fn($query) => $query->where('evaluator_id', $this->evaluator_id))
            ->when($this->from_date, fn($query) => $query->whereDate('evaluation_date', '>=', $this->from_date))
            ->when($this->to_date, fn($query) => $query->whereDate('evaluation_date', '<=', $this->to_date));

        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'submitted' => (clone $query)->where('status', 'submitted')->count(),
            'reviewed' => (clone $query)->where('status', 'reviewed')->count(),
            'average_rating' => round((float) (clone $query)->avg('rating'), 2),
        ];
    }

    #[Computed]
    public function evaluators()
    {
        return User::query()
            ->role('supervisor')
            ->orderBy('first_name')
            ->get();
    }
};
?>
