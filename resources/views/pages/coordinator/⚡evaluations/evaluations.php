<?php

use App\Livewire\Concerns\WithSorting;
use App\Models\Evaluation;
use App\Models\StudentProfile;
use App\Models\User;
use App\Support\AuditLogger;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Evaluation Review')] class extends Component
{
    use WithPagination;
    use WithSorting;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Coordinator Workspace', 'href' => '#'],
        ['label' => 'Monitoring', 'href' => '#'],
        ['label' => 'Evaluations'],
    ];

    public string $title = 'Evaluation Review';

    public string $description = 'Review evaluations submitted by your students’ immediate supervisors.';

    public string $search = '';

    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->evaluations);
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        unset($this->evaluations);
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'status', 'sortBy', 'sortDirection');
        $this->resetPage();
        unset($this->evaluations);
    }

    protected function evaluationQuery(): Builder
    {
        return Evaluation::query()
            ->with(['student.studentProfile', 'evaluator'])
            ->where('evaluator_type', 'company_supervisor')
            ->whereHas('student', fn (Builder $query) => $query->where('coordinator_id', Auth::id()));
    }

    public function markReviewed(int $evaluationId): void
    {
        DB::transaction(function () use ($evaluationId): void {
            $evaluation = $this->evaluationQuery()
                ->where('status', 'submitted')
                ->lockForUpdate()
                ->findOrFail($evaluationId);
            $oldValues = ['status' => $evaluation->status];
            $evaluation->update(['status' => 'reviewed']);

            AuditLogger::log(
                module: 'Evaluations',
                action: 'reviewed',
                target: $evaluation,
                targetLabel: $evaluation->student?->display_name,
                description: 'Marked a supervisor evaluation as reviewed.',
                oldValues: $oldValues,
                newValues: ['status' => $evaluation->status],
            );
        });

        unset($this->evaluations, $this->summary);
        Flux::toast(variant: 'success', text: 'Evaluation reviewed.');
    }

    #[Computed]
    public function summary(): array
    {
        $counts = $this->evaluationQuery()->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'total' => (int) $counts->sum(),
            'pending' => (int) ($counts['pending'] ?? 0),
            'submitted' => (int) ($counts['submitted'] ?? 0),
            'reviewed' => (int) ($counts['reviewed'] ?? 0),
        ];
    }

    #[Computed]
    public function evaluations(): LengthAwarePaginator
    {
        $query = $this->evaluationQuery()
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->search !== '', function (Builder $query): void {
                $search = '%'.$this->search.'%';

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('status', 'like', $search)
                        ->orWhere('remarks', 'like', $search)
                        ->orWhereHas('student', function (Builder $student) use ($search): void {
                            $student->where('name', 'like', $search)
                                ->orWhere('first_name', 'like', $search)
                                ->orWhere('last_name', 'like', $search)
                                ->orWhereHas('studentProfile', fn (Builder $profile) => $profile->where('student_id', 'like', $search));
                        })
                        ->orWhereHas('evaluator', fn (Builder $evaluator) => $evaluator->where('name', 'like', $search));
                });
            });

        return $this->applySorting($query, [
            'student' => User::select('name')->whereColumn('users.id', 'evaluations.student_id'),
            'student_id' => StudentProfile::select('student_id')->whereColumn('student_profiles.user_id', 'evaluations.student_id'),
            'supervisor' => User::select('name')->whereColumn('users.id', 'evaluations.evaluator_id'),
            'date' => 'evaluation_date',
            'rating' => 'rating',
            'status' => 'status',
        ])
            ->latest('evaluation_date')
            ->latest('created_at')
            ->paginate(10);
    }
};
