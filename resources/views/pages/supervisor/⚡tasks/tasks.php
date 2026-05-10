<?php

use App\Models\OjtAssignment;
use App\Models\StudentTask;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Tasks')] class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Supervisor Workspace', 'href' => '#'],
        ['label' => 'Tasks'],
    ];

    public string $title = 'Tasks';

    public string $description = 'Assign and monitor tasks for trainees assigned to you.';

    public string $search = '';

    public string $status = '';

    public array $student_ids = [];

    public string $task_title = '';

    public string $task_description = '';

    public string $priority = 'normal';

    public ?string $due_date = null;

    public function updatedSearch(): void
    {
        unset($this->tasks);
    }

    public function updatedStatus(): void
    {
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
        $taskGroupId = (string) Str::uuid();
        $sortOrder = $this->nextSupervisorTaskSortOrder('pending');

        $assignments->each(function (OjtAssignment $assignment) use ($taskGroupId, $sortOrder): void {
            StudentTask::query()->create([
                'student_id' => $assignment->student_id,
                'assigned_by' => Auth::id(),
                'ojt_assignment_id' => $assignment->id,
                'task_group_id' => $taskGroupId,
                'title' => $this->task_title,
                'description' => $this->task_description ?: null,
                'priority' => $this->priority,
                'due_date' => $this->due_date ?: null,
                'status' => 'pending',
                'sort_order' => $sortOrder,
            ]);
        });

        $this->resetTaskForm();
        unset($this->tasks, $this->summary, $this->columns);

        Flux::modal('create-supervisor-task')->close();
        Flux::toast(
            variant: 'success',
            heading: 'Task assigned.',
            text: $assignments->count() === 1
                ? 'The task was assigned to the trainee.'
                : 'The task was assigned to '.$assignments->count().' trainees.'
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
            ->where('company_supervisor_id', Auth::id())
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
            ->whereHas('assignment', fn (Builder $query) => $query->where('company_supervisor_id', Auth::id()));
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

    public function sortTaskCard(string $taskCardKey, int $position, string $status): void
    {
        abort_unless(array_key_exists($status, StudentTask::statusLabels()), 422);

        $tasks = $this->taskCardQuery($taskCardKey)->get();

        abort_if($tasks->isEmpty(), 404);

        $previousStatuses = $tasks
            ->pluck('status')
            ->unique()
            ->reject(fn (string $previousStatus): bool => $previousStatus === $status)
            ->values();

        DB::transaction(function () use ($tasks, $taskCardKey, $position, $status, $previousStatuses): void {
            $tasks->each(function (StudentTask $task) use ($position, $status): void {
                $task->update([
                    'status' => $status,
                    'sort_order' => max(0, $position),
                    'completed_at' => $status === 'done' ? now() : null,
                ]);
            });

            $this->reorderSupervisorStatus($status, $taskCardKey, $position);

            $previousStatuses->each(function (string $previousStatus): void {
                $this->reorderSupervisorStatus($previousStatus);
            });
        });

        unset($this->tasks, $this->summary, $this->columns);
    }

    protected function taskCardQuery(string $taskCardKey): Builder
    {
        if (Str::startsWith($taskCardKey, 'task-')) {
            $taskId = Str::after($taskCardKey, 'task-');

            abort_unless(ctype_digit($taskId), 404);

            return $this->taskQuery()->whereKey((int) $taskId);
        }

        return $this->taskQuery()->where('task_group_id', $taskCardKey);
    }

    protected function reorderSupervisorStatus(string $status, ?string $movedTaskCardKey = null, ?int $position = null): void
    {
        $taskCards = $this->taskQuery()
            ->where('status', $status)
            ->when($movedTaskCardKey, function (Builder $query) use ($movedTaskCardKey): void {
                if (Str::startsWith($movedTaskCardKey, 'task-')) {
                    $taskId = Str::after($movedTaskCardKey, 'task-');

                    if (ctype_digit($taskId)) {
                        $query->whereKeyNot((int) $taskId);
                    }

                    return;
                }

                $query->where(function (Builder $nested) use ($movedTaskCardKey): void {
                    $nested->where('task_group_id', '!=', $movedTaskCardKey)
                        ->orWhereNull('task_group_id');
                });
            })
            ->orderBy('sort_order')
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'normal' THEN 1 ELSE 2 END")
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest('created_at')
            ->get()
            ->groupBy(fn (StudentTask $task): string => $this->taskCardKey($task))
            ->map(fn (Collection $tasks, string $key): array => [
                'key' => $key,
                'task_ids' => $tasks->pluck('id')->all(),
            ])
            ->values();

        if ($movedTaskCardKey !== null) {
            $movedTaskIds = $this->taskCardQuery($movedTaskCardKey)
                ->where('status', $status)
                ->pluck('id')
                ->all();

            if ($movedTaskIds !== []) {
                $taskCards->splice(min(max(0, $position ?? 0), $taskCards->count()), 0, [[
                    'key' => $movedTaskCardKey,
                    'task_ids' => $movedTaskIds,
                ]]);
            }
        }

        $taskCards->values()->each(function (array $taskCard, int $index): void {
            StudentTask::query()
                ->whereIn('id', $taskCard['task_ids'])
                ->update(['sort_order' => $index]);
        });
    }

    protected function taskCardKey(StudentTask $task): string
    {
        return $task->task_group_id ?: 'task-'.$task->id;
    }

    protected function nextSupervisorTaskSortOrder(string $status): int
    {
        return ((int) $this->taskQuery()
            ->where('status', $status)
            ->max('sort_order')) + 1;
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
            ->where('company_supervisor_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('student')
            ->filter();
    }

    #[Computed]
    public function tasks()
    {
        return $this->applyFilters($this->taskQuery())
            ->orderBy('sort_order')
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
                $taskCards = $tasks->get($status, collect())
                    ->groupBy(fn (StudentTask $task): string => $this->taskCardKey($task))
                    ->map(function ($groupedTasks, string $key): array {
                        $students = $groupedTasks
                            ->pluck('student')
                            ->filter()
                            ->values();

                        return [
                            'key' => $key,
                            'task' => $groupedTasks->first(),
                            'tasks' => $groupedTasks,
                            'students' => $students,
                            'visible_students' => $students->take(3),
                            'hidden_students' => max($students->count() - 3, 0),
                        ];
                    })
                    ->values();

                return [
                    'status' => $status,
                    'title' => $label,
                    'tasks' => $taskCards,
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
