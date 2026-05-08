<?php

use App\Models\OjtAssignment;
use App\Models\StudentTask;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Tasks')] class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Professor Workspace', 'href' => '#'],
        ['label' => 'Tasks'],
    ];

    public string $title = 'Tasks';

    public string $description = 'Assign and monitor tasks for students assigned to you.';

    public string $search = '';

    public string $status = '';

    public array $student_ids = [];

    public string $task_title = '';

    public string $task_description = '';

    public string $priority = 'normal';

    public ?string $due_date = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->tasks);
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        unset($this->tasks, $this->summary);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';

        unset($this->tasks, $this->summary, $this->columns);
    }

    public function saveTask(): void
    {
        $this->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'distinct'],
            'task_title' => ['required', 'string', 'max:255'],
            'task_description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high'])],
            'due_date' => ['nullable', 'date'],
        ], [], [
            'student_ids' => 'students',
            'student_ids.*' => 'student',
            'task_title' => 'task title',
            'task_description' => 'task description',
            'due_date' => 'due date',
        ]);

        $assignments = $this->assignedStudentAssignments($this->student_ids);

        $assignments->each(function (OjtAssignment $assignment): void {
            StudentTask::query()->create([
                'student_id' => $assignment->student_id,
                'assigned_by' => Auth::id(),
                'ojt_assignment_id' => $assignment->id,
                'title' => $this->task_title,
                'description' => $this->task_description ?: null,
                'priority' => $this->priority,
                'due_date' => $this->due_date ?: null,
                'status' => 'pending',
            ]);
        });

        $this->resetTaskForm();
        unset($this->tasks, $this->summary, $this->columns);

        Flux::modal('create-professor-task')->close();
        Flux::toast(
            variant: 'success',
            heading: 'Task assigned.',
            text: $assignments->count() === 1
                ? 'The task was assigned to the student.'
                : 'The task was assigned to '.$assignments->count().' students.'
        );
    }

    public function resetTaskForm(): void
    {
        $this->student_ids = [];
        $this->task_title = '';
        $this->task_description = '';
        $this->priority = 'normal';
        $this->due_date = null;

        $this->resetValidation();
    }

    protected function assignedStudentAssignments(array $studentIds)
    {
        $studentIds = collect($studentIds)
            ->map(fn ($studentId): int => (int) $studentId)
            ->filter()
            ->unique()
            ->values();

        $assignments = OjtAssignment::query()
            ->where('professor_id', Auth::id())
            ->whereIn('student_id', $studentIds)
            ->get();

        abort_unless($assignments->count() === $studentIds->count(), 403);

        return $assignments;
    }

    protected function taskQuery(): Builder
    {
        return StudentTask::query()
            ->with(['student.studentProfile', 'assignment', 'assignedBy'])
            ->where('assigned_by', Auth::id())
            ->whereHas('assignment', fn (Builder $query) => $query->where('professor_id', Auth::id()));
    }

    protected function applyFilters(Builder $query): Builder
    {
        return $query
            ->when($this->search !== '', function (Builder $query) {
                $search = '%'.$this->search.'%';

                $query->where(function (Builder $nested) use ($search) {
                    $nested->where('title', 'like', $search)
                        ->orWhereHas('student', function (Builder $studentQuery) use ($search) {
                            $studentQuery
                                ->where('users.name', 'like', $search)
                                ->orWhere('users.email', 'like', $search)
                                ->orWhereHas('studentProfile', function (Builder $profileQuery) use ($search) {
                                    $profileQuery->where('student_id', 'like', $search)
                                        ->orWhere('course', 'like', $search);
                                });
                        });
                });
            })
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status));
    }

    public function statusColor(?string $status): string
    {
        return match ($status) {
            'done' => 'green',
            'in_review' => 'blue',
            'in_progress' => 'amber',
            default => 'zinc',
        };
    }

    public function priorityColor(?string $priority): string
    {
        return match ($priority) {
            'high' => 'red',
            'low' => 'sky',
            default => 'zinc',
        };
    }

    #[Computed]
    public function assignableStudents()
    {
        return OjtAssignment::query()
            ->with('student.studentProfile')
            ->where('professor_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('student')
            ->filter();
    }

    #[Computed]
    public function tasks()
    {
        return $this->applyFilters($this->taskQuery())
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'normal' THEN 1 ELSE 2 END")
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest('created_at')
            ->get();
    }

    #[Computed]
    public function columns(): array
    {
        $tasks = $this->tasks->groupBy('status');

        return collect(StudentTask::statusLabels())
            ->map(function (string $label, string $status) use ($tasks): array {
                return [
                    'status' => $status,
                    'title' => $label,
                    'tasks' => $tasks->get($status, collect()),
                ];
            })
            ->values()
            ->all();
    }

    public function displayColumns(): array
    {
        $columns = collect($this->columns)->keyBy('status');

        return collect(['pending', 'in_progress', 'done', 'in_review'])
            ->map(fn (string $status): array => $columns->get($status))
            ->filter()
            ->values()
            ->all();
    }

    #[Computed]
    public function summary(): array
    {
        $query = $this->taskQuery();

        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'done' => (clone $query)->where('status', 'done')->count(),
        ];
    }
};
